/**
 * Login Page JavaScript
 * TrackSite Construction Management System
 */

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    
    // Get form elements
    const loginForm = document.getElementById('loginForm');
    const btnLogin = document.getElementById('btnLogin');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    
    // Handle form submission
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Add enter key listener
    if (usernameInput) {
        usernameInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                passwordInput.focus();
            }
        });
    }
    
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            fadeOut(alert);
        }, 5000);
    });
});

/**
 * Handle login form submission
 */
function handleLogin(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const btnLogin = document.getElementById('btnLogin');
    
    // Validate inputs
    const username = formData.get('username').trim();
    const password = formData.get('password');
    
    if (!username || !password) {
        showAlert('Please enter both username and password.', 'error');
        return;
    }
    
    // Show loading state
    btnLogin.classList.add('loading');
    btnLogin.disabled = true;
    
    // Send AJAX request
    fetch('api/login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            
            // Redirect after short delay
            setTimeout(() => {
                window.location.href = data.redirect_url;
            }, 1000);
        } else {
            showAlert(data.message, 'error');
            
            // Reset loading state
            btnLogin.classList.remove('loading');
            btnLogin.disabled = false;
            
            // Clear password field
            document.getElementById('password').value = '';
            document.getElementById('password').focus();
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        showAlert('An error occurred. Please try again.', 'error');
        
        // Reset loading state
        btnLogin.classList.remove('loading');
        btnLogin.disabled = false;
    });
}

/**
 * Toggle password visibility
 */
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

/**
 * Show alert message
 */
function showAlert(message, type = 'info') {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    
    // Set icon based on type
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'error') icon = 'exclamation-circle';
    if (type === 'warning') icon = 'exclamation-triangle';
    
    alert.innerHTML = `
        <i class="fas fa-${icon}"></i>
        ${message}
    `;
    
    // Insert alert before form
    const form = document.getElementById('loginForm');
    form.parentNode.insertBefore(alert, form);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        fadeOut(alert);
    }, 5000);
}

/**
 * Fade out element
 */
function fadeOut(element) {
    let opacity = 1;
    const timer = setInterval(() => {
        if (opacity <= 0.1) {
            clearInterval(timer);
            element.remove();
        }
        element.style.opacity = opacity;
        opacity -= 0.1;
    }, 50);
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Handle keyboard shortcuts
 */
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + L to focus username field
    if ((e.ctrlKey || e.metaKey) && e.key === 'l') {
        e.preventDefault();
        document.getElementById('username').focus();
    }
});

/**
 * Clear form on page load (for back button)
 */
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        document.getElementById('loginForm').reset();
        const btnLogin = document.getElementById('btnLogin');
        btnLogin.classList.remove('loading');
        btnLogin.disabled = false;
    }
});