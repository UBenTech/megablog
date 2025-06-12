<?php

class Mailer {
    /**
     * Sends an email.
     *
     * @param string $to Recipient email address.
     * @param string $subject Email subject.
     * @param string $message Email body (HTML or plain text).
     * @param string|null $from Sender email address. If null, uses ADMIN_EMAIL.
     * @param string|null $fromName Sender name. If null, uses SITE_NAME.
     * @return bool True if email was accepted for delivery, false otherwise.
     */
    public static function send(string $to, string $subject, string $message, string $from = null, string $fromName = null): bool {
        if ($from === null) {
            $from = defined('ADMIN_EMAIL') ? ADMIN_EMAIL : 'noreply@example.com';
        }
        if ($fromName === null) {
            $fromName = defined('SITE_NAME') ? SITE_NAME : 'My Admin Panel';
        }

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . $fromName . ' <' . $from . '>' . "\r\n";
        // $headers .= 'Cc: myboss@example.com' . "\r\n"; // Optional CC
        // $headers .= 'Bcc: mysecretboss@example.com' . "\r\n"; // Optional BCC

        // Basic error logging
        if (mail($to, $subject, $message, $headers)) {
            Logger::logActivity("Email sent to {$to} with subject '{$subject}'.");
            return true;
        } else {
            $error = error_get_last();
            Logger::logActivity("Failed to send email to {$to}. Subject: '{$subject}'. Error: " . ($error['message'] ?? 'Unknown mail error'));
            return false;
        }
    }

    /**
     * Sends a password reset email.
     * (This is a specific use case, you might have more such methods)
     *
     * @param string $userEmail The email address of the user.
     * @param string $resetToken The password reset token.
     * @return bool
     */
    public static function sendPasswordResetEmail(string $userEmail, string $resetToken): bool {
        $subject = SITE_NAME . " - Password Reset Request";
        $resetLink = ADMIN_BASE_URL . '/pages/reset_password.php?token=' . urlencode($resetToken); // Assuming a reset_password.php page

        $message = "<p>Hello,</p>";
        $message .= "<p>You requested a password reset for your account on " . SITE_NAME . ".</p>";
        $message .= "<p>Please click the link below to reset your password:</p>";
        $message .= "<p><a href='{$resetLink}'>{$resetLink}</a></p>";
        $message .= "<p>If you did not request this, please ignore this email.</p>";
        $message .= "<p>This link will expire in 1 hour (or as configured).</p>"; // Inform about token expiry
        $message .= "<br><p>Thanks,<br>The " . SITE_NAME . " Team</p>";

        return self::send($userEmail, $subject, $message);
    }

    /**
     * Sends a welcome email to a new user.
     *
     * @param string $userEmail
     * @param string $userName
     * @return bool
     */
    public static function sendWelcomeEmail(string $userEmail, string $userName): bool {
        $subject = "Welcome to " . SITE_NAME . "!";
        $loginLink = ADMIN_BASE_URL . '/pages/login.php';

        $message = "<p>Hello {$userName},</p>";
        $message .= "<p>Welcome to " . SITE_NAME . "! Your account has been created.</p>";
        // You might include temporary password or instructions to set one if applicable
        $message .= "<p>You can login here: <a href='{$loginLink}'>{$loginLink}</a></p>";
        $message .= "<br><p>Thanks,<br>The " . SITE_NAME . " Team</p>";

        return self::send($userEmail, $subject, $message);
    }
}
?>
