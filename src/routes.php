<?php
/**
 * This file sets up phroses' routing / method mapping.  This is included
 * from within the start method of the phroses class, so self here refers to 
 * \Phroses\Phroses
 */

namespace Phroses;

use \reqc;
use \reqc\Output;
use \Phroses\JsonServer;
use \listen\Events;
use \inix\Config as inix;
use \phyrex\Template;
use \Phroses\Theme\Theme;
use \Phroses\Upload;
use \Phroses\Routes\Route;
use \Phroses\Routes\Controller as RouteController;
use \Phroses\Exceptions\UploadException;

// request variables
use const \reqc\{ VARS, MIME_TYPES, PATH, EXTENSION, METHOD, HOST, BASEURL };

$routes = [];

/**
 * GET PAGE/200
 * This route gets page information and either displays it as html or json
 */
$routes[] = new class extends Route {
	public $method = "get";
	public $response = RouteController::RESPONSES["PAGE"][200];

	public function follow(&$page, &$site, &$out) {
		parent::follow($page, $site, $out);

		if(safeArrayValEquals($_GET, "mode", "json")) {
			$out = new JsonServer();
			$out->send($page->getData(), 200);
		}

		if(safeArrayValEquals($_GET, "mode", "css")) {
			$out->setContentType(MIME_TYPES["CSS"]);
			$page->views--;
			die($page->css);
		}

		if($page->css != null) $page->theme->push("stylesheets", [ "src" => "?mode=css"] );
		
		$page->display();
	}
};

/**
 * GET PAGE/301
 * This route redirects to a different page.  If the destination is not specified, an error is displayed instead
 */
$routes[] = new class extends Route {
	public $method = "get";
	public $response = RouteController::RESPONSES["PAGE"][301];

	public function follow(&$page, &$site, &$out) {
		parent::follow($page, $site, $out);

		if(array_key_exists("destination", $page->content) && !empty($page->content["destination"]) && $page->content["destination"] != PATH) {
			$out->redirect($page->content["destination"]);
		} 
		
		$page->theme->setType("page", true);
		$page->display([ "main" => (string) new Template(INCLUDES["TPL"]."/errors/redirect.tpl") ]);

	}

	public function rules($cascade, $page, $site) {
		return [ 
			4 => function() use (&$page) { 
				return $page->type == "redirect"; 
			} 
		];
	}
};

/**
 * GET SYS/200
 * Displays an internal phroses "view" (can be a dashboard page or asset file)
 */
$routes[] = new class extends Route {
	public $method = "get";
	public $response = RouteController::RESPONSES["SYS"][200];

	public function follow(&$page, &$site, &$out) {
		parent::follow($page, $site, $out);

		$path = substr(PATH, strlen($site->adminURI));

		if(!is_dir($file = INCLUDES["VIEWS"].$path) && file_exists($file) && strtolower(EXTENSION) != "php") {
			readfileCached($file);
		}

		ob_start();
		$page->theme->push("stylesheets", [ "src" => "{$site->adminURI}/assets/css/phroses.css" ]);
		$page->theme->push("stylesheets", [ "src" => "//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" ]);
		$page->theme->push("scripts", [ "src" => "{$site->adminURI}/assets/js/phroses.min.js", "attrs" => "defer data-adminuri=\"{$site->adminURI}\" id=\"phroses-script\"" ]);

		if(!$_SESSION) {
			$out->setCode(401);
			include INCLUDES["VIEWS"]."/login.php";
		
		} else {
			if(METHOD == "GET") {				
				$dashbar = new Template(INCLUDES["TPL"]."/dashbar.tpl");
				$dashbar->host = HOST;
				$dashbar->adminuri = $site->adminURI;
				echo $dashbar;
			}

			if(file_exists($file = INCLUDES["VIEWS"]."{$path}/index.php")) include $file;
			else if(file_exists($file = INCLUDES["VIEWS"]."{$path}.php")) include $file;
			else echo new Template(INCLUDES["TPL"]."/errors/404.tpl");
		}

		if($page->theme->hasType("admin")) $page->theme->setType("admin", true);
		else $page->theme->setType("page", true);
		
		$content = new Template(INCLUDES["TPL"]."/admin.tpl");
		$content->content = trim(ob_get_clean());

		$page->theme->title = $title ?? "Phroses System Page";
		$page->theme->main = (string) $content;
		$page->display();

	}
	
	public function rules($cascade, $page, $site) {
		return [
			1 => function() use (&$site) {
				return (
					PATH != "/" &&
					stringStartsWith(PATH, $site->adminURI) && (

						file_exists(($adminpath = INCLUDES["VIEWS"].substr(PATH, strlen($site->adminURI))).".php") || // views/page.php
						file_exists($adminpath) || // views/page.css
						file_exists("$adminpath/index.php") // views/page/index.php
					) && $site->ipHasAccess($_SERVER["REMOTE_ADDR"])
				); 
			}
		];
	}
};

/**
 * GET PAGE/404
 * Displays a a 404 not found error when a page or asset is not found.
 */
$routes[] = new class extends Route {
	public $method = "get";
	public $response = RouteController::RESPONSES["PAGE"][404];
	
	public function follow(&$page, &$site, &$out) {
		parent::follow($page, $site, $out);

		$out->setCode(404);
		$out->setContentType(MIME_TYPES["HTML"]);
	
		if($page->theme->hasError("404")) die($page->theme->readError("404"));

		$page->theme->setType("page", true);
		$page->theme->title = "404 Not Found";
		$page->theme->main = (string) new Template(INCLUDES["TPL"]."/errors/404.tpl");

		$page->display();
	}
	
	public function rules($cascade, $page, $site) {
		return [ 
			0 => function() use (&$page) { return !$page->id; },
			5 => function() use (&$page, &$cascade) { 
				return $cascade->getResult() == RouteController::RESPONSES["PAGE"][200] && !$page->public && !$_SESSION; 
			}
		];
	}
};

/**
 * (All) ASSET
 * Serves theme asset files
 */
$routes[] = new class extends Route {
	public $response = RouteController::RESPONSES["ASSET"];
	
	public function follow(&$page, &$site, &$out) {
		parent::follow($page, $site, $out);

		$page->theme->readAsset(PATH); 
	}
	
	public function rules($cascade, $page, $site) {
		return [ 
			7 => function() use (&$page, &$cascade) { 
				return (
					in_array($cascade->getResult(), [ RouteController::RESPONSES["MAINTENANCE"], RouteController::RESPONSES["PAGE"][404] ]) && 
					$page->theme->hasAsset(PATH)
				); 
			} 
		];
	}
};

/**
 * (All) API
 * Runs the theme API, if it has one
 */
$routes[] = new class extends Route {
	public $response = RouteController::RESPONSES["API"];

	public function follow(&$page, &$site, &$out) { 
		parent::follow($page, $site, $out);

		$page->theme->runApi(); 
	}
	public function rules($cascade, $page, $site) {
		return [ 
			3 => function() use (&$page) { 
				return (stringStartsWith(PATH, "/api") && $page->theme->hasApi()); 
			} 
		];
	}
};


/**
 * POST (Default handler)
 * This handles all post requests.  If a page does not exist, this route creates one based on request parameters.
 */
$routes[] = new class extends Route {
	public $method = "post";
	public $response = RouteController::RESPONSES["DEFAULT"];

	public function follow(&$page, &$site, &$out) {
		parent::follow($page, $site, $out);

		$out = new JsonServer();

		// Validation
		$out->restrict();
		$out->error("resource_exists", Phroses::$response != RouteController::RESPONSES["PAGE"][404]);

		foreach(["title","type"] as $type) {
			$out->error("missing_value", !array_key_exists($type, $_REQUEST), 400, [ "field" => $type ]);
		}

		$out->error("bad_value", !$page->theme->hasType($_REQUEST["type"]), 400, [ "field" => "type" ]);

		$page = Page::create(PATH, $_REQUEST["title"], $_REQUEST["type"], $_REQUEST["content"] ?? "{}", $site->id, $site->theme);
		$out->error("create_fail", !$page);

		$out->success(200, [
			"id" => $page->id, 
			"content" => $page->theme->getBody(),
			"typefields" => $page->theme->getEditorFields()
		]);
	}
};

/**
 * PATCH (Default handler)
 * This handles all put requests.  If a page exists, this route edits it based on request parameters.
 */
$routes[] = new class extends Route {
	public $method = "patch";
	public $response = RouteController::RESPONSES["DEFAULT"];

	public function follow(&$page, &$site, &$out) {
		parent::follow($page, $site, $out);
		$out = new JsonServer();

		// Validation
		$out->restrict();
		$out->requireExistingPage();
		$out->requireChanges(["type", "uri", "title", "content", "public", "css"]);
		$out->error("bad_value", !$page->theme->hasType($_REQUEST["type"] ?? $page->type), 400, [ "field" => "type" ]);
		$out->error("resource_exists", isset($_REQUEST["uri"]) && $site->hasPage($_REQUEST["uri"]));
		
		// if requesting a page type to change to redirect with no destination specified, just send the typefields
		if(safeArrayValEquals($_REQUEST, "type", "redirect") && (!isset($_REQUEST["content"]) || 
			(isset($_REQUEST["content"]) && !isset(json_decode($_REQUEST["content"])->destination)))) {

			$out->success(200, [ "typefields" => $page->theme->getEditorFields("redirect") ]);
		}
		
		$sanitizer = new Sanitizer($_REQUEST);
		$sanitizer->applyCallback("htmlspecialchars_decode", ["content"]);
		$_REQUEST = $sanitizer();

		foreach($_REQUEST as $key => $value) {
			if(!isset($page->{$key}) && $key != "css") continue;
			$page->{$key} = $value;
		}

		if(isset($_REQUEST["type"]) && $_REQUEST["type"] != "redirect") {
			$page->content = "{}";
		}

		$output = [];
		if(!isset($_REQUEST["nocontent"])) $output["content"] = $page->theme->getBody();
		if(isset($_REQUEST["type"])) $output["typefields"] = $page->theme->getEditorFields($_REQUEST["type"]);

		$out->success(200, $output);
	}
};

/**
 * DELETE (Default Handler)
 * This handles all delete requests. If a page exists, this route deletes it.
 */
$routes[] = new class extends Route {
	public $method = "delete";
	public $response = RouteController::RESPONSES["DEFAULT"];

	public function follow(&$page, &$site, &$out) {
		parent::follow($page, $site, $out);

		$out = new JsonServer();
		$out->restrict();
		$out->requireExistingPage();
		$out->error("delete_failed", !$page->delete());
		$out->success();
	}
};

/**
 * GET UPLOAD
 * This route serves upload files.
 */
$routes[] = new class extends Route {
	public $method = "get";
	public $response = RouteController::RESPONSES["UPLOAD"];
	
	public function follow(&$page, &$site, &$out) {
		parent::follow($page, $site, $out);

		(new Upload($site, substr(PATH, 8)))->display();
	}

	public function rules($cascade, $page, $site) {
		return [ 
			2 => function() use (&$site) { 
				return (
					stringStartsWith(PATH, "/uploads") && trim(PATH, "/") != "uploads" && (
						(new Upload($site, substr(PATH, 8)))->exists() || strtolower(METHOD) == "post"
					)
				); 
			}
		];
	}
};

/**
 * DELETE UPLOAD
 * This route deletes uploads
 */
$routes[] = new class extends Route {
	public $method = "delete";
	public $response = RouteController::RESPONSES["UPLOAD"];

	public function follow(&$page, &$site, &$out) {
		parent::follow($page, $site, $out);

		$out = new JsonServer;
		$out->restrict();

		try {
			$upload = new Upload($site, substr(PATH, 8));
			if(!$upload->delete()) throw new UploadException("failed_delete");

		} catch(UploadException $e) {
			$out->error($e->getMessage());
		}

		$out->success();
	}
};

/**
 * PATCH UPLOAD
 * Allows renaming of an upload
 */
$routes[] = new class extends Route {
	public $method = "patch";
	public $response = RouteController::RESPONSES["UPLOAD"];
	private $out;

	public function follow(&$page, &$site, &$out) {
		parent::follow($page, $site, $out);

		$out = new JsonServer;
		$out->restrict();
		$out->error("missing_value", !isset($_REQUEST["to"]), 400, [ "value" => "to" ]);

		try {
			$upload = new Upload($site, substr(PATH, 8));
			if(!$upload->rename($_REQUEST["to"])) throw new UploadException("failed_rename");
		} catch(UploadException $e) {
			$out->error($e->getMessage());
		}

		$out->success();
	}
};

/**
 * POST UPLOAD
 * Creates a new upload
 */
$routes[] = new class extends Route {
	public $method = "post";
	public $response = RouteController::RESPONSES["UPLOAD"];

	public function follow(&$page, &$site, &$out) {
		parent::follow($page, $site, $out);

		$out = new JsonServer;
		$out->restrict();
		$out->error("missing_value", !isset($_FILES["file"]), 400, [ "value" => "file" ]);
		
		try {
			Upload::create($site, substr(PATH, 8), $_FILES["file"]);
		} catch(UploadException $e) {
			$out->error($e->getMessage());
		}

		$out->success();
	}
};

/**
 * (All) MAINTENANCE
 * This route displays maintenance mode if a site is in one.
 */
$routes[] = new class extends Route {
	public $response = RouteController::RESPONSES["MAINTENANCE"];

	public function follow(&$page, &$site, &$out) {
		parent::follow($page, $site, $out);
		
		$out->setCode(503);
		die(new Template(INCLUDES["TPL"]."/maintenance.tpl"));
	}

	public function rules($cascade, $page, $site) {
		return [
			6 => function() use (&$site, &$cascade) { 
				return $site->maintenance && !$_SESSION && $cascade->getResult() != RouteController::RESPONSES["SYS"][200]; 
			} 
		];
	}
};


return $routes;  // return a list of routes for the listen event