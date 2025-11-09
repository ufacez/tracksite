<?php
/**
 * Database Configuration File
 * TrackSite Construction Management System
 * 
 * This file contains database connection settings and PDO setup
 */

// Prevent direct access
if (!defined('TRACKSITE_INCLUDED')) {
    define('TRACKSITE_INCLUDED', true);
}

// Database Configuration Constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'construction_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get Database Connection
 * Returns a PDO instance with error handling
 * 
 * @return PDO|null Database connection or null on failure
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            return null;
        }
    }
    
    return $pdo;
}

/**
 * Test Database Connection
 * 
 * @return bool True if connection successful, false otherwise
 */
function testDBConnection() {
    try {
        $pdo = getDBConnection();
        if ($pdo === null) {
            return false;
        }
        
        // Simple query to test connection
        $stmt = $pdo->query("SELECT 1");
        return $stmt !== false;
        
    } catch (PDOException $e) {
        error_log("Database Test Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Close Database Connection
 */
function closeDBConnection() {
    global $pdo;
    $pdo = null;
}

// Initialize connection on file include
$db = getDBConnection();

if ($db === null) {
    die("ERROR: Could not connect to database. Please check your configuration.");
}
?>