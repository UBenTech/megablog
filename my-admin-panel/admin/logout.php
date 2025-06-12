<?php
// my-admin-panel/admin/logout.php

// Ensure configuration is loaded first for session settings and Auth class path.
if (file_exists(__DIR__ . '/../config/config.php')) {
    require_once __DIR__ . '/../config/config.php';
} else {
    die("Critical error: Main configuration file not found.");
}

// Ensure Auth class is available
if (file_exists(__DIR__ . '/../lib/Auth.php')) {
    require_once __DIR__ . '/../lib/Auth.php';
} else {
    die("Critical error: Auth class not found.");
}

// Ensure Logger class is available (Auth::logout() might use it)
if (file_exists(__DIR__ . '/../lib/Logger.php')) {
    require_once __DIR__ . '/../lib/Logger.php';
}


// config.php should start the session. If not, ensure it's started
// because Auth::logout() will operate on session variables and destroy the session.
if (session_status() == PHP_SESSION_NONE) {
    // Fallback, config.php should manage this.
    // session_name(SESSION_COOKIE_NAME); // from config
    // session_set_cookie_params([...]); // from config
    session_start();
}

Auth::logout();

// Determine where to redirect after logout
$admin_base_url = defined('ADMIN_BASE_URL') ? rtrim(ADMIN_BASE_URL, '/') : '/admin';
$login_page_url = $admin_base_url . '/pages/login.php';

// Redirect to the login page
header('Location: ' . $login_page_url);
exit;
?>
