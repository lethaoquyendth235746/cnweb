<?php
/**
 * admin/products.php
 * Quản lý Linh kiện & Máy bay Drone XAG - CRUD & Quản lý Động Dòng máy (dongmay)
 * Giai đoạn 3: Tailwind CSS + ProductAdminService + PRG Pattern
 */

$pageTitle = 'Quản lý Linh kiện & Drone';
require_once __DIR__ . '/includes/auth_middleware.php';
checkAdminAuth();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/services/ProductAdminService.php';
require_once __DIR__ . '/services/CategoryAdminService.php';
require_once __DIR__ . '/../model/mathang.php';

$productService = new ProductAdminService();
$categoryService = new CategoryAdminService();

// ==========================================
// 1. XỬ LÝ FORM POST THEO CHUẨN PRG
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Thêm mới sản phẩm
    if ($action === 'create_product') {
        try {
            $data = [
                'tenmathang' => $_POST['tenmathang'] ?? '',
                'danhmuc_id' => (int)($_POST['danhmuc_id'] ?? 0),
                'dongmay'    => $_POST['dongmay'] ?? '',
                'giagoc'     => $_POST['giagoc'] ?? 0,
                'giaban'     => $_POST['giaban'] ?? 0,
                'soluongton' => $_POST['soluongton'] ?? 0,
                'mota'       => $_POST['mota'] ?? '',
                'noibat'     => isset($_POST['noibat']) ? 1 : 0
            ];
            $file = isset($_FILES['hinhanh']) && $_FILES['hinhanh']['error'] !== UPLOAD_ERR_NO_FILE ? $_FILES['hinhanh'] : null;

            $newId = $productService->createProduct($data, $file);
            setAdminFlash('success', "Đã thêm mới linh kiện [{$data['tenmathang']}] thành công (ID: #{$newId})!");
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi thêm mới: ' . $e->getMessage());
        }

        header('Location: products.php');
        exit;
    }

    // Cập nhật sản phẩm
    if ($action === 'update_product') {
        $id = (int)($_POST['product_id'] ?? 0);
        try {
            $data = [
                'tenmathang' => $_POST['tenmathang'] ?? '',
                'danhmuc_id' => (int)($_POST['danhmuc_id'] ?? 0),
                'dongmay'    => $_POST['dongmay'] ?? '',
                'giagoc'     => $_POST['giagoc'] ?? 0,
                'giaban'     => $_POST['giaban'] ?? 0,
                'soluongton' => $_POST['soluongton'] ?? 0,
                'mota'       => $_POST['mota'] ?? '',
                'noibat'     => isset($_POST['noibat']) ? 1 : 0
            ];
            $file = isset($_FILES['hinhanh']) && $_FILES['hinhanh']['error'] !== UPLOAD_ERR_NO_FILE ? $_FILES['hinhanh'] : null;

            $productService->updateProduct($id, $data, $file);
            setAdminFlash('success', "Đã cập nhật linh kiện #{$id} thành công!");
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi cập nhật: ' . $e->getMessage());
        }

        header('Location: products.php');
        exit;
    }

    // Bật/tắt trạng thái nổi bật (1-click)
    if ($action === 'toggle_noibat') {
        $id = (int)($_POST['product_id'] ?? 0);
        try {
            $newVal = $productService->toggleNoiBat($id);
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => true,
                    'noibat'  => $newVal,
                    'message' => $newVal ? "Đã bật trạng thái nổi bật cho linh kiện #{$id}!" : "Đã tắt trạng thái nổi bật cho linh kiện #{$id}!"
                ]);
                exit;
            }
            setAdminFlash('success', "Đã " . ($newVal ? "bật" : "tắt") . " trạng thái nổi bật cho linh kiện #{$id}!");
        } catch (Throwable $e) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit;
            }
            setAdminFlash('danger', 'Lỗi cập nhật nổi bật: ' . $e->getMessage());
        }

        $redirect = !empty($_POST['return_url']) ? $_POST['return_url'] : 'products.php';
        header('Location: ' . $redirect);
        exit;
    }

    // Xóa sản phẩm (Chỉ SuperAdmin)
    if ($action === 'delete_product') {
        requireSuperAdmin('products.php');
        $id = (int)($_POST['product_id'] ?? 0);
        try {
            $productService->deleteProduct($id);
            setAdminFlash('success', "Đã xóa vĩnh viễn linh kiện #{$id} khỏi hệ thống!");
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi xóa sản phẩm: ' . $e->getMessage());
        }

        header('Location: products.php');
        exit;
    }

    // ==========================================
    // QUẢN LÝ DÒNG MÁY (DONGMAY) ACTIONS
    // ==========================================

    // Thêm mới dòng máy Drone
    if ($action === 'create_dongmay') {
        $tenDongMay = trim($_POST['tendongmay'] ?? '');
        try {
            $newId = $productService->addDongMay($tenDongMay);
            setAdminFlash('success', "Đã thêm mới dòng máy [{$tenDongMay}] thành công (ID: #{$newId})!");
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi thêm dòng máy: ' . $e->getMessage());
        }

        header('Location: products.php?open=dongmay');
        exit;
    }

    // Cập nhật tên dòng máy Drone
    if ($action === 'update_dongmay') {
        $id = (int)($_POST['dongmay_id'] ?? 0);
        $tenMoi = trim($_POST['tendongmay'] ?? '');
        try {
            $productService->updateDongMay($id, $tenMoi);
            setAdminFlash('success', "Đã đổi tên dòng máy thành [{$tenMoi}] và đồng bộ tất cả linh kiện liên quan!");
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi cập nhật dòng máy: ' . $e->getMessage());
        }

        header('Location: products.php?open=dongmay');
        exit;
    }

    // Xóa dòng máy Drone
    if ($action === 'delete_dongmay') {
        $id = (int)($_POST['dongmay_id'] ?? 0);
        try {
            $productService->deleteDongMay($id);
            setAdminFlash('success', "Đã xóa dòng máy thành công! Các linh kiện liên quan đã được chuyển sang chế độ 'Dùng chung'.");
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi xóa dòng máy: ' . $e->getMessage());
        }

        header('Location: products.php?open=dongmay');
        exit;
    }
}

// ==========================================
// 2. BỘ LỌC & DANH SÁCH SẢN PHẨM
// ==========================================
$search = trim($_GET['search'] ?? '');
$danhmucId = (isset($_GET['danhmuc_id']) && $_GET['danhmuc_id'] !== '') ? (int)$_GET['danhmuc_id'] : null;
$dongmay = trim($_GET['dongmay'] ?? '');
$noibat = (isset($_GET['noibat']) && $_GET['noibat'] !== '') ? (int)$_GET['noibat'] : null;
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$filters = [
    'search'     => $search,
    'danhmuc_id' => $danhmucId,
    'dongmay'    => $dongmay,
    'noibat'     => $noibat,
    'limit'      => $limit,
    'offset'     => $offset
];

$productsData = $productService->getAllProducts($filters);
$products = $productsData['products'];
$totalProducts = $productsData['total'];
$totalPages = ceil($totalProducts / $limit);

$categories = $categoryService->getAllCategoriesWithCount();
$allDongMay = $productService->getAllDongMay();
$isSuper = isSuperAdmin();

require_once __DIR__ . '/inc/layout_header.php';
?>

<div class="space-y-6">

    <!-- Tiêu đề trang & Nút hành động -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Quản lý Linh kiện & Drone XAG</h1>
            <p class="text-sm text-slate-500 mt-1">
                Danh mục máy bay nông nghiệp, phụ tùng thay thế chính hãng XAG theo từng dòng máy tương thích.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <!-- Nút Quản lý Dòng máy -->
            <button type="button" onclick="openDongMayModal()" class="inline-flex items-center gap-1.5 px-3 py-2.5 bg-white border border-slate-300 hover:border-red-300 text-slate-700 hover:text-red-700 text-xs font-bold rounded-md transition shadow-none">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <span>Quản lý dòng máy</span>
            </button>

            <!-- Nút Thêm linh kiện mới -->
            <button type="button" onclick="openCreateModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white text-xs font-bold rounded-md transition shadow-none shadow-none/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Thêm linh kiện mới</span>
            </button>
        </div>
    </div>

    <!-- THANH BỘ LỌC ĐA NĂNG -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-none">
        <form action="products.php" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tìm kiếm linh kiện</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tên linh kiện, mã hoặc mô tả..." class="w-full pl-9 pr-3 py-2 text-xs rounded-md bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:bg-white transition">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Danh mục linh kiện</label>
                <select name="danhmuc_id" class="w-full px-3 py-2 text-xs rounded-md bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:bg-white transition">
                    <option value="">-- Tất cả danh mục --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= ($danhmucId === (int)$cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['tendanhmuc']) ?> (<?= $cat['tong_san_pham'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Dòng máy tương thích</label>
                <select name="dongmay" class="w-full px-3 py-2 text-xs rounded-md bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:bg-white transition">
                    <option value="">-- Tất cả dòng máy --</option>
                    <?php foreach ($allDongMay as $dm): ?>
                        <option value="<?= htmlspecialchars($dm['tendongmay']) ?>" <?= ($dongmay === $dm['tendongmay']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dm['tendongmay']) ?> (<?= (int)$dm['tong_san_pham'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Sản phẩm nổi bật</label>
                <select name="noibat" class="w-full px-3 py-2 text-xs rounded-md bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:bg-white transition">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="1" <?= ($noibat === 1) ? 'selected' : '' ?>>⭐ Sản phẩm nổi bật</option>
                    <option value="0" <?= ($noibat === 0) ? 'selected' : '' ?>>Sản phẩm thông thường</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-md transition shadow-none flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span>Lọc</span>
                </button>
                <a href="products.php" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-md transition" title="Xóa bộ lọc">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </a>
            </div>
        </form>
    </div>

    <!-- BẢNG DANH SÁCH LINH KIỆN -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-none overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Linh kiện & Hình ảnh</th>
                        <th class="py-3.5 px-4">Danh mục</th>
                        <th class="py-3.5 px-4">Dòng máy XAG</th>
                        <th class="py-3.5 px-4 text-right">Giá vốn / Giá bán</th>
                        <th class="py-3.5 px-4 text-center">Tồn kho</th>
                        <th class="py-3.5 px-4 text-center">Lượt xem/mua</th>
                        <th class="py-3.5 px-4 text-center">Nổi bật</th>
                        <th class="py-3.5 px-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <p class="font-medium text-slate-600">Không tìm thấy linh kiện nào phù hợp.</p>
                                <a href="products.php" class="text-xs text-red-600 hover:underline mt-1">Xóa bộ lọc</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): 
                            $imgUrl = MATHANG::layHinhAnh($p['hinhanh'], '../');
                            $ton = (int)$p['soluongton'];
                            $isNoiBat = !empty($p['noibat']);
                        ?>
                            <tr class="table-hover-row transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Product" class="w-12 h-12 rounded-md object-cover border border-slate-200 flex-shrink-0" onerror="this.onerror=null; this.src='../images/products/no-image.png';">
                                        <div class="min-w-0 max-w-[220px]">
                                            <div class="font-bold text-slate-900 text-sm truncate" title="<?= htmlspecialchars($p['tenmathang']) ?>">
                                                <?= htmlspecialchars($p['tenmathang']) ?>
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono">ID: #<?= (int)$p['id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-700 font-medium">
                                    <?= htmlspecialchars($p['tendanhmuc']) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if (!empty($p['dongmay'])): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold bg-orange-50 text-orange-700 border border-orange-200">
                                             <?= htmlspecialchars($p['dongmay']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 italic text-[11px]">Dùng chung</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="font-bold text-red-600 text-sm font-mono-num"><?= formatVND($p['giaban']) ?></div>
                                    <div class="text-[11px] text-slate-400 font-mono-num line-through"><?= formatVND($p['giagoc']) ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <?php if ($ton === 0): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-rose-100 text-rose-700">
                                            Hết hàng (0)
                                        </span>
                                    <?php elseif ($ton <= 5): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-amber-100 text-amber-800">
                                            Tồn ít (<?= $ton ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">
                                            <?= $ton ?> cái
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center text-slate-500 font-mono-num whitespace-nowrap">
                                    <div><?= number_format($p['luotmua']) ?> đã mua</div>
                                    <div class="text-[10px] text-slate-400"><?= number_format($p['luotxem']) ?> xem</div>
                                </td>
                                <!-- Cột Bật/Tắt Sản phẩm Nổi bật (1-click Ngôi sao) -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <button type="button" 
                                            onclick="toggleNoiBat(<?= (int)$p['id'] ?>, this)" 
                                            class="star-toggle-btn p-1.5 rounded-lg border transition inline-flex items-center justify-center <?= $isNoiBat ? 'bg-amber-50 border-amber-200 text-[#F59E0B]' : 'bg-slate-50 border-slate-200 text-slate-300 hover:text-slate-400' ?>" 
                                            title="<?= $isNoiBat ? 'Đang nổi bật (Bấm 1-click để tắt)' : 'Bình thường (Bấm 1-click để bật nổi bật)' ?>"
                                            data-id="<?= (int)$p['id'] ?>"
                                            data-noibat="<?= $isNoiBat ? '1' : '0' ?>">
                                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                        </svg>
                                    </button>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" onclick='openEditModal(<?= json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition" title="Chỉnh sửa linh kiện">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <?php if ($isSuper): ?>
                                            <button type="button" onclick="confirmDeleteProduct(<?= (int)$p['id'] ?>, '<?= htmlspecialchars(addslashes($p['tenmathang'])) ?>')" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Xóa linh kiện">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
            <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-500">
                    Trang <strong><?= $page ?></strong> / <strong><?= $totalPages ?></strong> (Tổng <?= number_format($totalProducts) ?> linh kiện)
                </span>
                <div class="flex items-center gap-1">
                    <?php for ($p = 1; $p <= $totalPages; $p++): 
                        $q = $_GET;
                        $q['page'] = $p;
                        $pageUrl = 'products.php?' . http_build_query($q);
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
     MODAL THÊM MỚI / CHỈNH SỬA SẢN PHẨM
     ========================================== -->
<div id="product-modal" class="modal-overlay modal-hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="modal-container bg-white w-full max-w-2xl rounded-3xl shadow-none border border-slate-100 overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header Modal -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 id="product-modal-title" class="text-base font-extrabold text-slate-900">Thêm linh kiện Drone mới</h3>
            <button type="button" onclick="closeModal('product-modal')" class="p-2 text-slate-400 hover:text-slate-600 rounded-md hover:bg-slate-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Form Body -->
        <form id="product-form" action="products.php" method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-6 space-y-4">
            <input type="hidden" name="action" id="product-form-action" value="create_product">
            <input type="hidden" name="product_id" id="product-form-id" value="">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tên linh kiện / Máy bay Drone XAG <span class="text-rose-500">*</span></label>
                <input type="text" name="tenmathang" id="input-tenmathang" required placeholder="Ví dụ: Cánh quạt Drone XAG P100 Pro (Cặp)" class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Danh mục linh kiện <span class="text-rose-500">*</span></label>
                    <select name="danhmuc_id" id="input-danhmuc-id" required class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['tendanhmuc']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Dòng máy tương thích (dongmay)</label>
                    <select name="dongmay" id="input-dongmay" class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                        <option value="">Dùng chung / Tất cả model</option>
                        <?php foreach ($allDongMay as $dm): ?>
                            <option value="<?= htmlspecialchars($dm['tendongmay']) ?>"><?= htmlspecialchars($dm['tendongmay']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Giá gốc nhập (VNĐ)</label>
                    <input type="number" name="giagoc" id="input-giagoc" min="0" step="1000" placeholder="0" class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Giá bán ra (VNĐ) <span class="text-rose-500">*</span></label>
                    <input type="number" name="giaban" id="input-giaban" required min="0" step="1000" placeholder="0" class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tồn kho ban đầu</label>
                    <input type="number" name="soluongton" id="input-soluongton" min="0" value="0" class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Mô tả thông số kỹ thuật</label>
                <textarea name="mota" id="input-mota" rows="3" placeholder="Thông số động cơ, sải cánh, điện áp, chuẩn chống nước IPX..." class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Hình ảnh sản phẩm (JPG, PNG, WEBP)</label>
                <div class="flex items-center gap-4 mt-1">
                    <img id="product-img-preview" src="../images/products/no-image.png" class="w-16 h-16 rounded-md object-cover border border-slate-200" alt="Preview">
                    <input type="file" name="hinhanh" accept="image/*" data-preview-target="product-img-preview" class="text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                </div>
            </div>

            <!-- Checkbox Sản phẩm nổi bật -->
            <div class="flex items-center gap-3 p-3 bg-amber-50/60 rounded-xl border border-amber-200/60">
                <input type="checkbox" name="noibat" id="input-noibat" value="1" class="w-4 h-4 text-amber-500 rounded border-slate-300 focus:ring-amber-400 focus:ring-offset-0 cursor-pointer">
                <label for="input-noibat" class="text-xs font-bold text-slate-800 cursor-pointer flex items-center gap-1.5 select-none">
                    <svg class="w-4 h-4 text-amber-500 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <span>Hiển thị ở mục Sản phẩm nổi bật trên Trang chủ</span>
                </label>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('product-modal')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-md transition">
                    Hủy bỏ
                </button>
                <button type="submit" id="product-modal-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white font-bold text-xs rounded-md transition shadow-none shadow-none/25">
                    Lưu linh kiện
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ==========================================
     MODAL QUẢN LÝ DÒNG MÁY DRONE (DONGMAY)
     ========================================== -->
<div id="dongmay-modal" class="modal-overlay modal-hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="modal-container bg-white w-full max-w-xl rounded-3xl shadow-none border border-slate-100 overflow-hidden flex flex-col max-h-[90vh]">
        
        <!-- Header Modal -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <h3 class="text-base font-extrabold text-slate-900">Quản lý Dòng máy Drone XAG</h3>
                <p class="text-xs text-slate-500 mt-0.5">Thêm mới, đổi tên hoặc loại bỏ các dòng máy tương thích</p>
            </div>
            <button type="button" onclick="closeModal('dongmay-modal')" class="p-2 text-slate-400 hover:text-slate-600 rounded-md hover:bg-slate-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="p-6 space-y-5 overflow-y-auto">
            <!-- Form Thêm mới Dòng máy -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                <label class="block text-xs font-bold text-slate-700 mb-2">Thêm dòng máy mới</label>
                <form action="products.php" method="POST" class="flex gap-2">
                    <input type="hidden" name="action" value="create_dongmay">
                    <input type="text" name="tendongmay" required placeholder="Ví dụ: XAG P150, XAG V50, XAG M500..." class="flex-1 text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 bg-white">
                    <button type="submit" class="px-4 py-2.5 bg-red-700 hover:bg-red-800 text-white font-bold text-xs rounded-md transition shadow-none whitespace-nowrap">
                        + Thêm
                    </button>
                </form>
            </div>

            <!-- Danh sách Dòng máy -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Danh sách dòng máy (<?= count($allDongMay) ?>)</span>
                    <span class="text-[11px] text-slate-400">Tự động đồng bộ sang linh kiện</span>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3">Tên dòng máy</th>
                                <th class="py-2.5 px-3 text-center">Linh kiện gắn</th>
                                <th class="py-2.5 px-3 text-right">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($allDongMay)): ?>
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-slate-400">Chưa có dòng máy nào trong hệ thống.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allDongMay as $dm): ?>
                                    <tr id="dm-row-<?= (int)$dm['id'] ?>" class="table-hover-row">
                                        <td class="py-2.5 px-3">
                                            <span class="font-bold text-slate-900"><?= htmlspecialchars($dm['tendongmay']) ?></span>
                                        </td>
                                        <td class="py-2.5 px-3 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold <?= $dm['tong_san_pham'] > 0 ? 'bg-orange-50 text-orange-700 border border-orange-200' : 'bg-slate-100 text-slate-500' ?>">
                                                <?= (int)$dm['tong_san_pham'] ?> linh kiện
                                            </span>
                                        </td>
                                        <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-1">
                                                <button type="button" onclick="startEditDongMay(<?= (int)$dm['id'] ?>)" class="px-2 py-1 text-xs text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded font-semibold transition" title="Đổi tên">
                                                    Sửa
                                                </button>
                                                <form action="products.php" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc muốn xóa dòng máy [<?= htmlspecialchars(addslashes($dm['tendongmay'])) ?>]? <?= $dm['tong_san_pham'] > 0 ? "Hiện có {$dm['tong_san_pham']} linh kiện đang gắn nhãn này sẽ được chuyển sang \'Dùng chung\'." : "" ?>');">
                                                    <input type="hidden" name="action" value="delete_dongmay">
                                                    <input type="hidden" name="dongmay_id" value="<?= (int)$dm['id'] ?>">
                                                    <button type="submit" class="px-2 py-1 text-xs text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 rounded font-semibold transition" title="Xóa dòng máy">
                                                        Xóa
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Hàng Sửa Dòng Máy (Ẩn mặc định) -->
                                    <tr id="dm-edit-row-<?= (int)$dm['id'] ?>" class="hidden bg-amber-50/60">
                                        <td colspan="3" class="p-2.5">
                                            <form action="products.php" method="POST" class="flex items-center gap-2">
                                                <input type="hidden" name="action" value="update_dongmay">
                                                <input type="hidden" name="dongmay_id" value="<?= (int)$dm['id'] ?>">
                                                <input type="text" name="tendongmay" value="<?= htmlspecialchars($dm['tendongmay']) ?>" required class="flex-1 text-xs px-2.5 py-1.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 bg-white">
                                                <button type="submit" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-md transition">
                                                    Lưu
                                                </button>
                                                <button type="button" onclick="cancelEditDongMay(<?= (int)$dm['id'] ?>)" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-semibold rounded-md transition">
                                                    Hủy
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-end">
            <button type="button" onclick="closeModal('dongmay-modal')" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-md transition">
                Đóng
            </button>
        </div>

    </div>
</div>

<!-- ==========================================
     MODAL XÁC NHẬN XÓA SẢN PHẨM (SUPER ADMIN)
     ========================================== -->
<div id="delete-modal" class="modal-overlay modal-hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="modal-container bg-white w-full max-w-md rounded-3xl shadow-none border border-slate-100 p-6 text-center">
        <div class="w-12 h-12 rounded-md bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <h3 class="text-base font-extrabold text-slate-900 mb-1">Xác nhận xóa linh kiện?</h3>
        <p class="text-xs text-slate-500 mb-4">
            Bạn có chắc chắn muốn xóa vĩnh viễn linh kiện <strong id="delete-product-name" class="text-slate-800"></strong>? Thao tác này không thể hoàn tác.
        </p>
        <form action="products.php" method="POST" class="flex items-center justify-center gap-2">
            <input type="hidden" name="action" value="delete_product">
            <input type="hidden" name="product_id" id="delete-product-id" value="">
            <button type="button" onclick="closeModal('delete-modal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-md transition">
                Hủy bỏ
            </button>
            <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-md transition shadow-none shadow-none/30">
                Xóa vĩnh viễn
            </button>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('product-modal-title').textContent = 'Thêm mới Linh kiện & Drone XAG';
    document.getElementById('product-form-action').value = 'create_product';
    document.getElementById('product-form-id').value = '';
    document.getElementById('product-form').reset();
    document.getElementById('input-noibat').checked = false;
    document.getElementById('product-img-preview').src = '../images/products/no-image.png';
    document.getElementById('product-modal-submit-btn').textContent = 'Thêm linh kiện';
    openModal('product-modal');
}

function openEditModal(prod) {
    document.getElementById('product-modal-title').textContent = 'Chỉnh sửa linh kiện #' + prod.id;
    document.getElementById('product-form-action').value = 'update_product';
    document.getElementById('product-form-id').value = prod.id;
    document.getElementById('input-tenmathang').value = prod.tenmathang || '';
    document.getElementById('input-danhmuc-id').value = prod.danhmuc_id || '';
    document.getElementById('input-dongmay').value = prod.dongmay || '';
    document.getElementById('input-giagoc').value = prod.giagoc || 0;
    document.getElementById('input-giaban').value = prod.giaban || 0;
    document.getElementById('input-soluongton').value = prod.soluongton || 0;
    document.getElementById('input-mota').value = prod.mota || '';
    document.getElementById('input-noibat').checked = (parseInt(prod.noibat) === 1);

    const imgPreview = document.getElementById('product-img-preview');
    if (prod.hinhanh) {
        imgPreview.src = prod.hinhanh.indexOf('images/') === 0 ? ('../' + prod.hinhanh) : ('../images/products/' + prod.hinhanh.split('/').pop());
    } else {
        imgPreview.src = '../images/products/no-image.png';
    }

    document.getElementById('product-modal-submit-btn').textContent = 'Lưu thay đổi';
    openModal('product-modal');
}

function toggleNoiBat(id, btn) {
    btn.disabled = true;
    btn.style.opacity = '0.5';

    const formData = new FormData();
    formData.append('action', 'toggle_noibat');
    formData.append('product_id', id);

    fetch('products.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.style.opacity = '1';
        if (data && data.success) {
            const isNoiBat = (parseInt(data.noibat) === 1);
            btn.setAttribute('data-noibat', isNoiBat ? '1' : '0');
            if (isNoiBat) {
                btn.className = 'star-toggle-btn p-1.5 rounded-lg border transition inline-flex items-center justify-center bg-amber-50 border-amber-200 text-[#F59E0B]';
                btn.title = 'Đang nổi bật (Bấm 1-click để tắt)';
            } else {
                btn.className = 'star-toggle-btn p-1.5 rounded-lg border transition inline-flex items-center justify-center bg-slate-50 border-slate-200 text-slate-300 hover:text-slate-400';
                btn.title = 'Bình thường (Bấm 1-click để bật nổi bật)';
            }
        } else {
            alert((data && data.error) ? data.error : 'Không thể cập nhật trạng thái nổi bật.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.style.opacity = '1';
        console.error(err);
        alert('Lỗi kết nối máy chủ khi đổi trạng thái nổi bật.');
    });
}

function confirmDeleteProduct(id, name) {
    document.getElementById('delete-product-id').value = id;
    document.getElementById('delete-product-name').textContent = name;
    openModal('delete-modal');
}

// Quản lý Modal Dòng máy Drone
function openDongMayModal() {
    openModal('dongmay-modal');
}

function startEditDongMay(id) {
    const viewRow = document.getElementById('dm-row-' + id);
    const editRow = document.getElementById('dm-edit-row-' + id);
    if (viewRow) viewRow.classList.add('hidden');
    if (editRow) editRow.classList.remove('hidden');
}

function cancelEditDongMay(id) {
    const viewRow = document.getElementById('dm-row-' + id);
    const editRow = document.getElementById('dm-edit-row-' + id);
    if (viewRow) viewRow.classList.remove('hidden');
    if (editRow) editRow.classList.add('hidden');
}

// Tự động mở modal Dòng máy nếu URL có ?open=dongmay
document.addEventListener('DOMContentLoaded', function() {
    if (new URLSearchParams(window.location.search).get('open') === 'dongmay') {
        openDongMayModal();
    }
});
</script>

<?php require_once __DIR__ . '/inc/layout_footer.php'; ?>
