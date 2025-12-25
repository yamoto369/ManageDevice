<?php
/**
 * Get Pending Transfer Requests API
 * GET: Returns requests where current user needs to confirm
 */

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Require authentication
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$currentUserId = getCurrentUserId();

// Get statuses filter from query params
$statusesParam = isset($_GET['statuses']) ? $_GET['statuses'] : 'pending,confirmed,rejected';
$statuses = array_filter(explode(',', $statusesParam));

// Validate statuses
$validStatuses = ['pending', 'confirmed', 'rejected', 'cancelled'];
$statuses = array_intersect($statuses, $validStatuses);
if (empty($statuses)) {
    $statuses = ['pending', 'confirmed', 'rejected'];
}

try {
    $db = getDB();
    
    // Build status placeholders
    $placeholders = implode(',', array_fill(0, count($statuses), '?'));
    
    // Get transfer requests with specified statuses
    // Order by: pending first, then by created_at desc
    $stmt = $db->prepare("
        SELECT tr.*, 
               d.name as device_name, d.imei_sn as device_imei, d.manufacturer as device_manufacturer,
               d.current_holder_id,
               fu.id as from_user_id, fu.name as from_user_name, fu.email as from_user_email, fu.avatar as from_user_avatar,
               tu.id as to_user_id, tu.name as to_user_name, tu.email as to_user_email, tu.avatar as to_user_avatar
        FROM transfer_requests tr
        JOIN devices d ON tr.device_id = d.id
        JOIN users fu ON tr.from_user_id = fu.id
        JOIN users tu ON tr.to_user_id = tu.id
        WHERE tr.status IN ($placeholders)
        ORDER BY 
            CASE WHEN tr.status = 'pending' THEN 0 ELSE 1 END,
            tr.created_at DESC
    ");
    $stmt->execute($statuses);
    $requests = $stmt->fetchAll();
    
    // Count by status
    $counts = [
        'pending' => 0,
        'confirmed' => 0,
        'rejected' => 0
    ];
    foreach ($requests as $req) {
        if (isset($counts[$req['status']])) {
            $counts[$req['status']]++;
        }
    }
    
    // Add aliases and role indicators
    foreach ($requests as &$request) {
        $request['from_user_alias'] = getUserAlias($currentUserId, $request['from_user_id']);
        $request['to_user_alias'] = getUserAlias($currentUserId, $request['to_user_id']);
        
        // Role indicators
        $request['is_initiator'] = ($request['from_user_id'] == $currentUserId);
        
        // Only allow actions on pending requests
        $request['can_cancel'] = ($request['status'] == 'pending' && $request['from_user_id'] == $currentUserId);
        
        // Can respond (accept/reject) if:
        // - Status is pending AND
        // - Transfer: recipient can respond
        // - Borrow request: current device holder can respond
        $request['can_respond'] = false;
        if ($request['status'] == 'pending') {
            if ($request['type'] == 'transfer' && $request['to_user_id'] == $currentUserId) {
                $request['can_respond'] = true;
            } else if ($request['type'] == 'borrow_request' && $request['current_holder_id'] == $currentUserId) {
                $request['can_respond'] = true;
            }
        }
    }
    
    jsonResponse([
        'success' => true,
        'data' => $requests,
        'count' => count($requests),
        'counts' => $counts
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
