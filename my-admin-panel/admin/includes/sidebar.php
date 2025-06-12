<?php
// admin/includes/sidebar.php

// Ensure ADMIN_BASE_URL is defined (should be by config.php via header.php)
$admin_base_url = defined('ADMIN_BASE_URL') ? rtrim(ADMIN_BASE_URL, '/') : '/admin';

// Get current script name to determine active page
$current_script = basename($_SERVER['SCRIPT_NAME']);

$nav_items = [
    'Dashboard' => [
        'url' => $admin_base_url . '/pages/dashboard.php',
        'icon' => 'home', // Lucide icon name
        'script_name' => 'dashboard.php'
    ],
    'Content' => [ // Group heading
        'is_heading' => true
    ],
    'Posts' => [
        'url' => $admin_base_url . '/pages/posts_list.php',
        'icon' => 'file-text',
        'script_name' => 'posts_list.php' // Also 'posts_edit.php' could activate this
    ],
    'Pages' => [
        'url' => $admin_base_url . '/pages/pages_list.php',
        'icon' => 'layout-template',
        'script_name' => 'pages_list.php'
    ],
    'Media Library' => [
        'url' => $admin_base_url . '/pages/media_library.php',
        'icon' => 'image',
        'script_name' => 'media_library.php'
    ],
    'Comments' => [
        'url' => $admin_base_url . '/pages/comments_list.php',
        'icon' => 'message-square',
        'script_name' => 'comments_list.php'
    ],
    'Site Management' => [
        'is_heading' => true
    ],
    'Users' => [
        'url' => $admin_base_url . '/pages/users_list.php',
        'icon' => 'users',
        'script_name' => 'users_list.php' // Also 'users_edit.php'
    ],
    'Services' => [
        'url' => $admin_base_url . '/pages/services_list.php',
        'icon' => 'briefcase',
        'script_name' => 'services_list.php'
    ],
    'Service Requests' => [
        'url' => $admin_base_url . '/pages/service_requests.php',
        'icon' => 'inbox',
        'script_name' => 'service_requests.php'
    ],
     'Support Tickets' => [
        'url' => $admin_base_url . '/pages/tickets_list.php',
        'icon' => 'life-buoy',
        'script_name' => 'tickets_list.php'
    ],
    'Settings' => [
        'url' => $admin_base_url . '/pages/settings.php',
        'icon' => 'settings',
        'script_name' => 'settings.php'
    ],
    'Reports' => [
        'url' => $admin_base_url . '/pages/reports.php',
        'icon' => 'bar-chart-2',
        'script_name' => 'reports.php'
    ],
    'System' => [
        'is_heading' => true
    ],
    'Logout' => [
        'url' => $admin_base_url . '/logout.php',
        'icon' => 'log-out',
        'script_name' => 'logout.php' // Not really a page, but for consistency
    ]
];

// Function to check if a nav item should be active
// Can be expanded, e.g. if users_edit.php should make 'Users' active
function isAdminNavActive(string $nav_item_script_name, string $current_script_name): bool {
    if ($nav_item_script_name === $current_script_name) {
        return true;
    }
    // Add specific parent-child active states if needed
    if ($current_script_name === 'users_edit.php' && $nav_item_script_name === 'users_list.php') {
        return true;
    }
    if ($current_script_name === 'posts_edit.php' && $nav_item_script_name === 'posts_list.php') {
        return true;
    }
    // Add more rules if necessary
    return false;
}

?>
<aside class="w-64 bg-gray-900 text-gray-300 min-h-screen fixed top-0 left-0 pt-[calc(3.5rem+1px)] shadow-lg print:hidden">
    <!-- Adjust pt (padding-top) to match header height if header is fixed and has specific height -->
    <nav class="py-4 px-2">
        <ul>
            <?php foreach ($nav_items as $name => $item): ?>
                <?php if (isset($item['is_heading']) && $item['is_heading']): ?>
                    <li class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-3">
                        <?php echo htmlspecialchars($name); ?>
                    </li>
                <?php else: ?>
                    <?php $is_active = isAdminNavActive($item['script_name'], $current_script); ?>
                    <li>
                        <a href="<?php echo htmlspecialchars($item['url']); ?>"
                           class="flex items-center space-x-3 px-3 py-2.5 rounded-md text-sm font-medium transition-colors duration-150 ease-in-out
                                  <?php echo $is_active
                                        ? 'bg-indigo-600 text-white shadow-md'
                                        : 'hover:bg-gray-700 hover:text-white'; ?>">
                            <i data-lucide="<?php echo htmlspecialchars($item['icon']); ?>" class="w-5 h-5 opacity-80"></i>
                            <span><?php echo htmlspecialchars($name); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </nav>
</aside>
<script>
    // Ensure Lucide icons are rendered in the sidebar if not already handled globally after AJAX or dynamic loads.
    // Header should call lucide.createIcons() on DOMContentLoaded. This is a fallback if needed.
    // document.addEventListener('DOMContentLoaded', () => {
    // if (typeof lucide !== 'undefined') {
    // lucide.createIcons();
    // }
    // });
</script>
