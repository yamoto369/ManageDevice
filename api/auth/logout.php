<?php
/**
 * User Logout API
 * POST or GET
 */

require_once __DIR__ . '/../../config/database.php';

startSession();

// Destroy session
$_SESSION = [];
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Check if it's an API call or redirect
if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
    jsonResponse(['success' => true, 'message' => 'Đăng xuất thành công']);
} else {
    header('Location: ../../index.php');
    exit;
}
