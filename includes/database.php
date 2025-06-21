<?php
require_once 'config.php';

class Database {
    private $pdo;
    private static $instance = null;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    // Generic query execution
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            return false;
        }
    }

    // Fetch single row
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : false;
    }

    // Fetch all rows
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : false;
    }

    // Insert and return last insert ID
    public function insert($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $this->pdo->lastInsertId() : false;
    }

    // Update/Delete operations
    public function execute($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->rowCount() : false;
    }

    // Check if record exists
    public function exists($table, $conditions = []) {
        $where = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $where[] = "$column = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT COUNT(*) as count FROM $table";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $result = $this->fetchOne($sql, $params);
        return $result && $result['count'] > 0;
    }

    // Get count of records
    public function count($table, $conditions = []) {
        $where = [];
        $params = [];
        
        foreach ($conditions as $column => $value) {
            $where[] = "$column = ?";
            $params[] = $value;
        }
        
        $sql = "SELECT COUNT(*) as count FROM $table";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        
        $result = $this->fetchOne($sql, $params);
        return $result ? (int)$result['count'] : 0;
    }

    // Pagination helper
    public function paginate($sql, $params = [], $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT $perPage OFFSET $offset";
        
        $data = $this->fetchAll($sql, $params);
        
        // Get total count (remove ORDER BY and LIMIT for count query)
        $countSql = preg_replace('/ORDER BY.*?(?=LIMIT|$)/i', '', $sql);
        $countSql = preg_replace('/LIMIT.*$/i', '', $countSql);
        $countSql = "SELECT COUNT(*) as total FROM ($countSql) as subquery";
        
        $total = $this->fetchOne($countSql, $params);
        $totalCount = $total ? (int)$total['total'] : 0;
        
        return [
            'data' => $data,
            'total' => $totalCount,
            'pages' => ceil($totalCount / $perPage),
            'current_page' => $page,
            'per_page' => $perPage
        ];
    }

    // Transaction methods
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollback() {
        return $this->pdo->rollback();
    }
}

// Global database instance
$db = Database::getInstance();
?> 