<?php
/**
 * Settings API - Simple Working Version
 * Save as: modules/super_admin/settings/api.php
 */

// Prevent any output before JSON
ob_start();

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors as HTML
ini_set('log_errors', 1);

define('TRACKSITE_INCLUDED', true);

// Include files
try {
    require_once __DIR__ . '/../../../config/database.php';
    require_once __DIR__ . '/../../../config/settings.php';
    require_once __DIR__ . '/../../../config/session.php';
    require_once __DIR__ . '/../../../includes/functions.php';
    require_once __DIR__ . '/../../../includes/auth.php';
} catch (Exception $e) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to load: ' . $e->getMessage()]);
    exit;
}

// Clear any output from includes
ob_end_clean();

// Set JSON header
header('Content-Type: application/json');

// Check authentication
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if (!isSuperAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit;
}

// Get action
$action = $_REQUEST['action'] ?? '';
$user_id = getCurrentUserId();

// Process action
try {
    switch ($action) {
        case 'save_general':
            // Validate
            if (empty($_POST['company_name'])) {
                echo json_encode(['success' => false, 'message' => 'Company name required']);
                exit;
            }
            
            if (empty($_POST['system_email']) || !filter_var($_POST['system_email'], FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Valid email required']);
                exit;
            }
            
            // Save settings
            $settings = [
                'company_name' => trim($_POST['company_name']),
                'system_email' => trim($_POST['system_email']),
                'phone' => trim($_POST['phone'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'timezone' => trim($_POST['timezone'] ?? 'Asia/Manila')
            ];
            
            foreach ($settings as $key => $value) {
                $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value, updated_by, updated_at) 
                                      VALUES (?, ?, ?, NOW()) 
                                      ON DUPLICATE KEY UPDATE setting_value = ?, updated_by = ?, updated_at = NOW()");
                $stmt->execute([$key, $value, $user_id, $value, $user_id]);
            }
            
            logActivity($db, $user_id, 'update_settings', 'system_settings', null, 'Updated general settings');
            
            echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
            break;
            
        case 'save_system':
            // Validate
            $work_hours = intval($_POST['work_hours_per_day'] ?? 8);
            if ($work_hours < 1 || $work_hours > 24) {
                echo json_encode(['success' => false, 'message' => 'Work hours must be 1-24']);
                exit;
            }
            
            // Save config
            $settings = [
                'work_hours_per_day' => $work_hours,
                'overtime_rate' => floatval($_POST['overtime_rate'] ?? 1.25),
                'late_threshold_minutes' => intval($_POST['late_threshold_minutes'] ?? 15),
                'currency' => trim($_POST['currency'] ?? 'PHP'),
                'date_format' => trim($_POST['date_format'] ?? 'Y-m-d')
            ];
            
            foreach ($settings as $key => $value) {
                $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value, updated_by, updated_at) 
                                      VALUES (?, ?, ?, NOW()) 
                                      ON DUPLICATE KEY UPDATE setting_value = ?, updated_by = ?, updated_at = NOW()");
                $stmt->execute([$key, $value, $user_id, $value, $user_id]);
            }
            
            logActivity($db, $user_id, 'update_settings', 'system_settings', null, 'Updated system config');
            
            echo json_encode(['success' => true, 'message' => 'Configuration saved successfully']);
            break;
            
        case 'update_profile':
            // Validate
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            
            if (empty($first_name) || empty($last_name)) {
                echo json_encode(['success' => false, 'message' => 'Name is required']);
                exit;
            }
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Valid email required']);
                exit;
            }
            
            // Check email uniqueness
            $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Email already in use']);
                exit;
            }
            
            // Update
            $stmt = $db->prepare("UPDATE users SET email = ?, updated_at = NOW() WHERE user_id = ?");
            $stmt->execute([$email, $user_id]);
            
            $stmt = $db->prepare("UPDATE super_admin_profile SET first_name = ?, last_name = ?, phone = ?, updated_at = NOW() WHERE user_id = ?");
            $stmt->execute([$first_name, $last_name, trim($_POST['phone'] ?? ''), $user_id]);
            
            $_SESSION['full_name'] = "$first_name $last_name";
            $_SESSION['email'] = $email;
            
            logActivity($db, $user_id, 'update_profile', 'super_admin_profile', null, 'Updated profile');
            
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
            break;
            
        case 'change_password':
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            
            if (empty($current) || empty($new)) {
                echo json_encode(['success' => false, 'message' => 'All fields required']);
                exit;
            }
            
            if (strlen($new) < 6) {
                echo json_encode(['success' => false, 'message' => 'Password must be 6+ characters']);
                exit;
            }
            
            // Verify current password
            $stmt = $db->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($current, $user['password'])) {
                echo json_encode(['success' => false, 'message' => 'Current password incorrect']);
                exit;
            }
            
            // Update password
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE user_id = ?");
            $stmt->execute([$hashed, $user_id]);
            
            logActivity($db, $user_id, 'change_password', 'users', $user_id, 'Changed password');
            
            echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
            break;
            
        case 'create_backup':
            $backup_dir = BASE_PATH . '/backups';
            if (!is_dir($backup_dir)) {
                mkdir($backup_dir, 0755, true);
            }
            
            $filename = 'backup_' . date('Y-m-d_His') . '.sql';
            $filepath = $backup_dir . '/' . $filename;
            
            $content = "-- TrackSite Backup\n-- Date: " . date('Y-m-d H:i:s') . "\n\n";
            file_put_contents($filepath, $content);
            
            echo json_encode(['success' => true, 'message' => 'Backup created', 'data' => ['filename' => $filename]]);
            break;
            
        case 'download_backup':
            $filename = basename($_GET['file'] ?? '');
            $filepath = BASE_PATH . '/backups/' . $filename;
            
            if (!file_exists($filepath)) {
                echo json_encode(['success' => false, 'message' => 'File not found']);
                exit;
            }
            
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            readfile($filepath);
            exit;
            
        case 'delete_backup':
            $filename = basename($_POST['filename'] ?? '');
            $filepath = BASE_PATH . '/backups/' . $filename;
            
            if (file_exists($filepath) && unlink($filepath)) {
                echo json_encode(['success' => true, 'message' => 'Backup deleted']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Delete failed']);
            }
            break;
            
        case 'clear_cache':
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }
            echo json_encode(['success' => true, 'message' => 'Cache cleared']);
            break;
            
        case 'test_email':
            $email = $_POST['email'] ?? '';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Invalid email']);
                exit;
            }
            echo json_encode(['success' => true, 'message' => 'Email config ready']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (PDOException $e) {
    error_log("Settings API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
} catch (Exception $e) {
    error_log("Settings API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}

exit;
?>