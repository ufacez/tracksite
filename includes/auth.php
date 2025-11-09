<?php
/**
 * Authentication Functions
 * TrackSite Construction Management System
 * 
 * Handles user authentication and authorization
 */

// Prevent direct access
if (!defined('TRACKSITE_INCLUDED')) {
    define('TRACKSITE_INCLUDED', true);
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/functions.php';

/**
 * Authenticate user login
 * 
 * @param PDO $db Database connection
 * @param string $username Username or email
 * @param string $password Password
 * @return array Result with 'success' (bool), 'message' (string), and 'user' (array)
 */
function authenticateUser($db, $username, $password) {
    $result = ['success' => false, 'message' => '', 'user' => null];
    
    // Check for rate limiting
    if (isLoginLocked($username)) {
        $result['message'] = 'Too many failed login attempts. Please try again in ' . LOGIN_LOCKOUT_TIME/60 . ' minutes.';
        return $result;
    }
    
    try {
        // Find user by username or email
        $sql = "SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active' LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            recordFailedLogin($username);
            $result['message'] = 'Invalid username or password.';
            return $result;
        }
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            recordFailedLogin($username);
            $result['message'] = 'Invalid username or password.';
            return $result;
        }
        
        // Get additional user data based on user level
        if ($user['user_level'] === USER_LEVEL_SUPER_ADMIN) {
            $sql = "SELECT * FROM super_admin_profile WHERE user_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user['user_id']]);
            $profile = $stmt->fetch();
            
            if ($profile) {
                $user = array_merge($user, $profile);
            }
        } elseif ($user['user_level'] === USER_LEVEL_WORKER) {
            $sql = "SELECT * FROM workers WHERE user_id = ? AND employment_status = 'active'";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user['user_id']]);
            $worker = $stmt->fetch();
            
            if (!$worker) {
                $result['message'] = 'Your account is not active. Please contact administrator.';
                return $result;
            }
            
            $user = array_merge($user, $worker);
        }
        
        // Update last login
        $sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$user['user_id']]);
        
        // Clear failed login attempts
        clearFailedLogins($username);
        
        // Log activity
        logActivity($db, $user['user_id'], 'login', 'users', $user['user_id'], 'User logged in');
        
        $result['success'] = true;
        $result['message'] = 'Login successful!';
        $result['user'] = $user;
        
    } catch (PDOException $e) {
        error_log("Authentication Error: " . $e->getMessage());
        $result['message'] = 'An error occurred during authentication.';
    }
    
    return $result;
}

/**
 * Register new user
 * 
 * @param PDO $db Database connection
 * @param array $data User data
 * @return array Result with 'success' (bool), 'message' (string), and 'user_id' (int)
 */
function registerUser($db, $data) {
    $result = ['success' => false, 'message' => '', 'user_id' => null];
    
    try {
        // Validate required fields
        $required = ['username', 'email', 'password', 'user_level'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $result['message'] = 'Missing required field: ' . $field;
                return $result;
            }
        }
        
        // Validate email
        if (!validateEmail($data['email'])) {
            $result['message'] = 'Invalid email address.';
            return $result;
        }
        
        // Validate password
        $password_check = validatePassword($data['password']);
        if (!$password_check['valid']) {
            $result['message'] = $password_check['message'];
            return $result;
        }
        
        // Check if username exists
        $sql = "SELECT user_id FROM users WHERE username = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$data['username']]);
        if ($stmt->fetch()) {
            $result['message'] = 'Username already exists.';
            return $result;
        }
        
        // Check if email exists
        $sql = "SELECT user_id FROM users WHERE email = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            $result['message'] = 'Email already exists.';
            return $result;
        }
        
        // Hash password
        $hashed_password = password_hash($data['password'], PASSWORD_HASH_ALGO);
        
        // Insert user
        $sql = "INSERT INTO users (username, password, email, user_level, status) VALUES (?, ?, ?, ?, 'active')";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $data['username'],
            $hashed_password,
            $data['email'],
            $data['user_level']
        ]);
        
        $user_id = $db->lastInsertId();
        
        $result['success'] = true;
        $result['message'] = 'User registered successfully!';
        $result['user_id'] = $user_id;
        
    } catch (PDOException $e) {
        error_log("Registration Error: " . $e->getMessage());
        $result['message'] = 'An error occurred during registration.';
    }
    
    return $result;
}

/**
 * Change user password
 * 
 * @param PDO $db Database connection
 * @param int $user_id User ID
 * @param string $old_password Old password
 * @param string $new_password New password
 * @return array Result with 'success' (bool) and 'message' (string)
 */
function changePassword($db, $user_id, $old_password, $new_password) {
    $result = ['success' => false, 'message' => ''];
    
    try {
        // Get current password
        $sql = "SELECT password FROM users WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $result['message'] = 'User not found.';
            return $result;
        }
        
        // Verify old password
        if (!password_verify($old_password, $user['password'])) {
            $result['message'] = 'Current password is incorrect.';
            return $result;
        }
        
        // Validate new password
        $password_check = validatePassword($new_password);
        if (!$password_check['valid']) {
            $result['message'] = $password_check['message'];
            return $result;
        }
        
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_HASH_ALGO);
        
        // Update password
        $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$hashed_password, $user_id]);
        
        // Log activity
        logActivity($db, $user_id, 'change_password', 'users', $user_id, 'Password changed');
        
        $result['success'] = true;
        $result['message'] = 'Password changed successfully!';
        
    } catch (PDOException $e) {
        error_log("Change Password Error: " . $e->getMessage());
        $result['message'] = 'An error occurred while changing password.';
    }
    
    return $result;
}

/**
 * Record failed login attempt
 * 
 * @param string $username Username
 */
function recordFailedLogin($username) {
    if (!isset($_SESSION['failed_logins'])) {
        $_SESSION['failed_logins'] = [];
    }
    
    $_SESSION['failed_logins'][$username] = [
        'count' => ($_SESSION['failed_logins'][$username]['count'] ?? 0) + 1,
        'time' => time()
    ];
}

/**
 * Check if login is locked due to too many attempts
 * 
 * @param string $username Username
 * @return bool True if locked, false otherwise
 */
function isLoginLocked($username) {
    if (!isset($_SESSION['failed_logins'][$username])) {
        return false;
    }
    
    $failed = $_SESSION['failed_logins'][$username];
    
    // Check if lockout period has expired
    if (time() - $failed['time'] > LOGIN_LOCKOUT_TIME) {
        unset($_SESSION['failed_logins'][$username]);
        return false;
    }
    
    // Check if max attempts exceeded
    return $failed['count'] >= MAX_LOGIN_ATTEMPTS;
}

/**
 * Clear failed login attempts
 * 
 * @param string $username Username
 */
function clearFailedLogins($username) {
    if (isset($_SESSION['failed_logins'][$username])) {
        unset($_SESSION['failed_logins'][$username]);
    }
}

/**
 * Get user by ID
 * 
 * @param PDO $db Database connection
 * @param int $user_id User ID
 * @return array|false User data or false
 */
function getUserById($db, $user_id) {
    try {
        $sql = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Get User Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update user status
 * 
 * @param PDO $db Database connection
 * @param int $user_id User ID
 * @param string $status New status
 * @return bool True on success, false on failure
 */
function updateUserStatus($db, $user_id, $status) {
    try {
        $sql = "UPDATE users SET status = ?, updated_at = NOW() WHERE user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$status, $user_id]);
        
        logActivity($db, getCurrentUserId(), 'update_user_status', 'users', $user_id, "Status changed to: $status");
        
        return true;
    } catch (PDOException $e) {
        error_log("Update User Status Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if username exists
 * 
 * @param PDO $db Database connection
 * @param string $username Username
 * @param int $exclude_user_id User ID to exclude from check
 * @return bool True if exists, false otherwise
 */
function usernameExists($db, $username, $exclude_user_id = null) {
    try {
        $sql = "SELECT user_id FROM users WHERE username = ?";
        $params = [$username];
        
        if ($exclude_user_id) {
            $sql .= " AND user_id != ?";
            $params[] = $exclude_user_id;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("Username Check Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if email exists
 * 
 * @param PDO $db Database connection
 * @param string $email Email address
 * @param int $exclude_user_id User ID to exclude from check
 * @return bool True if exists, false otherwise
 */
function emailExists($db, $email, $exclude_user_id = null) {
    try {
        $sql = "SELECT user_id FROM users WHERE email = ?";
        $params = [$email];
        
        if ($exclude_user_id) {
            $sql .= " AND user_id != ?";
            $params[] = $exclude_user_id;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("Email Check Error: " . $e->getMessage());
        return false;
    }
}
?>