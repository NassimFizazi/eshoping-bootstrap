<?php
class Session {
    // Start or resume session
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Set user session data
    public static function setUser($user_id, $username, $email, $is_admin = false) {
        self::start();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['is_admin'] = $is_admin;
        $_SESSION['logged_in'] = true;
    }

    // Check if user is logged in
    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    // Check if user is admin
    public static function isAdmin() {
        self::start();
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }

    // Get user ID
    public static function getUserId() {
        self::start();
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }

    // Get username
    public static function getUsername() {
        self::start();
        return isset($_SESSION['username']) ? $_SESSION['username'] : null;
    }

    // Get user email
    public static function getEmail() {
        self::start();
        return isset($_SESSION['email']) ? $_SESSION['email'] : null;
    }

    // Get csrf token
    public static function getCsrfToken() {
        self::start();
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Verify csrf token
    public static function verifyCsrfToken($token) {
        self::start();
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            return false;
        }
        return true;
    }

    // Destroy session (logout)
    public static function destroy() {
        self::start();
        $_SESSION = array();
        session_destroy();
        
        // Clear session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }

    // Set a flash message
    public static function setFlash($type, $message) {
        self::start();
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    // Get flash message and clear it
    public static function getFlash() {
        self::start();
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}
?>
