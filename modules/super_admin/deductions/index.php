<?php
/**
 * Deductions Management - Main Page
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

// Get filter parameters
$worker_filter = isset($_GET['worker']) ? intval($_GET['worker']) : 0;
$type_filter = isset($_GET['type']) ? sanitizeString($_GET['type']) : '';
$status_filter = isset($_GET['status']) ? sanitizeString($_GET['status']) : '';
$date_from = isset($_GET['date_from']) ? sanitizeString($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitizeString($_GET['date_to']) : '';
$search_query = isset($_GET['search']) ? sanitizeString($_GET['search']) : '';

// Handle delete action
if (isset($_POST['delete_deduction'])) {
    $deduction_id = intval($_POST['deduction_id']);
    
    try {
        // Get deduction details before deletion
        $stmt = $db->prepare("SELECT d.*, w.first_name, w.last_name FROM deductions d
                             JOIN workers w ON d.worker_id = w.worker_id
                             WHERE d.deduction_id = ?");
        $stmt->execute([$deduction_id]);
        $deduction = $stmt->fetch();
        
        if ($deduction) {
            $stmt = $db->prepare("DELETE FROM deductions WHERE deduction_id = ?");
            $stmt->execute([$deduction_id]);
            
            logActivity($db, getCurrentUserId(), 'delete_deduction', 'deductions', $deduction_id,
                "Deleted {$deduction['deduction_type']} deduction for {$deduction['first_name']} {$deduction['last_name']}");
            
            setFlashMessage('Deduction deleted successfully', 'success');
        }
    } catch (PDOException $e) {
        error_log("Delete Error: " . $e->getMessage());
        setFlashMessage('Failed to delete deduction', 'error');
    }
    
    redirect($_SERVER['PHP_SELF']);
}

// Build query
$sql = "SELECT d.*, w.worker_code, w.first_name, w.last_name, w.position,
        u.username as created_by_name
        FROM deductions d
        JOIN workers w ON d.worker_id = w.worker_id
        LEFT JOIN users u ON d.created_by = u.user_id
        WHERE w.is_archived = FALSE";

$params = [];

if ($worker_filter > 0) {
    $sql .= " AND d.worker_id = ?";
    $params[] = $worker_filter;
}

if (!empty($type_filter)) {
    $sql .= " AND d.deduction_type = ?";
    $params[] = $type_filter;
}

if (!empty($status_filter)) {
    $sql .= " AND d.status = ?";
    $params[] = $status_filter;
}

if (!empty($date_from)) {
    $sql .= " AND d.deduction_date >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $sql .= " AND d.deduction_date <= ?";
    $params[] = $date_to;
}

if (!empty($search_query)) {
    $sql .= " AND (w.first_name LIKE ? OR w.last_name LIKE ? OR w.worker_code LIKE ? OR d.description LIKE ?)";
    $search_param = '%' . $search_query . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql .= " ORDER BY d.deduction_date DESC, d.created_at DESC";

try {
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $deductions = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Deductions Query Error: " . $e->getMessage());
    $deductions = [];
}

// Calculate statistics
$total_deductions = 0;
$total_sss = 0;
$total_philhealth = 0;
$total_pagibig = 0;
$total_other = 0;

foreach ($deductions as $ded) {
    if ($ded['status'] === 'applied') {
        $total_deductions += $ded['amount'];
        
        switch ($ded['deduction_type']) {
            case 'sss':
                $total_sss += $ded['amount'];
                break;
            case 'philhealth':
                $total_philhealth += $ded['amount'];
                break;
            case 'pagibig':
                $total_pagibig += $ded['amount'];
                break;
            default:
                $total_other += $ded['amount'];
                break;
        }
    }
}

// Get all active workers for filter
try {
    $stmt = $db->query("SELECT worker_id, worker_code, first_name, last_name 
                        FROM workers 
                        WHERE employment_status = 'active' AND is_archived = FALSE
                        ORDER BY first_name, last_name");
    $workers = $stmt->fetchAll();
} catch (PDOException $e) {
    $workers = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deductions Management - <?php echo SYSTEM_NAME; ?></title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" 
          integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" 
          crossorigin="anonymous" />
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dashboard.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/workers.css">
</head>
<body>
    <div class="container">
        <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>
        
        <div class="main">
            <?php include __DIR__ . '/../../../includes/topbar.php'; ?>
            
            <div class="workers-content">
                
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
                        <h1><i class="fas fa-minus-circle"></i> Deductions Management</h1>
                        <p class="subtitle">Manage worker deductions (SSS, PhilHealth, Pag-IBIG, etc.)</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="window.location.href='add.php'">
                            <i class="fas fa-plus"></i> Add Deduction
                        </button>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="stats-cards" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 30px;">
                    <div class="stat-card card-blue">
                        <div class="card-content">
                            <div class="card-value">₱<?php echo number_format($total_sss, 2); ?></div>
                            <div class="card-label">SSS Contributions</div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                    </div>
                    
                    <div class="stat-card card-green">
                        <div class="card-content">
                            <div class="card-value">₱<?php echo number_format($total_philhealth, 2); ?></div>
                            <div class="card-label">PhilHealth</div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                    </div>
                    
                    <div class="stat-card card-orange">
                        <div class="card-content">
                            <div class="card-value">₱<?php echo number_format($total_pagibig, 2); ?></div>
                            <div class="card-label">Pag-IBIG Fund</div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-home"></i>
                        </div>
                    </div>
                    
                    <div class="stat-card card-purple">
                        <div class="card-content">
                            <div class="card-value">₱<?php echo number_format($total_deductions, 2); ?></div>
                            <div class="card-label">Total Deductions</div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="filter-card">
                    <form method="GET" action="" id="filterForm">
                        <div class="filter-row">
                            <div class="filter-group">
                                <select name="worker" onchange="document.getElementById('filterForm').submit()">
                                    <option value="0">All Workers</option>
                                    <?php foreach ($workers as $w): ?>
                                        <option value="<?php echo $w['worker_id']; ?>" 
                                                <?php echo $worker_filter == $w['worker_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($w['first_name'] . ' ' . $w['last_name'] . ' (' . $w['worker_code'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <select name="type" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">All Types</option>
                                    <option value="sss" <?php echo $type_filter === 'sss' ? 'selected' : ''; ?>>SSS</option>
                                    <option value="philhealth" <?php echo $type_filter === 'philhealth' ? 'selected' : ''; ?>>PhilHealth</option>
                                    <option value="pagibig" <?php echo $type_filter === 'pagibig' ? 'selected' : ''; ?>>Pag-IBIG</option>
                                    <option value="tax" <?php echo $type_filter === 'tax' ? 'selected' : ''; ?>>Tax</option>
                                    <option value="loan" <?php echo $type_filter === 'loan' ? 'selected' : ''; ?>>Loan</option>
                                    <option value="other" <?php echo $type_filter === 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <select name="status" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="applied" <?php echo $status_filter === 'applied' ? 'selected' : ''; ?>>Applied</option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <input type="date" 
                                       name="date_from" 
                                       value="<?php echo htmlspecialchars($date_from); ?>" 
                                       placeholder="From Date">
                            </div>
                            
                            <div class="filter-group">
                                <input type="date" 
                                       name="date_to" 
                                       value="<?php echo htmlspecialchars($date_to); ?>" 
                                       placeholder="To Date">
                            </div>
                            
                            <div class="filter-group" style="flex: 2;">
                                <input type="text" 
                                       name="search" 
                                       value="<?php echo htmlspecialchars($search_query); ?>"
                                       placeholder="Search deductions...">
                            </div>
                            
                            <button type="submit" class="btn btn-filter">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            
                            <?php if (!empty($worker_filter) || !empty($type_filter) || !empty($status_filter) || !empty($date_from) || !empty($date_to) || !empty($search_query)): ?>
                            <button type="button" 
                                    class="btn btn-secondary" 
                                    onclick="window.location.href='index.php'">
                                <i class="fas fa-times"></i> Clear
                            </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <!-- Deductions Table -->
                <div class="workers-table-card">
                    <div class="table-info">
                        <span>Showing <?php echo count($deductions); ?> deduction(s)</span>
                    </div>
                    
                    <div class="table-wrapper">
                        <table class="workers-table">
                            <thead>
                                <tr>
                                    <th>Worker</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($deductions)): ?>
                                <tr>
                                    <td colspan="8" class="no-data">
                                        <i class="fas fa-minus-circle"></i>
                                        <p>No deductions found</p>
                                        <button class="btn btn-sm btn-primary" onclick="window.location.href='add.php'">
                                            <i class="fas fa-plus"></i> Add First Deduction
                                        </button>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($deductions as $ded): ?>
                                    <tr>
                                        <td>
                                            <div class="worker-info">
                                                <div class="worker-avatar">
                                                    <?php echo getInitials($ded['first_name'] . ' ' . $ded['last_name']); ?>
                                                </div>
                                                <div>
                                                    <div class="worker-name">
                                                        <?php echo htmlspecialchars($ded['first_name'] . ' ' . $ded['last_name']); ?>
                                                    </div>
                                                    <div class="worker-code">
                                                        <?php echo htmlspecialchars($ded['worker_code']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="deduction-type-badge type-<?php echo $ded['deduction_type']; ?>">
                                                <?php echo strtoupper($ded['deduction_type']); ?>
                                            </span>
                                        </td>
                                        <td><strong style="color: #dc3545;">₱<?php echo number_format($ded['amount'], 2); ?></strong></td>
                                        <td>
                                            <small><?php echo htmlspecialchars($ded['description'] ?? 'No description'); ?></small>
                                        </td>
                                        <td><?php echo formatDate($ded['deduction_date']); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $ded['status']; ?>">
                                                <?php echo ucfirst($ded['status']); ?>
                                            </span>
                                        </td>
                                        <td><small><?php echo htmlspecialchars($ded['created_by_name'] ?? 'System'); ?></small></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="action-btn btn-view" 
                                                        onclick="viewDeduction(<?php echo $ded['deduction_id']; ?>)"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="action-btn btn-edit" 
                                                        onclick="window.location.href='edit.php?id=<?php echo $ded['deduction_id']; ?>'"
                                                        title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this deduction?')">
                                                    <input type="hidden" name="deduction_id" value="<?php echo $ded['deduction_id']; ?>">
                                                    <button type="submit" 
                                                            name="delete_deduction"
                                                            class="action-btn btn-delete" 
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- View Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Deduction Details</h2>
                <button class="modal-close" onclick="closeModal('viewModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #DAA520;"></i>
                    <p style="margin-top: 15px; color: #666;">Loading details...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo JS_URL; ?>/dashboard.js"></script>
    <script src="<?php echo JS_URL; ?>/workers.js"></script>
    
    <script>
        function viewDeduction(deductionId) {
            showModal('viewModal');
            
            fetch(`view_deduction.php?id=${deductionId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('modalBody').innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('modalBody').innerHTML = `
                        <div style="text-align: center; padding: 40px;">
                            <i class="fas fa-exclamation-circle" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></i>
                            <p style="color: #666;">Failed to load deduction details</p>
                        </div>
                    `;
                });
        }
        
        function showModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
        
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
        
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.style.animation = 'slideUp 0.3s ease-in';
                setTimeout(() => alert.remove(), 300);
            }
        }
        
        // Auto-dismiss alerts
        setTimeout(() => {
            const flashMessage = document.getElementById('flashMessage');
            if (flashMessage) closeAlert('flashMessage');
        }, 5000);
        
        // Close modal on outside click
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
    </script>
    
    <style>
        .deduction-type-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .type-sss {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .type-philhealth {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .type-pagibig {
            background: #e8f5e9;
            color: #388e3c;
        }
        
        .type-tax {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .type-loan {
            background: #ffebee;
            color: #c62828;
        }
        
        .type-other {
            background: #f5f5f5;
            color: #616161;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: #fff;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            animation: slideDown 0.3s ease-out;
        }
        
        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 20px;
            color: #1a1a1a;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            color: #666;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .modal-close:hover {
            background: #f0f0f0;
            color: #1a1a1a;
        }
        
        .modal-body {
            padding: 25px;
            overflow-y: auto;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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