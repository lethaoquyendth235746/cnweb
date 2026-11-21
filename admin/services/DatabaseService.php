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
            // Auto‑migration: ensure required columns and tables exist (run once per request)
            static $migrationRun = false;
            if (!$migrationRun) {
                $migrationRun = true;
                try {
                    $sqlFile = realpath(__DIR__ . '/../../data/update_admin_features.sql');
                    if ($sqlFile && file_exists($sqlFile)) {
                        $sql = file_get_contents($sqlFile);
                        // Execute the whole script; MySQL allows multiple statements
                        $this->pdo->exec($sql);
                    }
                } catch (Exception $e) {
                    error_log('Auto‑migration failed: ' . $e->getMessage());
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
     * Lấy ID vừa được chèn tự tăng gần nhất
     */
    public static function lastInsertId(?string $name = null): string|false {
        return self::pdo()->lastInsertId($name);
    }
}
