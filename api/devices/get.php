<?php
/**
 * Get Single Device API
 * GET: id
 */

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Require authentication
if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$currentUserId = getCurrentUserId();
$deviceId = intval($_GET['id'] ?? 0);

if ($deviceId <= 0) {
    jsonResponse(['success' => false, 'message' => 'Device ID là bắt buộc'], 400);
}

try {
    $db = getDB();
    
    // Get device info with holder
    $stmt = $db->prepare("
        SELECT d.*, u.id as holder_id, u.name as holder_name, u.email as holder_email, u.avatar as holder_avatar
        FROM devices d 
        LEFT JOIN users u ON d.current_holder_id = u.id 
        WHERE d.id = ?
    ");
    $stmt->execute([$deviceId]);
    $device = $stmt->fetch();
    
    if (!$device) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy thiết bị'], 404);
    }
    
    // Add holder alias
    if ($device['holder_id']) {
        $device['holder_alias'] = getUserAlias($currentUserId, $device['holder_id']);
    } else {
        $device['holder_alias'] = null;
    }
    
    jsonResponse([
        'success' => true,
        'device' => $device
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
