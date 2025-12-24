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

try {
    $db = getDB();
    
    // Get pending requests where:
    // 1. User is the recipient (to_user_id) and request is a transfer
    // 2. User is the current device holder and request is a borrow_request
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
        WHERE tr.status = 'pending'
        AND (
            (tr.type = 'transfer' AND tr.to_user_id = ?)
            OR (tr.type = 'borrow_request' AND d.current_holder_id = ?)
        )
        ORDER BY tr.created_at DESC
    ");
    $stmt->execute([$currentUserId, $currentUserId]);
    $requests = $stmt->fetchAll();
    
    // Add aliases
    foreach ($requests as &$request) {
        $request['from_user_alias'] = getUserAlias($currentUserId, $request['from_user_id']);
        $request['to_user_alias'] = getUserAlias($currentUserId, $request['to_user_id']);
    }
    
    jsonResponse([
        'success' => true,
        'data' => $requests,
        'count' => count($requests)
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
