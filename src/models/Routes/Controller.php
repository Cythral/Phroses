<?php

namespace Phroses\Routes;

use \Phroses\Phroses;
use \Phroses\Cascade;
use const \reqc\{ METHOD };

class Controller {
    private $routes = [];
    private $rules = [];
    private $ruleArgs = [];
    private $cascade;

    const RESPONSES = [
		"DEFAULT" => 0,

		"PAGE" => [
			200 => 1,
			301 => 2,
			404 => 3
		],

		"SYS" => [
			200 => 4
		],

		"ASSET" => 5,
		"API" => 6,
		"UPLOAD" => 7,
		"MAINTENANCE" => 8
	];
       
    const METHODS = [
        "GET",
        "POST",
        "PUT",
        "PATCH",
        "DELETE"
    ];
    
    public function __construct($defaultRoute = self::RESPONSES["PAGE"][200]) {
        $this->cascade = new Cascade($defaultRoute);
    }
    
    public function addRuleArgs(...$args) {
        $this->ruleArgs = array_merge($this->ruleArgs, $args);
    }

    public function addRoutes(array $routes) {
        foreach($routes as $route) $this->addRoute($route);
    }
    
    public function addRoute(Route $route) {
        $methods = ($route->method) ? [ strtoupper($route->method) ] : self::METHODS;
        $route->controller = $this;
        
        foreach($methods as $method) {
			if(!isset($this->routes[$method])) $this->routes[$method] = [];
			$this->routes[$method][$route->response] = $route;
		}
        
        $this->setupRules($route);
    }
    
    private function setupRules(Route $route) {
        $rules = array_map(function($expr) use ($route) { 
            return [ $expr, $route->response ]; 
        }, $route->rules($this->cascade, ...$this->ruleArgs));
        
        $this->rules = array_merge($this->rules, $rules);
    }

    public function getResponse() {
        sort($this->rules);
        
        foreach($this->rules as [ $expr, $response ]) {
            $this->cascade->addRule($expr(), $response);
        }

        return $this->cascade->getResult();
    }

    /**
     * Selects a route based on rules
     */
    public function select(string $method = METHOD, ?int $response = null) {
        $response = $response ?? $this->getResponse();

        if($response == self::RESPONSES["SYS"][200]) $method = "GET";
        if(!isset($this->routes[$method][$response])) $response = self::RESPONSES["DEFAULT"];
        
        return $this->routes[$method][$response] ?? null;
    }
}