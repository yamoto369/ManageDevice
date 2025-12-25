<?php
/**
 * Update Device API
 * POST: id, name, manufacturer, status, description
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

$id = intval($input['id'] ?? 0);
$name = trim($input['name'] ?? '');
$manufacturer = trim($input['manufacturer'] ?? '');
$status = $input['status'] ?? '';
$description = trim($input['description'] ?? '');

// Validation
if ($id <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID thiết bị không hợp lệ'], 400);
}

if (empty($name)) {
    jsonResponse(['success' => false, 'message' => 'Tên thiết bị là bắt buộc'], 400);
}

if (!in_array($status, ['available', 'broken'])) {
    jsonResponse(['success' => false, 'message' => 'Trạng thái không hợp lệ'], 400);
}

try {
    $db = getDB();
    
    // Check if device exists
    $stmt = $db->prepare("SELECT id FROM devices WHERE id = ?");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy thiết bị'], 404);
    }
    
    // Update device
    $stmt = $db->prepare("UPDATE devices SET name = ?, manufacturer = ?, status = ?, description = ? WHERE id = ?");
    $stmt->execute([$name, $manufacturer, $status, $description, $id]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Cập nhật thiết bị thành công',
        'device' => [
            'id' => $id,
            'name' => $name,
            'manufacturer' => $manufacturer,
            'status' => $status,
            'description' => $description
        ]
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
