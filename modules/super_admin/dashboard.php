<?php
/**
 * Super Admin Dashboard - FIXED
 * TrackSite Construction Management System
 */

// Define constant to allow includes
define('TRACKSITE_INCLUDED', true);

// Include required files
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/settings.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

// Require super admin access
requireSuperAdmin();

// Get current user info
$user_id = getCurrentUserId();
$username = getCurrentUsername();
$full_name = $_SESSION['full_name'] ?? 'Administrator';

// Get flash message
$flash = getFlashMessage();

// Fetch dashboard statistics - FIXED to exclude archived records
try {
    // Total active workers (excluding archived)
    $stmt = $db->query("SELECT COUNT(*) as total FROM workers 
                        WHERE employment_status = 'active' AND is_archived = FALSE");
    $total_workers = $stmt->fetch()['total'];
    
    // Workers on site today (excluding archived)
    $stmt = $db->query("SELECT COUNT(DISTINCT a.worker_id) as total 
                        FROM attendance a
                        JOIN workers w ON a.worker_id = w.worker_id
                        WHERE a.attendance_date = CURDATE() 
                        AND a.status IN ('present', 'late', 'overtime')
                        AND a.is_archived = FALSE
                        AND w.is_archived = FALSE");
    $on_site_today = $stmt->fetch()['total'];
    
    // Workers on leave (excluding archived)
    $stmt = $db->query("SELECT COUNT(*) as total FROM workers 
                        WHERE employment_status = 'on_leave' AND is_archived = FALSE");
    $on_leave = $stmt->fetch()['total'];
    
    // Workers with overtime today (excluding archived)
    $stmt = $db->query("SELECT COUNT(DISTINCT a.worker_id) as total 
                        FROM attendance a
                        JOIN workers w ON a.worker_id = w.worker_id
                        WHERE a.attendance_date = CURDATE() 
                        AND a.status = 'overtime'
                        AND a.is_archived = FALSE
                        AND w.is_archived = FALSE");
    $overtime_today = $stmt->fetch()['total'];
    
    // Recent attendance (last 6 records, excluding archived)
    $stmt = $db->query("SELECT a.*, w.first_name, w.last_name, w.worker_code, w.position 
                        FROM attendance a 
                        JOIN workers w ON a.worker_id = w.worker_id 
                        WHERE a.attendance_date = CURDATE()
                        AND a.is_archived = FALSE
                        AND w.is_archived = FALSE
                        ORDER BY a.time_in DESC 
                        LIMIT 6");
    $recent_attendance = $stmt->fetchAll();
    
    // Today's schedules (workers scheduled for today, excluding archived)
    $today = strtolower(date('l')); // Get day name (monday, tuesday, etc.)
    $stmt = $db->query("SELECT s.*, w.worker_code, w.first_name, w.last_name,
                        (SELECT COUNT(*) FROM attendance a 
                         WHERE a.worker_id = w.worker_id 
                         AND a.attendance_date = CURDATE()
                         AND a.is_archived = FALSE) as has_attendance
                        FROM schedules s 
                        JOIN workers w ON s.worker_id = w.worker_id 
                        WHERE s.day_of_week = '$today' 
                        AND s.is_active = 1
                        AND w.employment_status = 'active'
                        AND w.is_archived = FALSE
                        LIMIT 5");
    $today_schedules = $stmt->fetchAll();
    
    // Recent Activity (Last 10 activities)
    $sql = "SELECT al.*, u.username 
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.user_id
            ORDER BY al.created_at DESC
            LIMIT 10";
    $stmt = $db->query($sql);
    $recent_activities = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard Query Error: " . $e->getMessage());
    $total_workers = 0;
    $on_site_today = 0;
    $on_leave = 0;
    $overtime_today = 0;
    $recent_attendance = [];
    $today_schedules = [];
    $recent_activities = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SYSTEM_NAME; ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" 
          integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" 
          crossorigin="anonymous" />
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dashboard.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../../includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="main">
            <!-- Top Bar -->
            <?php include __DIR__ . '/../../includes/topbar.php'; ?>
            
            <!-- Dashboard Content -->
            <div class="dashboard-content">
                
                <!-- Flash Message -->
                <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>" id="flashMessage">
                    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'info-circle'; ?>"></i>
                    <span><?php echo htmlspecialchars($flash['message']); ?></span>
                    <button class="alert-close" onclick="closeAlert('flashMessage')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="stats-cards">
                    <div class="stat-card card-blue">
                        <div class="card-content">
                            <div class="card-value"><?php echo $total_workers; ?></div>
                            <div class="card-label">Total Workers</div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    
                    <div class="stat-card card-green">
                        <div class="card-content">
                            <div class="card-value"><?php echo $on_site_today; ?></div>
                            <div class="card-label">On Site Today</div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                    </div>
                    
                    <div class="stat-card card-orange">
                        <div class="card-content">
                            <div class="card-value"><?php echo $on_leave; ?></div>
                            <div class="card-label">On Leave</div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                    </div>
                    
                    <div class="stat-card card-purple">
                        <div class="card-content">
                            <div class="card-value"><?php echo $overtime_today; ?></div>
                            <div class="card-label">Overtime Today</div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Tables Section -->
                <div class="tables-section">
                    
                    <!-- Recent Attendance Table -->
                    <div class="table-container recent-attendance">
                        <div class="table-header">
                            <h2>Recent Attendance</h2>
                            <a href="<?php echo BASE_URL; ?>/modules/super_admin/attendance/index.php" class="btn btn-view-all">
                                View All
                            </a>
                        </div>
                        
                        <div class="table-wrapper">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Worker</th>
                                        <th>Trade</th>
                                        <th>Time In</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_attendance)): ?>
                                    <tr>
                                        <td colspan="5" class="no-data">
                                            <i class="fas fa-inbox"></i>
                                            <p>No attendance records for today</p>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_attendance as $record): ?>
                                        <tr>
                                            <td>
                                                <div class="worker-info">
                                                    <div class="worker-avatar">
                                                        <?php echo getInitials($record['first_name'] . ' ' . $record['last_name']); ?>
                                                    </div>
                                                    <span><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($record['position']); ?></td>
                                            <td><?php echo $record['time_in'] ? formatTime($record['time_in']) : '--'; ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $record['status']; ?>">
                                                    <?php echo ucfirst($record['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-icons">
                                                    <button class="action-icon icon-view" 
                                                            onclick="window.location.href='<?php echo BASE_URL; ?>/modules/super_admin/attendance/index.php'"
                                                            title="View Details">
                                                        <i class="far fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Today's Schedule Table -->
                    <div class="table-container today-schedule">
                        <div class="table-header">
                            <h2>Shifts Today</h2>
                            <a href="<?php echo BASE_URL; ?>/modules/super_admin/schedule/index.php" class="btn btn-view-all">
                                View All
                            </a>
                        </div>
                        
                        <div class="table-wrapper">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Worker</th>
                                        <th>Shift Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($today_schedules)): ?>
                                    <tr>
                                        <td colspan="3" class="no-data">
                                            <i class="fas fa-calendar-times"></i>
                                            <p>No schedules for today</p>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                        <?php foreach ($today_schedules as $schedule): ?>
                                        <tr>
                                            <td>
                                                <div class="worker-info">
                                                    <div class="worker-avatar-small">
                                                        <?php echo getInitials($schedule['first_name'] . ' ' . $schedule['last_name']); ?>
                                                    </div>
                                                    <span><?php echo htmlspecialchars($schedule['first_name'] . ' ' . $schedule['last_name']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php echo date('H:i', strtotime($schedule['start_time'])); ?> - 
                                                <?php echo date('H:i', strtotime($schedule['end_time'])); ?>
                                            </td>
                                            <td>
                                                <?php if ($schedule['has_attendance'] > 0): ?>
                                                    <i class="fas fa-check-circle status-icon-success"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle status-icon-danger"></i>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Recent Activity Section -->
                    <div class="activity-section">
                        <div class="table-container">
                            <div class="table-header">
                                <h2><i class="fas fa-history"></i> Recent Activity</h2>
                                <span class="activity-subtitle">System activity log</span>
                            </div>
                            <div class="activity-list">
                                <?php if (empty($recent_activities)): ?>
                                <div class="no-data">
                                    <i class="fas fa-history"></i>
                                    <p>No recent activity</p>
                                </div>
                                <?php else: ?>
                                    <?php foreach ($recent_activities as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon activity-icon-<?php echo getActivityColor($activity['action']); ?>">
                                            <i class="fas fa-<?php echo getActivityIcon($activity['action']); ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-text">
                                                <strong><?php echo htmlspecialchars($activity['username'] ?? 'System'); ?></strong>
                                                <span class="activity-action"><?php echo ucwords(str_replace('_', ' ', $activity['action'])); ?></span>
                                                <?php if ($activity['description']): ?>
                                                    <span class="activity-description">- <?php echo htmlspecialchars($activity['description']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="activity-meta">
                                                <span class="activity-time">
                                                    <i class="far fa-clock"></i>
                                                    <?php echo timeAgo($activity['created_at']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="<?php echo JS_URL; ?>/dashboard.js"></script>
</body>
</html>