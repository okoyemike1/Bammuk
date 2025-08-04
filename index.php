<?php
// index.php (root of the project)

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$routesUrl = rtrim($baseUrl, '/') . '/routes/user_api.php?endpoint=';

$endpoints = [
    "signup"         => "{$routesUrl}signup",
    "login"          => "{$routesUrl}login",
    "verify-otp"     => "{$routesUrl}verify-otp",
    "update-profile" => "{$routesUrl}update-profile"
];

$response = [
    "status" => "success",
    "message" => "🚗 Welcome to the Car Rental API",
    "instructions" => "Send POST requests to the following endpoints with Content-Type: application/json",
    "endpoints" => [
        "POST signup" => "Register a user with: email, password, confirmPassword",
        "POST login"  => "Login with: email, password",
        "POST verify-otp" => "Verify OTP with: email, otp",
        "POST update-profile" => "Update user with: email, fullname, description",
    ],
    "example_requests" => $endpoints
];

echo json_encode($response, JSON_PRETTY_PRINT);
