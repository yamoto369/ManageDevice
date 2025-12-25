<?php
/**
 * Update current user's profile (name and/or password)
 * POST /api/members/update-profile.php
 */
header('Content-Type: application/json');

require_once '../../config/database.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get current user
$currentUser = getCurrentUser();
if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$name = trim($input['name'] ?? '');
$currentPassword = $input['current_password'] ?? '';
$newPassword = $input['new_password'] ?? '';

// Validate name
if (empty($name)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Tên hiển thị không được để trống']);
    exit;
}

try {
    $db = getDB();
    
    // If changing password, verify current password first
    if (!empty($newPassword)) {
        if (empty($currentPassword)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập mật khẩu hiện tại']);
            exit;
        }
        
        if (strlen($newPassword) < 6) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự']);
            exit;
        }
        
        // Get current password hash
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$currentUser['id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Mật khẩu hiện tại không đúng']);
            exit;
        }
        
        // Update both name and password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET name = ?, password = ? WHERE id = ?");
        $stmt->execute([$name, $hashedPassword, $currentUser['id']]);
    } else {
        // Only update name
        $stmt = $db->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->execute([$name, $currentUser['id']]);
    }
    
    // Update session name
    $_SESSION['user_name'] = $name;
    
    echo json_encode([
        'success' => true,
        'message' => 'Cập nhật thông tin thành công',
        'user' => [
            'id' => $currentUser['id'],
            'name' => $name,
            'email' => $currentUser['email']
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
}
