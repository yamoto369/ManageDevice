<?php
/**
 * Create Transfer Request API
 * POST: device_id, to_user_id, note
 * 
 * Logic:
 * - If current holder = logged in user -> Direct transfer request
 * - If current holder != logged in user -> Borrow request
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

$deviceId = intval($input['device_id'] ?? 0);
$toUserId = intval($input['to_user_id'] ?? 0);
$note = trim($input['note'] ?? '');

// Validation
if ($deviceId <= 0) {
    jsonResponse(['success' => false, 'message' => 'Device ID là bắt buộc'], 400);
}

if ($toUserId <= 0) {
    jsonResponse(['success' => false, 'message' => 'Người nhận là bắt buộc'], 400);
}

try {
    $db = getDB();
    
    // Get device info
    $stmt = $db->prepare("SELECT * FROM devices WHERE id = ?");
    $stmt->execute([$deviceId]);
    $device = $stmt->fetch();
    
    if (!$device) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy thiết bị'], 404);
    }
    
    // Determine request type first to validate correctly
    $currentHolderId = $device['current_holder_id'];
    
    // Only block self-transfer when current user is the holder
    if ($currentHolderId == $currentUserId && $toUserId == $currentUserId) {
        jsonResponse(['success' => false, 'message' => 'Không thể chuyển thiết bị cho chính mình'], 400);
    }
    
    // Check if to_user exists and is approved
    $stmt = $db->prepare("SELECT id, name, role, status FROM users WHERE id = ?");
    $stmt->execute([$toUserId]);
    $toUser = $stmt->fetch();
    
    if (!$toUser) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy người nhận'], 404);
    }
    
    if ($toUser['status'] !== 'approved') {
        jsonResponse(['success' => false, 'message' => 'Người nhận chưa được phê duyệt hoặc đã bị vô hiệu hóa'], 400);
    }
    
    // If device is broken, can only transfer to warehouse users
    if ($device['status'] === 'broken' && $toUser['role'] !== 'warehouse') {
        jsonResponse(['success' => false, 'message' => 'Thiết bị hỏng chỉ có thể chuyển cho người quản lý kho (warehouse)'], 400);
    }
    
    if ($currentHolderId == $currentUserId) {
        // Current user is holder -> Direct transfer
        $type = 'transfer';
        $fromUserId = $currentUserId;
        // Request goes to the intended recipient for confirmation
    } else if ($currentHolderId) {
        // Someone else is holding -> Borrow request (request goes to current holder)
        // Check if current holder is approved (not pending/deactivated)
        $stmt = $db->prepare("SELECT id, status FROM users WHERE id = ?");
        $stmt->execute([$currentHolderId]);
        $currentHolder = $stmt->fetch();
        
        if (!$currentHolder || $currentHolder['status'] !== 'approved') {
            jsonResponse(['success' => false, 'message' => 'Người đang giữ thiết bị đã bị vô hiệu hóa, không thể tạo yêu cầu'], 400);
        }
        
        $type = 'borrow_request';
        $fromUserId = $currentUserId;
        // Actually, the request should go to current holder, so swap
        $toUserId = $currentHolderId;
        $targetUserId = $input['to_user_id']; // Store original intent
    } else {
        // No one is holding -> Direct assignment (admin assigns to user)
        $type = 'transfer';
        $fromUserId = $currentUserId;
    }
    
    // Check for existing pending request
    $stmt = $db->prepare("SELECT id FROM transfer_requests WHERE device_id = ? AND status = 'pending'");
    $stmt->execute([$deviceId]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Đã có yêu cầu đang chờ xử lý cho thiết bị này'], 400);
    }
    
    // Create transfer request
    $stmt = $db->prepare("INSERT INTO transfer_requests (device_id, from_user_id, to_user_id, type, note) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$deviceId, $fromUserId, $toUserId, $type, $note]);
    
    $requestId = $db->lastInsertId();
    
    jsonResponse([
        'success' => true,
        'message' => $type == 'transfer' ? 'Đã gửi yêu cầu chuyển giao' : 'Đã gửi yêu cầu mượn thiết bị',
        'request' => [
            'id' => $requestId,
            'device_id' => $deviceId,
            'type' => $type,
            'status' => 'pending'
        ]
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
