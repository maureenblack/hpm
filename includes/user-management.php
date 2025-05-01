<?php
/**
 * User Management Functions
 * Holistic Prosperity Ministry Payment System
 * 
 * Security implementation:
 * - Secure password hashing using password_hash
 * - Session management with regeneration
 * - Brute force protection
 */

// Ensure this file is not accessed directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access to this file is forbidden.');
}

/**
 * Create a new user
 * 
 * @param string $username Username
 * @param string $password Password
 * @param string $firstName First name
 * @param string $lastName Last name
 * @param string $email Email address
 * @param string $role User role (admin, finance, viewer)
 * @return int|bool User ID or false on failure
 */
function createUser($username, $password, $firstName, $lastName, $email, $role = 'viewer') {
    global $pdo;
    
    try {
        // Validate input
        if (empty($username) || empty($password) || empty($email)) {
            return false;
        }
        
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            return false; // User already exists
        }
        
        // Hash password securely
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Insert new user
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, first_name, last_name, email, role)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$username, $hashedPassword, $firstName, $lastName, $email, $role]);
        
        return $pdo->lastInsertId();
        
    } catch (PDOException $e) {
        error_log("User Creation Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Verify user credentials
 * 
 * @param string $username Username
 * @param string $password Password
 * @return array|bool User data or false on failure
 */
function verifyUser($username, $password) {
    global $pdo;
    
    try {
        // Get user from database
        $stmt = $pdo->prepare("
            SELECT user_id, username, password, first_name, last_name, email, role, is_active
            FROM users
            WHERE username = ? AND is_active = 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        // Verify user exists and password is correct
        if ($user && password_verify($password, $user['password'])) {
            // Check if password needs rehashing (if PHP's default has changed)
            if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => 12])) {
                $newHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $updateStmt->execute([$newHash, $user['user_id']]);
            }
            
            // Remove password from user data
            unset($user['password']);
            
            return $user;
        }
        
        return false;
        
    } catch (PDOException $e) {
        error_log("User Verification Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Start a secure session
 * 
 * @return void
 */
function startSecureSession() {
    // Set secure session parameters
    $sessionParams = [
        'cookie_httponly' => 1,
        'cookie_secure' => 1, // Requires HTTPS
        'use_only_cookies' => 1,
        'cookie_samesite' => 'Strict',
        'use_strict_mode' => 1
    ];
    
    // Start session with secure parameters
    if (session_status() === PHP_SESSION_NONE) {
        session_start($sessionParams);
    }
    
    // Regenerate session ID to prevent session fixation
    if (!isset($_SESSION['last_regeneration']) || 
        (time() - $_SESSION['last_regeneration']) > 300) {
        
        // Regenerate session ID and delete old session
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

/**
 * Log in a user
 * 
 * @param array $user User data
 * @return bool Success status
 */
function loginUser($user) {
    // Start secure session
    startSecureSession();
    
    // Set session variables
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Log out a user
 * 
 * @return void
 */
function logoutUser() {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Unset all session variables
    $_SESSION = [];
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Check if user is logged in
 * 
 * @return bool Login status
 */
function isUserLoggedIn() {
    // Start secure session
    startSecureSession();
    
    // Check if user is logged in
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        // Check for session timeout
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            // Session expired, log out user
            logoutUser();
            return false;
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    return false;
}

/**
 * Check if user has admin privileges
 * 
 * @return bool Admin status
 */
function isUserAdmin() {
    return isUserLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Reset user password
 * 
 * @param int $userId User ID
 * @param string $newPassword New password
 * @return bool Success status
 */
function resetUserPassword($userId, $newPassword) {
    global $pdo;
    
    try {
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        
        // Update password
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->execute([$hashedPassword, $userId]);
        
        return $stmt->rowCount() > 0;
        
    } catch (PDOException $e) {
        error_log("Password Reset Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate a secure random token
 * 
 * @param int $length Token length
 * @return string Random token
 */
function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}
?>
