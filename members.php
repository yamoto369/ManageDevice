<?php
/**
 * Members Page
 */
require_once 'config/database.php';
requireAuth();

define('PAGE_TITLE', 'Quản lý thành viên');
require_once 'includes/header.php';

$currentUserId = getCurrentUserId();
?>

<main class="flex-1 overflow-y-auto p-4 md:p-8 lg:px-12">
    <div class="max-w-6xl mx-auto flex flex-col gap-6">
        <!-- Page Heading -->
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-col gap-2 max-w-2xl">
                <h1 class="text-slate-900 dark:text-white text-3xl md:text-4xl font-black tracking-tight">Quản lý Thành viên</h1>
                <p class="text-slate-500 dark:text-slate-400 text-base">Danh sách thành viên và thiết bị đang giữ. Bạn có thể đặt biệt danh (alias) riêng để dễ nhớ.</p>
            </div>
        </div>
        
        <!-- Search Bar -->
        <div class="w-full">
            <label class="relative flex w-full md:max-w-md items-center group">
                <span class="absolute left-4 text-slate-400 group-focus-within:text-primary transition-colors material-symbols-outlined">search</span>
                <input id="search-input" class="w-full h-12 pl-12 pr-4 rounded-xl bg-white dark:bg-[#15202b] border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all shadow-sm" placeholder="Tìm kiếm theo tên hoặc email..." type="text"/>
            </label>
        </div>
        
        <!-- Members Table -->
        <div class="bg-white dark:bg-[#15202b] rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-left border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-16">Avatar</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-1/4">Thông tin thành viên</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-1/5">Biệt danh (Alias)</th>
                            <th class="px-6 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-1/3">Thiết bị đang giữ</th>
                        </tr>
                    </thead>
                    <tbody id="members-table" class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                <span class="material-symbols-outlined text-4xl mb-2">hourglass_empty</span>
                                <p>Đang tải...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div id="pagination" class="flex items-center justify-between border-t border-slate-200 dark:border-slate-700 px-6 py-4 bg-slate-50 dark:bg-slate-800/50">
                <p id="pagination-info" class="text-xs text-slate-500 dark:text-slate-400">Hiển thị 0 thành viên</p>
                <div class="flex gap-2">
                    <button id="prev-btn" disabled class="p-1 rounded-md text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 disabled:opacity-50">
                        <span class="material-symbols-outlined">chevron_left</span>
                    </button>
                    <button id="next-btn" class="p-1 rounded-md text-slate-600 dark:text-slate-300 hover:text-primary hover:bg-slate-200 dark:hover:bg-slate-700">
                        <span class="material-symbols-outlined">chevron_right</span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Alias Edit Panel -->
        <div id="alias-panel" class="hidden mt-2 p-6 rounded-xl border border-primary/20 bg-blue-50/50 dark:bg-blue-900/10 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 left-0 w-1 h-full bg-primary"></div>
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="flex flex-col gap-2 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">edit_note</span>
                        <h3 id="alias-title" class="text-slate-900 dark:text-white text-base font-bold">Đặt biệt danh</h3>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 text-sm">Biệt danh này chỉ hiển thị với riêng bạn để giúp nhận diện dễ dàng hơn.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto items-end">
                    <div class="w-full md:w-64">
                        <input id="alias-input" class="w-full h-10 px-3 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary/50 text-sm" placeholder="Nhập biệt danh..." type="text"/>
                    </div>
                    <button onclick="saveAlias()" class="h-10 px-4 rounded-lg bg-primary text-white text-sm font-medium hover:bg-blue-600 transition-colors shadow-sm flex items-center justify-center gap-2 min-w-[120px]">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        Lưu thay đổi
                    </button>
                    <button onclick="closeAliasPanel()" class="h-10 px-3 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
const currentUserId = <?php echo $currentUserId; ?>;
let currentPage = 1;
let totalPages = 1;
let editingUserId = null;

async function loadMembers() {
    const search = document.getElementById('search-input').value;
    
    const params = new URLSearchParams({
        page: currentPage,
        limit: 20,
        ...(search && { search })
    });
    
    const result = await API.get(`api/members/list.php?${params}`);
    const tbody = document.getElementById('members-table');
    
    if (result.success && result.data.length > 0) {
        tbody.innerHTML = result.data.map(member => `
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                <td class="px-6 py-4 align-top">
                    <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold ring-2 ring-white dark:ring-slate-700">
                        ${member.name.charAt(0).toUpperCase()}
                    </div>
                </td>
                <td class="px-6 py-4 align-top">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white">${member.name}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">${member.email}</span>
                    </div>
                </td>
                <td class="px-6 py-4 align-top">
                    ${member.id != currentUserId ? (member.alias ? `
                        <button onclick="openAliasPanel(${member.id}, '${member.name}', '${member.alias}')" class="group/alias flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-primary text-sm font-medium transition-colors w-fit">
                            <span>${member.alias}</span>
                            <span class="material-symbols-outlined text-[16px] opacity-0 group-hover/alias:opacity-100 transition-opacity">edit</span>
                        </button>
                        <p class="text-[10px] text-slate-400 mt-1 italic">Chỉ bạn mới thấy</p>
                    ` : `
                        <button onclick="openAliasPanel(${member.id}, '${member.name}', '')" class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-dashed border-slate-300 dark:border-slate-600 hover:border-primary hover:text-primary text-slate-500 dark:text-slate-400 text-sm font-normal transition-colors w-fit group/btn">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            <span>Đặt biệt danh</span>
                        </button>
                    `) : `<span class="text-slate-400 text-sm italic">Tài khoản của bạn</span>`}
                </td>
                <td class="px-6 py-4 align-top">
                    <div class="flex flex-wrap gap-2">
                        ${member.devices.length > 0 ? member.devices.map(d => `
                            <span class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 dark:bg-slate-800 px-2.5 py-1 text-xs font-medium text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                <span class="material-symbols-outlined text-[14px]">${getDeviceIcon(d.name)}</span>
                                ${d.name}
                            </span>
                        `).join('') : `<span class="text-slate-400 text-sm italic">Không có thiết bị</span>`}
                    </div>
                </td>
            </tr>
        `).join('');
        
        // Update pagination
        totalPages = result.pagination.total_pages;
        const total = result.pagination.total;
        document.getElementById('pagination-info').textContent = `Hiển thị ${result.data.length} trong số ${total} thành viên`;
        document.getElementById('prev-btn').disabled = currentPage <= 1;
        document.getElementById('next-btn').disabled = currentPage >= totalPages;
    } else {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                    <span class="material-symbols-outlined text-4xl mb-2">group</span>
                    <p>Không tìm thấy thành viên</p>
                </td>
            </tr>
        `;
    }
}

function openAliasPanel(userId, userName, currentAlias) {
    editingUserId = userId;
    document.getElementById('alias-title').textContent = `Đặt biệt danh cho ${userName}`;
    document.getElementById('alias-input').value = currentAlias;
    document.getElementById('alias-panel').classList.remove('hidden');
    document.getElementById('alias-input').focus();
}

function closeAliasPanel() {
    editingUserId = null;
    document.getElementById('alias-panel').classList.add('hidden');
}

async function saveAlias() {
    if (!editingUserId) return;
    
    const alias = document.getElementById('alias-input').value.trim();
    
    const result = await API.post('api/members/set-alias.php', {
        target_user_id: editingUserId,
        alias: alias
    });
    
    if (result.success) {
        Toast.success(result.message);
        closeAliasPanel();
        loadMembers();
    } else {
        Toast.error(result.message);
    }
}

// Event listeners
document.getElementById('search-input').addEventListener('input', debounce(() => {
    currentPage = 1;
    loadMembers();
}, 300));

document.getElementById('prev-btn').addEventListener('click', () => {
    if (currentPage > 1) {
        currentPage--;
        loadMembers();
    }
});

document.getElementById('next-btn').addEventListener('click', () => {
    if (currentPage < totalPages) {
        currentPage++;
        loadMembers();
    }
});

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

loadMembers();
</script>

<?php require_once 'includes/footer.php'; ?>
