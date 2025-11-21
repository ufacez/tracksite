<?php
/**
 * Cash Advance Repayment System
 * TrackSite Construction Management System
 * FILE: modules/super_admin/cashadvance/repayment.php
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

$advance_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($advance_id <= 0) {
    setFlashMessage('Invalid cash advance ID', 'error');
    redirect(BASE_URL . '/modules/super_admin/cashadvance/index.php');
}

// Get cash advance details
try {
    $stmt = $db->prepare("SELECT ca.*, 
                         w.worker_code, w.first_name, w.last_name, w.position, w.daily_rate
                         FROM cash_advances ca
                         JOIN workers w ON ca.worker_id = w.worker_id
                         WHERE ca.advance_id = ?");
    $stmt->execute([$advance_id]);
    $advance = $stmt->fetch();
    
    if (!$advance) {
        setFlashMessage('Cash advance not found', 'error');
        redirect(BASE_URL . '/modules/super_admin/cashadvance/index.php');
    }
    
    if ($advance['status'] !== 'approved' && $advance['status'] !== 'repaying') {
        setFlashMessage('This cash advance cannot accept repayments', 'error');
        redirect(BASE_URL . '/modules/super_admin/cashadvance/index.php');
    }
    
} catch (PDOException $e) {
    error_log("Fetch Cash Advance Error: " . $e->getMessage());
    setFlashMessage('Database error occurred', 'error');
    redirect(BASE_URL . '/modules/super_admin/cashadvance/index.php');
}

// Handle repayment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_repayment'])) {
    $errors = [];
    
    $repayment_amount = isset($_POST['repayment_amount']) ? floatval($_POST['repayment_amount']) : 0;
    $repayment_date = isset($_POST['repayment_date']) ? sanitizeString($_POST['repayment_date']) : date('Y-m-d');
    $payment_method = isset($_POST['payment_method']) ? sanitizeString($_POST['payment_method']) : 'cash';
    $notes = isset($_POST['notes']) ? sanitizeString($_POST['notes']) : '';
    
    // Validation
    if ($repayment_amount <= 0) {
        $errors[] = 'Repayment amount must be greater than zero';
    }
    
    if ($repayment_amount > $advance['balance']) {
        $errors[] = 'Repayment amount cannot exceed remaining balance (₱' . number_format($advance['balance'], 2) . ')';
    }
    
    if (empty($errors)) {
        try {
            $db->beginTransaction();
            
            // Calculate new balance
            $new_balance = $advance['balance'] - $repayment_amount;
            $new_status = $new_balance <= 0 ? 'completed' : 'repaying';
            
            // Update cash advance
            $stmt = $db->prepare("UPDATE cash_advances 
                SET balance = ?,
                    status = ?,
                    updated_at = NOW()
                WHERE advance_id = ?");
            $stmt->execute([$new_balance, $new_status, $advance_id]);
            
            // Record repayment
            $stmt = $db->prepare("INSERT INTO cash_advance_repayments 
                (advance_id, repayment_date, amount, payment_method, notes, processed_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $advance_id,
                $repayment_date,
                $repayment_amount,
                $payment_method,
                $notes,
                getCurrentUserId()
            ]);
            
            // Log activity
            $worker_name = $advance['first_name'] . ' ' . $advance['last_name'];
            logActivity($db, getCurrentUserId(), 'add_repayment', 'cash_advance_repayments', $advance_id,
                "Added repayment of ₱" . number_format($repayment_amount, 2) . " for {$worker_name}");
            
            $db->commit();
            
            $message = 'Repayment recorded successfully!';
            if ($new_status === 'completed') {
                $message .= ' Cash advance is now fully paid.';
            }
            
            setFlashMessage($message, 'success');
            redirect(BASE_URL . '/modules/super_admin/cashadvance/index.php');
            
        } catch (PDOException $e) {
            $db->rollBack();
            error_log("Repayment Error: " . $e->getMessage());
            $errors[] = 'Failed to process repayment. Please try again.';
        }
    }
}

// Get repayment history
try {
    $stmt = $db->prepare("SELECT r.*, u.username as processed_by_name
                         FROM cash_advance_repayments r
                         LEFT JOIN users u ON r.processed_by = u.user_id
                         WHERE r.advance_id = ?
                         ORDER BY r.repayment_date DESC, r.created_at DESC");
    $stmt->execute([$advance_id]);
    $repayments = $stmt->fetchAll();
} catch (PDOException $e) {
    $repayments = [];
}

$paid_amount = $advance['amount'] - $advance['balance'];
$payment_percentage = $advance['amount'] > 0 ? ($paid_amount / $advance['amount']) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Advance Repayment - <?php echo SYSTEM_NAME; ?></title>
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css"/>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dashboard.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/cashadvance.css">
</head>
<body>
    <div class="container">
        <?php include __DIR__ . '/../../../includes/sidebar.php'; ?>
        
        <div class="main">
            <?php include __DIR__ . '/../../../includes/topbar.php'; ?>
            
            <div class="cashadvance-content">
                
                <?php if (!empty($errors)): ?>
                <div class="alert alert-error" id="errorMessage">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                    <button class="alert-close" onclick="closeAlert('errorMessage')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <?php endif; ?>
                
                <div class="page-header">
                    <div class="header-left">
                        <h1><i class="fas fa-money-bill-wave"></i> Process Repayment</h1>
                        <p class="subtitle">Record cash advance repayment</p>
                    </div>
                    <div class="header-actions">
                        <button class="btn btn-secondary" onclick="window.history.back()">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                    </div>
                </div>
                
                <!-- Worker & Advance Info -->
                <div class="repayment-card">
                    <h3 style="margin: 0 0 20px 0;">
                        <i class="fas fa-user"></i> Worker Information
                    </h3>
                    <div class="repayment-info">
                        <div class="info-item">
                            <span class="info-label">Worker</span>
                            <span class="info-value">
                                <?php echo htmlspecialchars($advance['first_name'] . ' ' . $advance['last_name']); ?>
                            </span>
                            <small><?php echo htmlspecialchars($advance['worker_code']); ?></small>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Position</span>
                            <span class="info-value"><?php echo htmlspecialchars($advance['position']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Request Date</span>
                            <span class="info-value">
                                <?php echo date('M d, Y', strtotime($advance['request_date'])); ?>
                            </span>
                        </div>
                    </div>
                    
                    <h3 style="margin: 20px 0 20px 0;">
                        <i class="fas fa-chart-line"></i> Repayment Progress
                    </h3>
                    <div class="repayment-info">
                        <div class="info-item">
                            <span class="info-label">Original Amount</span>
                            <span class="info-value large" style="color: #dc3545;">
                                ₱<?php echo number_format($advance['amount'], 2); ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Paid Amount</span>
                            <span class="info-value large" style="color: #28a745;">
                                ₱<?php echo number_format($paid_amount, 2); ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Remaining Balance</span>
                            <span class="info-value large" style="color: #ffc107;">
                                ₱<?php echo number_format($advance['balance'], 2); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $payment_percentage; ?>%">
                            <?php echo number_format($payment_percentage, 1); ?>% Paid
                        </div>
                    </div>
                </div>
                
                <!-- Repayment Form -->
                <div class="form-card">
                    <form method="POST" action="">
                        
                        <div class="form-section">
                            <h3><i class="fas fa-money-check-alt"></i> Repayment Details</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="repayment_amount">Repayment Amount *</label>
                                    <div class="input-with-prefix">
                                        <span class="input-prefix">₱</span>
                                        <input type="number" 
                                               name="repayment_amount" 
                                               id="repayment_amount" 
                                               step="0.01" 
                                               min="0.01" 
                                               max="<?php echo $advance['balance']; ?>"
                                               placeholder="0.00"
                                               required>
                                    </div>
                                    <small>Maximum: ₱<?php echo number_format($advance['balance'], 2); ?></small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="repayment_date">Repayment Date *</label>
                                    <input type="date" 
                                           name="repayment_date" 
                                           id="repayment_date" 
                                           value="<?php echo date('Y-m-d'); ?>"
                                           required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="payment_method">Payment Method *</label>
                                    <select name="payment_method" id="payment_method" required>
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="check">Check</option>
                                        <option value="payroll_deduction">Payroll Deduction</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-group full-width">
                                    <label for="notes">Notes (Optional)</label>
                                    <textarea name="notes" 
                                              id="notes" 
                                              rows="3" 
                                              placeholder="Enter any additional notes about this repayment..."></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="button" 
                                    class="btn btn-secondary" 
                                    onclick="document.getElementById('repayment_amount').value = <?php echo $advance['balance']; ?>">
                                <i class="fas fa-check-double"></i> Pay Full Balance
                            </button>
                            <button type="submit" name="submit_repayment" class="btn btn-primary">
                                <i class="fas fa-save"></i> Process Repayment
                            </button>
                        </div>
                        
                    </form>
                </div>
                
                <!-- Repayment History -->
                <?php if (!empty($repayments)): ?>
                <div class="table-card" style="margin-top: 30px;">
                    <div class="table-info">
                        <span><i class="fas fa-history"></i> Repayment History (<?php echo count($repayments); ?> records)</span>
                    </div>
                    <table class="advances-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Processed By</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($repayments as $rep): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($rep['repayment_date'])); ?></td>
                                <td>
                                    <strong style="color: #28a745;">
                                        ₱<?php echo number_format($rep['amount'], 2); ?>
                                    </strong>
                                </td>
                                <td>
                                    <span style="text-transform: capitalize;">
                                        <?php echo str_replace('_', ' ', $rep['payment_method']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($rep['processed_by_name'] ?? 'System'); ?></td>
                                <td>
                                    <small><?php echo htmlspecialchars($rep['notes'] ?: '--'); ?></small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
    
    <script src="<?php echo JS_URL; ?>/dashboard.js"></script>
    
    <script>
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.style.animation = 'slideUp 0.3s ease-in';
                setTimeout(() => alert.remove(), 300);
            }
        }
        
        setTimeout(() => {
            const errorMessage = document.getElementById('errorMessage');
            if (errorMessage) closeAlert('errorMessage');
        }, 5000);
        
        // Update remaining balance display when amount changes
        document.getElementById('repayment_amount').addEventListener('input', function() {
            const amount = parseFloat(this.value) || 0;
            const balance = <?php echo $advance['balance']; ?>;
            const remaining = balance - amount;
            
            if (remaining < 0) {
                this.value = balance;
            }
        });
    </script>
</body>
</html>