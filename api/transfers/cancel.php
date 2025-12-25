<?php
/**
 * Cancel Transfer Request API
 * POST: request_id
 * 
 * Only the initiator (from_user_id) can cancel their own pending request
 */

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Require authentication
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$currentUserId = getCurrentUserId();

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$requestId = intval($input['request_id'] ?? 0);

if ($requestId <= 0) {
    jsonResponse(['success' => false, 'message' => 'Request ID là bắt buộc'], 400);
}

try {
    $db = getDB();
    
    // Get the request
    $stmt = $db->prepare("
        SELECT tr.*
        FROM transfer_requests tr
        WHERE tr.id = ? AND tr.status = 'pending'
    ");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    
    if (!$request) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy yêu cầu hoặc yêu cầu đã được xử lý'], 404);
    }
    
    // Check permission - only initiator can cancel
    if ($request['from_user_id'] != $currentUserId) {
        jsonResponse(['success' => false, 'message' => 'Bạn không có quyền hủy yêu cầu này'], 403);
    }
    
    // Update request status to cancelled
    $stmt = $db->prepare("UPDATE transfer_requests SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$requestId]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Đã hủy yêu cầu chuyển giao'
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
