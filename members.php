<?php
/**
 * Members Page - With Role Management
 */
require_once 'config/database.php';
requireAuth();

define('PAGE_TITLE', 'Quản lý thành viên');
require_once 'includes/header.php';

$currentUserId = getCurrentUserId();
$canApprove = canApproveMembers();
$canManageRoles = isAdmin();
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
                <table class="w-full min-w-[900px] text-left border-collapse">
                    <thead class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-4 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-12">Avatar</th>
                            <th class="px-4 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Thông tin</th>
                            <th class="px-4 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-24">Role</th>
                            <th class="px-4 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-28">Trạng thái</th>
                            <th class="px-4 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Biệt danh</th>
                            <th class="px-4 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Thiết bị</th>
                            <?php if ($canApprove || $canManageRoles): ?>
                            <th class="px-4 py-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-28 text-right">Hành động</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="members-table" class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-slate-500">
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

<!-- Reset Password Confirm Modal -->
<div id="reset-password-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white dark:bg-[#1a2632] rounded-xl shadow-2xl max-w-md w-full mx-4 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                <span class="material-symbols-outlined">key</span>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Reset Password</h3>
        </div>
        <p id="reset-password-modal-text" class="text-slate-600 dark:text-slate-400 mb-2">Bạn có chắc chắn muốn reset password của thành viên này?</p>
        <p class="text-sm text-slate-500 dark:text-slate-500 mb-6">Password sẽ được đặt lại về mặc định: <code class="bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded font-mono">123456</code></p>
        <input type="hidden" id="reset-password-modal-user-id">
        <div class="flex gap-3 justify-end">
            <button onclick="closeResetPasswordModal()" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-medium hover:bg-slate-100 dark:hover:bg-slate-700">Hủy</button>
            <button onclick="confirmResetPassword()" class="px-4 py-2 rounded-lg bg-amber-500 text-white font-medium hover:bg-amber-600">Reset Password</button>
        </div>
    </div>
</div>

<!-- Role Change Modal -->
<div id="role-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white dark:bg-[#1a2632] rounded-xl shadow-2xl max-w-md w-full mx-4 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 rounded-lg bg-primary/10 text-primary">
                <span class="material-symbols-outlined">admin_panel_settings</span>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Thay đổi Role</h3>
        </div>
        <p id="role-modal-text" class="text-slate-600 dark:text-slate-400 mb-4">Chọn role cho thành viên:</p>
        <input type="hidden" id="role-modal-user-id">
        <div class="flex flex-col gap-2 mb-6">
            <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 dark:border-slate-600 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800">
                <input type="radio" name="role-select" value="user" class="text-primary focus:ring-primary">
                <div>
                    <p class="font-medium text-slate-900 dark:text-white">User</p>
                    <p class="text-xs text-slate-500">Xem danh sách và đặt alias</p>
                </div>
            </label>
            <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 dark:border-slate-600 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800">
                <input type="radio" name="role-select" value="mod" class="text-primary focus:ring-primary">
                <div>
                    <p class="font-medium text-slate-900 dark:text-white">Moderator</p>
                    <p class="text-xs text-slate-500">Sửa thiết bị và phê duyệt thành viên</p>
                </div>
            </label>
            <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 dark:border-slate-600 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800">
                <input type="radio" name="role-select" value="warehouse" class="text-primary focus:ring-primary">
                <div>
                    <p class="font-medium text-slate-900 dark:text-white">Warehouse</p>
                    <p class="text-xs text-slate-500">Quản lý kho, nhận thiết bị hỏng</p>
                </div>
            </label>
            <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 dark:border-slate-600 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800">
                <input type="radio" name="role-select" value="admin" class="text-primary focus:ring-primary">
                <div>
                    <p class="font-medium text-slate-900 dark:text-white">Admin</p>
                    <p class="text-xs text-slate-500">Toàn quyền quản lý hệ thống</p>
                </div>
            </label>
        </div>
        <div class="flex gap-3 justify-end">
            <button onclick="closeRoleModal()" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-medium hover:bg-slate-100 dark:hover:bg-slate-700">Hủy</button>
            <button onclick="saveRole()" class="px-4 py-2 rounded-lg bg-primary text-white font-medium hover:bg-blue-600">Lưu thay đổi</button>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div id="delete-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white dark:bg-[#1a2632] rounded-xl shadow-2xl max-w-md w-full mx-4 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                <span class="material-symbols-outlined">warning</span>
            </div>
            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Xác nhận xóa</h3>
        </div>
        <p id="delete-modal-text" class="text-slate-600 dark:text-slate-400 mb-6">Bạn có chắc chắn muốn xóa thành viên này? Hành động này không thể hoàn tác.</p>
        <input type="hidden" id="delete-modal-user-id">
        <div class="flex gap-3 justify-end">
            <button onclick="closeDeleteModal()" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 font-medium hover:bg-slate-100 dark:hover:bg-slate-700">Hủy</button>
            <button onclick="confirmDelete()" class="px-4 py-2 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700">Xóa thành viên</button>
        </div>
</div>
</div>

<script>
const currentUserId = <?php echo $currentUserId; ?>;
const canApprove = <?php echo $canApprove ? 'true' : 'false'; ?>;
const canManageRoles = <?php echo $canManageRoles ? 'true' : 'false'; ?>;
let currentPage = 1;
let totalPages = 1;
let editingUserId = null;

function getRoleBadge(role) {
    const badges = {
        'admin': '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Admin</span>',
        'mod': '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Mod</span>',
        'warehouse': '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400"><span class="material-symbols-outlined text-[12px]">warehouse</span>Kho</span>',
        'user': '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">User</span>'
    };
    return badges[role] || badges['user'];
}

function getStatusBadge(status) {
    if (status === 'approved') {
        return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400"><span class="material-symbols-outlined text-[12px]">check_circle</span>Đã duyệt</span>';
    }
    return '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"><span class="material-symbols-outlined text-[12px]">schedule</span>Chờ duyệt</span>';
}

function getActionButtons(member) {
    if (member.id == currentUserId) return '';
    
    let buttons = [];
    
    // Approve button (for pending members, visible to mod/admin)
    if (canApprove && member.status === 'pending') {
        buttons.push(`
            <button onclick="approveMember(${member.id}, '${member.name}')" title="Phê duyệt" 
                class="p-1.5 rounded-lg bg-green-100 text-green-700 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400 dark:hover:bg-green-900/50">
                <span class="material-symbols-outlined text-[18px]">check</span>
            </button>
        `);
    }
    
    // Role change button (admin only)
    if (canManageRoles) {
        // Reset password button (admin only)
        buttons.push(`
            <button onclick="openResetPasswordModal(${member.id}, '${member.name}')" title="Reset Password"
                class="p-1.5 rounded-lg bg-amber-100 text-amber-600 hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:hover:bg-amber-900/50">
                <span class="material-symbols-outlined text-[18px]">key</span>
            </button>
        `);
        buttons.push(`
            <button onclick="openRoleModal(${member.id}, '${member.name}', '${member.role}')" title="Thay đổi role"
                class="p-1.5 rounded-lg bg-primary/10 text-primary hover:bg-primary/20">
                <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
            </button>
        `);
        
        // Delete button (admin only)
        buttons.push(`
            <button onclick="openDeleteModal(${member.id}, '${member.name}')" title="Xóa thành viên"
                class="p-1.5 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50">
                <span class="material-symbols-outlined text-[18px]">delete</span>
            </button>
        `);
    }
    
    return buttons.length > 0 ? `<div class="flex gap-1 justify-end">${buttons.join('')}</div>` : '';
}

async function loadMembers() {
    const search = document.getElementById('search-input').value;
    
    const params = new URLSearchParams({
        page: currentPage,
        limit: 20,
        ...(search && { search })
    });
    
    const result = await API.get(`api/members/list.php?${params}`);
    const tbody = document.getElementById('members-table');
    const colSpan = (canApprove || canManageRoles) ? 7 : 6;
    
    if (result.success && result.data.length > 0) {
        tbody.innerHTML = result.data.map(member => `
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
                <td class="px-4 py-4 align-top">
                    ${member.role === 'warehouse' ? `
                    <div class="size-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 font-bold ring-2 ring-white dark:ring-slate-700">
                        <span class="material-symbols-outlined text-xl">warehouse</span>
                    </div>
                    ` : `
                    <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold ring-2 ring-white dark:ring-slate-700">
                        ${member.name.charAt(0).toUpperCase()}
                    </div>
                    `}
                </td>
                <td class="px-4 py-4 align-top">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-slate-900 dark:text-white">${member.name}</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400">${member.email}</span>
                    </div>
                </td>
                <td class="px-4 py-4 align-top">
                    ${getRoleBadge(member.role)}
                </td>
                <td class="px-4 py-4 align-top">
                    ${getStatusBadge(member.status)}
                </td>
                <td class="px-4 py-4 align-top">
                    ${member.id != currentUserId ? (member.alias ? `
                        <button onclick="openAliasPanel(${member.id}, '${member.name}', '${member.alias}')" class="group/alias flex items-center gap-2 px-2 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 text-primary text-xs font-medium transition-colors w-fit">
                            <span>${member.alias}</span>
                            <span class="material-symbols-outlined text-[14px] opacity-0 group-hover/alias:opacity-100">edit</span>
                        </button>
                    ` : `
                        <button onclick="openAliasPanel(${member.id}, '${member.name}', '')" class="flex items-center gap-1 px-2 py-1 rounded-lg border border-dashed border-slate-300 dark:border-slate-600 hover:border-primary hover:text-primary text-slate-400 text-xs transition-colors w-fit">
                            <span class="material-symbols-outlined text-[14px]">add</span>
                            <span>Alias</span>
                        </button>
                    `) : `<span class="text-slate-400 text-xs italic">Bạn</span>`}
                </td>
                <td class="px-4 py-4 align-top">
                    <div class="flex flex-wrap gap-1">
                        ${member.devices.length > 0 ? member.devices.slice(0, 3).map(d => `
                            <span class="inline-flex items-center gap-1 rounded bg-slate-100 dark:bg-slate-800 px-2 py-0.5 text-[10px] font-medium text-slate-600 dark:text-slate-300">
                                <span class="material-symbols-outlined text-[12px]">${getDeviceIcon(d.name)}</span>
                                ${d.name.length > 15 ? d.name.substring(0, 15) + '...' : d.name}
                            </span>
                        `).join('') : `<span class="text-slate-400 text-xs italic">Không có</span>`}
                        ${member.devices.length > 3 ? `<span class="text-xs text-slate-400">+${member.devices.length - 3}</span>` : ''}
                    </div>
                </td>
                ${(canApprove || canManageRoles) ? `<td class="px-4 py-4 align-top">${getActionButtons(member)}</td>` : ''}
            </tr>
        `).join('');
        
        totalPages = result.pagination.total_pages;
        const total = result.pagination.total;
        document.getElementById('pagination-info').textContent = `Hiển thị ${result.data.length} trong số ${total} thành viên`;
        document.getElementById('prev-btn').disabled = currentPage <= 1;
        document.getElementById('next-btn').disabled = currentPage >= totalPages;
    } else {
        tbody.innerHTML = `
            <tr>
                <td colspan="${colSpan}" class="px-6 py-8 text-center text-slate-500">
                    <span class="material-symbols-outlined text-4xl mb-2">group</span>
                    <p>Không tìm thấy thành viên</p>
                </td>
            </tr>
        `;
    }
}

// Approve member
async function approveMember(userId, userName) {
    const result = await API.post('api/members/approve.php', { user_id: userId });
    if (result.success) {
        Toast.success(result.message);
        loadMembers();
    } else {
        Toast.error(result.message);
    }
}

// Role Modal
function openRoleModal(userId, userName, currentRole) {
    document.getElementById('role-modal-user-id').value = userId;
    document.getElementById('role-modal-text').textContent = `Chọn role cho ${userName}:`;
    document.querySelector(`input[name="role-select"][value="${currentRole}"]`).checked = true;
    document.getElementById('role-modal').classList.remove('hidden');
}

function closeRoleModal() {
    document.getElementById('role-modal').classList.add('hidden');
}

async function saveRole() {
    const userId = document.getElementById('role-modal-user-id').value;
    const role = document.querySelector('input[name="role-select"]:checked').value;
    
    const result = await API.post('api/members/update-role.php', { user_id: userId, role: role });
    if (result.success) {
        Toast.success(result.message);
        closeRoleModal();
        loadMembers();
    } else {
        Toast.error(result.message);
    }
}

// Delete Modal
function openDeleteModal(userId, userName) {
    document.getElementById('delete-modal-user-id').value = userId;
    document.getElementById('delete-modal-text').textContent = `Bạn có chắc chắn muốn xóa thành viên "${userName}"? Hành động này không thể hoàn tác.`;
    document.getElementById('delete-modal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('delete-modal').classList.add('hidden');
}

async function confirmDelete() {
    const userId = document.getElementById('delete-modal-user-id').value;
    
    const result = await API.post('api/members/delete.php', { user_id: userId });
    if (result.success) {
        Toast.success(result.message);
        closeDeleteModal();
        loadMembers();
    } else {
        Toast.error(result.message);
    }
}

// Reset Password Modal
function openResetPasswordModal(userId, userName) {
    document.getElementById('reset-password-modal-user-id').value = userId;
    document.getElementById('reset-password-modal-text').textContent = `Bạn có chắc chắn muốn reset password của "${userName}"?`;
    document.getElementById('reset-password-modal').classList.remove('hidden');
}

function closeResetPasswordModal() {
    document.getElementById('reset-password-modal').classList.add('hidden');
}

async function confirmResetPassword() {
    const userId = document.getElementById('reset-password-modal-user-id').value;
    
    const result = await API.post('api/members/reset-password.php', { user_id: userId });
    if (result.success) {
        Toast.success(result.message);
        closeResetPasswordModal();
    } else {
        Toast.error(result.message);
    }
}

// Alias functions
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

// Close modals on outside click
document.getElementById('role-modal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeRoleModal();
});
document.getElementById('delete-modal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeDeleteModal();
});
document.getElementById('reset-password-modal').addEventListener('click', (e) => {
    if (e.target === e.currentTarget) closeResetPasswordModal();
});

loadMembers();
</script>

<?php require_once 'includes/footer.php'; ?>
