<?php
/**
 * admin/users.php
 * Quản trị Nhân sự Nội bộ (SuperAdmin & Kỹ thuật viên XAG) - Giai đoạn 3
 * Tailwind CSS + UserAdminService + PRG Pattern
 */

$pageTitle = 'Nhân sự & Phân quyền';
require_once __DIR__ . '/includes/auth_middleware.php';
checkAdminAuth();
requireSuperAdmin('index.php'); // Chỉ SuperAdmin mới được truy cập trang quản lý nhân sự

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/services/UserAdminService.php';

$userService = new UserAdminService();
$currentUserId = getCurrentUserId();

// ==========================================
// 1. XỬ LÝ FORM POST THEO CHUẨN PRG
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Tạo tài khoản nhân sự mới
    if ($action === 'create_user') {
        try {
            $data = [
                'email'       => $_POST['email'] ?? '',
                'matkhau'     => $_POST['matkhau'] ?? '',
                'hoten'       => $_POST['hoten'] ?? '',
                'sodienthoai' => $_POST['sodienthoai'] ?? '',
                'loai'        => (int)($_POST['loai'] ?? 2)
            ];
            $newId = $userService->createUser($data);
            setAdminFlash('success', "Đã tạo tài khoản nhân sự cho [{$data['hoten']}] thành công (ID: #{$newId})!");
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi tạo nhân sự: ' . $e->getMessage());
        }
        header('Location: users.php');
        exit;
    }

    // Cập nhật nhân sự
    if ($action === 'update_user') {
        $id = (int)($_POST['user_id'] ?? 0);
        try {
            $data = [
                'hoten'       => $_POST['hoten'] ?? '',
                'sodienthoai' => $_POST['sodienthoai'] ?? '',
                'loai'        => (int)($_POST['loai'] ?? 2)
            ];
            $userService->updateUser($id, $data);
            setAdminFlash('success', "Đã cập nhật thông tin nhân sự #{$id} thành công!");
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi cập nhật: ' . $e->getMessage());
        }
        header('Location: users.php');
        exit;
    }

    // Đổi trạng thái (Khóa / Mở khóa)
    if ($action === 'toggle_status') {
        $id = (int)($_POST['user_id'] ?? 0);
        $newStatus = (int)($_POST['new_status'] ?? 0);
        try {
            $userService->toggleUserStatus($id, $newStatus, $currentUserId);
            $msg = ($newStatus === 1) ? "Đã mở khóa tài khoản #{$id}." : "Đã khóa tài khoản #{$id}.";
            setAdminFlash('success', $msg);
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi thao tác: ' . $e->getMessage());
        }
        header('Location: users.php');
        exit;
    }

    // Đặt lại mật khẩu nhân viên
    if ($action === 'reset_password') {
        $id = (int)($_POST['user_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        try {
            $userService->resetPassword($id, $newPassword);
            setAdminFlash('success', "Đã đặt lại mật khẩu mới cho tài khoản #{$id} thành công!");
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi đổi mật khẩu: ' . $e->getMessage());
        }
        header('Location: users.php');
        exit;
    }
}

// ==========================================
// 2. LẤY DANH SÁCH NHÂN SỰ
// ==========================================
$roleFilter = (isset($_GET['loai']) && $_GET['loai'] !== '') ? (int)$_GET['loai'] : null;
$statusFilter = (isset($_GET['trangthai']) && $_GET['trangthai'] !== '') ? (int)$_GET['trangthai'] : null;
$search = trim($_GET['search'] ?? '');

$filters = [
    'loai'      => $roleFilter,
    'trangthai' => $statusFilter,
    'search'    => $search
];

$users = $userService->getAllInternalUsers($filters);
$totalUsers = count($users);

require_once __DIR__ . '/inc/layout_header.php';
?>

<div class="space-y-6">

    <!-- Tiêu đề trang & Nút thêm mới -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Nhân sự & Phân quyền Hệ thống</h1>
            <p class="text-sm text-slate-500 mt-1">
                Quản lý tài khoản Quản trị viên cấp cao (SuperAdmin) và Đội ngũ Kỹ thuật viên Drone XAG.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openCreateUserModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white text-xs font-bold rounded-md transition shadow-none shadow-none/25">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                <span>Thêm nhân sự mới</span>
            </button>
        </div>
    </div>

    <!-- THANH TÌM KIẾM & BỘ LỌC -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-none">
        <form action="users.php" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tìm kiếm nhân viên</label>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tên, email hoặc số điện thoại..." class="w-full px-3 py-2 text-xs rounded-md bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Vai trò quyền hạn</label>
                <select name="loai" class="w-full px-3 py-2 text-xs rounded-md bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    <option value="">-- Tất cả vai trò --</option>
                    <option value="1" <?= ($roleFilter === 1) ? 'selected' : '' ?>>Quản trị viên (SuperAdmin)</option>
                    <option value="2" <?= ($roleFilter === 2) ? 'selected' : '' ?>>Kỹ thuật viên / Nhân viên</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-md transition shadow-none">
                    Lọc nhân sự
                </button>
                <a href="users.php" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-md transition" title="Xóa bộ lọc">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </a>
            </div>
        </form>
    </div>

    <!-- BẢNG DANH SÁCH NHÂN SỰ -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-none overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Nhân sự</th>
                        <th class="py-3.5 px-4">Liên hệ (Email & SĐT)</th>
                        <th class="py-3.5 px-4 text-center">Vai trò quyền hạn</th>
                        <th class="py-3.5 px-4 text-center">Trạng thái</th>
                        <th class="py-3.5 px-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                Không tìm thấy nhân sự nào khớp điều kiện.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): 
                            $isSelf = ((int)$u['id'] === $currentUserId);
                            $isActive = ((int)$u['trangthai'] === 1);
                            $avatarUrl = !empty($u['hinhanh']) ? '../images/users/' . basename($u['hinhanh']) : '../images/users/user.png';
                        ?>
                            <tr class="table-hover-row transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="w-10 h-10 rounded-md object-cover border border-slate-200" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($u['hoten']) ?>&background=dc2626&color=fff';">
                                        <div>
                                            <div class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                                <span><?= htmlspecialchars($u['hoten']) ?></span>
                                                <?php if ($isSelf): ?>
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-orange-100 text-orange-800">Bạn</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono">ID: #<?= (int)$u['id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800"><?= htmlspecialchars($u['email']) ?></div>
                                    <div class="text-slate-500 font-mono-num text-[11px] mt-0.5"><?= htmlspecialchars($u['sodienthoai'] ?: 'Chưa có SĐT') ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold <?= $u['role_badge_class'] ?>">
                                        <?= htmlspecialchars($u['role_label']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold <?= $u['status_badge_class'] ?>">
                                        <?= htmlspecialchars($u['status_label']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" onclick='openEditUserModal(<?= json_encode($u, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)' class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition" title="Sửa thông tin nhân sự">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button type="button" onclick="openResetPasswordModal(<?= (int)$u['id'] ?>, '<?= htmlspecialchars(addslashes($u['hoten'])) ?>')" class="p-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg transition" title="Đặt lại mật khẩu">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                        </button>
                                        <?php if (!$isSelf): ?>
                                            <form action="users.php" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc muốn <?= $isActive ? 'KHÓA' : 'MỞ KHÓA' ?> tài khoản nhân sự này?');">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                                <input type="hidden" name="new_status" value="<?= $isActive ? 0 : 1 ?>">
                                                <button type="submit" class="p-1.5 <?= $isActive ? 'bg-rose-50 hover:bg-rose-100 text-rose-600' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700' ?> rounded-lg transition" title="<?= $isActive ? 'Khóa tài khoản' : 'Mở khóa' ?>">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                </button>
                                            </form>
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

</div>

<!-- ==========================================
     MODAL THÊM / SỬA NHÂN SỰ
     ========================================== -->
<div id="user-modal" class="modal-overlay modal-hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="modal-container bg-white w-full max-w-lg rounded-3xl border border-slate-100 overflow-hidden flex flex-col">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 id="user-modal-title" class="text-base font-extrabold text-slate-900">Thêm nhân sự mới</h3>
            <button type="button" onclick="closeModal('user-modal')" class="p-2 text-slate-400 hover:text-slate-600 rounded-md hover:bg-slate-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="user-form" action="users.php" method="POST" class="p-6 space-y-4">
            <input type="hidden" name="action" id="user-form-action" value="create_user">
            <input type="hidden" name="user_id" id="user-form-id" value="">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Họ và tên nhân sự <span class="text-rose-500">*</span></label>
                <input type="text" name="hoten" id="input-u-hoten" required placeholder="Nguyễn Văn A" class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div id="wrapper-email">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email đăng nhập <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" id="input-u-email" required placeholder="kythuat@xag.vn" class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Số điện thoại <span class="text-rose-500">*</span></label>
                    <input type="text" name="sodienthoai" id="input-u-sdt" required placeholder="0987xxxxxx" class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                </div>
            </div>

            <div id="wrapper-password">
                <label class="block text-xs font-bold text-slate-700 mb-1">Mật khẩu ban đầu <span class="text-rose-500">*</span></label>
                <input type="password" name="matkhau" id="input-u-pass" placeholder="Tối thiểu 6 ký tự" class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Vai trò quyền hạn <span class="text-rose-500">*</span></label>
                <select name="loai" id="input-u-loai" required class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    <option value="2">Kỹ thuật viên / Nhân viên (Xử lý đơn, quản lý kho)</option>
                    <option value="1">Quản trị viên (SuperAdmin - Toàn quyền hệ thống)</option>
                </select>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('user-modal')" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-md transition">
                    Hủy bỏ
                </button>
                <button type="submit" id="user-modal-submit-btn" class="px-5 py-2.5 bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white font-bold text-xs rounded-md transition shadow-none shadow-none/25">
                    Lưu nhân sự
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ==========================================
     MODAL ĐẶT LẠI MẬT KHẨU
     ========================================== -->
<div id="reset-password-modal" class="modal-overlay modal-hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="modal-container bg-white w-full max-w-md rounded-3xl border border-slate-100 p-6">
        <h3 class="text-base font-extrabold text-slate-900 mb-1">Đặt lại mật khẩu nhân viên</h3>
        <p class="text-xs text-slate-500 mb-4">
            Đang đặt mật khẩu mới cho nhân sự <strong id="reset-user-name" class="text-slate-800"></strong>.
        </p>
        <form action="users.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="user_id" id="reset-user-id" value="">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Mật khẩu mới (Tối thiểu 6 ký tự)</label>
                <input type="password" name="new_password" required minlength="6" placeholder="Nhập mật khẩu mới..." class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeModal('reset-password-modal')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-md transition">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-md transition shadow-none shadow-none/30">
                    Cập nhật mật khẩu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateUserModal() {
    document.getElementById('user-modal-title').textContent = 'Thêm nhân sự mới';
    document.getElementById('user-form-action').value = 'create_user';
    document.getElementById('user-form-id').value = '';
    document.getElementById('user-form').reset();
    document.getElementById('wrapper-email').style.display = 'block';
    document.getElementById('input-u-email').required = true;
    document.getElementById('wrapper-password').style.display = 'block';
    document.getElementById('input-u-pass').required = true;
    document.getElementById('user-modal-submit-btn').textContent = 'Tạo tài khoản';
    openModal('user-modal');
}

function openEditUserModal(u) {
    document.getElementById('user-modal-title').textContent = 'Chỉnh sửa nhân sự #' + u.id;
    document.getElementById('user-form-action').value = 'update_user';
    document.getElementById('user-form-id').value = u.id;
    document.getElementById('input-u-hoten').value = u.hoten || '';
    document.getElementById('input-u-sdt').value = u.sodienthoai || '';
    document.getElementById('input-u-loai').value = u.loai || 2;

    // Khi sửa không sửa trực tiếp email & pass (dùng modal reset pass riêng)
    document.getElementById('wrapper-email').style.display = 'none';
    document.getElementById('input-u-email').required = false;
    document.getElementById('wrapper-password').style.display = 'none';
    document.getElementById('input-u-pass').required = false;

    document.getElementById('user-modal-submit-btn').textContent = 'Lưu thay đổi';
    openModal('user-modal');
}

function openResetPasswordModal(id, name) {
    document.getElementById('reset-user-id').value = id;
    document.getElementById('reset-user-name').textContent = name;
    openModal('reset-password-modal');
}
</script>

<?php require_once __DIR__ . '/inc/layout_footer.php'; ?>
