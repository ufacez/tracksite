/**
 * Schedule Management JavaScript
 * TrackSite Construction Management System
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Schedule module initialized');
    autoDismissAlerts();
});

/**
 * Archive schedule
 */
function archiveSchedule(scheduleId, workerName) {
    if (confirm(`Archive schedule for ${workerName}?\n\nThis will deactivate the schedule but keep it in the system.`)) {
        const formData = new FormData();
        formData.append('action', 'archive');
        formData.append('id', scheduleId);
        
        fetch('/tracksite/api/schedule.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to archive schedule', 'error');
        });
    }
}

/**
 * Restore schedule
 */
function restoreSchedule(scheduleId) {
    if (confirm('Restore this schedule?\n\nThis will reactivate the schedule.')) {
        const formData = new FormData();
        formData.append('action', 'restore');
        formData.append('id', scheduleId);
        
        fetch('/tracksite/api/schedule.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showAlert(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to restore schedule', 'error');
        });
    }
}

/**
 * Delete schedule permanently
 */
function deleteSchedule(scheduleId, workerName) {
    if (confirm(`⚠️ WARNING: Permanently delete schedule for ${workerName}?\n\nThis action CANNOT be undone!`)) {
        const doubleCheck = confirm('Are you absolutely sure? This will permanently remove the schedule from the system.');
        
        if (doubleCheck) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', scheduleId);
            
            fetch('/tracksite/api/schedule.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Failed to delete schedule', 'error');
            });
        }
    }
}

/**
 * Show alert message
 */
function showAlert(message, type) {
    // Remove existing alerts
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
    
    const content = document.querySelector('.schedule-content');
    content.insertBefore(alertDiv, content.firstChild);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.style.animation = 'slideUp 0.3s ease-in';
            setTimeout(() => alertDiv.remove(), 300);
        }
    }, 5000);
}

/**
 * Close alert
 */
function closeAlert(alertId) {
    const alert = document.getElementById(alertId);
    if (alert) {
        alert.style.animation = 'slideUp 0.3s ease-in';
        setTimeout(() => alert.remove(), 300);
    }
}

/**
 * Auto-dismiss alerts
 */
function autoDismissAlerts() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
}

/**
 * Toggle all days checkbox
 */
function toggleAllDays(checkbox) {
    const dayCheckboxes = document.querySelectorAll('.day-checkbox');
    dayCheckboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
}

/**
 * Calculate hours between times
 */
function calculateHours() {
    const startTime = document.getElementById('start_time');
    const endTime = document.getElementById('end_time');
    const hoursDisplay = document.getElementById('hours_display');
    
    if (startTime && endTime && startTime.value && endTime.value) {
        const start = new Date(`2000-01-01 ${startTime.value}`);
        const end = new Date(`2000-01-01 ${endTime.value}`);
        
        let diff = (end - start) / 1000 / 60 / 60; // hours
        
        if (diff < 0) {
            diff += 24; // Handle overnight shifts
        }
        
        if (hoursDisplay) {
            hoursDisplay.textContent = diff.toFixed(1) + ' hours';
        }
    }
}

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

// Export functions
window.archiveSchedule = archiveSchedule;
window.restoreSchedule = restoreSchedule;
window.deleteSchedule = deleteSchedule;
window.closeAlert = closeAlert;
window.toggleAllDays = toggleAllDays;
window.calculateHours = calculateHours;