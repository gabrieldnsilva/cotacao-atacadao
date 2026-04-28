<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Singleton Database class to manage MariaDB connections.
 */
class Database {
    private static ?PDO $instance = null;

    /**
     * Get the PDO instance.
     * 
     * @return PDO
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            require_once __DIR__ . '/Config.php';

            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // In production, we should log this and show a generic message
                header('Content-Type: application/json', true, 500);
                echo json_encode([
                    "success" => false, 
                    "message" => "Database connection error: " . $e->getMessage()
                ]);
                exit();
            }
        }
        return self::$instance;
    }

    /**
     * Prevent cloning or unserialization of the singleton.
     */
    private function __construct() {}
    private function __clone() {}
}
