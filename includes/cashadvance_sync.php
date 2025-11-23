<?php
/**
 * Cash Advance - Deduction Synchronization
 * Include this in payroll generation to update cash advance status
 * 
 * Place in: includes/cashadvance_sync.php
 */

function syncCashAdvanceRepayments($db, $period_start, $period_end, $user_id) {
    try {
        // Get all cash advance deductions that were applied this period
        $stmt = $db->prepare("
            SELECT d.deduction_id, d.worker_id, d.amount, d.description
            FROM deductions d
            WHERE d.deduction_type = 'cashadvance'
            AND d.is_active = 1
            AND d.status = 'applied'
        ");
        $stmt->execute();
        $deductions = $stmt->fetchAll();
        
        foreach ($deductions as $ded) {
            // Find the related cash advance
            $stmt = $db->prepare("
                SELECT advance_id, amount, balance, repayment_amount
                FROM cash_advances
                WHERE worker_id = ?
                AND status IN ('approved', 'repaying')
                AND balance > 0
                ORDER BY request_date ASC
                LIMIT 1
            ");
            $stmt->execute([$ded['worker_id']]);
            $advance = $stmt->fetch();
            
            if ($advance) {
                // Record repayment
                $repayment_amount = min($ded['amount'], $advance['balance']);
                
                $stmt = $db->prepare("
                    INSERT INTO cash_advance_repayments 
                    (advance_id, repayment_date, amount, payment_method, notes, created_by)
                    VALUES (?, ?, ?, 'payroll_deduction', 'Automatic deduction from payroll', ?)
                ");
                $stmt->execute([
                    $advance['advance_id'],
                    $period_end,
                    $repayment_amount,
                    $user_id
                ]);
                
                // Update cash advance
                $new_balance = $advance['balance'] - $repayment_amount;
                $new_repayment_amount = $advance['repayment_amount'] + $repayment_amount;
                $new_status = $new_balance <= 0 ? 'completed' : 'repaying';
                
                $stmt = $db->prepare("
                    UPDATE cash_advances 
                    SET balance = ?,
                        repayment_amount = ?,
                        status = ?,
                        completed_at = CASE WHEN ? = 'completed' THEN NOW() ELSE completed_at END,
                        updated_at = NOW()
                    WHERE advance_id = ?
                ");
                $stmt->execute([
                    $new_balance,
                    $new_repayment_amount,
                    $new_status,
                    $new_status,
                    $advance['advance_id']
                ]);
                
                // If completed, deactivate the deduction
                if ($new_status === 'completed') {
                    $stmt = $db->prepare("
                        UPDATE deductions 
                        SET is_active = 0,
                            status = 'cancelled',
                            updated_at = NOW()
                        WHERE deduction_id = ?
                    ");
                    $stmt->execute([$ded['deduction_id']]);
                }
            }
        }
        
        return true;
        
    } catch (PDOException $e) {
        error_log("Cash Advance Sync Error: " . $e->getMessage());
        return false;
    }
}
?>