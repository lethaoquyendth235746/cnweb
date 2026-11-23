<?php
/**
 * admin/includes/auth_middleware.php
 * Middleware Kiểm tra Đăng nhập, Phân quyền RBAC & Quản lý Flash Message
 * Hệ thống Quản trị XAG Store - Giai đoạn 2 & Chuẩn hóa Phân quyền
 */

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

if (!function_exists('isAuthenticated')) {
    /**
     * Kiểm tra người dùng Quản trị / Nhân viên đã đăng nhập chưa
     */
    function isAuthenticated(): bool {
        if (isset($_SESSION['admin']) && !empty($_SESSION['admin']['id'])) {
            return true;
        }
        // Tương thích ngược nếu tồn tại $_SESSION['nguoidung'] có quyền quản trị
        if (isset($_SESSION['nguoidung']) && !empty($_SESSION['nguoidung']['id'])) {
            $loai = (int)($_SESSION['nguoidung']['loai'] ?? 0);
            return ($loai === 1 || $loai === 2);
        }
        return false;
    }
}

if (!function_exists('getCurrentUser')) {
    /**
     * Lấy thông tin người dùng đang đăng nhập trong Session Quản trị
     */
    function getCurrentUser(): ?array {
        if (isset($_SESSION['admin']) && !empty($_SESSION['admin']['id'])) {
            return $_SESSION['admin'];
        }
        if (isset($_SESSION['nguoidung']) && !empty($_SESSION['nguoidung']['id'])) {
            $loai = (int)($_SESSION['nguoidung']['loai'] ?? 0);
            if ($loai === 1 || $loai === 2) {
                return $_SESSION['nguoidung'];
            }
        }
        return null;
    }
}

if (!function_exists('getCurrentUserId')) {
    /**
     * Lấy ID của người dùng đang đăng nhập
     */
    function getCurrentUserId(): ?int {
        $u = getCurrentUser();
        return isset($u['id']) ? (int)$u['id'] : null;
    }
}

if (!function_exists('isSuperAdmin')) {
    /**
     * Kiểm tra có phải là Quản trị viên cấp cao (SuperAdmin - loai = 1)
     */
    function isSuperAdmin(): bool {
        if (!isAuthenticated()) {
            return false;
        }
        $u = getCurrentUser();
        return (int)($u['loai'] ?? 0) === 1;
    }
}

if (!function_exists('isTechnician')) {
    /**
     * Kiểm tra có phải là Kỹ thuật viên / Nhân viên (Technician - loai = 2)
     */
    function isTechnician(): bool {
        if (!isAuthenticated()) {
            return false;
        }
        $u = getCurrentUser();
        return (int)($u['loai'] ?? 0) === 2;
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
        $u = getCurrentUser();
        $loai = (int)($u['loai'] ?? 0);
        return $loai === 1 || $loai === 2;
    }
}

if (!function_exists('checkAdminAuth')) {
    /**
     * Bảo vệ toàn bộ khu vực Admin:
     * - Chưa đăng nhập -> Chuyển hướng về trang login admin
     * - Kiểm tra realtime CSDL: Nếu tài khoản bị khóa (trangthai == 0) hoặc bị xóa -> Thu hồi phiên ngay
     * - Chặn khách hàng thông thường (loai == 3)
     * - Phân quyền trang: users.php chỉ cho phép SuperAdmin (loai == 1)
     *
     * @param string|null $redirectUrl Đường dẫn chuyển hướng tùy chỉnh
     */
    function checkAdminAuth(?string $redirectUrl = null): void {
        // Xác định đường dẫn đăng nhập chính xác theo cổng đăng nhập hợp nhất
        if ($redirectUrl !== null) {
            $loginUrl = $redirectUrl;
        } else {
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            if (substr($scriptDir, -6) === '/admin') {
                $loginUrl = '../public/index.php?action=dangnhap';
            } elseif (strpos($scriptDir, '/admin/') !== false) {
                $loginUrl = '../../public/index.php?action=dangnhap';
            } else {
                $loginUrl = '../public/index.php?action=dangnhap';
            }
        }

        // 1. Kiểm tra session cơ bản
        if (!isAuthenticated()) {
            setAdminFlash('danger', 'Vui lòng đăng nhập để truy cập khu vực quản trị XAG Store.');
            header("Location: {$loginUrl}");
            exit();
        }

        $userId = getCurrentUserId();
        if (!$userId) {
            unset($_SESSION['admin']);
            setAdminFlash('danger', 'Phiên làm việc không hợp lệ. Vui lòng đăng nhập lại.');
            header("Location: {$loginUrl}");
            exit();
        }

        // 2. Truy vấn lại CSDL theo ID để xác nhận trạng thái tài khoản thời gian thực
        require_once dirname(__DIR__, 2) . '/model/database.php';
        try {
            $db = DATABASE::connect();
            $stmt = $db->prepare("SELECT id, email, hoten, sodienthoai, hinhanh, loai, trangthai FROM nguoidung WHERE id = :id LIMIT 1");
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $freshUser = $stmt->fetch(PDO::FETCH_ASSOC);

            // Tài khoản không tồn tại hoặc đã bị khóa
            if (!$freshUser || (int)$freshUser['trangthai'] !== 1) {
                unset($_SESSION['admin']);
                setAdminFlash('danger', 'Tài khoản của bạn đã bị khóa hoặc không tồn tại. Vui lòng liên hệ Quản trị viên hệ thống!');
                header("Location: {$loginUrl}");
                exit();
            }

            // Tài khoản khách hàng thông thường không được truy cập Admin
            if (!in_array((int)$freshUser['loai'], [1, 2], true)) {
                unset($_SESSION['admin']);
                setAdminFlash('warning', 'Tài khoản khách hàng không có quyền truy cập khu vực quản trị!');
                $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
                $publicUrl = (substr($scriptDir, -6) === '/admin') ? '../public/index.php' : '../../public/index.php';
                header("Location: {$publicUrl}");
                exit();
            }

            // Đồng bộ dữ liệu mới nhất vào Session Admin
            $_SESSION['admin'] = $freshUser;

            // 3. Phân quyền chi tiết: Trang users.php chỉ dành cho SuperAdmin (loai == 1)
            $currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
            if ($currentScript === 'users.php' && (int)$freshUser['loai'] !== 1) {
                setAdminFlash('danger', 'Thao tác bị từ chối! Quản lý nhân sự và phân quyền chỉ dành riêng cho Quản trị viên cấp cao (SuperAdmin).');
                $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
                $dashboardUrl = (substr($scriptDir, -6) === '/admin') ? 'index.php' : '../index.php';
                header("Location: {$dashboardUrl}");
                exit();
            }

        } catch (Throwable $e) {
            // Trường hợp lỗi CSDL tạm thời, cho phép tiếp tục nếu session hợp lệ
        }
    }
}

if (!function_exists('requireSuperAdmin')) {
    /**
     * Chặn các thao tác nhạy cảm nếu không phải là SuperAdmin (loai = 1)
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
