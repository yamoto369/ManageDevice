<?php
/**
 * Database Configuration
 * Kết nối MySQL database cho hệ thống quản lý thiết bị
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'device_manager');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO database connection
 * @return PDO
 */
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'Database connection failed']));
        }
    }
    
    return $pdo;
}

/**
 * Start session if not already started
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

/**
 * Get current logged in user ID
 * @return int|null
 */
function getCurrentUserId() {
    startSession();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current logged in user data
 * @return array|null
 */
function getCurrentUser() {
    startSession();
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id, email, name, avatar, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Require authentication - redirect to login if not authenticated
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Send JSON response
 * @param array $data
 * @param int $statusCode
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Get user's alias for a target user
 * @param int $userId The user who set the alias
 * @param int $targetUserId The user the alias is for
 * @return string|null
 */
function getUserAlias($userId, $targetUserId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT alias FROM user_aliases WHERE user_id = ? AND target_user_id = ?");
    $stmt->execute([$userId, $targetUserId]);
    $result = $stmt->fetch();
    return $result ? $result['alias'] : null;
}

/**
 * Get display name for a user (with alias if set by current user)
 * @param array $user User data with id and name
 * @param int|null $viewerId The user viewing (to check for alias)
 * @return array ['name' => string, 'alias' => string|null]
 */
function getDisplayName($user, $viewerId = null) {
    $alias = null;
    if ($viewerId && $user['id'] != $viewerId) {
        $alias = getUserAlias($viewerId, $user['id']);
    }
    return [
        'name' => $user['name'],
        'alias' => $alias
    ];
}
