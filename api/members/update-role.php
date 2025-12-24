<?php
/**
 * Update Member Role API
 * POST: user_id, role
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
$newRole = trim($input['role'] ?? '');

// Validation
if ($userId <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID người dùng không hợp lệ'], 400);
}

if (!in_array($newRole, ['user', 'mod', 'admin'])) {
    jsonResponse(['success' => false, 'message' => 'Role không hợp lệ'], 400);
}

// Cannot change own role
if ($userId === $currentUserId) {
    jsonResponse(['success' => false, 'message' => 'Không thể thay đổi role của chính mình'], 400);
}

try {
    $db = getDB();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id, name, role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy người dùng'], 404);
    }
    
    // Update role
    $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$newRole, $userId]);
    
    $roleLabels = ['user' => 'User', 'mod' => 'Moderator', 'admin' => 'Admin'];
    
    jsonResponse([
        'success' => true,
        'message' => 'Đã thay đổi role của ' . $user['name'] . ' thành ' . $roleLabels[$newRole]
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
