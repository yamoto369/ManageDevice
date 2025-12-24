<?php
/**
 * Pending Transfer Requests Page
 */
require_once 'config/database.php';
requireAuth();

define('PAGE_TITLE', 'Yêu cầu chờ xác nhận');
require_once 'includes/header.php';

$currentUserId = getCurrentUserId();
?>

<main class="flex-1 overflow-y-auto p-4 md:p-8 lg:px-12 xl:px-16">
    <div class="max-w-7xl mx-auto flex flex-col gap-6 pb-12">
        <!-- Page Heading -->
        <div class="flex flex-col gap-2">
            <h2 class="text-3xl md:text-4xl font-black text-slate-900 dark:text-white tracking-tight">Yêu cầu Chuyển giao</h2>
            <p class="text-slate-500 dark:text-slate-400 text-base">Danh sách các yêu cầu đang chờ bạn xác nhận.</p>
        </div>
        
        <!-- Stats Cards -->
        <div id="stats" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="flex flex-col gap-2 rounded-xl p-6 bg-white dark:bg-[#1a2632] border border-[#e7edf3] dark:border-slate-700 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400">
                        <span class="material-symbols-outlined">pending_actions</span>
                    </div>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Đang chờ xử lý</p>
                </div>
                <p id="stat-pending" class="text-slate-900 dark:text-white text-3xl font-bold mt-2">0</p>
            </div>
        </div>
        
        <!-- Requests List -->
        <div id="requests-list" class="flex flex-col gap-4">
            <div class="text-center py-8 text-slate-500">
                <span class="material-symbols-outlined text-4xl mb-2">hourglass_empty</span>
                <p>Đang tải...</p>
            </div>
        </div>
    </div>
</main>

<script>
const currentUserId = <?php echo $currentUserId; ?>;

async function loadPendingRequests() {
    const result = await API.get('api/transfers/pending.php');
    const container = document.getElementById('requests-list');
    
    if (result.success) {
        document.getElementById('stat-pending').textContent = result.count;
        
        if (result.data.length > 0) {
            container.innerHTML = result.data.map(req => `
                <div class="group bg-white dark:bg-[#1a2632] p-5 rounded-xl border border-[#e7edf3] dark:border-slate-700 shadow-sm hover:border-primary/50 transition-all duration-200 flex flex-col md:flex-row gap-6 items-start md:items-center">
                    <!-- Device Info -->
                    <div class="flex items-center gap-4 min-w-[280px]">
                        <div class="h-14 w-14 rounded-lg bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-2xl text-slate-400">${getDeviceIcon(req.device_name)}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-slate-900 dark:text-white font-semibold">${req.device_name}</span>
                            <span class="text-slate-500 dark:text-slate-400 text-sm">SN: ${req.device_imei}</span>
                        </div>
                    </div>
                    
                    <!-- Transfer Info -->
                    <div class="flex-1 flex flex-col sm:flex-row items-start sm:items-center gap-4 min-w-[250px]">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                                ${req.from_user_name.charAt(0).toUpperCase()}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-slate-900 dark:text-white">${req.from_user_alias || req.from_user_name}</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">${req.type === 'transfer' ? 'Chuyển giao' : 'Yêu cầu mượn'}</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-slate-400 rotate-90 sm:rotate-0 mx-2">arrow_right_alt</span>
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-bold text-xs">
                                ${req.to_user_name.charAt(0).toUpperCase()}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-slate-900 dark:text-white">${req.to_user_alias || req.to_user_name}</span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">Người nhận</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Meta & Actions -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full md:w-auto justify-between md:justify-end">
                        <div class="flex flex-col items-start md:items-end gap-1">
                            <span class="px-2.5 py-1 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 text-xs font-semibold tracking-wide uppercase">Đang chờ</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">${timeAgo(req.created_at)}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button onclick="rejectRequest(${req.id})" class="h-10 px-4 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-medium text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                                Từ chối
                            </button>
                            <button onclick="confirmRequest(${req.id})" class="h-10 px-4 rounded-lg bg-primary text-white font-medium text-sm hover:bg-primary/90 transition-colors shadow-sm shadow-blue-500/30 flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">check</span>
                                Xác nhận
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = `
                <div class="text-center py-12 text-slate-500">
                    <span class="material-symbols-outlined text-5xl mb-3">inbox</span>
                    <p class="text-lg font-medium">Không có yêu cầu nào</p>
                    <p class="text-sm">Bạn sẽ thấy các yêu cầu chuyển giao tại đây</p>
                </div>
            `;
        }
    }
}

async function confirmRequest(requestId) {
    if (!confirm('Xác nhận nhận thiết bị này?')) return;
    
    const result = await API.post('api/transfers/confirm.php', { request_id: requestId });
    
    if (result.success) {
        Toast.success(result.message);
        loadPendingRequests();
        checkNotifications();
    } else {
        Toast.error(result.message);
    }
}

async function rejectRequest(requestId) {
    if (!confirm('Từ chối yêu cầu này?')) return;
    
    const result = await API.post('api/transfers/reject.php', { request_id: requestId });
    
    if (result.success) {
        Toast.success(result.message);
        loadPendingRequests();
        checkNotifications();
    } else {
        Toast.error(result.message);
    }
}

loadPendingRequests();
</script>

<?php require_once 'includes/footer.php'; ?>
