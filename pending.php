<?php
/**
 * Pending/Inactive Approval Page
 * Shown to users whose accounts are not yet approved or have been deactivated
 */
require_once 'config/database.php';

startSession();

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$user = getCurrentUser();

// If user is approved, redirect to devices
if ($user && $user['status'] === 'approved') {
    header('Location: devices.php');
    exit;
}

// Determine if user is pending (new) or inactive (deactivated)
$isInactive = $user && $user['status'] === 'inactive';
?>
<!DOCTYPE html>
<html class="light" lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?php echo $isInactive ? 'Tài khoản bị vô hiệu hóa' : 'Chờ phê duyệt'; ?> - DeviceManager</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <!-- Tailwind CSS (Static Build) -->
    <link rel="stylesheet" href="css/tailwind.css">
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-[#0d141b] dark:text-slate-100 min-h-screen flex flex-col">

<!-- Header -->
<header class="w-full border-b border-[#e7edf3] dark:border-slate-800 bg-white dark:bg-[#1a2634]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center size-8 rounded-lg bg-primary/10 text-primary">
                    <span class="material-symbols-outlined text-2xl">devices</span>
                </div>
                <h1 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">Device Manager</h1>
            </div>
            <a href="api/auth/logout.php" class="flex items-center gap-2 px-4 py-2 rounded-lg text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-700 transition-colors">
                <span class="material-symbols-outlined text-[18px]">logout</span>
                <span class="text-sm font-medium">Đăng xuất</span>
            </a>
        </div>
    </div>
</header>

<!-- Main Content -->
<main class="flex-grow flex items-center justify-center p-4 sm:p-6 lg:p-8">
    <div class="w-full max-w-md text-center">
        <div class="bg-white dark:bg-[#1a2634] rounded-2xl shadow-lg border border-[#e7edf3] dark:border-slate-800 p-8">
            <!-- Icon -->
            <?php if ($isInactive): ?>
            <div class="mx-auto w-20 h-20 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-6">
                <span class="material-symbols-outlined text-4xl text-red-600 dark:text-red-400">block</span>
            </div>
            <?php else: ?>
            <div class="mx-auto w-20 h-20 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mb-6">
                <span class="material-symbols-outlined text-4xl text-amber-600 dark:text-amber-400">hourglass_top</span>
            </div>
            <?php endif; ?>
            
            <!-- Title -->
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-3">
                <?php echo $isInactive ? 'Tài khoản bị vô hiệu hóa' : 'Chờ phê duyệt'; ?>
            </h2>
            
            <!-- Description -->
            <p class="text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">
                <?php if ($isInactive): ?>
                    Tài khoản của bạn đã bị vô hiệu hóa bởi quản trị viên. Vui lòng liên hệ admin để được kích hoạt lại.
                <?php else: ?>
                    Tài khoản của bạn đã được tạo thành công. Vui lòng chờ quản trị viên phê duyệt để có thể sử dụng hệ thống.
                <?php endif; ?>
            </p>
            
            <!-- User Info -->
            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-3 justify-center">
                    <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                        <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></p>
                        <p class="text-xs text-slate-500"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Status Badge -->
            <?php if ($isInactive): ?>
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-sm font-medium">
                <span class="material-symbols-outlined text-[16px]">block</span>
                Đã bị vô hiệu hóa
            </div>
            <?php else: ?>
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 text-sm font-medium">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                </span>
                Đang chờ xử lý
            </div>
            <?php endif; ?>
            
            <!-- Refresh hint -->
            <p class="mt-6 text-xs text-slate-400">
                <?php if ($isInactive): ?>
                    Trang sẽ tự động làm mới khi tài khoản được kích hoạt lại
                <?php else: ?>
                    Trang sẽ tự động làm mới khi tài khoản được phê duyệt
                <?php endif; ?>
            </p>
        </div>
    </div>
</main>

<script>
// Auto refresh every 30 seconds to check status
setInterval(() => {
    fetch('api/auth/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ check_status: true })
    });
    window.location.reload();
}, 30000);
</script>
</body>
</html>

