<?php
/**
 * Deactivate Member API (Soft Delete)
 * POST: user_id
 * Requires admin role only
 * 
 * This sets user status to 'pending' instead of deleting.
 * The user's history and devices are preserved.
 * User will not appear in transfer lists and cannot receive transfers.
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
    
    if ($user['status'] === 'pending') {
        jsonResponse(['success' => false, 'message' => 'Người dùng đã ở trạng thái chờ duyệt'], 400);
    }
    
    // Cancel all pending transfer requests involving this user
    $stmt = $db->prepare("UPDATE transfer_requests SET status = 'cancelled' WHERE status = 'pending' AND (from_user_id = ? OR to_user_id = ?)");
    $stmt->execute([$userId, $userId]);
    $cancelledCount = $stmt->rowCount();
    
    // Set user status to pending (soft delete)
    // Devices and history are preserved
    $stmt = $db->prepare("UPDATE users SET status = 'pending' WHERE id = ?");
    $stmt->execute([$userId]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Đã vô hiệu hóa thành viên ' . $user['name']
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
