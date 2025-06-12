<?php
// admin/includes/header.php

if (file_exists(__DIR__ . '/../../config/config.php')) {
    require_once __DIR__ . '/../../config/config.php';
} else {
    die("Configuration file not found. Critical error.");
}

if (file_exists(__DIR__ . '/../../lib/Auth.php')) {
    require_once __DIR__ . '/../../lib/Auth.php';
} else {
    die("Auth class not found. Critical error.");
}

$page_title = $page_title ?? (defined('SITE_NAME') ? SITE_NAME . ' Admin' : 'Admin Panel');
$admin_base_url = defined('ADMIN_BASE_URL') ? ADMIN_BASE_URL : '/admin'; // Fallback
$site_name_display = defined('SITE_NAME') ? SITE_NAME . ' Admin Panel' : 'Admin Panel';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="<?php echo rtrim($admin_base_url, '/'); ?>/assets/css/admin.css?v=<?php echo time(); ?>">
    <script src="https://unpkg.com/lucide@latest"></script> <!-- Load Lucide icons -->
    <script src="<?php echo rtrim($admin_base_url, '/'); ?>/assets/js/admin.js?v=<?php echo time(); ?>" defer></script>
    <style>
        /* Ensure content is not hidden under fixed header/sidebar */
        /* body { padding-top: 3.5rem; } */ /* If header is fixed */
        /* .content-wrapper { margin-left: 16rem; } */ /* If sidebar is fixed w-64 */
    </style>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

    <header class="bg-gray-800 text-white h-14 flex-shrink-0 fixed top-0 left-0 right-0 z-50 shadow-md print:hidden">
        <div class="container mx-auto px-4 h-full flex items-center">
            <!-- Could add a mobile menu toggle here later -->
            <h1 class="text-xl font-semibold"><?php echo htmlspecialchars($site_name_display); ?></h1>
        </div>
    </header>

    <div class="flex flex-1 pt-14"> <!-- pt-14 to offset for fixed header height -->
        <?php include __DIR__ . '/sidebar.php'; // Include the sidebar ?>

        <!-- Main content area -->
        <main class="flex-1 p-6 lg:p-8 ml-0 md:ml-64 transition-all duration-300 ease-in-out">
            <!-- ml-64 for sidebar width on md screens and up. Sidebar should hide on smaller screens or overlay. -->
            <!-- The sidebar created is always visible for now. Responsive handling can be added later. -->
            <!-- Page content will start here -->
