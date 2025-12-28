<?php
/**
 * Activate Member API
 * POST: user_id
 * Requires admin role only
 * 
 * Reactivates an inactive member, setting their status to 'approved'
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
    
    // Check if user exists and is inactive
    $stmt = $db->prepare("SELECT id, name, status FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy người dùng'], 404);
    }
    
    if ($user['status'] !== 'inactive') {
        jsonResponse(['success' => false, 'message' => 'Chỉ có thể kích hoạt lại thành viên đã bị vô hiệu hóa'], 400);
    }
    
    // Reactivate the user
    $stmt = $db->prepare("UPDATE users SET status = 'approved' WHERE id = ?");
    $stmt->execute([$userId]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Đã kích hoạt lại thành viên ' . $user['name']
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
