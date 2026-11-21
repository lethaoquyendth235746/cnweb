<?php
/**
 * admin/profile.php
 * Hồ sơ cá nhân & Đổi mật khẩu - XAG Store
 * Giai đoạn 3: Tailwind CSS + UserAdminService + PRG Pattern
 */

$pageTitle = 'Hồ sơ cá nhân & Đổi mật khẩu';
require_once __DIR__ . '/includes/auth_middleware.php';
checkAdminAuth();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/services/UserAdminService.php';

$userService = new UserAdminService();
$currentUserId = getCurrentUserId();
$currentUser = $userService->getUserById($currentUserId);

// ==========================================
// 1. XỬ LÝ FORM POST THEO CHUẨN PRG
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Cập nhật thông tin cá nhân & Upload Avatar
    if ($action === 'update_profile') {
        $hoten = trim($_POST['hoten'] ?? '');
        $sodt = trim($_POST['sodienthoai'] ?? '');
        $hinhanh = $currentUser['hinhanh'] ?? 'images/users/user.png';

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $targetDir = __DIR__ . '/../images/users/';
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0755, true);
                }
                $filename = 'user_' . $currentUserId . '_' . time() . '.' . $ext;
                $targetPath = $targetDir . $filename;
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
                    $hinhanh = 'images/users/' . $filename;
                }
            }
        }

        try {
            $pdo = DatabaseService::pdo();
            $stmt = $pdo->prepare("UPDATE nguoidung SET hoten = :hoten, sodienthoai = :sodt, hinhanh = :hinhanh WHERE id = :id");
            $stmt->bindValue(':hoten', $hoten);
            $stmt->bindValue(':sodt', $sodt);
            $stmt->bindValue(':hinhanh', $hinhanh);
            $stmt->bindValue(':id', $currentUserId, PDO::PARAM_INT);
            $stmt->execute();

            // Cập nhật lại Session
            $_SESSION['nguoidung']['hoten'] = $hoten;
            $_SESSION['nguoidung']['sodienthoai'] = $sodt;
            $_SESSION['nguoidung']['hinhanh'] = $hinhanh;

            setAdminFlash('success', 'Đã cập nhật hồ sơ cá nhân thành công!');
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi cập nhật hồ sơ: ' . $e->getMessage());
        }

        header('Location: profile.php');
        exit;
    }

    // Đổi mật khẩu cá nhân
    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        try {
            if ($newPassword !== $confirmPassword) {
                throw new InvalidArgumentException("Mật khẩu mới và xác nhận mật khẩu không trùng khớp.");
            }
            if (strlen($newPassword) < 6) {
                throw new InvalidArgumentException("Mật khẩu mới phải có tối thiểu 6 ký tự.");
            }

            // Lấy thông tin user đầy đủ kèm trường matkhau
            $pdo = DatabaseService::pdo();
            $stmt = $pdo->prepare("SELECT * FROM nguoidung WHERE id = :id");
            $stmt->bindValue(':id', $currentUserId, PDO::PARAM_INT);
            $stmt->execute();
            $userWithPass = $stmt->fetch();

            if (!$userWithPass || !$userService->verifyAndUpgradePassword($currentPassword, $userWithPass)) {
                throw new InvalidArgumentException("Mật khẩu hiện tại không chính xác!");
            }

            $userService->changePassword($currentUserId, $newPassword);
            setAdminFlash('success', 'Đổi mật khẩu thành công! Mật khẩu mới đã được băm mã hóa an toàn.');
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi đổi mật khẩu: ' . $e->getMessage());
        }

        header('Location: profile.php');
        exit;
    }
}

$avatarUrl = !empty($currentUser['hinhanh']) ? '../images/users/' . basename($currentUser['hinhanh']) : '../images/users/user.png';
$isSuper = isSuperAdmin();

require_once __DIR__ . '/inc/layout_header.php';
?>

<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Tiêu đề trang -->
    <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Hồ sơ cá nhân & Đổi mật khẩu</h1>
        <p class="text-sm text-slate-500 mt-1">
            Quản lý thông tin tài khoản đang đăng nhập, ảnh đại diện và thiết lập bảo mật mật khẩu.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- CỘT TRÁI: THẺ THÔNG TIN TÓM TẮT -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-none text-center">
                <div class="relative inline-block mx-auto mb-4">
                    <img id="avatar-preview-box" src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="w-24 h-24 rounded-md object-cover border-4 border-red-500/30 shadow-none" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($currentUser['hoten'] ?? 'Admin') ?>&background=dc2626&color=fff';">
                    <span class="absolute bottom-0 right-0 w-6 h-6 rounded-md bg-emerald-500 border-2 border-white flex items-center justify-center text-white" title="Đang hoạt động">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </span>
                </div>

                <h3 class="text-base font-extrabold text-slate-900"><?= htmlspecialchars($currentUser['hoten'] ?? 'Quản trị viên') ?></h3>
                <p class="text-xs text-slate-500 mt-0.5"><?= htmlspecialchars($currentUser['email'] ?? '') ?></p>

                <div class="mt-4 pt-4 border-t border-slate-100 space-y-2 text-left text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Vai trò:</span>
                        <span class="font-bold text-slate-800"><?= $isSuper ? 'Quản trị viên (SuperAdmin)' : 'Kỹ thuật viên XAG' ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Số điện thoại:</span>
                        <span class="font-semibold text-slate-800 font-mono-num"><?= htmlspecialchars($currentUser['sodienthoai'] ?: 'Chưa cập nhật') ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Trạng thái:</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800">Đang hoạt động</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI: FORM CHỈNH SỬA & FORM ĐỔI MẬT KHẨU -->
        <div class="md:col-span-2 space-y-6">

            <!-- FORM 1: CẬP NHẬT THÔNG TIN CÁ NHÂN -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-none">
                <h3 class="text-base font-extrabold text-slate-900 mb-1">Cập nhật thông tin cá nhân</h3>
                <p class="text-xs text-slate-500 mb-5">Thay đổi họ tên hiển thị, số điện thoại liên lạc và ảnh đại diện</p>

                <form action="profile.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="action" value="update_profile">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email đăng nhập (Cố định)</label>
                        <input type="email" value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>" disabled class="w-full text-xs px-3.5 py-2.5 rounded-md bg-slate-100 border border-slate-200 text-slate-500 cursor-not-allowed">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Họ và tên <span class="text-rose-500">*</span></label>
                            <input type="text" name="hoten" value="<?= htmlspecialchars($currentUser['hoten'] ?? '') ?>" required class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Số điện thoại <span class="text-rose-500">*</span></label>
                            <input type="text" name="sodienthoai" value="<?= htmlspecialchars($currentUser['sodienthoai'] ?? '') ?>" required class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tải ảnh đại diện mới</label>
                        <input type="file" name="avatar" accept="image/*" data-preview-target="avatar-preview-box" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white font-bold text-xs rounded-md transition shadow-none shadow-none/25">
                            Lưu thông tin cá nhân
                        </button>
                    </div>
                </form>
            </div>

            <!-- FORM 2: ĐỔI MẬT KHẨU BẢO MẬT -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-none">
                <h3 class="text-base font-extrabold text-slate-900 mb-1">Đổi mật khẩu bảo mật</h3>
                <p class="text-xs text-slate-500 mb-5">Đảm bảo an toàn tài khoản với chuẩn mã hóa một chiều Bcrypt</p>

                <form action="profile.php" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="change_password">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Mật khẩu hiện tại <span class="text-rose-500">*</span></label>
                        <input type="password" name="current_password" required placeholder="Nhập mật khẩu đang dùng" class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Mật khẩu mới <span class="text-rose-500">*</span></label>
                            <input type="password" name="new_password" required minlength="6" placeholder="Tối thiểu 6 ký tự" class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Xác nhận mật khẩu mới <span class="text-rose-500">*</span></label>
                            <input type="password" name="confirm_password" required minlength="6" placeholder="Nhập lại mật khẩu mới" class="w-full text-xs px-3.5 py-2.5 rounded-md border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500">
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-md transition shadow-none">
                            Cập nhật mật khẩu mới
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/inc/layout_footer.php'; ?>
