<?php
/**
 * Database Configuration
 * Lùcas Customer Portal Database Connection
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'lucas_customer_portal';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    
    public $conn;
    
    public function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    public function commit() {
        return $this->conn->commit();
    }
    
    public function rollback() {
        return $this->conn->rollback();
    }
    
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }
    
    public function query($sql) {
        return $this->conn->query($sql);
    }
}

// Global database instance
$database = new Database();
$db = $database->getConnection();
?>
