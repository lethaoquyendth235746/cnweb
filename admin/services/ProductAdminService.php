<?php
/**
 * ProductAdminService.php
 * Quản lý toàn diện Danh mục Linh kiện Drone XAG: CRUD, Hỗ trợ cột dongmay & Upload ảnh an toàn
 * Hệ thống Quản trị XAG Store - Giai đoạn 2
 */

require_once __DIR__ . '/DatabaseService.php';
require_once __DIR__ . '/../config/db.php';

class ProductAdminService {

    /**
     * Danh sách các dòng máy Drone nông nghiệp XAG chính thức
     */
    public const SUPPORTED_DRONE_MODELS = [
        'XAG P100',
        'XAG P100 Pro',
        'XAG P40',
        'XAG V40',
        'XAG R150'
    ];

    /**
     * Lấy danh sách tên các dòng máy bay XAG (chuỗi tên phục vụ tương thích)
     */
    public function getDroneModels(): array {
        try {
            $all = $this->getAllDongMay();
            if (!empty($all)) {
                return array_column($all, 'tendongmay');
            }
        } catch (Throwable $e) {
            // fallback nếu chưa nạp schema
        }
        return self::SUPPORTED_DRONE_MODELS;
    }

    /**
     * Lấy danh sách mặt hàng có hỗ trợ tìm kiếm, lọc danh mục, lọc dòng máy và phân trang
     *
     * @param array $filters [
     *   'search'      => string|null,
     *   'danhmuc_id'  => int|null,
     *   'dongmay'     => string|null,
     *   'sort_by'     => string|null, ('id', 'giaban', 'soluongton', 'luotmua', 'luotxem')
     *   'sort_dir'    => string|null, ('ASC', 'DESC')
     *   'limit'       => int,
     *   'offset'      => int
     * ]
     * @return array ['products' => [...], 'total' => int]
     */
    public function getAllProducts(array $filters = []): array {
        $pdo = DatabaseService::pdo();

        $where = [];
        $params = [];

        // 1. Tìm kiếm từ khóa
        if (!empty($filters['search'])) {
            $kw = trim($filters['search']);
            $where[] = "(m.tenmathang LIKE :kw OR m.mota LIKE :kw OR m.dongmay LIKE :kw)";
            $params[':kw'] = "%{$kw}%";
        }

        // 2. Lọc theo danh mục
        if (!empty($filters['danhmuc_id'])) {
            $where[] = "m.danhmuc_id = :danhmuc_id";
            $params[':danhmuc_id'] = (int)$filters['danhmuc_id'];
        }

        // 3. Lọc theo dòng máy
        if (!empty($filters['dongmay'])) {
            $where[] = "m.dongmay LIKE :dongmay";
            $params[':dongmay'] = "%" . trim($filters['dongmay']) . "%";
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

        // Xử lý sắp xếp
        $allowedSortCols = [
            'id'         => 'm.id',
            'tenmathang' => 'm.tenmathang',
            'giaban'     => 'm.giaban',
            'giagoc'     => 'm.giagoc',
            'soluongton' => 'm.soluongton',
            'luotmua'    => 'm.luotmua',
            'luotxem'    => 'm.luotxem'
        ];
        $sortBy = $allowedSortCols[$filters['sort_by'] ?? ''] ?? 'm.id';
        $sortDir = strtoupper($filters['sort_dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        $limit = isset($filters['limit']) ? max(1, (int)$filters['limit']) : 20;
        $offset = isset($filters['offset']) ? max(0, (int)$filters['offset']) : 0;

        $sql = "SELECT m.id, m.tenmathang, m.mota, m.giagoc, m.giaban, m.soluongton, 
                       m.hinhanh, m.danhmuc_id, m.dongmay, m.luotxem, m.luotmua,
                       d.tendanhmuc
                FROM mathang m
                JOIN danhmuc d ON m.danhmuc_id = d.id
                {$whereClause}
                ORDER BY {$sortBy} {$sortDir}
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();

        foreach ($products as &$p) {
            $p['giagoc_fmt'] = formatVND($p['giagoc']);
            $p['giaban_fmt'] = formatVND($p['giaban']);
            $p['is_low_stock'] = ((int)$p['soluongton'] <= 5);
            $p['is_out_of_stock'] = ((int)$p['soluongton'] === 0);
        }

        return [
            'products' => $products,
            'total'    => $total,
            'limit'    => $limit,
            'offset'   => $offset
        ];
    }

    /**
     * Lấy thông tin chi tiết một linh kiện theo ID
     *
     * @param int $id
     * @return array|null
     */
    public function getProductById(int $id): ?array {
        $pdo = DatabaseService::pdo();

        $sql = "SELECT m.id, m.tenmathang, m.mota, m.giagoc, m.giaban, m.soluongton, 
                       m.hinhanh, m.danhmuc_id, m.dongmay, m.luotxem, m.luotmua,
                       d.tendanhmuc
                FROM mathang m
                JOIN danhmuc d ON m.danhmuc_id = d.id
                WHERE m.id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch();

        if ($product) {
            $product['giagoc_fmt'] = formatVND($product['giagoc']);
            $product['giaban_fmt'] = formatVND($product['giaban']);
        }

        return $product ?: null;
    }

    /**
     * Thêm mới mặt hàng linh kiện Drone XAG
     *
     * @param array $data [
     *   'tenmathang'  => string,
     *   'mota'        => string|null,
     *   'giagoc'      => float,
     *   'giaban'      => float,
     *   'soluongton'  => int,
     *   'danhmuc_id'  => int,
     *   'dongmay'     => string|array|null, (chuỗi hoặc mảng các model)
     *   'hinhanh'     => string|null (nếu không upload file)
     * ]
     * @param array|null $imageFile Mảng file upload từ $_FILES['hinhanh']
     * @return int ID bản ghi mới tạo
     * @throws InvalidArgumentException|Exception
     */
    public function createProduct(array $data, ?array $imageFile = null): int {
        $this->validateProductData($data);

        $pdo = DatabaseService::pdo();

        // Xử lý upload ảnh nếu có
        $imagePath = $this->handleImageUpload($imageFile);
        if ($imagePath === null && !empty($data['hinhanh'])) {
            $imagePath = trim($data['hinhanh']);
        }
        if (empty($imagePath)) {
            $imagePath = 'images/products/no_image.png';
        }

        // Xử lý dòng máy
        $dongmay = $this->formatDongMay($data['dongmay'] ?? null);

        $sql = "INSERT INTO mathang (tenmathang, mota, giagoc, giaban, soluongton, hinhanh, danhmuc_id, dongmay, luotxem, luotmua)
                VALUES (:tenmathang, :mota, :giagoc, :giaban, :soluongton, :hinhanh, :danhmuc_id, :dongmay, 0, 0)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tenmathang', trim($data['tenmathang']));
        $stmt->bindValue(':mota', $data['mota'] ?? null);
        $stmt->bindValue(':giagoc', (float)($data['giagoc'] ?? 0));
        $stmt->bindValue(':giaban', (float)$data['giaban']);
        $stmt->bindValue(':soluongton', max(0, (int)($data['soluongton'] ?? 0)), PDO::PARAM_INT);
        $stmt->bindValue(':hinhanh', $imagePath);
        $stmt->bindValue(':danhmuc_id', (int)$data['danhmuc_id'], PDO::PARAM_INT);
        $stmt->bindValue(':dongmay', $dongmay);
        $stmt->execute();

        return (int)$pdo->lastInsertId();
    }

    /**
     * Cập nhật thông tin linh kiện Drone XAG
     *
     * @param int $id ID sản phẩm cần cập nhật
     * @param array $data Thông tin cập nhật
     * @param array|null $imageFile File ảnh upload mới nếu có
     * @return bool
     * @throws DomainException|InvalidArgumentException|Exception
     */
    public function updateProduct(int $id, array $data, ?array $imageFile = null): bool {
        $existing = $this->getProductById($id);
        if (!$existing) {
            throw new DomainException("Mặt hàng linh kiện ID {$id} không tồn tại.");
        }

        $this->validateProductData($data);

        $pdo = DatabaseService::pdo();

        // Xử lý ảnh: nếu có upload ảnh mới thì lấy đường dẫn mới, ngược lại giữ ảnh cũ
        $newImagePath = $this->handleImageUpload($imageFile);
        $imagePath = $newImagePath ?? ($data['hinhanh'] ?? $existing['hinhanh']);

        $dongmay = $this->formatDongMay($data['dongmay'] ?? $existing['dongmay']);

        $sql = "UPDATE mathang 
                SET tenmathang = :tenmathang,
                    mota = :mota,
                    giagoc = :giagoc,
                    giaban = :giaban,
                    soluongton = :soluongton,
                    hinhanh = :hinhanh,
                    danhmuc_id = :danhmuc_id,
                    dongmay = :dongmay
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':tenmathang', trim($data['tenmathang']));
        $stmt->bindValue(':mota', $data['mota'] ?? null);
        $stmt->bindValue(':giagoc', (float)($data['giagoc'] ?? 0));
        $stmt->bindValue(':giaban', (float)$data['giaban']);
        $stmt->bindValue(':soluongton', max(0, (int)($data['soluongton'] ?? 0)), PDO::PARAM_INT);
        $stmt->bindValue(':hinhanh', $imagePath);
        $stmt->bindValue(':danhmuc_id', (int)$data['danhmuc_id'], PDO::PARAM_INT);
        $stmt->bindValue(':dongmay', $dongmay);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Xóa một mặt hàng linh kiện khỏi CSDL
     * Kiểm tra toàn vẹn dữ liệu: Chặn xóa nếu sản phẩm đã phát sinh trong đơn hàng hoặc phiếu nhập kho
     *
     * @param int $id ID sản phẩm
     * @return bool
     * @throws DomainException|Exception
     */
    public function deleteProduct(int $id): bool {
        $existing = $this->getProductById($id);
        if (!$existing) {
            throw new DomainException("Mặt hàng linh kiện ID {$id} không tồn tại.");
        }

        $pdo = DatabaseService::pdo();

        // 1. Kiểm tra trong chi tiết đơn hàng (donhangct)
        $stmtOrder = $pdo->prepare("SELECT COUNT(*) FROM donhangct WHERE mathang_id = :id");
        $stmtOrder->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtOrder->execute();
        $orderCount = (int)$stmtOrder->fetchColumn();

        // 2. Kiểm tra trong chi tiết phiếu nhập (ct_phieunhap)
        $stmtImport = $pdo->prepare("SELECT COUNT(*) FROM ct_phieunhap WHERE mathang_id = :id");
        $stmtImport->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtImport->execute();
        $importCount = (int)$stmtImport->fetchColumn();

        if ($orderCount > 0 || $importCount > 0) {
            $reason = [];
            if ($orderCount > 0) {
                $reason[] = "{$orderCount} đơn hàng";
            }
            if ($importCount > 0) {
                $reason[] = "{$importCount} phiếu nhập kho";
            }
            $reasonStr = implode(" và ", $reason);

            throw new DomainException(
                "Không thể xóa linh kiện '{$existing['tenmathang']}' vì đã phát sinh trong {$reasonStr}! " .
                "Để ngừng bán, bạn vui lòng chuyển số lượng tồn kho về 0."
            );
        }

        // 3. Thực hiện xóa an toàn
        $stmtDelete = $pdo->prepare("DELETE FROM mathang WHERE id = :id");
        $stmtDelete->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmtDelete->execute();
    }

    /**
     * Chuẩn hóa dữ liệu dòng máy thành chuỗi ngăn cách dấu phẩy
     */
    private function formatDongMay($dongmay): ?string {
        if (is_array($dongmay)) {
            $cleaned = array_filter(array_map('trim', $dongmay));
            return count($cleaned) > 0 ? implode(', ', $cleaned) : null;
        }
        if (is_string($dongmay)) {
            $trimmed = trim($dongmay);
            return $trimmed !== '' ? $trimmed : null;
        }
        return null;
    }

    /**
     * Xác thực tính hợp lệ của dữ liệu đầu vào sản phẩm
     */
    private function validateProductData(array $data): void {
        if (empty($data['tenmathang'])) {
            throw new InvalidArgumentException("Tên linh kiện Drone không được để trống.");
        }
        if (empty($data['danhmuc_id']) || (int)$data['danhmuc_id'] <= 0) {
            throw new InvalidArgumentException("Vui lòng chọn danh mục linh kiện hợp lệ.");
        }
        if (!isset($data['giaban']) || (float)$data['giaban'] <= 0) {
            throw new InvalidArgumentException("Giá bán lẻ linh kiện phải lớn hơn 0 VNĐ.");
        }
    }

    /**
     * Xử lý upload ảnh sản phẩm an toàn vào thư mục images/products/
     *
     * @param array|null $file Mảng $_FILES['hinhanh']
     * @return string|null Đường dẫn tương đối lưu trong CSDL (ví dụ 'images/products/prod_1700000000_abcd.jpg')
     * @throws RuntimeException
     */
    public function handleImageUpload(?array $file): ?string {
        if (!$file || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException("Lỗi khi tải ảnh lên (Mã lỗi: {$file['error']}).");
        }

        // Giới hạn 5MB
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            throw new RuntimeException("Kích thước tệp ảnh vượt quá giới hạn cho phép (tối đa 5MB).");
        }

        // Kiểm tra loại tệp an toàn bằng finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/pjpeg'=> 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($allowedMimes[$mime])) {
            throw new RuntimeException("Định dạng tệp không được hỗ trợ ({$mime}). Chỉ chấp nhận JPG, PNG hoặc WEBP.");
        }

        $ext = $allowedMimes[$mime];
        $filename = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        // Thư mục lưu trữ vật lý
        $targetDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'products';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Thử copy dự phòng nếu môi trường không cho move_uploaded_file (như CLI mock)
            if (!copy($file['tmp_name'], $targetPath)) {
                throw new RuntimeException("Không thể lưu tệp ảnh vào thư mục đích: {$targetPath}");
            }
        }

        // Trả về đường dẫn tương đối nhất quán với DB hiện hành
        return 'images/products/' . $filename;
    }

    // =========================================================================
    // QUẢN LÝ DÒNG MÁY TƯƠNG THÍCH (DONGMAY) - CRUD ĐỘNG
    // =========================================================================

    /**
     * Lấy danh sách tất cả dòng máy từ bảng dongmay kèm số lượng linh kiện đang liên kết
     *
     * @return array [ ['id' => int, 'tendongmay' => string, 'tong_san_pham' => int], ... ]
     */
    public function getAllDongMay(): array {
        $pdo = DatabaseService::pdo();
        $sql = "SELECT dm.id, dm.tendongmay, COUNT(m.id) AS tong_san_pham
                FROM dongmay dm
                LEFT JOIN mathang m ON (m.dongmay = dm.tendongmay)
                GROUP BY dm.id, dm.tendongmay
                ORDER BY dm.tendongmay ASC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin một dòng máy theo ID
     */
    public function getDongMayById(int $id): ?array {
        $pdo = DatabaseService::pdo();
        $stmt = $pdo->prepare("SELECT * FROM dongmay WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    /**
     * Thêm dòng máy Drone mới
     *
     * @throws InvalidArgumentException
     */
    public function addDongMay(string $tenDongMay): int {
        $name = trim($tenDongMay);
        if ($name === '') {
            throw new InvalidArgumentException("Tên dòng máy không được để trống.");
        }

        if (mb_strlen($name) > 100) {
            throw new InvalidArgumentException("Tên dòng máy không được vượt quá 100 ký tự.");
        }

        $pdo = DatabaseService::pdo();
        // Kiểm tra trùng tên không phân biệt hoa thường
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM dongmay WHERE LOWER(tendongmay) = LOWER(:name)");
        $stmtCheck->execute([':name' => $name]);
        if ((int)$stmtCheck->fetchColumn() > 0) {
            throw new InvalidArgumentException("Dòng máy '{$name}' đã tồn tại trong hệ thống.");
        }

        $stmt = $pdo->prepare("INSERT INTO dongmay (tendongmay) VALUES (:name)");
        $stmt->execute([':name' => $name]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Đổi tên dòng máy và đồng bộ sang cột dongmay của bảng mathang trong cùng Transaction
     *
     * @throws InvalidArgumentException|RuntimeException
     */
    public function updateDongMay(int $id, string $tenMoi): bool {
        $name = trim($tenMoi);
        if ($name === '') {
            throw new InvalidArgumentException("Tên dòng máy mới không được để trống.");
        }

        $pdo = DatabaseService::pdo();

        $cur = $this->getDongMayById($id);
        if (!$cur) {
            throw new InvalidArgumentException("Dòng máy #{$id} không tồn tại.");
        }

        $oldName = $cur['tendongmay'];
        if ($oldName === $name) {
            return true;
        }

        // Kiểm tra tên mới có bị trùng dòng máy khác không
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM dongmay WHERE LOWER(tendongmay) = LOWER(:name) AND id != :id");
        $stmtCheck->execute([':name' => $name, ':id' => $id]);
        if ((int)$stmtCheck->fetchColumn() > 0) {
            throw new InvalidArgumentException("Dòng máy '{$name}' đã tồn tại.");
        }

        DatabaseService::beginTransaction();
        try {
            // 1. Cập nhật bảng dongmay
            $stmt1 = $pdo->prepare("UPDATE dongmay SET tendongmay = :name WHERE id = :id");
            $stmt1->execute([':name' => $name, ':id' => $id]);

            // 2. Đồng bộ các mặt hàng đang dùng chuỗi tên cũ
            $stmt2 = $pdo->prepare("UPDATE mathang SET dongmay = :newName WHERE dongmay = :oldName");
            $stmt2->execute([':newName' => $name, ':oldName' => $oldName]);

            DatabaseService::commit();
            return true;
        } catch (Throwable $e) {
            DatabaseService::rollBack();
            throw new RuntimeException("Lỗi cập nhật dòng máy: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Xóa dòng máy. Tự động gỡ nhãn dòng máy khỏi các linh kiện đang gắn (chuyển về NULL / Dùng chung)
     *
     * @throws InvalidArgumentException|RuntimeException
     */
    public function deleteDongMay(int $id): bool {
        $pdo = DatabaseService::pdo();

        $cur = $this->getDongMayById($id);
        if (!$cur) {
            throw new InvalidArgumentException("Dòng máy #{$id} không tồn tại.");
        }

        $name = $cur['tendongmay'];

        DatabaseService::beginTransaction();
        try {
            // 1. Gỡ nhãn dòng máy ở bảng mathang
            $stmt1 = $pdo->prepare("UPDATE mathang SET dongmay = NULL WHERE dongmay = :name");
            $stmt1->execute([':name' => $name]);

            // 2. Xóa khỏi bảng dongmay
            $stmt2 = $pdo->prepare("DELETE FROM dongmay WHERE id = :id");
            $stmt2->execute([':id' => $id]);

            DatabaseService::commit();
            return true;
        } catch (Throwable $e) {
            DatabaseService::rollBack();
            throw new RuntimeException("Lỗi xóa dòng máy: " . $e->getMessage(), 0, $e);
        }
    }
}
