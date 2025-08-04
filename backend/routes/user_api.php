<?php
// routes/user_api.php

// Set response headers
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Load dependencies
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/user.php';

// Get request method and endpoint
$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_GET['endpoint'] ?? '';

// Initialize user model
$user = new User($conn);

// Handle POST requests
if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    switch ($endpoint) {
        case 'signup':
            if (!isset($data['email'], $data['password'], $data['confirmPassword'])) {
                http_response_code(400);
                echo json_encode(["error" => "Email, password, and confirmPassword are required"]);
                exit;
            }

            echo json_encode($user->signup(
                $data['email'],
                $data['password'],
                $data['confirmPassword']
            ));
            break;

        case 'login':
            if (!isset($data['email'], $data['password'])) {
                http_response_code(400);
                echo json_encode(["error" => "Email and password are required"]);
                exit;
            }

            echo json_encode($user->login(
                $data['email'],
                $data['password']
            ));
            break;

        case 'verify-otp':
            if (!isset($data['email'], $data['otp'])) {
                http_response_code(400);
                echo json_encode(["error" => "Email and OTP are required"]);
                exit;
            }

            echo json_encode($user->verifyOtp(
                $data['email'],
                $data['otp']
            ));
            break;

        case 'update-profile':
            if (!isset($data['email'], $data['fullname'], $data['description'])) {
                http_response_code(400);
                echo json_encode(["error" => "Email, fullname, and description are required"]);
                exit;
            }

            echo json_encode($user->updateProfile(
                $data['email'],
                $data['fullname'],
                $data['description']
            ));
            break;

        default:
            http_response_code(404);
            echo json_encode(["error" => "Invalid endpoint"]);
            break;
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
?>
