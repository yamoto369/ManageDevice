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
<header class="sticky top-0 z-50 flex items-center justify-between whitespace-nowrap border-b border-solid border-b-[#e7edf3] dark:border-b-slate-700 bg-white dark:bg-[#1a2632] px-10 py-3 shadow-sm">
    <div class="flex items-center gap-4 text-[#0d141b] dark:text-white">
        <a href="devices.php" class="flex items-center gap-3">
            <div class="size-8 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-3xl">devices</span>
            </div>
            <h2 class="text-[#0d141b] dark:text-white text-lg font-bold leading-tight tracking-[-0.015em]">DeviceManager</h2>
        </a>
    </div>
    <div class="flex flex-1 justify-end gap-8">
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
                    <span class="hidden sm:block text-sm font-medium"><?php echo htmlspecialchars($currentUser['name'] ?? 'User'); ?></span>
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

<script src="js/app.js"></script>
