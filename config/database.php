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
            $pdo->exec("SET time_zone = '+07:00'");
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
    $stmt = $db->prepare("SELECT id, email, name, avatar, role, status, created_at FROM users WHERE id = ?");
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
 * Require user to be approved - redirect to pending page if not
 */
function requireApproved() {
    $user = getCurrentUser();
    if (!$user || $user['status'] !== 'approved') {
        header('Location: pending.php');
        exit;
    }
}

/**
 * Get current user's role
 * @return string|null
 */
function getUserRole() {
    $user = getCurrentUser();
    return $user ? $user['role'] : null;
}

/**
 * Get current user's status
 * @return string|null
 */
function getUserStatus() {
    $user = getCurrentUser();
    return $user ? $user['status'] : null;
}

/**
 * Check if current user has specific role
 * @param string $role
 * @return bool
 */
function hasRole($role) {
    return getUserRole() === $role;
}

/**
 * Check if current user is admin
 * @return bool
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Check if current user is mod
 * @return bool
 */
function isMod() {
    return hasRole('mod');
}

/**
 * Check if current user can approve members (mod or admin)
 * @return bool
 */
function canApproveMembers() {
    $role = getUserRole();
    return in_array($role, ['mod', 'admin']);
}

/**
 * Check if current user can edit devices (mod or admin)
 * @return bool
 */
function canEditDevices() {
    $role = getUserRole();
    return in_array($role, ['mod', 'admin']);
}

/**
 * Check if current user can manage devices (add/delete - admin only)
 * @return bool
 */
function canManageDevices() {
    return isAdmin();
}

/**
 * Require specific role(s) - for API endpoints
 * @param array $allowedRoles
 */
function requireRole($allowedRoles) {
    $user = getCurrentUser();
    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
    }
    if ($user['status'] !== 'approved') {
        jsonResponse(['success' => false, 'message' => 'Tài khoản chưa được phê duyệt'], 403);
    }
    if (!in_array($user['role'], $allowedRoles)) {
        jsonResponse(['success' => false, 'message' => 'Không có quyền thực hiện thao tác này'], 403);
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

/**
 * Check if current user has warehouse role
 * @return bool
 */
function isWarehouse() {
    return hasRole('warehouse');
}

/**
 * Get the first user with warehouse role (for auto-assignment)
 * Falls back to first admin if no warehouse user exists
 * @return int|null
 */
function getFirstWarehouseUserId() {
    $db = getDB();
    
    // First try to find a warehouse user
    $stmt = $db->prepare("SELECT id FROM users WHERE role = 'warehouse' AND status = 'approved' ORDER BY id ASC LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        return $result['id'];
    }
    
    // Fallback to first admin
    $stmt = $db->prepare("SELECT id FROM users WHERE role = 'admin' AND status = 'approved' ORDER BY id ASC LIMIT 1");
    $stmt->execute();
    $result = $stmt->fetch();
    
    return $result ? $result['id'] : null;
}

