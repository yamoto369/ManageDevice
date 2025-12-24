<?php
/**
 * Add Device Page
 * Admin only
 */
require_once 'config/database.php';
requireAuth();

// Only admin can add devices
if (!canManageDevices()) {
    header('Location: devices.php');
    exit;
}

define('PAGE_TITLE', 'Thêm thiết bị mới');
require_once 'includes/header.php';
?>

<main class="flex-1 px-4 md:px-40 py-8">
    <div class="max-w-[960px] mx-auto">
        <!-- Breadcrumbs -->
        <div class="flex flex-wrap gap-2 pb-4">
            <a class="text-slate-500 dark:text-slate-400 hover:text-primary transition-colors text-base font-medium leading-normal flex items-center gap-1" href="devices.php">
                <span class="material-symbols-outlined text-lg">inventory_2</span>
                Thiết bị
            </a>
            <span class="text-slate-400 dark:text-slate-600 text-base font-medium leading-normal">/</span>
            <span class="text-slate-900 dark:text-slate-100 text-base font-medium leading-normal">Thêm Thiết bị Mới</span>
        </div>
        
        <!-- Page Heading -->
        <div class="flex flex-wrap justify-between gap-3 pb-8">
            <div class="flex min-w-72 flex-col gap-3">
                <h1 class="text-slate-900 dark:text-white text-3xl md:text-4xl font-black leading-tight tracking-[-0.033em]">Thêm Thiết bị Mới</h1>
                <p class="text-slate-500 dark:text-slate-400 text-base font-normal leading-normal max-w-2xl">
                    Điền thông tin vào biểu mẫu dưới đây để đăng ký thiết bị mới vào hệ thống quản lý kho nội bộ.
                </p>
            </div>
        </div>
        
        <!-- Form Container -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 md:p-8">
            <form id="addDeviceForm" class="flex flex-col gap-6">
                <div id="form-error" class="hidden p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm"></div>
                
                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Device Name -->
                    <label class="flex flex-col flex-1 gap-2">
                        <span class="text-slate-900 dark:text-white text-base font-semibold leading-normal">Tên thiết bị <span class="text-red-500">*</span></span>
                        <input name="name" required class="form-input w-full rounded-lg text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-primary focus:ring-primary h-12 px-4 text-base placeholder:text-slate-400 transition-all" placeholder="Ví dụ: MacBook Pro M2 14-inch" type="text"/>
                    </label>
                    
                    <!-- Manufacturer -->
                    <label class="flex flex-col flex-1 gap-2">
                        <span class="text-slate-900 dark:text-white text-base font-semibold leading-normal">Hãng sản xuất</span>
                        <input name="manufacturer" class="form-input w-full rounded-lg text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-primary focus:ring-primary h-12 px-4 text-base placeholder:text-slate-400 transition-all" placeholder="Ví dụ: Apple, Dell, HP" type="text"/>
                    </label>
                </div>
                
                <!-- Technical Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- IMEI/Serial -->
                    <label class="flex flex-col flex-1 gap-2">
                        <span class="text-slate-900 dark:text-white text-base font-semibold leading-normal">IMEI / Serial Number <span class="text-red-500">*</span></span>
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-primary transition-colors">
                                <span class="material-symbols-outlined">qr_code_2</span>
                            </span>
                            <input name="imei_sn" required class="form-input w-full rounded-lg text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-primary focus:ring-primary h-12 pl-10 pr-4 text-base placeholder:text-slate-400 transition-all font-mono" placeholder="Nhập số IMEI hoặc SN" type="text"/>
                        </div>
                    </label>
                    
                    <!-- Status -->
                    <label class="flex flex-col flex-1 gap-2">
                        <span class="text-slate-900 dark:text-white text-base font-semibold leading-normal">Trạng thái ban đầu</span>
                        <div class="relative">
                            <select name="status" class="form-select w-full cursor-pointer rounded-lg text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-primary focus:ring-primary h-12 px-4 text-base transition-all appearance-none">
                                <option value="available">Sẵn sàng sử dụng</option>
                                <option value="maintenance">Đang bảo trì</option>
                                <option value="broken">Hỏng / Cần sửa chữa</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500 dark:text-slate-400">
                                <span class="material-symbols-outlined">expand_more</span>
                            </div>
                        </div>
                    </label>
                </div>
                
                <!-- Description -->
                <label class="flex flex-col w-full gap-2">
                    <span class="text-slate-900 dark:text-white text-base font-semibold leading-normal">Mô tả chi tiết</span>
                    <textarea name="description" class="form-textarea w-full resize-y rounded-lg text-slate-900 dark:text-white bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-600 focus:border-primary focus:ring-primary min-h-[120px] p-4 text-base placeholder:text-slate-400 transition-all" placeholder="Ghi chú về tình trạng vật lý, phụ kiện đi kèm hoặc các thông tin đặc biệt khác..."></textarea>
                </label>
                
                <!-- Action Buttons -->
                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-4 pt-4 mt-2 border-t border-slate-100 dark:border-slate-700">
                    <a href="devices.php" class="w-full sm:w-auto px-6 py-2.5 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors text-center">
                        Hủy bỏ
                    </a>
                    <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 px-8 py-2.5 rounded-lg bg-primary hover:bg-blue-600 text-white font-bold shadow-lg shadow-blue-500/30 transition-all transform active:scale-95">
                        <span class="material-symbols-outlined text-xl">save</span>
                        Lưu thiết bị
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
document.getElementById('addDeviceForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const errorEl = document.getElementById('form-error');
    
    const result = await API.post('api/devices/add.php', {
        name: formData.get('name'),
        imei_sn: formData.get('imei_sn'),
        manufacturer: formData.get('manufacturer'),
        status: formData.get('status'),
        description: formData.get('description')
    });
    
    if (result.success) {
        Toast.success('Thêm thiết bị thành công!');
        setTimeout(() => window.location.href = 'devices.php', 1000);
    } else {
        errorEl.textContent = result.message;
        errorEl.classList.remove('hidden');
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
