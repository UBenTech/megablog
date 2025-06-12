<?php

class Auth {
    private static ?PDO $db = null;

    private static function db(): PDO {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    /**
     * Attempts to log in a user.
     *
     * @param string $email
     * @param string $password
     * @return bool True on successful login, false otherwise.
     */
    public static function login(string $email, string $password): bool {
        // In a real application, fetch user by email
        // For now, we'll use a placeholder. Replace with actual database query.
        // $user = Database::fetchOne("SELECT * FROM users WHERE email = :email AND status = 'active'", [':email' => $email]);

        // Placeholder user for demonstration.
        // Replace this with actual database user fetching and password verification.
        $demo_user_email = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'admin@example.com';
        $demo_user_pass_hash = password_hash('password123', PASSWORD_DEFAULT); // Example password

        if ($email === $demo_user_email && password_verify($password, $demo_user_pass_hash)) {
            // Password matches
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = 1; // Placeholder user ID
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = 'administrator'; // Placeholder role
            $_SESSION['logged_in_at'] = time();

            // Set a login token for "remember me" functionality if needed
            // self::setLoginToken($user['id']);

            Logger::logActivity('User logged in: ' . $email);
            return true;
        }

        Logger::logActivity('Failed login attempt for: ' . $email);
        return false;
    }

    /**
     * Logs out the current user.
     */
    public static function logout(): void {
        $email = $_SESSION['user_email'] ?? 'Unknown user';
        Logger::logActivity('User logged out: ' . $email);

        // Unset all session variables
        $_SESSION = [];

        // Destroy the session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Checks if a user is currently logged in.
     *
     * @return bool True if logged in, false otherwise.
     */
    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    /**
     * Gets the current logged-in user's ID.
     *
     * @return int|null User ID or null if not logged in.
     */
    public static function getUserId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Gets the current logged-in user's email.
     *
     * @return string|null User email or null if not logged in.
     */
    public static function getUserEmail(): ?string {
        return $_SESSION['user_email'] ?? null;
    }

    /**
     * Gets the current logged-in user's role.
     *
     * @return string|null User role or null if not logged in.
     */
    public static function getUserRole(): ?string {
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Checks if the current user has a specific role.
     *
     * @param string $role
     * @return bool
     */
    public static function hasRole(string $role): bool {
        $userRole = self::getUserRole();
        if ($userRole === null) {
            return false;
        }
        // Simple role check, can be expanded for role hierarchy
        return strtolower($userRole) === strtolower($role);
    }

    /**
     * Checks if the current user has a specific permission.
     * This is a placeholder. A real implementation would involve a permissions table
     * or a role-based permission system.
     *
     * @param string $permission
     * @return bool
     */
    public static function hasPermission(string $permission): bool {
        if (!self::isLoggedIn()) {
            return false;
        }

        $role = self::getUserRole();
        // Example: Administrators have all permissions
        if ($role === 'administrator') {
            return true;
        }

        // Example: Editors can edit posts
        if ($role === 'editor' && $permission === 'edit_posts') {
            return true;
        }

        // Add more role/permission checks as needed
        // $permissions = self::getRolePermissions($role); // Fetch from DB
        // return in_array($permission, $permissions);

        // Default to false if no specific permission is granted
        return false;
    }

    /**
     * Redirects to the login page if the user is not logged in.
     * Optionally, can check for a specific role or permission.
     *
     * @param string|null $requiredRole
     * @param string|null $requiredPermission
     */
    public static function requireLogin(string $requiredRole = null, string $requiredPermission = null): void {
        if (!self::isLoggedIn()) {
            // Store the intended URL to redirect back after login
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . ADMIN_BASE_URL . '/pages/login.php');
            exit;
        }

        if ($requiredRole !== null && !self::hasRole($requiredRole)) {
            // User does not have the required role
            // You might want to redirect to an 'unauthorized' page or show an error
            error_log("Access Denied: User ID " . self::getUserId() . " does not have role " . $requiredRole . " for " . $_SERVER['REQUEST_URI']);
            http_response_code(403);
            // For simplicity, redirecting to dashboard or showing a generic error.
            // In a real app, create an 'access_denied.php' page.
            echo "Access Denied: You do not have the required role.";
            // header('Location: ' . ADMIN_BASE_URL . '/pages/access_denied.php?error=role');
            exit;
        }

        if ($requiredPermission !== null && !self::hasPermission($requiredPermission)) {
            // User does not have the required permission
            error_log("Access Denied: User ID " . self::getUserId() . " does not have permission " . $requiredPermission . " for " . $_SERVER['REQUEST_URI']);
            http_response_code(403);
            echo "Access Denied: You do not have the required permission.";
            // header('Location: ' . ADMIN_BASE_URL . '/pages/access_denied.php?error=permission');
            exit;
        }
    }

    /**
     * Generates a CSRF token and stores it in the session.
     * @return string The generated CSRF token.
     */
    public static function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validates a CSRF token.
     * @param string $token The token from the form/request.
     * @return bool True if valid, false otherwise.
     */
    public static function validateCsrfToken(string $token): bool {
        if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            // Token is valid, unset it to prevent reuse (optional, depends on strategy)
            // unset($_SESSION['csrf_token']);
            return true;
        }
        error_log("CSRF token validation failed. Provided token: " . $token . " Session token: " . ($_SESSION['csrf_token'] ?? 'Not Set'));
        return false;
    }

    // Placeholder for password reset functionality
    public static function requestPasswordReset(string $email): bool {
        // 1. Check if email exists
        // 2. Generate a unique, expiring token
        // 3. Store token with user ID and expiry in a password_resets table
        // 4. Send email with reset link (using Mailer class)
        // Logger::logActivity('Password reset requested for: ' . $email);
        return true; // Placeholder
    }

    public static function verifyPasswordResetToken(string $token): bool {
        // 1. Check if token exists in password_resets table and is not expired
        // 2. If valid, store user ID in session for password update step
        return true; // Placeholder
    }

    public static function updateUserPassword(int $userId, string $newPassword): bool {
        // 1. Hash the new password
        // 2. Update user's password in the database
        // 3. Invalidate password reset token
        // Logger::logActivity('Password updated for user ID: ' . $userId);
        return true; // Placeholder
    }
}
?>
