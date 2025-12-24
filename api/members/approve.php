<?php
/**
 * Approve Member API
 * POST: user_id
 * Requires mod or admin role
 */

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Require authentication and mod/admin role
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

requireRole(['mod', 'admin']);

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$userId = intval($input['user_id'] ?? 0);

if ($userId <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID người dùng không hợp lệ'], 400);
}

try {
    $db = getDB();
    
    // Check if user exists and is pending
    $stmt = $db->prepare("SELECT id, name, status FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy người dùng'], 404);
    }
    
    if ($user['status'] === 'approved') {
        jsonResponse(['success' => false, 'message' => 'Người dùng đã được phê duyệt'], 400);
    }
    
    // Approve the user
    $stmt = $db->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
    $stmt->execute([$userId]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Đã phê duyệt tài khoản ' . $user['name']
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
