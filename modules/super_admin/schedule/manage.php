<?php
/**
 * Manage Schedules Page - Grid View
 * TrackSite Construction Management System
 */

define('TRACKSITE_INCLUDED', true);

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/settings.php';
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/auth.php';

requireSuperAdmin();

$full_name = $_SESSION['full_name'] ?? 'Administrator';
$flash = getFlashMessage();

// Get all workers with their schedules
try {
    $sql = "SELECT 
            w.worker_id,
            w.worker_code,
            w.first_name,
            w.last_name,
            w.position,
            GROUP_CONCAT(
                CONCAT(s.day_of_week, ':', s.start_time, '-', s.end_time, ':', s.is_active)
                ORDER BY FIELD(s.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')
                SEPARATOR '|'
            ) as schedule_data
            FROM workers w
            LEFT JOIN schedules s ON w.worker_id = s.worker_id
            WHERE w.employment_status = 'active' AND w.is_archived = FALSE
            GROUP BY w.worker_id
            ORDER BY w.first_name, w.last_name";
    
    $stmt = $db->query($sql);
    $workers = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Schedule Query Error: " . $e->getMessage());
    $workers = [];
}

// Parse schedule data for each worker
foreach ($workers as &$worker) {
    $worker['schedules'] = [];
    if (!empty($worker['schedule_data'])) {
        $schedules_raw = explode('|', $worker['schedule_data']);
        foreach ($schedules_raw as $schedule_raw) {
            $parts = explode(':', $schedule_raw);
            if (count($parts) >= 3) {
                $worker['schedules'][$parts[0]] = [
                    'time' => $parts[1],
                    'is_active' => $parts[2] == '1'
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Schedules - <?php echo SYSTEM_NAME; ?></title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" 
          integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" 
          crossorigin="anonymous" />
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dashboard.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/schedule.css">
</head>
<body>
    <div class="container">
        <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>
        
        <div class="main">
            <?php include __DIR__ . '/../../../includes/topbar.php'; ?>
            
            <div class="schedule-content">
                
                <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>" id="flashMessage">
                    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <span><?php echo htmlspecialchars($flash['message']); ?></span>
                    <button class="alert-close" onclick="closeAlert('flashMessage')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php endif; ?>
                
                <div class="page-header">
                    <div class="header-left">
                        <h1><i class="fas fa-cog"></i> Manage Worker Schedules</h1>
                        <p class="subtitle">View and manage all worker schedules in one place</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-secondary" onclick="window.location.href='index.php'">
                            <i class="fas fa-list"></i> List View
                        </button>
                        <button class="btn btn-primary" onclick="window.location.href='add.php'">
                            <i class="fas fa-plus"></i> Add Schedule
                        </button>
                    </div>
                </div>
                
                <!-- Info Banner -->
                <div class="info-banner">
                    <div class="info-banner-content">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <strong>Schedule Overview:</strong>
                            <p>This view shows all workers and their weekly schedules. Click on any worker card to manage their schedule.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Schedule Grid -->
                <div class="schedule-grid">
                    <?php if (empty($workers)): ?>
                        <div class="no-data" style="grid-column: 1 / -1;">
                            <i class="fas fa-users-slash"></i>
                            <p>No active workers found</p>
                            <small>Add workers to create schedules</small>
                        </div>
                    <?php else: ?>
                        <?php foreach ($workers as $worker): ?>
                        <div class="schedule-card">
                            <div class="schedule-card-header">
                                <div class="schedule-card-avatar">
                                    <?php echo getInitials($worker['first_name'] . ' ' . $worker['last_name']); ?>
                                </div>
                                <div class="schedule-card-info">
                                    <h3><?php echo htmlspecialchars($worker['first_name'] . ' ' . $worker['last_name']); ?></h3>
                                    <p><?php echo htmlspecialchars($worker['worker_code']); ?> • <?php echo htmlspecialchars($worker['position']); ?></p>
                                </div>
                            </div>
                            
                            <div class="schedule-days">
                                <?php
                                $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                                $day_labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                                
                                foreach ($days as $index => $day):
                                    $has_schedule = isset($worker['schedules'][$day]);
                                    $is_active = $has_schedule && $worker['schedules'][$day]['is_active'];
                                    $class = $is_active ? 'active' : '';
                                ?>
                                <div class="day-chip <?php echo $class; ?>" 
                                     title="<?php echo ucfirst($day); ?>: <?php echo $has_schedule ? $worker['schedules'][$day]['time'] : 'No schedule'; ?>">
                                    <?php echo $day_labels[$index]; ?>
                                    <?php if ($has_schedule): ?>
                                        <small><?php echo date('g A', strtotime(explode('-', $worker['schedules'][$day]['time'])[0])); ?></small>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="schedule-info">
                                <?php
                                $active_count = 0;
                                foreach ($worker['schedules'] as $schedule) {
                                    if ($schedule['is_active']) $active_count++;
                                }
                                ?>
                                <div class="schedule-stat">
                                    <i class="fas fa-calendar-check"></i>
                                    <span><?php echo $active_count; ?> active day<?php echo $active_count != 1 ? 's' : ''; ?></span>
                                </div>
                                <?php if ($active_count > 0): ?>
                                    <?php
                                    // Get first active schedule time
                                    foreach ($worker['schedules'] as $schedule) {
                                        if ($schedule['is_active']) {
                                            $times = explode('-', $schedule['time']);
                                            echo '<div class="schedule-stat">';
                                            echo '<i class="fas fa-clock"></i>';
                                            echo '<span>' . date('g:i A', strtotime($times[0])) . ' - ' . date('g:i A', strtotime($times[1])) . '</span>';
                                            echo '</div>';
                                            break;
                                        }
                                    }
                                    ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="schedule-card-actions">
                                <?php if (empty($worker['schedules'])): ?>
                                    <button class="btn btn-sm btn-primary" 
                                            onclick="window.location.href='add.php?worker_id=<?php echo $worker['worker_id']; ?>'">
                                        <i class="fas fa-plus"></i> Add Schedule
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" 
                                            onclick="viewWorkerSchedule(<?php echo $worker['worker_id']; ?>)">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button class="btn btn-sm btn-primary" 
                                            onclick="window.location.href='add.php?worker_id=<?php echo $worker['worker_id']; ?>'">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
    </div>
    
    <script src="<?php echo JS_URL; ?>/dashboard.js"></script>
    <script src="<?php echo JS_URL; ?>/schedule.js"></script>
    <script>
        function viewWorkerSchedule(workerId) {
            window.location.href = 'index.php?worker=' + workerId;
        }
        
        function closeAlert(id) {
            const alert = document.getElementById(id);
            if (alert) {
                alert.style.animation = 'slideUp 0.3s ease-in';
                setTimeout(() => alert.remove(), 300);
            }
        }
        
        // Auto-dismiss flash message
        setTimeout(() => {
            const flashMessage = document.getElementById('flashMessage');
            if (flashMessage) closeAlert('flashMessage');
        }, 5000);
    </script>
    
    <style>
        .info-banner {
            background: linear-gradient(135deg, rgba(218, 165, 32, 0.1), rgba(184, 134, 11, 0.1));
            border-left: 4px solid #DAA520;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .info-banner-content {
            display: flex;
            gap: 15px;
            align-items: start;
        }
        
        .info-banner-content i {
            font-size: 24px;
            color: #DAA520;
            margin-top: 2px;
        }
        
        .info-banner-content strong {
            display: block;
            margin-bottom: 5px;
            color: #1a1a1a;
        }
        
        .info-banner-content p {
            margin: 0;
            color: #666;
            line-height: 1.5;
        }
        
        .day-chip small {
            display: block;
            font-size: 9px;
            margin-top: 2px;
            opacity: 0.8;
        }
        
        .schedule-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .schedule-stat {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #666;
        }
        
        .schedule-stat i {
            color: #DAA520;
            width: 16px;
        }
        
        @keyframes slideUp {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }
    </style>
</body>
</html>