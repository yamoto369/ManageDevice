<?php
/**
 * Delete Member API
 * POST: user_id
 * Requires admin role only
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

// Cannot delete self
if ($userId === $currentUserId) {
    jsonResponse(['success' => false, 'message' => 'Không thể xóa chính mình'], 400);
}

try {
    $db = getDB();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id, name FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy người dùng'], 404);
    }
    
    // Transfer devices to null (unassign) before deletion
    $stmt = $db->prepare("UPDATE devices SET current_holder_id = NULL WHERE current_holder_id = ?");
    $stmt->execute([$userId]);
    
    // Delete user (cascades to user_aliases, transfer_requests, transfer_history)
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Đã xóa thành viên ' . $user['name']
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
