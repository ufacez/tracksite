<?php
/**
 * Super Admin Dashboard - FIXED WITH COMPREHENSIVE ACTIVITY
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
    $today = strtolower(date('l'));
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
    
    // ENHANCED: Recent Activity with detailed information
    $sql = "SELECT 
                al.*,
                u.username,
                u.user_level,
                CASE 
                    WHEN al.table_name = 'workers' THEN 
                        (SELECT CONCAT(first_name, ' ', last_name) FROM workers WHERE worker_id = al.record_id)
                    WHEN al.table_name = 'attendance' THEN 
                        (SELECT CONCAT(w.first_name, ' ', w.last_name) 
                         FROM attendance a 
                         JOIN workers w ON a.worker_id = w.worker_id 
                         WHERE a.attendance_id = al.record_id)
                    WHEN al.table_name = 'payroll' THEN
                        (SELECT CONCAT(w.first_name, ' ', w.last_name)
                         FROM payroll p
                         JOIN workers w ON p.worker_id = w.worker_id
                         WHERE p.payroll_id = al.record_id)
                    WHEN al.table_name = 'cash_advances' THEN
                        (SELECT CONCAT(w.first_name, ' ', w.last_name)
                         FROM cash_advances ca
                         JOIN workers w ON ca.worker_id = w.worker_id
                         WHERE ca.advance_id = al.record_id)
                    WHEN al.table_name = 'schedules' THEN
                        (SELECT CONCAT(w.first_name, ' ', w.last_name)
                         FROM schedules s
                         JOIN workers w ON s.worker_id = w.worker_id
                         WHERE s.schedule_id = al.record_id)
                    WHEN al.table_name = 'deductions' THEN
                        (SELECT CONCAT(w.first_name, ' ', w.last_name)
                         FROM deductions d
                         JOIN workers w ON d.worker_id = w.worker_id
                         WHERE d.deduction_id = al.record_id)
                    ELSE NULL
                END as affected_person
            FROM activity_logs al
            LEFT JOIN users u ON al.user_id = u.user_id
            ORDER BY al.created_at DESC
            LIMIT 15";
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

// Enhanced activity description function
function getEnhancedActivityDescription($activity) {
    $action = $activity['action'];
    $table = $activity['table_name'];
    $description = $activity['description'];
    $affected_person = $activity['affected_person'];
    
    // Build contextual description
    $context = '';
    
    switch($action) {
        case 'login':
            return 'logged into the system';
        case 'logout':
            return 'logged out of the system';
        case 'create':
            $context = 'created new ';
            break;
        case 'update':
            $context = 'updated ';
            break;
        case 'delete':
            $context = 'deleted ';
            break;
        case 'archive':
            $context = 'archived ';
            break;
        case 'restore':
            $context = 'restored ';
            break;
        case 'approve':
            $context = 'approved ';
            break;
        case 'reject':
            $context = 'rejected ';
            break;
        case 'mark_attendance':
            $context = 'marked attendance for ';
            break;
        case 'approve_cashadvance':
            $context = 'approved cash advance for ';
            break;
        case 'reject_cashadvance':
            $context = 'rejected cash advance for ';
            break;
        case 'record_cashadvance_payment':
            $context = 'recorded cash advance payment for ';
            break;
        case 'change_password':
            return 'changed their password';
        case 'update_user_status':
            return 'updated user status';
        default:
            $context = $action . ' ';
    }
    
    // Add table context
    switch($table) {
        case 'workers':
            $context .= 'worker';
            break;
        case 'attendance':
            $context .= 'attendance record';
            break;
        case 'payroll':
            $context .= 'payroll';
            break;
        case 'cash_advances':
            $context .= 'cash advance';
            break;
        case 'schedules':
            $context .= 'schedule';
            break;
        case 'deductions':
            $context .= 'deduction';
            break;
        default:
            $context .= 'record';
    }
    
    // Add affected person if available
    if ($affected_person) {
        $context .= ' for ' . $affected_person;
    }
    
    // Add description if available and different from context
    if ($description && !str_contains(strtolower($description), strtolower($context))) {
        $context .= ' - ' . $description;
    }
    
    return $context;
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
    <style>
        /* Enhanced Activity Section Styles */
        .activity-section {
            grid-column: 1 / -1;
            margin-top: 20px;
        }
        
        .activity-list {
            max-height: 600px;
            overflow-y: auto;
        }
        
        .activity-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover {
            background: #f8f9fa;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 16px;
        }
        
        .activity-icon-success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .activity-icon-info {
            background: rgba(23, 162, 184, 0.1);
            color: #17a2b8;
        }
        
        .activity-icon-warning {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .activity-icon-danger {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .activity-icon-secondary {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
        }
        
        .activity-content {
            flex: 1;
            min-width: 0;
        }
        
        .activity-text {
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
            line-height: 1.5;
        }
        
        .activity-text strong {
            color: #1a1a1a;
            font-weight: 600;
        }
        
        .activity-action {
            color: #666;
        }
        
        .activity-description {
            color: #888;
            font-size: 13px;
        }
        
        .activity-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 12px;
            color: #999;
        }
        
        .activity-time i {
            margin-right: 4px;
        }
        
        .activity-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .badge-create { background: #d4edda; color: #155724; }
        .badge-update { background: #fff3cd; color: #856404; }
        .badge-delete { background: #f8d7da; color: #721c24; }
        .badge-login { background: #d1ecf1; color: #0c5460; }
        .badge-approve { background: #d4edda; color: #155724; }
        .badge-reject { background: #f8d7da; color: #721c24; }
    </style>
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
                                        <th>Position</th>
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
                                                    <span class="status-badge status-present">Checked In</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-pending">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Enhanced Recent Activity Section -->
                    <div class="activity-section">
                        <div class="table-container">
                            <div class="table-header">
                                <h2><i class="fas fa-history"></i> Recent System Activity</h2>
                                <span class="activity-subtitle">Last 15 activities across all modules</span>
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
                                                <span class="activity-action">
                                                    <?php echo getEnhancedActivityDescription($activity); ?>
                                                </span>
                                            </div>
                                            <div class="activity-meta">
                                                <span class="activity-time">
                                                    <i class="far fa-clock"></i>
                                                    <?php echo timeAgo($activity['created_at']); ?>
                                                </span>
                                                <span class="activity-badge badge-<?php echo $activity['action']; ?>">
                                                    <?php echo strtoupper(str_replace('_', ' ', $activity['action'])); ?>
                                                </span>
                                                <?php if ($activity['table_name']): ?>
                                                <span class="activity-module">
                                                    <i class="far fa-folder"></i>
                                                    <?php echo ucfirst(str_replace('_', ' ', $activity['table_name'])); ?>
                                                </span>
                                                <?php endif; ?>
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