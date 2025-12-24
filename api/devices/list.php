<?php
/**
 * List Devices API
 * GET: search, status, page, limit
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
$status = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = min(50, max(1, intval($_GET['limit'] ?? 10)));
$offset = ($page - 1) * $limit;

try {
    $db = getDB();
    
    // Build query
    $where = [];
    $params = [];
    
    if (!empty($search)) {
        $where[] = "(d.name LIKE ? OR d.imei_sn LIKE ? OR u.name LIKE ?)";
        $searchParam = "%{$search}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    if (!empty($status) && in_array($status, ['available', 'in_use', 'broken', 'maintenance'])) {
        $where[] = "d.status = ?";
        $params[] = $status;
    }
    
    $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Count total
    $countSql = "SELECT COUNT(*) as total FROM devices d 
                 LEFT JOIN users u ON d.current_holder_id = u.id 
                 {$whereClause}";
    $stmt = $db->prepare($countSql);
    $stmt->execute($params);
    $total = $stmt->fetch()['total'];
    
    // Get devices with holder info
    $sql = "SELECT d.*, u.id as holder_id, u.name as holder_name, u.email as holder_email, u.avatar as holder_avatar
            FROM devices d
            LEFT JOIN users u ON d.current_holder_id = u.id
            {$whereClause}
            ORDER BY d.created_at DESC
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $devices = $stmt->fetchAll();
    
    // Add alias info for current user
    foreach ($devices as &$device) {
        if ($device['holder_id']) {
            $alias = getUserAlias($currentUserId, $device['holder_id']);
            $device['holder_alias'] = $alias;
        } else {
            $device['holder_alias'] = null;
        }
    }
    
    jsonResponse([
        'success' => true,
        'data' => $devices,
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
