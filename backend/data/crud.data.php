<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

require_once "../config/config.php";
require_once __DIR__."/schema.php";

/**
 * CRUD Operations class
 * * This class provides methods to perform basic CRUD operations on a database.
 * * It includes methods to create, read, update, and delete records.
 * 
 * * @package Crud
 * * @version 1.0
 * * @author Your Name
 * * @license MIT
 * * * @link    
 * * @since   1.0
 * * @todo    Add more methods for advanced operations
 */

class Crud extends DBConfig {
    private static $db;
    private $schemas;
    /**
     * Class Constructor
     * 
     * @param object $db Database connection object
     */
    public function __construct()
    {
        self::$db = self::connect();
        $this->schemas = new Schema(self::$db);
        $this->createSchemas();
    }

    /**
     * Create schemas
     * * @return void
     * * This method can be used to create database schemas if needed.
     * * It can be called after the class is instantiated.
     */
    private function createSchemas() {
        // You can call schema creation methods here
        $this->schemas->createUsersTable();
        $this->schemas->createCarsTable();
        $this->schemas->createTestimonialsTable();
        $this->schemas->createLikesTable();
        $this->schemas->createSessionsTable();
    }

    /**
     * Create a new record in the database
     * 
     * @param string $table Table name
     * @param array $data Data to insert
     * @return bool True on success, false on failure
     */
    public function create($table, $data) {
        // Implementation for creating a record
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), '?'));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = self::$db->prepare($sql);
        $values = array_values($data);
        return $stmt->execute($values);
    }

    /**
     * Read records from the database
     * 
     * @param string $table Table name
     * @param array $conditions Conditions for the query
     * @return array Result set
     */
    public function findAll($table, $fields = ['*']) {
        // Implementation for reading records
        $sql = "SELECT " . implode(", ", $fields) . " FROM $table";
        $stmt = self::$db->prepare($sql);
        $stmt->execute();
        if ($stmt && $stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }
            return $result;
        } else {
            return false; // No records found
        }
    }

    /**
     * Read One record from the database based on given params.
     * 
     * @param string $table Table name
     * @param array $conditions Conditions for the query
     * @return array Result set
     */
    public function findOne($table, $conditions = [], $fields = ['*']) {
        // Implementation for reading records
        $sql = "SELECT " . implode(", ", $fields) . " FROM $table";
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", array_map(function($key) {
                return "$key = ?";
            }, array_keys($conditions)));
        }
        $stmt = self::$db->prepare($sql);
        $values = array_values($conditions);
        $stmt->execute($values);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result !== false) {
            return $result;
        } else {
            return false; // No record found
        }
    }

    /**
     * Find a record by ID
     * * @param string $table Table name
     * * @param int $id Record ID
     * * @param array $fields Fields to select
     * * @return array|false Record data or false if not found
     */
    public function findById($table, $id, $fields = ['*']) {
        // Implementation for reading a record by ID
        $sql = "SELECT " . implode(", ", $fields) . " FROM $table WHERE id = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$id]);
        if ($stmt->rowCount() == 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            return false; // No record found or multiple records found
        }
    }

    /**
     * Find records by a specific field
     * 
     * @param string $table Table name
     * @param string $field Field to search by
     * @param mixed $value Value to search for
     * @return array Result set
     */
    public function findByEmail($table, $email, $fields = ['*']) {
        // Implementation for finding records by a specific field
        $sql = "SELECT " . implode(", ", $fields) ." FROM $table WHERE email = ?";
        $stmt = self::$db->prepare($sql);
        $stmt->execute([$email]);
        if ($stmt && $stmt->rowCount() > 0) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result;
        } else {
            return false; // No records found
        }
    }

    /**
     * Update a record in the database
     * 
     * @param string $table Table name
     * @param array $data Data to update
     * @param array $conditions Conditions for the update
     * @return bool True on success, false on failure
     */
    public function update($table, $data, $conditions) {
        // Implementation for updating a record
        $set = implode(", ", array_map(function($key) {
            return "$key = ?";
        }, array_keys($data)));
        $sql = "UPDATE $table SET $set WHERE " . implode(" AND ", array_map(function($key) {
            return "$key = ?";
        }, array_keys($conditions)));
        $stmt = self::$db->prepare($sql);
        $values = array_merge(array_values($data), array_values($conditions));
        return $stmt->execute($values);
    }

    /**
     * Delete a record from the database
     * 
     * @param string $table Table name
     * @param array $conditions Conditions for the deletion
     * @return bool True on success, false on failure
     */
    public function delete($table, $conditions) {
        // Implementation for deleting a record
        $sql = "DELETE FROM $table WHERE " . implode(" AND ", array_map(function($key) {
            return "$key = ?";
        }, array_keys($conditions)));
        $stmt = self::$db->prepare($sql);
        $values = array_values($conditions);
        return $stmt->execute($values);
    }

    /**
     * Check if a record exists in the database
     * * @param string $table Table name
     * * @param array $conditions Conditions for the check
     * * @return bool True if exists, false otherwise  
     * 
     */
    public function exists($table, $conditions) {
        // Implementation for checking if a record exists
        $sql = "SELECT COUNT(*) FROM $table WHERE " . implode(" AND ", array_map(function($key) {
            return "$key = ?";
        }, array_keys($conditions)));
        $stmt = self::$db->prepare($sql);
        $values = array_values($conditions);
        $stmt->execute($values);
        $count = $stmt->fetchColumn();
        return $count > 0; // Returns true if count is greater than 0
    }
}