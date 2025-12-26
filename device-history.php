<?php
/**
 * Device History Page
 */
require_once 'config/database.php';
requireAuth();

$deviceId = intval($_GET['id'] ?? 0);
if ($deviceId <= 0) {
    header('Location: devices.php');
    exit;
}

define('PAGE_TITLE', 'Lịch sử thiết bị');
require_once 'includes/header.php';

$currentUserId = getCurrentUserId();
?>

<main class="flex-1 overflow-y-auto p-4 md:p-8">
    <div class="max-w-[1200px] mx-auto flex flex-col gap-6">
        <!-- Breadcrumbs -->
        <nav class="flex flex-wrap gap-2 text-sm font-medium">
            <a class="text-slate-500 dark:text-slate-400 hover:text-primary transition-colors" href="devices.php">Danh sách thiết bị</a>
            <span class="text-slate-400 dark:text-slate-600">/</span>
            <span id="breadcrumb-device" class="text-slate-900 dark:text-white font-semibold">Đang tải...</span>
        </nav>
        
        <!-- Device Summary Header -->
        <div id="device-header" class="bg-white dark:bg-[#1a2632] rounded-xl border border-[#e7edf3] dark:border-slate-700 p-6 shadow-sm">
            <div class="animate-pulse flex gap-6">
                <div class="h-24 w-32 bg-slate-200 dark:bg-slate-700 rounded-lg"></div>
                <div class="flex-1 space-y-3">
                    <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded w-1/3"></div>
                    <div class="h-4 bg-slate-200 dark:bg-slate-700 rounded w-1/4"></div>
                    <div class="h-6 bg-slate-200 dark:bg-slate-700 rounded w-20"></div>
                </div>
            </div>
        </div>
        
        <!-- History Timeline -->
        <div class="bg-white dark:bg-[#1a2632] rounded-xl border border-[#e7edf3] dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-[#e7edf3] dark:border-slate-700">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Lịch sử chuyển giao</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-medium">
                        <tr>
                            <th class="px-6 py-4" scope="col">Thời gian</th>
                            <th class="px-6 py-4" scope="col">Chuyển từ</th>
                            <th class="px-6 py-4 w-12 text-center" scope="col"></th>
                            <th class="px-6 py-4" scope="col">Chuyển đến</th>
                            <th class="px-6 py-4" scope="col">Loại</th>
                            <th class="px-6 py-4 min-w-[200px]" scope="col">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody id="history-table" class="divide-y divide-[#e7edf3] dark:divide-slate-700 text-slate-900 dark:text-slate-200">
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                <span class="material-symbols-outlined text-4xl mb-2">hourglass_empty</span>
                                <p>Đang tải...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
const deviceId = <?php echo $deviceId; ?>;
const currentUserId = <?php echo $currentUserId; ?>;

async function loadDeviceHistory() {
    const result = await API.get(`api/devices/history.php?device_id=${deviceId}`);
    
    if (result.success) {
        const device = result.device;
        const history = result.history;
        
        // Update breadcrumb
        document.getElementById('breadcrumb-device').textContent = device.name;
        
        // Update header
        document.getElementById('device-header').innerHTML = `
            <div class="flex flex-col md:flex-row gap-6 items-start md:items-center justify-between">
                <div class="flex gap-5 items-center">
                    <div class="relative bg-slate-100 dark:bg-slate-700 rounded-lg h-24 w-32 flex items-center justify-center overflow-hidden">
                        ${device.image ? 
                            `<img src="${device.image}" alt="${device.name}" class="h-full w-full object-cover">` :
                            `<span class="material-symbols-outlined text-4xl text-slate-400">${getDeviceIcon(device.name)}</span>`
                        }
                    </div>
                    <div class="flex flex-col gap-1">
                        <h2 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">${device.name}</h2>
                        <div class="flex flex-wrap gap-3 text-sm text-slate-500 dark:text-slate-400">
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">fingerprint</span>
                                S/N: ${device.imei_sn}
                            </span>
                            ${device.manufacturer ? `<span>| ${device.manufacturer}</span>` : ''}
                        </div>
                        <div class="mt-2">
                            ${getStatusBadge(device.status)}
                        </div>
                    </div>
                </div>
                <div class="flex w-full md:w-auto gap-3 flex-wrap">
                    <a href="transfer.php?device_id=${device.id}" class="flex-1 md:flex-none flex items-center justify-center gap-2 rounded-lg bg-primary hover:bg-blue-600 text-white px-4 py-2.5 text-sm font-bold transition-all shadow-sm shadow-blue-500/20">
                        <span class="material-symbols-outlined text-[20px]">swap_horiz</span>
                        <span>Chuyển giao</span>
                    </a>
                </div>
            </div>
            ${device.holder_id ? `
                <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-sm text-slate-500">Người đang giữ: 
                        <span class="font-medium text-slate-900 dark:text-white">${device.holder_alias || device.holder_name}</span>
                        ${device.holder_alias ? `<span class="text-slate-400">(${device.holder_name})</span>` : ''}
                    </p>
                </div>
            ` : ''}
        `;
        
        // Update history table
        const tbody = document.getElementById('history-table');
        if (history.length > 0) {
            tbody.innerHTML = history.map(record => {
                const actionLabels = {
                    'assign': { text: 'Cấp phát', class: 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' },
                    'transfer': { text: 'Chuyển giao', class: 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' },
                    'return': { text: 'Thu hồi', class: 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300' },
                    'borrow': { text: 'Mượn', class: 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300' }
                };
                const action = actionLabels[record.action_type] || actionLabels['transfer'];
                
                return `
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-semibold text-slate-900 dark:text-white">${formatDate(record.created_at).split(' ')[1]}</span>
                                <span class="text-xs text-slate-500">${formatDate(record.created_at).split(' ')[0]}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            ${record.from_user_id ? `
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                                        ${record.from_user_name.charAt(0).toUpperCase()}
                                    </div>
                                    <span class="font-medium">${record.from_user_alias || record.from_user_name}</span>
                                </div>
                            ` : `
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[18px] text-slate-500">inventory_2</span>
                                    </div>
                                    <span class="font-medium">Kho</span>
                                </div>
                            `}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="material-symbols-outlined text-slate-400">arrow_right_alt</span>
                        </td>
                        <td class="px-6 py-4">
                            ${record.to_user_id ? `
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-xs">
                                        ${record.to_user_name.charAt(0).toUpperCase()}
                                    </div>
                                    <span class="font-medium">${record.to_user_alias || record.to_user_name}</span>
                                </div>
                            ` : `
                                <div class="flex items-center gap-3">
                                    <div class="size-8 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[18px] text-slate-500">inventory_2</span>
                                    </div>
                                    <span class="font-medium">Kho</span>
                                </div>
                            `}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ${action.class} ring-1 ring-inset ring-current/10">${action.text}</span>
                        </td>
                        <td class="px-6 py-4 text-slate-500 dark:text-slate-400 truncate max-w-xs">${record.note || '-'}</td>
                    </tr>
                `;
            }).join('');
        } else {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                        <span class="material-symbols-outlined text-4xl mb-2">history</span>
                        <p>Chưa có lịch sử chuyển giao</p>
                    </td>
                </tr>
            `;
        }
    } else {
        Toast.error(result.message || 'Không thể tải thông tin thiết bị');
    }
}

// Register callback to reload when transfer status changes
onTransferStatusChange.push(loadDeviceHistory);

loadDeviceHistory();
</script>

<?php require_once 'includes/footer.php'; ?>
