<?php
/**
 * User Login API
 * POST: email, password
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

// Validation
if (empty($email) || empty($password)) {
    jsonResponse(['success' => false, 'message' => 'Email và mật khẩu là bắt buộc'], 400);
}

try {
    $db = getDB();
    
    // Find user by email
    $stmt = $db->prepare("SELECT id, email, password, name, avatar FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password'])) {
        jsonResponse(['success' => false, 'message' => 'Email hoặc mật khẩu không đúng'], 401);
    }
    
    // Create session
    startSession();
    $_SESSION['user_id'] = $user['id'];
    
    // Remove password from response
    unset($user['password']);
    
    jsonResponse([
        'success' => true,
        'message' => 'Đăng nhập thành công',
        'user' => $user
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
