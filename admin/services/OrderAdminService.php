<?php
/**
 * OrderAdminService.php
 * Quản lý vòng đời đơn hàng, phân trang, lọc nâng cao & State Machine giao dịch kho
 * Hệ thống Quản trị XAG Store - Giai đoạn 2
 */

require_once __DIR__ . '/DatabaseService.php';
require_once __DIR__ . '/../config/db.php';

class OrderAdminService {

    /**
     * Định nghĩa ma trận chuyển đổi trạng thái hợp lệ (Order State Machine)
     * Dựa trên đặc tả chi tiết trong DATABASE_GAP_ANALYSIS.md
     */
    private const ALLOWED_TRANSITIONS = [
        0 => [1, 4],       // Chờ xác nhận -> Đã xác nhận HOẶC Hủy
        1 => [2, 4],       // Đã xác nhận -> Đang vận chuyển HOẶC Hủy
        2 => [3, 5],       // Đang vận chuyển -> Hoàn thành HOẶC Trả hàng/Hoàn tiền
        3 => [5],          // Hoàn thành -> Trả hàng/Hoàn tiền (đổi trả bảo hành 7 ngày)
        4 => [],           // Đã hủy -> Trạng thái kết thúc (Terminal State)
        5 => []            // Trả hàng -> Trạng thái kết thúc (Terminal State)
    ];

    /**
     * Lấy danh sách tất cả đơn hàng theo bộ lọc đa năng và phân trang
     *
     * @param array $filters [
     *   'trangthai' => int|null,
     *   'search'    => string|null, (mã đơn, họ tên, sđt, email)
     *   'from_date' => string|null, (YYYY-MM-DD)
     *   'to_date'   => string|null, (YYYY-MM-DD)
     *   'limit'     => int, (mặc định 20)
     *   'offset'    => int (mặc định 0)
     * ]
     * @return array ['orders' => [...], 'total' => int, 'limit' => int, 'offset' => int]
     */
    public function getAllOrders(array $filters = []): array {
        $pdo = DatabaseService::pdo();

        $where = [];
        $params = [];

        // 1. Lọc theo trạng thái
        if (isset($filters['trangthai']) && $filters['trangthai'] !== '' && $filters['trangthai'] !== null) {
            $where[] = "dh.trangthai = :trangthai";
            $params[':trangthai'] = (int)$filters['trangthai'];
        }

        // 2. Tìm kiếm từ khóa (Mã đơn, Tên khách hàng, Số điện thoại, Email)
        if (!empty($filters['search'])) {
            $keyword = trim($filters['search']);
            $where[] = "(dh.id = :kw_id OR nd.hoten LIKE :kw_name OR nd.sodienthoai LIKE :kw_phone OR nd.email LIKE :kw_email)";
            $params[':kw_id'] = is_numeric($keyword) ? (int)$keyword : 0;
            $params[':kw_name'] = "%{$keyword}%";
            $params[':kw_phone'] = "%{$keyword}%";
            $params[':kw_email'] = "%{$keyword}%";
        }

        // 3. Khoảng ngày tạo đơn
        if (!empty($filters['from_date'])) {
            $where[] = "DATE(dh.ngay) >= :from_date";
            $params[':from_date'] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $where[] = "DATE(dh.ngay) <= :to_date";
            $params[':to_date'] = $filters['to_date'];
        }

        $whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

        // Đếm tổng số bản ghi khớp bộ lọc
        $countSql = "SELECT COUNT(*) 
                     FROM donhang dh 
                     JOIN nguoidung nd ON dh.nguoidung_id = nd.id 
                     {$whereClause}";
        $stmtCount = $pdo->prepare($countSql);
        foreach ($params as $k => $v) {
            $stmtCount->bindValue($k, $v);
        }
        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();

        // Lấy danh sách đơn hàng có phân trang
        $limit = isset($filters['limit']) ? max(1, (int)$filters['limit']) : 20;
        $offset = isset($filters['offset']) ? max(0, (int)$filters['offset']) : 0;

        $sql = "SELECT dh.id, dh.nguoidung_id, dh.diachi_id, dh.ngay, dh.tongtien, dh.ghichu, dh.trangthai,
                       dh.phuongthucthanhtoan, dh.trangthaithanhtoan,
                       nd.hoten, nd.sodienthoai, nd.email,
                       dc.diachi AS diachi_chitiet
                FROM donhang dh
                JOIN nguoidung nd ON dh.nguoidung_id = nd.id
                LEFT JOIN diachi dc ON dh.diachi_id = dc.id
                {$whereClause}
                ORDER BY dh.ngay DESC, dh.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $orders = $stmt->fetchAll();

        // Nạp danh sách chi tiết mặt hàng cho từng đơn hàng
        if (count($orders) > 0) {
            $orderIds = array_column($orders, 'id');
            $inPlaceholders = implode(',', array_fill(0, count($orderIds), '?'));

            $itemSql = "SELECT ct.id, ct.donhang_id, ct.mathang_id, ct.dongia, ct.soluong, ct.thanhtien,
                               m.tenmathang, m.hinhanh, m.dongmay
                        FROM donhangct ct
                        JOIN mathang m ON ct.mathang_id = m.id
                        WHERE ct.donhang_id IN ({$inPlaceholders})
                        ORDER BY ct.id ASC";

            $stmtItems = $pdo->prepare($itemSql);
            $stmtItems->execute($orderIds);
            $allItems = $stmtItems->fetchAll();

            $itemsByOrderId = [];
            foreach ($allItems as $item) {
                $item['dongia_fmt'] = formatVND($item['dongia']);
                $item['thanhtien_fmt'] = formatVND($item['thanhtien']);
                $itemsByOrderId[$item['donhang_id']][] = $item;
            }

            foreach ($orders as &$order) {
                $order['tongtien_fmt'] = formatVND($order['tongtien']);
                $order['ngay_fmt'] = formatDateVN($order['ngay'], true);
                $order['status_meta'] = getOrderStatusMeta($order['trangthai']);
                $order['items'] = $itemsByOrderId[$order['id']] ?? [];
                $order['total_items'] = count($order['items']);
            }
        }

        return [
            'orders' => $orders,
            'total'  => $total,
            'limit'  => $limit,
            'offset' => $offset
        ];
    }

    /**
     * Lấy thông tin chi tiết một đơn hàng cụ thể kèm items và địa chỉ
     *
     * @param int $orderId
     * @return array|null
     */
    public function getOrderById(int $orderId): ?array {
        $pdo = DatabaseService::pdo();

        $sql = "SELECT dh.id, dh.nguoidung_id, dh.diachi_id, dh.ngay, dh.tongtien, dh.ghichu, dh.trangthai,
                       dh.phuongthucthanhtoan, dh.trangthaithanhtoan,
                       nd.hoten, nd.sodienthoai, nd.email,
                       dc.diachi AS diachi_chitiet
                FROM donhang dh
                JOIN nguoidung nd ON dh.nguoidung_id = nd.id
                LEFT JOIN diachi dc ON dh.diachi_id = dc.id
                WHERE dh.id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        // Lấy chi tiết items
        $itemSql = "SELECT ct.id, ct.donhang_id, ct.mathang_id, ct.dongia, ct.soluong, ct.thanhtien,
                           m.tenmathang, m.hinhanh, m.dongmay, m.soluongton
                    FROM donhangct ct
                    JOIN mathang m ON ct.mathang_id = m.id
                    WHERE ct.donhang_id = :id
                    ORDER BY ct.id ASC";
        $stmtItems = $pdo->prepare($itemSql);
        $stmtItems->bindValue(':id', $orderId, PDO::PARAM_INT);
        $stmtItems->execute();
        $items = $stmtItems->fetchAll();

        foreach ($items as &$it) {
            $it['dongia_fmt'] = formatVND($it['dongia']);
            $it['thanhtien_fmt'] = formatVND($it['thanhtien']);
        }

        $order['tongtien_fmt'] = formatVND($order['tongtien']);
        $order['ngay_fmt'] = formatDateVN($order['ngay'], true);
        $order['status_meta'] = getOrderStatusMeta($order['trangthai']);
        $order['items'] = $items;

        return $order;
    }

    /**
     * Đếm số lượng đơn hàng theo từng trạng thái (0..5) phục vụ Badge Sidebar & Tab lọc
     *
     * @return array ['all' => int, 0 => int, 1 => int, 2 => int, 3 => int, 4 => int, 5 => int]
     */
    public function getOrderStatusCounts(): array {
        $pdo = DatabaseService::pdo();

        $sql = "SELECT trangthai, COUNT(*) as count 
                FROM donhang 
                GROUP BY trangthai";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll();

        $counts = [
            'all' => 0,
            0 => 0,
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0
        ];

        foreach ($rows as $r) {
            $st = (int)$r['trangthai'];
            $cnt = (int)$r['count'];
            $counts[$st] = $cnt;
            $counts['all'] += $cnt;
        }

        return $counts;
    }

    /**
     * Kiểm tra tính hợp lệ của việc chuyển đổi trạng thái đơn hàng (State Machine)
     *
     * @param int $currentStatus Trạng thái hiện tại
     * @param int $newStatus Trạng thái mong muốn chuyển đến
     * @return bool
     */
    public function canTransition(int $currentStatus, int $newStatus): bool {
        if ($currentStatus === $newStatus) {
            return true; // Giữ nguyên trạng thái (cho phép cập nhật ghi chú)
        }
        $allowed = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];
        return in_array($newStatus, $allowed, true);
    }

    /**
     * Cập nhật trạng thái đơn hàng theo State Machine kèm giao dịch an toàn (Transaction + Pessimistic Lock)
     * - Chuyển sang 4 (Đã hủy) hoặc 5 (Trả hàng): Tự động cộng trả lại tồn kho và trừ luotmua
     * - Chuyển sang 3 (Hoàn thành): Ghi nhận thành công
     *
     * @param int $orderId Mã đơn hàng
     * @param int $newStatus Mã trạng thái mới (0..5)
     * @param string|null $adminNote Ghi chú bổ sung từ quản trị viên
     * @return bool
     * @throws DomainException|RuntimeException
     */
    public function updateOrderStatus(int $orderId, int $newStatus, ?string $adminNote = null): bool {
        if ($newStatus < 0 || $newStatus > 5) {
            throw new InvalidArgumentException("Mã trạng thái đơn hàng không hợp lệ ({$newStatus}). Phải từ 0 đến 5.");
        }

        $pdo = DatabaseService::pdo();
        DatabaseService::beginTransaction();

        try {
            // 1. Khóa bi quan (FOR UPDATE) để chống Race Condition khi hai Admin cùng duyệt đơn
            $stmtOrder = $pdo->prepare("SELECT * FROM donhang WHERE id = :id FOR UPDATE");
            $stmtOrder->bindValue(':id', $orderId, PDO::PARAM_INT);
            $stmtOrder->execute();
            $order = $stmtOrder->fetch();

            if (!$order) {
                throw new DomainException("Đơn hàng #{$orderId} không tồn tại trong hệ thống.");
            }

            $currentStatus = (int)$order['trangthai'];

            // 2. Kiểm tra State Machine
            if (!$this->canTransition($currentStatus, $newStatus)) {
                $curMeta = getOrderStatusMeta($currentStatus);
                $newMeta = getOrderStatusMeta($newStatus);
                throw new DomainException("Quy tắc chuyển trạng thái không hợp lệ: Không thể chuyển từ '{$curMeta['label']}' sang '{$newMeta['label']}'.");
            }

            // Nếu trạng thái không đổi và không có note mới, kết thúc luôn
            if ($currentStatus === $newStatus && $adminNote === null) {
                DatabaseService::commit();
                return true;
            }

            // 3. Xử lý tồn kho khi ĐƠN BỊ HỦY (trangthai = 4) hoặc TRẢ HÀNG (trangthai = 5)
            // Chỉ hoàn kho nếu trạng thái trước đó là đang xử lý bình thường (0, 1, 2) hoặc (3 -> 5)
            $shouldRestoreStock = false;
            if (in_array($newStatus, [4, 5], true) && in_array($currentStatus, [0, 1, 2, 3], true)) {
                $shouldRestoreStock = true;
            }

            if ($shouldRestoreStock) {
                $stmtItems = $pdo->prepare("SELECT mathang_id, soluong FROM donhangct WHERE donhang_id = :id");
                $stmtItems->bindValue(':id', $orderId, PDO::PARAM_INT);
                $stmtItems->execute();
                $items = $stmtItems->fetchAll();

                $stmtUpdateStock = $pdo->prepare(
                    "UPDATE mathang 
                     SET soluongton = soluongton + :soluong,
                         luotmua = GREATEST(0, luotmua - :soluong)
                     WHERE id = :id"
                );

                foreach ($items as $it) {
                    $stmtUpdateStock->bindValue(':soluong', (int)$it['soluong'], PDO::PARAM_INT);
                    $stmtUpdateStock->bindValue(':id', (int)$it['mathang_id'], PDO::PARAM_INT);
                    $stmtUpdateStock->execute();
                }
            }

            // 4. Chuẩn bị ghi chú cập nhật
            $finalNote = $order['ghichu'] ?? '';
            if (!empty($adminNote)) {
                $timeTag = date('H:i d/m/Y');
                $user = $_SESSION['admin']['hoten'] ?? $_SESSION['nguoidung']['hoten'] ?? 'Admin';
                $append = "[{$timeTag} - {$user}]: {$adminNote}";
                $finalNote = !empty($finalNote) ? $finalNote . "\n" . $append : $append;
            }

            // 5. Cập nhật trạng thái đơn hàng
            $stmtUpdateOrder = $pdo->prepare(
                "UPDATE donhang 
                 SET trangthai = :trangthai, 
                     ghichu = :ghichu 
                 WHERE id = :id"
            );
            $stmtUpdateOrder->bindValue(':trangthai', $newStatus, PDO::PARAM_INT);
            $stmtUpdateOrder->bindValue(':ghichu', $finalNote, PDO::PARAM_STR);
            $stmtUpdateOrder->bindValue(':id', $orderId, PDO::PARAM_INT);
            $stmtUpdateOrder->execute();

            DatabaseService::commit();
            return true;

        } catch (Exception $e) {
            DatabaseService::rollBack();
            throw $e;
        }
    }

    /**
     * Xác nhận đã nhận tiền thanh toán VietQR cho đơn hàng
     * Cập nhật trangthaithanhtoan = 1
     * Nếu đơn hàng đang ở trạng thái 0 (Chờ xác nhận), tự động nâng sang 1 (Đã xác nhận & Chờ đóng gói)
     *
     * @param int $orderId
     * @param string|null $adminNote
     * @return bool
     * @throws DomainException|Exception
     */
    public function confirmVietQRPayment(int $orderId, ?string $adminNote = null): bool {
        $pdo = DatabaseService::pdo();
        DatabaseService::beginTransaction();

        try {
            $stmtOrder = $pdo->prepare("SELECT * FROM donhang WHERE id = :id FOR UPDATE");
            $stmtOrder->bindValue(':id', $orderId, PDO::PARAM_INT);
            $stmtOrder->execute();
            $order = $stmtOrder->fetch();

            if (!$order) {
                throw new DomainException("Đơn hàng #{$orderId} không tồn tại trong hệ thống.");
            }

            $currentStatus = (int)$order['trangthai'];
            // Tự động nâng trạng thái từ 0 -> 1 nếu đang chờ xác nhận
            $newStatus = ($currentStatus === 0) ? 1 : $currentStatus;

            // Ghi chú hệ thống
            $finalNote = $order['ghichu'] ?? '';
            $timeTag = date('H:i d/m/Y');
            $user = $_SESSION['admin']['hoten'] ?? $_SESSION['nguoidung']['hoten'] ?? 'Admin';
            $confirmMsg = "[{$timeTag} - {$user}]: Xác nhận đã nhận tiền chuyển khoản VietQR thành công.";
            if (!empty($adminNote)) {
                $confirmMsg .= " Ghi chú: " . $adminNote;
            }
            $finalNote = !empty($finalNote) ? $finalNote . "\n" . $confirmMsg : $confirmMsg;

            $stmtUpdate = $pdo->prepare(
                "UPDATE donhang 
                 SET trangthaithanhtoan = 1, 
                     trangthai = :trangthai, 
                     ghichu = :ghichu 
                 WHERE id = :id"
            );
            $stmtUpdate->bindValue(':trangthai', $newStatus, PDO::PARAM_INT);
            $stmtUpdate->bindValue(':ghichu', $finalNote, PDO::PARAM_STR);
            $stmtUpdate->bindValue(':id', $orderId, PDO::PARAM_INT);
            $stmtUpdate->execute();

            DatabaseService::commit();
            return true;

        } catch (Exception $e) {
            DatabaseService::rollBack();
            throw $e;
        }
    }
}
