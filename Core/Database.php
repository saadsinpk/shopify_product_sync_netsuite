<?php
namespace Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;

    private function __construct() {
        // Prevent direct creation of object
    }

    public static function getInstance() {
        if (self::$instance === null) {
            $host = DB_HOST;
            $dbname = DB_NAME;
            $username = DB_USER;
            $password = DB_PASS;

            try {
                self::$instance = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Database Connection Failed: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    // Prevent duplication of connection
    private function __clone() {}

    // Prevent duplication of connection
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}
