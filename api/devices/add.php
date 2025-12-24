<?php
/**
 * Add Device API
 * POST: name, imei_sn, manufacturer, status, description
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

$name = trim($input['name'] ?? '');
$imei_sn = trim($input['imei_sn'] ?? '');
$manufacturer = trim($input['manufacturer'] ?? '');
$status = $input['status'] ?? 'available';
$description = trim($input['description'] ?? '');

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Tên thiết bị là bắt buộc';
}

if (empty($imei_sn)) {
    $errors[] = 'IMEI/Serial Number là bắt buộc';
}

if (!in_array($status, ['available', 'in_use', 'broken', 'maintenance'])) {
    $status = 'available';
}

if (!empty($errors)) {
    jsonResponse(['success' => false, 'message' => implode(', ', $errors)], 400);
}

try {
    $db = getDB();
    
    // Check if IMEI/SN already exists
    $stmt = $db->prepare("SELECT id FROM devices WHERE imei_sn = ?");
    $stmt->execute([$imei_sn]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'IMEI/Serial Number đã tồn tại'], 400);
    }
    
    // Insert device
    $stmt = $db->prepare("INSERT INTO devices (name, imei_sn, manufacturer, status, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $imei_sn, $manufacturer, $status, $description]);
    
    $deviceId = $db->lastInsertId();
    
    jsonResponse([
        'success' => true,
        'message' => 'Thêm thiết bị thành công',
        'device' => [
            'id' => $deviceId,
            'name' => $name,
            'imei_sn' => $imei_sn,
            'manufacturer' => $manufacturer,
            'status' => $status,
            'description' => $description
        ]
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
