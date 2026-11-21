<?php
/**
 * UserAdminService.php
 * Quản trị Nhân sự Nội bộ (SuperAdmin & Kỹ thuật viên): Phân quyền, Khóa/Mở, Mật khẩu Bcrypt & Dual Auth
 * Hệ thống Quản trị XAG Store - Giai đoạn 2
 */

require_once __DIR__ . '/DatabaseService.php';
require_once __DIR__ . '/../config/db.php';

class UserAdminService {

    /**
     * Lấy danh sách tài khoản nội bộ (Admin và Kỹ thuật viên: loai IN (1, 2))
     *
     * @param array $filters [
     *   'loai'      => int|null, (1: Admin, 2: Kỹ thuật viên)
     *   'trangthai' => int|null, (1: Kích hoạt, 0: Bị khóa)
     *   'search'    => string|null (họ tên, email, sđt)
     * ]
     * @return array
     */
    public function getAllInternalUsers(array $filters = []): array {
        $pdo = DatabaseService::pdo();

        $where = ["loai IN (1, 2)"];
        $params = [];

        if (isset($filters['loai']) && in_array((int)$filters['loai'], [1, 2], true)) {
            $where[] = "loai = :loai";
            $params[':loai'] = (int)$filters['loai'];
        }

        if (isset($filters['trangthai']) && $filters['trangthai'] !== '' && $filters['trangthai'] !== null) {
            $where[] = "trangthai = :trangthai";
            $params[':trangthai'] = (int)$filters['trangthai'];
        }

        if (!empty($filters['search'])) {
            $kw = trim($filters['search']);
            $where[] = "(hoten LIKE :kw OR email LIKE :kw OR sodienthoai LIKE :kw)";
            $params[':kw'] = "%{$kw}%";
        }

        $whereClause = "WHERE " . implode(" AND ", $where);

        $sql = "SELECT id, email, sodienthoai, hoten, loai, trangthai, hinhanh
                FROM nguoidung
                {$whereClause}
                ORDER BY loai ASC, id ASC";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $users = $stmt->fetchAll();

        foreach ($users as &$u) {
            $u['role_label'] = ((int)$u['loai'] === 1) ? 'Quản trị viên (Admin)' : 'Kỹ thuật viên / Nhân viên';
            $u['role_badge_class'] = ((int)$u['loai'] === 1) ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800';
            $u['status_label'] = ((int)$u['trangthai'] === 1) ? 'Hoạt động' : 'Đang bị khóa';
            $u['status_badge_class'] = ((int)$u['trangthai'] === 1) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800';
        }

        return $users;
    }

    /**
     * Lấy thông tin người dùng theo ID (không kèm mật khẩu)
     *
     * @param int $id
     * @return array|null
     */
    public function getUserById(int $id): ?array {
        $pdo = DatabaseService::pdo();

        $stmt = $pdo->prepare("SELECT id, email, sodienthoai, hoten, loai, trangthai, hinhanh FROM nguoidung WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            $user['role_label'] = ((int)$user['loai'] === 1) ? 'Quản trị viên (Admin)' : 'Kỹ thuật viên / Nhân viên';
        }

        return $user ?: null;
    }

    /**
     * Tạo tài khoản nhân sự nội bộ mới (SuperAdmin hoặc Kỹ thuật viên)
     * Mật khẩu được băm bằng chuẩn Bcrypt hiện đại
     *
     * @param array $data [
     *   'email'       => string,
     *   'matkhau'     => string,
     *   'hoten'       => string,
     *   'sodienthoai' => string,
     *   'loai'        => int, (1: Admin, 2: Kỹ thuật viên)
     *   'hinhanh'     => string|null
     * ]
     * @return int ID tài khoản mới
     * @throws InvalidArgumentException
     */
    public function createUser(array $data): int {
        $email = trim($data['email'] ?? '');
        $rawPassword = $data['matkhau'] ?? '';
        $hoten = trim($data['hoten'] ?? '');
        $sodt = trim($data['sodienthoai'] ?? '');
        $loai = (int)($data['loai'] ?? 2);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Địa chỉ email không hợp lệ.");
        }
        if (strlen($rawPassword) < 6) {
            throw new InvalidArgumentException("Mật khẩu phải chứa ít nhất 6 ký tự.");
        }
        if (empty($hoten)) {
            throw new InvalidArgumentException("Họ và tên không được để trống.");
        }
        if (empty($sodt)) {
            throw new InvalidArgumentException("Số điện thoại liên hệ không được để trống.");
        }
        if (!in_array($loai, [1, 2], true)) {
            throw new InvalidArgumentException("Loại tài khoản nội bộ chỉ có thể là 1 (Admin) hoặc 2 (Kỹ thuật viên).");
        }

        $pdo = DatabaseService::pdo();

        // Kiểm tra email duy nhất
        $stmtCheck = $pdo->prepare("SELECT id FROM nguoidung WHERE email = :email");
        $stmtCheck->bindValue(':email', $email);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            throw new InvalidArgumentException("Email '{$email}' đã tồn tại trong hệ thống.");
        }

        // Băm mật khẩu chuẩn Bcrypt
        $hashedPassword = password_hash($rawPassword, PASSWORD_BCRYPT);
        $hinhanh = !empty($data['hinhanh']) ? trim($data['hinhanh']) : 'images/users/user.png';

        $sql = "INSERT INTO nguoidung (email, matkhau, sodienthoai, hoten, loai, trangthai, hinhanh)
                VALUES (:email, :matkhau, :sodt, :hoten, :loai, 1, :hinhanh)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':matkhau', $hashedPassword);
        $stmt->bindValue(':sodt', $sodt);
        $stmt->bindValue(':hoten', $hoten);
        $stmt->bindValue(':loai', $loai, PDO::PARAM_INT);
        $stmt->bindValue(':hinhanh', $hinhanh);
        $stmt->execute();

        return (int)$pdo->lastInsertId();
    }

    /**
     * Cập nhật thông tin tài khoản nhân sự
     *
     * @param int $id
     * @param array $data
     * @return bool
     * @throws DomainException|InvalidArgumentException
     */
    public function updateUser(int $id, array $data): bool {
        $existing = $this->getUserById($id);
        if (!$existing) {
            throw new DomainException("Tài khoản người dùng ID {$id} không tồn tại.");
        }

        $hoten = trim($data['hoten'] ?? $existing['hoten']);
        $sodt = trim($data['sodienthoai'] ?? $existing['sodienthoai']);
        $loai = isset($data['loai']) ? (int)$data['loai'] : (int)$existing['loai'];
        $hinhanh = trim($data['hinhanh'] ?? $existing['hinhanh']);

        if (empty($hoten)) {
            throw new InvalidArgumentException("Họ tên không được để trống.");
        }
        if (empty($sodt)) {
            throw new InvalidArgumentException("Số điện thoại không được để trống.");
        }
        if (!in_array($loai, [1, 2], true)) {
            throw new InvalidArgumentException("Quyền nội bộ không hợp lệ.");
        }

        $pdo = DatabaseService::pdo();

        $sql = "UPDATE nguoidung 
                SET hoten = :hoten, 
                    sodienthoai = :sodt, 
                    loai = :loai, 
                    hinhanh = :hinhanh 
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':hoten', $hoten);
        $stmt->bindValue(':sodt', $sodt);
        $stmt->bindValue(':loai', $loai, PDO::PARAM_INT);
        $stmt->bindValue(':hinhanh', $hinhanh);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Khóa hoặc mở khóa tài khoản nhân sự
     * Ngăn chặn Quản trị viên tự khóa chính tài khoản của mình
     *
     * @param int $id ID tài khoản cần đổi
     * @param int $newStatus 1: Kích hoạt, 0: Khóa
     * @param int|null $currentUserId ID tài khoản đang thao tác
     * @return bool
     * @throws DomainException
     */
    public function toggleUserStatus(int $id, int $newStatus, ?int $currentUserId = null): bool {
        if ($currentUserId !== null && $id === $currentUserId && $newStatus === 0) {
            throw new DomainException("Bạn không thể tự khóa tài khoản của chính mình!");
        }

        $existing = $this->getUserById($id);
        if (!$existing) {
            throw new DomainException("Tài khoản ID {$id} không tồn tại.");
        }

        $pdo = DatabaseService::pdo();
        $stmt = $pdo->prepare("UPDATE nguoidung SET trangthai = :trangthai WHERE id = :id");
        $stmt->bindValue(':trangthai', $newStatus ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Đổi vai trò quyền hạn (1: Admin, 2: Kỹ thuật viên)
     *
     * @param int $id
     * @param int $newRole
     * @return bool
     */
    public function changeUserRole(int $id, int $newRole): bool {
        if (!in_array($newRole, [1, 2], true)) {
            throw new InvalidArgumentException("Vai trò không hợp lệ.");
        }

        $pdo = DatabaseService::pdo();
        $stmt = $pdo->prepare("UPDATE nguoidung SET loai = :loai WHERE id = :id");
        $stmt->bindValue(':loai', $newRole, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Đổi mật khẩu tài khoản người dùng bằng thuật toán Bcrypt
     *
     * @param int $id ID người dùng
     * @param string $newPassword Mật khẩu thô mới
     * @return bool
     * @throws InvalidArgumentException
     */
    public function changePassword(int $id, string $newPassword): bool {
        if (strlen($newPassword) < 6) {
            throw new InvalidArgumentException("Mật khẩu mới phải có tối thiểu 6 ký tự.");
        }

        $hashed = password_hash($newPassword, PASSWORD_BCRYPT);

        $pdo = DatabaseService::pdo();
        $stmt = $pdo->prepare("UPDATE nguoidung SET matkhau = :matkhau WHERE id = :id");
        $stmt->bindValue(':matkhau', $hashed);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Xác thực mật khẩu thông minh & Tự động nâng cấp hash (Dual Compatibility: MD5 -> Bcrypt)
     * - Nếu trong DB là Bcrypt ($2y$...): Sử dụng password_verify
     * - Nếu trong DB là mã MD5 cũ (32 ký tự): Kiểm tra md5(), nếu đúng -> Tự động băm Bcrypt và lưu lại vào DB!
     *
     * @param string $inputPassword Mật khẩu người dùng nhập vào
     * @param array $user Bản ghi người dùng từ bảng nguoidung
     * @return bool
     */
    public function verifyAndUpgradePassword(string $inputPassword, array $user): bool {
        $storedHash = $user['matkhau'] ?? '';
        if (empty($storedHash)) {
            return false;
        }

        // Trường hợp 1: Mật khẩu đã được mã hóa bằng Bcrypt hoặc Argon
        if (str_starts_with($storedHash, '$2y$') || str_starts_with($storedHash, '$2a$')) {
            return password_verify($inputPassword, $storedHash);
        }

        // Trường hợp 2: Mật khẩu lưu dạng MD5 cũ (32 ký tự hex)
        if (strlen($storedHash) === 32 && ctype_xdigit($storedHash)) {
            if (md5($inputPassword) === strtolower($storedHash)) {
                // Tự động nâng cấp lên Bcrypt và cập nhật lại vào CSDL
                $newHash = password_hash($inputPassword, PASSWORD_BCRYPT);
                $pdo = DatabaseService::pdo();
                $stmt = $pdo->prepare("UPDATE nguoidung SET matkhau = :matkhau WHERE id = :id");
                $stmt->bindValue(':matkhau', $newHash);
                $stmt->bindValue(':id', (int)$user['id'], PDO::PARAM_INT);
                $stmt->execute();

                return true;
            }
        }

        return false;
    }
}
