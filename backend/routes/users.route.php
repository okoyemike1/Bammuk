<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
require_once "../controller/Tasks.controller.php";
require_once "../helpers/Utility.helper.php";

function usersRoutes($endpoint, $data, $path)
{
    switch ($endpoint) {
        case 'profile': // PUT /users/profile (index.php uses $_POST/$_GET; emulate via POST)
            $response = getUsersControllerInstance('updateProfile', $data);
            break;
        case 'books-upload': // POST /users/books-upload -> authors upload
            $response = getUsersControllerInstance('upload', $data);
            break;
        case 'books-invite': // POST /users/books-invite with bookId, email
            $response = getUsersControllerInstance('invite', $data);
            break;
        case 'books-reviews': // GET /users/books-reviews?bookId=1
            $response = getUsersControllerInstance('bookReviews', $data);
            break;
        case 'reviews-add': // POST /users/reviews-add with bookId, comment
            $response = getUsersControllerInstance('addReview', $data);
            break;
        case 'books-reviewed': // GET /users/books-reviewed
            $response = getUsersControllerInstance('booksReviewed', $data);
            break;
        case 'my-reviews': // GET /users/my-reviews
            $response = getUsersControllerInstance('myReviews', $data);
            break;
        default:
            $response = ['statuscode' => 404, 'status' => 'Not Found'];
            break;
    }
    return $response;
}

function getUsersControllerInstance($method, $data)
{
    $className = 'TasksController';
    if (!class_exists($className)) {
        return ['statuscode' => 500, 'status' => 'TasksController not found'];
    }
    $instance = new $className($data);
    if (!method_exists($instance, $method)) {
        return ['statuscode' => 404, 'status' => 'Method not found'];
    }
    return call_user_func_array([$instance, $method], [$data]);
}


