<?php
/**
 * InventoryAdminService.php
 * Quản lý Kho Hàng 3NF: Tổng hợp tồn kho, Tạo phiếu nhập nhiều linh kiện & Hủy phiếu an toàn
 * Hệ thống Quản trị XAG Store - Giai đoạn 2
 */

require_once __DIR__ . '/DatabaseService.php';
require_once __DIR__ . '/../config/db.php';

class InventoryAdminService {

    /**
     * Báo cáo tổng hợp tồn kho toàn diện của tất cả linh kiện Drone XAG
     * Kết hợp dữ liệu từ: Bảng mặt hàng, Lịch sử nhập kho (ct_phieunhap), và Bán hàng (donhangct)
     *
     * @param array $filters [
     *   'danhmuc_id'   => int|null,
     *   'dongmay'      => string|null,
     *   'stock_status' => string|null, ('all', 'low', 'out', 'in_stock')
     *   'keyword'      => string|null,
     *   'limit'        => int,
     *   'offset'       => int
     * ]
     * @return array ['items' => [...], 'total' => int]
     */
    public function getInventorySummary(array $filters = []): array {
        $pdo = DatabaseService::pdo();

        $where = [];
        $params = [];

        // Lọc danh mục
        if (!empty($filters['danhmuc_id'])) {
            $where[] = "m.danhmuc_id = :danhmuc_id";
            $params[':danhmuc_id'] = (int)$filters['danhmuc_id'];
        }

        // Lọc dòng máy (XAG P100, P40, V40, R150...)
        if (!empty($filters['dongmay'])) {
            $where[] = "m.dongmay LIKE :dongmay";
            $params[':dongmay'] = "%" . trim($filters['dongmay']) . "%";
        }

        // Lọc theo tình trạng tồn kho
        if (!empty($filters['stock_status'])) {
            switch ($filters['stock_status']) {
                case 'low': // Tồn kho thấp (1..5)
                    $where[] = "m.soluongton > 0 AND m.soluongton <= 5";
                    break;
                case 'out': // Hết hàng (0)
                    $where[] = "m.soluongton = 0";
                    break;
                case 'in_stock': // Còn nhiều (> 5)
                    $where[] = "m.soluongton > 5";
                    break;
            }
        }

        // Tìm kiếm theo từ khóa
        if (!empty($filters['keyword'])) {
            $kw = '%' . trim($filters['keyword']) . '%';
            $where[] = "(m.tenmathang LIKE :kw_name OR m.dongmay LIKE :kw_model OR d.tendanhmuc LIKE :kw_cat)";
            $params[':kw_name'] = $kw;
            $params[':kw_model'] = $kw;
            $params[':kw_cat'] = $kw;
        }

        $whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

        // Đếm tổng số
        $countSql = "SELECT COUNT(*) 
                     FROM mathang m 
                     JOIN danhmuc d ON m.danhmuc_id = d.id 
                     {$whereClause}";
        $stmtCount = $pdo->prepare($countSql);
        foreach ($params as $k => $v) {
            $stmtCount->bindValue($k, $v);
        }
        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();

        $limit = isset($filters['limit']) ? max(1, (int)$filters['limit']) : 20;
        $offset = isset($filters['offset']) ? max(0, (int)$filters['offset']) : 0;

        // Truy vấn dữ liệu tổng hợp
        $sql = "SELECT m.id, m.tenmathang, m.hinhanh, m.giagoc, m.giaban, m.soluongton, m.dongmay, m.luotmua,
                       d.id AS danhmuc_id, d.tendanhmuc,
                       -- Tổng số lượng đã nhập từ phiếu nhập thành công (trangthai = 1)
                       COALESCE((
                            SELECT SUM(ctpn.soluong) 
                            FROM ct_phieunhap ctpn 
                            JOIN phieunhap pn ON ctpn.phieunhap_id = pn.id 
                            WHERE ctpn.mathang_id = m.id AND pn.trangthai = 1
                       ), 0) AS tong_da_nhap,
                       -- Tổng số lượng đã bán từ các đơn hàng hợp lệ (không tính đơn hủy 4 hoặc hoàn 5)
                       COALESCE((
                            SELECT SUM(dhct.soluong) 
                            FROM donhangct dhct 
                            JOIN donhang dh ON dhct.donhang_id = dh.id 
                            WHERE dhct.mathang_id = m.id AND dh.trangthai NOT IN (4, 5)
                       ), 0) AS tong_da_ban,
                       -- Tổng giá trị tồn kho theo giá vốn (giagoc * soluongton)
                       (m.giagoc * m.soluongton) AS gia_tri_ton_kho
                FROM mathang m
                JOIN danhmuc d ON m.danhmuc_id = d.id
                {$whereClause}
                ORDER BY m.soluongton ASC, m.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        foreach ($items as &$item) {
            $item['giagoc_fmt'] = formatVND($item['giagoc']);
            $item['giaban_fmt'] = formatVND($item['giaban']);
            $item['gia_tri_ton_kho_fmt'] = formatVND($item['gia_tri_ton_kho']);
            $ton = (int)$item['soluongton'];
            if ($ton === 0) {
                $item['stock_status_label'] = 'Hết hàng';
                $item['stock_status_class'] = 'bg-rose-100 text-rose-800';
            } elseif ($ton <= 5) {
                $item['stock_status_label'] = 'Cảnh báo tồn ít';
                $item['stock_status_class'] = 'bg-amber-100 text-amber-800';
            } else {
                $item['stock_status_label'] = 'Còn hàng';
                $item['stock_status_class'] = 'bg-emerald-100 text-emerald-800';
            }
        }

        return [
            'items'  => $items,
            'total'  => $total,
            'limit'  => $limit,
            'offset' => $offset
        ];
    }

    /**
     * Lấy danh sách lịch sử chứng từ nhập kho kèm chi tiết linh kiện
     *
     * @param array $filters [
     *   'from_date'  => string|null,
     *   'to_date'    => string|null,
     *   'trangthai'  => int|null,
     *   'search'     => string|null, (mã phiếu, nhà cung cấp, ghi chú)
     *   'limit'      => int,
     *   'offset'     => int
     * ]
     * @return array ['receipts' => [...], 'total' => int]
     */
    public function getImportReceipts(array $filters = []): array {
        $pdo = DatabaseService::pdo();

        $where = [];
        $params = [];

        if (isset($filters['trangthai']) && $filters['trangthai'] !== '' && $filters['trangthai'] !== null) {
            $where[] = "pn.trangthai = :trangthai";
            $params[':trangthai'] = (int)$filters['trangthai'];
        }

        if (!empty($filters['from_date'])) {
            $where[] = "DATE(pn.ngaynhap) >= :from_date";
            $params[':from_date'] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $where[] = "DATE(pn.ngaynhap) <= :to_date";
            $params[':to_date'] = $filters['to_date'];
        }

        if (!empty($filters['search'])) {
            $kw = '%' . trim($filters['search']) . '%';
            $where[] = "(pn.maphieunhap LIKE :search_code OR pn.nhacungcap LIKE :search_supplier OR nd.hoten LIKE :search_creator)";
            $params[':search_code'] = $kw;
            $params[':search_supplier'] = $kw;
            $params[':search_creator'] = $kw;
        }

        $whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

        // Đếm tổng số
        $countSql = "SELECT COUNT(*) 
                     FROM phieunhap pn 
                     JOIN nguoidung nd ON pn.nguoidung_id = nd.id 
                     {$whereClause}";
        $stmtCount = $pdo->prepare($countSql);
        foreach ($params as $k => $v) {
            $stmtCount->bindValue($k, $v);
        }
        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();

        $limit = isset($filters['limit']) ? max(1, (int)$filters['limit']) : 15;
        $offset = isset($filters['offset']) ? max(0, (int)$filters['offset']) : 0;

        $sql = "SELECT pn.id, pn.maphieunhap, pn.nguoidung_id, pn.nhacungcap, pn.ngaynhap, 
                       pn.tongtien, pn.trangthai, pn.ghichu, pn.ngaytao,
                       nd.hoten AS nguoi_lap, nd.email AS email_nguoi_lap
                FROM phieunhap pn
                JOIN nguoidung nd ON pn.nguoidung_id = nd.id
                {$whereClause}
                ORDER BY pn.ngaynhap DESC, pn.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $receipts = $stmt->fetchAll();

        // Lấy chi tiết mặt hàng cho từng phiếu nhập
        if (count($receipts) > 0) {
            $receiptIds = array_column($receipts, 'id');
            $inPlaceholders = implode(',', array_fill(0, count($receiptIds), '?'));

            $itemSql = "SELECT ct.id, ct.phieunhap_id, ct.mathang_id, ct.soluong, 
                               ct.dongianhap, ct.thanhtien, ct.ghichu,
                               m.tenmathang, m.hinhanh, m.dongmay
                        FROM ct_phieunhap ct
                        JOIN mathang m ON ct.mathang_id = m.id
                        WHERE ct.phieunhap_id IN ({$inPlaceholders})
                        ORDER BY ct.id ASC";

            $stmtItems = $pdo->prepare($itemSql);
            $stmtItems->execute($receiptIds);
            $items = $stmtItems->fetchAll();

            $itemsByReceipt = [];
            foreach ($items as $it) {
                $it['dongianhap_fmt'] = formatVND($it['dongianhap']);
                $it['thanhtien_fmt'] = formatVND($it['thanhtien']);
                $itemsByReceipt[$it['phieunhap_id']][] = $it;
            }

            foreach ($receipts as &$rc) {
                $rc['tongtien_fmt'] = formatVND($rc['tongtien']);
                $rc['ngaynhap_fmt'] = formatDateVN($rc['ngaynhap'], true);
                $rc['is_completed'] = ((int)$rc['trangthai'] === 1);
                $rc['items'] = $itemsByReceipt[$rc['id']] ?? [];
                $rc['total_items'] = count($rc['items']);
            }
        }

        return [
            'receipts' => $receipts,
            'total'    => $total,
            'limit'    => $limit,
            'offset'   => $offset
        ];
    }

    /**
     * Lấy chi tiết 1 phiếu nhập kho theo ID
     *
     * @param int $receiptId
     * @return array|null
     */
    public function getImportReceiptById(int $receiptId): ?array {
        $pdo = DatabaseService::pdo();

        $sql = "SELECT pn.id, pn.maphieunhap, pn.nguoidung_id, pn.nhacungcap, pn.ngaynhap, 
                       pn.tongtien, pn.trangthai, pn.ghichu, pn.ngaytao,
                       nd.hoten AS nguoi_lap, nd.email AS email_nguoi_lap, nd.sodienthoai AS sdt_nguoi_lap
                FROM phieunhap pn
                JOIN nguoidung nd ON pn.nguoidung_id = nd.id
                WHERE pn.id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $receiptId, PDO::PARAM_INT);
        $stmt->execute();
        $receipt = $stmt->fetch();

        if (!$receipt) {
            return null;
        }

        $itemSql = "SELECT ct.id, ct.phieunhap_id, ct.mathang_id, ct.soluong, 
                           ct.dongianhap, ct.thanhtien, ct.ghichu,
                           m.tenmathang, m.hinhanh, m.dongmay, m.soluongton
                    FROM ct_phieunhap ct
                    JOIN mathang m ON ct.mathang_id = m.id
                    WHERE ct.phieunhap_id = :id
                    ORDER BY ct.id ASC";

        $stmtItems = $pdo->prepare($itemSql);
        $stmtItems->bindValue(':id', $receiptId, PDO::PARAM_INT);
        $stmtItems->execute();
        $items = $stmtItems->fetchAll();

        foreach ($items as &$it) {
            $it['dongianhap_fmt'] = formatVND($it['dongianhap']);
            $it['thanhtien_fmt'] = formatVND($it['thanhtien']);
        }

        $receipt['tongtien_fmt'] = formatVND($receipt['tongtien']);
        $receipt['ngaynhap_fmt'] = formatDateVN($receipt['ngaynhap'], true);
        $receipt['items'] = $items;

        return $receipt;
    }

    /**
     * Tự động sinh mã chứng từ nhập kho duy nhất theo chuẩn PN-YYYYMMDD-XXX
     *
     * @return string
     */
    public function generateReceiptCode(): string {
        $pdo = DatabaseService::pdo();
        $datePrefix = date('Ymd');
        $codePrefix = "PN-{$datePrefix}-";

        $sql = "SELECT maphieunhap FROM phieunhap WHERE maphieunhap LIKE :prefix ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':prefix', "{$codePrefix}%");
        $stmt->execute();
        $lastCode = $stmt->fetchColumn();

        if ($lastCode) {
            $seq = (int)substr($lastCode, -3);
            $newSeq = str_pad((string)($seq + 1), 3, '0', STR_PAD_LEFT);
        } else {
            $newSeq = '001';
        }

        return "{$codePrefix}{$newSeq}";
    }

    /**
     * Lập phiếu nhập kho chứa nhiều mặt hàng linh kiện Drone XAG cùng lúc
     * Sử dụng Giao dịch an toàn (Transaction + Pessimistic Lock):
     * 1. Sinh mã phiếu nhập PN-YYYYMMDD-XXX
     * 2. Thêm bản ghi vào `phieunhap`
     * 3. Thêm chi tiết từng linh kiện vào `ct_phieunhap`
     * 4. Tự động cộng dồn `soluongton` và cập nhật `giagoc` mới nhất vào `mathang`
     *
     * @param array $headerData [
     *   'nhacungcap' => string,
     *   'ngaynhap'   => string|null, (mặc định now)
     *   'ghichu'     => string|null
     * ]
     * @param array $items Mảng các dòng sản phẩm: [
     *   ['mathang_id' => int, 'soluong' => int, 'dongianhap' => float, 'ghichu' => string|null],
     *   ...
     * ]
     * @param int $userId ID nhân viên/Admin lập phiếu
     * @return array ['success' => bool, 'receipt_id' => int, 'maphieunhap' => string]
     * @throws InvalidArgumentException|Exception
     */
    public function createImportReceipt(array $headerData, array $items, ?int $userId = null): array {
        if ($userId === null) {
            $userId = (int)($headerData['nguoidung_id'] ?? 0);
        }
        if (empty($headerData['nhacungcap'])) {
            throw new InvalidArgumentException("Tên nhà cung cấp / đơn vị phân phối không được để trống.");
        }

        if (empty($items) || !is_array($items)) {
            throw new InvalidArgumentException("Phiếu nhập kho phải chứa ít nhất một mặt hàng linh kiện.");
        }

        $pdo = DatabaseService::pdo();
        DatabaseService::beginTransaction();

        try {
            // 1. Kiểm tra tính hợp lệ và tính tổng tiền
            $totalAmount = 0.0;
            $validatedItems = [];
            $seenProductIds = [];

            foreach ($items as $idx => $it) {
                $productId = (int)($it['mathang_id'] ?? 0);
                $qty = (int)($it['soluong'] ?? 0);
                $unitPrice = (float)($it['dongianhap'] ?? 0);
                $itemNote = trim($it['ghichu'] ?? '');

                if ($productId <= 0) {
                    throw new InvalidArgumentException("Dòng số " . ($idx + 1) . ": Chưa chọn mặt hàng linh kiện.");
                }
                if ($qty <= 0) {
                    throw new InvalidArgumentException("Dòng số " . ($idx + 1) . ": Số lượng nhập kho phải lớn hơn 0.");
                }
                if ($unitPrice < 0) {
                    throw new InvalidArgumentException("Dòng số " . ($idx + 1) . ": Đơn giá nhập không được âm.");
                }
                if (in_array($productId, $seenProductIds, true)) {
                    throw new InvalidArgumentException("Linh kiện ID {$productId} xuất hiện nhiều lần trong cùng một phiếu nhập! Vui lòng gộp số lượng.");
                }

                $seenProductIds[] = $productId;
                $lineTotal = $qty * $unitPrice;
                $totalAmount += $lineTotal;

                $validatedItems[] = [
                    'mathang_id' => $productId,
                    'soluong'    => $qty,
                    'dongianhap' => $unitPrice,
                    'thanhtien'  => $lineTotal,
                    'ghichu'     => $itemNote
                ];
            }

            // 2. Sinh mã chứng từ duy nhất
            $receiptCode = $this->generateReceiptCode();
            $ngaynhap = !empty($headerData['ngaynhap']) ? $headerData['ngaynhap'] : date('Y-m-d H:i:s');
            $ghichu = trim($headerData['ghichu'] ?? '');

            // 3. Chèn vào bảng `phieunhap`
            $sqlReceipt = "INSERT INTO phieunhap (maphieunhap, nguoidung_id, nhacungcap, ngaynhap, tongtien, trangthai, ghichu)
                           VALUES (:maphieunhap, :nguoidung_id, :nhacungcap, :ngaynhap, :tongtien, 1, :ghichu)";
            $stmtReceipt = $pdo->prepare($sqlReceipt);
            $stmtReceipt->bindValue(':maphieunhap', $receiptCode);
            $stmtReceipt->bindValue(':nguoidung_id', $userId, PDO::PARAM_INT);
            $stmtReceipt->bindValue(':nhacungcap', trim($headerData['nhacungcap']));
            $stmtReceipt->bindValue(':ngaynhap', $ngaynhap);
            $stmtReceipt->bindValue(':tongtien', $totalAmount);
            $stmtReceipt->bindValue(':ghichu', $ghichu);
            $stmtReceipt->execute();

            $receiptId = (int)$pdo->lastInsertId();

            // 4. Chèn chi tiết và cập nhật tồn kho
            $sqlItem = "INSERT INTO ct_phieunhap (phieunhap_id, mathang_id, soluong, dongianhap, thanhtien, ghichu)
                        VALUES (:phieunhap_id, :mathang_id, :soluong, :dongianhap, :thanhtien, :ghichu)";
            $stmtItem = $pdo->prepare($sqlItem);

            $sqlLockProduct = "SELECT id, tenmathang, soluongton, giagoc FROM mathang WHERE id = :id FOR UPDATE";
            $stmtLock = $pdo->prepare($sqlLockProduct);

            $sqlUpdateProduct = "UPDATE mathang 
                                 SET soluongton = soluongton + :qty,
                                     giagoc = :giagoc
                                 WHERE id = :id";
            $stmtUpdateProduct = $pdo->prepare($sqlUpdateProduct);

            foreach ($validatedItems as $vItem) {
                // Chèn dòng chi tiết
                $stmtItem->execute([
                    ':phieunhap_id' => $receiptId,
                    ':mathang_id'   => $vItem['mathang_id'],
                    ':soluong'      => $vItem['soluong'],
                    ':dongianhap'   => $vItem['dongianhap'],
                    ':thanhtien'    => $vItem['thanhtien'],
                    ':ghichu'       => $vItem['ghichu']
                ]);

                // Khóa dòng sản phẩm và cộng dồn tồn kho
                $stmtLock->execute([':id' => $vItem['mathang_id']]);
                $product = $stmtLock->fetch();

                if (!$product) {
                    throw new DomainException("Mặt hàng linh kiện ID {$vItem['mathang_id']} không tồn tại!");
                }

                // Cập nhật giá gốc mới nhất nếu giá nhập > 0
                $newGiaGoc = $vItem['dongianhap'] > 0 ? $vItem['dongianhap'] : $product['giagoc'];
                $stmtUpdateProduct->execute([
                    ':qty'    => $vItem['soluong'],
                    ':giagoc' => $newGiaGoc,
                    ':id'     => $vItem['mathang_id']
                ]);
            }

            DatabaseService::commit();

            return [
                'success'      => true,
                'receipt_id'   => $receiptId,
                'maphieunhap'  => $receiptCode,
                'total_amount' => $totalAmount
            ];

        } catch (Exception $e) {
            DatabaseService::rollBack();
            throw $e;
        }
    }

    /**
     * Hủy phiếu nhập kho (trangthai = 0) và hoàn trừ lại số lượng tồn kho tương ứng
     * Kiểm tra đảm bảo không để tồn kho bị âm sau khi hoàn trừ
     *
     * @param int $receiptId
     * @return bool
     * @throws DomainException|Exception
     */
    public function cancelImportReceipt(int $receiptId): bool {
        $pdo = DatabaseService::pdo();
        DatabaseService::beginTransaction();

        try {
            // 1. Khóa phiếu nhập
            $stmtReceipt = $pdo->prepare("SELECT * FROM phieunhap WHERE id = :id FOR UPDATE");
            $stmtReceipt->bindValue(':id', $receiptId, PDO::PARAM_INT);
            $stmtReceipt->execute();
            $receipt = $stmtReceipt->fetch();

            if (!$receipt) {
                throw new DomainException("Phiếu nhập kho #{$receiptId} không tồn tại.");
            }

            if ((int)$receipt['trangthai'] === 0) {
                throw new DomainException("Phiếu nhập kho {$receipt['maphieunhap']} đã bị hủy trước đó!");
            }

            // 2. Lấy chi tiết các mặt hàng trong phiếu
            $stmtItems = $pdo->prepare("SELECT mathang_id, soluong FROM ct_phieunhap WHERE phieunhap_id = :id");
            $stmtItems->bindValue(':id', $receiptId, PDO::PARAM_INT);
            $stmtItems->execute();
            $items = $stmtItems->fetchAll();

            // 3. Khóa và kiểm tra tồn kho từng mặt hàng
            $stmtLock = $pdo->prepare("SELECT id, tenmathang, soluongton FROM mathang WHERE id = :id FOR UPDATE");
            $stmtDeduct = $pdo->prepare("UPDATE mathang SET soluongton = soluongton - :soluong WHERE id = :id");

            foreach ($items as $it) {
                $mId = (int)$it['mathang_id'];
                $qty = (int)$it['soluong'];

                $stmtLock->bindValue(':id', $mId, PDO::PARAM_INT);
                $stmtLock->execute();
                $prod = $stmtLock->fetch();

                if (!$prod) {
                    throw new DomainException("Mặt hàng ID {$mId} không tồn tại trong hệ thống.");
                }

                $currentStock = (int)$prod['soluongton'];
                if ($currentStock < $qty) {
                    throw new DomainException(
                        "Không thể hủy phiếu nhập! Mặt hàng '{$prod['tenmathang']}' hiện chỉ còn {$currentStock} kiện trong kho, " .
                        "không đủ để trừ lại {$qty} kiện hàng đã xuất bán cho khách hàng."
                    );
                }

                $stmtDeduct->bindValue(':soluong', $qty, PDO::PARAM_INT);
                $stmtDeduct->bindValue(':id', $mId, PDO::PARAM_INT);
                $stmtDeduct->execute();
            }

            // 4. Đổi trạng thái phiếu nhập sang 0 (Đã hủy)
            $stmtCancel = $pdo->prepare("UPDATE phieunhap SET trangthai = 0 WHERE id = :id");
            $stmtCancel->bindValue(':id', $receiptId, PDO::PARAM_INT);
            $stmtCancel->execute();

            DatabaseService::commit();
            return true;

        } catch (Exception $e) {
            DatabaseService::rollBack();
            throw $e;
        }
    }
}
