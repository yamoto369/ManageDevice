<?php
/**
 * Device History API
 * GET: device_id
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
$deviceId = intval($_GET['device_id'] ?? 0);

if ($deviceId <= 0) {
    jsonResponse(['success' => false, 'message' => 'Device ID là bắt buộc'], 400);
}

try {
    $db = getDB();
    
    // Get device info
    $stmt = $db->prepare("SELECT d.*, u.name as holder_name, u.email as holder_email 
                          FROM devices d 
                          LEFT JOIN users u ON d.current_holder_id = u.id 
                          WHERE d.id = ?");
    $stmt->execute([$deviceId]);
    $device = $stmt->fetch();
    
    if (!$device) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy thiết bị'], 404);
    }
    
    // Get transfer history
    $stmt = $db->prepare("
        SELECT th.*, 
               fu.name as from_user_name, fu.email as from_user_email,
               tu.name as to_user_name, tu.email as to_user_email
        FROM transfer_history th
        LEFT JOIN users fu ON th.from_user_id = fu.id
        LEFT JOIN users tu ON th.to_user_id = tu.id
        WHERE th.device_id = ?
        ORDER BY th.created_at DESC
    ");
    $stmt->execute([$deviceId]);
    $history = $stmt->fetchAll();
    
    // Add aliases for current user
    foreach ($history as &$record) {
        if ($record['from_user_id']) {
            $record['from_user_alias'] = getUserAlias($currentUserId, $record['from_user_id']);
        }
        if ($record['to_user_id']) {
            $record['to_user_alias'] = getUserAlias($currentUserId, $record['to_user_id']);
        }
    }
    
    // Add holder alias
    if ($device['current_holder_id']) {
        $device['holder_alias'] = getUserAlias($currentUserId, $device['current_holder_id']);
    } else {
        $device['holder_alias'] = null;
    }
    
    jsonResponse([
        'success' => true,
        'device' => $device,
        'history' => $history
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
