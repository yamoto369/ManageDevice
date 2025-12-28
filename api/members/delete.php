<?php
/**
 * Deactivate Member API (Soft Delete)
 * POST: user_id
 * Requires admin role only
 * 
 * This sets user status to 'inactive' instead of deleting.
 * The user's history is preserved.
 * User will not appear in transfer lists and cannot receive transfers.
 * Cannot deactivate if user is holding devices.
 */

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Require authentication and admin role
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

requireRole(['admin']);

$currentUserId = getCurrentUserId();

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$userId = intval($input['user_id'] ?? 0);

if ($userId <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID người dùng không hợp lệ'], 400);
}

// Cannot deactivate self
if ($userId === $currentUserId) {
    jsonResponse(['success' => false, 'message' => 'Không thể vô hiệu hóa chính mình'], 400);
}

try {
    $db = getDB();
    
    // Check if user exists and is approved
    $stmt = $db->prepare("SELECT id, name, status FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy người dùng'], 404);
    }
    
    if ($user['status'] !== 'approved') {
        jsonResponse(['success' => false, 'message' => 'Chỉ có thể vô hiệu hóa thành viên đã được phê duyệt'], 400);
    }
    
    // Check if user is holding any devices
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM devices WHERE current_holder_id = ?");
    $stmt->execute([$userId]);
    $deviceCount = $stmt->fetch()['count'];
    
    if ($deviceCount > 0) {
        jsonResponse([
            'success' => false, 
            'message' => 'Thành viên đang giữ ' . $deviceCount . ' thiết bị. Vui lòng chuyển hết thiết bị sang người khác trước khi vô hiệu hóa.'
        ], 400);
    }
    
    // Cancel all pending transfer requests involving this user
    $stmt = $db->prepare("UPDATE transfer_requests SET status = 'cancelled' WHERE status = 'pending' AND (from_user_id = ? OR to_user_id = ?)");
    $stmt->execute([$userId, $userId]);
    $cancelledCount = $stmt->rowCount();
    
    // Set user status to inactive
    // History is preserved
    $stmt = $db->prepare("UPDATE users SET status = 'inactive' WHERE id = ?");
    $stmt->execute([$userId]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Đã vô hiệu hóa thành viên ' . $user['name']
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
