<?php
/**
 * CustomerAdminService.php
 * Quản trị Khách hàng & Hợp tác xã (loai = 3): Thống kê chi tiêu, Sổ địa chỉ & Lịch sử đơn hàng chi tiết
 * Hệ thống Quản trị XAG Store - Giai đoạn 2
 */

require_once __DIR__ . '/DatabaseService.php';
require_once __DIR__ . '/../config/db.php';

class CustomerAdminService {

    /**
     * Lấy danh sách khách hàng (loai = 3) kèm thống kê tổng đơn, chi tiêu tích lũy và đơn chờ duyệt
     *
     * @param array $filters [
     *   'search'    => string|null, (họ tên, email, sđt)
     *   'trangthai' => int|null, (1: Kích hoạt, 0: Khóa)
     *   'sort_by'   => string|null, ('tong_chi_tieu', 'tong_don', 'id')
     *   'sort_dir'  => string|null, ('ASC', 'DESC')
     *   'limit'     => int,
     *   'offset'    => int
     * ]
     * @return array ['customers' => [...], 'total' => int]
     */
    public function getAllCustomers(array $filters = []): array {
        $pdo = DatabaseService::pdo();

        $where = ["nd.loai = 3"];
        $params = [];

        if (isset($filters['trangthai']) && $filters['trangthai'] !== '' && $filters['trangthai'] !== null) {
            $where[] = "nd.trangthai = :trangthai";
            $params[':trangthai'] = (int)$filters['trangthai'];
        }

        if (!empty($filters['search'])) {
            $kw = trim($filters['search']);
            $where[] = "(nd.hoten LIKE :kw OR nd.email LIKE :kw OR nd.sodienthoai LIKE :kw)";
            $params[':kw'] = "%{$kw}%";
        }

        $whereClause = "WHERE " . implode(" AND ", $where);

        // Đếm tổng số khách hàng
        $countSql = "SELECT COUNT(*) FROM nguoidung nd {$whereClause}";
        $stmtCount = $pdo->prepare($countSql);
        foreach ($params as $k => $v) {
            $stmtCount->bindValue($k, $v);
        }
        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();

        // Xử lý sắp xếp
        $allowedSort = [
            'tong_chi_tieu' => 'tong_chi_tieu',
            'tong_don'      => 'tong_don',
            'id'            => 'nd.id',
            'hoten'         => 'nd.hoten'
        ];
        $sortBy = $allowedSort[$filters['sort_by'] ?? ''] ?? 'tong_chi_tieu';
        $sortDir = strtoupper($filters['sort_dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $limit = isset($filters['limit']) ? max(1, (int)$filters['limit']) : 20;
        $offset = isset($filters['offset']) ? max(0, (int)$filters['offset']) : 0;

        $sql = "SELECT nd.id, nd.email, nd.sodienthoai, nd.hoten, nd.trangthai, nd.hinhanh,
                       COUNT(dh.id) AS tong_don,
                       COALESCE(SUM(CASE WHEN dh.trangthai = 3 THEN dh.tongtien ELSE 0 END), 0) AS tong_chi_tieu,
                       COUNT(CASE WHEN dh.trangthai = 0 THEN 1 END) AS don_cho_duyet
                FROM nguoidung nd
                LEFT JOIN donhang dh ON nd.id = dh.nguoidung_id
                {$whereClause}
                GROUP BY nd.id
                ORDER BY {$sortBy} {$sortDir}
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $customers = $stmt->fetchAll();

        foreach ($customers as &$c) {
            $c['tong_chi_tieu_fmt'] = formatVND($c['tong_chi_tieu']);
            $c['status_label'] = ((int)$c['trangthai'] === 1) ? 'Hoạt động' : 'Đang bị khóa';
            $c['status_badge_class'] = ((int)$c['trangthai'] === 1) ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800';
        }

        return [
            'customers' => $customers,
            'total'     => $total,
            'limit'     => $limit,
            'offset'    => $offset
        ];
    }

    /**
     * Lấy thông tin hồ sơ chi tiết của một khách hàng kèm danh sách địa chỉ và tóm tắt giao dịch
     *
     * @param int $customerId
     * @return array|null
     */
    public function getCustomerById(int $customerId): ?array {
        $pdo = DatabaseService::pdo();

        $sql = "SELECT nd.id, nd.email, nd.sodienthoai, nd.hoten, nd.trangthai, nd.hinhanh,
                       COUNT(dh.id) AS tong_don,
                       COALESCE(SUM(CASE WHEN dh.trangthai = 3 THEN dh.tongtien ELSE 0 END), 0) AS tong_chi_tieu,
                       COUNT(CASE WHEN dh.trangthai = 0 THEN 1 END) AS don_cho_duyet,
                       COUNT(CASE WHEN dh.trangthai = 3 THEN 1 END) AS don_thanh_cong,
                       COUNT(CASE WHEN dh.trangthai = 4 THEN 1 END) AS don_da_huy
                FROM nguoidung nd
                LEFT JOIN donhang dh ON nd.id = dh.nguoidung_id
                WHERE nd.id = :id AND nd.loai = 3
                GROUP BY nd.id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $customerId, PDO::PARAM_INT);
        $stmt->execute();
        $customer = $stmt->fetch();

        if (!$customer) {
            return null;
        }

        $customer['tong_chi_tieu_fmt'] = formatVND($customer['tong_chi_tieu']);
        $customer['addresses'] = $this->getCustomerAddresses($customerId);
        $customer['recent_orders'] = $this->getCustomerOrders($customerId, 10);

        return $customer;
    }

    /**
     * Lấy danh sách địa chỉ giao hàng của khách hàng (bảng diachi)
     *
     * @param int $customerId
     * @return array
     */
    public function getCustomerAddresses(int $customerId): array {
        $pdo = DatabaseService::pdo();

        $stmt = $pdo->prepare("SELECT id, diachi, macdinh FROM diachi WHERE nguoidung_id = :uid ORDER BY macdinh DESC, id ASC");
        $stmt->bindValue(':uid', $customerId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách đơn hàng đã đặt của một khách hàng
     *
     * @param int $customerId
     * @param int $limit
     * @return array
     */
    public function getCustomerOrders(int $customerId, int $limit = 10): array {
        $pdo = DatabaseService::pdo();

        $sql = "SELECT dh.id, dh.ngay, dh.tongtien, dh.trangthai, dh.ghichu,
                       dc.diachi AS diachi_giao
                FROM donhang dh
                LEFT JOIN diachi dc ON dh.diachi_id = dc.id
                WHERE dh.nguoidung_id = :uid
                ORDER BY dh.ngay DESC, dh.id DESC
                LIMIT :limit";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':uid', $customerId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $orders = $stmt->fetchAll();

        foreach ($orders as &$o) {
            $o['tongtien_fmt'] = formatVND($o['tongtien']);
            $o['ngay_fmt'] = formatDateVN($o['ngay'], true);
            $o['status_meta'] = getOrderStatusMeta($o['trangthai']);
        }

        return $orders;
    }

    /**
     * Khóa hoặc mở khóa tài khoản khách hàng
     *
     * @param int $customerId
     * @param int $newStatus (1: Kích hoạt, 0: Khóa)
     * @return bool
     * @throws DomainException
     */
    public function toggleCustomerStatus(int $customerId, int $newStatus): bool {
        $pdo = DatabaseService::pdo();

        $stmtCheck = $pdo->prepare("SELECT id FROM nguoidung WHERE id = :id AND loai = 3");
        $stmtCheck->bindValue(':id', $customerId, PDO::PARAM_INT);
        $stmtCheck->execute();
        if (!$stmtCheck->fetch()) {
            throw new DomainException("Khách hàng ID {$customerId} không tồn tại.");
        }

        $stmt = $pdo->prepare("UPDATE nguoidung SET trangthai = :trangthai WHERE id = :id");
        $stmt->bindValue(':trangthai', $newStatus ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':id', $customerId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
