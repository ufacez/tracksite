<?php
/**
 * Sidebar Component - UPDATED
 * TrackSite Construction Management System
 * 
 * Reusable sidebar navigation for super admin
 */

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<div class="sidebar">
    <ul>
        <!-- Logo Section -->
        <li>
            <div class="logo-section">
                <div class="logo">
                    <img src="<?php echo IMAGES_URL; ?>/logo.png" alt="<?php echo SYSTEM_NAME; ?> Logo" class="logo-img">
                    <span class="logo-text"><?php echo SYSTEM_NAME; ?></span>
                </div>
            </div>
        </li>
        
        <!-- Dashboard -->
        <li>
            <a href="<?php echo BASE_URL; ?>/modules/super_admin/dashboard.php" 
               class="<?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i>
                <div class="title">Dashboard</div>
            </a>
        </li>
        
        <!-- Attendance -->
        <li>
            <a href="<?php echo BASE_URL; ?>/modules/super_admin/attendance/index.php"
               class="<?php echo ($current_dir === 'attendance') ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i>
                <div class="title">Attendance</div>
            </a>
        </li>
        
        <!-- Workers -->
        <li>
            <a href="<?php echo BASE_URL; ?>/modules/super_admin/workers/index.php"
               class="<?php echo ($current_dir === 'workers') ? 'active' : ''; ?>">
                <i class="fas fa-user-hard-hat"></i>
                <div class="title">Workers</div>
            </a>
        </li>
        
        <!-- Schedule -->
        <li>
            <a href="<?php echo BASE_URL; ?>/modules/super_admin/schedule/index.php"
               class="<?php echo ($current_dir === 'schedule') ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i>
                <div class="title">Schedule</div>
            </a>
        </li>
        
        <!-- Payroll -->
        <li>
            <a href="<?php echo BASE_URL; ?>/modules/super_admin/payroll/index.php"
               class="<?php echo ($current_dir === 'payroll') ? 'active' : ''; ?>">
                <i class="fas fa-money-check-edit-alt"></i>
                <div class="title">Payroll</div>
            </a>
        </li>
        
        <!-- Deductions -->
        <li>
            <a href="<?php echo BASE_URL; ?>/modules/super_admin/deductions/index.php"
               class="<?php echo ($current_dir === 'deductions') ? 'active' : ''; ?>">
                <i class="fas fa-money-check-alt"></i>
                <div class="title">Deductions</div>
            </a>
        </li>
        
        <!-- Cash Advance -->
        <li>
            <a href="<?php echo BASE_URL; ?>/modules/super_admin/cashadvance/index.php"
               class="<?php echo ($current_dir === 'cashadvance') ? 'active' : ''; ?>">
                <i class="fas fa-dollar-sign"></i>
                <div class="title">Cash Advance</div>
            </a>
        </li>
        
        <!-- Archive - NEW SEPARATE MODULE -->
        <li>
            <a href="<?php echo BASE_URL; ?>/modules/super_admin/archive/index.php"
               class="<?php echo ($current_dir === 'archive') ? 'active' : ''; ?>">
                <i class="fas fa-archive"></i>
                <div class="title">Archive</div>
            </a>
        </li>
        
        <!-- Settings -->
        <li>
            <a href="<?php echo BASE_URL; ?>/modules/super_admin/settings/index.php"
               class="<?php echo ($current_dir === 'settings') ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <div class="title">Settings</div>
            </a>
        </li>
        
        <!-- Logout -->
        <li>
            <a href="<?php echo BASE_URL; ?>/logout.php" class="logout-link">
                <i class="fas fa-sign-out"></i>
                <div class="title">Log Out</div>
            </a>
        </li>
    </ul>
</div>