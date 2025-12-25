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
        
        <!-- Filter Boxes -->
        <div id="filter-boxes" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Pending Filter -->
            <button onclick="toggleFilter('pending')" id="filter-pending" data-active="true"
                class="filter-box flex flex-col gap-2 rounded-xl p-6 bg-white dark:bg-[#1a2632] border-2 border-orange-400 dark:border-orange-500 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer text-left">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400">
                            <span class="material-symbols-outlined">pending_actions</span>
                        </div>
                        <p class="text-slate-700 dark:text-slate-300 text-sm font-medium">Đang chờ xử lý</p>
                    </div>
                    <div class="toggle-indicator w-10 h-6 rounded-full bg-orange-400 dark:bg-orange-500 flex items-center px-1 transition-all">
                        <div class="w-4 h-4 rounded-full bg-white shadow-sm transform translate-x-4 transition-transform"></div>
                    </div>
                </div>
                <p id="stat-pending" class="text-slate-900 dark:text-white text-3xl font-bold mt-2">0</p>
            </button>
            
            <!-- Confirmed Filter -->
            <button onclick="toggleFilter('confirmed')" id="filter-confirmed" data-active="true"
                class="filter-box flex flex-col gap-2 rounded-xl p-6 bg-white dark:bg-[#1a2632] border-2 border-green-400 dark:border-green-500 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer text-left">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                            <span class="material-symbols-outlined">check_circle</span>
                        </div>
                        <p class="text-slate-700 dark:text-slate-300 text-sm font-medium">Đã xác nhận</p>
                    </div>
                    <div class="toggle-indicator w-10 h-6 rounded-full bg-green-400 dark:bg-green-500 flex items-center px-1 transition-all">
                        <div class="w-4 h-4 rounded-full bg-white shadow-sm transform translate-x-4 transition-transform"></div>
                    </div>
                </div>
                <p id="stat-confirmed" class="text-slate-900 dark:text-white text-3xl font-bold mt-2">0</p>
            </button>
            
            <!-- Rejected Filter -->
            <button onclick="toggleFilter('rejected')" id="filter-rejected" data-active="true"
                class="filter-box flex flex-col gap-2 rounded-xl p-6 bg-white dark:bg-[#1a2632] border-2 border-red-400 dark:border-red-500 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer text-left">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                            <span class="material-symbols-outlined">cancel</span>
                        </div>
                        <p class="text-slate-700 dark:text-slate-300 text-sm font-medium">Đã từ chối</p>
                    </div>
                    <div class="toggle-indicator w-10 h-6 rounded-full bg-red-400 dark:bg-red-500 flex items-center px-1 transition-all">
                        <div class="w-4 h-4 rounded-full bg-white shadow-sm transform translate-x-4 transition-transform"></div>
                    </div>
                </div>
                <p id="stat-rejected" class="text-slate-900 dark:text-white text-3xl font-bold mt-2">0</p>
            </button>
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

// Filter state - all active by default
const filters = {
    pending: true,
    confirmed: true,
    rejected: true
};

// Status badge configurations
const statusConfig = {
    pending: {
        label: 'Đang chờ',
        bgClass: 'bg-orange-100 dark:bg-orange-900/30',
        textClass: 'text-orange-700 dark:text-orange-400'
    },
    confirmed: {
        label: 'Đã xác nhận',
        bgClass: 'bg-green-100 dark:bg-green-900/30',
        textClass: 'text-green-700 dark:text-green-400'
    },
    rejected: {
        label: 'Đã từ chối',
        bgClass: 'bg-red-100 dark:bg-red-900/30',
        textClass: 'text-red-700 dark:text-red-400'
    }
};

function toggleFilter(status) {
    filters[status] = !filters[status];
    updateFilterUI(status);
    loadRequests();
}

function updateFilterUI(status) {
    const box = document.getElementById(`filter-${status}`);
    const isActive = filters[status];
    box.dataset.active = isActive;
    
    const toggle = box.querySelector('.toggle-indicator');
    const dot = toggle.querySelector('div');
    
    const colorMap = {
        pending: { active: 'bg-orange-400 dark:bg-orange-500', inactive: 'bg-slate-300 dark:bg-slate-600' },
        confirmed: { active: 'bg-green-400 dark:bg-green-500', inactive: 'bg-slate-300 dark:bg-slate-600' },
        rejected: { active: 'bg-red-400 dark:bg-red-500', inactive: 'bg-slate-300 dark:bg-slate-600' }
    };
    
    const borderMap = {
        pending: { active: 'border-orange-400 dark:border-orange-500', inactive: 'border-slate-200 dark:border-slate-700' },
        confirmed: { active: 'border-green-400 dark:border-green-500', inactive: 'border-slate-200 dark:border-slate-700' },
        rejected: { active: 'border-red-400 dark:border-red-500', inactive: 'border-slate-200 dark:border-slate-700' }
    };
    
    // Update toggle background
    toggle.classList.remove(...colorMap[status].active.split(' '), ...colorMap[status].inactive.split(' '));
    toggle.classList.add(...(isActive ? colorMap[status].active : colorMap[status].inactive).split(' '));
    
    // Update dot position
    if (isActive) {
        dot.classList.add('translate-x-4');
        dot.classList.remove('translate-x-0');
    } else {
        dot.classList.remove('translate-x-4');
        dot.classList.add('translate-x-0');
    }
    
    // Update box border
    box.classList.remove(...borderMap[status].active.split(' '), ...borderMap[status].inactive.split(' '));
    box.classList.add(...(isActive ? borderMap[status].active : borderMap[status].inactive).split(' '));
    
    // Update opacity
    box.style.opacity = isActive ? '1' : '0.6';
}

function getActiveStatuses() {
    return Object.entries(filters)
        .filter(([_, active]) => active)
        .map(([status]) => status);
}

async function loadRequests() {
    const activeStatuses = getActiveStatuses();
    const container = document.getElementById('requests-list');
    
    if (activeStatuses.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12 text-slate-500">
                <span class="material-symbols-outlined text-5xl mb-3">filter_alt_off</span>
                <p class="text-lg font-medium">Không có bộ lọc nào được chọn</p>
                <p class="text-sm">Hãy bật ít nhất một bộ lọc để xem yêu cầu</p>
            </div>
        `;
        return;
    }
    
    const result = await API.get(`api/transfers/pending.php?statuses=${activeStatuses.join(',')}`);
    
    if (result.success) {
        // Update counts (always show total counts for each status)
        document.getElementById('stat-pending').textContent = result.counts.pending || 0;
        document.getElementById('stat-confirmed').textContent = result.counts.confirmed || 0;
        document.getElementById('stat-rejected').textContent = result.counts.rejected || 0;
        
        if (result.data.length > 0) {
            container.innerHTML = result.data.map(req => {
                const config = statusConfig[req.status] || statusConfig.pending;
                const isRecipient = req.to_user_id == currentUserId;
                
                return `
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
                            <div class="h-8 w-8 rounded-full ${req.is_initiator ? 'bg-blue-100 ring-2 ring-blue-400' : 'bg-primary/10'} flex items-center justify-center text-primary font-bold text-xs">
                                ${req.from_user_name.charAt(0).toUpperCase()}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-slate-900 dark:text-white">
                                    ${req.from_user_alias || req.from_user_name}
                                    ${req.is_initiator ? '<span class="text-xs text-blue-500 ml-1">(Bạn)</span>' : ''}
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">${req.type === 'transfer' ? 'Chuyển giao' : 'Yêu cầu mượn'}</span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-slate-400 rotate-90 sm:rotate-0 mx-2">arrow_right_alt</span>
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-full ${isRecipient && req.status === 'pending' ? 'bg-green-100 ring-2 ring-green-400' : 'bg-green-100'} flex items-center justify-center text-green-700 font-bold text-xs">
                                ${req.to_user_name.charAt(0).toUpperCase()}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-slate-900 dark:text-white">
                                    ${req.to_user_alias || req.to_user_name}
                                    ${isRecipient ? '<span class="text-xs text-green-500 ml-1">(Bạn)</span>' : ''}
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">Người nhận</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Meta & Actions -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full md:w-auto justify-between md:justify-end">
                        <div class="flex flex-col items-start md:items-end gap-1">
                            <span class="px-2.5 py-1 rounded-full ${config.bgClass} ${config.textClass} text-xs font-semibold tracking-wide uppercase">${config.label}</span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">${timeAgo(req.created_at)}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            ${req.can_cancel ? `
                                <button onclick="cancelRequest(${req.id})" class="h-10 px-4 rounded-lg border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 font-medium text-sm hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">cancel</span>
                                    Hủy yêu cầu
                                </button>
                            ` : ''}
                            ${req.can_respond ? `
                                <button onclick="rejectRequest(${req.id})" class="h-10 px-4 rounded-lg border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-medium text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                    Từ chối
                                </button>
                                <button onclick="confirmRequest(${req.id})" class="h-10 px-4 rounded-lg bg-primary text-white font-medium text-sm hover:bg-primary/90 transition-colors shadow-sm shadow-blue-500/30 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">check</span>
                                    Xác nhận
                                </button>
                            ` : ''}
                            ${!req.can_cancel && !req.can_respond && req.status === 'pending' ? `
                                <span class="text-xs text-slate-400 italic">Không phải của bạn</span>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `}).join('');
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

async function cancelRequest(requestId) {
    if (!confirm('Hủy yêu cầu chuyển giao này?')) return;
    
    const result = await API.post('api/transfers/cancel.php', { request_id: requestId });
    
    if (result.success) {
        Toast.success(result.message);
        loadRequests();
        checkNotifications();
    } else {
        Toast.error(result.message);
    }
}

async function confirmRequest(requestId) {
    if (!confirm('Xác nhận nhận thiết bị này?')) return;
    
    const result = await API.post('api/transfers/confirm.php', { request_id: requestId });
    
    if (result.success) {
        Toast.success(result.message);
        loadRequests();
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
        loadRequests();
        checkNotifications();
    } else {
        Toast.error(result.message);
    }
}

// Register callback to reload when transfer status changes
onTransferStatusChange.push(loadRequests);

// Initial load
loadRequests();
</script>

<?php require_once 'includes/footer.php'; ?>
