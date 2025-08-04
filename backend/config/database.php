<?php
// config/database.php

// Database configuration
$host = 'localhost';
$db   = 'bam';
$user = 'root';   // Adjust based on your setup
$pass = '';       // Adjust based on your setup

// Disable mysqli exceptions for custom error handling
mysqli_report(MYSQLI_REPORT_OFF);

// Create a connection to MySQL server (initially no DB selected)
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database server connection failed.',
        'error'   => $conn->connect_error
    ]));
}

// Attempt to select the database
if (!$conn->select_db($db)) {
    $createDbSql = "CREATE DATABASE `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

    if (!$conn->query($createDbSql)) {
        $conn->close();
        http_response_code(500);
        die(json_encode([
            'success' => false,
            'message' => 'Failed to create database.',
            'error'   => $conn->error
        ]));
    }

    // Select the newly created database
    $conn->select_db($db);
}

// Optional: set charset for future queries
$conn->set_charset('utf8mb4');
?>
