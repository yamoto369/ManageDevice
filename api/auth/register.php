<?php
/**
 * User Registration API
 * POST: email, password, name
 */

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$name = trim($input['name'] ?? '');

// Validation
$errors = [];

if (empty($email)) {
    $errors[] = 'Email là bắt buộc';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email không hợp lệ';
}

if (empty($password)) {
    $errors[] = 'Mật khẩu là bắt buộc';
} elseif (strlen($password) < 6) {
    $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
}

if (empty($name)) {
    $errors[] = 'Tên là bắt buộc';
}

if (!empty($errors)) {
    jsonResponse(['success' => false, 'message' => implode(', ', $errors)], 400);
}

try {
    $db = getDB();
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'message' => 'Email đã được sử dụng'], 400);
    }
    
    // Check if this is the first user (will be admin)
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users");
    $stmt->execute();
    $userCount = $stmt->fetch()['count'];
    
    $isFirstUser = ($userCount == 0);
    $role = $isFirstUser ? 'admin' : 'user';
    $status = $isFirstUser ? 'approved' : 'pending';
    
    // Hash password and insert user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("INSERT INTO users (email, password, name, role, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$email, $hashedPassword, $name, $role, $status]);
    
    $userId = $db->lastInsertId();
    
    // Auto login only for approved users (first user)
    if ($status === 'approved') {
        startSession();
        $_SESSION['user_id'] = $userId;
        
        jsonResponse([
            'success' => true,
            'message' => 'Đăng ký thành công',
            'user' => [
                'id' => $userId,
                'email' => $email,
                'name' => $name,
                'role' => $role,
                'status' => $status
            ]
        ]);
    } else {
        // Pending user - don't auto login
        jsonResponse([
            'success' => true,
            'message' => 'Đăng ký thành công. Vui lòng chờ admin phê duyệt tài khoản của bạn.',
            'pending' => true,
            'user' => [
                'id' => $userId,
                'email' => $email,
                'name' => $name,
                'role' => $role,
                'status' => $status
            ]
        ]);
    }
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
