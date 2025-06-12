<?php

class Settings {
    private static ?self $instance = null;
    private array $settings = [];
    private static bool $dbConnected = false;

    private function __construct() {
        $this->loadSettings();
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadSettings(): void {
        // Try to load settings from the database
        // Ensure Database class and config are loaded
        if (!class_exists('Database')) {
            if (file_exists(__DIR__ . '/Database.php')) {
                require_once __DIR__ . '/Database.php';
            } else {
                // Cannot proceed without Database class if we intend to use it
                // error_log("Settings Error: Database class not found.");
                // Fallback to default non-DB behavior or die. For now, we'll allow it to proceed
                // and it will just use default values or values set via set() method.
                self::$dbConnected = false;
            }
        }

        // Check if DB_HOST is defined, as an indicator that config.php was loaded
        // and Database class can attempt connection.
        if (defined('DB_HOST') && class_exists('Database')) {
            try {
                // Fetch all settings marked for autoload or all settings if few
                $stmt = Database::query("SELECT setting_key, setting_value FROM settings WHERE autoload = TRUE OR autoload = 1");
                if ($stmt) {
                    $dbSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                    if ($dbSettings) {
                        $this->settings = $dbSettings;
                        self::$dbConnected = true;
                    }
                }
            } catch (PDOException $e) {
                error_log("Settings Error: Could not connect to database to load settings. " . $e->getMessage());
                self::$dbConnected = false;
                // Continue with empty settings, defaults can be applied by the application
            }
        } else {
             self::$dbConnected = false;
             // error_log("Settings Info: DB_HOST not defined or Database class not found. Settings will not be loaded from DB.");
        }


        // Define some default fallbacks if not loaded from DB
        $this->settings['site_name'] = $this->settings['site_name'] ?? (defined('SITE_NAME') ? SITE_NAME : 'My Application');
        $this->settings['admin_email'] = $this->settings['admin_email'] ?? (defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'admin@example.com');
        $this->settings['posts_per_page'] = $this->settings['posts_per_page'] ?? 10;
        // Add more defaults as necessary
    }

    /**
     * Get a setting value by key.
     *
     * @param string $key The setting key.
     * @param mixed $default The default value to return if the key is not found.
     * @return mixed The setting value or the default.
     */
    public static function get(string $key, mixed $default = null): mixed {
        $instance = self::getInstance();
        return $instance->settings[$key] ?? $default;
    }

    /**
     * Get all settings.
     * @return array
     */
    public static function getAll(): array {
        return self::getInstance()->settings;
    }

    /**
     * Set a setting value (primarily for runtime, does not save to DB automatically).
     * To save to DB, a saveSetting() method would be needed.
     *
     * @param string $key The setting key.
     * @param mixed $value The setting value.
     */
    public static function set(string $key, mixed $value): void {
        $instance = self::getInstance();
        $instance->settings[$key] = $value;
    }

    /**
     * Saves a setting to the database.
     *
     * @param string $key The setting key.
     * @param mixed $value The value to save.
     * @param string $group The setting group (optional).
     * @param bool $autoload Whether the setting should be autoloaded (optional).
     * @return bool True on success, false on failure.
     */
    public static function save(string $key, mixed $value, string $group = 'general', ?bool $autoload = null): bool {
        if (!self::$dbConnected && defined('DB_HOST') && class_exists('Database')) {
             // Attempt to establish connection if not already connected (e.g. initial load failed)
             // This is simplified; a robust solution might involve a dedicated connection check/retry.
             self::getInstance()->loadSettings(); // This will try to connect and set self::$dbConnected
        }

        if (!self::$dbConnected) {
            error_log("Settings Error: Cannot save setting '{$key}'. No database connection.");
            return false;
        }

        try {
            $currentValue = self::get($key, null); // Check if exists for INSERT vs UPDATE
            $serializedValue = is_array($value) || is_object($value) ? serialize($value) : $value;

            if ($currentValue !== null || self::dbSettingExists($key)) { // If exists, UPDATE
                $sql = "UPDATE settings SET setting_value = :value, setting_group = :group";
                if ($autoload !== null) {
                    $sql .= ", autoload = :autoload";
                }
                $sql .= " WHERE setting_key = :key";
            } else { // If not exists, INSERT
                $sql = "INSERT INTO settings (setting_key, setting_value, setting_group";
                if ($autoload !== null) {
                    $sql .= ", autoload";
                }
                $sql .= ") VALUES (:key, :value, :group";
                if ($autoload !== null) {
                    $sql .= ", :autoload";
                }
                $sql .= ")";
            }

            $params = [':key' => $key, ':value' => $serializedValue, ':group' => $group];
            if ($autoload !== null) {
                $params[':autoload'] = (int)$autoload;
            }

            $stmt = Database::query($sql, $params);
            if ($stmt && $stmt->rowCount() > 0) {
                self::getInstance()->settings[$key] = $value; // Update runtime cache
                return true;
            } elseif ($stmt) { // Query executed but no rows affected (e.g. update with same value)
                self::getInstance()->settings[$key] = $value; // Still update runtime cache
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Settings Save Error for key '{$key}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if a setting key exists in the database.
     * @param string $key
     * @return bool
     */
    private static function dbSettingExists(string $key): bool {
        if (!self::$dbConnected) return false;
        try {
            $stmt = Database::query("SELECT COUNT(*) FROM settings WHERE setting_key = :key", [':key' => $key]);
            return $stmt && $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Settings Error (dbSettingExists for key '{$key}'): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reloads settings from the database.
     */
    public static function refresh(): void {
        self::getInstance()->loadSettings();
    }
}

// Initialize the singleton instance so settings are loaded.
// Settings::getInstance();
// It's better to call Settings::get() or Settings::getInstance() when first needed,
// to ensure DB connection is attempted at the right time (after config is loaded).
?>
