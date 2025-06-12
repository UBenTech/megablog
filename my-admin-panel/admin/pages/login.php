<?php
$page_title = 'Admin Login';
// No direct require_once for config.php here, header.php handles it.
// Auth class will be loaded by header.php or if not, Auth::method() will autoload if an autoloader is set,
// but for direct calls like Auth::generateCsrfToken(), it's better to ensure it's loaded.
// Let's assume header.php or a central bootstrap file handles core includes.
// For safety, we can add it here if there's no guarantee.
if (file_exists(__DIR__ . '/../../lib/Auth.php')) {
    require_once __DIR__ . '/../../lib/Auth.php';
} else {
    die("Auth class not found. Critical error.");
}
if (file_exists(__DIR__ . '/../../lib/Logger.php')) { // For logging attempts
    require_once __DIR__ . '/../../lib/Logger.php';
}

// Start session if not already started (config.php should handle this)
if (session_status() == PHP_SESSION_NONE) {
    // This is a fallback, config.php should ideally manage session start
    session_start();
}

$admin_base_url = defined('ADMIN_BASE_URL') ? ADMIN_BASE_URL : (rtrim(dirname($_SERVER['SCRIPT_NAME']), '/pages') ?: '/admin');


// If already logged in, redirect to dashboard
if (Auth::isLoggedIn()) {
    header('Location: ' . rtrim($admin_base_url, '/') . '/index.php');
    exit;
}

$error_message = '';
$csrf_token = Auth::generateCsrfToken(); // Generate CSRF token

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !Auth::validateCsrfToken($_POST['csrf_token'])) {
        $error_message = 'Invalid request. Please try again.';
        Logger::logActivity('CSRF token validation failed for login attempt.');
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error_message = 'Email and password are required.';
        } else {
            if (Auth::login($email, $password)) {
                // Login successful, redirect to admin dashboard (via admin index)
                // Logger::logActivity is called within Auth::login()
                $redirect_url = $_SESSION['redirect_url'] ?? rtrim($admin_base_url, '/') . '/index.php';
                unset($_SESSION['redirect_url']); // Clear stored redirect URL
                header('Location: ' . $redirect_url);
                exit;
            } else {
                // Auth::login() already logs failed attempt
                $error_message = 'Login failed. Invalid email or password.';
            }
        }
    }
    // Regenerate CSRF token after a POST attempt to prevent reuse if login page is re-displayed
    $csrf_token = Auth::generateCsrfToken();
}

include __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-lg shadow-lg">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Admin Panel Login
            </h2>
        </div>
        <form class="mt-8 space-y-6" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
            <?php endif; ?>

            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                           placeholder="Email address" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                           placeholder="Password">
                </div>
            </div>

            <!-- <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" name="remember_me" type="checkbox"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                        Remember me
                    </label>
                </div>

                <div class="text-sm">
                    <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">
                        Forgot your password?
                    </a>
                </div>
            </div> -->

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <!-- Heroicon name: solid/lock-closed -->
                        <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                    </span>
                    Sign in
                </button>
            </div>
        </form>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
?>
