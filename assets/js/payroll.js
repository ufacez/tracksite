/**
 * Payroll JavaScript Functions
 * TrackSite Construction Management System
 */

// View Payroll Details
function viewPayroll(workerId, periodStart, periodEnd) {
    const modal = document.getElementById('viewModal');
    const modalBody = document.getElementById('modalBody');
    
    // Show modal with loading state
    modal.style.display = 'flex';
    modalBody.innerHTML = `
        <div style="text-align: center; padding: 40px;">
            <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #DAA520;"></i>
            <p style="margin-top: 15px; color: #666;">Loading payroll details...</p>
        </div>
    `;
    
    // Get base URL from the current location
    const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.indexOf('/modules/'));
    
    fetch(`${baseUrl}/api/payroll.php?action=get&worker_id=${workerId}&period_start=${periodStart}&period_end=${periodEnd}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const payroll = data.data;
                const netPay = payroll.gross_pay - payroll.total_deductions;
                
                modalBody.innerHTML = `
                    <div class="payroll-detail-grid">
                        <!-- Worker Information -->
                        <div class="detail-section">
                            <h3><i class="fas fa-user"></i> Worker Information</h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <label>Name:</label>
                                    <span>${payroll.first_name} ${payroll.last_name}</span>
                                </div>
                                <div class="detail-item">
                                    <label>Worker Code:</label>
                                    <span>${payroll.worker_code}</span>
                                </div>
                                <div class="detail-item">
                                    <label>Position:</label>
                                    <span>${payroll.position}</span>
                                </div>
                                <div class="detail-item">
                                    <label>Daily Rate:</label>
                                    <span>₱${parseFloat(payroll.daily_rate).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pay Period Information -->
                        <div class="detail-section">
                            <h3><i class="fas fa-calendar"></i> Pay Period</h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <label>Period:</label>
                                    <span>${new Date(payroll.period_start).toLocaleDateString()} - ${new Date(payroll.period_end).toLocaleDateString()}</span>
                                </div>
                                <div class="detail-item">
                                    <label>Days Worked:</label>
                                    <span>${payroll.days_worked} days</span>
                                </div>
                                <div class="detail-item">
                                    <label>Total Hours:</label>
                                    <span>${parseFloat(payroll.total_hours).toFixed(2)} hours</span>
                                </div>
                                <div class="detail-item">
                                    <label>Average Hours/Day:</label>
                                    <span>${payroll.days_worked > 0 ? (payroll.total_hours / payroll.days_worked).toFixed(2) : '0.00'} hours</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Earnings -->
                        <div class="detail-section">
                            <h3><i class="fas fa-money-bill-wave"></i> Earnings</h3>
                            <div class="earnings-breakdown">
                                <div class="breakdown-item">
                                    <span>Basic Pay (${payroll.days_worked} days × ₱${parseFloat(payroll.daily_rate).toFixed(2)})</span>
                                    <strong>₱${parseFloat(payroll.gross_pay).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
                                </div>
                                <div class="breakdown-total">
                                    <span>Gross Pay</span>
                                    <strong>₱${parseFloat(payroll.gross_pay).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Deductions -->
                        <div class="detail-section">
                            <h3><i class="fas fa-minus-circle"></i> Deductions</h3>
                            <div class="deductions-breakdown">
                                ${payroll.deductions && payroll.deductions.length > 0 ? 
                                    payroll.deductions.map(ded => `
                                        <div class="breakdown-item">
                                            <span>${ded.deduction_type.toUpperCase()}${ded.description ? ' - ' + ded.description : ''}</span>
                                            <strong>₱${parseFloat(ded.amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
                                        </div>
                                    `).join('') 
                                : '<p style="color: #999; text-align: center; padding: 10px;">No deductions for this period</p>'}
                                <div class="breakdown-total">
                                    <span>Total Deductions</span>
                                    <strong style="color: #dc3545;">₱${parseFloat(payroll.total_deductions).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Net Pay -->
                        <div class="detail-section net-pay-section">
                            <h3><i class="fas fa-hand-holding-usd"></i> Net Pay</h3>
                            <div class="net-pay-amount">
                                <span>Take Home Pay</span>
                                <strong>₱${netPay.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
                            </div>
                            <div class="payment-status">
                                <span>Status:</span>
                                <span class="status-badge status-${payroll.payment_status || 'unpaid'}">${(payroll.payment_status || 'unpaid').toUpperCase()}</span>
                            </div>
                        </div>
                    </div>
                    
                    <style>
                        .payroll-detail-grid {
                            display: grid;
                            gap: 20px;
                        }
                        .detail-section {
                            background: #f8f9fa;
                            border-radius: 8px;
                            padding: 20px;
                        }
                        .detail-section h3 {
                            margin: 0 0 15px 0;
                            font-size: 16px;
                            color: #1a1a1a;
                            display: flex;
                            align-items: center;
                            gap: 8px;
                        }
                        .detail-grid {
                            display: grid;
                            grid-template-columns: repeat(2, 1fr);
                            gap: 15px;
                        }
                        .detail-item {
                            display: flex;
                            flex-direction: column;
                            gap: 5px;
                        }
                        .detail-item label {
                            font-size: 11px;
                            color: #666;
                            text-transform: uppercase;
                            font-weight: 600;
                        }
                        .detail-item span {
                            font-size: 14px;
                            color: #1a1a1a;
                            font-weight: 500;
                        }
                        .earnings-breakdown, .deductions-breakdown {
                            display: flex;
                            flex-direction: column;
                            gap: 10px;
                        }
                        .breakdown-item {
                            display: flex;
                            justify-content: space-between;
                            padding: 10px;
                            background: #fff;
                            border-radius: 6px;
                        }
                        .breakdown-total {
                            display: flex;
                            justify-content: space-between;
                            padding: 15px;
                            background: #fff;
                            border-radius: 6px;
                            border-top: 2px solid #DAA520;
                            margin-top: 10px;
                        }
                        .net-pay-section {
                            background: linear-gradient(135deg, rgba(218, 165, 32, 0.1), rgba(184, 134, 11, 0.1));
                        }
                        .net-pay-amount {
                            display: flex;
                            justify-content: space-between;
                            padding: 20px;
                            background: #fff;
                            border-radius: 8px;
                            margin-bottom: 15px;
                        }
                        .net-pay-amount strong {
                            font-size: 24px;
                            color: #28a745;
                        }
                        .payment-status {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            padding: 15px;
                            background: #fff;
                            border-radius: 8px;
                        }
                        @media (max-width: 768px) {
                            .detail-grid {
                                grid-template-columns: 1fr;
                            }
                        }
                    </style>
                `;
            } else {
                modalBody.innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <i class="fas fa-exclamation-circle" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></i>
                        <p style="color: #666;">${data.message || 'Failed to load payroll details'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('View Error:', error);
            modalBody.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-exclamation-circle" style="font-size: 48px; color: #dc3545; margin-bottom: 15px;"></i>
                    <p style="color: #666;">Failed to load payroll details. Please try again.</p>
                </div>
            `;
        });
}

// Mark as Paid
function markAsPaid(workerId, periodStart, periodEnd) {
    if (confirm('Mark this payroll as paid?\n\nThis will update the payment status to PAID.')) {
        const formData = new FormData();
        formData.append('action', 'mark_paid');
        formData.append('worker_id', workerId);
        formData.append('period_start', periodStart);
        formData.append('period_end', periodEnd);
        
        const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.indexOf('/modules/'));
        
        fetch(`${baseUrl}/api/payroll.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showAlert(data.message || 'Failed to update payment status', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to update payment status. Please try again.', 'error');
        });
    }
}

// Close Modal
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('viewModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

// Show Alert Function
function showAlert(message, type) {
    // Remove any existing alerts
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.style.animation = 'slideDown 0.3s ease-out';
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
        <button class="alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    const content = document.querySelector('.payroll-content');
    content.insertBefore(alertDiv, content.firstChild);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.style.animation = 'slideUp 0.3s ease-in';
            setTimeout(() => alertDiv.remove(), 300);
        }
    }, 5000);
}

// Close Alert Function
function closeAlert(id) {
    const alert = document.getElementById(id);
    if (alert) {
        alert.style.animation = 'slideUp 0.3s ease-in';
        setTimeout(() => alert.remove(), 300);
    }
}

// Auto-dismiss flash messages
setTimeout(() => {
    const flashMessage = document.getElementById('flashMessage');
    if (flashMessage) {
        closeAlert('flashMessage');
    }
}, 5000);

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
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
`;
document.head.appendChild(style);