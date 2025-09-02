<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

/**
 * Database Configuration Class
 * 
 * This class handles the database connection settings and provides methods to connect to the database.
 * 
 * @package Database
 * @version 1.0
 * @author Your Name
 * @license MIT
 * @link    
 * @since   1.0
 */
class DBConfig
{
    /**
     * Database settings
     * 
     * @return object Returns an object containing database settings
     */
    protected static function dbSettings() {

        $settings = [
            "db_host" => "localhost",
            "db_name" => "bammuk",
            "db_user" => "root",
            "db_password" => ""
        ];
        return (object) $settings;
    }
     
    /**
     * Connect to the database
     * * @return object Returns a PDO connection object
     * * @throws Exception If the connection fails
     */
    public static function connect() {
        $settings = self::dbSettings();

        try {
            $dsn = "mysql:host={$settings->db_host};dbname={$settings->db_name};charset=utf8";
            $conn = new PDO($dsn, $settings->db_user, $settings->db_password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            return $conn;
        } catch (PDOException $e) {
            // throw new Exception("Connection failed: " . $e->getMessage());
            return ['statuscode' => 500, 'status' => "Connection failed: " . $e->getMessage(), 'error' => true];
        }
    }
}
