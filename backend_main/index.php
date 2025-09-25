<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");


$method = $_SERVER["REQUEST_METHOD"];
$queryString = $_SERVER["QUERY_STRING"] ?? '';

$uri = $_SERVER["REQUEST_URI"];
$response = handleRequest($uri, $method);

echo json_encode($response);

function handleRequest($url, $method) {
    $url = trim($url, '/');
    $parts = explode('/', $url);
    $dir = "routes/";
    if (count($parts)>=3) {
        $path = $parts[3];
        $route = $parts[4];
        chdir(__DIR__."/routes/");
        $fullpath = __DIR__."/routes/{$path}.route.php";
        if (is_file($fullpath)) {
            require_once $fullpath;
            $function = $path."Routes";
            if (function_exists($function)) {
                // $data = ($method == "POST") ? $_POST = json_decode(file_get_contents('php://input'), true) :
                // $_GET;
                $data = $method == "POST" ? $_POST : $_GET;
                $response = $function($route, $data, $path);
            } else {
                $response = json_encode(["statuscode"=>-1, "status"=>"Invalid function path"]);
            }
        } else {
            $response = json_encode(["statuscode"=>-1, "status"=>"Invalid  file path $path"]);
        }
    } else {
        $response = json_encode(["statuscode"=>-1, "status"=>"Invalid path parts"]);
    }
    return $response;
}