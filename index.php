<?php
/**
 * Main Entry Point
 * TrackSite Construction Management System
 * 
 * Redirects users to appropriate page based on login status
 */

// Define constant to allow includes
define('TRACKSITE_INCLUDED', true);

// Include required files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/includes/functions.php';

// Check if user is logged in
if (isLoggedIn()) {
    // Redirect based on user level
    if (isSuperAdmin()) {
        redirect(BASE_URL . '/modules/super_admin/dashboard.php');
    } else if (isWorker()) {
        redirect(BASE_URL . '/modules/worker/dashboard.php');
    } else {
        // Unknown user level, logout for safety
        redirect(BASE_URL . '/logout.php');
    }
} else {
    // Not logged in, redirect to login page
    redirect(BASE_URL . '/login.php');
}

// In any PHP file with database access
require_once 'includes/notifications.php';  

// Test notification

?>