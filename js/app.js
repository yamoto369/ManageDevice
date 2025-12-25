/**
 * Device Manager - JavaScript Utilities
 */

// API helper
const API = {
    async request(url, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            const result = await response.json();
            return result;
        } catch (error) {
            console.error('API Error:', error);
            return { success: false, message: 'Đã xảy ra lỗi kết nối' };
        }
    },

    get(url) {
        return this.request(url, 'GET');
    },

    post(url, data) {
        return this.request(url, 'POST', data);
    }
};

// Toast notifications
const Toast = {
    show(message, type = 'info') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            info: 'bg-blue-500',
            warning: 'bg-yellow-500'
        };

        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-[100] px-6 py-3 rounded-lg text-white shadow-lg ${colors[type]} animate-fade-in`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('opacity-0', 'transition-opacity');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },

    success(message) { this.show(message, 'success'); },
    error(message) { this.show(message, 'error'); },
    info(message) { this.show(message, 'info'); },
    warning(message) { this.show(message, 'warning'); }
};

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Format relative time
function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);

    if (diff < 60) return 'Vừa xong';
    if (diff < 3600) return `${Math.floor(diff / 60)} phút trước`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} giờ trước`;
    if (diff < 604800) return `${Math.floor(diff / 86400)} ngày trước`;
    return formatDate(dateString);
}

// Status labels
const STATUS_LABELS = {
    'available': { text: 'Sẵn sàng', class: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 border-green-200 dark:border-green-800' },
    'in_use': { text: 'Đang mượn', class: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 border-blue-200 dark:border-blue-800' },
    'broken': { text: 'Hỏng', class: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border-red-200 dark:border-red-800' },
    'maintenance': { text: 'Bảo trì', class: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 border-yellow-200 dark:border-yellow-800' }
};

function getStatusBadge(status) {
    const config = STATUS_LABELS[status] || STATUS_LABELS['available'];
    return `<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold ${config.class} border">
        <span class="size-1.5 rounded-full ${status === 'available' ? 'bg-green-500' : status === 'in_use' ? 'bg-blue-500' : status === 'broken' ? 'bg-red-500' : 'bg-yellow-500'}"></span>
        ${config.text}
    </span>`;
}

// Device icon based on name
function getDeviceIcon(name) {
    const nameLower = name.toLowerCase();
    if (nameLower.includes('iphone') || nameLower.includes('phone') || nameLower.includes('samsung') || nameLower.includes('pixel')) {
        return 'smartphone';
    }
    if (nameLower.includes('ipad') || nameLower.includes('tablet')) {
        return 'tablet_mac';
    }
    if (nameLower.includes('macbook') || nameLower.includes('imac')) {
        return 'laptop_mac';
    }
    if (nameLower.includes('laptop') || nameLower.includes('dell') || nameLower.includes('thinkpad') || nameLower.includes('xps')) {
        return 'laptop_windows';
    }
    return 'devices';
}

// Notification polling
let notificationInterval = null;

async function checkNotifications() {
    try {
        // Use notifications.php which only returns requests for current user to action
        const result = await API.get('api/transfers/notifications.php');
        if (result.success) {
            const count = result.count || 0;
            const badge = document.getElementById('notification-badge');
            const countSpan = document.getElementById('notification-count');
            const list = document.getElementById('notification-list');

            if (badge) {
                badge.classList.toggle('hidden', count === 0);
            }

            if (countSpan) {
                countSpan.textContent = count > 0 ? `${count} yêu cầu mới` : 'Không có yêu cầu mới';
            }

            if (list && result.data && result.data.length > 0) {
                list.innerHTML = result.data.slice(0, 5).map(req => `
                    <div class="flex gap-3 p-4 hover:bg-gray-50 dark:hover:bg-[#23303e] border-b border-gray-100 dark:border-gray-700 relative" id="notif-item-${req.id}">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-primary rounded-l"></div>
                        <div class="flex-shrink-0">
                            <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                ${req.from_user_name.charAt(0).toUpperCase()}
                            </div>
                        </div>
                        <div class="flex-1 flex flex-col gap-2">
                            <div>
                                <p class="text-sm text-gray-900 dark:text-white leading-snug">
                                    <span class="font-bold">${req.from_user_alias || req.from_user_name}</span> 
                                    ${req.type === 'transfer' ? 'chuyển giao' : 'yêu cầu mượn'} 
                                    <span class="text-primary font-medium">${req.device_name}</span>
                                </p>
                                <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px]">schedule</span>
                                    ${timeAgo(req.created_at)}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button onclick="confirmFromPopup(${req.id}, event)" class="flex-1 bg-primary hover:bg-blue-600 text-white text-xs font-semibold py-1.5 px-3 rounded transition-colors flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">check</span>
                                    Xác nhận
                                </button>
                                <button onclick="rejectFromPopup(${req.id}, event)" class="flex-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs font-semibold py-1.5 px-3 rounded transition-colors flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">close</span>
                                    Từ chối
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            } else if (list) {
                list.innerHTML = '<p class="p-4 text-center text-gray-500 text-sm">Không có thông báo mới</p>';
            }
        }
    } catch (error) {
        console.error('Error checking notifications:', error);
    }
}

// Confirm transfer request from notification popup
async function confirmFromPopup(requestId, event) {
    event.stopPropagation();

    const result = await API.post('api/transfers/confirm.php', { request_id: requestId });

    if (result.success) {
        Toast.success(result.message);
        // Remove the notification item with animation
        const item = document.getElementById(`notif-item-${requestId}`);
        if (item) {
            item.style.transition = 'all 0.3s ease';
            item.style.opacity = '0';
            item.style.transform = 'translateX(20px)';
            setTimeout(() => {
                checkNotifications();
            }, 300);
        } else {
            checkNotifications();
        }
    } else {
        Toast.error(result.message);
    }
}

// Reject transfer request from notification popup
async function rejectFromPopup(requestId, event) {
    event.stopPropagation();

    const result = await API.post('api/transfers/reject.php', { request_id: requestId });

    if (result.success) {
        Toast.success(result.message);
        // Remove the notification item with animation
        const item = document.getElementById(`notif-item-${requestId}`);
        if (item) {
            item.style.transition = 'all 0.3s ease';
            item.style.opacity = '0';
            item.style.transform = 'translateX(20px)';
            setTimeout(() => {
                checkNotifications();
            }, 300);
        } else {
            checkNotifications();
        }
    } else {
        Toast.error(result.message);
    }
}

// Initialize dropdowns
document.addEventListener('DOMContentLoaded', function () {
    // Notification dropdown
    const notifBtn = document.getElementById('notification-btn');
    const notifDropdown = document.getElementById('notification-dropdown');

    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown.classList.toggle('hidden');
            // Close user menu
            document.getElementById('user-menu-dropdown')?.classList.add('hidden');
        });
    }

    // User menu dropdown
    const userBtn = document.getElementById('user-menu-btn');
    const userDropdown = document.getElementById('user-menu-dropdown');

    if (userBtn && userDropdown) {
        userBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('hidden');
            // Close notification dropdown
            notifDropdown?.classList.add('hidden');
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', () => {
        notifDropdown?.classList.add('hidden');
        userDropdown?.classList.add('hidden');
    });

    // Check notifications on load and every 30 seconds
    checkNotifications();
    notificationInterval = setInterval(checkNotifications, 30000);
});

// Modal helper
function showModal(content, options = {}) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50';
    modal.id = 'modal-overlay';

    modal.innerHTML = `
        <div class="bg-white dark:bg-[#1a2632] rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-auto">
            ${options.title ? `
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">${options.title}</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            ` : ''}
            <div class="p-6">${content}</div>
        </div>
    `;

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const modal = document.getElementById('modal-overlay');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
}
