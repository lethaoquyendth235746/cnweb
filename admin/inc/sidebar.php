<?php
/**
 * admin/inc/sidebar.php
 * Thanh điều hướng bên trái (Sidebar) của Admin XAG Store
 */

$currentPage = basename($_SERVER['PHP_SELF']);

// Lấy số liệu động cho badge
$pendingOrdersCount = 0;
$lowStockCount = 0;

try {
    if (class_exists('OrderAdminService')) {
        $orderSvc = new OrderAdminService();
        $statusCounts = $orderSvc->getOrderStatusCounts();
        $pendingOrdersCount = $statusCounts[0] ?? 0;
    }
    if (class_exists('DashboardService')) {
        $dashSvc = new DashboardService();
        $kpi = $dashSvc->getKpiMetrics();
        $lowStockCount = ($kpi['sap_het_hang'] ?? 0) + ($kpi['het_hang'] ?? 0);
    }
} catch (Throwable $e) {
    // Không chặn render nếu lỗi truy vấn
}

$currentUser = getCurrentUser();
$isSuper = isSuperAdmin();
?>
<!-- Lớp phủ mờ (Backdrop) trên Mobile -->
<div id="sidebar-backdrop" class="fixed inset-0 bg-slate-900/60 z-40 hidden md:hidden transition-opacity"></div>

<!-- Khung Sidebar chính -->
<aside id="admin-sidebar" class="fixed top-0 left-0 bottom-0 w-64 bg-[#111315] text-zinc-300 z-50 flex flex-col -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out border-r border-zinc-800/80">
    <!-- Header Logo -->
    <div class="h-16 flex items-center justify-between px-5 bg-[#111315] border-b border-zinc-800/80">
        <a href="index.php" class="flex items-center space-x-3 group">
            <div class="w-9 h-9 rounded-lg bg-red-500/10 border border-red-500/30 flex items-center justify-center text-red-500 group-hover:scale-105 transition-transform overflow-hidden">
                <img src="../images/logo.png" alt="XAG Store" class="w-7 h-7 object-contain" onerror="this.onerror=null; this.src='../images/logo.png';">
            </div>
            <div>
                <span class="text-base font-bold text-white tracking-wide flex items-center gap-1.5">
                    LPT <span class="text-red-500 font-extrabold">DRONE</span>
                </span>
                <span class="text-[10px] text-zinc-400 tracking-wider font-semibold uppercase block -mt-0.5">ADMIN PORTAL</span>
            </div>
        </a>
        <button id="sidebar-close-btn" class="md:hidden text-zinc-400 hover:text-white p-1 rounded-lg hover:bg-zinc-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Navigation Links -->
    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
        <!-- Nhóm: Tổng quan -->
        <div>
            <div class="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-zinc-500">Tổng quan</div>
            <a href="index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium transition-all <?= ($currentPage === 'index.php') ? 'bg-red-700 text-white font-semibold' : 'text-zinc-400 hover:bg-zinc-900 hover:text-red-400' ?>">
                <svg class="w-5 h-5 <?= ($currentPage === 'index.php') ? 'text-white' : 'text-zinc-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Bảng điều khiển</span>
            </a>
        </div>

        <!-- Nhóm: Bán hàng -->
        <div>
            <div class="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-zinc-500">Bán hàng</div>
            <a href="orders.php" class="flex items-center justify-between px-3 py-2.5 rounded-md text-sm font-medium transition-all <?= ($currentPage === 'orders.php') ? 'bg-red-700 text-white shadow-none shadow-none/25 font-semibold' : 'text-zinc-400 hover:bg-zinc-900 hover:text-red-400' ?>">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 <?= ($currentPage === 'orders.php') ? 'text-white' : 'text-zinc-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span>Đơn hàng Drone</span>
                </div>
                <?php if ($pendingOrdersCount > 0): ?>
                    <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-red-700 rounded-md animate-pulse shadow-none shadow-none/50">
                        <?= $pendingOrdersCount ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>

        <!-- Nhóm: Kho & Sản phẩm -->
        <div>
            <div class="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-zinc-500">Kho & Linh kiện</div>
            <div class="space-y-1">
                <a href="products.php" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium transition-all <?= ($currentPage === 'products.php') ? 'bg-red-700 text-white shadow-none shadow-none/25 font-semibold' : 'text-zinc-400 hover:bg-zinc-900 hover:text-red-400' ?>">
                    <svg class="w-5 h-5 <?= ($currentPage === 'products.php') ? 'text-white' : 'text-zinc-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span>Linh kiện & Drone</span>
                </a>

                <a href="categories.php" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium transition-all <?= ($currentPage === 'categories.php') ? 'bg-red-700 text-white shadow-none shadow-none/25 font-semibold' : 'text-zinc-400 hover:bg-zinc-900 hover:text-red-400' ?>">
                    <svg class="w-5 h-5 <?= ($currentPage === 'categories.php') ? 'text-white' : 'text-zinc-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                    <span>Danh mục</span>
                </a>

                <a href="inventory.php" class="flex items-center justify-between px-3 py-2.5 rounded-md text-sm font-medium transition-all <?= ($currentPage === 'inventory.php') ? 'bg-red-700 text-white shadow-none shadow-none/25 font-semibold' : 'text-zinc-400 hover:bg-zinc-900 hover:text-red-400' ?>">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 <?= ($currentPage === 'inventory.php') ? 'text-white' : 'text-zinc-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        <span>Quản lý Kho & Nhập</span>
                    </div>
                    <?php if ($lowStockCount > 0): ?>
                        <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-red-700 rounded-md shadow-none shadow-none/50" title="Sản phẩm tồn kho thấp hoặc hết hàng">
                            <?= $lowStockCount ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Nhóm: Hệ thống & Khách hàng -->
        <div>
            <div class="px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-zinc-500">Khách hàng & Hệ thống</div>
            <div class="space-y-1">
                <a href="customers.php" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium transition-all <?= ($currentPage === 'customers.php') ? 'bg-red-700 text-white shadow-none shadow-none/25 font-semibold' : 'text-zinc-400 hover:bg-zinc-900 hover:text-red-400' ?>">
                    <svg class="w-5 h-5 <?= ($currentPage === 'customers.php') ? 'text-white' : 'text-zinc-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>Khách hàng & HTX</span>
                </a>

                <?php if ($isSuper): ?>
                <a href="users.php" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium transition-all <?= ($currentPage === 'users.php') ? 'bg-red-700 text-white shadow-none shadow-none/25 font-semibold' : 'text-zinc-400 hover:bg-zinc-900 hover:text-red-400' ?>">
                    <svg class="w-5 h-5 <?= ($currentPage === 'users.php') ? 'text-white' : 'text-zinc-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>Nhân sự & Quyền</span>
                </a>
                <?php endif; ?>

                <a href="profile.php" class="flex items-center gap-3 px-3 py-2.5 rounded-md text-sm font-medium transition-all <?= ($currentPage === 'profile.php') ? 'bg-red-700 text-white shadow-none shadow-none/25 font-semibold' : 'text-zinc-400 hover:bg-zinc-900 hover:text-red-400' ?>">
                    <svg class="w-5 h-5 <?= ($currentPage === 'profile.php') ? 'text-white' : 'text-zinc-400' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>Hồ sơ & Đổi mật khẩu</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer Profile tóm tắt -->
    <div class="p-3 border-t border-zinc-800/80 bg-[#111315]">
        <div class="flex items-center gap-3 p-2 rounded-md bg-zinc-900/80 border border-zinc-800/60">
            <?php 
                $avatarImg = !empty($currentUser['hinhanh']) ? '../images/users/' . basename($currentUser['hinhanh']) : '../images/users/user.png';
            ?>
            <img src="<?= htmlspecialchars($avatarImg) ?>" alt="Avatar" class="w-9 h-9 rounded-md object-cover border border-red-500/30" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($currentUser['hoten'] ?? 'Admin') ?>&background=dc2626&color=fff';">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-white truncate"><?= htmlspecialchars($currentUser['hoten'] ?? 'Quản trị viên') ?></p>
                <p class="text-[11px] text-red-400 truncate"><?= $isSuper ? 'Quản trị viên cấp cao' : 'Kỹ thuật viên XAG' ?></p>
            </div>
            <a href="ktnguoidung/index.php?action=dangxuat" title="Đăng xuất" class="text-zinc-400 hover:text-rose-400 transition-colors p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </a>
        </div>
    </div>
</aside>
