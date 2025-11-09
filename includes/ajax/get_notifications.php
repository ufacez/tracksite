<?php
/**
 * Get Notifications API
 * TrackSite Construction Management System
 * 
 * AJAX endpoint for fetching user notifications
 */

define('TRACKSITE_INCLUDED', true);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/notifications.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    jsonError('Unauthorized access');
}

$user_id = getCurrentUserId();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get':
            // Get notifications
            $limit = (int)($_GET['limit'] ?? 10);
            $unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
            
            $notifications = getUserNotifications($db, $user_id, $unread_only, $limit);
            $unread_count = getUnreadCount($db, $user_id);
            
            // Format notifications
            $formatted = [];
            foreach ($notifications as $notif) {
                $formatted[] = [
                    'id' => $notif['notification_id'],
                    'type' => $notif['type'],
                    'title' => $notif['title'],
                    'message' => $notif['message'],
                    'link' => $notif['link'],
                    'is_read' => (bool)$notif['is_read'],
                    'created_at' => $notif['created_at'],
                    'time_ago' => timeAgo($notif['created_at'])
                ];
            }
            
            jsonSuccess('Notifications retrieved', [
                'notifications' => $formatted,
                'unread_count' => $unread_count
            ]);
            break;
            
        case 'mark_read':
            // Mark single notification as read
            $notification_id = (int)($_POST['notification_id'] ?? 0);
            
            if ($notification_id <= 0) {
                jsonError('Invalid notification ID');
            }
            
            if (markAsRead($db, $notification_id, $user_id)) {
                $unread_count = getUnreadCount($db, $user_id);
                jsonSuccess('Notification marked as read', ['unread_count' => $unread_count]);
            } else {
                jsonError('Failed to mark notification as read');
            }
            break;
            
        case 'mark_all_read':
            // Mark all notifications as read
            if (markAllAsRead($db, $user_id)) {
                jsonSuccess('All notifications marked as read', ['unread_count' => 0]);
            } else {
                jsonError('Failed to mark all notifications as read');
            }
            break;
            
        case 'delete':
            // Delete notification
            $notification_id = (int)($_POST['notification_id'] ?? 0);
            
            if ($notification_id <= 0) {
                jsonError('Invalid notification ID');
            }
            
            if (deleteNotification($db, $notification_id, $user_id)) {
                $unread_count = getUnreadCount($db, $user_id);
                jsonSuccess('Notification deleted', ['unread_count' => $unread_count]);
            } else {
                jsonError('Failed to delete notification');
            }
            break;
            
        case 'count':
            // Get unread count only
            $unread_count = getUnreadCount($db, $user_id);
            jsonSuccess('Unread count retrieved', ['unread_count' => $unread_count]);
            break;
            
        default:
            jsonError('Invalid action');
    }
    
} catch (Exception $e) {
    error_log("Notification API Error: " . $e->getMessage());
    jsonError('An error occurred while processing your request');
}
?>