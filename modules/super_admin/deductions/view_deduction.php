<?php
/**
 * View Deduction - Modal Content
 * TrackSite Construction Management System
 */

define('TRACKSITE_INCLUDED', true);

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/settings.php';
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/auth.php';

requireSuperAdmin();

$deduction_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($deduction_id <= 0) {
    echo '<div style="text-align: center; padding: 40px;">
            <i class="fas fa-exclamation-circle" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></i>
            <p style="color: #666;">Invalid deduction ID</p>
          </div>';
    exit;
}

try {
    $stmt = $db->prepare("SELECT d.*, 
                         w.worker_code, w.first_name, w.last_name, w.position, w.daily_rate,
                         u.username as created_by_name
                         FROM deductions d
                         JOIN workers w ON d.worker_id = w.worker_id
                         LEFT JOIN users u ON d.created_by = u.user_id
                         WHERE d.deduction_id = ?");
    $stmt->execute([$deduction_id]);
    $deduction = $stmt->fetch();
    
    if (!$deduction) {
        echo '<div style="text-align: center; padding: 40px;">
                <i class="fas fa-exclamation-circle" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></i>
                <p style="color: #666;">Deduction not found</p>
              </div>';
        exit;
    }
} catch (PDOException $e) {
    error_log("View Deduction Error: " . $e->getMessage());
    echo '<div style="text-align: center; padding: 40px;">
            <i class="fas fa-exclamation-circle" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></i>
            <p style="color: #666;">Database error occurred</p>
          </div>';
    exit;
}

// Format deduction type
$type_labels = [
    'sss' => 'SSS Contribution',
    'philhealth' => 'PhilHealth',
    'pagibig' => 'Pag-IBIG Fund',
    'tax' => 'Withholding Tax',
    'loan' => 'Loan Repayment',
    'other' => 'Other'
];

$type_label = $type_labels[$deduction['deduction_type']] ?? ucfirst($deduction['deduction_type']);
$initials = getInitials($deduction['first_name'] . ' ' . $deduction['last_name']);
$status_class = 'status-' . $deduction['status'];
?>

<style>
    .deduction-details-grid {
        display: grid;
        gap: 20px;
    }
    
    .worker-profile-card {
        background: linear-gradient(135deg, rgba(218, 165, 32, 0.1), rgba(184, 134, 11, 0.1));
        border-radius: 10px;
        padding: 25px;
        text-align: center;
    }
    
    .worker-profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #DAA520, #B8860B);
        color: #1a1a1a;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 700;
        margin: 0 auto 15px;
    }
    
    .worker-profile-name {
        font-size: 20px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 5px;
    }
    
    .worker-profile-code {
        font-size: 14px;
        color: #666;
        margin-bottom: 10px;
    }
    
    .worker-card-position {
        display: inline-block;
        padding: 5px 15px;
        background: #fff;
        border-radius: 20px;
        font-size: 13px;
        color: #666;
        font-weight: 500;
    }
    
    .worker-info-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .worker-info-section:last-child {
        margin-bottom: 0;
    }
    
    .worker-info-section h3 {
        margin: 0 0 15px 0;
        font-size: 14px;
        color: #1a1a1a;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .info-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .info-row:last-child {
        margin-bottom: 0;
    }
    
    .info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .info-label {
        font-size: 11px;
        color: #666;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    
    .info-value {
        font-size: 14px;
        color: #1a1a1a;
        font-weight: 500;
    }
    
    .info-value.amount {
        font-size: 24px;
        font-weight: 700;
        color: #dc3545;
    }
    
    .deduction-type-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 12px;
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
    
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-applied {
        background: #d4edda;
        color: #155724;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
    }
</style>

<div class="deduction-details-grid">
    <div class="worker-profile-card">
        <div class="worker-profile-avatar"><?php echo $initials; ?></div>
        <div class="worker-profile-name">
            <?php echo htmlspecialchars($deduction['first_name'] . ' ' . $deduction['last_name']); ?>
        </div>
        <div class="worker-profile-code"><?php echo htmlspecialchars($deduction['worker_code']); ?></div>
        <div class="worker-card-position"><?php echo htmlspecialchars($deduction['position']); ?></div>
    </div>
    
    <div>
        <div class="worker-info-section">
            <h3><i class="fas fa-minus-circle"></i> Deduction Information</h3>
            <div class="info-row">
                <div class="info-item">
                    <span class="info-label">Type</span>
                    <span class="deduction-type-badge type-<?php echo $deduction['deduction_type']; ?>">
                        <?php echo $type_label; ?>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Amount</span>
                    <span class="info-value amount">₱<?php echo number_format($deduction['amount'], 2); ?></span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-item">
                    <span class="info-label">Deduction Date</span>
                    <span class="info-value"><?php echo date('F d, Y', strtotime($deduction['deduction_date'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="status-badge <?php echo $status_class; ?>">
                        <?php echo ucfirst($deduction['status']); ?>
                    </span>
                </div>
            </div>
            <?php if ($deduction['description']): ?>
            <div class="info-row">
                <div class="info-item" style="grid-column: 1 / -1;">
                    <span class="info-label">Description</span>
                    <span class="info-value"><?php echo htmlspecialchars($deduction['description']); ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="worker-info-section">
            <h3><i class="fas fa-info-circle"></i> System Information</h3>
            <div class="info-row">
                <div class="info-item">
                    <span class="info-label">Created By</span>
                    <span class="info-value"><?php echo htmlspecialchars($deduction['created_by_name'] ?? 'System'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Created Date</span>
                    <span class="info-value"><?php echo date('M d, Y h:i A', strtotime($deduction['created_at'])); ?></span>
                </div>
            </div>
            <?php if ($deduction['updated_at'] && $deduction['updated_at'] !== $deduction['created_at']): ?>
            <div class="info-row">
                <div class="info-item" style="grid-column: 1 / -1;">
                    <span class="info-label">Last Updated</span>
                    <span class="info-value"><?php echo date('M d, Y h:i A', strtotime($deduction['updated_at'])); ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($deduction['payroll_id']): ?>
        <div class="worker-info-section" style="background: linear-gradient(135deg, rgba(218, 165, 32, 0.1), rgba(184, 134, 11, 0.1));">
            <h3><i class="fas fa-link"></i> Payroll Link</h3>
            <div class="info-row">
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value">Linked to Payroll #<?php echo $deduction['payroll_id']; ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>