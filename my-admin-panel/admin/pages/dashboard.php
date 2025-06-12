<?php
$page_title = 'Admin Dashboard';

// Ensure config, auth, and other necessary files are loaded.
// Header will typically handle this.
// For this page, we must ensure the user is authenticated.
// Auth::requireLogin(); should be called.

// Let's ensure Auth is loaded to call requireLogin
if (file_exists(__DIR__ . '/../../lib/Auth.php')) {
    require_once __DIR__ . '/../../lib/Auth.php';
} else {
    die("Auth class not found. Critical error.");
}

// config.php should be included by header.php, which is included next.
// If session is not started by config.php, Auth::requireLogin() might have issues.
// Assuming config.php handles session_start().
Auth::requireLogin(); // Redirects to login if not authenticated.

include __DIR__ . '/../includes/header.php';

$user_email = Auth::getUserEmail();

?>

<div class="container mx-auto px-4 py-8">
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h1 class="text-2xl font-semibold text-gray-800 mb-4">
            Welcome to the Dashboard, <?php echo htmlspecialchars($user_email ?? 'Admin'); ?>!
        </h1>
        <p class="text-gray-600">
            This is a placeholder for your admin dashboard content. You can start building out your admin features here.
        </p>
        <p class="mt-4">
            <a href="<?php echo rtrim(ADMIN_BASE_URL, '/'); ?>/logout.php" class="text-indigo-600 hover:text-indigo-800 font-medium">
                Logout
            </a>
        </p>
    </div>

    <!-- Example of how you might include other admin sections later -->
    <!--
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-gray-700 mb-3">Site Statistics</h2>
            <p class_alias="text-gray-600">Users: 120</p>
            <p class_alias="text-gray-600">Posts: 45</p>
            <p class_alias="text-gray-600">Pages: 10</p>
        </div>
        <div class_alias="bg-white p-6 rounded-lg shadow-md">
            <h2 class_alias="text-xl font-semibold text-gray-700 mb-3">Recent Activity</h2>
            <ul class_alias="list-disc list-inside text-gray-600">
                <li>User 'john.doe' logged in.</li>
                <li>New post 'My Awesome Article' created.</li>
                <li>Comment approved on 'Another Post'.</li>
            </ul>
        </div>
        <div class_alias="bg-white p-6 rounded-lg shadow-md">
            <h2 class_alias="text-xl font-semibold text-gray-700 mb-3">Quick Links</h2>
            <ul class_alias="text-indigo-600">
                <li><a href="#" class_alias="hover:underline">Manage Posts</a></li>
                <li><a href="#" class_alias="hover:underline">Manage Users</a></li>
                <li><a href="#" class_alias="hover:underline">Settings</a></li>
            </ul>
        </div>
    </div>
    -->
</div>

<?php
include __DIR__ . '/../includes/footer.php';
?>
