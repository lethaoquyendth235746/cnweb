<?php
/**
 * admin/orders.php
 * Quản lý Đơn hàng Drone & Vòng đời State Machine - XAG Store
 * Giai đoạn 3: Tailwind CSS + Service Layer + PRG Pattern
 */

$pageTitle = 'Quản lý Đơn hàng Drone';
require_once __DIR__ . '/includes/auth_middleware.php';
checkAdminAuth();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/services/OrderAdminService.php';

$orderService = new OrderAdminService();

// ==========================================
// 1. XỬ LÝ AJAX LẤY CHI TIẾT ĐƠN HÀNG (JSON)
// ==========================================
if (isset($_GET['ajax']) && $_GET['ajax'] === 'order_detail') {
    header('Content-Type: application/json; charset=utf-8');
    $orderId = (int)($_GET['id'] ?? 0);
    $order = $orderService->getOrderById($orderId);
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
    } else {
        echo json_encode(['success' => true, 'order' => $order]);
    }
    exit;
}

// ==========================================
// 2. XỬ LÝ FORM POST THEO CHUẨN PRG
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = (int)($_POST['new_status'] ?? 0);
        $adminNote = trim($_POST['admin_note'] ?? '');

        try {
            $orderService->updateOrderStatus($orderId, $newStatus, $adminNote);
            $statusMeta = getOrderStatusMeta($newStatus);
            setAdminFlash('success', "Đã cập nhật trạng thái đơn hàng #{$orderId} thành: " . $statusMeta['label']);
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi cập nhật: ' . $e->getMessage());
        }

        // Chuyển hướng giữ nguyên các filter hiện tại
        $redirectUrl = 'orders.php';
        $params = [];
        if (isset($_POST['return_trangthai']) && $_POST['return_trangthai'] !== '') {
            $params['trangthai'] = $_POST['return_trangthai'];
        }
        if (!empty($_POST['return_search'])) {
            $params['search'] = $_POST['return_search'];
        }
        if (!empty($params)) {
            $redirectUrl .= '?' . http_build_query($params);
        }

        header("Location: {$redirectUrl}");
        exit;
    }
}

// ==========================================
// 3. LẤY DỮ LIỆU & BỘ LỌC ĐA NĂNG
// ==========================================
$selectedStatus = (isset($_GET['trangthai']) && $_GET['trangthai'] !== '') ? (int)$_GET['trangthai'] : null;
$search = trim($_GET['search'] ?? '');
$fromDate = trim($_GET['from_date'] ?? '');
$toDate = trim($_GET['to_date'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

$filters = [
    'trangthai' => $selectedStatus,
    'search'    => $search,
    'from_date' => $fromDate,
    'to_date'   => $toDate,
    'limit'     => $limit,
    'offset'    => $offset
];

$ordersData = $orderService->getAllOrders($filters);
$orders = $ordersData['orders'];
$totalOrders = $ordersData['total'];
$totalPages = ceil($totalOrders / $limit);

// Lấy số đếm đơn hàng theo trạng thái cho Tabs
$statusCounts = $orderService->getOrderStatusCounts();

// Ma trận State Machine hiển thị trạng thái chuyển đổi hợp lệ
$allowedTransitionsMap = [
    0 => [1, 4],
    1 => [2, 4],
    2 => [3, 5],
    3 => [5],
    4 => [],
    5 => []
];

require_once __DIR__ . '/inc/layout_header.php';
?>

<div class="space-y-6">

    <!-- Tiêu đề trang -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Quản lý Đơn hàng Drone</h1>
            <p class="text-sm text-slate-500 mt-1">
                Theo dõi quy trình kiểm định seal, xuất kho linh kiện và cập nhật vận đơn Drone XAG.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold px-3 py-1.5 bg-slate-100 text-slate-600 rounded-md">
                Tổng cộng: <strong class="text-slate-900 font-mono-num"><?= number_format($totalOrders) ?></strong> đơn
            </span>
        </div>
    </div>

    <!-- HỆ THỐNG TABS LỌC TRẠNG THÁI -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-2 shadow-none overflow-x-auto">
        <div class="flex items-center gap-1.5 min-w-max">
            <!-- Tab: Tất cả -->
            <a href="orders.php<?= (!empty($search) ? '?search=' . urlencode($search) : '') ?>" 
               class="flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-bold transition-all <?= ($selectedStatus === null) ? 'bg-slate-900 text-white shadow-none' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
                <span>Tất cả</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] <?= ($selectedStatus === null) ? 'bg-slate-700 text-white' : 'bg-slate-200 text-slate-700' ?>">
                    <?= $statusCounts['all'] ?? 0 ?>
                </span>
            </a>

            <!-- Tab 0: Chờ xác nhận -->
            <a href="orders.php?trangthai=0<?= (!empty($search) ? '&search=' . urlencode($search) : '') ?>" 
               class="flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-bold transition-all <?= ($selectedStatus === 0) ? 'bg-amber-500 text-white shadow-none' : 'text-slate-600 hover:bg-amber-50 hover:text-amber-800' ?>">
                <span>Chờ xác nhận</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] <?= ($selectedStatus === 0) ? 'bg-amber-600 text-white' : 'bg-amber-100 text-amber-800' ?>">
                    <?= $statusCounts[0] ?? 0 ?>
                </span>
            </a>

            <!-- Tab 1: Đã xác nhận -->
            <a href="orders.php?trangthai=1<?= (!empty($search) ? '&search=' . urlencode($search) : '') ?>" 
               class="flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-bold transition-all <?= ($selectedStatus === 1) ? 'bg-blue-600 text-white shadow-none' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-800' ?>">
                <span>Đã xác nhận & Đóng gói</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] <?= ($selectedStatus === 1) ? 'bg-blue-700 text-white' : 'bg-blue-100 text-blue-800' ?>">
                    <?= $statusCounts[1] ?? 0 ?>
                </span>
            </a>

            <!-- Tab 2: Đang giao -->
            <a href="orders.php?trangthai=2<?= (!empty($search) ? '&search=' . urlencode($search) : '') ?>" 
               class="flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-bold transition-all <?= ($selectedStatus === 2) ? 'bg-indigo-600 text-white shadow-none' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-800' ?>">
                <span>Đang vận chuyển</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] <?= ($selectedStatus === 2) ? 'bg-indigo-700 text-white' : 'bg-indigo-100 text-indigo-800' ?>">
                    <?= $statusCounts[2] ?? 0 ?>
                </span>
            </a>

            <!-- Tab 3: Hoàn thành -->
            <a href="orders.php?trangthai=3<?= (!empty($search) ? '&search=' . urlencode($search) : '') ?>" 
               class="flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-bold transition-all <?= ($selectedStatus === 3) ? 'bg-emerald-600 text-white shadow-none' : 'text-slate-600 hover:bg-emerald-50 hover:text-emerald-800' ?>">
                <span>Hoàn thành</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] <?= ($selectedStatus === 3) ? 'bg-emerald-700 text-white' : 'bg-emerald-100 text-emerald-800' ?>">
                    <?= $statusCounts[3] ?? 0 ?>
                </span>
            </a>

            <!-- Tab 4: Đã hủy -->
            <a href="orders.php?trangthai=4<?= (!empty($search) ? '&search=' . urlencode($search) : '') ?>" 
               class="flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-bold transition-all <?= ($selectedStatus === 4) ? 'bg-rose-600 text-white shadow-none' : 'text-slate-600 hover:bg-rose-50 hover:text-rose-800' ?>">
                <span>Đã hủy</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] <?= ($selectedStatus === 4) ? 'bg-rose-700 text-white' : 'bg-rose-100 text-rose-800' ?>">
                    <?= $statusCounts[4] ?? 0 ?>
                </span>
            </a>

            <!-- Tab 5: Trả hàng / Hoàn tiền -->
            <a href="orders.php?trangthai=5<?= (!empty($search) ? '&search=' . urlencode($search) : '') ?>" 
               class="flex items-center gap-2 px-3.5 py-2 rounded-md text-xs font-bold transition-all <?= ($selectedStatus === 5) ? 'bg-purple-600 text-white shadow-none' : 'text-slate-600 hover:bg-purple-50 hover:text-purple-800' ?>">
                <span>Trả hàng / Hoàn tiền</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] <?= ($selectedStatus === 5) ? 'bg-purple-700 text-white' : 'bg-purple-100 text-purple-800' ?>">
                    <?= $statusCounts[5] ?? 0 ?>
                </span>
            </a>
        </div>
    </div>

    <!-- THANH TÌM KIẾM & BỘ LỌC NGÀY -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-none">
        <form action="orders.php" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <?php if ($selectedStatus !== null): ?>
                <input type="hidden" name="trangthai" value="<?= $selectedStatus ?>">
            <?php endif; ?>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Từ khóa tìm kiếm</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Mã đơn (#), Tên khách, SĐT..." class="w-full pl-9 pr-3 py-2 text-xs rounded-md bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:bg-white transition">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Từ ngày</label>
                <input type="date" name="from_date" value="<?= htmlspecialchars($fromDate) ?>" class="w-full px-3 py-2 text-xs rounded-md bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:bg-white transition">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Đến ngày</label>
                <input type="date" name="to_date" value="<?= htmlspecialchars($toDate) ?>" class="w-full px-3 py-2 text-xs rounded-md bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:bg-white transition">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-md transition shadow-none flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span>Lọc đơn</span>
                </button>
                <a href="orders.php" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-md transition" title="Xóa bộ lọc">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </a>
            </div>
        </form>
    </div>

    <!-- BẢNG DANH SÁCH ĐƠN HÀNG -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-none overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Mã đơn</th>
                        <th class="py-3.5 px-4">Khách hàng & Liên hệ</th>
                        <th class="py-3.5 px-4">Ngày tạo đơn</th>
                        <th class="py-3.5 px-4 text-right">Tổng tiền</th>
                        <th class="py-3.5 px-4 text-center">Trạng thái</th>
                        <th class="py-3.5 px-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-10 h-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                    <p class="font-medium text-slate-600">Không tìm thấy đơn hàng nào khớp với điều kiện lọc.</p>
                                    <a href="orders.php" class="text-xs text-red-600 hover:underline mt-1">Xóa bộ lọc để xem lại tất cả</a>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $od): 
                            $stMeta = getOrderStatusMeta($od['trangthai']);
                        ?>
                            <tr class="table-hover-row transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                    #<?= (int)$od['id'] ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($od['hoten']) ?></div>
                                    <div class="flex items-center gap-2 text-slate-500 mt-0.5">
                                        <span class="font-mono-num"><?= htmlspecialchars($od['sodienthoai']) ?></span>
                                        <?php if (!empty($od['email'])): ?>
                                            <span>•</span>
                                            <span class="text-slate-400 truncate max-w-[160px]"><?= htmlspecialchars($od['email']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($od['diachi_chitiet'])): ?>
                                        <div class="text-[11px] text-slate-400 mt-0.5 truncate max-w-[280px]">
                                            📍 <?= htmlspecialchars($od['diachi_chitiet']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 font-mono-num whitespace-nowrap">
                                    <?= formatDateVN($od['ngay'], true) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-slate-900 text-sm font-mono-num whitespace-nowrap">
                                    <?= formatVND($od['tongtien']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold border <?= $stMeta['badge_class'] ?>">
                                        <span class="w-1.5 h-1.5 rounded-md <?= $stMeta['dot_class'] ?>"></span>
                                        <?= htmlspecialchars($stMeta['label']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <button type="button" onclick="openOrderDetail(<?= (int)$od['id'] ?>)" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 rounded-md font-bold transition shadow-none">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <span>Xử lý đơn</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
            <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-500">
                    Trang <strong><?= $page ?></strong> / <strong><?= $totalPages ?></strong> (Tổng <?= number_format($totalOrders) ?> đơn)
                </span>
                <div class="flex items-center gap-1">
                    <?php for ($p = 1; $p <= $totalPages; $p++): 
                        $query = $_GET;
                        $query['page'] = $p;
                        $pageUrl = 'orders.php?' . http_build_query($query);
                    ?>
                        <a href="<?= $pageUrl ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition <?= ($p === $page) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ==========================================
     MODAL CHI TIẾT & CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG
     ========================================== -->
<div id="order-detail-modal" class="modal-overlay modal-hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="modal-container bg-white w-full max-w-3xl rounded-3xl shadow-none border border-slate-100 overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header Modal -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span>Chi tiết Đơn hàng</span>
                    <span id="modal-order-id-badge" class="font-mono text-red-600 font-black">#---</span>
                </h3>
                <p id="modal-order-date" class="text-xs text-slate-500 mt-0.5">Thời gian đặt: ---</p>
            </div>
            <button type="button" onclick="closeModal('order-detail-modal')" class="p-2 text-slate-400 hover:text-slate-600 rounded-md hover:bg-slate-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Body Modal (Cuộn được) -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            
            <!-- Thông tin khách hàng & Địa chỉ -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200/60 text-xs">
                <div>
                    <h4 class="font-bold text-slate-400 uppercase tracking-wider mb-2 text-[10px]">Người nhận hàng</h4>
                    <p id="modal-customer-name" class="font-bold text-slate-900 text-sm">---</p>
                    <p id="modal-customer-phone" class="text-slate-600 font-mono-num mt-0.5">SĐT: ---</p>
                    <p id="modal-customer-email" class="text-slate-500 mt-0.5">Email: ---</p>
                </div>
                <div>
                    <h4 class="font-bold text-slate-400 uppercase tracking-wider mb-2 text-[10px]">Địa chỉ giao & Ghi chú</h4>
                    <p id="modal-customer-address" class="text-slate-700 leading-relaxed">---</p>
                    <p id="modal-order-note" class="text-amber-800 bg-amber-50 p-2 rounded-lg mt-2 border border-amber-200">Ghi chú: Không có</p>
                </div>
            </div>

            <!-- Bảng danh sách linh kiện trong đơn -->
            <div>
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-3">Danh sách linh kiện trong đơn</h4>
                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3">Linh kiện</th>
                                <th class="py-2.5 px-3">Dòng máy</th>
                                <th class="py-2.5 px-3 text-right">Đơn giá</th>
                                <th class="py-2.5 px-3 text-center">SL</th>
                                <th class="py-2.5 px-3 text-right">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="modal-order-items-tbody" class="divide-y divide-slate-100">
                            <!-- JS tự động render -->
                        </tbody>
                        <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                            <tr>
                                <td colspan="4" class="py-3 px-3 text-right text-slate-700">Tổng thanh toán:</td>
                                <td id="modal-order-grand-total" class="py-3 px-3 text-right text-base text-red-600 font-mono-num font-black">0 đ</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Form cập nhật trạng thái (State Machine) -->
            <form id="order-update-form" action="orders.php" method="POST" class="bg-orange-50/40 p-4 rounded-2xl border border-orange-200/80">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" id="form-order-id" value="">
                <input type="hidden" name="return_trangthai" value="<?= htmlspecialchars((string)$selectedStatus) ?>">
                <input type="hidden" name="return_search" value="<?= htmlspecialchars($search) ?>">

                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Cập nhật vòng đời đơn hàng (State Machine)</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Chuyển sang trạng thái tiếp theo</label>
                        <select name="new_status" id="modal-select-status" class="w-full text-xs font-semibold px-3 py-2 rounded-md bg-white border border-orange-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                            <!-- Các option hợp lệ sẽ được JS populate theo State Machine -->
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Ghi chú xử lý nội bộ của Admin</label>
                        <input type="text" name="admin_note" id="modal-admin-note" placeholder="Mã vận đơn bưu cục / lý do hủy..." class="w-full text-xs px-3 py-2 rounded-md bg-white border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <span id="modal-status-rule-tip" class="text-[11px] text-slate-500 italic">
                        * Khi chuyển sang Hủy hoặc Hoàn tiền, linh kiện sẽ tự động được hoàn về kho XAG.
                    </span>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white font-bold text-xs rounded-md transition shadow-none shadow-none/25">
                        Cập nhật ngay
                    </button>
                </div>
            </form>

        </div>

        <!-- Footer Modal -->
        <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-end">
            <button type="button" onclick="closeModal('order-detail-modal')" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-md transition">
                Đóng cửa sổ
            </button>
        </div>

    </div>
</div>

<!-- SCRIPT XỬ LÝ MODAL AJAX VÀ STATE MACHINE -->
<script>
const STATE_META = {
    0: { label: 'Chờ xác nhận', desc: 'Đơn mới, giữ chỗ tồn kho' },
    1: { label: 'Đã xác nhận & Chờ đóng gói', desc: 'Kiểm tra seal, tem XAG' },
    2: { label: 'Đang vận chuyển', desc: 'Hàng đã xuất kho vật lý' },
    3: { label: 'Hoàn thành', desc: 'Khách nhận & đối soát doanh thu' },
    4: { label: 'Đã hủy', desc: 'Hủy đơn, hoàn lại tồn kho' },
    5: { label: 'Trả hàng / Hoàn tiền', desc: 'Đổi trả bảo hành, hoàn tồn kho' }
};

const ALLOWED_TRANSITIONS = <?= json_encode($allowedTransitionsMap) ?>;

function openOrderDetail(orderId) {
    // Reset modal trước khi load
    document.getElementById('modal-order-id-badge').textContent = '#' + orderId;
    document.getElementById('form-order-id').value = orderId;
    document.getElementById('modal-order-items-tbody').innerHTML = '<tr><td colspan="5" class="py-6 text-center text-slate-400">Đang tải chi tiết đơn hàng...</td></tr>';

    fetch('orders.php?ajax=order_detail&id=' + orderId)
        .then(response => response.json())
        .then(data => {
            if (!data.success || !data.order) {
                alert('Không thể tải chi tiết đơn hàng: ' + (data.message || 'Lỗi không xác định'));
                return;
            }

            const ord = data.order;
            document.getElementById('modal-order-date').textContent = 'Thời gian đặt: ' + ord.ngay_fmt;
            document.getElementById('modal-customer-name').textContent = ord.hoten || 'Khách vãng lai';
            document.getElementById('modal-customer-phone').textContent = 'SĐT: ' + (ord.sodienthoai || '---');
            document.getElementById('modal-customer-email').textContent = 'Email: ' + (ord.email || '---');
            document.getElementById('modal-customer-address').textContent = ord.diachi_chitiet || 'Địa chỉ chưa cập nhật';
            document.getElementById('modal-order-note').textContent = 'Ghi chú đặt hàng: ' + (ord.ghichu || 'Không có ghi chú');
            document.getElementById('modal-order-grand-total').textContent = ord.tongtien_fmt;

            // Render danh sách items
            const tbody = document.getElementById('modal-order-items-tbody');
            tbody.innerHTML = '';
            if (ord.items && ord.items.length > 0) {
                ord.items.forEach(item => {
                    const imgUrl = item.hinhanh ? ('../images/products/' + item.hinhanh.split('/').pop()) : '../images/products/no-image.png';
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50 transition-colors';
                    tr.innerHTML = `
                        <td class="py-2.5 px-3">
                            <div class="flex items-center gap-2">
                                <img src="${imgUrl}" class="w-8 h-8 rounded-lg object-cover border border-slate-200" onerror="this.onerror=null; this.src='../images/products/no-image.png';">
                                <span class="font-bold text-slate-800">${item.tenmathang}</span>
                            </div>
                        </td>
                        <td class="py-2.5 px-3 text-slate-500">${item.dongmay || 'Dùng chung'}</td>
                        <td class="py-2.5 px-3 text-right font-mono-num text-slate-700">${item.dongia_fmt}</td>
                        <td class="py-2.5 px-3 text-center font-bold text-slate-900">${item.soluong}</td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900 font-mono-num">${item.thanhtien_fmt}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-slate-400">Không có dữ liệu mặt hàng</td></tr>';
            }

            // Populate các trạng thái hợp lệ tiếp theo theo State Machine
            const currentStatus = parseInt(ord.trangthai, 10);
            const selectStatus = document.getElementById('modal-select-status');
            selectStatus.innerHTML = '';

            // Option 1: Giữ nguyên trạng thái hiện tại
            const currentOpt = document.createElement('option');
            currentOpt.value = currentStatus;
            currentOpt.textContent = `[Hiện tại] ${STATE_META[currentStatus].label}`;
            currentOpt.selected = true;
            selectStatus.appendChild(currentOpt);

            // Các trạng thái được phép chuyển đến
            const allowed = ALLOWED_TRANSITIONS[currentStatus] || [];
            allowed.forEach(nextSt => {
                const opt = document.createElement('option');
                opt.value = nextSt;
                opt.textContent = `➔ Chuyển sang: ${STATE_META[nextSt].label} (${STATE_META[nextSt].desc})`;
                selectStatus.appendChild(opt);
            });

            if (allowed.length === 0) {
                document.getElementById('modal-status-rule-tip').textContent = 'Đơn hàng này đã ở trạng thái kết thúc (Terminal State). Không thể chuyển tiếp.';
            } else {
                document.getElementById('modal-status-rule-tip').textContent = '* Chọn trạng thái hợp lệ theo luồng State Machine để bảo đảm an toàn tồn kho.';
            }

            openModal('order-detail-modal');
        })
        .catch(err => {
            alert('Lỗi kết nối khi tải đơn hàng: ' + err);
        });
}
</script>

<?php require_once __DIR__ . '/inc/layout_footer.php'; ?>
