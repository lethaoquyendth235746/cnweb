<?php
/**
 * admin/contacts.php
 * Quản trị Yêu cầu Tư vấn & Liên hệ Kỹ thuật - LPT DRONE / XAG Store
 * Giai đoạn 3: Phẳng lì, Tông Đỏ trầm (#B91C1C) - Đen than chì (#111315) + PRG Pattern
 */

$pageTitle = 'Yêu cầu Tư vấn & Liên hệ Kỹ thuật';
require_once __DIR__ . '/includes/auth_middleware.php';
checkAdminAuth();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/services/ContactAdminService.php';

$contactService = new ContactAdminService();

// ==========================================
// 1. XỬ LÝ FORM POST THEO CHUẨN PRG
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Cập nhật trạng thái & ghi chú admin
    if ($action === 'update_status') {
        $id = (int)($_POST['contact_id'] ?? 0);
        $newStatus = (int)($_POST['trangthai'] ?? 0);
        $ghichu = trim($_POST['ghichu_admin'] ?? '');

        try {
            $contactService->updateStatus($id, $newStatus, $ghichu);
            $statusLabel = ($newStatus === 1) ? 'Đã xử lý' : 'Chờ tư vấn';
            setAdminFlash('success', "Đã cập nhật yêu cầu #{$id} sang trạng thái: [{$statusLabel}].");
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi cập nhật: ' . $e->getMessage());
        }

        header('Location: contacts.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
        exit;
    }

    // Xóa yêu cầu liên hệ
    if ($action === 'delete') {
        $id = (int)($_POST['contact_id'] ?? 0);

        try {
            $contactService->deleteContact($id);
            setAdminFlash('success', "Đã xóa yêu cầu tư vấn #{$id} thành công.");
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi khi xóa: ' . $e->getMessage());
        }

        header('Location: contacts.php');
        exit;
    }
}

// ==========================================
// 2. LẤY DANH SÁCH & BỘ LỌC
// ==========================================
$search = trim($_GET['search'] ?? '');
$trangthai = (isset($_GET['trangthai']) && $_GET['trangthai'] !== '') ? (int)$_GET['trangthai'] : null;
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

$filters = [
    'search'    => $search,
    'trangthai' => $trangthai,
    'limit'     => $limit,
    'offset'    => $offset
];

$data = $contactService->getAllContacts($filters);
$contacts = $data['contacts'];
$totalContacts = $data['total'];
$totalPages = (int)ceil($totalContacts / $limit);

$stats = $contactService->getContactStats();

require_once __DIR__ . '/inc/layout_header.php';
?>

<div class="space-y-6">

    <!-- TIÊU ĐỀ TRANG & THỐNG KÊ NHANH -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-[#111315] tracking-tight">Tư vấn Kỹ thuật & Khách hàng Liên hệ</h1>
            <p class="text-sm text-slate-500 mt-1">
                Tiếp nhận yêu cầu tìm mã phụ tùng linh kiện, chuẩn đoán sự cố kỹ thuật và gọi lại tư vấn cho bà con nông dân, phi công XAG.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold px-3 py-1.5 bg-slate-100 text-slate-700 rounded-md border border-slate-200">
                Tổng cộng: <strong class="text-[#111315] font-mono-num"><?= number_format($stats['total']) ?></strong> yêu cầu
            </span>
        </div>
    </div>

    <!-- 3 THẺ KPI THỐNG KÊ (THIẾT KẾ PHẲNG LÌ, TÔNG ĐỎ TRẦM - ĐEN THAN CHÌ) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Tổng yêu cầu -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng số lượt gửi</p>
                <h3 class="text-2xl font-black text-[#111315] mt-1 font-mono-num"><?= number_format($stats['total']) ?></h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-700 border border-slate-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            </div>
        </div>

        <!-- Chờ tư vấn -->
        <div class="bg-white rounded-xl border border-red-200 p-4 flex items-center justify-between bg-gradient-to-r from-red-50/40 to-white">
            <div>
                <p class="text-xs font-bold text-red-700 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-red-600 animate-ping"></span>
                    Chờ gọi lại tư vấn
                </p>
                <h3 class="text-2xl font-black text-red-700 mt-1 font-mono-num"><?= number_format($stats['pending']) ?></h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-red-100/80 flex items-center justify-center text-red-700 border border-red-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Đã xử lý -->
        <div class="bg-white rounded-xl border border-slate-200/90 p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Đã hỗ trợ / Xử lý xong</p>
                <h3 class="text-2xl font-black text-emerald-700 mt-1 font-mono-num"><?= number_format($stats['processed']) ?></h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-700 border border-emerald-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
        </div>
    </div>

    <!-- THANH TÌM KIẾM & BỘ LỌC -->
    <div class="bg-white rounded-xl border border-slate-200/90 p-4 shadow-none">
        <form action="contacts.php" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tìm kiếm yêu cầu</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tên khách, SĐT, dòng máy, sự cố..." class="w-full pl-9 pr-3 py-2 text-xs rounded-md bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 focus:bg-white transition">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Trạng thái xử lý</label>
                <select name="trangthai" class="w-full px-3 py-2 text-xs rounded-md bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 focus:bg-white transition">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="0" <?= ($trangthai === 0) ? 'selected' : '' ?>>Chờ tư vấn</option>
                    <option value="1" <?= ($trangthai === 1) ? 'selected' : '' ?>>Đã xử lý</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-red-700 hover:bg-red-800 text-white text-xs font-bold rounded-md transition shadow-none flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span>Lọc danh sách</span>
                </button>
                <a href="contacts.php" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-md transition border border-slate-200" title="Xóa bộ lọc">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </a>
            </div>
        </form>
    </div>

    <!-- BẢNG DANH SÁCH LIÊN HỆ -->
    <div class="bg-white rounded-xl border border-slate-200/90 shadow-none overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 uppercase text-[10px] tracking-wider border-b border-slate-200 font-bold">
                        <th class="py-3 px-4 w-12 text-center">#</th>
                        <th class="py-3 px-4 min-w-[170px]">Khách hàng</th>
                        <th class="py-3 px-4 min-w-[140px]">Số điện thoại</th>
                        <th class="py-3 px-4 min-w-[120px]">Dòng máy</th>
                        <th class="py-3 px-4 min-w-[280px]">Nội dung yêu cầu / Sự cố</th>
                        <th class="py-3 px-4 min-w-[110px] text-center">Trạng thái</th>
                        <th class="py-3 px-4 min-w-[180px]">Ghi chú kỹ thuật</th>
                        <th class="py-3 px-4 w-28 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($contacts)): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="text-sm font-semibold text-slate-600">Không có yêu cầu tư vấn nào phù hợp</p>
                                <p class="text-xs text-slate-400 mt-1">Hãy thử xóa bộ lọc tìm kiếm hoặc kiểm tra lại.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($contacts as $c): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors <?= ($c['trangthai'] == 0) ? 'bg-red-50/20' : '' ?>">
                                <td class="py-3 px-4 text-center font-mono text-slate-400 font-bold">
                                    #<?= $c['id'] ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-[#111315] text-[13px]"><?= htmlspecialchars($c['hoten']) ?></div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <?= formatDateVN($c['ngaygui'], true) ?>
                                    </div>
                                    <?php if (!empty($c['nguoidung_id'])): ?>
                                        <span class="inline-block mt-1 px-1.5 py-0.5 text-[9px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded">
                                            Thành viên (ID #<?= $c['nguoidung_id'] ?>)
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-block mt-1 px-1.5 py-0.5 text-[9px] font-semibold bg-slate-100 text-slate-600 rounded">
                                            Khách vãng lai
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-slate-800 font-mono"><?= htmlspecialchars($c['sodienthoai']) ?></span>
                                    </div>
                                    <a href="tel:<?= htmlspecialchars($c['sodienthoai']) ?>" class="inline-flex items-center gap-1 text-[11px] text-red-700 hover:text-red-800 font-semibold mt-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                        Gọi ngay
                                    </a>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if (!empty($c['dongmay'])): ?>
                                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-[#111315] border border-slate-200">
                                            <?= htmlspecialchars($c['dongmay']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400">---</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="text-slate-800 leading-relaxed whitespace-pre-wrap max-w-sm font-medium">
                                        <?= htmlspecialchars($c['noidung']) ?>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($c['trangthai'] == 0): ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-red-100 text-red-700 border border-red-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-pulse"></span>
                                            Chờ tư vấn
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Đã xử lý
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if (!empty($c['ghichu_admin'])): ?>
                                        <div class="text-[11px] text-slate-700 bg-amber-50/70 p-2 rounded border border-amber-200/80 leading-normal">
                                            <?= htmlspecialchars($c['ghichu_admin']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-[11px] italic">Chưa có ghi chú</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Nút Xử lý / Cập nhật -->
                                        <button type="button" 
                                                class="btn-open-edit p-1.5 text-slate-700 hover:text-white bg-slate-100 hover:bg-red-700 rounded transition border border-slate-200"
                                                title="Cập nhật trạng thái & ghi chú"
                                                data-id="<?= $c['id'] ?>"
                                                data-hoten="<?= htmlspecialchars($c['hoten']) ?>"
                                                data-sdt="<?= htmlspecialchars($c['sodienthoai']) ?>"
                                                data-dongmay="<?= htmlspecialchars($c['dongmay'] ?? '') ?>"
                                                data-noidung="<?= htmlspecialchars($c['noidung']) ?>"
                                                data-trangthai="<?= $c['trangthai'] ?>"
                                                data-ghichu="<?= htmlspecialchars($c['ghichu_admin'] ?? '') ?>">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>

                                        <!-- Nút Xóa -->
                                        <form action="contacts.php" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa yêu cầu tư vấn #<?= $c['id'] ?> không?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="contact_id" value="<?= $c['id'] ?>">
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded transition" title="Xóa yêu cầu">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PHÂN TRANG (PAGINATION) -->
        <?php if ($totalPages > 1): ?>
            <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                <div class="text-xs text-slate-500">
                    Trang <strong class="text-slate-800"><?= $page ?></strong> / <?= $totalPages ?> (Tổng <?= number_format($totalContacts) ?> yêu cầu)
                </div>
                <div class="flex items-center gap-1">
                    <?php if ($page > 1): ?>
                        <a href="contacts.php?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&trangthai=<?= $trangthai ?>" class="px-2.5 py-1 text-xs font-semibold rounded bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 transition">Trước</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                            <a href="contacts.php?page=<?= $i ?>&search=<?= urlencode($search) ?>&trangthai=<?= $trangthai ?>" class="px-2.5 py-1 text-xs font-semibold rounded <?= ($i === $page) ? 'bg-red-700 text-white border border-red-700' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100' ?> transition">
                                <?= $i ?>
                            </a>
                        <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                            <span class="px-1 text-slate-400">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="contacts.php?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&trangthai=<?= $trangthai ?>" class="px-2.5 py-1 text-xs font-semibold rounded bg-white border border-slate-200 text-slate-600 hover:bg-slate-100 transition">Sau</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ==========================================
     MODAL CẬP NHẬT TRẠNG THÁI & GHI CHÚ
     ========================================== -->
<div id="contact-edit-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop mờ -->
    <div class="fixed inset-0 bg-slate-900/60 transition-opacity" id="modal-backdrop"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200">
            <!-- Header Modal -->
            <div class="bg-[#111315] px-5 py-4 flex items-center justify-between border-b border-zinc-800">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-md bg-red-700/20 text-red-500 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <h3 class="text-sm font-bold text-white tracking-wide uppercase" id="modal-title">
                        Xử lý Tư vấn Yêu cầu #<span id="modal-contact-id-display"></span>
                    </h3>
                </div>
                <button type="button" id="btn-close-modal" class="text-zinc-400 hover:text-white p-1 rounded-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Form xử lý -->
            <form action="contacts.php" method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="contact_id" id="modal-contact-id" value="">

                <div class="px-6 py-5 space-y-4">
                    <!-- Tóm tắt thông tin khách -->
                    <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs space-y-1">
                        <div><strong class="text-slate-600">Khách hàng:</strong> <span id="modal-hoten" class="font-bold text-[#111315]"></span> (<span id="modal-sdt" class="font-mono text-red-700 font-bold"></span>)</div>
                        <div><strong class="text-slate-600">Dòng máy:</strong> <span id="modal-dongmay" class="font-semibold text-slate-800"></span></div>
                        <div class="pt-1 text-slate-700 italic border-t border-slate-200 mt-1" id="modal-noidung"></div>
                    </div>

                    <!-- Chọn trạng thái -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Trạng thái xử lý <span class="text-red-600">*</span>
                        </label>
                        <select name="trangthai" id="modal-trangthai" class="w-full px-3 py-2 text-xs rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 bg-white">
                            <option value="0">Chờ tư vấn (Chưa liên hệ)</option>
                            <option value="1">Đã xử lý (Đã gọi tư vấn / báo giá hoàn tất)</option>
                        </select>
                    </div>

                    <!-- Ghi chú của Admin / Kỹ thuật viên -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Ghi chú kỹ thuật viên / Admin
                        </label>
                        <textarea name="ghichu_admin" id="modal-ghichu" rows="4" placeholder="Ví dụ: Đã gọi lúc 9h30 báo giá cánh P100 Pro, khách hẹn chiều chốt đơn..." class="w-full px-3 py-2 text-xs rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 bg-white"></textarea>
                        <p class="text-[11px] text-slate-400 mt-1">Ghi chú này hiển thị nội bộ để các kỹ thuật viên cùng theo dõi tiến độ xử lý.</p>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="bg-slate-50 px-6 py-3 flex items-center justify-end gap-2 border-t border-slate-200">
                    <button type="button" id="btn-cancel-modal" class="px-4 py-2 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-md hover:bg-slate-100 transition">
                        Đóng
                    </button>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-red-700 hover:bg-red-800 rounded-md transition shadow-none flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        <span>Lưu thay đổi</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('contact-edit-modal');
    const backdrop = document.getElementById('modal-backdrop');
    const btnClose = document.getElementById('btn-close-modal');
    const btnCancel = document.getElementById('btn-cancel-modal');

    function closeModal() {
        modal.classList.add('hidden');
    }

    if (backdrop) backdrop.addEventListener('click', closeModal);
    if (btnClose) btnClose.addEventListener('click', closeModal);
    if (btnCancel) btnCancel.addEventListener('click', closeModal);

    // Bắt sự kiện click mở modal trên các nút sửa
    document.querySelectorAll('.btn-open-edit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const hoten = this.getAttribute('data-hoten');
            const sdt = this.getAttribute('data-sdt');
            const dongmay = this.getAttribute('data-dongmay') || '---';
            const noidung = this.getAttribute('data-noidung');
            const trangthai = this.getAttribute('data-trangthai');
            const ghichu = this.getAttribute('data-ghichu');

            document.getElementById('modal-contact-id-display').textContent = id;
            document.getElementById('modal-contact-id').value = id;
            document.getElementById('modal-hoten').textContent = hoten;
            document.getElementById('modal-sdt').textContent = sdt;
            document.getElementById('modal-dongmay').textContent = dongmay;
            document.getElementById('modal-noidung').textContent = noidung;
            document.getElementById('modal-trangthai').value = trangthai;
            document.getElementById('modal-ghichu').value = ghichu;

            modal.classList.remove('hidden');
        });
    });
});
</script>

<?php
require_once __DIR__ . '/inc/layout_footer.php';
?>
