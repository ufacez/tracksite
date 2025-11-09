<?php
/**
 * Top Bar Component
 * TrackSite Construction Management System
 * 
 * Reusable top navigation bar with notifications and search
 */

// Include notifications functions
require_once __DIR__ . '/notifications.php';

// Get current user info
$user_id = getCurrentUserId();
$username = getCurrentUsername();
$full_name = $_SESSION['full_name'] ?? 'Administrator';
$user_level = getCurrentUserLevel();

// Get unread notification count
$unread_count = getUnreadCount($db, $user_id);

// Generate avatar URL
$avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=f39c12&color=fff";
?>
<div class="top-bar">
    <!-- Search Bar -->
    <div class="search">
        <input type="text" 
               name="search" 
               id="searchInput" 
               placeholder="Search workers, attendance, payroll..."
               autocomplete="off"
               aria-label="Search">
        <label for="searchInput">
            <i class="fas fa-search"></i>
        </label>
    </div>
    
    <!-- Notifications -->
    <div class="notifications">
        <button class="notification-btn" 
                id="notificationBtn" 
                onclick="toggleNotifications()"
                aria-label="Notifications"
                aria-expanded="false">
            <i class="fas fa-bell"></i>
            <?php if ($unread_count > 0): ?>
            <span class="notification-badge" id="notificationBadge">
                <?php echo $unread_count > 99 ? '99+' : $unread_count; ?>
            </span>
            <?php else: ?>
            <span class="notification-badge" id="notificationBadge" style="display: none;"></span>
            <?php endif; ?>
        </button>
        
        <!-- Notification Dropdown -->
        <div class="notification-dropdown" id="notificationDropdown">
            <div class="notification-header">
                <h4>Notifications</h4>
                <button class="mark-read-btn" 
                        onclick="markAllRead()"
                        aria-label="Mark all as read">
                    <i class="fas fa-check-double"></i> Mark all read
                </button>
            </div>
            <div class="notification-list">
                <!-- Notifications will be loaded dynamically via JavaScript -->
                <div class="notification-item">
                    <div class="notification-content" style="text-align: center; padding: 20px; color: #999;">
                        <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 10px;"></i>
                        <p>Loading notifications...</p>
                    </div>
                </div>
            </div>
            <div class="notification-footer">
                <a href="<?php echo BASE_URL; ?>/modules/super_admin/notifications.php" class="view-all-link">
                    View all notifications
                </a>
            </div>
        </div>
    </div>
    
    <!-- User Profile -->
    <div class="user">
        <a href="<?php echo BASE_URL; ?>/modules/super_admin/settings/index.php" 
           title="Manage Account"
           aria-label="User profile">
            <img src="<?php echo $avatar_url; ?>" 
                 alt="<?php echo htmlspecialchars($full_name); ?>"
                 loading="lazy">
        </a>
        <div class="user-info">
            <div>
                <span class="user-name"><?php echo htmlspecialchars($full_name); ?></span>
                <span class="user-role">
                    <?php echo $user_level === 'super_admin' ? 'Administrator' : 'Worker'; ?>
                </span>
            </div>
        </div>
    </div>
</div>