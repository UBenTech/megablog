<?php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    // .env file not found, relying on environment variables or defaults
    // You might want to log this or handle it more gracefully
    error_log("Notice: .env file not found, relying on environment variables or defaults. Error: " . $e->getMessage());
}

// Database connection constants
defined('DB_HOST') or define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
defined('DB_USER') or define('DB_USER', $_ENV['DB_USER'] ?? 'u662439561_main5_');
defined('DB_PASS') or define('DB_PASS', $_ENV['DB_PASS'] ?? 'XpGmn&9a');
defined('DB_NAME') or define('DB_NAME', $_ENV['DB_NAME'] ?? 'u662439561_Main5_');

// Site settings
defined('SITE_NAME') or define('SITE_NAME', $_ENV['SITE_NAME'] ?? 'My Admin Panel');
defined('ADMIN_EMAIL') or define('ADMIN_EMAIL', $_ENV['ADMIN_EMAIL'] ?? 'admin@example.com');

// Session settings
defined('SESSION_COOKIE_NAME') or define('SESSION_COOKIE_NAME', 'admin_session');
defined('SESSION_DURATION') or define('SESSION_DURATION', 60 * 60 * 24 * 7); // 7 days

// Error reporting (adjust for production)
ini_set('display_errors', 1); // Should be 0 in production
ini_set('display_startup_errors', 1); // Should be 0 in production
error_reporting(E_ALL);

// Default timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// Base URL (useful for links and redirects)
// Attempt to detect HTTPS
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
defined('BASE_URL') or define('BASE_URL', $_ENV['BASE_URL'] ?? rtrim($protocol . $host, '/'));
defined('ADMIN_BASE_URL') or define('ADMIN_BASE_URL', BASE_URL . '/admin');


// CSRF Token secret key (should be a long random string, set in .env)
defined('CSRF_SECRET') or define('CSRF_SECRET', $_ENV['CSRF_SECRET'] ?? 'change_this_to_a_very_strong_random_string');

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_name(SESSION_COOKIE_NAME);
    session_set_cookie_params([
        'lifetime' => SESSION_DURATION,
        'path' => '/',
        'samesite' => 'Lax' // Or 'Strict'
        // 'secure' => true, // Uncomment if using HTTPS
        // 'httponly' => true, // Recommended
    ]);
    session_start();
}

?>
