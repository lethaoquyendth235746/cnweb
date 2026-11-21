<?php
/**
 * CategoryAdminService.php
 * Quản lý Danh mục Linh kiện Drone XAG & Kiểm tra Ràng buộc Khóa Ngoại
 * Hệ thống Quản trị XAG Store - Giai đoạn 2
 */

require_once __DIR__ . '/DatabaseService.php';
require_once __DIR__ . '/../config/db.php';

class CategoryAdminService {

    /**
     * Lấy danh sách tất cả các danh mục kèm tổng số lượng linh kiện thuộc danh mục
     *
     * @return array
     */
    public function getAllCategoriesWithCount(): array {
        $pdo = DatabaseService::pdo();

        $sql = "SELECT d.id, d.tendanhmuc, 
                       COUNT(m.id) AS tong_san_pham,
                       COALESCE(SUM(m.soluongton), 0) AS tong_ton_kho
                FROM danhmuc d
                LEFT JOIN mathang m ON d.id = m.danhmuc_id
                GROUP BY d.id
                ORDER BY d.id ASC";

        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Lấy thông tin 1 danh mục theo ID
     *
     * @param int $id
     * @return array|null
     */
    public function getCategoryById(int $id): ?array {
        $pdo = DatabaseService::pdo();

        $stmt = $pdo->prepare("SELECT * FROM danhmuc WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $cat = $stmt->fetch();

        return $cat ?: null;
    }

    /**
     * Tạo mới một danh mục linh kiện Drone XAG
     *
     * @param string $tenDanhMuc
     * @return int ID của danh mục mới tạo
     * @throws InvalidArgumentException
     */
    public function createCategory(string $tenDanhMuc): int {
        $name = trim($tenDanhMuc);
        if (empty($name)) {
            throw new InvalidArgumentException("Tên danh mục linh kiện không được để trống.");
        }

        $pdo = DatabaseService::pdo();

        // Kiểm tra trùng tên danh mục
        $stmtCheck = $pdo->prepare("SELECT id FROM danhmuc WHERE LOWER(tendanhmuc) = LOWER(:name)");
        $stmtCheck->bindValue(':name', $name);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            throw new InvalidArgumentException("Danh mục '{$name}' đã tồn tại trong hệ thống.");
        }

        $stmt = $pdo->prepare("INSERT INTO danhmuc (tendanhmuc) VALUES (:tendanhmuc)");
        $stmt->bindValue(':tendanhmuc', $name);
        $stmt->execute();

        return (int)$pdo->lastInsertId();
    }

    /**
     * Cập nhật tên danh mục linh kiện
     *
     * @param int $id
     * @param string $tenDanhMuc
     * @return bool
     * @throws DomainException|InvalidArgumentException
     */
    public function updateCategory(int $id, string $tenDanhMuc): bool {
        $existing = $this->getCategoryById($id);
        if (!$existing) {
            throw new DomainException("Danh mục ID {$id} không tồn tại.");
        }

        $name = trim($tenDanhMuc);
        if (empty($name)) {
            throw new InvalidArgumentException("Tên danh mục không được để trống.");
        }

        $pdo = DatabaseService::pdo();

        // Kiểm tra trùng tên với danh mục khác
        $stmtCheck = $pdo->prepare("SELECT id FROM danhmuc WHERE LOWER(tendanhmuc) = LOWER(:name) AND id != :id");
        $stmtCheck->bindValue(':name', $name);
        $stmtCheck->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtCheck->execute();
        if ($stmtCheck->fetch()) {
            throw new InvalidArgumentException("Tên danh mục '{$name}' đã được sử dụng bởi danh mục khác.");
        }

        $stmt = $pdo->prepare("UPDATE danhmuc SET tendanhmuc = :tendanhmuc WHERE id = :id");
        $stmt->bindValue(':tendanhmuc', $name);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Xóa danh mục linh kiện khỏi hệ thống
     * Ngăn chặn xóa nếu còn sản phẩm linh kiện liên kết
     *
     * @param int $id
     * @return bool
     * @throws DomainException
     */
    public function deleteCategory(int $id): bool {
        $existing = $this->getCategoryById($id);
        if (!$existing) {
            throw new DomainException("Danh mục ID {$id} không tồn tại.");
        }

        $pdo = DatabaseService::pdo();

        // Kiểm tra xem danh mục có chứa mặt hàng nào không
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM mathang WHERE danhmuc_id = :id");
        $stmtCheck->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $productCount = (int)$stmtCheck->fetchColumn();

        if ($productCount > 0) {
            throw new DomainException(
                "Không thể xóa danh mục '{$existing['tendanhmuc']}' vì hiện đang có {$productCount} linh kiện Drone thuộc danh mục này! " .
                "Vui lòng chuyển các linh kiện sang danh mục khác trước khi xóa."
            );
        }

        $stmt = $pdo->prepare("DELETE FROM danhmuc WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
