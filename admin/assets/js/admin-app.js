/**
 * admin/assets/js/admin-app.js
 * XAG Store - Trình điều khiển tương tác Giao diện Quản trị
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Khởi tạo Lucide Icons nếu có thư viện
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // 2. Sidebar Mobile Toggle
    const sidebar = document.getElementById('admin-sidebar');
    const sidebarBackdrop = document.getElementById('sidebar-backdrop');
    const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
    const sidebarCloseBtn = document.getElementById('sidebar-close-btn');

    function openSidebar() {
        if (sidebar && sidebarBackdrop) {
            sidebar.classList.remove('-translate-x-full');
            sidebarBackdrop.classList.remove('hidden');
        }
    }

    function closeSidebar() {
        if (sidebar && sidebarBackdrop) {
            sidebar.classList.add('-translate-x-full');
            sidebarBackdrop.classList.add('hidden');
        }
    }

    if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', openSidebar);
    }
    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', closeSidebar);
    }
    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', closeSidebar);
    }

    // 3. User Dropdown Toggle ở Topbar
    const userMenuBtn = document.getElementById('user-menu-btn');
    const userMenuDropdown = document.getElementById('user-menu-dropdown');

    if (userMenuBtn && userMenuDropdown) {
        userMenuBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            userMenuDropdown.classList.toggle('hidden');
        });

        document.addEventListener('click', function (e) {
            if (!userMenuDropdown.contains(e.target) && !userMenuBtn.contains(e.target)) {
                userMenuDropdown.classList.add('hidden');
            }
        });
    }

    // 4. Quản lý Toast tự động biến mất sau 3.5s
    const toasts = document.querySelectorAll('.toast-item');
    toasts.forEach(function (toast) {
        const timeoutId = setTimeout(function () {
            dismissToast(toast);
        }, 3500);

        const closeBtn = toast.querySelector('.toast-close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                clearTimeout(timeoutId);
                dismissToast(toast);
            });
        }
    });

    function dismissToast(toast) {
        toast.classList.add('hide');
        setTimeout(function () {
            if (toast && toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 400);
    }

    // 5. Image Preview tự động khi chọn file
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview-target]');
    fileInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            const targetId = input.getAttribute('data-preview-target');
            const previewImg = document.getElementById(targetId);
            if (previewImg && input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        });
    });

    // 6. Đóng Modal khi bấm phím ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal-overlay:not(.modal-hidden)');
            openModals.forEach(function (modal) {
                closeModal(modal.id);
            });
        }
    });
});

/**
 * Mở modal chung theo ID
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('modal-hidden');
        document.body.classList.add('overflow-hidden');
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
}

/**
 * Đóng modal chung theo ID
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('modal-hidden');
        // Kiểm tra xem còn modal nào đang mở không trước khi gỡ overflow-hidden
        const remaining = document.querySelectorAll('.modal-overlay:not(.modal-hidden)');
        if (remaining.length === 0) {
            document.body.classList.remove('overflow-hidden');
        }
    }
}

/**
 * Tiện ích định dạng số sang VNĐ (Client-side)
 */
function formatVNDJs(amount) {
    const num = Math.round(Number(amount) || 0);
    return num.toLocaleString('vi-VN') + ' ₫';
}

/**
 * Tính toán tự động thành tiền trên các dòng Phiếu nhập kho (Inventory)
 */
function recalculateReceiptTotal() {
    const rows = document.querySelectorAll('#receipt-items-table tbody tr.receipt-row');
    let grandTotal = 0;

    rows.forEach(function (row) {
        const qtyInput = row.querySelector('.item-qty');
        const priceInput = row.querySelector('.item-price');
        const totalCell = row.querySelector('.item-subtotal');

        const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
        const price = parseFloat(priceInput ? priceInput.value : 0) || 0;
        const subtotal = Math.max(0, qty * price);

        if (totalCell) {
            totalCell.textContent = formatVNDJs(subtotal);
        }
        grandTotal += subtotal;
    });

    const grandTotalDisplay = document.getElementById('receipt-grand-total');
    if (grandTotalDisplay) {
        grandTotalDisplay.textContent = formatVNDJs(grandTotal);
    }
}
