<?php
// my-admin-panel/admin/index.php

// Ensure configuration is loaded first.
if (file_exists(__DIR__ . '/../config/config.php')) {
    require_once __DIR__ . '/../config/config.php';
} else {
    // If config.php is critical and not found, you might want to die or handle error
    die("Critical error: Main configuration file not found.");
}

// Ensure Auth class is available
if (file_exists(__DIR__ . '/../lib/Auth.php')) {
    require_once __DIR__ . '/../lib/Auth.php';
} else {
    die("Critical error: Auth class not found.");
}

// config.php should start the session. If not, ensure it's started.
if (session_status() == PHP_SESSION_NONE) {
    // This is a fallback, config.php should ideally manage session start
    // Consider if your config.php reliably starts the session.
    // session_name(SESSION_COOKIE_NAME); // From config.php
    // session_set_cookie_params(['lifetime' => SESSION_DURATION, ...]); // From config.php
    session_start();
}


$admin_base_url = defined('ADMIN_BASE_URL') ? rtrim(ADMIN_BASE_URL, '/') : '/admin';

if (Auth::isLoggedIn()) {
    // User is logged in, redirect to the dashboard
    header('Location: ' . $admin_base_url . '/pages/dashboard.php');
    exit;
} else {
    // User is not logged in, redirect to the login page
    header('Location: ' . $admin_base_url . '/pages/login.php');
    exit;
}

?>
