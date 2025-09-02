<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
require_once "../controller/Auth.controller.php";

/**
 * Authentication router
 * 
 * @param array $data request payload
 * @param string $route request route
 * 
 * @return string | array
 */
function authRoutes($endpoint, $data, $path)
{
    switch ($endpoint) {
        case 'register':
            $path = $path."Controller";
            $response = getClassInstance($endpoint, $path, $data);
            break;
        case 'login':

            $response = getClassInstance($endpoint, $path."Controller", $data);
            break;
        case 'get_users':
            $response = "List of All users.";
            break;
        default:
            $response = "404 Not Found";
        break;
    }
    return $response;
}

function getClassInstance($endpoint, $path, $data)
{
    
    $className = ucfirst($path);
    // die(var_dump($className));
    $methodName = $endpoint;
    if (!class_exists($className)) {
        return "Class not found.";
    }
    
    $classInstance = new $className($data);

    if (!method_exists($classInstance, $methodName)) {
        return "Method not found.";
    }
    return call_user_func_array([$classInstance, $methodName], [$data]);
}