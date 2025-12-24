<?php
/**
 * Reject Transfer Request API
 * POST: request_id
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
    
    // Get request with device info
    $stmt = $db->prepare("
        SELECT tr.*, d.current_holder_id
        FROM transfer_requests tr
        JOIN devices d ON tr.device_id = d.id
        WHERE tr.id = ? AND tr.status = 'pending'
    ");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    
    if (!$request) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy yêu cầu hoặc yêu cầu đã được xử lý'], 404);
    }
    
    // Check permission
    $canReject = false;
    
    if ($request['type'] == 'transfer') {
        // Transfer: recipient can reject
        if ($request['to_user_id'] == $currentUserId) {
            $canReject = true;
        }
    } else {
        // Borrow request: current holder can reject
        if ($request['current_holder_id'] == $currentUserId) {
            $canReject = true;
        }
    }
    
    if (!$canReject) {
        jsonResponse(['success' => false, 'message' => 'Bạn không có quyền từ chối yêu cầu này'], 403);
    }
    
    // Update request status
    $stmt = $db->prepare("UPDATE transfer_requests SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$requestId]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Đã từ chối yêu cầu'
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
