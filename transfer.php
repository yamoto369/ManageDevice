<?php
/**
 * Transfer Device Page
 */
require_once 'config/database.php';
requireAuth();

$deviceId = intval($_GET['device_id'] ?? 0);
if ($deviceId <= 0) {
    header('Location: devices.php');
    exit;
}

define('PAGE_TITLE', 'Chuyển giao thiết bị');
require_once 'includes/header.php';

$currentUserId = getCurrentUserId();
?>

<main class="flex-grow flex flex-col items-center py-8 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-[1024px] flex flex-col gap-6">
        <!-- Breadcrumbs -->
        <nav class="flex items-center text-sm text-slate-500 dark:text-slate-400 gap-2">
            <a class="hover:text-primary transition-colors" href="devices.php">Danh sách thiết bị</a>
            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
            <span id="breadcrumb-device" class="text-slate-900 dark:text-white font-medium">Chuyển giao</span>
        </nav>
        
        <!-- Page Heading -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1 class="text-slate-900 dark:text-white text-3xl md:text-4xl font-black tracking-tight mb-2">Chuyển giao thiết bị</h1>
                <p class="text-slate-500 dark:text-slate-400 text-base max-w-2xl">
                    Tạo yêu cầu bàn giao thiết bị cho thành viên khác trong đội ngũ.
                </p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mt-4">
            <!-- Left Column: Device Summary -->
            <div class="lg:col-span-4 flex flex-col gap-6">
                <div id="device-card" class="bg-white dark:bg-[#1a2632] rounded-xl shadow-sm border border-[#e7edf3] dark:border-slate-700 overflow-hidden sticky top-24">
                    <div class="p-5 animate-pulse">
                        <div class="h-32 bg-slate-200 dark:bg-slate-700 rounded-lg mb-4"></div>
                        <div class="h-5 bg-slate-200 dark:bg-slate-700 rounded w-2/3 mb-2"></div>
                        <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/2"></div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Transfer Form -->
            <div class="lg:col-span-8">
                <div class="bg-white dark:bg-[#1a2632] rounded-xl shadow-sm border border-[#e7edf3] dark:border-slate-700 flex flex-col">
                    <!-- Form Content -->
                    <div class="p-6 md:p-8 flex flex-col gap-6">
                        <div id="form-error" class="hidden p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm"></div>
                        <div id="form-info" class="hidden p-4 bg-blue-50 border border-blue-100 rounded-lg flex gap-3">
                            <span class="material-symbols-outlined text-primary shrink-0">info</span>
                            <div>
                                <p id="info-text" class="text-sm text-slate-700"></p>
                            </div>
                        </div>
                        
                        <form id="transferForm">
                            <!-- Select User -->
                            <div class="flex flex-col gap-2 mb-6">
                                <label class="text-sm font-bold text-slate-900 dark:text-white">Chọn người nhận <span class="text-red-500">*</span></label>
                                <select id="to-user" name="to_user_id" required class="form-select w-full rounded-lg border-[#e7edf3] dark:border-slate-600 bg-slate-50 dark:bg-slate-800 p-3 focus:ring-primary focus:border-primary">
                                    <option value="">Đang tải danh sách...</option>
                                </select>
                            </div>
                            
                            <!-- Note -->
                            <div class="flex flex-col gap-2 mb-6">
                                <label class="text-sm font-bold text-slate-900 dark:text-white">Ghi chú</label>
                                <textarea name="note" rows="4" class="form-textarea w-full rounded-lg border-[#e7edf3] dark:border-slate-600 bg-slate-50 dark:bg-slate-800 p-3 focus:ring-primary focus:border-primary resize-none" placeholder="Mô tả tình trạng máy hiện tại, phụ kiện đi kèm..."></textarea>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#e7edf3] dark:border-slate-700">
                                <a href="devices.php" class="px-5 py-2.5 rounded-lg text-sm font-bold text-slate-700 dark:text-white bg-white dark:bg-slate-700 border border-[#e7edf3] dark:border-slate-600 hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors">
                                    Hủy bỏ
                                </a>
                                <button type="submit" class="flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-bold text-white bg-primary hover:bg-blue-600 shadow-sm shadow-blue-200 dark:shadow-none transition-all">
                                    <span class="material-symbols-outlined text-[20px]">send</span>
                                    Gửi yêu cầu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
const deviceId = <?php echo $deviceId; ?>;
const currentUserId = <?php echo $currentUserId; ?>;
let device = null;

async function loadDevice() {
    const result = await API.get(`api/devices/get.php?id=${deviceId}`);
    
    if (result.success) {
        device = result.device;
        
        // Update breadcrumb
        document.getElementById('breadcrumb-device').textContent = `Chuyển giao: ${device.name}`;
        
        // Update device card
        document.getElementById('device-card').innerHTML = `
            <div class="p-4 bg-slate-50 dark:bg-slate-800 border-b border-[#e7edf3] dark:border-slate-700">
                <div class="w-full aspect-video bg-white rounded-lg flex items-center justify-center">
                    <span class="material-symbols-outlined text-6xl text-slate-300">${getDeviceIcon(device.name)}</span>
                </div>
            </div>
            <div class="p-5 flex flex-col gap-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">${device.name}</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">S/N: ${device.imei_sn}</p>
                </div>
                <div class="space-y-3 pt-3 border-t border-[#e7edf3] dark:border-slate-700">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Trạng thái</span>
                        <span>${getStatusBadge(device.status)}</span>
                    </div>
                    ${device.manufacturer ? `
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Hãng</span>
                            <span class="font-medium text-slate-900 dark:text-white">${device.manufacturer}</span>
                        </div>
                    ` : ''}
                    ${device.holder_id ? `
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Đang giữ</span>
                            <span class="font-medium text-slate-900 dark:text-white">${device.holder_alias || device.holder_name}</span>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
        
        // Update info message based on holder
        const infoEl = document.getElementById('form-info');
        const infoText = document.getElementById('info-text');
        
        if (device.holder_id == currentUserId) {
            infoText.innerHTML = '<strong>Bạn đang giữ thiết bị này.</strong> Khi chọn người nhận, yêu cầu chuyển giao sẽ được gửi để họ xác nhận.';
            infoEl.classList.remove('hidden');
        } else if (device.holder_id) {
            infoText.innerHTML = `<strong>Thiết bị đang được ${device.holder_alias || device.holder_name} giữ.</strong> Yêu cầu mượn sẽ được gửi đến người đang giữ để phê duyệt.`;
            infoEl.classList.remove('hidden');
        } else {
            infoText.innerHTML = '<strong>Thiết bị chưa được giao cho ai.</strong> Bạn có thể chỉ định người nhận thiết bị này.';
            infoEl.classList.remove('hidden');
        }
    } else {
        Toast.error('Không thể tải thông tin thiết bị');
    }
}

async function loadUsers() {
    const result = await API.get('api/members/list.php?limit=100');
    
    if (result.success) {
        const select = document.getElementById('to-user');
        let users = result.data.filter(u => u.id != currentUserId);
        
        // If device is broken, only show warehouse users
        if (device && device.status === 'broken') {
            users = users.filter(u => u.role === 'warehouse');
            
            // Show info message about restriction
            const infoEl = document.getElementById('form-info');
            const infoText = document.getElementById('info-text');
            infoText.innerHTML = '<strong>Thiết bị đang ở trạng thái hỏng.</strong> Chỉ có thể chuyển cho người quản lý kho (warehouse role).';
            infoEl.classList.remove('hidden');
        }
        
        if (users.length > 0) {
            select.innerHTML = '<option value="">-- Chọn người nhận --</option>' + 
                users.map(u => {
                    const roleLabel = u.role === 'warehouse' ? ' [Kho]' : '';
                    return `<option value="${u.id}">${u.alias || u.name}${roleLabel} (${u.email})</option>`;
                }).join('');
        } else {
            select.innerHTML = '<option value="">Không có người nhận phù hợp</option>';
        }
    }
}

document.getElementById('transferForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const errorEl = document.getElementById('form-error');
    
    const toUserId = formData.get('to_user_id');
    if (!toUserId) {
        errorEl.textContent = 'Vui lòng chọn người nhận';
        errorEl.classList.remove('hidden');
        return;
    }
    
    const result = await API.post('api/transfers/create.php', {
        device_id: deviceId,
        to_user_id: parseInt(toUserId),
        note: formData.get('note')
    });
    
    if (result.success) {
        Toast.success(result.message);
        setTimeout(() => window.location.href = 'devices.php', 1500);
    } else {
        errorEl.textContent = result.message;
        errorEl.classList.remove('hidden');
    }
});

// Load data
loadDevice();
loadUsers();
</script>

<?php require_once 'includes/footer.php'; ?>
