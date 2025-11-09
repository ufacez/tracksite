<?php
/**
 * Workers API Endpoint
 * TrackSite Construction Management System
 */

define('TRACKSITE_INCLUDED', true);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn()) {
    jsonError('Unauthorized access');
}

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    
    switch ($action) {
        case 'view':
            $worker_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            $response = viewWorker($db, $worker_id);
            break;
            
        case 'delete':
            if (!isSuperAdmin()) {
                jsonError('Unauthorized action');
            }
            $worker_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
            $response = deleteWorker($db, $worker_id, getCurrentUserId());
            break;
            
        case 'list':
            $response = listWorkers($db);
            break;
            
        default:
            $response['message'] = 'Invalid action';
    }
    
} catch (Exception $e) {
    error_log("Workers API Error: " . $e->getMessage());
    $response['message'] = 'An error occurred';
}

echo json_encode($response);
exit();

/**
 * View worker details
 */
function viewWorker($db, $worker_id) {
    $result = ['success' => false, 'message' => '', 'data' => null];
    
    try {
        $sql = "SELECT w.*, u.email, u.username 
                FROM workers w 
                JOIN users u ON w.user_id = u.user_id 
                WHERE w.worker_id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$worker_id]);
        $worker = $stmt->fetch();
        
        if ($worker) {
            $result['success'] = true;
            $result['message'] = 'Worker details retrieved';
            $result['data'] = $worker;
        } else {
            $result['message'] = 'Worker not found';
        }
        
    } catch (PDOException $e) {
        error_log("View Worker Error: " . $e->getMessage());
        $result['message'] = 'Failed to retrieve worker details';
    }
    
    return $result;
}

/**
 * Delete worker (Soft Delete)
 */
function deleteWorker($db, $worker_id, $admin_id) {
    $result = ['success' => false, 'message' => ''];
    
    try {
        // Get worker details first
        $stmt = $db->prepare("SELECT * FROM workers WHERE worker_id = ?");
        $stmt->execute([$worker_id]);
        $worker = $stmt->fetch();
        
        if (!$worker) {
            $result['message'] = 'Worker not found';
            return $result;
        }
        
        // Soft delete - mark as archived
        $stmt = $db->prepare("UPDATE workers SET 
                              is_archived = TRUE, 
                              archived_at = NOW(), 
                              archived_by = ?,
                              archive_reason = 'Archived by admin',
                              updated_at = NOW()
                              WHERE worker_id = ?");
        $stmt->execute([$admin_id, $worker_id]);
        
        // Also deactivate user account
        $stmt = $db->prepare("UPDATE users SET status = 'inactive' WHERE user_id = ?");
        $stmt->execute([$worker['user_id']]);
        
        // Log activity
        logActivity($db, $admin_id, 'archive_worker', 'workers', $worker_id, 
                   "Archived worker: {$worker['first_name']} {$worker['last_name']} ({$worker['worker_code']})");
        
        $result['success'] = true;
        $result['message'] = 'Worker archived successfully. You can restore it from the archive.';
        
    } catch (PDOException $e) {
        error_log("Archive Worker Error: " . $e->getMessage());
        $result['message'] = 'Failed to archive worker';
    }
    
    return $result;
}

/**
 * List all workers
 */
function listWorkers($db) {
    $result = ['success' => false, 'message' => '', 'data' => []];
    
    try {
        $sql = "SELECT w.worker_id, w.worker_code, w.first_name, w.last_name, 
                       w.position, w.employment_status, w.daily_rate
                FROM workers w 
                WHERE w.employment_status = 'active'
                ORDER BY w.first_name, w.last_name";
        
        $stmt = $db->query($sql);
        $workers = $stmt->fetchAll();
        
        $result['success'] = true;
        $result['message'] = 'Workers retrieved successfully';
        $result['data'] = $workers;
        
    } catch (PDOException $e) {
        error_log("List Workers Error: " . $e->getMessage());
        $result['message'] = 'Failed to retrieve workers';
    }
    
    return $result;
}
?>