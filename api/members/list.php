<?php
/**
 * List Members API
 * GET: search, page, limit
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

// Get query parameters
$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = min(50, max(1, intval($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

try {
    $db = getDB();
    
    // Build query
    $where = [];
    $params = [];
    
    if (!empty($search)) {
        $where[] = "(u.name LIKE ? OR u.email LIKE ?)";
        $searchParam = "%{$search}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Count total
    $countSql = "SELECT COUNT(*) as total FROM users u {$whereClause}";
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    
    // Get members
    $sql = "SELECT u.id, u.email, u.name, u.avatar, u.role, u.status, u.created_at
            FROM users u
            {$whereClause}
            ORDER BY u.name ASC
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $members = $stmt->fetchAll();
    
    // Get devices and aliases for each member
    foreach ($members as &$member) {
        // Get alias
        $member['alias'] = getUserAlias($currentUserId, $member['id']);
        
        // Get devices this member is holding
        $stmt = $db->prepare("SELECT id, name, imei_sn, status FROM devices WHERE current_holder_id = ?");
        $stmt->execute([$member['id']]);
        $member['devices'] = $stmt->fetchAll();
        $member['device_count'] = count($member['devices']);
    }
    
    // Count pending members (for header badge)
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE status = 'pending'");
    $stmt->execute();
    $pendingCount = $stmt->fetch()['count'];
    
    jsonResponse([
        'success' => true,
        'data' => $members,
        'pending_count' => $pendingCount,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'total_pages' => ceil($total / $limit)
        ]
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi'], 500);
}
