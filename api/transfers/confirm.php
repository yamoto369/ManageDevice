<?php
/**
 * Confirm Transfer Request API
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
    $db->beginTransaction();
    
    // Get request with device info
    $stmt = $db->prepare("
        SELECT tr.*, d.current_holder_id, d.name as device_name
        FROM transfer_requests tr
        JOIN devices d ON tr.device_id = d.id
        WHERE tr.id = ? AND tr.status = 'pending'
    ");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    
    if (!$request) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy yêu cầu hoặc yêu cầu đã được xử lý'], 404);
    }
    
    // Check permission
    $canConfirm = false;
    $newHolderId = null;
    
    if ($request['type'] == 'transfer') {
        // Transfer: recipient confirms
        if ($request['to_user_id'] == $currentUserId) {
            $canConfirm = true;
            $newHolderId = $currentUserId; // Current user becomes new holder
        }
    } else {
        // Borrow request: current holder confirms
        if ($request['current_holder_id'] == $currentUserId) {
            $canConfirm = true;
            $newHolderId = $request['from_user_id']; // Requester becomes new holder
        }
    }
    
    if (!$canConfirm) {
        $db->rollBack();
        jsonResponse(['success' => false, 'message' => 'Bạn không có quyền xác nhận yêu cầu này'], 403);
    }
    
    // Update request status
    $stmt = $db->prepare("UPDATE transfer_requests SET status = 'confirmed' WHERE id = ?");
    $stmt->execute([$requestId]);
    
    // Update device holder (keep status unchanged)
    $stmt = $db->prepare("UPDATE devices SET current_holder_id = ? WHERE id = ?");
    $stmt->execute([$newHolderId, $request['device_id']]);
    
    // Add to transfer history
    $actionType = $request['type'] == 'transfer' ? 'transfer' : 'borrow';
    $stmt = $db->prepare("INSERT INTO transfer_history (device_id, from_user_id, to_user_id, action_type, note) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $request['device_id'],
        $request['current_holder_id'], // Previous holder
        $newHolderId,
        $actionType,
        $request['note']
    ]);
    
    $db->commit();
    
    jsonResponse([
        'success' => true,
        'message' => 'Đã xác nhận chuyển giao thành công'
    ]);
    
} catch (PDOException $e) {
    $db->rollBack();
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
