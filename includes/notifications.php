<?php
/**
 * Notification Management Functions
 * TrackSite Construction Management System
 * 
 * Handles notification creation, retrieval, and management
 */

// Prevent direct access
if (!defined('TRACKSITE_INCLUDED')) {
    define('TRACKSITE_INCLUDED', true);
}

// ============================================
// NOTIFICATION TYPES
// ============================================
define('NOTIFICATION_INFO', 'info');
define('NOTIFICATION_SUCCESS', 'success');
define('NOTIFICATION_WARNING', 'warning');
define('NOTIFICATION_DANGER', 'danger');

/**
 * Create a new notification
 * 
 * @param PDO $db Database connection
 * @param int $user_id User ID to send notification to (null for all admins)
 * @param string $type Notification type (info, success, warning, danger)
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $link Optional link URL
 * @param array $data Optional additional data (JSON)
 * @return bool True on success, false on failure
 */
function createNotification($db, $user_id, $type, $title, $message, $link = null, $data = null) {
    try {
        $sql = "INSERT INTO notifications (user_id, type, title, message, link, data, is_read, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 0, NOW())";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $user_id,
            $type,
            $title,
            $message,
            $link,
            $data ? json_encode($data) : null
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Create Notification Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create notification for all super admins
 * 
 * @param PDO $db Database connection
 * @param string $type Notification type
 * @param string $title Notification title
 * @param string $message Notification message
 * @param string $link Optional link URL
 * @return bool True on success, false on failure
 */
function createAdminNotification($db, $type, $title, $message, $link = null) {
    try {
        // Get all super admin user IDs
        $sql = "SELECT user_id FROM users WHERE user_level = 'super_admin' AND status = 'active'";
        $stmt = $db->query($sql);
        $admins = $stmt->fetchAll();
        
        foreach ($admins as $admin) {
            createNotification($db, $admin['user_id'], $type, $title, $message, $link);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Create Admin Notification Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user notifications
 * 
 * @param PDO $db Database connection
 * @param int $user_id User ID
 * @param bool $unread_only Get only unread notifications
 * @param int $limit Limit number of results
 * @return array Array of notifications
 */
function getUserNotifications($db, $user_id, $unread_only = false, $limit = 50) {
    try {
        $sql = "SELECT * FROM notifications WHERE user_id = ?";
        
        if ($unread_only) {
            $sql .= " AND is_read = 0";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        
        $stmt = $db->prepare($sql);
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Get Notifications Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get unread notification count
 * 
 * @param PDO $db Database connection
 * @param int $user_id User ID
 * @return int Count of unread notifications
 */
function getUnreadCount($db, $user_id) {
    try {
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        return (int)$result['count'];
    } catch (PDOException $e) {
        error_log("Get Unread Count Error: " . $e->getMessage());
        return 0;
    }
}

/**
 * Mark notification as read
 * 
 * @param PDO $db Database connection
 * @param int $notification_id Notification ID
 * @param int $user_id User ID (for security)
 * @return bool True on success, false on failure
 */
function markAsRead($db, $notification_id, $user_id) {
    try {
        $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() 
                WHERE notification_id = ? AND user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$notification_id, $user_id]);
        return true;
    } catch (PDOException $e) {
        error_log("Mark Read Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Mark all notifications as read for a user
 * 
 * @param PDO $db Database connection
 * @param int $user_id User ID
 * @return bool True on success, false on failure
 */
function markAllAsRead($db, $user_id) {
    try {
        $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() 
                WHERE user_id = ? AND is_read = 0";
        $stmt = $db->prepare($sql);
        $stmt->execute([$user_id]);
        return true;
    } catch (PDOException $e) {
        error_log("Mark All Read Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete notification
 * 
 * @param PDO $db Database connection
 * @param int $notification_id Notification ID
 * @param int $user_id User ID (for security)
 * @return bool True on success, false on failure
 */
function deleteNotification($db, $notification_id, $user_id) {
    try {
        $sql = "DELETE FROM notifications WHERE notification_id = ? AND user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$notification_id, $user_id]);
        return true;
    } catch (PDOException $e) {
        error_log("Delete Notification Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete old notifications (cleanup)
 * 
 * @param PDO $db Database connection
 * @param int $days_old Delete notifications older than this many days
 * @return bool True on success, false on failure
 */
function deleteOldNotifications($db, $days_old = 30) {
    try {
        $sql = "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $db->prepare($sql);
        $stmt->execute([$days_old]);
        return true;
    } catch (PDOException $e) {
        error_log("Delete Old Notifications Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Create attendance notification
 * 
 * @param PDO $db Database connection
 * @param string $event Event type (clock_in, clock_out, late, absent)
 * @param array $worker_data Worker data
 */
function notifyAttendanceEvent($db, $event, $worker_data) {
    $title = '';
    $message = '';
    $type = NOTIFICATION_INFO;
    $link = BASE_URL . '/modules/super_admin/attendance/index.php';
    
    $worker_name = $worker_data['first_name'] . ' ' . $worker_data['last_name'];
    
    switch ($event) {
        case 'clock_in':
            $title = 'Worker Clocked In';
            $message = "{$worker_name} has clocked in at " . date('h:i A');
            $type = NOTIFICATION_SUCCESS;
            break;
            
        case 'clock_out':
            $title = 'Worker Clocked Out';
            $message = "{$worker_name} has clocked out at " . date('h:i A');
            $type = NOTIFICATION_INFO;
            break;
            
        case 'late':
            $title = 'Late Arrival';
            $message = "{$worker_name} arrived late today";
            $type = NOTIFICATION_WARNING;
            break;
            
        case 'absent':
            $title = 'Worker Absent';
            $message = "{$worker_name} is marked as absent today";
            $type = NOTIFICATION_DANGER;
            break;
            
        case 'overtime':
            $title = 'Overtime Recorded';
            $message = "{$worker_name} worked overtime today";
            $type = NOTIFICATION_INFO;
            break;
    }
    
    if ($title && $message) {
        createAdminNotification($db, $type, $title, $message, $link);
    }
}

/**
 * Create cash advance notification
 * 
 * @param PDO $db Database connection
 * @param string $event Event type (request, approve, reject)
 * @param array $advance_data Cash advance data
 */
function notifyCashAdvanceEvent($db, $event, $advance_data) {
    $title = '';
    $message = '';
    $type = NOTIFICATION_INFO;
    $link = BASE_URL . '/modules/super_admin/cashadvance/index.php';
    
    $amount = formatCurrency($advance_data['amount']);
    
    switch ($event) {
        case 'request':
            $title = 'New Cash Advance Request';
            $message = "A worker requested a cash advance of {$amount}";
            $type = NOTIFICATION_WARNING;
            break;
            
        case 'approve':
            $title = 'Cash Advance Approved';
            $message = "Cash advance of {$amount} has been approved";
            $type = NOTIFICATION_SUCCESS;
            break;
            
        case 'reject':
            $title = 'Cash Advance Rejected';
            $message = "Cash advance of {$amount} has been rejected";
            $type = NOTIFICATION_DANGER;
            break;
    }
    
    if ($title && $message) {
        createAdminNotification($db, $type, $title, $message, $link);
    }
}

/**
 * Create payroll notification
 * 
 * @param PDO $db Database connection
 * @param string $period_start Start date
 * @param string $period_end End date
 * @param int $worker_count Number of workers
 */
function notifyPayrollGenerated($db, $period_start, $period_end, $worker_count) {
    $title = 'Payroll Generated';
    $message = "Payroll for {$worker_count} workers has been generated for period " . 
               date('M d', strtotime($period_start)) . " - " . date('M d, Y', strtotime($period_end));
    $link = BASE_URL . '/modules/super_admin/payroll/index.php';
    
    createAdminNotification($db, NOTIFICATION_SUCCESS, $title, $message, $link);
}

/**
 * Create worker notification
 * 
 * @param PDO $db Database connection
 * @param string $event Event type (new, update, terminate)
 * @param array $worker_data Worker data
 */
function notifyWorkerEvent($db, $event, $worker_data) {
    $title = '';
    $message = '';
    $type = NOTIFICATION_INFO;
    $link = BASE_URL . '/modules/super_admin/workers/index.php';
    
    $worker_name = $worker_data['first_name'] . ' ' . $worker_data['last_name'];
    
    switch ($event) {
        case 'new':
            $title = 'New Worker Added';
            $message = "{$worker_name} has been added to the system";
            $type = NOTIFICATION_SUCCESS;
            break;
            
        case 'update':
            $title = 'Worker Profile Updated';
            $message = "{$worker_name}'s profile has been updated";
            $type = NOTIFICATION_INFO;
            break;
            
        case 'terminate':
            $title = 'Worker Terminated';
            $message = "{$worker_name} has been terminated";
            $type = NOTIFICATION_DANGER;
            break;
    }
    
    if ($title && $message) {
        createAdminNotification($db, $type, $title, $message, $link);
    }
}
?>