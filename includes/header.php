<?php
/**
 * Shared Header Component
 * Includes navigation bar with notification bell
 */

if (!defined('PAGE_TITLE')) {
    define('PAGE_TITLE', 'Device Manager');
}

$currentUser = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Redirect pending/inactive users to pending page (except on pending.php itself)
if ($currentUser && $currentUser['status'] !== 'approved' && $currentPage !== 'pending') {
    header('Location: pending.php');
    exit;
}

// Count pending members for badge (only for mod/admin)
$pendingMembersCount = 0;
if ($currentUser && $currentUser['status'] === 'approved' && in_array($currentUser['role'], ['mod', 'admin'])) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE status = 'pending'");
    $stmt->execute();
    $pendingMembersCount = $stmt->fetch()['count'];
}

// Helper function to get role badge HTML
function getRoleBadge($role) {
    $badges = [
        'admin' => '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Admin</span>',
        'mod' => '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Mod</span>',
        'warehouse' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400"><span class="material-symbols-outlined text-[12px]">warehouse</span>Kho</span>',
        'user' => ''
    ];
    return $badges[$role] ?? '';
}
?>
<!DOCTYPE html>
<html class="light" lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo PAGE_TITLE; ?> - DeviceManager</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark text-[#0d141b] dark:text-slate-100 font-display min-h-screen flex flex-col overflow-x-hidden">

<!-- Top Navigation -->
<header class="sticky top-0 z-50 flex items-center justify-between whitespace-nowrap border-b border-solid border-b-[#e7edf3] dark:border-b-slate-700 bg-white dark:bg-[#1a2632] px-4 md:px-10 py-3 shadow-sm">
    <div class="flex items-center gap-4 text-[#0d141b] dark:text-white">
        <!-- Mobile Menu Button -->
        <button id="mobile-menu-btn" class="md:hidden flex items-center justify-center size-10 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
            <span class="material-symbols-outlined text-2xl">menu</span>
        </button>
        <a href="devices.php" class="flex items-center gap-3">
            <div class="size-8 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-3xl">devices</span>
            </div>
            <h2 class="hidden sm:block text-[#0d141b] dark:text-white text-lg font-bold leading-tight tracking-[-0.015em]">DeviceManager</h2>
        </a>
    </div>
    <div class="flex flex-1 justify-end gap-4 md:gap-8">
        <nav class="hidden md:flex items-center gap-9">
            <a class="text-sm font-medium leading-normal transition-colors <?php echo $currentPage == 'devices' ? 'text-[#0d141b] dark:text-slate-200 font-bold border-b-2 border-primary' : 'text-[#0d141b] dark:text-slate-200 hover:text-primary'; ?>" href="devices.php">Thiết bị</a>
            <a class="text-sm font-medium leading-normal transition-colors <?php echo $currentPage == 'pending-transfers' ? 'text-[#0d141b] dark:text-slate-200 font-bold border-b-2 border-primary' : 'text-[#0d141b] dark:text-slate-200 hover:text-primary'; ?>" href="pending-transfers.php">Yêu cầu</a>
            <a class="text-sm font-medium leading-normal transition-colors flex items-center gap-1.5 <?php echo $currentPage == 'members' ? 'text-[#0d141b] dark:text-slate-200 font-bold border-b-2 border-primary' : 'text-[#0d141b] dark:text-slate-200 hover:text-primary'; ?>" href="members.php">
                Thành viên
                <?php if ($pendingMembersCount > 0): ?>
                <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold rounded-full bg-red-500 text-white"><?php echo $pendingMembersCount > 99 ? '99+' : $pendingMembersCount; ?></span>
                <?php endif; ?>
            </a>
        </nav>
        <div class="flex items-center gap-4">
            <!-- Notification Bell -->
            <div class="relative" id="notification-container">
                <button id="notification-btn" class="flex size-10 cursor-pointer items-center justify-center overflow-hidden rounded-full bg-primary/10 hover:bg-primary/20 transition-colors text-primary relative">
                    <span class="material-symbols-outlined">notifications</span>
                    <span id="notification-badge" class="hidden absolute top-2 right-2 flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                    </span>
                </button>
                <!-- Notification Dropdown -->
                <div id="notification-dropdown" class="hidden absolute right-0 top-full mt-3 w-[380px] z-50 origin-top-right rounded-xl bg-white dark:bg-[#1a2632] shadow-2xl ring-1 ring-black ring-opacity-5">
                    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-gray-900 dark:text-white font-bold text-base">Thông báo</h3>
                        <span id="notification-count" class="text-primary text-xs font-medium">0 yêu cầu mới</span>
                    </div>
                    <div id="notification-list" class="max-h-[400px] overflow-y-auto">
                        <p class="p-4 text-center text-gray-500 text-sm">Không có thông báo mới</p>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 dark:bg-[#23303e] border-t border-gray-100 dark:border-gray-700 text-center rounded-b-xl">
                        <a class="text-sm text-primary font-semibold hover:text-blue-700" href="pending-transfers.php">Xem tất cả yêu cầu</a>
                    </div>
                </div>
            </div>
            
            <!-- User Menu -->
            <div class="relative" id="user-menu-container">
                <button id="user-menu-btn" class="flex items-center gap-2 cursor-pointer">
                    <?php if (($currentUser['role'] ?? '') === 'warehouse'): ?>
                    <div class="rounded-full size-10 border border-slate-200 dark:border-slate-600 bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400">
                        <span class="material-symbols-outlined">warehouse</span>
                    </div>
                    <?php else: ?>
                    <div class="bg-center bg-no-repeat bg-cover rounded-full size-10 border border-slate-200 dark:border-slate-600 bg-primary/10 flex items-center justify-center text-primary font-bold">
                        <?php echo strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)); ?>
                    </div>
                    <?php endif; ?>
                    <span class="hidden sm:flex items-center gap-2">
                        <span class="text-sm font-medium"><?php echo htmlspecialchars($currentUser['name'] ?? 'User'); ?></span>
                        <?php echo getRoleBadge($currentUser['role'] ?? 'user'); ?>
                    </span>
                </button>
                <div id="user-menu-dropdown" class="hidden absolute right-0 top-full mt-2 w-48 bg-white dark:bg-[#1a2632] rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2">
                    <div class="px-4 py-2 border-b border-gray-100 dark:border-gray-700">
                        <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($currentUser['name'] ?? 'User'); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></p>
                    </div>
                    <button id="update-profile-btn" class="w-full text-left block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700">
                        <span class="material-symbols-outlined text-[16px] mr-2 align-middle">manage_accounts</span>
                        Cập nhật thông tin
                    </button>
                    <a href="api/auth/logout.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700">
                        <span class="material-symbols-outlined text-[16px] mr-2 align-middle">logout</span>
                        Đăng xuất
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Menu Dropdown -->
<div id="mobile-menu" class="md:hidden hidden fixed top-[57px] left-0 right-0 z-40 bg-white dark:bg-[#1a2632] border-b border-[#e7edf3] dark:border-slate-700 shadow-lg transform transition-all duration-200 ease-out">
    <nav class="flex flex-col py-2">
        <a class="flex items-center gap-3 px-6 py-3 text-base font-medium transition-colors <?php echo $currentPage == 'devices' ? 'text-primary bg-primary/5 border-l-4 border-primary' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 border-l-4 border-transparent'; ?>" href="devices.php">
            <span class="material-symbols-outlined text-xl">inventory_2</span>
            Thiết bị
        </a>
        <a class="flex items-center gap-3 px-6 py-3 text-base font-medium transition-colors <?php echo $currentPage == 'pending-transfers' ? 'text-primary bg-primary/5 border-l-4 border-primary' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 border-l-4 border-transparent'; ?>" href="pending-transfers.php">
            <span class="material-symbols-outlined text-xl">swap_horiz</span>
            Yêu cầu chuyển giao
        </a>
        <a class="flex items-center gap-3 px-6 py-3 text-base font-medium transition-colors <?php echo $currentPage == 'members' ? 'text-primary bg-primary/5 border-l-4 border-primary' : 'text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 border-l-4 border-transparent'; ?>" href="members.php">
            <span class="material-symbols-outlined text-xl">group</span>
            <span class="flex-1">Thành viên</span>
            <?php if ($pendingMembersCount > 0): ?>
            <span class="inline-flex items-center justify-center min-w-[20px] h-[20px] px-1.5 text-[10px] font-bold rounded-full bg-red-500 text-white"><?php echo $pendingMembersCount > 99 ? '99+' : $pendingMembersCount; ?></span>
            <?php endif; ?>
        </a>
        <div class="border-t border-slate-100 dark:border-slate-700 my-2"></div>
        <a class="flex items-center gap-3 px-6 py-3 text-base font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors border-l-4 border-transparent" href="api/auth/logout.php">
            <span class="material-symbols-outlined text-xl">logout</span>
            Đăng xuất
        </a>
    </nav>
</div>

<script src="js/app.js"></script>

<!-- Profile Update Modal -->
<div id="profile-modal" class="fixed inset-0 z-[100] hidden">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" id="profile-modal-backdrop"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-[#1a2632] rounded-xl shadow-2xl w-full max-w-md relative">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Cập nhật thông tin</h3>
                <button id="close-profile-modal" class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                    <span class="material-symbols-outlined text-gray-500">close</span>
                </button>
            </div>
            <form id="profile-form" class="p-6 space-y-4">
                <div id="profile-message" class="hidden p-3 rounded-lg text-sm"></div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($currentUser['email'] ?? ''); ?>" disabled 
                           class="form-input block w-full py-2.5 px-3 rounded-lg border-gray-200 dark:border-slate-600 bg-gray-100 dark:bg-slate-700 text-gray-500 dark:text-gray-400 cursor-not-allowed sm:text-sm"/>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Email không thể thay đổi</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tên hiển thị</label>
                    <input type="text" name="name" id="profile-name" required
                           value="<?php echo htmlspecialchars($currentUser['name'] ?? ''); ?>"
                           class="form-input block w-full py-2.5 px-3 rounded-lg border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm"/>
                </div>
                
                <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Đổi mật khẩu (tùy chọn)</p>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Mật khẩu hiện tại</label>
                            <div class="relative">
                                <input type="password" name="current_password" id="profile-current-password"
                                       class="form-input block w-full py-2.5 px-3 pr-10 rounded-lg border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm"
                                       placeholder="Nhập mật khẩu hiện tại"/>
                                <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </button>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm text-gray-600 dark:text-gray-400 mb-1">Mật khẩu mới</label>
                            <div class="relative">
                                <input type="password" name="new_password" id="profile-new-password" minlength="6"
                                       class="form-input block w-full py-2.5 px-3 pr-10 rounded-lg border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:border-primary focus:ring-primary sm:text-sm"
                                       placeholder="Tối thiểu 6 ký tự"/>
                                <button type="button" class="toggle-password absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button type="button" id="cancel-profile-btn" class="flex-1 py-2.5 px-4 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                        Hủy
                    </button>
                    <button type="submit" class="flex-1 py-2.5 px-4 rounded-lg bg-primary hover:bg-blue-600 text-white font-medium transition-colors">
                        Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isHidden = mobileMenu.classList.contains('hidden');
            
            if (isHidden) {
                mobileMenu.classList.remove('hidden');
                mobileMenuBtn.querySelector('.material-symbols-outlined').textContent = 'close';
            } else {
                mobileMenu.classList.add('hidden');
                mobileMenuBtn.querySelector('.material-symbols-outlined').textContent = 'menu';
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                mobileMenu.classList.add('hidden');
                mobileMenuBtn.querySelector('.material-symbols-outlined').textContent = 'menu';
            }
        });
        
        // Close menu on window resize to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                mobileMenu.classList.add('hidden');
                mobileMenuBtn.querySelector('.material-symbols-outlined').textContent = 'menu';
            }
        });
    }
    
    // Profile modal handling
    const profileModal = document.getElementById('profile-modal');
    const updateProfileBtn = document.getElementById('update-profile-btn');
    const closeProfileModal = document.getElementById('close-profile-modal');
    const cancelProfileBtn = document.getElementById('cancel-profile-btn');
    const profileModalBackdrop = document.getElementById('profile-modal-backdrop');
    const profileForm = document.getElementById('profile-form');
    const profileMessage = document.getElementById('profile-message');
    
    function openProfileModal() {
        profileModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        // Close user menu dropdown
        document.getElementById('user-menu-dropdown').classList.add('hidden');
    }
    
    function closeProfileModalFn() {
        profileModal.classList.add('hidden');
        document.body.style.overflow = '';
        profileMessage.classList.add('hidden');
        profileForm.reset();
        document.getElementById('profile-name').value = '<?php echo htmlspecialchars($currentUser['name'] ?? '', ENT_QUOTES); ?>';
    }
    
    if (updateProfileBtn) {
        updateProfileBtn.addEventListener('click', openProfileModal);
    }
    
    if (closeProfileModal) {
        closeProfileModal.addEventListener('click', closeProfileModalFn);
    }
    
    if (cancelProfileBtn) {
        cancelProfileBtn.addEventListener('click', closeProfileModalFn);
    }
    
    if (profileModalBackdrop) {
        profileModalBackdrop.addEventListener('click', closeProfileModalFn);
    }
    
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('.material-symbols-outlined');
            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        });
    });
    
    // Profile form submission
    if (profileForm) {
        profileForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const name = document.getElementById('profile-name').value.trim();
            const currentPassword = document.getElementById('profile-current-password').value;
            const newPassword = document.getElementById('profile-new-password').value;
            
            try {
                const res = await fetch('api/members/update-profile.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name: name,
                        current_password: currentPassword,
                        new_password: newPassword
                    })
                });
                const data = await res.json();
                
                if (data.success) {
                    profileMessage.textContent = data.message;
                    profileMessage.classList.remove('hidden', 'bg-red-50', 'border-red-200', 'text-red-700');
                    profileMessage.classList.add('bg-green-50', 'border', 'border-green-200', 'text-green-700');
                    
                    // Update displayed name
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    profileMessage.textContent = data.message;
                    profileMessage.classList.remove('hidden', 'bg-green-50', 'border-green-200', 'text-green-700');
                    profileMessage.classList.add('bg-red-50', 'border', 'border-red-200', 'text-red-700');
                }
            } catch (err) {
                profileMessage.textContent = 'Đã xảy ra lỗi kết nối';
                profileMessage.classList.remove('hidden', 'bg-green-50', 'border-green-200', 'text-green-700');
                profileMessage.classList.add('bg-red-50', 'border', 'border-red-200', 'text-red-700');
            }
        });
    }
});
</script>

