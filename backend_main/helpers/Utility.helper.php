<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

/**
 * Utility Helper class
 * 
 * * This class provides utility functions for various operations.
 * * It includes methods for validation, sanitization, and other common tasks.
 * 
 * * @package Utility
 * * @version 1.0
 * * @author Your Name
 * * @license MIT
 * * @link    
 * * @since   1.0
 */
class UtilityHelper
{
    /**
     * Validate email format
     * 
     * @param string $email Email address to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Sanitize input data
     * 
     * @param mixed $data Data to sanitize
     * @return mixed Sanitized data
     */
    public static function sanitizeInput($data)
    {
        return htmlspecialchars(strip_tags(trim($data)));
    }

    /**
     * Validate required fields in an array
     * 
     * @param array $data Data to validate
     * @param array $requiredFields Fields that are required
     * @return array Array containing error status and messages
     */
    public static function validateFields($data, $requiredFields)
    {
        $errors = [];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = "$field is required.";
            } else {
                $data[$field] = stripslashes($data[$field]);
                $data[$field] = is_string($data[$field]) ? self::sanitizeInput($data[$field]) : $data[$field];
            }
        }

        if (empty($errors)) {
            return ['error' => false, 'data' => $data];
        } else {
            return ['error' => true, 'error_msg' => $errors, 'data' => null];
        }
    }

    /**
     * Respond with a JSON message
     * 
     * @param int $statusCode HTTP status code
     * @param string $status Status message
     * @param mixed $data Data to include in the response
     * @return array JSON response
     */
    public static function jsonResponse($statusCode, $status, $data = [])
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode([
            'status_code' => $statusCode,
            'status' => $status,
            'data' => $data
        ]);
    }
}