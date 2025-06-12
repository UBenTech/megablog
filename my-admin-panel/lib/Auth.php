<?php

// Ensure Database and Logger are available
if (!class_exists('Database')) {
    if (file_exists(__DIR__ . '/Database.php')) {
        require_once __DIR__ . '/Database.php';
    } else {
        die('Auth Error: Database class not found.');
    }
}
if (!class_exists('Logger')) {
    if (file_exists(__DIR__ . '/Logger.php')) {
        require_once __DIR__ . '/Logger.php';
    } else {
        die('Auth Error: Logger class not found.');
    }
}


class Auth {
    // db() method remains the same
    private static ?PDO $db = null;

    private static function db(): PDO {
        if (self::$db === null) {
            // Ensure config is loaded if using Database::getInstance() for the first time
            // It's better if config.php is always loaded by the entry script (e.g. admin/index.php)
             if (!defined('DB_HOST') && file_exists(__DIR__ . '/../config/config.php')) {
                 require_once __DIR__ . '/../config/config.php';
             }
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    /**
     * Attempts to log in a user using database credentials.
     *
     * @param string $emailOrUsername Either email or username.
     * @param string $password
     * @return bool True on successful login, false otherwise.
     */
    public static function login(string $emailOrUsername, string $password): bool {
        $sql = "SELECT id, username, email, password_hash, role_id, status FROM users
                WHERE (email = :identifier OR username = :identifier) AND status = 'active'";

        $user = Database::fetchOne($sql, [':identifier' => $emailOrUsername]);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Password matches & user is active

            // Check for lockout (future enhancement, for now, assume not locked)
            // if ($user['status'] === 'locked' || ($user['lockout_until'] && new DateTime() < new DateTime($user['lockout_until']))) {
            //     Logger::logActivity('Login attempt for locked account: ' . $emailOrUsername);
            //     return false;
            // }

            session_regenerate_id(true); // Prevent session fixation

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['user_username'] = $user['username']; // Store username
            $_SESSION['user_email'] = $user['email'];

            // Fetch role name (assuming roles table and user.role_id)
            $roleInfo = null;
            if ($user['role_id']) {
                 $roleInfo = Database::fetchOne("SELECT name FROM roles WHERE id = :role_id", [':role_id' => $user['role_id']]);
            }
            $_SESSION['user_role'] = $roleInfo ? $roleInfo['name'] : 'default'; // Fallback role

            $_SESSION['logged_in_at'] = time();

            // Update last_login_at and reset failed attempts
            Database::execute("UPDATE users SET last_login_at = CURRENT_TIMESTAMP, failed_login_attempts = 0, lockout_until = NULL WHERE id = :id", [':id' => $user['id']]);

            Logger::logActivity('User logged in: ' . $user['username'] . ' (Email: ' . $user['email'] . ')');
            return true;
        } else {
            // Log failed attempt and handle lockout (future enhancement)
            if ($user) { // User found, but password mismatch or inactive
                 Database::execute("UPDATE users SET failed_login_attempts = failed_login_attempts + 1 WHERE id = :id", [':id' => $user['id']]);
                 // Add lockout logic here if failed_login_attempts exceeds a threshold
            }
            Logger::logActivity('Failed login attempt for: ' . $emailOrUsername . ($user ? ' (User found, pass mismatch or inactive)' : ' (User not found)'));
            return false;
        }
    }

    // logout(), isLoggedIn(), getUserId(), getUserEmail(), getUserRole() remain the same for now
    // ... (copy existing methods for logout, isLoggedIn, getUserId, getUserEmail, getUserRole, hasRole, hasPermission, requireLogin, CSRF methods) ...

    /**
     * Logs out the current user.
     */
    public static function logout(): void {
        $email = $_SESSION['user_email'] ?? $_SESSION['user_username'] ?? 'Unknown user';
        Logger::logActivity('User logged out: ' . $email);

        $_SESSION = []; // Unset all session variables

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    public static function getUserId(): ?int {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function getUserUsername(): ?string {
        return $_SESSION['user_username'] ?? null;
    }

    public static function getUserEmail(): ?string {
        return $_SESSION['user_email'] ?? null;
    }

    public static function getUserRole(): ?string {
        return $_SESSION['user_role'] ?? null; // This now stores role name
    }

    public static function hasRole(string $role): bool {
        $userRole = self::getUserRole();
        if ($userRole === null) return false;
        return strtolower($userRole) === strtolower($role);
    }

    // hasPermission can be more sophisticated later by checking `role_permissions` table
    public static function hasPermission(string $permissionName): bool {
        if (!self::isLoggedIn()) return false;

        $roleName = self::getUserRole();
        if ($roleName === 'administrator') return true; // Admin has all permissions

        // More detailed permission check
        // $role = Database::fetchOne("SELECT id FROM roles WHERE name = :name", [':name' => $roleName]);
        // if (!$role) return false;
        // $perm = Database::fetchOne("SELECT id FROM permissions WHERE name = :name", [':name' => $permissionName]);
        // if (!$perm) return false;
        // $has = Database::fetchOne("SELECT 1 FROM role_permissions WHERE role_id = :role_id AND permission_id = :perm_id", [':role_id' => $role['id'], ':perm_id' => $perm['id']]);
        // return (bool)$has;

        // Placeholder for now, matching original simplicity after admin check
        if ($roleName === 'editor' && $permissionName === 'edit_posts') return true;

        return false;
    }

    public static function requireLogin(string $requiredRole = null, string $requiredPermission = null): void {
        if (!self::isLoggedIn()) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . rtrim(ADMIN_BASE_URL, '/') . '/pages/login.php');
            exit;
        }
        if ($requiredRole !== null && !self::hasRole($requiredRole)) {
            http_response_code(403);
            Logger::logActivity("Access Denied (Role): User " . self::getUserUsername() . " to " . $_SERVER['REQUEST_URI']);
            // In a real app, redirect to an 'access_denied.php' page.
            die("Access Denied: You do not have the required role ({$requiredRole}). Your role: " . self::getUserRole());
        }
        if ($requiredPermission !== null && !self::hasPermission($requiredPermission)) {
            http_response_code(403);
            Logger::logActivity("Access Denied (Permission): User " . self::getUserUsername() . " to " . $_SERVER['REQUEST_URI']);
            die("Access Denied: You do not have the required permission ({$requiredPermission}).");
        }
    }

    public static function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCsrfToken(string $token): bool {
        if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
            return true;
        }
        Logger::logActivity("CSRF token validation failed. Provided: " . $token . " Session: " . ($_SESSION['csrf_token'] ?? 'Not Set'));
        return false;
    }


    /**
     * Creates a new user in the database.
     *
     * @param string $username
     * @param string $email
     * @param string $password Plain text password
     * @param int|null $roleId ID of the role for the user
     * @param string $status User status (e.g., 'active', 'pending')
     * @return int|false The ID of the newly created user, or false on failure.
     */
    public static function createUser(string $username, string $email, string $password, ?int $roleId = null, string $status = 'active'): int|false {
        // Validate input (e.g., password strength, email format, username availability)
        if (empty($username) || empty($email) || empty($password)) {
            Logger::logActivity("User creation failed: Missing required fields.");
            return false; // Or throw exception
        }
        if (self::fetchUserByEmail($email)) {
            Logger::logActivity("User creation failed: Email '{$email}' already exists.");
            return false; // Or throw specific exception
        }
        if (self::fetchUserByUsername($username)) {
            Logger::logActivity("User creation failed: Username '{$username}' already exists."); // Corrected Logger call
            return false; // Or throw specific exception
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        if (!$password_hash) {
            Logger::logActivity("User creation failed: Password hashing failed for '{$username}'.");
            return false; // Should not happen with modern PHP
        }

        $sql = "INSERT INTO users (username, email, password_hash, role_id, status, created_at, updated_at)
                VALUES (:username, :email, :password_hash, :role_id, :status, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";

        $params = [
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $password_hash,
            ':role_id' => $roleId,
            ':status' => $status
        ];

        if (Database::execute($sql, $params)) {
            $newUserId = Database::lastInsertId();
            Logger::logActivity("User created successfully: {$username} (ID: {$newUserId})");
            return (int)$newUserId;
        } else {
            Logger::logActivity("User creation failed: Database error for '{$username}'.");
            return false;
        }
    }

    /**
     * Updates an existing user's details.
     *
     * @param int $userId
     * @param array $data Associative array of data to update (e.g., ['username' => 'new_user', 'email' => 'new@example.com'])
     *                    To update password, include 'password' => 'new_plain_password'.
     * @return bool True on success, false on failure.
     */
    public static function updateUser(int $userId, array $data): bool {
        if (empty($data)) {
            return false;
        }

        $fields = [];
        $params = [':id' => $userId];

        if (isset($data['username'])) {
            // Check if username is being changed and if the new one is unique (excluding current user)
            $existingUser = self::fetchUserByUsername($data['username']);
            if ($existingUser && $existingUser['id'] !== $userId) {
                 Logger::logActivity("User update failed for ID {$userId}: Username '{$data['username']}' already taken.");
                 return false; // Or throw specific exception
            }
            $fields[] = "username = :username";
            $params[':username'] = $data['username'];
        }
        if (isset($data['email'])) {
            // Check if email is being changed and if new one is unique
            $existingUser = self::fetchUserByEmail($data['email']);
            if ($existingUser && $existingUser['id'] !== $userId) {
                 Logger::logActivity("User update failed for ID {$userId}: Email '{$data['email']}' already taken.");
                 return false; // Or throw specific exception
            }
            $fields[] = "email = :email";
            $params[':email'] = $data['email'];
        }
        if (!empty($data['password'])) { // Check for non-empty password to update
            $fields[] = "password_hash = :password_hash";
            $params[':password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (isset($data['role_id'])) {
            $fields[] = "role_id = :role_id";
            $params[':role_id'] = $data['role_id'] === '' ? null : (int)$data['role_id']; // Allow setting role to null
        }
        if (isset($data['status'])) {
            $fields[] = "status = :status";
            $params[':status'] = $data['status'];
        }
        // Add other updatable fields like first_name, last_name etc.
        if (isset($data['first_name'])) {
            $fields[] = "first_name = :first_name";
            $params[':first_name'] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $fields[] = "last_name = :last_name";
            $params[':last_name'] = $data['last_name'];
        }


        if (empty($fields)) {
            return true; // Nothing to update
        }

        $fields[] = "updated_at = CURRENT_TIMESTAMP";
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";

        if (Database::execute($sql, $params)) {
            Logger::logActivity("User updated successfully: ID {$userId}");
            return true;
        } else {
            Logger::logActivity("User update failed or no changes made: ID {$userId}");
            // Check if it failed or simply no rows were affected because data was the same
            // For simplicity, returning false if execute didn't return a truthy value (e.g. >0 affected rows for some drivers)
            // Database::execute returns rowCount, so if 0, it means no change or error.
            // To be more precise, one might need to check for PDO errors specifically.
            return false;
        }
    }

    /**
     * Fetches a user by their ID.
     * @param int $userId
     * @return array|false User data as an associative array, or false if not found.
     */
    public static function fetchUserById(int $userId): array|false {
        return Database::fetchOne("SELECT * FROM users WHERE id = :id", [':id' => $userId]);
    }

    /**
     * Fetches a user by their email.
     * @param string $email
     * @return array|false User data as an associative array, or false if not found.
     */
    public static function fetchUserByEmail(string $email): array|false {
        return Database::fetchOne("SELECT * FROM users WHERE email = :email", [':email' => $email]);
    }

    /**
     * Fetches a user by their username.
     * @param string $username
     * @return array|false User data as an associative array, or false if not found.
     */
    public static function fetchUserByUsername(string $username): array|false {
        return Database::fetchOne("SELECT * FROM users WHERE username = :username", [':username' => $username]);
    }

    /**
     * Deletes a user.
     * @param int $userId
     * @return bool True on success, false on failure.
     */
    public static function deleteUser(int $userId): bool {
        // Optional: Add checks, e.g., cannot delete own account or last admin.
        if ($userId === self::getUserId()) {
            Logger::logActivity("User deletion failed: Attempt to delete own account (ID: {$userId}).");
            return false; // Cannot delete self
        }

        if (Database::execute("DELETE FROM users WHERE id = :id", [':id' => $userId])) {
            Logger::logActivity("User deleted successfully: ID {$userId}");
            return true;
        }
        Logger::logActivity("User deletion failed for ID: {$userId}.");
        return false;
    }

    // Placeholder for password reset functionality (remains the same)
    // ... (copy existing password reset methods) ...
    public static function requestPasswordReset(string $email): bool {
        $user = self::fetchUserByEmail($email);
        if (!$user) {
            Logger::logActivity("Password reset request for non-existent email: " . $email);
            return false; // Or true to not reveal email existence
        }

        // Generate a unique, expiring token
        $token = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $token); // Hash the token before storing
        $expires_at = date('Y-m-d H:i:s', time() + 3600); // Token expires in 1 hour

        // Store token hash with user ID and expiry in password_resets table
        $sql = "INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (:user_id, :token_hash, :expires_at)";
        if (Database::execute($sql, [':user_id' => $user['id'], ':token_hash' => $token_hash, ':expires_at' => $expires_at])) {
            // Send email with reset link (using Mailer class and the plain token)
            if (class_exists('Mailer') && method_exists('Mailer', 'sendPasswordResetEmail')) {
                Mailer::sendPasswordResetEmail($user['email'], $token); // Send the plain token, not the hash
            }
            Logger::logActivity('Password reset token generated and email sent for: ' . $email);
            return true;
        }
        Logger::logActivity('Failed to store password reset token for: ' . $email);
        return false;
    }

    public static function verifyPasswordResetToken(string $token): ?int {
        $token_hash = hash('sha256', $token);
        $sql = "SELECT user_id, expires_at FROM password_resets WHERE token_hash = :token_hash AND used = 0"; // Added used flag
        $reset_data = Database::fetchOne($sql, [':token_hash' => $token_hash]);

        if ($reset_data && time() < strtotime($reset_data['expires_at'])) {
            return (int)$reset_data['user_id']; // Token is valid
        }
        if ($reset_data) { // Token found but expired or used
            self::invalidatePasswordResetToken($token_hash); // Mark as used/expired if found
        }
        Logger::logActivity('Password reset token verification failed or token expired for token hash: ' . $token_hash);
        return null;
    }

    public static function invalidatePasswordResetToken(string $token_hash): void {
        // Mark token as used (or delete it)
        // Database::execute("DELETE FROM password_resets WHERE token_hash = :token_hash", [':token_hash' => $token_hash]);
        // Or, if you have a 'used' column:
         Database::execute("UPDATE password_resets SET used = 1 WHERE token_hash = :token_hash", [':token_hash' => $token_hash]);
    }


    public static function updateUserPassword(int $userId, string $newPassword): bool {
        $password_hash = password_hash($newPassword, PASSWORD_DEFAULT);
        if (!$password_hash) return false;

        $sql = "UPDATE users SET password_hash = :password_hash, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
        if (Database::execute($sql, [':password_hash' => $password_hash, ':id' => $userId])) {
            // Optionally, invalidate all password reset tokens for this user
            // Database::execute("DELETE FROM password_resets WHERE user_id = :user_id", [':user_id' => $userId]);
            Logger::logActivity('Password updated for user ID: ' . $userId);
            return true;
        }
        return false;
    }
}
?>
