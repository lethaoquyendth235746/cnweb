<?php
/**
 * DashboardService.php
 * Cung cấp dữ liệu thống kê KPI, biểu đồ doanh thu và danh sách giám sát vận hành
 * Hệ thống Quản trị XAG Store - Giai đoạn 2
 */

require_once __DIR__ . '/DatabaseService.php';
require_once __DIR__ . '/../config/db.php';

class DashboardService {

    /**
     * Lấy toàn bộ chỉ số KPI tổng hợp cho Bảng điều khiển (Dashboard)
     *
     * @return array
     */
    public function getKpiMetrics(): array {
        $pdo = DatabaseService::pdo();

        // 1. Tổng doanh thu thực tế (chỉ tính đơn hoàn thành - trangthai = 3)
        $stmtRev = $pdo->query("SELECT COALESCE(SUM(tongtien), 0) FROM donhang WHERE trangthai = 3");
        $tongDoanhThu = (float)$stmtRev->fetchColumn();

        // 2. Doanh thu hôm nay
        $stmtToday = $pdo->query("SELECT COALESCE(SUM(tongtien), 0) FROM donhang WHERE trangthai = 3 AND DATE(ngay) = CURDATE()");
        $doanhThuHomNay = (float)$stmtToday->fetchColumn();

        // 3. Doanh thu tháng này
        $stmtMonth = $pdo->query("SELECT COALESCE(SUM(tongtien), 0) FROM donhang WHERE trangthai = 3 AND YEAR(ngay) = YEAR(CURDATE()) AND MONTH(ngay) = MONTH(CURDATE())");
        $doanhThuThangNay = (float)$stmtMonth->fetchColumn();

        // 4. Tổng số đơn hàng
        $stmtOrders = $pdo->query("SELECT COUNT(*) FROM donhang");
        $tongDonHang = (int)$stmtOrders->fetchColumn();

        // 5. Số đơn chờ xác nhận (trangthai = 0)
        $stmtPending = $pdo->query("SELECT COUNT(*) FROM donhang WHERE trangthai = 0");
        $donChoXacNhan = (int)$stmtPending->fetchColumn();

        // 6. Số đơn đang giao hàng (trangthai = 2)
        $stmtShipping = $pdo->query("SELECT COUNT(*) FROM donhang WHERE trangthai = 2");
        $donDangGiao = (int)$stmtShipping->fetchColumn();

        // 7. Số đơn hoàn thành (trangthai = 3)
        $stmtCompleted = $pdo->query("SELECT COUNT(*) FROM donhang WHERE trangthai = 3");
        $donHoanThanh = (int)$stmtCompleted->fetchColumn();

        // 8. Số đơn đã hủy (trangthai = 4)
        $stmtCancelled = $pdo->query("SELECT COUNT(*) FROM donhang WHERE trangthai = 4");
        $donDaHuy = (int)$stmtCancelled->fetchColumn();

        // 9. Thống kê sản phẩm & tồn kho
        $stmtTotalProducts = $pdo->query("SELECT COUNT(*) FROM mathang");
        $tongSanPham = (int)$stmtTotalProducts->fetchColumn();

        // Sắp hết hàng (1 <= soluongton <= 5)
        $stmtLowStock = $pdo->query("SELECT COUNT(*) FROM mathang WHERE soluongton > 0 AND soluongton <= 5");
        $sapHetHang = (int)$stmtLowStock->fetchColumn();

        // Hết hàng (soluongton = 0)
        $stmtOutOfStock = $pdo->query("SELECT COUNT(*) FROM mathang WHERE soluongton = 0");
        $hetHang = (int)$stmtOutOfStock->fetchColumn();

        // 10. Thống kê nhập kho
        $stmtImports = $pdo->query("SELECT COUNT(*) FROM phieunhap WHERE trangthai = 1");
        $tongPhieuNhap = (int)$stmtImports->fetchColumn();

        $stmtImportCost = $pdo->query("SELECT COALESCE(SUM(tongtien), 0) FROM phieunhap WHERE trangthai = 1");
        $tongChiNhapKho = (float)$stmtImportCost->fetchColumn();

        return [
            'tong_doanh_thu'       => $tongDoanhThu,
            'tong_doanh_thu_fmt'   => formatVND($tongDoanhThu),
            'doanh_thu_hom_nay'    => $doanhThuHomNay,
            'doanh_thu_hom_nay_fmt'=> formatVND($doanhThuHomNay),
            'doanh_thu_thang_nay'  => $doanhThuThangNay,
            'doanh_thu_thang_nay_fmt'=> formatVND($doanhThuThangNay),
            'tong_don_hang'        => $tongDonHang,
            'don_cho_xac_nhan'     => $donChoXacNhan,
            'don_dang_giao'        => $donDangGiao,
            'don_hoan_thanh'       => $donHoanThanh,
            'don_da_huy'           => $donDaHuy,
            'tong_san_pham'        => $tongSanPham,
            'sap_het_hang'         => $sapHetHang,
            'het_hang'             => $hetHang,
            'tong_phieu_nhap'      => $tongPhieuNhap,
            'tong_chi_nhap_kho'    => $tongChiNhapKho,
            'tong_chi_nhap_kho_fmt'=> formatVND($tongChiNhapKho)
        ];
    }

    /**
     * Dữ liệu biểu đồ doanh thu theo ngày (mặc định 7 ngày gần nhất)
     * Phục vụ trực tiếp cho Chart.js / ApexCharts
     *
     * @param int $days Số ngày cần lấy (mặc định 7)
     * @return array ['labels' => [...], 'revenues' => [...], 'orders' => [...]]
     */
    public function getRevenueChartData(int $days = 7): array {
        $pdo = DatabaseService::pdo();

        // Truy vấn doanh thu theo từng ngày của các đơn hoàn tất (trangthai = 3)
        $sql = "SELECT DATE(ngay) as order_date, 
                       COALESCE(SUM(tongtien), 0) as daily_revenue, 
                       COUNT(*) as daily_orders 
                FROM donhang 
                WHERE trangthai = 3 
                  AND ngay >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                GROUP BY DATE(ngay)";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':days', $days - 1, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $dataMap = [];
        foreach ($rows as $r) {
            $dataMap[$r['order_date']] = [
                'revenue' => (float)$r['daily_revenue'],
                'orders'  => (int)$r['daily_orders']
            ];
        }

        $labels = [];
        $revenues = [];
        $orders = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $displayLabel = date('d/m', strtotime($date));

            $labels[] = $displayLabel;
            $revenues[] = $dataMap[$date]['revenue'] ?? 0;
            $orders[] = $dataMap[$date]['orders'] ?? 0;
        }

        return [
            'labels'   => $labels,
            'revenues' => $revenues,
            'orders'   => $orders
        ];
    }

    /**
     * Dữ liệu biểu đồ doanh thu theo tháng (mặc định 6 tháng gần nhất)
     *
     * @param int $months Số tháng cần lấy (mặc định 6)
     * @return array ['labels' => [...], 'revenues' => [...], 'orders' => [...]]
     */
    public function getMonthlyRevenueChartData(int $months = 6): array {
        $pdo = DatabaseService::pdo();

        $sql = "SELECT DATE_FORMAT(ngay, '%Y-%m') as month_key, 
                       COALESCE(SUM(tongtien), 0) as monthly_revenue, 
                       COUNT(*) as monthly_orders 
                FROM donhang 
                WHERE trangthai = 3 
                  AND ngay >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)
                GROUP BY DATE_FORMAT(ngay, '%Y-%m')";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':months', $months - 1, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $dataMap = [];
        foreach ($rows as $r) {
            $dataMap[$r['month_key']] = [
                'revenue' => (float)$r['monthly_revenue'],
                'orders'  => (int)$r['monthly_orders']
            ];
        }

        $labels = [];
        $revenues = [];
        $orders = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthKey = date('Y-m', strtotime("-$i months"));
            $displayLabel = 'T' . date('n/Y', strtotime("-$i months"));

            $labels[] = $displayLabel;
            $revenues[] = $dataMap[$monthKey]['revenue'] ?? 0;
            $orders[] = $dataMap[$monthKey]['orders'] ?? 0;
        }

        return [
            'labels'   => $labels,
            'revenues' => $revenues,
            'orders'   => $orders
        ];
    }

    /**
     * Danh sách linh kiện Drone XAG bán chạy nhất
     *
     * @param int $limit Số lượng cần lấy
     * @return array
     */
    public function getTopSellingProducts(int $limit = 5): array {
        $pdo = DatabaseService::pdo();

        $sql = "SELECT m.id, m.tenmathang, m.hinhanh, m.giaban, m.dongmay, m.soluongton, m.luotmua,
                       d.tendanhmuc,
                       COALESCE(SUM(ct.soluong), 0) AS tong_da_ban,
                       COALESCE(SUM(ct.thanhtien), 0) AS tong_doanh_so
                FROM mathang m
                JOIN danhmuc d ON m.danhmuc_id = d.id
                LEFT JOIN donhangct ct ON m.id = ct.mathang_id
                LEFT JOIN donhang dh ON ct.donhang_id = dh.id AND dh.trangthai = 3
                GROUP BY m.id
                ORDER BY tong_da_ban DESC, m.luotmua DESC
                LIMIT :limit";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();

        foreach ($products as &$p) {
            $p['giaban_fmt'] = formatVND($p['giaban']);
            $p['tong_doanh_so_fmt'] = formatVND($p['tong_doanh_so']);
        }

        return $products;
    }


    /**
     * Alias for low stock alerts – returns items with stock <= threshold.
     * Provides same data as getLowStockProducts but with customizable limit.
     *
     * @param int $threshold Stock threshold (default 5)
     * @param int $limit Maximum number of items to return (default 6)
     * @return array List of low‑stock products
     */
    public function getLowStockAlerts(int $threshold = 5, int $limit = 6): array {
        $items = $this->getLowStockProducts($threshold);
        // If a specific limit is requested, truncate the result.
        if ($limit > 0 && count($items) > $limit) {
            $items = array_slice($items, 0, $limit);
        }
        return $items;
    }

    public function getLowStockProducts(int $threshold = 5): array {
        $pdo = DatabaseService::pdo();

        $sql = "SELECT m.id, m.tenmathang, m.hinhanh, m.giagoc, m.giaban, m.soluongton, m.dongmay,
                       d.tendanhmuc
                FROM mathang m
                JOIN danhmuc d ON m.danhmuc_id = d.id
                WHERE m.soluongton <= :threshold
                ORDER BY m.soluongton ASC, m.id DESC
                LIMIT 10";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':threshold', $threshold, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        foreach ($items as &$item) {
            $item['giaban_fmt'] = formatVND($item['giaban']);
            $item['is_out_of_stock'] = ((int)$item['soluongton'] === 0);
        }

        return $items;
    }

    /**
     * Danh sách đơn hàng mới nhất
     *
     * @param int $limit Số đơn cần lấy
     * @return array
     */
    public function getRecentOrders(int $limit = 6): array {
        $pdo = DatabaseService::pdo();

        $sql = "SELECT dh.id, dh.nguoidung_id, dh.diachi_id, dh.ngay, dh.tongtien, dh.ghichu, dh.trangthai,
                       nd.hoten, nd.sodienthoai, nd.email,
                       dc.diachi AS diachi_chitiet
                FROM donhang dh
                JOIN nguoidung nd ON dh.nguoidung_id = nd.id
                LEFT JOIN diachi dc ON dh.diachi_id = dc.id
                ORDER BY dh.ngay DESC, dh.id DESC
                LIMIT :limit";

        $stmt = $pdo->prepare($sql);
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
}
