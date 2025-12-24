<?php
/**
 * Delete Device API
 * POST: id
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

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$id = intval($input['id'] ?? 0);

if ($id <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID thiết bị không hợp lệ'], 400);
}

try {
    $db = getDB();
    
    // Check if device exists
    $stmt = $db->prepare("SELECT id, name FROM devices WHERE id = ?");
    $stmt->execute([$id]);
    $device = $stmt->fetch();
    
    if (!$device) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy thiết bị'], 404);
    }
    
    // Delete device (cascades to transfer_history and transfer_requests via ON DELETE CASCADE)
    $stmt = $db->prepare("DELETE FROM devices WHERE id = ?");
    $stmt->execute([$id]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Đã xóa thiết bị ' . $device['name']
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
