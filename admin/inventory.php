<?php
/**
 * admin/inventory.php
 * Quản lý Kho Hàng 3NF: Báo cáo Tồn Kho & Lập Phiếu Nhập Nhiều Dòng Linh Kiện
 * Giai đoạn 3: Tailwind CSS + InventoryAdminService + PRG Pattern
 */

$pageTitle = 'Quản lý Kho & Nhập hàng';
require_once __DIR__ . '/includes/auth_middleware.php';
checkAdminAuth();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/services/InventoryAdminService.php';
require_once __DIR__ . '/services/ProductAdminService.php';
require_once __DIR__ . '/services/CategoryAdminService.php';

$inventoryService = new InventoryAdminService();
$productService = new ProductAdminService();
$categoryService = new CategoryAdminService();

// ==========================================
// 1. XỬ LÝ FORM POST THEO CHUẨN PRG
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Lập phiếu nhập kho mới nhiều dòng linh kiện
    if ($action === 'create_receipt') {
        $nhacungcap = trim($_POST['nhacungcap'] ?? '');
        $ngaynhap = trim($_POST['ngaynhap'] ?? date('Y-m-d H:i:s'));
        $ghichu = trim($_POST['ghichu'] ?? '');
        $currentUserId = getCurrentUserId();

        $receiptData = [
            'nguoidung_id' => $currentUserId,
            'nhacungcap'   => $nhacungcap,
            'ngaynhap'     => $ngaynhap,
            'ghichu'       => $ghichu
        ];

        $mathangIds = $_POST['mathang_id'] ?? [];
        $soluongs = $_POST['soluong'] ?? [];
        $dongias = $_POST['dongianhap'] ?? [];
        $ghichus = $_POST['ghichu_item'] ?? [];

        $items = [];
        $seen = [];
        for ($i = 0; $i < count($mathangIds); $i++) {
            $mId = (int)($mathangIds[$i] ?? 0);
            $qty = (int)($soluongs[$i] ?? 0);
            $price = (float)($dongias[$i] ?? 0);
            $note = trim($ghichus[$i] ?? '');

            if ($mId > 0 && $qty > 0 && $price >= 0) {
                if (isset($seen[$mId])) {
                    // Tự động gộp số lượng nếu trùng mặt hàng
                    $items[$seen[$mId]]['soluong'] += $qty;
                } else {
                    $seen[$mId] = count($items);
                    $items[] = [
                        'mathang_id'  => $mId,
                        'soluong'     => $qty,
                        'dongianhap'  => $price,
                        'ghichu'      => $note
                    ];
                }
            }
        }

        try {
            $res = $inventoryService->createImportReceipt($receiptData, $items, $currentUserId);
            $receiptId = is_array($res) ? ($res['receipt_id'] ?? 0) : $res;
            setAdminFlash('success', "Đã lập phiếu nhập kho #{$receiptId} thành công! Số lượng tồn kho đã được cộng dồn.");
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi lập phiếu nhập: ' . $e->getMessage());
        }

        header('Location: inventory.php?tab=receipts');
        exit;
    }

    // Hủy phiếu nhập kho (Chỉ SuperAdmin)
    if ($action === 'cancel_receipt') {
        requireSuperAdmin('inventory.php?tab=receipts');
        $receiptId = (int)($_POST['receipt_id'] ?? 0);
        $reason = trim($_POST['cancel_reason'] ?? 'Hủy bởi Quản trị viên');

        try {
            $inventoryService->cancelImportReceipt($receiptId);
            setAdminFlash('success', "Đã hủy phiếu nhập #{$receiptId} và hoàn trừ tồn kho an toàn!");
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Không thể hủy phiếu nhập: ' . $e->getMessage());
        }

        header('Location: inventory.php?tab=receipts');
        exit;
    }
}

// ==========================================
// 2. LẤY DỮ LIỆU TAB & BỘ LỌC
// ==========================================
$currentTab = $_GET['tab'] ?? 'stock'; // 'stock' hoặc 'receipts'
$stockStatus = $_GET['stock_status'] ?? '';
$searchKeyword = trim($_GET['keyword'] ?? '');
$danhmucId = (isset($_GET['danhmuc_id']) && $_GET['danhmuc_id'] !== '') ? (int)$_GET['danhmuc_id'] : null;

// Lấy danh sách cho Tab 1: Tồn kho
$stockFilters = [
    'danhmuc_id'   => $danhmucId,
    'stock_status' => $stockStatus,
    'keyword'      => $searchKeyword,
    'limit'        => 50,
    'offset'       => 0
];
$inventorySummary = $inventoryService->getInventorySummary($stockFilters);
$stockItems = $inventorySummary['items'];
$totalStockItems = $inventorySummary['total'];

// Lấy danh sách cho Tab 2: Phiếu nhập
$receiptFilters = [
    'search' => $searchKeyword,
    'limit'  => 20,
    'offset' => 0
];
$receiptsData = $inventoryService->getImportReceipts($receiptFilters);
$receipts = $receiptsData['receipts'];
$totalReceipts = $receiptsData['total'];

// Danh sách danh mục & linh kiện hỗ trợ form lập phiếu nhập
$categories = $categoryService->getAllCategoriesWithCount();
$allProducts = $productService->getAllProducts(['limit' => 200])['products'];
$isSuper = isSuperAdmin();

require_once __DIR__ . '/inc/layout_header.php';
?>

<div class="space-y-6">

    <!-- Tiêu đề trang & Nút hành động -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Quản lý Kho & Nhập hàng Drone</h1>
            <p class="text-sm text-slate-500 mt-1">
                Kiểm soát số lượng tồn kho thực tế, giá trị vốn tồn đọng và quản lý chứng từ nhập kho 3NF.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openCreateReceiptModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white text-xs font-bold rounded-md transition shadow-none shadow-none/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Lập phiếu nhập mới</span>
            </button>
        </div>
    </div>

    <!-- 2 TABS CHUYỂN ĐỔI CHẾ ĐỘ -->
    <div class="border-b border-slate-200 flex items-center gap-6 text-sm font-bold">
        <a href="inventory.php?tab=stock" class="pb-3 border-b-2 flex items-center gap-2 transition <?= ($currentTab === 'stock') ? 'border-red-600 text-red-600' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <span>Báo cáo Tồn kho Thực tế (<?= $totalStockItems ?>)</span>
        </a>

        <a href="inventory.php?tab=receipts" class="pb-3 border-b-2 flex items-center gap-2 transition <?= ($currentTab === 'receipts') ? 'border-red-600 text-red-600' : 'border-transparent text-slate-500 hover:text-slate-800' ?>">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <span>Lịch sử Chứng từ Nhập kho (<?= $totalReceipts ?>)</span>
        </a>
    </div>

    <?php if ($currentTab === 'stock'): ?>
        <!-- ==========================================
             NỘI DUNG TAB 1: BÁO CÁO TỒN KHO THỰC TẾ
             ========================================== -->
        <!-- Bộ lọc nhanh tồn kho -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-none">
            <form action="inventory.php" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <input type="hidden" name="tab" value="stock">

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tìm kiếm linh kiện</label>
                    <input type="text" name="keyword" value="<?= htmlspecialchars($searchKeyword) ?>" placeholder="Tên linh kiện, dòng máy..." class="w-full px-3 py-2 text-xs rounded-md bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Danh mục</label>
                    <select name="danhmuc_id" class="w-full px-3 py-2 text-xs rounded-md bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                        <option value="">-- Tất cả danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>" <?= ($danhmucId === (int)$cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['tendanhmuc']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tình trạng tồn</label>
                    <select name="stock_status" class="w-full px-3 py-2 text-xs rounded-md bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="low" <?= ($stockStatus === 'low') ? 'selected' : '' ?>>Cảnh báo tồn ít (≤ 5)</option>
                        <option value="out" <?= ($stockStatus === 'out') ? 'selected' : '' ?>>Hết hàng (0)</option>
                        <option value="in_stock" <?= ($stockStatus === 'in_stock') ? 'selected' : '' ?>>Tồn dồi dào (> 5)</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-md transition shadow-none">
                        Lọc tồn kho
                    </button>
                    <a href="inventory.php?tab=stock" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-md transition" title="Xóa bộ lọc">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </a>
                </div>
            </form>
        </div>

        <!-- Bảng tổng hợp tồn kho -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-none overflow-hidden flex flex-col">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-4">Linh kiện Drone</th>
                            <th class="py-3.5 px-4">Dòng máy</th>
                            <th class="py-3.5 px-4 text-center">Tổng đã nhập</th>
                            <th class="py-3.5 px-4 text-center">Tổng đã bán</th>
                            <th class="py-3.5 px-4 text-center">Tồn kho hiện tại</th>
                            <th class="py-3.5 px-4 text-right">Giá vốn</th>
                            <th class="py-3.5 px-4 text-right">Tổng giá trị vốn</th>
                            <th class="py-3.5 px-4 text-center">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($stockItems)): ?>
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    Không có dữ liệu tồn kho khớp với bộ lọc.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($stockItems as $it): 
                                $imgUrl = !empty($it['hinhanh']) ? '../images/products/' . basename($it['hinhanh']) : '../images/products/no-image.png';
                            ?>
                                <tr class="table-hover-row transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Prod" class="w-10 h-10 rounded-lg object-cover border border-slate-200 flex-shrink-0" onerror="this.onerror=null; this.src='../images/products/no-image.png';">
                                            <div class="truncate max-w-[200px]">
                                                <div class="font-bold text-slate-800 truncate"><?= htmlspecialchars($it['tenmathang']) ?></div>
                                                <div class="text-[11px] text-slate-400 font-mono">#<?= (int)$it['id'] ?> • <?= htmlspecialchars($it['tendanhmuc']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                            <?= htmlspecialchars($it['dongmay'] ?: 'Dùng chung') ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-bold text-slate-600 font-mono-num">
                                        <?= number_format($it['tong_da_nhap']) ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-bold text-slate-600 font-mono-num">
                                        <?= number_format($it['tong_da_ban']) ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="text-sm font-extrabold text-slate-900 font-mono-num">
                                            <?= number_format($it['soluongton']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono-num text-slate-600">
                                        <?= $it['giagoc_fmt'] ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-red-600 font-mono-num">
                                        <?= $it['gia_tri_ton_kho_fmt'] ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-bold <?= $it['stock_status_class'] ?>">
                                            <?= htmlspecialchars($it['stock_status_label']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php else: ?>
        <!-- ==========================================
             NỘI DUNG TAB 2: LỊCH SỬ PHIẾU NHẬP KHO
             ========================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-none overflow-hidden flex flex-col">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-4">Mã Phiếu Nhập</th>
                            <th class="py-3.5 px-4">Nhà Cung Cấp</th>
                            <th class="py-3.5 px-4">Người Lập Phiếu</th>
                            <th class="py-3.5 px-4">Ngày Nhập Kho</th>
                            <th class="py-3.5 px-4 text-center">Số Mặt Hàng</th>
                            <th class="py-3.5 px-4 text-right">Tổng Tiền Nhập</th>
                            <th class="py-3.5 px-4 text-center">Trạng Thái</th>
                            <th class="py-3.5 px-4 text-right">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($receipts)): ?>
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    Chưa có phiếu nhập kho nào được ghi nhận.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($receipts as $rc): 
                                $isCompleted = ((int)$rc['trangthai'] === 1);
                            ?>
                                <tr class="table-hover-row transition-colors">
                                    <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                        <?= htmlspecialchars($rc['maphieunhap']) ?>
                                    </td>
                                    <td class="py-3.5 px-4 font-bold text-slate-800">
                                        <?= htmlspecialchars($rc['nhacungcap']) ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-600">
                                        <?= htmlspecialchars($rc['nguoi_lap']) ?>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono-num text-slate-500">
                                        <?= $rc['ngaynhap_fmt'] ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-bold text-slate-700">
                                        <?= (int)$rc['total_items'] ?> loại
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-red-600 text-sm font-mono-num">
                                        <?= $rc['tongtien_fmt'] ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <?php if ($isCompleted): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800">
                                                Hoàn thành
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-rose-100 text-rose-800">
                                                Đã hủy phiếu
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button" onclick='showReceiptDetail(<?= json_encode($rc, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition" title="Xem chi tiết các mặt hàng trong phiếu">
                                                Chi tiết
                                            </button>
                                            <?php if ($isSuper && $isCompleted): ?>
                                                <button type="button" onclick="confirmCancelReceipt(<?= (int)$rc['id'] ?>, '<?= htmlspecialchars(addslashes($rc['maphieunhap'])) ?>')" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold rounded-lg transition" title="Hủy phiếu nhập này">
                                                    Hủy
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- ==========================================
     MODAL LẬP PHIẾU NHẬP KHO MỚI (NHIỀU DÒNG)
     ========================================== -->
<div id="create-receipt-modal" class="modal-overlay modal-hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="modal-container bg-white w-full max-w-4xl rounded-3xl shadow-2xl border border-slate-100 overflow-hidden flex flex-col max-h-[92vh]">
        
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#B91C1C]"></span>
                    <span>Lập Phiếu Nhập Kho Linh Kiện Drone XAG</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Tự động tạo mã chứng từ 3NF và cộng dồn tồn kho vật lý</p>
            </div>
            <button type="button" onclick="closeModal('create-receipt-modal')" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="inventory.php" method="POST" id="create-receipt-form" onsubmit="return validateReceiptForm(event)" class="flex-1 overflow-y-auto p-6 space-y-5">
            <input type="hidden" name="action" value="create_receipt">

            <!-- Thông tin chung chứng từ: 3 ô thẳng hàng bằng nhau tuyệt đối chiều cao 40px -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px;">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Nhà cung cấp <span class="text-[#B91C1C]">*</span></label>
                    <input type="text" name="nhacungcap" required placeholder="XAG Official / XAG Vietnam Corp" class="w-full h-[40px] text-xs px-3.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#B91C1C]/20 focus:border-[#B91C1C] transition bg-white text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Ngày giờ nhập</label>
                    <input type="datetime-local" name="ngaynhap" value="<?= date('Y-m-d\TH:i') ?>" class="w-full h-[40px] text-xs px-3.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#B91C1C]/20 focus:border-[#B91C1C] transition bg-white text-slate-800">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Ghi chú chứng từ / Vận đơn</label>
                    <input type="text" name="ghichu" placeholder="Nhập lô bảo hành đợt 1 / Bay vụ hè thu" class="w-full h-[40px] text-xs px-3.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#B91C1C]/20 focus:border-[#B91C1C] transition bg-white text-slate-800">
                </div>
            </div>

            <!-- Bảng nhiều dòng linh kiện -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Chi tiết các mặt hàng nhập</h4>
                    <button type="button" onclick="addReceiptRow()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-[#B91C1C] text-xs font-bold rounded-lg transition border border-red-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Thêm dòng linh kiện</span>
                    </button>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-sm">
                    <table id="receipt-items-table" class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3" style="width: 42%;">Linh kiện XAG</th>
                                <th class="py-2.5 px-3 text-center" style="width: 14%;">Số lượng</th>
                                <th class="py-2.5 px-3 text-right" style="width: 20%;">Đơn giá nhập</th>
                                <th class="py-2.5 px-3 text-right" style="width: 18%;">Thành tiền</th>
                                <th class="py-2.5 px-3 text-center" style="width: 6%;">Xóa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <!-- Dòng mẫu mặc định -->
                            <tr class="receipt-row">
                                <td class="py-2.5 px-3 align-middle" style="width: 42%;">
                                    <select name="mathang_id[]" required onchange="onProductSelectChange(this)" class="w-full h-[38px] text-xs px-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#B91C1C]/20 focus:border-[#B91C1C] transition bg-white text-slate-800">
                                        <option value="">-- Chọn mặt hàng --</option>
                                        <?php foreach ($allProducts as $p): ?>
                                            <option value="<?= (int)$p['id'] ?>" data-giavon="<?= (float)$p['giagoc'] ?>" data-price="<?= (float)$p['giagoc'] ?>">
                                                <?= htmlspecialchars($p['tenmathang']) ?> (<?= htmlspecialchars($p['dongmay'] ?: 'Dùng chung') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" name="ghichu_item[]" placeholder="Mã lô / Serial..." class="w-full mt-[6px] text-[11px] h-[28px] px-2.5 rounded-md border border-slate-200 bg-slate-50 text-slate-600 focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#B91C1C]/30 focus:border-[#B91C1C] transition">
                                </td>
                                <td class="py-2.5 px-3 align-middle" style="width: 14%;">
                                    <input type="number" name="soluong[]" min="1" value="1" required oninput="recalculateReceiptTotal()" class="item-qty w-full h-[38px] text-center text-xs font-bold px-2 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#B91C1C]/20 focus:border-[#B91C1C] transition bg-white text-slate-800">
                                </td>
                                <td class="py-2.5 px-3 align-middle" style="width: 20%;">
                                    <input type="number" name="dongianhap[]" min="0" step="1000" value="0" required oninput="recalculateReceiptTotal()" class="item-price w-full h-[38px] text-right text-xs font-mono-num px-2.5 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-[#B91C1C]/20 focus:border-[#B91C1C] transition bg-white text-slate-800">
                                </td>
                                <td class="py-2.5 px-3 text-right font-bold text-slate-800 font-mono-num item-subtotal text-xs align-middle" style="width: 18%;">
                                    0 đ
                                </td>
                                <td class="py-2.5 px-3 text-center align-middle" style="width: 6%;">
                                    <button type="button" onclick="removeReceiptRow(this)" class="p-1.5 text-slate-400 hover:text-[#B91C1C] rounded-md hover:bg-red-50 transition" title="Xóa dòng này">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                            <tr>
                                <td colspan="3" class="py-3 px-3 text-right text-xs font-bold text-slate-700">Tổng tiền thanh toán phiếu nhập:</td>
                                <td id="receipt-grand-total" class="py-3 px-3 text-right text-sm sm:text-base text-[#B91C1C] font-mono-num font-extrabold whitespace-nowrap">0 đ</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('create-receipt-modal')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition border border-slate-200">
                    Hủy bỏ
                </button>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-[#B91C1C] hover:bg-[#991B1B] text-white font-bold text-xs rounded-lg transition shadow-sm hover:shadow active:scale-[0.99]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Lưu & Nhập kho ngay</span>
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ==========================================
     MODAL XEM CHI TIẾT CHỨNG TỪ PHIẾU NHẬP
     ========================================== -->
<div id="receipt-detail-modal" class="modal-overlay modal-hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="modal-container bg-white w-full max-w-3xl rounded-3xl shadow-none border border-slate-100 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <h3 id="rc-modal-title" class="text-base font-extrabold text-slate-900">Chi tiết Phiếu Nhập PN-XXXX</h3>
                <p id="rc-modal-sub" class="text-xs text-slate-500 mt-0.5">Nhà cung cấp: ---</p>
            </div>
            <button type="button" onclick="closeModal('receipt-detail-modal')" class="p-2 text-slate-400 hover:text-slate-600 rounded-md hover:bg-slate-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-6 overflow-y-auto flex-1">
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Linh kiện</th>
                            <th class="py-2.5 px-3">Dòng máy</th>
                            <th class="py-2.5 px-3 text-center">Số lượng</th>
                            <th class="py-2.5 px-3 text-right">Đơn giá nhập</th>
                            <th class="py-2.5 px-3 text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody id="rc-modal-tbody" class="divide-y divide-slate-100">
                        <!-- JS render -->
                    </tbody>
                    <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                        <tr>
                            <td colspan="4" class="py-3 px-3 text-right text-slate-700">Tổng giá trị phiếu nhập:</td>
                            <td id="rc-modal-grand-total" class="py-3 px-3 text-right text-base text-red-600 font-mono-num font-black">0 đ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-end">
            <button type="button" onclick="closeModal('receipt-detail-modal')" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-md transition">
                Đóng cửa sổ
            </button>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL XÁC NHẬN HỦY PHIẾU NHẬP (SUPER ADMIN)
     ========================================== -->
<div id="cancel-receipt-modal" class="modal-overlay modal-hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="modal-container bg-white w-full max-w-md rounded-3xl shadow-none border border-slate-100 p-6 text-center">
        <div class="w-12 h-12 rounded-md bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <h3 class="text-base font-extrabold text-slate-900 mb-1">Xác nhận hủy phiếu nhập?</h3>
        <p class="text-xs text-slate-500 mb-4">
            Khi hủy phiếu nhập <strong id="cancel-rc-code" class="text-slate-800"></strong>, toàn bộ số lượng linh kiện trong phiếu sẽ bị trừ lùi khỏi tồn kho.
        </p>
        <form action="inventory.php" method="POST" class="space-y-3">
            <input type="hidden" name="action" value="cancel_receipt">
            <input type="hidden" name="receipt_id" id="cancel-rc-id" value="">
            <input type="text" name="cancel_reason" required placeholder="Nhập lý do hủy phiếu (sai số lượng, đổi NCC...)" class="w-full text-xs px-3 py-2 rounded-md border border-slate-300">
            <div class="flex items-center justify-center gap-2 pt-2">
                <button type="button" onclick="closeModal('cancel-receipt-modal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-md transition">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-md transition shadow-none shadow-none/30">
                    Xác nhận hủy phiếu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function formatVND(amount) {
    const num = Math.round(Number(amount) || 0);
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + " đ";
}

function openCreateReceiptModal() {
    openModal('create-receipt-modal');
    recalculateReceiptTotal();
}

function onProductSelectChange(select) {
    const selectedOption = select.options[select.selectedIndex];
    const defaultPrice = parseFloat(selectedOption.getAttribute('data-giavon') || selectedOption.getAttribute('data-price') || 0);
    const row = select.closest('tr');
    const priceInput = row.querySelector('.item-price');
    if (priceInput) {
        const curPrice = parseFloat(priceInput.value) || 0;
        if (curPrice === 0 && defaultPrice > 0) {
            priceInput.value = defaultPrice;
        }
    }
    checkDuplicateProduct(select);
    recalculateReceiptTotal();
}

function checkDuplicateProduct(currentSelect) {
    const currentVal = currentSelect.value;
    if (!currentVal) return;

    const allSelects = document.querySelectorAll('#receipt-items-table tbody tr.receipt-row select[name="mathang_id[]"]');
    let duplicateRow = null;

    allSelects.forEach(sel => {
        if (sel !== currentSelect && sel.value === currentVal) {
            duplicateRow = sel.closest('tr');
        }
    });

    if (duplicateRow) {
        const prodName = currentSelect.options[currentSelect.selectedIndex].text.trim();
        const shouldMerge = confirm(
            `Linh kiện "${prodName}" đã có trong danh sách nhập kho!\n\n` +
            `Bạn có muốn GỘP số lượng vào dòng đã có không?\n` +
            `- Bấm "OK" để gộp số lượng.\n` +
            `- Bấm "Cancel" để chọn mặt hàng khác.`
        );

        if (shouldMerge) {
            const currentQty = parseInt(currentSelect.closest('tr').querySelector('.item-qty').value) || 1;
            const targetQtyInput = duplicateRow.querySelector('.item-qty');
            targetQtyInput.value = (parseInt(targetQtyInput.value) || 0) + currentQty;

            const rows = document.querySelectorAll('#receipt-items-table tbody tr.receipt-row');
            if (rows.length > 1) {
                currentSelect.closest('tr').remove();
            } else {
                currentSelect.value = '';
                const pInput = currentSelect.closest('tr').querySelector('.item-price');
                if (pInput) pInput.value = 0;
            }
        } else {
            currentSelect.value = '';
            const pInput = currentSelect.closest('tr').querySelector('.item-price');
            if (pInput) pInput.value = 0;
        }
    }
}

function addReceiptRow() {
    const tbody = document.querySelector('#receipt-items-table tbody');
    const firstRow = tbody.querySelector('tr.receipt-row');
    if (!firstRow) return;

    const newRow = firstRow.cloneNode(true);
    newRow.querySelector('select').selectedIndex = 0;
    newRow.querySelector('.item-qty').value = 1;
    newRow.querySelector('.item-price').value = 0;
    newRow.querySelector('.item-subtotal').textContent = '0 đ';
    const noteInput = newRow.querySelector('input[name="ghichu_item[]"]');
    if (noteInput) noteInput.value = '';

    tbody.appendChild(newRow);
    recalculateReceiptTotal();
}

function removeReceiptRow(btn) {
    const tbody = document.querySelector('#receipt-items-table tbody');
    const rows = tbody.querySelectorAll('tr.receipt-row');
    if (rows.length <= 1) {
        alert('Phiếu nhập kho phải chứa ít nhất một dòng linh kiện.');
        return;
    }
    btn.closest('tr').remove();
    recalculateReceiptTotal();
}

function recalculateReceiptTotal() {
    const rows = document.querySelectorAll('#receipt-items-table tbody tr.receipt-row');
    let grandTotal = 0;

    rows.forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const subtotal = Math.round(qty * price);
        grandTotal += subtotal;
        const subtotalEl = row.querySelector('.item-subtotal');
        if (subtotalEl) {
            subtotalEl.textContent = formatVND(subtotal);
        }
    });

    const grandTotalEl = document.getElementById('receipt-grand-total');
    if (grandTotalEl) {
        grandTotalEl.textContent = formatVND(grandTotal);
    }
}

// Aliases
const recalcReceiptTotal = recalculateReceiptTotal;
const updateRowTotal = recalculateReceiptTotal;

function validateReceiptForm(e) {
    const nccInput = document.querySelector('input[name="nhacungcap"]');
    if (!nccInput || !nccInput.value.trim()) {
        alert('Vui lòng nhập tên Nhà cung cấp / đơn vị phân phối.');
        if (nccInput) nccInput.focus();
        if (e && e.preventDefault) e.preventDefault();
        return false;
    }

    const rows = document.querySelectorAll('#receipt-items-table tbody tr.receipt-row');
    if (rows.length === 0) {
        alert('Phiếu nhập kho phải chứa ít nhất một dòng linh kiện.');
        if (e && e.preventDefault) e.preventDefault();
        return false;
    }

    let hasValidItem = false;
    const selectedIds = new Set();

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const select = row.querySelector('select[name="mathang_id[]"]');
        const qtyInput = row.querySelector('.item-qty');
        const priceInput = row.querySelector('.item-price');

        const mId = select ? select.value : '';
        const qty = parseInt(qtyInput ? qtyInput.value : 0) || 0;
        const price = parseFloat(priceInput ? priceInput.value : 0) || 0;

        if (!mId) {
            alert(`Dòng số ${i + 1}: Vui lòng chọn linh kiện XAG hoặc xóa dòng này.`);
            if (select) select.focus();
            if (e && e.preventDefault) e.preventDefault();
            return false;
        }

        if (selectedIds.has(mId)) {
            const prodName = select.options[select.selectedIndex].text.trim();
            alert(`Linh kiện "${prodName}" bị trùng lặp ở dòng số ${i + 1}. Vui lòng gộp số lượng hoặc xóa dòng trùng.`);
            select.focus();
            if (e && e.preventDefault) e.preventDefault();
            return false;
        }
        selectedIds.add(mId);

        if (qty < 1) {
            alert(`Dòng số ${i + 1}: Số lượng nhập phải lớn hơn hoặc bằng 1.`);
            if (qtyInput) qtyInput.focus();
            if (e && e.preventDefault) e.preventDefault();
            return false;
        }

        if (price < 0) {
            alert(`Dòng số ${i + 1}: Đơn giá nhập không được nhỏ hơn 0.`);
            if (priceInput) priceInput.focus();
            if (e && e.preventDefault) e.preventDefault();
            return false;
        }

        hasValidItem = true;
    }

    if (!hasValidItem) {
        alert('Vui lòng chọn ít nhất 1 mặt hàng linh kiện hợp lệ để nhập kho.');
        if (e && e.preventDefault) e.preventDefault();
        return false;
    }

    return true;
}

function showReceiptDetail(rc) {
    document.getElementById('rc-modal-title').textContent = 'Phiếu Nhập ' + rc.maphieunhap;
    document.getElementById('rc-modal-sub').textContent = 'Nhà cung cấp: ' + rc.nhacungcap + ' • Người lập: ' + rc.nguoi_lap + ' • ' + rc.ngaynhap_fmt;
    document.getElementById('rc-modal-grand-total').textContent = rc.tongtien_fmt;

    const tbody = document.getElementById('rc-modal-tbody');
    tbody.innerHTML = '';
    if (rc.items && rc.items.length > 0) {
        rc.items.forEach(it => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="py-2.5 px-3 font-bold text-slate-800">${it.tenmathang}</td>
                <td class="py-2.5 px-3 text-slate-500">${it.dongmay || 'Dùng chung'}</td>
                <td class="py-2.5 px-3 text-center font-bold text-slate-900">${it.soluong}</td>
                <td class="py-2.5 px-3 text-right font-mono-num text-slate-700">${it.dongianhap_fmt}</td>
                <td class="py-2.5 px-3 text-right font-bold text-slate-900 font-mono-num">${it.thanhtien_fmt}</td>
            `;
            tbody.appendChild(tr);
        });
    }
    openModal('receipt-detail-modal');
}

function confirmCancelReceipt(id, code) {
    document.getElementById('cancel-rc-id').value = id;
    document.getElementById('cancel-rc-code').textContent = code;
    openModal('cancel-receipt-modal');
}
</script>

<?php require_once __DIR__ . '/inc/layout_footer.php'; ?>
