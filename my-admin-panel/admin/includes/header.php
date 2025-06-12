<?php
// admin/includes/header.php

// Ensure config is loaded for base URL and site name, start session
if (file_exists(__DIR__ . '/../../config/config.php')) {
    require_once __DIR__ . '/../../config/config.php';
} else {
    die("Configuration file not found. Critical error.");
}

// Ensure Auth class is available for any auth-related info if needed in header
if (file_exists(__DIR__ . '/../../lib/Auth.php')) {
    require_once __DIR__ . '/../../lib/Auth.php';
} else {
    die("Auth class not found. Critical error.");
}

$page_title = $page_title ?? (defined('SITE_NAME') ? SITE_NAME . ' Admin' : 'Admin Panel');
$admin_base_url = defined('ADMIN_BASE_URL') ? ADMIN_BASE_URL : '/admin'; // Fallback

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="<?php echo rtrim($admin_base_url, '/'); ?>/assets/css/admin.css?v=<?php echo time(); ?>">
    <!-- In development, time() prevents caching. For production, use a file hash or version number. -->
    <script src="<?php echo rtrim($admin_base_url, '/'); ?>/assets/js/admin.js?v=<?php echo time(); ?>" defer></script>
</head>
<body class="bg-gray-100">
    <header class="bg-gray-800 text-white p-4">
        <div class="container mx-auto">
            <h1 class="text-xl font-semibold"><?php echo htmlspecialchars(defined('SITE_NAME') ? SITE_NAME . ' Admin Panel' : 'Admin Panel'); ?></h1>
            <!-- Basic navigation can go here later -->
        </div>
    </header>
    <main class="container mx-auto p-4">
        <!-- Page content will start here -->
