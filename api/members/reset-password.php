<?php
/**
 * Reset Password API
 * POST: user_id
 * Requires admin role
 * Resets password to default: 123456
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
$currentUserId = getCurrentUserId();

// Validation
if ($userId <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID thành viên không hợp lệ'], 400);
}

// Cannot reset own password
if ($userId === $currentUserId) {
    jsonResponse(['success' => false, 'message' => 'Không thể reset password của chính mình'], 400);
}

try {
    $db = getDB();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id, name FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy thành viên'], 404);
    }
    
    // Reset password to default: 123456
    $defaultPassword = '123456';
    $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $userId]);
    
    jsonResponse([
        'success' => true,
        'message' => "Đã reset password của {$user['name']} về mặc định (123456)"
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
