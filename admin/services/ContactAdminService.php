<?php
/**
 * ContactAdminService.php
 * Quản lý Luồng Nghiệp vụ Tư vấn Kỹ thuật & Liên hệ khách hàng
 * Hệ thống Quản trị XAG Store / LPT DRONE
 */

require_once __DIR__ . '/DatabaseService.php';
require_once __DIR__ . '/../config/db.php';

class ContactAdminService {

    /**
     * Lấy danh sách liên hệ kèm thông tin tài khoản (nếu có) và phân trang
     *
     * @param array $filters [
     *   'search'    => string|null,
     *   'trangthai' => int|null,
     *   'limit'     => int,
     *   'offset'    => int
     * ]
     * @return array ['contacts' => [...], 'total' => int]
     */
    public function getAllContacts(array $filters = []): array {
        $pdo = DatabaseService::pdo();

        $where = ["1=1"];
        $params = [];

        if (isset($filters['trangthai']) && $filters['trangthai'] !== '' && $filters['trangthai'] !== null) {
            $where[] = "lh.trangthai = :trangthai";
            $params[':trangthai'] = (int)$filters['trangthai'];
        }

        if (!empty($filters['search'])) {
            $kw = trim($filters['search']);
            $where[] = "(lh.hoten LIKE :kw1 OR lh.sodienthoai LIKE :kw2 OR lh.dongmay LIKE :kw3 OR lh.noidung LIKE :kw4 OR lh.ghichu_admin LIKE :kw5)";
            $params[':kw1'] = "%{$kw}%";
            $params[':kw2'] = "%{$kw}%";
            $params[':kw3'] = "%{$kw}%";
            $params[':kw4'] = "%{$kw}%";
            $params[':kw5'] = "%{$kw}%";
        }

        $whereClause = "WHERE " . implode(" AND ", $where);

        // Đếm tổng số lượng liên hệ khớp bộ lọc
        $countSql = "SELECT COUNT(*) FROM lienhe lh {$whereClause}";
        $stmtCount = $pdo->prepare($countSql);
        foreach ($params as $k => $v) {
            $stmtCount->bindValue($k, $v);
        }
        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();

        $limit = isset($filters['limit']) ? max(1, (int)$filters['limit']) : 15;
        $offset = isset($filters['offset']) ? max(0, (int)$filters['offset']) : 0;

        $sql = "SELECT lh.*, nd.email AS khach_email, nd.hoten AS khach_hoten_taikhoan
                FROM lienhe lh
                LEFT JOIN nguoidung nd ON lh.nguoidung_id = nd.id
                {$whereClause}
                ORDER BY lh.trangthai ASC, lh.ngaygui DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'contacts' => $contacts,
            'total'    => $total
        ];
    }

    /**
     * Lấy chi tiết một yêu cầu liên hệ theo ID
     */
    public function getContactById(int $id): ?array {
        $pdo = DatabaseService::pdo();
        $sql = "SELECT lh.*, nd.email AS khach_email, nd.hoten AS khach_hoten_taikhoan
                FROM lienhe lh
                LEFT JOIN nguoidung nd ON lh.nguoidung_id = nd.id
                WHERE lh.id = :id
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Cập nhật trạng thái và ghi chú của Admin
     *
     * @param int $id ID liên hệ
     * @param int $trangthai 0: Chờ tư vấn, 1: Đã xử lý
     * @param string|null $ghichu_admin Ghi chú nội bộ của kỹ thuật viên / admin
     */
    public function updateStatus(int $id, int $trangthai, ?string $ghichu_admin = null): bool {
        $pdo = DatabaseService::pdo();
        $sql = "UPDATE lienhe 
                SET trangthai = :trangthai, 
                    ghichu_admin = :ghichu_admin 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':trangthai'     => $trangthai,
            ':ghichu_admin'  => $ghichu_admin,
            ':id'            => $id
        ]);
    }

    /**
     * Xóa một yêu cầu liên hệ
     */
    public function deleteContact(int $id): bool {
        $pdo = DatabaseService::pdo();
        $sql = "DELETE FROM lienhe WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Lấy số lượng liên hệ đang chờ tư vấn (trangthai = 0)
     */
    public function getPendingCount(): int {
        $pdo = DatabaseService::pdo();
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM lienhe WHERE trangthai = 0");
            return (int)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Lấy số liệu thống kê tổng quan về liên hệ
     */
    public function getContactStats(): array {
        $pdo = DatabaseService::pdo();
        try {
            $sql = "SELECT 
                        COUNT(*) AS tong_so,
                        SUM(CASE WHEN trangthai = 0 THEN 1 ELSE 0 END) AS cho_tu_van,
                        SUM(CASE WHEN trangthai = 1 THEN 1 ELSE 0 END) AS da_xu_ly
                    FROM lienhe";
            $stmt = $pdo->query($sql);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'total'     => (int)($row['tong_so'] ?? 0),
                'pending'   => (int)($row['cho_tu_van'] ?? 0),
                'processed' => (int)($row['da_xu_ly'] ?? 0)
            ];
        } catch (Throwable $e) {
            return ['total' => 0, 'pending' => 0, 'processed' => 0];
        }
    }
}
