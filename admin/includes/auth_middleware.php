<?php
/**
 * admin/includes/auth_middleware.php
 * Middleware Kiểm tra Đăng nhập, Phân quyền RBAC & Quản lý Flash Message
 * Hệ thống Quản trị XAG Store - Giai đoạn 2
 */

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

if (!function_exists('isAuthenticated')) {
    /**
     * Kiểm tra người dùng đã đăng nhập chưa
     */
    function isAuthenticated(): bool {
        return isset($_SESSION['nguoidung']) && !empty($_SESSION['nguoidung']['id']);
    }
}

if (!function_exists('getCurrentUser')) {
    /**
     * Lấy thông tin người dùng đang đăng nhập trong Session
     */
    function getCurrentUser(): ?array {
        return $_SESSION['nguoidung'] ?? null;
    }
}

if (!function_exists('getCurrentUserId')) {
    /**
     * Lấy ID của người dùng đang đăng nhập
     */
    function getCurrentUserId(): ?int {
        return isset($_SESSION['nguoidung']['id']) ? (int)$_SESSION['nguoidung']['id'] : null;
    }
}

if (!function_exists('isSuperAdmin')) {
    /**
     * Kiểm tra có phải là Quản trị viên cấp cao (SuperAdmin - loai = 1)
     */
    function isSuperAdmin(): bool {
        return isAuthenticated() && (int)($_SESSION['nguoidung']['loai'] ?? 0) === 1;
    }
}

if (!function_exists('isTechnician')) {
    /**
     * Kiểm tra có phải là Kỹ thuật viên / Nhân viên (Technician - loai = 2)
     */
    function isTechnician(): bool {
        return isAuthenticated() && (int)($_SESSION['nguoidung']['loai'] ?? 0) === 2;
    }
}

if (!function_exists('isAdminOrStaff')) {
    /**
     * Kiểm tra có phải là nhân sự nội bộ (Admin hoặc Kỹ thuật viên: loai IN (1, 2))
     */
    function isAdminOrStaff(): bool {
        if (!isAuthenticated()) {
            return false;
        }
        $loai = (int)($_SESSION['nguoidung']['loai'] ?? 0);
        return $loai === 1 || $loai === 2;
    }
}

if (!function_exists('checkAdminAuth')) {
    /**
     * Bảo vệ toàn bộ khu vực Admin:
     * - Chưa đăng nhập -> Chuyển hướng về đăng nhập
     * - Tài khoản bị khóa (trangthai == 0) -> Đăng xuất & chuyển hướng
     * - Khách hàng thông thường (loai == 3) -> Chặn truy cập
     *
     * @param string|null $redirectUrl Đường dẫn chuyển hướng tùy chỉnh
     */
    function checkAdminAuth(?string $redirectUrl = null): void {
        // Xác định đường dẫn đăng nhập chính xác theo ngữ cảnh thư mục gọi
        if ($redirectUrl !== null) {
            $loginUrl = $redirectUrl;
        } else {
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            if (substr($scriptDir, -6) === '/admin') {
                $loginUrl = 'ktnguoidung/index.php?action=dangnhap';
            } elseif (strpos($scriptDir, '/admin/') !== false) {
                $loginUrl = '../ktnguoidung/index.php?action=dangnhap';
            } else {
                $loginUrl = 'ktnguoidung/index.php?action=dangnhap';
            }
        }

        if (!isAuthenticated()) {
            setAdminFlash('danger', 'Vui lòng đăng nhập để truy cập khu vực quản trị XAG Store.');
            header("Location: {$loginUrl}");
            exit();
        }

        $user = $_SESSION['nguoidung'];

        // Kiểm tra tài khoản bị khóa
        if (isset($user['trangthai']) && (int)$user['trangthai'] === 0) {
            unset($_SESSION['nguoidung']);
            setAdminFlash('danger', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Quản trị viên hệ thống!');
            header("Location: {$loginUrl}");
            exit();
        }

        // Chặn khách hàng thông thường truy cập khu vực nội bộ
        if ((int)($user['loai'] ?? 0) === 3) {
            unset($_SESSION['nguoidung']);
            setAdminFlash('warning', 'Bạn không có quyền truy cập vào bảng điều khiển quản trị!');
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            $publicUrl = (substr($scriptDir, -6) === '/admin') ? '../public/index.php' : '../../public/index.php';
            header("Location: {$publicUrl}");
            exit();
        }
    }
}

if (!function_exists('requireSuperAdmin')) {
    /**
     * Chặn các thao tác nhạy cảm nếu không phải là SuperAdmin (loai = 1)
     * (Ví dụ: Xóa sản phẩm, hủy phiếu nhập, quản lý nhân sự, đổi quyền)
     *
     * @param string|null $fallbackUrl
     */
    function requireSuperAdmin(?string $fallbackUrl = null): void {
        checkAdminAuth();

        if (!isSuperAdmin()) {
            setAdminFlash('danger', 'Thao tác bị từ chối! Quyền này chỉ dành riêng cho Quản trị viên cấp cao (SuperAdmin).');
            $target = $fallbackUrl ?? ($_SERVER['HTTP_REFERER'] ?? 'index.php');
            header("Location: {$target}");
            exit();
        }
    }
}

if (!function_exists('setAdminFlash')) {
    /**
     * Thiết lập Flash Message thông báo theo chuẩn PRG
     *
     * @param string $type success | danger | warning | info
     * @param string $message Nội dung thông báo
     */
    function setAdminFlash(string $type, string $message): void {
        if (!isset($_SESSION['admin_flash_messages'])) {
            $_SESSION['admin_flash_messages'] = [];
        }
        $_SESSION['admin_flash_messages'][] = [
            'type'    => $type,
            'message' => $message,
            'time'    => time()
        ];
    }
}

if (!function_exists('getAdminFlash')) {
    /**
     * Lấy danh sách Flash Message và tự động xóa khỏi Session
     *
     * @return array Danh sách mảng thông báo [['type' => ..., 'message' => ...]]
     */
    function getAdminFlash(): array {
        if (!isset($_SESSION['admin_flash_messages']) || !is_array($_SESSION['admin_flash_messages'])) {
            return [];
        }
        $messages = $_SESSION['admin_flash_messages'];
        unset($_SESSION['admin_flash_messages']);
        return $messages;
    }
}

if (!function_exists('hasAdminFlash')) {
    /**
     * Kiểm tra có Flash Message đang chờ hiển thị không
     */
    function hasAdminFlash(): bool {
        return !empty($_SESSION['admin_flash_messages']);
    }
}
