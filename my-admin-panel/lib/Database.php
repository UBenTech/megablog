<?php

class Database {
    private static ?PDO $instance = null;
    private PDO $pdo;
    private string $host = DB_HOST;
    private string $user = DB_USER;
    private string $pass = DB_PASS;
    private string $dbname = DB_NAME;

    private function __construct() {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // Log error or handle more gracefully in a real application
            error_log("Database Connection Error: " . $e->getMessage());
            // Potentially throw a custom exception or die with a user-friendly message
            die("Could not connect to the database. Please check the configuration and ensure the database server is running.");
        }
    }

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            // Ensure config is loaded
            if (!defined('DB_HOST')) {
                // This is a fallback, ideally config.php is always included before DB operations
                $configFile = __DIR__ . '/../config/config.php';
                if (file_exists($configFile)) {
                    require_once $configFile;
                } else {
                    die("Database configuration is missing.");
                }
            }
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {}

    /**
     * A simple query helper method.
     * For more complex scenarios, consider using query builder or ORM.
     *
     * @param string $sql The SQL query string.
     * @param array $params Parameters to bind to the query.
     * @return PDOStatement|false The PDOStatement object, or false on failure.
     */
    public static function query(string $sql, array $params = []): PDOStatement|false {
        try {
            $stmt = self::getInstance()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database Query Error: " . $e->getMessage() . " SQL: " . $sql . " Params: " . print_r($params, true));
            // In a production environment, you might want to throw an exception
            // or return a more specific error type.
            return false;
        }
    }

    /**
     * Fetches a single row.
     *
     * @param string $sql
     * @param array $params
     * @return mixed The first row as an associative array, or false if no rows or error.
     */
    public static function fetchOne(string $sql, array $params = []): mixed {
        $stmt = self::query($sql, $params);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }

    /**
     * Fetches all rows.
     *
     * @param string $sql
     * @param array $params
     * @return array All rows as an array of associative arrays, or an empty array if no rows or error.
     */
    public static function fetchAll(string $sql, array $params = []): array {
        $stmt = self::query($sql, $params);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Executes a statement (INSERT, UPDATE, DELETE) and returns the number of affected rows.
     *
     * @param string $sql
     * @param array $params
     * @return int The number of affected rows, or 0 if an error occurred or no rows were affected.
     */
    public static function execute(string $sql, array $params = []): int {
        $stmt = self::query($sql, $params);
        return $stmt ? $stmt->rowCount() : 0;
    }

    /**
     * Returns the ID of the last inserted row or sequence value.
     *
     * @param string|null $name Name of the sequence object from which the ID should be returned.
     * @return string|false The ID of the last inserted row, or false on failure.
     */
    public static function lastInsertId(string $name = null): string|false {
        try {
            return self::getInstance()->lastInsertId($name);
        } catch (PDOException $e) {
            error_log("Database Error getting lastInsertId: " . $e->getMessage());
            return false;
        }
    }
}
?>
