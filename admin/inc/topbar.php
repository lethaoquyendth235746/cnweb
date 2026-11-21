<?php
/**
 * admin/inc/topbar.php
 * Thanh điều hướng trên cùng (Topbar) của Admin XAG Store
 */

$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitles = [
    'index.php'      => ['title' => 'Bảng điều khiển', 'parent' => 'Tổng quan'],
    'orders.php'     => ['title' => 'Quản lý Đơn hàng Drone', 'parent' => 'Bán hàng'],
    'products.php'   => ['title' => 'Quản lý Linh kiện & Drone', 'parent' => 'Kho & Linh kiện'],
    'categories.php' => ['title' => 'Danh mục Linh kiện', 'parent' => 'Kho & Linh kiện'],
    'inventory.php'  => ['title' => 'Quản lý Kho & Nhập hàng', 'parent' => 'Kho & Linh kiện'],
    'customers.php'  => ['title' => 'Khách hàng & Hợp tác xã', 'parent' => 'Khách hàng'],
    'users.php'      => ['title' => 'Nhân sự & Phân quyền', 'parent' => 'Hệ thống'],
    'profile.php'    => ['title' => 'Hồ sơ & Đổi mật khẩu', 'parent' => 'Cá nhân'],
];

$currentMeta = $pageTitles[$currentPage] ?? ['title' => 'Hệ thống Quản trị', 'parent' => 'Admin'];
$currentUser = getCurrentUser();
$avatarImg = !empty($currentUser['hinhanh']) ? '../images/users/' . basename($currentUser['hinhanh']) : '../images/users/user.png';
$isSuper = isSuperAdmin();
?>
<header class="h-16 bg-white border-b border-zinc-200/80 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 shadow-sm">
    <!-- Trái: Nút Toggle Mobile & Breadcrumbs -->
    <div class="flex items-center gap-3 sm:gap-4">
        <!-- Nút mở Sidebar Mobile -->
        <button id="sidebar-toggle-btn" class="md:hidden p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>

        <!-- Breadcrumbs -->
        <nav class="flex items-center text-sm font-medium text-slate-500">
            <span class="text-slate-400 hidden sm:inline"><?= htmlspecialchars($currentMeta['parent']) ?></span>
            <svg class="w-4 h-4 mx-2 text-slate-300 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="text-red-600 font-semibold truncate max-w-[200px] sm:max-w-none"><?= htmlspecialchars($currentMeta['title']) ?></span>
        </nav>
    </div>

    <!-- Phải: Quick Search, Nút xem trang bán hàng & User Dropdown -->
    <div class="flex items-center gap-2 sm:gap-4">
        <!-- Quick Search Form -->
        <form action="products.php" method="GET" class="relative hidden lg:block" onsubmit="
            var q = this.search.value.trim();
            if (/^\d+$/.test(q)) {
                this.action = 'orders.php';
            } else {
                this.action = 'products.php';
            }
        ">
            <div class="relative">
                <input type="text" name="search" placeholder="Mã đơn (#12) hoặc tên linh kiện..." class="w-64 pl-9 pr-4 py-1.5 text-xs rounded-xl bg-slate-100 border border-slate-200 text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:bg-white transition-all">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </form>

        <!-- Nút Xem trang bán hàng -->
        <a href="../public/index.php" target="_blank" rel="noopener noreferrer" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50/60 border border-red-200 rounded-xl hover:bg-red-50 hover:border-red-300 transition-colors shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            <span>Xem Website</span>
        </a>

        <!-- Phân cách dọc -->
        <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

        <!-- User Menu Dropdown -->
        <div class="relative">
            <button id="user-menu-btn" type="button" class="flex items-center gap-2 p-1 rounded-xl hover:bg-slate-100 transition-colors focus:outline-none">
                <img src="<?= htmlspecialchars($avatarImg) ?>" alt="User Avatar" class="w-8 h-8 rounded-full object-cover border border-red-500/40" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($currentUser['hoten'] ?? 'Admin') ?>&background=dc2626&color=fff';">
                <div class="hidden sm:block text-left">
                    <p class="text-xs font-bold text-slate-700 leading-tight"><?= htmlspecialchars($currentUser['hoten'] ?? 'Admin') ?></p>
                    <p class="text-[10px] text-slate-500 font-medium"><?= $isSuper ? 'Quản trị viên' : 'Kỹ thuật viên' ?></p>
                </div>
                <svg class="w-4 h-4 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <!-- Dropdown Menu -->
            <div id="user-menu-dropdown" class="hidden absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-50 animate-fadeIn">
                <div class="px-4 py-2 border-b border-slate-100">
                    <p class="text-xs font-bold text-slate-800"><?= htmlspecialchars($currentUser['hoten'] ?? '') ?></p>
                    <p class="text-[11px] text-slate-500 truncate"><?= htmlspecialchars($currentUser['email'] ?? '') ?></p>
                </div>
                <a href="profile.php" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-red-600 transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>Hồ sơ & Đổi mật khẩu</span>
                </a>
                <a href="../public/index.php" target="_blank" class="sm:hidden flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-red-600 transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    <span>Xem Website</span>
                </a>
                <div class="my-1 border-t border-slate-100"></div>
                <a href="ktnguoidung/index.php?action=dangxuat" class="flex items-center gap-2.5 px-4 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 transition-colors">
                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span>Đăng xuất hệ thống</span>
                </a>
            </div>
        </div>
    </div>
</header>
