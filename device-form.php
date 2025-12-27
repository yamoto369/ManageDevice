<?php
/**
 * Device Form Page (Add / Edit)
 * Add: Admin only
 * Edit: Mod and Admin
 */
require_once 'config/database.php';
requireAuth();

// Check for edit mode
$deviceId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$isEditMode = $deviceId > 0;

// Permission check
if ($isEditMode) {
    // Edit mode: mod or admin can edit
    if (!canEditDevices()) {
        header('Location: devices.php');
        exit;
    }
} else {
    // Add mode: admin only
    if (!canManageDevices()) {
        header('Location: devices.php');
        exit;
    }
}

$pageTitle = $isEditMode ? 'Chỉnh sửa thiết bị' : 'Thêm thiết bị mới';
define('PAGE_TITLE', $pageTitle);
require_once 'includes/header.php';
?>

<main class="flex-1 px-4 md:px-40 py-8">
    <div class="max-w-[960px] mx-auto">
        <!-- Breadcrumbs -->
        <div class="flex flex-wrap gap-2 pb-4">
            <a class="text-slate-500 dark:text-slate-400 hover:text-primary transition-colors text-base font-medium leading-normal flex items-center gap-1" href="devices.php">
                <span class="material-symbols-outlined text-lg">inventory_2</span>
                Thiết bị
            </a>
            <span class="text-slate-400 dark:text-slate-600 text-base font-medium leading-normal">/</span>
            <span class="text-slate-900 dark:text-slate-100 text-base font-medium leading-normal"><?php echo $isEditMode ? 'Chỉnh sửa' : 'Thêm Thiết bị Mới'; ?></span>
        </div>
        
        <!-- Page Heading -->
        <div class="flex flex-wrap justify-between gap-3 pb-8">
            <div class="flex min-w-72 flex-col gap-3">
                <h1 class="text-slate-900 dark:text-white text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em]"><?php echo $isEditMode ? 'Chỉnh sửa Thiết bị' : 'Thêm Thiết bị Mới'; ?></h1>
                <p class="text-slate-500 dark:text-slate-400 text-base font-normal leading-normal max-w-2xl">
                    <?php echo $isEditMode ? 'Cập nhật thông tin thiết bị trong hệ thống.' : 'Điền thông tin vào biểu mẫu dưới đây để đăng ký thiết bị mới vào hệ thống quản lý kho nội bộ.'; ?>
                </p>
            </div>
        </div>
        
        <!-- Form Container -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 md:p-8">
            <form id="deviceForm" class="flex flex-col gap-6">
                <input type="hidden" name="id" value="<?php echo $deviceId; ?>">
                <div id="form-error" class="hidden p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm"></div>
                
                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Device Name -->
                    <label class="flex flex-col flex-1 gap-2">
                        <span class="text-slate-900 dark:text-white text-base font-semibold leading-normal">Tên thiết bị <span class="text-red-500">*</span></span>
                        <input name="name" required class="form-input w-full rounded-lg text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-primary focus:ring-primary h-12 px-4 text-base placeholder:text-slate-400 transition-all" placeholder="Ví dụ: MacBook Pro M2 14-inch" type="text"/>
                    </label>
                    
                    <!-- Manufacturer -->
                    <label class="flex flex-col flex-1 gap-2">
                        <span class="text-slate-900 dark:text-white text-base font-semibold leading-normal">Hãng sản xuất</span>
                        <input name="manufacturer" class="form-input w-full rounded-lg text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-primary focus:ring-primary h-12 px-4 text-base placeholder:text-slate-400 transition-all" placeholder="Ví dụ: Apple, Dell, HP" type="text"/>
                    </label>
                </div>
                
                <!-- Technical Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- IMEI/Serial -->
                    <label class="flex flex-col flex-1 gap-2">
                        <span class="text-slate-900 dark:text-white text-base font-semibold leading-normal">IMEI / Serial Number <span class="text-red-500">*</span></span>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                                <span class="material-symbols-outlined">qr_code_2</span>
                            </span>
                            <input name="imei_sn" <?php echo $isEditMode ? 'readonly' : 'required'; ?> class="form-input w-full rounded-lg text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-primary focus:ring-primary h-12 pl-10 pr-4 text-base placeholder:text-slate-400 transition-all font-mono <?php echo $isEditMode ? 'opacity-60 cursor-not-allowed' : ''; ?>" placeholder="Nhập số IMEI hoặc SN" type="text"/>
                        </div>
                        <?php if ($isEditMode): ?>
                        <span class="text-xs text-slate-400">IMEI/SN không thể thay đổi</span>
                        <?php endif; ?>
                    </label>
                    
                    <!-- Status -->
                    <label class="flex flex-col flex-1 gap-2">
                        <span class="text-slate-900 dark:text-white text-base font-semibold leading-normal">Trạng thái</span>
                        <div class="relative">
                            <select name="status" class="form-select w-full cursor-pointer rounded-lg text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-primary focus:ring-primary h-12 px-4 text-base transition-all appearance-none">
                                <option value="available">Sẵn sàng sử dụng</option>
                                <option value="broken">Hỏng</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined">expand_more</span>
                            </div>
                        </div>
                    </label>
                </div>
                
                <!-- Description -->
                <label class="flex flex-col w-full gap-2">
                    <span class="text-slate-900 dark:text-white text-base font-semibold leading-normal">Mô tả chi tiết</span>
                    <textarea name="description" class="form-textarea w-full resize-y rounded-lg text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-primary focus:ring-primary min-h-[120px] p-4 text-base placeholder:text-slate-400 transition-all" placeholder="Ghi chú về tình trạng vật lý, phụ kiện đi kèm hoặc các thông tin đặc biệt khác..."></textarea>
                </label>
                
                <!-- Device Image -->
                <div class="flex flex-col w-full gap-2">
                    <span class="text-slate-900 dark:text-white text-base font-semibold leading-normal">Hình ảnh thiết bị</span>
                    
                    <!-- Image Preview -->
                    <div id="imagePreviewContainer" class="hidden relative group">
                        <img id="imagePreview" src="" alt="Device Preview" class="w-full max-w-md h-auto rounded-lg border border-slate-300 dark:border-slate-600 object-cover">
                        <button type="button" id="removeImage" class="absolute top-2 right-2 p-2 bg-red-500 hover:bg-red-600 text-white rounded-full shadow-lg transition-all opacity-0 group-hover:opacity-100">
                            <span class="material-symbols-outlined text-lg">close</span>
                        </button>
                    </div>
                    
                    <!-- Upload/Capture Buttons -->
                    <div id="imageUploadButtons" class="flex flex-wrap gap-3">
                        <!-- Upload from file -->
                        <label class="flex items-center gap-2 px-4 py-2.5 rounded-lg border-2 border-dashed border-slate-300 dark:border-slate-600 hover:border-primary dark:hover:border-primary cursor-pointer transition-all bg-slate-50 dark:bg-slate-900/50 hover:bg-blue-50 dark:hover:bg-blue-900/20">
                            <span class="material-symbols-outlined text-slate-500 dark:text-slate-400">upload_file</span>
                            <span class="text-slate-700 dark:text-slate-300 font-medium">Chọn ảnh</span>
                            <input type="file" id="imageFile" accept="image/*" class="hidden">
                        </label>
                        
                        <!-- Capture from camera (opens camera modal) -->
                        <button type="button" id="openCameraBtn" class="flex items-center gap-2 px-4 py-2.5 rounded-lg border-2 border-dashed border-slate-300 dark:border-slate-600 hover:border-primary dark:hover:border-primary cursor-pointer transition-all bg-slate-50 dark:bg-slate-900/50 hover:bg-blue-50 dark:hover:bg-blue-900/20">
                            <span class="material-symbols-outlined text-slate-500 dark:text-slate-400">photo_camera</span>
                            <span class="text-slate-700 dark:text-slate-300 font-medium">Chụp ảnh</span>
                        </button>
                    </div>
                    
                    <!-- Upload Progress -->
                    <div id="uploadProgress" class="hidden">
                        <div class="flex items-center gap-3">
                            <div class="flex-1 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div id="uploadProgressBar" class="h-full bg-primary transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <span id="uploadProgressText" class="text-sm text-slate-600 dark:text-slate-400">0%</span>
                        </div>
                    </div>
                    
                    <!-- File size info -->
                    <p class="text-xs text-slate-400 dark:text-slate-500">
                        <span class="material-symbols-outlined text-sm align-middle">info</span>
                        Mỗi thiết bị chỉ hỗ trợ 1 ảnh. Ảnh lớn hơn 1MB sẽ tự động được nén.
                    </p>
                </div>

<!-- Camera Modal -->
<div id="cameraModal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm"></div>
    
    <!-- Modal Content -->
    <div class="relative flex flex-col h-full w-full max-w-2xl mx-auto p-4">
        <!-- Header -->
        <div class="flex items-center justify-between py-3">
            <h3 class="text-white text-lg font-semibold">Chụp ảnh thiết bị</h3>
            <button type="button" id="closeCameraBtn" class="p-2 text-white hover:bg-white/20 rounded-full transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <!-- Camera View -->
        <div class="flex-1 relative bg-black rounded-xl overflow-hidden flex items-center justify-center">
            <video id="cameraVideo" autoplay playsinline class="max-w-full max-h-full object-contain"></video>
            <canvas id="cameraCanvas" class="hidden"></canvas>
            
            <!-- Loading/Error State -->
            <div id="cameraLoading" class="absolute inset-0 flex flex-col items-center justify-center text-white">
                <span class="material-symbols-outlined text-5xl animate-pulse">videocam</span>
                <p class="mt-2">Đang truy cập camera...</p>
            </div>
            <div id="cameraError" class="hidden absolute inset-0 flex flex-col items-center justify-center text-white text-center p-4">
                <span class="material-symbols-outlined text-5xl text-red-400">videocam_off</span>
                <p class="mt-2 text-red-300" id="cameraErrorText">Không thể truy cập camera</p>
                <button type="button" id="retryCameraBtn" class="mt-4 px-4 py-2 bg-primary hover:bg-blue-600 rounded-lg font-medium transition-colors">
                    Thử lại
                </button>
            </div>
        </div>
        
        <!-- Controls -->
        <div class="flex items-center justify-center gap-4 py-4">
            <!-- Switch Camera Button -->
            <button type="button" id="switchCameraBtn" class="p-3 bg-white/20 hover:bg-white/30 text-white rounded-full transition-colors" title="Đổi camera">
                <span class="material-symbols-outlined">cameraswitch</span>
            </button>
            
            <!-- Capture Button -->
            <button type="button" id="captureBtn" class="p-4 bg-white hover:bg-slate-100 text-slate-900 rounded-full shadow-lg transition-all transform active:scale-95" title="Chụp ảnh">
                <span class="material-symbols-outlined text-3xl">photo_camera</span>
            </button>
            
            <!-- Placeholder for symmetry -->
            <div class="p-3 opacity-0">
                <span class="material-symbols-outlined">cameraswitch</span>
            </div>
        </div>
    </div>
</div>
                
                <!-- Action Buttons -->
                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-4 pt-4 mt-2 border-t border-slate-100 dark:border-slate-700">
                    <a href="devices.php" class="w-full sm:w-auto px-6 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors text-center">
                        Hủy bỏ
                    </a>
                    <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-8 py-2.5 rounded-lg bg-primary hover:bg-blue-600 text-white font-bold shadow-lg shadow-blue-500/30 transition-all transform active:scale-95">
                        <span class="material-symbols-outlined text-xl">save</span>
                        <?php echo $isEditMode ? 'Cập nhật' : 'Lưu thiết bị'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
const isEditMode = <?php echo $isEditMode ? 'true' : 'false'; ?>;
const deviceId = <?php echo $deviceId; ?>;
let selectedImageFile = null;
let currentImagePath = null;
let imageRemoved = false; // Track if user removed the existing image

// Image Preview Elements
const imagePreviewContainer = document.getElementById('imagePreviewContainer');
const imagePreview = document.getElementById('imagePreview');
const imageUploadButtons = document.getElementById('imageUploadButtons');
const uploadProgress = document.getElementById('uploadProgress');
const uploadProgressBar = document.getElementById('uploadProgressBar');
const uploadProgressText = document.getElementById('uploadProgressText');

// Camera Elements
const cameraModal = document.getElementById('cameraModal');
const cameraVideo = document.getElementById('cameraVideo');
const cameraCanvas = document.getElementById('cameraCanvas');
const cameraLoading = document.getElementById('cameraLoading');
const cameraError = document.getElementById('cameraError');
const cameraErrorText = document.getElementById('cameraErrorText');

let cameraStream = null;
let currentFacingMode = 'environment'; // 'environment' = back camera, 'user' = front camera

// Show image preview
function showImagePreview(src, isNew = true) {
    imagePreview.src = src;
    imagePreviewContainer.classList.remove('hidden');
    if (isNew) {
        imageUploadButtons.classList.add('hidden');
    }
}

// Hide image preview
function hideImagePreview() {
    imagePreview.src = '';
    imagePreviewContainer.classList.add('hidden');
    imageUploadButtons.classList.remove('hidden');
    selectedImageFile = null;
    // Mark image as removed if we had an existing image
    if (currentImagePath) {
        imageRemoved = true;
    }
}

// Handle file selection
function handleFileSelect(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
        Toast.error('Vui lòng chọn file ảnh');
        return;
    }
    
    selectedImageFile = file;
    
    // Show preview
    const reader = new FileReader();
    reader.onload = (e) => {
        showImagePreview(e.target.result, true);
    };
    reader.readAsDataURL(file);
    
    // Show file size info
    const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
    if (file.size > 1024 * 1024) {
        Toast.info(`Ảnh ${sizeMB}MB sẽ được tự động nén khi lưu`);
    }
}

// ==================== Camera Functions ====================

// Check if camera API is supported
function isCameraSupported() {
    return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
}

// Check if we're on a secure context (HTTPS or localhost)
function isSecureContext() {
    return window.isSecureContext || 
           location.protocol === 'https:' || 
           location.hostname === 'localhost' || 
           location.hostname === '127.0.0.1';
}

// Check camera permission status (if Permissions API is available)
async function checkCameraPermission() {
    try {
        if (navigator.permissions) {
            const result = await navigator.permissions.query({ name: 'camera' });
            return result.state; // 'granted', 'denied', or 'prompt'
        }
    } catch (e) {
        // Permissions API not supported for camera
    }
    return 'prompt'; // Default to prompt if we can't check
}

// Open camera modal and request permission
async function openCamera() {
    // Check if camera API is supported
    if (!isCameraSupported()) {
        Toast.error('Trình duyệt của bạn không hỗ trợ camera');
        return;
    }
    
    // Check if we're on secure context
    if (!isSecureContext()) {
        Toast.error('Cần truy cập qua HTTPS hoặc localhost để sử dụng camera');
        return;
    }
    
    // Check current permission status
    const permissionStatus = await checkCameraPermission();
    
    if (permissionStatus === 'denied') {
        Toast.error('Quyền camera đã bị từ chối. Vui lòng cho phép trong cài đặt trình duyệt.');
        // Show instructions based on device
        showCameraPermissionHelp();
        return;
    }
    
    // Open modal
    cameraModal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Reset states
    cameraLoading.classList.remove('hidden');
    cameraError.classList.add('hidden');
    cameraVideo.classList.add('hidden');
    
    await startCamera();
}

// Show help for enabling camera permission
function showCameraPermissionHelp() {
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const isAndroid = /Android/.test(navigator.userAgent);
    
    let message = '';
    if (isIOS) {
        message = 'Trên iOS: Vào Cài đặt > Safari > Camera > Cho phép';
    } else if (isAndroid) {
        message = 'Trên Android: Nhấn biểu tượng khóa bên cạnh URL > Quyền > Camera > Cho phép';
    } else {
        message = 'Nhấn biểu tượng khóa/camera bên cạnh thanh địa chỉ > Cho phép camera';
    }
    
    // Show as a longer toast or alert
    setTimeout(() => {
        alert('Hướng dẫn bật camera:\n\n' + message);
    }, 100);
}

// Start camera stream
async function startCamera() {
    try {
        // Stop any existing stream
        stopCamera();
        
        // Request camera permission - this will trigger the permission prompt
        const constraints = {
            video: {
                facingMode: currentFacingMode,
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            },
            audio: false
        };
        
        cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
        
        // Show video feed
        cameraVideo.srcObject = cameraStream;
        cameraVideo.classList.remove('hidden');
        cameraLoading.classList.add('hidden');
        cameraError.classList.add('hidden');
        
        // Wait for video to be ready
        await new Promise((resolve) => {
            cameraVideo.onloadedmetadata = resolve;
        });
        await cameraVideo.play();
        
    } catch (error) {
        cameraLoading.classList.add('hidden');
        cameraError.classList.remove('hidden');
        
        // Show appropriate error message
        if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
            cameraErrorText.textContent = 'Bạn cần cấp quyền truy cập camera để chụp ảnh. Vui lòng cho phép khi được hỏi.';
        } else if (error.name === 'NotFoundError' || error.name === 'DevicesNotFoundError') {
            cameraErrorText.textContent = 'Không tìm thấy camera trên thiết bị này';
        } else if (error.name === 'NotReadableError' || error.name === 'TrackStartError') {
            cameraErrorText.textContent = 'Camera đang được sử dụng bởi ứng dụng khác';
        } else if (error.name === 'OverconstrainedError') {
            // Try again with simpler constraints
            try {
                cameraStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                cameraVideo.srcObject = cameraStream;
                cameraVideo.classList.remove('hidden');
                cameraLoading.classList.add('hidden');
                cameraError.classList.add('hidden');
                await cameraVideo.play();
                return;
            } catch (e) {
                cameraErrorText.textContent = 'Không thể khởi động camera với cấu hình yêu cầu';
            }
        } else if (error.name === 'SecurityError') {
            cameraErrorText.textContent = 'Lỗi bảo mật: Cần truy cập qua HTTPS để sử dụng camera';
        } else {
            cameraErrorText.textContent = 'Không thể truy cập camera: ' + error.message;
        }
    }
}

// Stop camera stream
function stopCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
    cameraVideo.srcObject = null;
}

// Close camera modal
function closeCamera() {
    stopCamera();
    cameraModal.classList.add('hidden');
    document.body.style.overflow = '';
}

// Switch between front and back camera
async function switchCamera() {
    currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
    await startCamera();
}

// Capture photo from video stream
function capturePhoto() {
    if (!cameraStream) {
        Toast.error('Camera chưa sẵn sàng');
        return;
    }
    
    // Set canvas size to match video
    cameraCanvas.width = cameraVideo.videoWidth;
    cameraCanvas.height = cameraVideo.videoHeight;
    
    // Draw current frame to canvas
    const ctx = cameraCanvas.getContext('2d');
    
    // Flip horizontally if using front camera
    if (currentFacingMode === 'user') {
        ctx.translate(cameraCanvas.width, 0);
        ctx.scale(-1, 1);
    }
    
    ctx.drawImage(cameraVideo, 0, 0);
    
    // Convert canvas to blob
    cameraCanvas.toBlob((blob) => {
        if (blob) {
            // Create a File object from blob
            const filename = `camera_${Date.now()}.jpg`;
            selectedImageFile = new File([blob], filename, { type: 'image/jpeg' });
            
            // Show preview
            const imageUrl = URL.createObjectURL(blob);
            showImagePreview(imageUrl, true);
            
            // Close camera modal
            closeCamera();
            
            Toast.success('Đã chụp ảnh thành công');
        } else {
            Toast.error('Không thể chụp ảnh');
        }
    }, 'image/jpeg', 0.9);
}

// Camera event listeners
document.getElementById('openCameraBtn').addEventListener('click', openCamera);
document.getElementById('closeCameraBtn').addEventListener('click', closeCamera);
document.getElementById('switchCameraBtn').addEventListener('click', switchCamera);
document.getElementById('captureBtn').addEventListener('click', capturePhoto);
document.getElementById('retryCameraBtn').addEventListener('click', startCamera);

// Close camera on escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !cameraModal.classList.contains('hidden')) {
        closeCamera();
    }
});

// ==================== End Camera Functions ====================

// Upload image to server
async function uploadImage(deviceIdToUpload) {
    if (!selectedImageFile) return { success: true };
    
    const formData = new FormData();
    formData.append('device_id', deviceIdToUpload);
    formData.append('image', selectedImageFile);
    
    // Show progress
    uploadProgress.classList.remove('hidden');
    
    return new Promise((resolve) => {
        const xhr = new XMLHttpRequest();
        
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                uploadProgressBar.style.width = percent + '%';
                uploadProgressText.textContent = percent + '%';
            }
        });
        
        xhr.addEventListener('load', () => {
            uploadProgress.classList.add('hidden');
            try {
                const result = JSON.parse(xhr.responseText);
                resolve(result);
            } catch (e) {
                resolve({ success: false, message: 'Lỗi xử lý phản hồi' });
            }
        });
        
        xhr.addEventListener('error', () => {
            uploadProgress.classList.add('hidden');
            resolve({ success: false, message: 'Lỗi kết nối' });
        });
        
        xhr.open('POST', 'api/devices/upload-image.php');
        xhr.withCredentials = true;
        xhr.send(formData);
    });
}

// Delete image from server
async function deleteImage(deviceIdToDelete) {
    return API.post('api/devices/delete-image.php', { device_id: deviceIdToDelete });
}

// Handle remove image button
document.getElementById('removeImage').addEventListener('click', () => {
    hideImagePreview();
    // Reset file input
    document.getElementById('imageFile').value = '';
});

// File input change handler
document.getElementById('imageFile').addEventListener('change', handleFileSelect);

// Load device data if editing
async function loadDeviceData() {
    if (!isEditMode) return;
    
    const result = await API.get(`api/devices/get.php?id=${deviceId}`);
    if (result.success && result.device) {
        const device = result.device;
        const form = document.getElementById('deviceForm');
        form.querySelector('[name="name"]').value = device.name || '';
        form.querySelector('[name="manufacturer"]').value = device.manufacturer || '';
        form.querySelector('[name="imei_sn"]').value = device.imei_sn || '';
        form.querySelector('[name="status"]').value = device.status || 'available';
        form.querySelector('[name="description"]').value = device.description || '';
        
        // Load existing image if any
        if (device.image) {
            currentImagePath = device.image;
            showImagePreview(device.image, true); // true to hide upload buttons
        }
    } else {
        Toast.error('Không thể tải thông tin thiết bị');
        setTimeout(() => window.location.href = 'devices.php', 1500);
    }
}

document.getElementById('deviceForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const errorEl = document.getElementById('form-error');
    
    const payload = {
        name: formData.get('name'),
        manufacturer: formData.get('manufacturer'),
        status: formData.get('status'),
        description: formData.get('description')
    };
    
    let result;
    let newDeviceId = deviceId;
    
    if (isEditMode) {
        payload.id = deviceId;
        result = await API.post('api/devices/update.php', payload);
    } else {
        payload.imei_sn = formData.get('imei_sn');
        result = await API.post('api/devices/add.php', payload);
        if (result.success && result.device) {
            newDeviceId = result.device.id;
        }
    }
    
    if (result.success) {
        // Handle image: upload new image or delete existing image
        if (selectedImageFile) {
            const uploadResult = await uploadImage(newDeviceId);
            if (!uploadResult.success) {
                Toast.warning('Thiết bị đã lưu nhưng upload ảnh thất bại: ' + uploadResult.message);
            }
        } else if (isEditMode && imageRemoved && !selectedImageFile) {
            // User removed the image and didn't select a new one
            const deleteResult = await deleteImage(newDeviceId);
            if (!deleteResult.success) {
                Toast.warning('Thiết bị đã lưu nhưng xóa ảnh thất bại: ' + deleteResult.message);
            }
        }
        
        Toast.success(isEditMode ? 'Cập nhật thiết bị thành công!' : 'Thêm thiết bị thành công!');
        setTimeout(() => window.location.href = 'devices.php', 1000);
    } else {
        errorEl.textContent = result.message;
        errorEl.classList.remove('hidden');
    }
});

// Load data on page ready
loadDeviceData();
</script>

<?php require_once 'includes/footer.php'; ?>
