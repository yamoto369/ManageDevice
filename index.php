<?php
/**
 * Login / Register Page
 */
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: devices.php');
    exit;
}
?>
<!DOCTYPE html>
<html class="light" lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Device Manager - Đăng nhập</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
                },
            },
        }
    </script>
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
        </div>
    </div>
</header>

<!-- Main Content -->
<main class="flex-grow flex items-center justify-center p-4 sm:p-6 lg:p-8">
    <div class="w-full max-w-5xl bg-white dark:bg-[#1a2634] rounded-xl shadow-lg border border-[#e7edf3] dark:border-slate-800 overflow-hidden flex flex-col md:flex-row min-h-[600px]">
        
        <!-- Left Column: Branding -->
        <div class="hidden md:flex md:w-1/2 bg-gradient-to-br from-primary to-blue-600 relative overflow-hidden">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="relative z-10 flex flex-col justify-end p-12 h-full text-white">
                <div class="mb-6">
                    <span class="inline-flex items-center justify-center p-3 bg-white/20 backdrop-blur-sm rounded-lg mb-6">
                        <span class="material-symbols-outlined text-3xl">inventory_2</span>
                    </span>
                    <h2 class="text-3xl font-bold mb-4 leading-tight">Quản lý tài sản hiệu quả</h2>
                    <p class="text-slate-100 text-lg opacity-90">Hệ thống tập trung giúp theo dõi, cấp phát và thu hồi thiết bị cho đội ngũ của bạn một cách dễ dàng.</p>
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-300">
                    <span class="material-symbols-outlined text-base">security</span>
                    <span>Bảo mật nội bộ © 2024</span>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Form -->
        <div class="w-full md:w-1/2 p-8 lg:p-12 flex flex-col justify-center">
            <!-- Tabs -->
            <div class="flex border-b border-[#e7edf3] dark:border-slate-700 mb-8">
                <button id="tab-login" class="flex-1 pb-4 text-center border-b-2 border-primary text-primary font-semibold text-sm transition-colors" onclick="showTab('login')">
                    Đăng nhập
                </button>
                <button id="tab-register" class="flex-1 pb-4 text-center border-b-2 border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 font-semibold text-sm transition-colors" onclick="showTab('register')">
                    Đăng ký
                </button>
            </div>
            
            <!-- Login Form -->
            <div id="form-login">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Chào mừng trở lại</h2>
                    <p class="text-slate-500 dark:text-slate-400">Vui lòng nhập thông tin đăng nhập của bạn.</p>
                </div>
                
                <form id="loginForm" class="flex flex-col gap-5">
                    <div id="login-error" class="hidden p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm"></div>
                    
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">Email công việc</span>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined text-[20px]">mail</span>
                            </div>
                            <input name="email" type="email" required class="form-input block w-full pl-10 pr-3 py-3 rounded-lg border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary focus:ring-primary sm:text-sm" placeholder="user@team.com"/>
                        </div>
                    </label>
                    
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">Mật khẩu</span>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined text-[20px]">lock</span>
                            </div>
                            <input name="password" type="password" required class="form-input block w-full pl-10 pr-3 py-3 rounded-lg border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary focus:ring-primary sm:text-sm" placeholder="••••••••"/>
                        </div>
                    </label>
                    
                    <button type="submit" class="mt-2 w-full flex justify-center items-center gap-2 bg-primary hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        <span>Đăng nhập</span>
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </button>
                </form>
            </div>
            
            <!-- Register Form -->
            <div id="form-register" class="hidden">
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-white mb-2">Tạo tài khoản mới</h2>
                    <p class="text-slate-500 dark:text-slate-400">Điền thông tin để đăng ký tài khoản.</p>
                </div>
                
                <form id="registerForm" class="flex flex-col gap-5">
                    <div id="register-error" class="hidden p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm"></div>
                    
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">Họ và tên</span>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined text-[20px]">person</span>
                            </div>
                            <input name="name" type="text" required class="form-input block w-full pl-10 pr-3 py-3 rounded-lg border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary focus:ring-primary sm:text-sm" placeholder="Nguyễn Văn A"/>
                        </div>
                    </label>
                    
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">Email công việc</span>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined text-[20px]">mail</span>
                            </div>
                            <input name="email" type="email" required class="form-input block w-full pl-10 pr-3 py-3 rounded-lg border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary focus:ring-primary sm:text-sm" placeholder="user@team.com"/>
                        </div>
                    </label>
                    
                    <label class="block">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5 block">Mật khẩu</span>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <span class="material-symbols-outlined text-[20px]">lock</span>
                            </div>
                            <input name="password" type="password" required minlength="6" class="form-input block w-full pl-10 pr-3 py-3 rounded-lg border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-white placeholder-slate-400 focus:border-primary focus:ring-primary sm:text-sm" placeholder="Tối thiểu 6 ký tự"/>
                        </div>
                    </label>
                    
                    <button type="submit" class="mt-2 w-full flex justify-center items-center gap-2 bg-primary hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        <span>Đăng ký</span>
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
function showTab(tab) {
    const loginTab = document.getElementById('tab-login');
    const registerTab = document.getElementById('tab-register');
    const loginForm = document.getElementById('form-login');
    const registerForm = document.getElementById('form-register');
    
    if (tab === 'login') {
        loginTab.classList.add('border-primary', 'text-primary');
        loginTab.classList.remove('border-transparent', 'text-slate-500');
        registerTab.classList.remove('border-primary', 'text-primary');
        registerTab.classList.add('border-transparent', 'text-slate-500');
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
    } else {
        registerTab.classList.add('border-primary', 'text-primary');
        registerTab.classList.remove('border-transparent', 'text-slate-500');
        loginTab.classList.remove('border-primary', 'text-primary');
        loginTab.classList.add('border-transparent', 'text-slate-500');
        registerForm.classList.remove('hidden');
        loginForm.classList.add('hidden');
    }
}

// Login form
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const errorEl = document.getElementById('login-error');
    
    try {
        const res = await fetch('api/auth/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: formData.get('email'),
                password: formData.get('password')
            })
        });
        const data = await res.json();
        
        if (data.success) {
            // Check if user is pending
            if (data.user && data.user.status === 'pending') {
                window.location.href = 'pending.php';
            } else {
                window.location.href = 'devices.php';
            }
        } else {
            errorEl.textContent = data.message;
            errorEl.classList.remove('hidden');
        }
    } catch (err) {
        errorEl.textContent = 'Đã xảy ra lỗi kết nối';
        errorEl.classList.remove('hidden');
    }
});

// Register form
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const errorEl = document.getElementById('register-error');
    
    try {
        const res = await fetch('api/auth/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: formData.get('name'),
                email: formData.get('email'),
                password: formData.get('password')
            })
        });
        const data = await res.json();
        
        if (data.success) {
            // Check if user is pending (needs approval)
            if (data.pending) {
                // Show success message in green instead of redirecting
                errorEl.textContent = data.message;
                errorEl.classList.remove('hidden', 'bg-red-50', 'border-red-200', 'text-red-700');
                errorEl.classList.add('bg-green-50', 'border-green-200', 'text-green-700');
            } else {
                window.location.href = 'devices.php';
            }
        } else {
            errorEl.textContent = data.message;
            errorEl.classList.remove('hidden');
        }
    } catch (err) {
        errorEl.textContent = 'Đã xảy ra lỗi kết nối';
        errorEl.classList.remove('hidden');
    }
});
</script>
</body>
</html>
