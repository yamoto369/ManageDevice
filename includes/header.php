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

// Redirect pending users to pending page (except on pending.php itself)
if ($currentUser && $currentUser['status'] === 'pending' && $currentPage !== 'pending') {
    header('Location: pending.php');
    exit;
}

// Helper function to get role badge HTML
function getRoleBadge($role) {
    $badges = [
        'admin' => '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Admin</span>',
        'mod' => '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Mod</span>',
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
            <a class="text-sm font-medium leading-normal transition-colors <?php echo $currentPage == 'members' ? 'text-[#0d141b] dark:text-slate-200 font-bold border-b-2 border-primary' : 'text-[#0d141b] dark:text-slate-200 hover:text-primary'; ?>" href="members.php">Thành viên</a>
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
                    <div class="bg-center bg-no-repeat bg-cover rounded-full size-10 border border-slate-200 dark:border-slate-600 bg-primary/10 flex items-center justify-center text-primary font-bold">
                        <?php echo strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)); ?>
                    </div>
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
            Thành viên
        </a>
        <div class="border-t border-slate-100 dark:border-slate-700 my-2"></div>
        <a class="flex items-center gap-3 px-6 py-3 text-base font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors border-l-4 border-transparent" href="api/auth/logout.php">
            <span class="material-symbols-outlined text-xl">logout</span>
            Đăng xuất
        </a>
    </nav>
</div>

<script src="js/app.js"></script>
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
});
</script>
