<?php
/**
 * admin/index.php
 * Bảng điều khiển Quản trị Trung tâm (Dashboard) - XAG Store
 * Giai đoạn 3: Tailwind CSS + Chart.js + Service Layer
 */

$pageTitle = 'Bảng điều khiển';
require_once __DIR__ . '/inc/layout_header.php';
require_once __DIR__ . '/services/DashboardService.php';
require_once __DIR__ . '/services/OrderAdminService.php';

$dashService = new DashboardService();

// Lấy chỉ số KPI
$kpis = $dashService->getKpiMetrics();

// Lấy dữ liệu biểu đồ 7 ngày & 6 tháng
$chartData7Days = $dashService->getRevenueChartData(7);
$chartData6Months = $dashService->getMonthlyRevenueChartData(6);

// Lấy đơn hàng mới nhất và cảnh báo tồn kho
$recentOrders = $dashService->getRecentOrders(6);
$lowStockAlerts = $dashService->getLowStockAlerts(5);
$topProducts = $dashService->getTopSellingProducts(5);
?>

<div class="space-y-6">

    <!-- Tiêu đề trang & Chào mừng -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <span>Tổng quan Vận hành XAG Store</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                    Trực tiếp
                </span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Theo dõi hiệu suất kinh doanh máy bay nông nghiệp, tình trạng tồn kho linh kiện và tiến độ xử lý đơn hàng.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="inventory.php" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 hover:border-slate-400 transition shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Nhập kho mới</span>
            </a>
            <a href="orders.php" class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold text-white bg-gradient-to-r from-red-600 to-orange-600 rounded-xl hover:from-red-700 hover:to-orange-700 transition shadow-sm shadow-red-600/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span>Xử lý đơn (<?= $kpis['don_cho_xac_nhan'] ?>)</span>
            </a>
        </div>
    </div>

    <!-- 4 THẺ KPI TRỰC QUAN -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        
        <!-- KPI 1: Doanh thu thực tế -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Doanh thu thực tế</span>
                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 tracking-tight font-mono-num">
                    <?= $kpis['tong_doanh_thu_fmt'] ?>
                </div>
                <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
                    <span class="text-red-600 font-semibold">Hôm nay:</span>
                    <span class="font-bold text-slate-700"><?= $kpis['doanh_thu_hom_nay_fmt'] ?></span>
                    <span class="text-slate-300">•</span>
                    <span>Tháng: <?= $kpis['doanh_thu_thang_nay_fmt'] ?></span>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-red-500 to-orange-500"></div>
        </div>

        <!-- KPI 2: Tổng đơn hàng & Chờ xác nhận -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Đơn hàng Drone</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 tracking-tight font-mono-num flex items-baseline gap-2">
                    <span><?= number_format($kpis['tong_don_hang']) ?></span>
                    <span class="text-xs font-medium text-slate-500">đơn toàn bộ</span>
                </div>
                <div class="mt-2 flex items-center gap-1.5 text-xs">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold <?= ($kpis['don_cho_xac_nhan'] > 0) ? 'bg-amber-100 text-amber-800 animate-pulse' : 'bg-slate-100 text-slate-600' ?>">
                        <span class="w-1.5 h-1.5 rounded-full <?= ($kpis['don_cho_xac_nhan'] > 0) ? 'bg-amber-500' : 'bg-slate-400' ?>"></span>
                        <?= $kpis['don_cho_xac_nhan'] ?> chờ duyệt
                    </span>
                    <span class="text-slate-400">•</span>
                    <span class="text-slate-500 font-medium"><?= $kpis['don_dang_giao'] ?> đang giao</span>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-400 to-indigo-600"></div>
        </div>

        <!-- KPI 3: Tổng linh kiện & Danh mục -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Linh kiện & Drone</span>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-slate-900 tracking-tight font-mono-num flex items-baseline gap-2">
                    <span><?= number_format($kpis['tong_san_pham']) ?></span>
                    <span class="text-xs font-medium text-slate-500">mặt hàng XAG</span>
                </div>
                <div class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
                    <span class="font-medium text-purple-700 font-semibold"><?= $kpis['tong_phieu_nhap'] ?> phiếu nhập</span>
                    <span class="text-slate-300">•</span>
                    <span>Vốn: <?= $kpis['tong_chi_nhap_kho_fmt'] ?></span>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-400 to-pink-600"></div>
        </div>

        <!-- KPI 4: Cảnh báo tồn kho thấp & Hết hàng -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden group hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cảnh báo Tồn kho</span>
                <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-rose-600 tracking-tight font-mono-num flex items-baseline gap-2">
                    <span><?= ($kpis['sap_het_hang'] + $kpis['het_hang']) ?></span>
                    <span class="text-xs font-medium text-slate-500">mặt hàng cần nhập</span>
                </div>
                <div class="mt-2 flex items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1 font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">
                        <?= $kpis['het_hang'] ?> hết hàng
                    </span>
                    <span class="inline-flex items-center gap-1 font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-md">
                        <?= $kpis['sap_het_hang'] ?> tồn ≤ 5
                    </span>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-400 to-rose-600"></div>
        </div>

    </div>

    <!-- BIỂU ĐỒ CHART.JS DOANH THU & ĐƠN HÀNG -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-5 sm:p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h3 class="text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    <span>Xu hướng Doanh thu & Lượng đơn hàng</span>
                    <span id="chart-view-label" class="text-xs font-normal text-slate-500">(7 ngày gần nhất)</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Biểu đồ đối soát doanh thu hoàn thành và số lượng đơn hàng theo chu kỳ</p>
            </div>
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl self-start">
                <button type="button" id="btn-chart-7d" onclick="switchChartView('7d')" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-white text-red-600 shadow-sm transition">
                    7 ngày
                </button>
                <button type="button" id="btn-chart-6m" onclick="switchChartView('6m')" class="px-3 py-1.5 text-xs font-medium rounded-lg text-slate-600 hover:text-slate-900 transition">
                    6 tháng
                </button>
            </div>
        </div>

        <div class="relative w-full h-72 sm:h-80">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- 2 BẢNG THAO TÁC NHANH: ĐƠN HÀNG MỚI & CẢNH BÁO TỒN KHO -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- BẢNG 1: 6 Đơn hàng mới nhất -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Đơn hàng mới tiếp nhận</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Theo dõi và xác nhận đơn đặt hàng Drone kịp thời</p>
                </div>
                <a href="orders.php" class="text-xs font-semibold text-red-600 hover:text-red-700 flex items-center gap-1">
                    <span>Xem tất cả</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Mã đơn</th>
                            <th class="py-3 px-4">Khách hàng</th>
                            <th class="py-3 px-4 text-right">Tổng tiền</th>
                            <th class="py-3 px-4 text-center">Trạng thái</th>
                            <th class="py-3 px-4 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($recentOrders)): ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    Chưa có đơn hàng nào được ghi nhận.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentOrders as $order): ?>
                                <tr class="table-hover-row transition-colors">
                                    <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                        #<?= (int)$order['id'] ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-slate-800 truncate max-w-[140px]"><?= htmlspecialchars($order['hoten']) ?></div>
                                        <div class="text-[11px] text-slate-400 font-mono-num"><?= htmlspecialchars($order['sodienthoai']) ?></div>
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-slate-900 font-mono-num">
                                        <?= $order['tongtien_fmt'] ?>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold <?= $order['status_meta']['badge_class'] ?>">
                                            <?= htmlspecialchars($order['status_meta']['label']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="orders.php?search=<?= (int)$order['id'] ?>" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition">
                                            <span>Xem</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- BẢNG 2: Cảnh báo Tồn kho thấp & Nhập hàng nhanh -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span>Cảnh báo Linh kiện sắp hết</span>
                        <?php if (!empty($lowStockAlerts)): ?>
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                        <?php endif; ?>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Linh kiện có số lượng tồn ≤ 5 cần lập phiếu nhập kho bổ sung</p>
                </div>
                <a href="inventory.php" class="text-xs font-semibold text-red-600 hover:text-red-700 flex items-center gap-1">
                    <span>Vào kho</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>

            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Linh kiện Drone</th>
                            <th class="py-3 px-4">Dòng máy</th>
                            <th class="py-3 px-4 text-center">Tồn kho</th>
                            <th class="py-3 px-4 text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($lowStockAlerts)): ?>
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-400">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-8 h-8 text-emerald-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>Tuyệt vời! Toàn bộ linh kiện đều đang đủ tồn kho an toàn.</span>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($lowStockAlerts as $item): ?>
                                <tr class="table-hover-row transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <?php 
                                                $prodImg = !empty($item['hinhanh']) ? '../images/products/' . basename($item['hinhanh']) : '../images/products/no-image.png';
                                            ?>
                                            <img src="<?= htmlspecialchars($prodImg) ?>" alt="Product" class="w-9 h-9 rounded-lg object-cover border border-slate-200" onerror="this.onerror=null; this.src='../images/products/no-image.png';">
                                            <div class="truncate max-w-[160px]">
                                                <div class="font-bold text-slate-800 truncate"><?= htmlspecialchars($item['tenmathang']) ?></div>
                                                <div class="text-[11px] text-slate-500 font-mono-num"><?= formatVND($item['giaban']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                            <?= htmlspecialchars($item['dongmay'] ?: 'Dùng chung') ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <?php if ((int)$item['soluongton'] === 0): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700">
                                                Hết hàng (0)
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                                Còn <?= (int)$item['soluongton'] ?> cái
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <a href="inventory.php?action=new_receipt&product_id=<?= (int)$item['id'] ?>" class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-red-700 bg-red-50 hover:bg-red-100 rounded-lg transition">
                                            <span>Nhập kho</span>
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<!-- SCRIPT BIỂU ĐỒ CHART.JS -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Bộ dữ liệu từ PHP
    const data7d = {
        labels: <?= json_encode($chartData7Days['labels'] ?? []) ?>,
        revenues: <?= json_encode($chartData7Days['revenues'] ?? []) ?>,
        orders: <?= json_encode($chartData7Days['orders'] ?? []) ?>
    };

    const data6m = {
        labels: <?= json_encode($chartData6Months['labels'] ?? []) ?>,
        revenues: <?= json_encode($chartData6Months['revenues'] ?? []) ?>,
        orders: <?= json_encode($chartData6Months['orders'] ?? []) ?>
    };

    let activeData = data7d;

    // Gradient màu Đỏ Cam XAG cho Doanh thu
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(234, 88, 12, 0.35)');
    gradient.addColorStop(1, 'rgba(234, 88, 12, 0.0)');

    const config = {
        type: 'line',
        data: {
            labels: activeData.labels,
            datasets: [
                {
                    label: 'Doanh thu (VNĐ)',
                    data: activeData.revenues,
                    borderColor: '#DC2626',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#EA580C',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    yAxisID: 'y'
                },
                {
                    label: 'Số đơn hàng',
                    data: activeData.orders,
                    type: 'bar',
                    backgroundColor: 'rgba(99, 102, 241, 0.35)',
                    borderColor: '#6366f1',
                    borderWidth: 1.5,
                    borderRadius: 6,
                    barThickness: 16,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        font: { family: 'Inter', size: 12, weight: '500' }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.datasetIndex === 0) {
                                label += new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' ₫';
                            } else {
                                label += context.parsed.y + ' đơn';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Inter', size: 11 } }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { family: 'Inter', size: 11 },
                        callback: function (val) {
                            if (val >= 1000000) {
                                return (val / 1000000).toFixed(1) + ' tr';
                            }
                            if (val >= 1000) {
                                return (val / 1000).toFixed(0) + ' k';
                            }
                            return val;
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: {
                        stepSize: 1,
                        font: { family: 'Inter', size: 11 },
                        precision: 0
                    }
                }
            }
        }
    };

    window.mainRevenueChart = new Chart(ctx, config);

    window.switchChartView = function (mode) {
        const btn7d = document.getElementById('btn-chart-7d');
        const btn6m = document.getElementById('btn-chart-6m');
        const label = document.getElementById('chart-view-label');

        if (mode === '7d') {
            btn7d.className = 'px-3 py-1.5 text-xs font-bold rounded-lg bg-white text-red-600 shadow-sm transition';
            btn6m.className = 'px-3 py-1.5 text-xs font-medium rounded-lg text-slate-600 hover:text-slate-900 transition';
            label.textContent = '(7 ngày gần nhất)';
            
            window.mainRevenueChart.data.labels = data7d.labels;
            window.mainRevenueChart.data.datasets[0].data = data7d.revenues;
            window.mainRevenueChart.data.datasets[1].data = data7d.orders;
        } else {
            btn6m.className = 'px-3 py-1.5 text-xs font-bold rounded-lg bg-white text-red-600 shadow-sm transition';
            btn7d.className = 'px-3 py-1.5 text-xs font-medium rounded-lg text-slate-600 hover:text-slate-900 transition';
            label.textContent = '(6 tháng gần nhất)';

            window.mainRevenueChart.data.labels = data6m.labels;
            window.mainRevenueChart.data.datasets[0].data = data6m.revenues;
            window.mainRevenueChart.data.datasets[1].data = data6m.orders;
        }
        window.mainRevenueChart.update();
    };
});
</script>

<?php require_once __DIR__ . '/inc/layout_footer.php'; ?>
