<?php
/**
 * Upload Device Image API
 * POST: device_id, image (file)
 * Automatically compresses images larger than 1MB
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

// Get device ID
$deviceId = intval($_POST['device_id'] ?? 0);

if ($deviceId <= 0) {
    jsonResponse(['success' => false, 'message' => 'ID thiết bị không hợp lệ'], 400);
}

// Check if image file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File quá lớn (vượt quá giới hạn server)',
        UPLOAD_ERR_FORM_SIZE => 'File quá lớn',
        UPLOAD_ERR_PARTIAL => 'File upload không hoàn tất',
        UPLOAD_ERR_NO_FILE => 'Không có file được upload',
        UPLOAD_ERR_NO_TMP_DIR => 'Lỗi server: thiếu thư mục tạm',
        UPLOAD_ERR_CANT_WRITE => 'Lỗi server: không thể ghi file',
        UPLOAD_ERR_EXTENSION => 'Lỗi server: extension ngăn upload'
    ];
    $errorCode = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
    $message = $errorMessages[$errorCode] ?? 'Lỗi upload không xác định';
    jsonResponse(['success' => false, 'message' => $message], 400);
}

$file = $_FILES['image'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    jsonResponse(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPEG, PNG, GIF, WebP)'], 400);
}

try {
    $db = getDB();
    
    // Check if device exists
    $stmt = $db->prepare("SELECT id, image FROM devices WHERE id = ?");
    $stmt->execute([$deviceId]);
    $device = $stmt->fetch();
    
    if (!$device) {
        jsonResponse(['success' => false, 'message' => 'Không tìm thấy thiết bị'], 404);
    }
    
    // Create uploads directory if not exists
    $uploadDir = __DIR__ . '/../../uploads/devices/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $extension = strtolower($extension) ?: 'jpg';
    $filename = 'device_' . $deviceId . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Check file size and compress if > 1MB
    $fileSize = $file['size'];
    $maxSize = 1 * 1024 * 1024; // 1MB
    
    if ($fileSize > $maxSize) {
        // Compress image
        $compressedPath = compressImage($file['tmp_name'], $mimeType, $filepath, $maxSize);
        if ($compressedPath === false) {
            jsonResponse(['success' => false, 'message' => 'Không thể nén ảnh'], 500);
        }
    } else {
        // Move file directly
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            jsonResponse(['success' => false, 'message' => 'Không thể lưu file'], 500);
        }
    }
    
    // Delete old image if exists
    if ($device['image']) {
        $oldImagePath = $uploadDir . basename($device['image']);
        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }
    }
    
    // Update device with new image path
    $imagePath = 'uploads/devices/' . $filename;
    $stmt = $db->prepare("UPDATE devices SET image = ? WHERE id = ?");
    $stmt->execute([$imagePath, $deviceId]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Upload ảnh thành công',
        'image' => $imagePath
    ]);
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Đã xảy ra lỗi: ' . $e->getMessage()], 500);
}

/**
 * Compress image to reduce file size
 * 
 * @param string $sourcePath Source image path
 * @param string $mimeType MIME type of the image
 * @param string $destPath Destination path
 * @param int $maxSize Maximum file size in bytes
 * @return string|false Destination path on success, false on failure
 */
function compressImage($sourcePath, $mimeType, $destPath, $maxSize) {
    // Create image resource based on type
    switch ($mimeType) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($sourcePath);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($sourcePath);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }
    
    if (!$image) {
        return false;
    }
    
    // Get original dimensions
    $width = imagesx($image);
    $height = imagesy($image);
    
    // Start with quality 90, reduce until file size is acceptable
    $quality = 90;
    $minQuality = 30;
    $scale = 1.0;
    
    // If image is very large, resize it first
    $maxDimension = 1920;
    if ($width > $maxDimension || $height > $maxDimension) {
        if ($width > $height) {
            $newWidth = $maxDimension;
            $newHeight = intval($height * ($maxDimension / $width));
        } else {
            $newHeight = $maxDimension;
            $newWidth = intval($width * ($maxDimension / $height));
        }
        
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG
        if ($mimeType === 'image/png') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }
    
    // Try reducing quality until file size is acceptable
    while ($quality >= $minQuality) {
        // Save to temp file to check size
        $tempFile = sys_get_temp_dir() . '/compress_' . uniqid() . '.jpg';
        
        // Always save as JPEG for compression (best compression ratio)
        imagejpeg($image, $tempFile, $quality);
        
        $fileSize = filesize($tempFile);
        
        if ($fileSize <= $maxSize) {
            // File is small enough, move to destination
            // Update destination extension to jpg since we're saving as JPEG
            $destPath = preg_replace('/\.[^.]+$/', '.jpg', $destPath);
            rename($tempFile, $destPath);
            imagedestroy($image);
            return $destPath;
        }
        
        // Delete temp file
        unlink($tempFile);
        
        // Reduce quality
        $quality -= 10;
    }
    
    // If still too large after quality reduction, resize further
    $currentWidth = imagesx($image);
    $currentHeight = imagesy($image);
    $newWidth = intval($currentWidth * 0.7);
    $newHeight = intval($currentHeight * 0.7);
    
    $resized = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $currentWidth, $currentHeight);
    imagedestroy($image);
    
    // Save with medium quality
    $destPath = preg_replace('/\.[^.]+$/', '.jpg', $destPath);
    imagejpeg($resized, $destPath, 70);
    imagedestroy($resized);
    
    return $destPath;
}
