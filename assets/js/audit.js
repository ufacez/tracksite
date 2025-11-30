/**
 * Audit Trail JavaScript
 * TrackSite Construction Management System
 */

// Get base URL
function getBaseUrl() {
    const path = window.location.pathname;
    const modulesIndex = path.indexOf('/modules/');
    if (modulesIndex === -1) {
        return window.location.origin;
    }
    return window.location.origin + path.substring(0, modulesIndex);
}

const baseUrl = getBaseUrl();

console.log('Audit Trail JS Loaded - Base URL:', baseUrl);

// Current page and filters
let currentPage = 1;
let currentFilters = {};

/**
 * Initialize audit trail page
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Audit Trail module initialized');
    
    // Load audit trail
    loadAuditTrail();
    
    // Auto-dismiss alerts
    autoDismissAlerts();
    
    // Setup filter form
    setupFilters();
});

/**
 * Setup filter form
 */
function setupFilters() {
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });
    }
}

/**
 * Apply filters
 */
function applyFilters() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    
    currentFilters = {
        module: formData.get('module') || '',
        action_type: formData.get('action_type') || '',
        severity: formData.get('severity') || '',
        date_from: formData.get('date_from') || '',
        date_to: formData.get('date_to') || '',
        search: formData.get('search') || ''
    };
    
    currentPage = 1;
    loadAuditTrail();
}

/**
 * Reset filters
 */
function resetFilters() {
    document.getElementById('filterForm').reset();
    currentFilters = {};
    currentPage = 1;
    loadAuditTrail();
}

/**
 * Load audit trail
 */
function loadAuditTrail(page = 1) {
    currentPage = page;
    
    showLoading('Loading audit trail...');
    
    const params = new URLSearchParams({
        action: 'list',
        page: currentPage,
        ...currentFilters
    });
    
    fetch(`${baseUrl}/api/audit.php?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                displayAuditTrail(data.data.records);
                displayPagination(data.data.pagination);
                updateStats(data.data.stats);
            } else {
                showAlert(data.message || 'Failed to load audit trail', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Load Error:', error);
            showAlert('Failed to load audit trail. Please try again.', 'error');
        });
}

/**
 * Display audit trail records
 */
function displayAuditTrail(records) {
    const tbody = document.querySelector('.audit-table tbody');
    
    if (!records || records.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="no-data">
                    <i class="fas fa-clipboard-list"></i>
                    <p>No audit records found</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = records.map(record => `
        <tr onclick="viewAuditDetail(${record.audit_id})">
            <td>
                <span class="module-badge">${escapeHtml(record.module)}</span>
            </td>
            <td>
                <span class="action-badge action-${record.action_type}">
                    ${escapeHtml(record.action_type)}
                </span>
            </td>
            <td>
                <strong>${escapeHtml(record.username || 'System')}</strong><br>
                <small style="color: #999;">${formatDateTime(record.created_at)}</small>
            </td>
            <td>${escapeHtml(record.record_identifier || '-')}</td>
            <td>${escapeHtml(record.changes_summary || '-')}</td>
            <td>
                <span class="severity-badge severity-${record.severity}">
                    ${escapeHtml(record.severity)}
                </span>
            </td>
            <td style="text-align: center;">
                ${record.success == 1 ? 
                    '<i class="fas fa-check-circle" style="color: #28a745; font-size: 18px;"></i>' : 
                    '<i class="fas fa-times-circle" style="color: #dc3545; font-size: 18px;"></i>'
                }
            </td>
            <td>
                <button class="action-btn btn-view" 
                        onclick="event.stopPropagation(); viewAuditDetail(${record.audit_id})"
                        title="View Details">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

/**
 * Display pagination
 */
function displayPagination(pagination) {
    const paginationDiv = document.querySelector('.pagination');
    
    if (!pagination || pagination.total_pages <= 1) {
        paginationDiv.innerHTML = '';
        return;
    }
    
    let html = `
        <button onclick="loadAuditTrail(1)" ${pagination.current_page === 1 ? 'disabled' : ''}>
            <i class="fas fa-angle-double-left"></i>
        </button>
        <button onclick="loadAuditTrail(${pagination.current_page - 1})" 
                ${pagination.current_page === 1 ? 'disabled' : ''}>
            <i class="fas fa-angle-left"></i>
        </button>
    `;
    
    // Show max 5 page numbers
    const start = Math.max(1, pagination.current_page - 2);
    const end = Math.min(pagination.total_pages, pagination.current_page + 2);
    
    for (let i = start; i <= end; i++) {
        html += `
            <button onclick="loadAuditTrail(${i})" 
                    class="${i === pagination.current_page ? 'active' : ''}">
                ${i}
            </button>
        `;
    }
    
    html += `
        <button onclick="loadAuditTrail(${pagination.current_page + 1})" 
                ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>
            <i class="fas fa-angle-right"></i>
        </button>
        <button onclick="loadAuditTrail(${pagination.total_pages})" 
                ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}>
            <i class="fas fa-angle-double-right"></i>
        </button>
        <span class="pagination-info">
            Page ${pagination.current_page} of ${pagination.total_pages} 
            (${pagination.total_records} records)
        </span>
    `;
    
    paginationDiv.innerHTML = html;
}

/**
 * Update statistics
 */
function updateStats(stats) {
    if (!stats) return;
    
    document.getElementById('stat-total').textContent = stats.total_actions || 0;
    document.getElementById('stat-critical').textContent = stats.critical || 0;
    document.getElementById('stat-high').textContent = stats.high || 0;
    document.getElementById('stat-failed').textContent = stats.failed || 0;
}

/**
 * View audit detail
 */
function viewAuditDetail(auditId) {
    showLoading('Loading audit details...');
    
    fetch(`${baseUrl}/api/audit.php?action=detail&id=${auditId}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                displayAuditDetail(data.data);
                showModal('auditDetailModal');
            } else {
                showAlert(data.message || 'Failed to load audit details', 'error');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Detail Error:', error);
            showAlert('Failed to load audit details. Please try again.', 'error');
        });
}

/**
 * Display audit detail in modal
 */
function displayAuditDetail(audit) {
    const modalBody = document.getElementById('auditDetailBody');
    
    // Parse changes
    let changesHtml = '<p style="color: #999;">No changes recorded</p>';
    
    if (audit.old_values && audit.new_values) {
        try {
            const oldValues = JSON.parse(audit.old_values);
            const newValues = JSON.parse(audit.new_values);
            const changes = [];
            
            for (const key in newValues) {
                if (oldValues[key] !== undefined && oldValues[key] !== newValues[key]) {
                    changes.push({
                        field: key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                        old: oldValues[key],
                        new: newValues[key]
                    });
                }
            }
            
            if (changes.length > 0) {
                changesHtml = `
                    <table class="changes-table">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Old Value</th>
                                <th>New Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${changes.map(change => `
                                <tr>
                                    <td><strong>${escapeHtml(change.field)}</strong></td>
                                    <td class="value-old">${escapeHtml(String(change.old))}</td>
                                    <td class="value-new">${escapeHtml(String(change.new))}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                `;
            }
        } catch (e) {
            console.error('Parse changes error:', e);
        }
    }
    
    modalBody.innerHTML = `
        <div class="audit-detail-section">
            <h3><i class="fas fa-info-circle"></i> Audit Information</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Audit ID</span>
                    <span class="detail-value">#${audit.audit_id}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Date & Time</span>
                    <span class="detail-value">${formatDateTime(audit.created_at)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">User</span>
                    <span class="detail-value">${escapeHtml(audit.username || 'System')}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">User Level</span>
                    <span class="detail-value">${escapeHtml(audit.user_level || 'N/A')}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Module</span>
                    <span class="detail-value">
                        <span class="module-badge">${escapeHtml(audit.module)}</span>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Action</span>
                    <span class="detail-value">
                        <span class="action-badge action-${audit.action_type}">
                            ${escapeHtml(audit.action_type)}
                        </span>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Severity</span>
                    <span class="detail-value">
                        <span class="severity-badge severity-${audit.severity}">
                            ${escapeHtml(audit.severity)}
                        </span>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">
                        ${audit.success == 1 ? 
                            '<span style="color: #28a745;"><i class="fas fa-check-circle"></i> Success</span>' : 
                            '<span style="color: #dc3545;"><i class="fas fa-times-circle"></i> Failed</span>'
                        }
                    </span>
                </div>
            </div>
        </div>
        
        <div class="audit-detail-section">
            <h3><i class="fas fa-file-alt"></i> Record Information</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Table</span>
                    <span class="detail-value">${escapeHtml(audit.table_name)}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Record ID</span>
                    <span class="detail-value">${audit.record_id || 'N/A'}</span>
                </div>
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <span class="detail-label">Record Identifier</span>
                    <span class="detail-value">${escapeHtml(audit.record_identifier || 'N/A')}</span>
                </div>
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <span class="detail-label">Summary</span>
                    <span class="detail-value">${escapeHtml(audit.changes_summary || 'No summary')}</span>
                </div>
            </div>
        </div>
        
        <div class="audit-detail-section">
            <h3><i class="fas fa-exchange-alt"></i> Changes Made</h3>
            ${changesHtml}
        </div>
        
        <div class="audit-detail-section">
            <h3><i class="fas fa-network-wired"></i> Session Information</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">IP Address</span>
                    <span class="detail-value">${escapeHtml(audit.ip_address || 'N/A')}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Request Method</span>
                    <span class="detail-value">${escapeHtml(audit.request_method || 'N/A')}</span>
                </div>
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <span class="detail-label">User Agent</span>
                    <span class="detail-value" style="font-size: 12px; word-break: break-all;">
                        ${escapeHtml(audit.user_agent || 'N/A')}
                    </span>
                </div>
                ${audit.error_message ? `
                <div class="detail-item" style="grid-column: 1 / -1;">
                    <span class="detail-label">Error Message</span>
                    <span class="detail-value" style="color: #dc3545;">
                        ${escapeHtml(audit.error_message)}
                    </span>
                </div>
                ` : ''}
            </div>
        </div>
    `;
}

/**
 * Export audit trail
 */
function exportAudit() {
    const params = new URLSearchParams({
        action: 'export',
        ...currentFilters
    });
    
    showLoading('Preparing export...');
    
    window.location.href = `${baseUrl}/api/audit.php?${params.toString()}`;
    
    setTimeout(() => hideLoading(), 2000);
}

/**
 * Show modal
 */
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Close modal
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

/**
 * Show loading overlay
 */
function showLoading(message = 'Loading...') {
    let overlay = document.getElementById('loadingOverlay');
    
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        `;
        
        overlay.innerHTML = `
            <div style="background: #fff; padding: 30px 40px; border-radius: 10px; text-align: center;">
                <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: #DAA520; margin-bottom: 15px;"></i>
                <p style="margin: 0; font-size: 16px; color: #333;">${message}</p>
            </div>
        `;
        
        document.body.appendChild(overlay);
    }
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.remove();
    }
}

/**
 * Show alert
 */
function showAlert(message, type = 'info') {
    const existingAlert = document.querySelector('.alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.style.animation = 'slideDown 0.3s ease-out';
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${escapeHtml(message)}</span>
        <button class="alert-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    const content = document.querySelector('.audit-content');
    content.insertBefore(alertDiv, content.firstChild);
    
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
function closeAlert(id) {
    const alert = document.getElementById(id);
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
            if (alert.parentElement) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                
                setTimeout(() => {
                    alert.remove();
                }, 300);
            }
        }, 5000);
    });
}

/**
 * Format date time
 */
function formatDateTime(dateTimeString) {
    const date = new Date(dateTimeString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Escape HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

// Make functions globally available
window.loadAuditTrail = loadAuditTrail;
window.applyFilters = applyFilters;
window.resetFilters = resetFilters;
window.viewAuditDetail = viewAuditDetail;
window.exportAudit = exportAudit;
window.closeModal = closeModal;
window.closeAlert = closeAlert;

console.log('All Audit Trail functions registered globally');