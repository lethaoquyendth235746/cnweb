<?php
/**
 * admin/categories.php
 * Quản lý Danh mục Linh kiện Drone XAG - Giai đoạn 3
 * Tailwind CSS + CategoryAdminService + PRG Pattern
 */

$pageTitle = 'Danh mục Linh kiện';
require_once __DIR__ . '/includes/auth_middleware.php';
checkAdminAuth();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/services/CategoryAdminService.php';

$categoryService = new CategoryAdminService();

// ==========================================
// 1. XỬ LÝ FORM POST THEO CHUẨN PRG
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Thêm mới danh mục
    if ($action === 'create_category') {
        $name = trim($_POST['tendanhmuc'] ?? '');
        try {
            $newId = $categoryService->createCategory($name);
            setAdminFlash('success', "Đã thêm mới danh mục [{$name}] thành công (ID: #{$newId})!");
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi thêm danh mục: ' . $e->getMessage());
        }
        header('Location: categories.php');
        exit;
    }

    // Cập nhật danh mục
    if ($action === 'update_category') {
        $id = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['tendanhmuc'] ?? '');
        try {
            $categoryService->updateCategory($id, $name);
            setAdminFlash('success', "Đã cập nhật danh mục #{$id} thành [{$name}] thành công!");
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi cập nhật danh mục: ' . $e->getMessage());
        }
        header('Location: categories.php');
        exit;
    }

    // Xóa danh mục an toàn
    if ($action === 'delete_category') {
        $id = (int)($_POST['category_id'] ?? 0);
        try {
            $categoryService->deleteCategory($id);
            setAdminFlash('success', "Đã xóa danh mục #{$id} thành công!");
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Không thể xóa: ' . $e->getMessage());
        }
        header('Location: categories.php');
        exit;
    }
}

// ==========================================
// 2. LẤY DỮ LIỆU DANH MỤC
// ==========================================
$categories = $categoryService->getAllCategoriesWithCount();
$totalCategories = count($categories);
$totalProductsAll = array_sum(array_column($categories, 'tong_san_pham'));
$totalStockAll = array_sum(array_column($categories, 'tong_ton_kho'));

require_once __DIR__ . '/inc/layout_header.php';
?>

<div class="space-y-6">

    <!-- Tiêu đề trang -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Danh mục Linh kiện XAG</h1>
            <p class="text-sm text-slate-500 mt-1">
                Phân loại cấu trúc phụ tùng Drone (Động cơ, Cánh quạt, Pin thông minh, Radar, Mạch điều khiển...).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openCreateModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white text-xs font-bold rounded-xl transition shadow-sm shadow-red-600/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Thêm danh mục mới</span>
            </button>
        </div>
    </div>

    <!-- 3 THẺ TỔNG HỢP DANH MỤC -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tổng số phân loại</span>
                <div class="text-xl font-black text-slate-900 mt-1 font-mono-num"><?= $totalCategories ?> danh mục</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tổng mặt hàng</span>
                <div class="text-xl font-black text-red-600 mt-1 font-mono-num"><?= number_format($totalProductsAll) ?> linh kiện</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tổng số tồn kho</span>
                <div class="text-xl font-black text-blue-600 mt-1 font-mono-num"><?= number_format($totalStockAll) ?> cái</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </div>
        </div>
    </div>

    <!-- BẢNG DANH MỤC LINH KIỆN -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-5">ID</th>
                        <th class="py-3.5 px-5">Tên Danh Mục Linh Kiện</th>
                        <th class="py-3.5 px-5 text-center">Số Mặt Hàng Thuộc Danh Mục</th>
                        <th class="py-3.5 px-5 text-center">Tổng Tồn Kho</th>
                        <th class="py-3.5 px-5 text-right">Thao Tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                Chưa có danh mục nào trong hệ thống. Hãy tạo danh mục đầu tiên!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): 
                            $hasProducts = ((int)$cat['tong_san_pham'] > 0);
                        ?>
                            <tr class="table-hover-row transition-colors">
                                <td class="py-3.5 px-5 font-mono font-bold text-slate-900">
                                    #<?= (int)$cat['id'] ?>
                                </td>
                                <td class="py-3.5 px-5">
                                    <div class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                        <span><?= htmlspecialchars($cat['tendanhmuc']) ?></span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-5 text-center">
                                    <a href="products.php?danhmuc_id=<?= (int)$cat['id'] ?>" class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold <?= $hasProducts ? 'bg-red-50 text-red-700 hover:bg-red-100' : 'bg-slate-100 text-slate-500' ?> transition">
                                        <span><?= (int)$cat['tong_san_pham'] ?> sản phẩm</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </td>
                                <td class="py-3.5 px-5 text-center font-bold text-slate-800 font-mono-num">
                                    <?= number_format($cat['tong_ton_kho']) ?> cái
                                </td>
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" onclick="openEditModal(<?= (int)$cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['tendanhmuc'])) ?>')" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition" title="Sửa tên danh mục">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button type="button" onclick="confirmDeleteCategory(<?= (int)$cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['tendanhmuc'])) ?>', <?= (int)$cat['tong_san_pham'] ?>)" class="p-1.5 <?= $hasProducts ? 'bg-slate-50 text-slate-300 cursor-not-allowed' : 'bg-rose-50 hover:bg-rose-100 text-rose-600' ?> rounded-lg transition" title="<?= $hasProducts ? 'Không thể xóa danh mục đang có sản phẩm' : 'Xóa danh mục' ?>">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ==========================================
     MODAL THÊM / SỬA DANH MỤC
     ========================================== -->
<div id="category-modal" class="modal-overlay modal-hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="modal-container bg-white w-full max-w-md rounded-3xl shadow-2xl border border-slate-100 overflow-hidden flex flex-col">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 id="category-modal-title" class="text-base font-extrabold text-slate-900">Thêm danh mục linh kiện</h3>
            <button type="button" onclick="closeModal('category-modal')" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <form id="category-form" action="categories.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" id="category-form-action" value="create_category">
            <input type="hidden" name="category_id" id="category-form-id" value="">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tên danh mục linh kiện <span class="text-rose-500">*</span></label>
                <input type="text" name="tendanhmuc" id="input-tendanhmuc" required placeholder="Ví dụ: Cánh quạt & Động cơ Brushless" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('category-modal')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                    Hủy bỏ
                </button>
                <button type="submit" id="category-modal-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white font-bold text-xs rounded-xl transition shadow-md shadow-red-600/25">
                    Lưu danh mục
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
     MODAL XÁC NHẬN XÓA DANH MỤC
     ========================================== -->
<div id="delete-category-modal" class="modal-overlay modal-hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="modal-container bg-white w-full max-w-md rounded-3xl shadow-2xl border border-slate-100 p-6 text-center">
        <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <h3 class="text-base font-extrabold text-slate-900 mb-1">Xác nhận xóa danh mục?</h3>
        <p id="delete-cat-message" class="text-xs text-slate-500 mb-4"></p>
        <form id="delete-cat-form" action="categories.php" method="POST" class="flex items-center justify-center gap-2">
            <input type="hidden" name="action" value="delete_category">
            <input type="hidden" name="category_id" id="delete-cat-id" value="">
            <button type="button" onclick="closeModal('delete-category-modal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                Hủy bỏ
            </button>
            <button type="submit" id="btn-confirm-delete-cat" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl transition shadow-md shadow-rose-600/30">
                Xác nhận xóa
            </button>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('category-modal-title').textContent = 'Thêm danh mục linh kiện mới';
    document.getElementById('category-form-action').value = 'create_category';
    document.getElementById('category-form-id').value = '';
    document.getElementById('input-tendanhmuc').value = '';
    document.getElementById('category-modal-submit-btn').textContent = 'Thêm danh mục';
    openModal('category-modal');
}

function openEditModal(id, name) {
    document.getElementById('category-modal-title').textContent = 'Chỉnh sửa danh mục #' + id;
    document.getElementById('category-form-action').value = 'update_category';
    document.getElementById('category-form-id').value = id;
    document.getElementById('input-tendanhmuc').value = name;
    document.getElementById('category-modal-submit-btn').textContent = 'Lưu thay đổi';
    openModal('category-modal');
}

function confirmDeleteCategory(id, name, count) {
    if (count > 0) {
        alert('Không thể xóa danh mục "' + name + '" vì đang chứa ' + count + ' linh kiện. Vui lòng chuyển hoặc xóa các linh kiện trước!');
        return;
    }
    document.getElementById('delete-cat-id').value = id;
    document.getElementById('delete-cat-message').innerHTML = 'Bạn có chắc chắn muốn xóa danh mục <strong class="text-slate-800">' + name + '</strong> không?';
    openModal('delete-category-modal');
}
</script>

<?php require_once __DIR__ . '/inc/layout_footer.php'; ?>
