<?php

namespace Phroses;

function FileList($dir) : array {
    if(file_exists($dir)) {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach($iterator as $file) {
            if(!substr($file, strrpos($file, ".")+1)) continue;
            $files[] = $file;
        }
        return $files;
    }
    return [];
}

function JsonOutput($array) {
    http_response_code(400);
    header("content-type: application/json; charset=utf8");
    die(json_encode($array));
}


function HandleMethod(string $method, callable $handler, array $filters = []) {
    if(strtolower($_SERVER["REQUEST_METHOD"]) == strtolower($method)) {
        if(count($filters) > 0) {
            foreach($filters as $k => $f) {                
                if(is_array($f)) {
                    if(!array_key_exists($k, $_REQUEST)) JsonOutput([ "type" => "error", "error" => "missing_value", "field" => $k ]);
                    $val = $_REQUEST[$k];

                    if(isset($f["filter"]) && !filter_var($val, [
                        "url" => FILTER_VALIDATE_URL,
                        "int" => FILTER_VALIDATE_INT,
                        "email" => FILTER_VALIDATE_EMAIL
                    ][$f["filter"]])) JsonOutput([ "type" => "error", "error" => "bad_filter", "filter" => $f["filter"], "field" => $k ]);

                    if((isset($f["size"]["min"]) && strlen($val) < $f["size"]["min"]) ||
                       (isset($f["size"]["max"]) && strlen($val) > $f["size"]["max"])) {
                        JsonOutput([ "type" => "error", "error" => "bad_size", "field" => $k ]);
                    }
                } else if(!array_key_exists($f, $_REQUEST)) JsonOutput([ "type" => "error", "error" => "missing_value", "field" => $f ]);
            }    
        }
        
        http_response_code(200);
        $handler();
        die;
    }
}