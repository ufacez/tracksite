/**
 * Deductions JavaScript Functions
 * TrackSite Construction Management System
 */

// Get base URL
const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.indexOf('/modules/'));

/**
 * Toggle deduction active status
 */
function toggleDeduction(deductionId) {
    if (!confirm('Toggle this deduction\'s status?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'toggle_active');
    formData.append('id', deductionId);
    
    fetch(`${baseUrl}/api/deductions.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to toggle status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to toggle deduction status', 'error');
    });
}

/**
 * Delete deduction
 */
function deleteDeduction(deductionId) {
    if (!confirm('Delete this deduction?\n\nThis action cannot be undone.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', deductionId);
    
    fetch(`${baseUrl}/api/deductions.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showAlert(data.message || 'Failed to delete', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to delete deduction', 'error');
    });
}

/**
 * Show Alert
 */
function showAlert(message, type) {
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
    
    const content = document.querySelector('.workers-content');
    content.insertBefore(alertDiv, content.firstChild);
    
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.style.animation = 'slideUp 0.3s ease-in';
            setTimeout(() => alertDiv.remove(), 300);
        }
    }, 5000);
}

/**
 * Close Alert
 */
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
    if (flashMessage) closeAlert('flashMessage');
}, 5000);