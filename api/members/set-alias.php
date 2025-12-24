<?php
/**
 * Set User Alias API
 * POST: target_user_id, alias
 * 
 * Creates or updates alias for a target user (visible only to current user)
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

$targetUserId = intval($input['target_user_id'] ?? 0);
$alias = trim($input['alias'] ?? '');

// Validation
if ($targetUserId <= 0) {
    jsonResponse(['success' => false, 'message' => 'Target user ID là bắt buộc'], 400);
}

if ($targetUserId == $currentUserId) {
    jsonResponse(['success' => false, 'message' => 'Không thể đặt biệt danh cho chính mình'], 400);
}

try {
    $db = getDB();
    
    // Check if target user exists
    $stmt = $db->prepare("SELECT id, name FROM users WHERE id = ?");
    $stmt->execute([$targetUserId]);
    $targetUser = $stmt->fetch();
    
    if (!$targetUser) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy người dùng'], 404);
    }
    
    if (empty($alias)) {
        // Delete alias if empty
        $stmt = $db->prepare("DELETE FROM user_aliases WHERE user_id = ? AND target_user_id = ?");
        $stmt->execute([$currentUserId, $targetUserId]);
        
        jsonResponse([
            'success' => true,
            'message' => 'Đã xóa biệt danh'
        ]);
    } else {
        // Insert or update alias
        $stmt = $db->prepare("
            INSERT INTO user_aliases (user_id, target_user_id, alias) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE alias = VALUES(alias)
        ");
        $stmt->execute([$currentUserId, $targetUserId, $alias]);
        
        jsonResponse([
            'success' => true,
            'message' => 'Đã lưu biệt danh',
            'alias' => $alias
        ]);
    }
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
