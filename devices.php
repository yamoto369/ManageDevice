<?php
/**
 * Device List Page (Home)
 */
require_once 'config/database.php';
requireAuth();

define('PAGE_TITLE', 'Danh sách thiết bị');
require_once 'includes/header.php';

$currentUserId = getCurrentUserId();
$canManage = canManageDevices();
$canEdit = canEditDevices();
?>

<!-- Main Content Area -->
<main class="flex-1 px-4 md:px-10 py-8 max-w-[1440px] mx-auto w-full">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-[#0d141b] dark:text-white text-3xl font-black leading-tight tracking-[-0.033em]">Danh sách thiết bị</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Quản lý toàn bộ thiết bị công nghệ của đội ngũ</p>
        </div>
        <?php if ($canManage): ?>
        <a href="device-form.php" class="flex shrink-0 cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-lg h-10 px-5 bg-primary hover:bg-blue-600 text-white text-sm font-bold leading-normal tracking-[0.015em] transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[20px]">add</span>
            <span class="truncate">Thêm thiết bị</span>
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Controls Bar: Search & Filters -->
    <div class="bg-white dark:bg-[#1a2632] p-4 rounded-xl border border-[#e7edf3] dark:border-slate-700 shadow-sm mb-6 flex flex-col lg:flex-row gap-4 justify-between items-start lg:items-center">
        <!-- Search -->
        <div class="relative w-full lg:w-96 group">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-symbols-outlined text-slate-400">search</span>
            </div>
            <input id="search-input" class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 dark:border-slate-600 rounded-lg leading-5 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm transition-all" placeholder="Tìm theo tên, IMEI hoặc người giữ..." type="text"/>
        </div>
        <!-- Filters -->
        <div class="flex flex-wrap gap-3 w-full lg:w-auto">
            <select id="status-filter" class="px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200">
                <option value="">Trạng thái: Tất cả</option>
                <option value="available">Sẵn sàng</option>
                <option value="broken">Hỏng</option>
            </select>
        </div>
    </div>
    
    <!-- Data Table -->
    <div class="bg-white dark:bg-[#1a2632] rounded-xl border border-[#e7edf3] dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800 border-b border-[#e7edf3] dark:border-slate-700">
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-[30%]">Tên thiết bị</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-[20%]">IMEI / SN</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-[15%]">Trạng thái</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-[25%]">Người đang giữ</th>
                        <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-[10%] text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody id="devices-table" class="divide-y divide-[#e7edf3] dark:divide-slate-700">
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                            <span class="material-symbols-outlined text-4xl mb-2">hourglass_empty</span>
                            <p>Đang tải...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div id="pagination" class="px-6 py-4 flex items-center justify-between border-t border-[#e7edf3] dark:border-slate-700 bg-slate-50/50 dark:bg-[#1a2632]">
            <p class="text-sm text-slate-500 dark:text-slate-400">
                <span id="pagination-info">Hiển thị 0 thiết bị</span>
            </p>
            <div class="flex gap-2">
                <button id="prev-btn" disabled class="px-3 py-1.5 rounded border border-slate-300 dark:border-slate-600 text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Trước
                </button>
                <button id="next-btn" class="px-3 py-1.5 rounded border border-slate-300 dark:border-slate-600 text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Sau
                </button>
            </div>
        </div>
    </div>
</main>

<script>
const currentUserId = <?php echo $currentUserId; ?>;
const canEdit = <?php echo $canEdit ? 'true' : 'false'; ?>;
let currentPage = 1;
let totalPages = 1;

async function loadDevices() {
    const search = document.getElementById('search-input').value;
    const status = document.getElementById('status-filter').value;
    
    const params = new URLSearchParams({
        page: currentPage,
        limit: 10,
        ...(search && { search }),
        ...(status && { status })
    });
    
    const result = await API.get(`api/devices/list.php?${params}`);
    const tbody = document.getElementById('devices-table');
    
    if (result.success && result.data.length > 0) {
        tbody.innerHTML = result.data.map(device => `
            <tr class="group hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                            <span class="material-symbols-outlined text-[20px]">${getDeviceIcon(device.name)}</span>
                        </div>
                        <div>
                            <a href="device-history.php?id=${device.id}" class="text-sm font-semibold text-[#0d141b] dark:text-white hover:text-primary cursor-pointer">${device.name}</a>
                            <p class="text-xs text-slate-500">${device.manufacturer || ''}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="font-mono text-sm text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded">${device.imei_sn}</span>
                </td>
                <td class="px-6 py-4">
                    ${getStatusBadge(device.status)}
                </td>
                <td class="px-6 py-4">
                    ${device.holder_id ? `
                        <div class="flex items-center gap-3">
                            <div class="size-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">
                                ${device.holder_name.charAt(0).toUpperCase()}
                            </div>
                            <span class="text-sm font-medium text-[#0d141b] dark:text-slate-200">
                                ${device.holder_alias || device.holder_name}
                                ${device.holder_alias ? `<span class="text-xs text-slate-400 ml-1">(${device.holder_name})</span>` : ''}
                            </span>
                        </div>
                    ` : `
                        <div class="flex items-center gap-2">
                            <div class="size-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-400">
                                <span class="material-symbols-outlined text-[16px]">person_off</span>
                            </div>
                            <span class="text-sm text-slate-400 italic">Chưa giao</span>
                        </div>
                    `}
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-1">
                        <a href="transfer.php?device_id=${device.id}" class="p-2 rounded-full text-slate-400 hover:text-green-500 hover:bg-green-50 dark:hover:bg-green-900/30 transition-all inline-flex" title="Chuyển giao">
                            <span class="material-symbols-outlined text-[20px]">swap_horiz</span>
                        </a>
                        ${canEdit ? `
                        <a href="device-form.php?id=${device.id}" class="p-2 rounded-full text-slate-400 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-700 transition-all inline-flex" title="Chỉnh sửa">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                        </a>
                        ` : ''}
                        <a href="device-history.php?id=${device.id}" class="p-2 rounded-full text-slate-400 hover:text-primary hover:bg-slate-100 dark:hover:bg-slate-700 transition-all inline-flex" title="Lịch sử">
                            <span class="material-symbols-outlined text-[20px]">history</span>
                        </a>
                    </div>
                </td>
            </tr>
        `).join('');
        
        // Update pagination
        totalPages = result.pagination.total_pages;
        const total = result.pagination.total;
        const start = (currentPage - 1) * 10 + 1;
        const end = Math.min(currentPage * 10, total);
        document.getElementById('pagination-info').textContent = `Hiển thị ${start} đến ${end} trong tổng số ${total} thiết bị`;
        document.getElementById('prev-btn').disabled = currentPage <= 1;
        document.getElementById('next-btn').disabled = currentPage >= totalPages;
    } else {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                    <span class="material-symbols-outlined text-4xl mb-2">inventory_2</span>
                    <p>Chưa có thiết bị nào</p>
                </td>
            </tr>
        `;
        document.getElementById('pagination-info').textContent = 'Hiển thị 0 thiết bị';
    }
}

// Event listeners
document.getElementById('search-input').addEventListener('input', debounce(() => {
    currentPage = 1;
    loadDevices();
}, 300));

document.getElementById('status-filter').addEventListener('change', () => {
    currentPage = 1;
    loadDevices();
});

document.getElementById('prev-btn').addEventListener('click', () => {
    if (currentPage > 1) {
        currentPage--;
        loadDevices();
    }
});

document.getElementById('next-btn').addEventListener('click', () => {
    if (currentPage < totalPages) {
        currentPage++;
        loadDevices();
    }
});

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Load on page ready
loadDevices();
</script>

<?php require_once 'includes/footer.php'; ?>
