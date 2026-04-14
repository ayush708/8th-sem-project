<?php
// Start session if it's not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define constants only if they are not already defined
if (!defined('SITEURL')) {
    define('SITEURL', 'http://localhost/OPS/');
}
if (!defined('LOCALHOST')) {
    define('LOCALHOST', 'localhost');
}
if (!defined('DB_USERNAME')) {
    define('DB_USERNAME', 'root');
}
if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', '');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'petshop');
}

// Database class for all database operations
if (!interface_exists('DataAccessInterface')) {
    interface DataAccessInterface {
        public function prepare($query);
        public function bind($stmt, $types, ...$params);
        public function execute($stmt);
        public function getResult($stmt);
    }
}

if (!trait_exists('InputSanitizerTrait')) {
    trait InputSanitizerTrait {
        protected function clean($value) {
            return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
        }
    }
}

if (!class_exists('BaseManager')) {
    abstract class BaseManager {
        use InputSanitizerTrait;

        protected $db;

        public function __construct($db = null) {
            $this->db = $db ?: Database::getInstance();
        }

        public function setDatabase(DataAccessInterface $db) {
            $this->db = $db;
            return $this;
        }

        protected function sanitize($value) {
            return $this->clean($value);
        }

        public function __destruct() {
            // Reserved for child resource cleanup when needed.
        }
    }
}

if (!class_exists('Database')) {
    final class Database implements DataAccessInterface {
    private $conn;
    private static $instance = null;

    private function __construct() {
        $this->conn = mysqli_connect(LOCALHOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
        if (!$this->conn) {
            die("Connection failed: " . mysqli_connect_error());
        }
    }

    // Singleton pattern for database connection
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    // Prepare and execute query
    public function prepare($query) {
        return mysqli_prepare($this->conn, $query);
    }

    // Execute query
    public function query($query) {
        return mysqli_query($this->conn, $query);
    }

    // Get result
    public function getResult($stmt) {
        return mysqli_stmt_get_result($stmt);
    }

    // Fetch associative array
    public function fetchAssoc($result) {
        return mysqli_fetch_assoc($result);
    }

    // Fetch all rows
    public function fetchAll($result) {
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    // Get number of rows
    public function numRows($result) {
        return mysqli_num_rows($result);
    }

    // Get last inserted ID
    public function lastInsertId() {
        return mysqli_insert_id($this->conn);
    }

    // Bind parameters
    public function bind($stmt, $types, ...$params) {
        return mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    // Execute statement
    public function execute($stmt) {
        return mysqli_stmt_execute($stmt);
    }

    // Close connection
    public function close() {
        return mysqli_close($this->conn);
    }

    public function __destruct() {
        if (is_resource($this->conn) || $this->conn instanceof mysqli) {
            @mysqli_close($this->conn);
        }
    }
    }
}

// Get database instance
$conn = Database::getInstance()->getConnection();
?>
