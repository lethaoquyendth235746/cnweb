<?php
/**
 * DatabaseService.php
 * Hạ tầng kết nối Cơ sở dữ liệu Singleton & Quản lý Giao dịch (Transaction Manager)
 * Hệ thống Quản trị XAG Store - Giai đoạn 2
 */

class DatabaseService {
    private static ?DatabaseService $instance = null;
    private ?PDO $pdo = null;

    private string $host = 'localhost';
    private string $dbname = 'xag_store';
    private string $username = 'root';
    private string $password = '';
    private int $port = 3306;
    private string $charset = 'utf8mb4';

    /**
     * Khởi tạo Private theo mô hình Singleton Pattern
     */
    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};port={$this->port};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset} COLLATE {$this->charset}_unicode_ci"
        ];

        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            // Auto migration: đảm bảo các cột và bảng cần thiết tồn tại (chạy 1 lần mỗi request)
            static $migrationRun = false;
            if (!$migrationRun) {
                $migrationRun = true;
                try {
                    $sqlFile = realpath(__DIR__ . '/../../data/update_admin_features.sql');
                    if ($sqlFile && file_exists($sqlFile)) {
                        $sql = file_get_contents($sqlFile);
                        // Thực thi toàn bộ script; MySQL cho phép multiple statements
                        $this->pdo->exec($sql);
                    }
                } catch (Exception $e) {
                    error_log('Auto migration failed: ' . $e->getMessage());
                }

                // Tự động kiểm tra và khởi tạo bảng dongmay nếu chưa tồn tại
                try {
                    $this->initDongMaySchema();
                } catch (Exception $e) {
                    error_log('Init dongmay schema failed: ' . $e->getMessage());
                }

                // Tự động kiểm tra và bổ sung cột thanh toán VietQR cho bảng donhang nếu chưa tồn tại
                try {
                    $this->initPaymentSchema();
                } catch (Exception $e) {
                    error_log('Init payment schema failed: ' . $e->getMessage());
                }

                // Tự động kiểm tra và khởi tạo bảng lienhe nếu chưa tồn tại
                try {
                    $this->initLienHeSchema();
                } catch (Exception $e) {
                    error_log('Init lienhe schema failed: ' . $e->getMessage());
                }
            }
        } catch (PDOException $e) {
            throw new RuntimeException("Lỗi kết nối CSDL XAG Store: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Chặn sao chép đối tượng (Clone)
     */
    private function __clone() {}

    /**
     * Chặn unserialize đối tượng
     */
    public function __wakeup() {
        throw new RuntimeException("Không thể unserialize DatabaseService Singleton");
    }

    /**
     * Lấy instance duy nhất của DatabaseService
     */
    public static function getInstance(): DatabaseService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Lấy đối tượng PDO trực tiếp
     */
    public static function pdo(): PDO {
        return self::getInstance()->getConnection();
    }

    /**
     * Lấy đối tượng PDO từ instance
     */
    public function getConnection(): PDO {
        return $this->pdo;
    }

    /**
     * Bắt đầu một giao dịch an toàn (Database Transaction)
     */
    public static function beginTransaction(): bool {
        $pdo = self::pdo();
        if (!$pdo->inTransaction()) {
            return $pdo->beginTransaction();
        }
        return true;
    }

    /**
     * Cam kết xác nhận giao dịch (Commit)
     */
    public static function commit(): bool {
        $pdo = self::pdo();
        if ($pdo->inTransaction()) {
            return $pdo->commit();
        }
        return false;
    }

    /**
     * Hủy bỏ / hoàn nguyên giao dịch (RollBack)
     */
    public static function rollBack(): bool {
        $pdo = self::pdo();
        if ($pdo->inTransaction()) {
            return $pdo->rollBack();
        }
        return false;
    }

    /**
     * Kiểm tra trạng thái đang trong giao dịch
     */
    public static function inTransaction(): bool {
        return self::pdo()->inTransaction();
    }

    /**
     * Lấy ID vừa được chèn tự động gần nhất
     */
    public static function lastInsertId(?string $name = null): string|false {
        return self::pdo()->lastInsertId($name);
    }

    /**
     * Tự động khởi tạo cấu trúc và dữ liệu mẫu cho bảng dòng máy (dongmay)
     */
    private function initDongMaySchema(): void {
        $sql = "CREATE TABLE IF NOT EXISTS `dongmay` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `tendongmay` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_tendongmay` (`tendongmay`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->pdo->exec($sql);

        // Kiểm tra xem bảng dongmay có dữ liệu chưa
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM `dongmay`");
        $count = (int)$stmt->fetchColumn();

        if ($count === 0) {
            // Danh sách cơ bản ban đầu
            $defaultModels = [
                'XAG P100',
                'XAG P100 Pro',
                'XAG P40',
                'XAG V40',
                'XAG R150'
            ];

            // Thu thập thêm các dòng máy đang có trong bảng mathang
            try {
                $stmtCheck = $this->pdo->query("SELECT DISTINCT TRIM(`dongmay`) AS dm FROM `mathang` WHERE `dongmay` IS NOT NULL AND TRIM(`dongmay`) != ''");
                while ($row = $stmtCheck->fetch()) {
                    $val = trim($row['dm'] ?? '');
                    if ($val !== '' && !in_array($val, $defaultModels, true)) {
                        $defaultModels[] = $val;
                    }
                }
            } catch (Throwable $e) {
                // Bỏ qua nếu bảng mathang chưa có dữ liệu
            }

            $insertStmt = $this->pdo->prepare("INSERT IGNORE INTO `dongmay` (`tendongmay`) VALUES (:tendongmay)");
            foreach ($defaultModels as $model) {
                $insertStmt->execute([':tendongmay' => $model]);
            }
        }
    }

    /**
     * Tự động kiểm tra và bổ sung cột phuongthucthanhtoan và trangthaithanhtoan vào bảng donhang
     */
    private function initPaymentSchema(): void {
        try {
            $stmt = $this->pdo->query("SHOW COLUMNS FROM `donhang` LIKE 'phuongthucthanhtoan'");
            if (!$stmt->fetch()) {
                $this->pdo->exec("ALTER TABLE `donhang` ADD COLUMN `phuongthucthanhtoan` VARCHAR(50) NOT NULL DEFAULT 'COD'");
            }

            $stmt2 = $this->pdo->query("SHOW COLUMNS FROM `donhang` LIKE 'trangthaithanhtoan'");
            if (!$stmt2->fetch()) {
                $this->pdo->exec("ALTER TABLE `donhang` ADD COLUMN `trangthaithanhtoan` TINYINT(1) NOT NULL DEFAULT 0");
            }
        } catch (Throwable $e) {
            error_log('Init payment schema error: ' . $e->getMessage());
        }
    }

    /**
     * Tự động khởi tạo cấu trúc bảng lienhe (tư vấn kỹ thuật & liên hệ) nếu chưa tồn tại
     */
    private function initLienHeSchema(): void {
        $sql = "CREATE TABLE IF NOT EXISTS `lienhe` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nguoidung_id` int(11) DEFAULT NULL COMMENT 'ID khách nếu đã đăng nhập, NULL nếu khách vãng lai',
            `hoten` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
            `sodienthoai` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
            `dongmay` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `noidung` text COLLATE utf8mb4_unicode_ci NOT NULL,
            `trangthai` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0: Chờ tư vấn, 1: Đã xử lý',
            `ghichu_admin` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `ngaygui` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $this->pdo->exec($sql);
    }
}
