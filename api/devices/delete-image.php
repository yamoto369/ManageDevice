<?php
/**
 * Delete Device Image API
 * POST: device_id
 * Removes the image from a device
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

$deviceId = intval($input['device_id'] ?? 0);

if ($deviceId <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID thiết bị không hợp lệ'], 400);
}

try {
    $db = getDB();
    
    // Get current device image
    $stmt = $db->prepare("SELECT id, image FROM devices WHERE id = ?");
    $stmt->execute([$deviceId]);
    $device = $stmt->fetch();
    
    if (!$device) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy thiết bị'], 404);
    }
    
    // Delete the image file if it exists
    if ($device['image']) {
        $imagePath = __DIR__ . '/../../' . $device['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    // Update device to remove image reference
    $stmt = $db->prepare("UPDATE devices SET image = NULL WHERE id = ?");
    $stmt->execute([$deviceId]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Đã xóa ảnh thiết bị'
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
