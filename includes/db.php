<?php
require_once 'config.php';

class Database {
    private $connection;
    private static $instance = null;

    private function __construct() {
        try {
            // Create database directory if it doesn't exist
            $dbDir = dirname(DB_PATH);
            if (!file_exists($dbDir)) {
                mkdir($dbDir, 0777, true);
            }

            $this->connection = new PDO(
                "sqlite:" . DB_PATH,
                null,
                null,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );

            // Enable foreign key support
            $this->connection->exec('PRAGMA foreign_keys = ON');
        } catch (PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            die("Une erreur est survenue. Veuillez réessayer plus tard.");
        }
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Get database instance
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Get database connection
    public function getConnection() {
        return $this->connection;
    }

    // Prepare and execute query
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            throw new Exception("Une erreur est survenue lors de l'exécution de la requête.");
        }
    }

    // Get single record
    public function single($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    // Get multiple records
    public function get($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    // Insert record
    public function insert($table, $data) {
        $fields = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
        
        $this->query($sql, array_values($data));
        return $this->connection->lastInsertId();
    }

    // Update record
    public function update($table, $data, $where, $whereParams = []) {
        $fields = implode('=?,', array_keys($data)) . '=?';
        $sql = "UPDATE {$table} SET {$fields} WHERE {$where}";
        
        $params = array_merge(array_values($data), $whereParams);
        $this->query($sql, $params);
    }

    // Delete record
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $this->query($sql, $params);
    }
}
