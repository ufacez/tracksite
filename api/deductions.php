<?php
/**
 * Deductions API
 * TrackSite Construction Management System
 * 
 * Handles all deduction-related AJAX requests
 */

define('TRACKSITE_INCLUDED', true);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    jsonError('Unauthorized access');
}

$user_id = getCurrentUserId();

// Handle different actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'toggle_active':
                if (!isSuperAdmin()) {
                    http_response_code(403);
                    jsonError('Unauthorized access. Admin privileges required.');
                }
                
                $deduction_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
                
                if ($deduction_id <= 0) {
                    http_response_code(400);
                    jsonError('Invalid deduction ID');
                }
                
                // Toggle active status
                $stmt = $db->prepare("UPDATE deductions 
                                     SET is_active = NOT is_active,
                                         updated_at = NOW()
                                     WHERE deduction_id = ?");
                $stmt->execute([$deduction_id]);
                
                // Get new status
                $stmt = $db->prepare("SELECT is_active FROM deductions WHERE deduction_id = ?");
                $stmt->execute([$deduction_id]);
                $result = $stmt->fetch();
                
                logActivity($db, $user_id, 'toggle_deduction', 'deductions', $deduction_id,
                           'Toggled deduction status to ' . ($result['is_active'] ? 'active' : 'inactive'));
                
                http_response_code(200);
                jsonSuccess('Deduction status updated', [
                    'is_active' => (bool)$result['is_active']
                ]);
                break;
                
            case 'delete':
                if (!isSuperAdmin()) {
                    http_response_code(403);
                    jsonError('Unauthorized access. Admin privileges required.');
                }
                
                $deduction_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
                
                if ($deduction_id <= 0) {
                    http_response_code(400);
                    jsonError('Invalid deduction ID');
                }
                
                // Get deduction details before deleting
                $stmt = $db->prepare("SELECT d.*, w.first_name, w.last_name 
                                     FROM deductions d
                                     JOIN workers w ON d.worker_id = w.worker_id
                                     WHERE d.deduction_id = ?");
                $stmt->execute([$deduction_id]);
                $deduction = $stmt->fetch();
                
                if (!$deduction) {
                    http_response_code(404);
                    jsonError('Deduction not found');
                }
                
                // Delete deduction
                $stmt = $db->prepare("DELETE FROM deductions WHERE deduction_id = ?");
                $stmt->execute([$deduction_id]);
                
                logActivity($db, $user_id, 'delete_deduction', 'deductions', $deduction_id,
                           "Deleted {$deduction['deduction_type']} deduction for {$deduction['first_name']} {$deduction['last_name']}");
                
                http_response_code(200);
                jsonSuccess('Deduction deleted successfully');
                break;
                
            default:
                http_response_code(400);
                jsonError('Invalid action');
        }
        
    } catch (PDOException $e) {
        error_log("Deductions API Error: " . $e->getMessage());
        http_response_code(500);
        jsonError('Database error occurred. Please try again.');
    } catch (Exception $e) {
        error_log("Deductions API Error: " . $e->getMessage());
        http_response_code(500);
        jsonError('An error occurred. Please try again.');
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    try {
        switch ($action) {
            case 'get':
                $deduction_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
                
                if ($deduction_id <= 0) {
                    http_response_code(400);
                    jsonError('Invalid deduction ID');
                }
                
                $stmt = $db->prepare("SELECT d.*, 
                                     w.worker_code, w.first_name, w.last_name, w.position,
                                     u.username as created_by_name
                                     FROM deductions d
                                     JOIN workers w ON d.worker_id = w.worker_id
                                     LEFT JOIN users u ON d.created_by = u.user_id
                                     WHERE d.deduction_id = ?");
                $stmt->execute([$deduction_id]);
                $deduction = $stmt->fetch();
                
                if (!$deduction) {
                    http_response_code(404);
                    jsonError('Deduction not found');
                }
                
                http_response_code(200);
                jsonSuccess('Deduction retrieved', $deduction);
                break;
                
            case 'list':
                $worker_id = isset($_GET['worker_id']) ? intval($_GET['worker_id']) : 0;
                $is_active = isset($_GET['is_active']) ? ($_GET['is_active'] === 'true' ? 1 : 0) : null;
                
                $sql = "SELECT d.*, 
                        w.worker_code, w.first_name, w.last_name, w.position
                        FROM deductions d
                        JOIN workers w ON d.worker_id = w.worker_id
                        WHERE 1=1";
                $params = [];
                
                if ($worker_id > 0) {
                    $sql .= " AND d.worker_id = ?";
                    $params[] = $worker_id;
                }
                
                if ($is_active !== null) {
                    $sql .= " AND d.is_active = ?";
                    $params[] = $is_active;
                }
                
                $sql .= " ORDER BY d.created_at DESC";
                
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $deductions = $stmt->fetchAll();
                
                http_response_code(200);
                jsonSuccess('Deductions retrieved', [
                    'count' => count($deductions),
                    'deductions' => $deductions
                ]);
                break;
                
            default:
                http_response_code(400);
                jsonError('Invalid action');
        }
        
    } catch (PDOException $e) {
        error_log("Deductions API Error: " . $e->getMessage());
        http_response_code(500);
        jsonError('Database error occurred. Please try again.');
    } catch (Exception $e) {
        error_log("Deductions API Error: " . $e->getMessage());
        http_response_code(500);
        jsonError('An error occurred. Please try again.');
    }
    
} else {
    http_response_code(405);
    jsonError('Invalid request method. Only GET and POST are allowed.');
}
?>