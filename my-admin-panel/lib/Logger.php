<?php

class Logger {
    private static string $logFile = __DIR__ . '/../logs/app.log'; // Default log file path
    private static bool $isInitialized = false;

    private static function initialize(): void {
        if (self::$isInitialized) {
            return;
        }

        $logDir = dirname(self::$logFile);
        if (!file_exists($logDir)) {
            // Attempt to create the log directory
            if (!mkdir($logDir, 0775, true) && !is_dir($logDir)) {
                // Fallback if directory creation fails, e.g., log to error_log
                error_log("Logger Error: Could not create log directory: {$logDir}. Logging to system error log instead.");
                self::$logFile = 'php_error_log'; // Special value to use error_log()
            }
        } elseif (!is_writable($logDir) || (file_exists(self::$logFile) && !is_writable(self::$logFile))) {
            error_log("Logger Error: Log directory or file is not writable: " . (file_exists(self::$logFile) ? self::$logFile : $logDir) . ". Logging to system error log instead.");
            self::$logFile = 'php_error_log';
        }

        self::$isInitialized = true;
    }

    /**
     * Sets a custom log file path.
     * Should be called before any log entries are made, ideally during application setup.
     *
     * @param string $path The absolute path to the log file.
     */
    public static function setLogFile(string $path): void {
        self::$logFile = $path;
        self::$isInitialized = false; // Re-initialize on next log attempt with new path
    }

    /**
     * Logs a message.
     *
     * @param string $level Log level (e.g., INFO, ERROR, DEBUG, ACTIVITY).
     * @param string $message The message to log.
     * @param array $context Optional context data to include with the log.
     */
    private static function log(string $level, string $message, array $context = []): void {
        self::initialize();

        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[{$timestamp}] [{$level}] {$message}";

        if (!empty($context)) {
            $formattedMessage .= " " . json_encode($context);
        }
        $formattedMessage .= PHP_EOL;

        if (self::$logFile === 'php_error_log') {
            error_log($formattedMessage);
        } else {
            // Attempt to write to the custom log file
            if (@file_put_contents(self::$logFile, $formattedMessage, FILE_APPEND | LOCK_EX) === false) {
                // Fallback if writing to custom log file fails
                $error = error_get_last();
                error_log("Logger Fallback: Failed to write to custom log file '" . self::$logFile . "'. Error: " . ($error['message'] ?? 'Unknown error') . ". Original message: " . $formattedMessage);
            }
        }
    }

    /**
     * Logs an informational message.
     *
     * @param string $message
     * @param array $context
     */
    public static function info(string $message, array $context = []): void {
        self::log('INFO', $message, $context);
    }

    /**
     * Logs an error message.
     *
     * @param string $message
     * @param array $context
     */
    public static function error(string $message, array $context = []): void {
        self::log('ERROR', $message, $context);
    }

    /**
     * Logs a debug message.
     *
     * @param string $message
     * @param array $context
     */
    public static function debug(string $message, array $context = []): void {
        // You might want to disable debug logging in production based on a config/env setting
        // if (defined('APP_DEBUG') && APP_DEBUG === true) {
        self::log('DEBUG', $message, $context);
        // }
    }

    /**
     * Logs a user activity or notable event.
     * This is a specific type of informational log.
     *
     * @param string $message
     * @param array $context
     */
    public static function logActivity(string $message, array $context = []): void {
        // Optionally, prefix activity logs or use a different log file
        // For now, just using a different level label
        $userId = Auth::getUserId() ?? 'Guest';
        $email = Auth::getUserEmail() ?? '-';
        $activityMessage = "UserActivity (User: {$userId}/{$email}): {$message}";
        self::log('ACTIVITY', $activityMessage, $context);
    }
}

// Example: Initialize logger with a specific path if needed (e.g., from config)
// if (defined('LOG_PATH')) {
// Logger::setLogFile(LOG_PATH);
// }

?>
