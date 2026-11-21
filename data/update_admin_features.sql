-- ============================================================================
-- BẢN MÃ NGUỒN CẬP NHẬT CƠ SỞ DỮ LIỆU CHO HỆ THỐNG QUẢN TRỊ (ADMIN) XAG STORE
-- File: data/update_admin_features.sql
-- Kiến trúc sư Hệ thống & Kỹ sư CSDL: Database Architect
-- Mục tiêu:
--   1. Chuẩn hóa bảng `donhang`: Bổ sung cột `trangthai` (vòng đời đơn hàng) và các chỉ mục tối ưu.
--   2. Tạo bảng Quản lý Kho hàng (`phieunhap`, `ct_phieunhap`) chuẩn 3NF, liên kết `nguoidung` và `mathang`.
--   3. Tạo các chỉ mục (INDEX) tăng tốc truy vấn Dashboard, Báo cáo & Lọc dữ liệu Admin.
--   4. Nạp dữ liệu mẫu (Seed Data) thực tế về nhập kho linh kiện Drone XAG.
-- ============================================================================

USE `xag_store`;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- ----------------------------------------------------------------------------
-- PHẦN 1: CHUẨN HÓA BẢNG `donhang` (Bổ sung trạng thái đơn hàng & chỉ mục)
-- ----------------------------------------------------------------------------
-- Kiểm tra và bổ sung cột `trangthai` cho bảng `donhang` nếu chưa có
-- Quy ước chuẩn hóa trạng thái vòng đời đơn hàng:
--   0: Chờ xác nhận (Đơn mới đặt qua web, chờ nhân viên kinh doanh liên hệ xác nhận)
--   1: Đã xác nhận & Chờ đóng gói (Đã liên hệ khách, kho sẵn sàng chuẩn bị linh kiện)
--   2: Đang vận chuyển (Đã bàn giao đơn vị vận chuyển Viettel Post / GHTK / Bến xe Miền Tây)
--   3: Hoàn thành (Khách đã nhận hàng, kiểm tra linh kiện và thanh toán thành công)
--   4: Đã hủy (Khách hủy hoặc admin hủy do hết linh kiện / không liên lạc được)
--   5: Trả hàng / Hoàn tiền (Đổi trả bảo hành linh kiện kỹ thuật)
-- ----------------------------------------------------------------------------

-- Bổ sung cột `trangthai`
SET @dbname = DATABASE();
SET @tablename = "donhang";
SET @columnname = "trangthai";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 1",
  "ALTER TABLE `donhang` ADD COLUMN `trangthai` TINYINT(4) NOT NULL DEFAULT 0 COMMENT '0: Chờ xác nhận, 1: Đã xác nhận, 2: Đang vận chuyển, 3: Hoàn thành, 4: Đã hủy, 5: Trả hàng/Hoàn tiền' AFTER `tongtien`;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Cập nhật trạng thái cho các đơn hàng mẫu có sẵn trong hệ thống
UPDATE `donhang` SET `trangthai` = 3 WHERE `id` = 1; -- Đơn hàng số 1: Đã hoàn tất giao nhận
UPDATE `donhang` SET `trangthai` = 2 WHERE `id` = 2; -- Đơn hàng số 2: Đang gửi chành xe vận chuyển

-- Bổ sung chỉ mục (INDEX) trên bảng `donhang` phục vụ lọc theo trạng thái và thời gian tạo đơn
SET @indexname = "idx_donhang_trangthai_ngay";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND INDEX_NAME = @indexname
  ) > 0,
  "SELECT 1",
  "ALTER TABLE `donhang` ADD INDEX `idx_donhang_trangthai_ngay` (`trangthai`, `ngay`);"
));
PREPARE addIndexIfNotExists FROM @preparedStatement;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;


-- ----------------------------------------------------------------------------
-- PHẦN 2: CHỈ MỤC TỐI ƯU HÓA CHO CÁC BẢNG HIỆN HỮU (`mathang`, `nguoidung`)
-- ----------------------------------------------------------------------------
-- Index hỗ trợ cảnh báo tồn kho thấp trên bảng `mathang`
SET @tablename = "mathang";
SET @indexname = "idx_mathang_soluongton";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND INDEX_NAME = @indexname
  ) > 0,
  "SELECT 1",
  "ALTER TABLE `mathang` ADD INDEX `idx_mathang_soluongton` (`soluongton`);"
));
PREPARE addIndexIfNotExists FROM @preparedStatement;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;

-- Index hỗ trợ phân loại và lọc người dùng theo quyền/trạng thái hoạt động
SET @tablename = "nguoidung";
SET @indexname = "idx_nguoidung_loai_trangthai";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND INDEX_NAME = @indexname
  ) > 0,
  "SELECT 1",
  "ALTER TABLE `nguoidung` ADD INDEX `idx_nguoidung_loai_trangthai` (`loai`, `trangthai`);"
));
PREPARE addIndexIfNotExists FROM @preparedStatement;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;


-- ----------------------------------------------------------------------------
-- PHẦN 3: THIẾT KẾ CẤU TRÚC 2 BẢNG QUẢN LÝ KHO HÀNG (3NF)
-- ----------------------------------------------------------------------------

-- 3.1. Bảng `phieunhap`: Lưu thông tin chung của phiếu nhập kho
CREATE TABLE IF NOT EXISTS `phieunhap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `maphieunhap` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã chứng từ kế toán duy nhất (VD: PN-20261101-001)',
  `nguoidung_id` int(11) NOT NULL COMMENT 'Mã nhân viên/Admin lập phiếu (FK -> nguoidung)',
  `nhacungcap` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Đơn vị phân phối/Nhà máy sản xuất linh kiện',
  `ngaynhap` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày giờ thực hiện nhập kho vật lý',
  `tongtien` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Tổng giá trị hàng hóa nhập kho (VNĐ)',
  `trangthai` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1: Hoàn thành (đã cộng tồn kho), 0: Đã hủy phiếu',
  `ghichu` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ghi chú vận đơn, biên bản bàn giao, kiểm định',
  `ngaytao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ngaycapnhat` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_maphieunhap` (`maphieunhap`),
  KEY `idx_phieunhap_nguoidung` (`nguoidung_id`),
  KEY `idx_phieunhap_ngaynhap` (`ngaynhap`),
  KEY `idx_phieunhap_trangthai` (`trangthai`),
  CONSTRAINT `fk_phieunhap_nguoidung` FOREIGN KEY (`nguoidung_id`) REFERENCES `nguoidung` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng thông tin chứng từ nhập kho linh kiện Drone';


-- 3.2. Bảng `ct_phieunhap`: Lưu chi tiết các mặt hàng trong từng phiếu nhập
CREATE TABLE IF NOT EXISTS `ct_phieunhap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phieunhap_id` int(11) NOT NULL COMMENT 'Tham chiếu phiếu nhập (FK -> phieunhap)',
  `mathang_id` int(11) NOT NULL COMMENT 'Tham chiếu mặt hàng Drone XAG (FK -> mathang)',
  `soluong` int(11) NOT NULL DEFAULT 1 COMMENT 'Số lượng kiện/bộ nhập kho (>0)',
  `dongianhap` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Đơn giá vốn nhập kho tại thời điểm lập phiếu (VNĐ)',
  `thanhtien` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Thành tiền = soluong * dongianhap',
  `ghichu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mã lô sản xuất (Batch No.), số Serial (nếu có)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_phieunhap_mathang` (`phieunhap_id`, `mathang_id`),
  KEY `idx_ctpn_phieunhap` (`phieunhap_id`),
  KEY `idx_ctpn_mathang` (`mathang_id`),
  CONSTRAINT `fk_ctpn_phieunhap` FOREIGN KEY (`phieunhap_id`) REFERENCES `phieunhap` (`id`) ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_ctpn_mathang` FOREIGN KEY (`mathang_id`) REFERENCES `mathang` (`id`) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chi tiết danh mục linh kiện theo từng phiếu nhập kho';


-- ----------------------------------------------------------------------------
-- PHẦN 4: DỮ LIỆU MẪU (SEED DATA) QUẢN LÝ NHẬP KHO THỰC TẾ
-- ----------------------------------------------------------------------------
-- Xóa sạch dữ liệu mẫu kho cũ nếu có để tránh trùng lặp
DELETE FROM `ct_phieunhap`;
DELETE FROM `phieunhap`;

-- Nhập 3 phiếu nhập kho Drone XAG thực tế:
-- Phiếu 1: PN-20261101-001 do Quản trị viên (id=1) lập - Nhập lô Động cơ XAG A20 và Cánh carbon 47 inch
INSERT INTO `phieunhap` (`id`, `maphieunhap`, `nguoidung_id`, `nhacungcap`, `ngaynhap`, `tongtien`, `trangthai`, `ghichu`) VALUES
(1, 'PN-20261101-001', 1, 'XAG Guangzhou Technology Co., Ltd (Nhà máy Quảng Châu)', '2026-11-01 08:30:00', 81500000.00, 1, 'Lô linh kiện thay thế chuẩn bị phục vụ vụ mùa Đông Xuân ĐBSCL');

INSERT INTO `ct_phieunhap` (`id`, `phieunhap_id`, `mathang_id`, `soluong`, `dongianhap`, `thanhtien`, `ghichu`) VALUES
(1, 1, 1, 10, 4800000.00, 48000000.00, 'Lô Động cơ XAG A20 - Hàng chính hãng nguyên seal tem QC'),
(2, 1, 4, 30, 1100000.00, 33000000.00, 'Cánh Carbon 47 inch cho P100 Pro - Quy cách 30 cặp');

-- Phiếu 2: PN-20261108-002 do Kỹ thuật viên XAG (id=2) lập - Nhập bổ sung Pin thông minh XAG
INSERT INTO `phieunhap` (`id`, `maphieunhap`, `nguoidung_id`, `nhacungcap`, `ngaynhap`, `tongtien`, `trangthai`, `ghichu`) VALUES
(2, 'PN-20261108-002', 2, 'Tổng kho XAG Miền Nam - Chi nhánh Cần Thơ', '2026-11-08 10:15:00', 242000000.00, 1, 'Nhập bổ sung Pin công suất cao phục vụ đội bay dịch vụ bảo vệ thực vật');

INSERT INTO `ct_phieunhap` (`id`, `phieunhap_id`, `mathang_id`, `soluong`, `dongianhap`, `thanhtien`, `ghichu`) VALUES
(3, 2, 7, 5, 22000000.00, 110000000.00, 'Pin thông minh XAG B13960S - Đã kiểm định dung lượng 100% qua máy test BMS'),
(4, 2, 8, 5, 26400000.00, 132000000.00, 'Pin siêu nạp XAG B13970 cho P100 Pro - Kèm phiếu bảo hành 1000 chu kỳ');

-- Phiếu 3: PN-20261115-003 do Quản trị viên (id=1) lập - Nhập Vòi phun ly tâm và Cụm bơm nhu động
INSERT INTO `phieunhap` (`id`, `maphieunhap`, `nguoidung_id`, `nhacungcap`, `ngaynhap`, `tongtien`, `trangthai`, `ghichu`) VALUES
(3, 'PN-20261115-003', 1, 'Công ty TNHH Thiết bị Nông nghiệp Thông minh XAG Việt Nam', '2026-11-15 14:00:00', 77000000.00, 1, 'Lô phụ tùng hệ thống phun thuốc dạng sương sương ly tâm');

INSERT INTO `ct_phieunhap` (`id`, `phieunhap_id`, `mathang_id`, `soluong`, `dongianhap`, `thanhtien`, `ghichu`) VALUES
(5, 3, 10, 12, 3500000.00, 42000000.00, 'Cụm bơm nhu động SuperX4 (12L/phút) - Lưu lượng chuẩn'),
(6, 3, 13, 10, 2800000.00, 28000000.00, 'Đầu phun ly tâm RevoSpray 2 - Đã bao gồm van ngắt điện từ'),
(7, 3, 12, 20, 350000.00, 7000000.00, 'Bộ dây ống dẫn silicone chịu hóa chất đặc chủng');

-- ----------------------------------------------------------------------------
-- KHÔI PHỤC KIỂM TRA KHÓA NGOẠI & KẾT THÚC TRANSACTION
-- ----------------------------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
