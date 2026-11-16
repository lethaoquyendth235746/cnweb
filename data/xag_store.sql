-- ============================================================================
-- CƠ SỞ DỮ LIỆU: CỬA HÀNG PHỤ TÙNG & LINH KIỆN DRONE NÔNG NGHIỆP XAG (XAG STORE)
-- Hệ thống: PHP thuần (MVC) + PDO MySQL / MariaDB (XAMPP)
-- Tương thích: Toàn bộ cấu trúc bảng từ shop.sql (danhmuc, mathang, nguoidung, diachi, donhang, donhangct)
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `xag_store` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `xag_store`;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ----------------------------------------------------------------------------
-- XÓA BẢNG CŨ NẾU ĐÃ TỒN TẠI (Đảm bảo import sạch)
-- ----------------------------------------------------------------------------
DROP TABLE IF EXISTS `donhangct`;
DROP TABLE IF EXISTS `donhang`;
DROP TABLE IF EXISTS `diachi`;
DROP TABLE IF EXISTS `mathang`;
DROP TABLE IF EXISTS `danhmuc`;
DROP TABLE IF EXISTS `nguoidung`;

-- ----------------------------------------------------------------------------
-- 1. CẤU TRÚC BẢNG: danhmuc (Danh mục linh kiện Drone)
-- ----------------------------------------------------------------------------
CREATE TABLE `danhmuc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tendanhmuc` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- DỮ LIỆU BẢNG: danhmuc (7 danh mục chuyên biệt cho Drone nông nghiệp XAG)
-- ----------------------------------------------------------------------------
INSERT INTO `danhmuc` (`id`, `tendanhmuc`) VALUES
(1, 'Động cơ (Motor)'),
(2, 'Cánh quạt'),
(3, 'Pin thông minh'),
(4, 'Bơm nhu động'),
(5, 'Vòi phun ly tâm'),
(6, 'Mạch điều khiển (FC/ESC)'),
(7, 'Khung sườn & Càng');

-- ----------------------------------------------------------------------------
-- 2. CẤU TRÚC BẢNG: nguoidung (Người dùng: Admin, Nhân viên, Khách hàng)
-- Mật khẩu mã hóa MD5 tương thích hàm md5($matkhau) trong model/nguoidung.php
-- ----------------------------------------------------------------------------
CREATE TABLE `nguoidung` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sodienthoai` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `matkhau` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hoten` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `loai` tinyint(4) NOT NULL DEFAULT 3 COMMENT '1: Quản trị (Admin), 2: Nhân viên, 3: Khách hàng',
  `trangthai` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1: Kích hoạt, 0: Khóa',
  `hinhanh` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- DỮ LIỆU BẢNG: nguoidung
-- Ghi chú mật khẩu mặc định:
-- 1. admin@xag.vn    / pass: 123456 (MD5: e10adc3949ba59abbe56e057f20f883e) - Quyền Admin (loai=1)
-- 2. nhanvien@xag.vn / pass: 123456 (MD5: e10adc3949ba59abbe56e057f20f883e) - Quyền Nhân viên (loai=2)
-- 3. abc@abc.com     / pass: abc    (MD5: 900150983cd24fb0d6963f7d28e17f72) - Tài khoản mẫu cũ (loai=1)
-- 4. khachhang@xag.vn/ pass: 123456 (MD5: e10adc3949ba59abbe56e057f20f883e) - Khách hàng mẫu (loai=3)
-- ----------------------------------------------------------------------------
INSERT INTO `nguoidung` (`id`, `email`, `sodienthoai`, `matkhau`, `hoten`, `loai`, `trangthai`, `hinhanh`) VALUES
(1, 'admin@xag.vn', '0988001001', 'e10adc3949ba59abbe56e057f20f883e', 'Quản trị viên XAG Store', 1, 1, 'images/users/user.png'),
(2, 'nhanvien@xag.vn', '0988002002', 'e10adc3949ba59abbe56e057f20f883e', 'Kỹ thuật viên XAG', 2, 1, 'images/users/male.png'),
(3, 'abc@abc.com', '0988994683', '900150983cd24fb0d6963f7d28e17f72', 'Admin Demo Cũ', 1, 1, 'images/users/user.png'),
(4, 'khachhang@xag.vn', '0988003003', 'e10adc3949ba59abbe56e057f20f883e', 'Nguyễn Văn Nông Dân', 3, 1, 'images/users/female.png'),
(5, 'tranvanlua@gmail.com', '0977112233', 'e10adc3949ba59abbe56e057f20f883e', 'Trần Văn Lúa (HTX An Giang)', 3, 1, 'images/users/female.png');

-- ----------------------------------------------------------------------------
-- 3. CẤU TRÚC BẢNG: mathang (Linh kiện, phụ tùng Drone XAG)
-- Cột `dongmay` được bổ sung để phân loại dòng máy bay mà vẫn giữ nguyên tương thích
-- ----------------------------------------------------------------------------
CREATE TABLE `mathang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenmathang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mota` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `giagoc` float NOT NULL DEFAULT 0,
  `giaban` float NOT NULL DEFAULT 0,
  `soluongton` int(11) NOT NULL DEFAULT 0,
  `hinhanh` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `danhmuc_id` int(11) NOT NULL,
  `dongmay` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dòng máy tương thích: P100, P100 Pro, P40, V40, R150',
  `luotxem` int(11) NOT NULL DEFAULT 0,
  `luotmua` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `danhmuc_id` (`danhmuc_id`),
  CONSTRAINT `mathang_ibfk_1` FOREIGN KEY (`danhmuc_id`) REFERENCES `danhmuc` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- DỮ LIỆU BẢNG: mathang (21 sản phẩm linh kiện thực tế phủ đều 7 danh mục & 5 dòng máy)
-- ----------------------------------------------------------------------------
INSERT INTO `mathang` (`id`, `tenmathang`, `mota`, `giagoc`, `giaban`, `soluongton`, `hinhanh`, `danhmuc_id`, `dongmay`, `luotxem`, `luotmua`) VALUES
-- --- DANH MỤC 1: ĐỘNG CƠ (MOTOR) ---
(1, 'Động cơ không chổi than XAG A20 (XAG P100 / P100 Pro)', 
 '<p><strong>Động cơ không chổi than XAG A20</strong> công suất cực lớn chuyên dụng cho dòng máy bay phun thuốc hạng nặng XAG P100 và P100 Pro. Gia công từ hợp kim nhôm hàng không CNC nguyên khối, tản nhiệt nhanh, độ bền cao trong môi trường hóa chất nông nghiệp.</p><ul><li><strong>Model:</strong> XAG A20 Brushless Motor</li><li><strong>Tương thích:</strong> XAG P100, XAG P100 Pro</li><li><strong>Công suất cực đại:</strong> 3200W/trục</li><li><strong>Chuẩn kháng nước:</strong> IPX6 chống bám dính hóa chất</li><li><strong>Tốc độ định mức:</strong> 3600 vòng/phút</li></ul>', 
 4800000, 5500000, 25, 'images/products/motor_xag_a20.jpg', 1, 'XAG P100, XAG P100 Pro', 128, 12),

(2, 'Cụm động cơ bước truyền động XAG A16 (XAG P40)', 
 '<p><strong>Động cơ chính hãng XAG A16</strong> dành riêng cho máy bay nông nghiệp đa năng XAG P40. Tối ưu lực kéo với cánh 40 inch, vận hành cực êm, momen xoắn cao giúp máy bay nâng tải 40kg ổn định trong điều kiện gió cấp 5.</p><ul><li><strong>Model:</strong> XAG A16 Motor</li><li><strong>Tương thích:</strong> XAG P40</li><li><strong>Công suất định mức:</strong> 2400W</li><li><strong>Kháng nước/bụi:</strong> IPX7 chống ăn mòn phân bón</li></ul>', 
 3900000, 4450000, 18, 'images/products/motor_xag_a16.jpg', 1, 'XAG P40', 95, 8),

(3, 'Cặp động cơ rotor kép XAG V40 (Trục trước & sau)', 
 '<p><strong>Cụm động cơ đôi rotor kép nghiêng độc quyền</strong> cho thiết kế 2 cánh nâng của XAG V40. Tiết kiệm điện năng đến 20%, tạo luồng gió xoáy cực mạnh đưa hạt thuốc bám dính sâu vào mặt dưới của tán lá.</p><ul><li><strong>Tương thích:</strong> XAG V40 Dual-rotor</li><li><strong>Điện áp:</strong> 48V - Công suất 2200W</li><li><strong>Tính năng:</strong> Tự động ngắt khi kẹt tải cơ học</li></ul>', 
 4200000, 4900000, 15, 'images/products/motor_xag_v40.jpg', 1, 'XAG V40', 74, 5),

-- --- DANH MỤC 2: CÁNH QUẠT ---
(4, 'Cánh quạt sợi carbon XAG P100 Pro (47 inch)', 
 '<p><strong>Cặp cánh quạt sợi carbon siêu nhẹ 47 inch</strong> đúc nguyên khối gia cường polymer tổng hợp cao cấp, chịu lực kéo uốn cực cao cho dòng XAG P100 Pro mang tải trọng bình thuốc 50kg. Giảm rung chấn, tối ưu khí động học và giảm tiêu hao pin.</p><ul><li><strong>Kích thước:</strong> 47 inch (47x15)</li><li><strong>Chất liệu:</strong> 100% Carbon Fiber 3K</li><li><strong>Đóng gói:</strong> 1 cặp (1 cánh thuận CW + 1 cánh nghịch CCW)</li><li><strong>Tương thích:</strong> XAG P100 Pro, XAG P100</li></ul>', 
 1100000, 1350000, 60, 'images/products/canh_quat_p100pro.jpg', 2, 'XAG P100 Pro, XAG P100', 215, 34),

(5, 'Bộ cánh quạt gập sợi thủy tinh gia cường XAG P40 (40 inch)', 
 '<p><strong>Bộ cánh quạt gập thông minh 40 inch</strong> chính hãng dành cho XAG P40. Cơ chế gập tiện lợi khi di chuyển trên đồng ruộng, chống va quẹt gãy nứt, tháo lắp cực nhanh với chốt an toàn hợp kim.</p><ul><li><strong>Kích thước:</strong> 40 inch</li><li><strong>Tương thích:</strong> XAG P40</li><li><strong>Quy cách:</strong> Hộp 1 bộ cánh đầy đủ chốt long đền</li><li><strong>Đạt chuẩn:</strong> Cân bằng động xuất xưởng ISO</li></ul>', 
 950000, 1200000, 45, 'images/products/canh_quat_p40.jpg', 2, 'XAG P40', 160, 26),

(6, 'Cánh quạt khí động học chuyên dụng XAG V40 (40 inch)', 
 '<p><strong>Cánh quạt bản rộng chuyên biệt</strong> tối ưu cho luồng gió đối lưu của máy bay 2 cánh nghiêng XAG V40. Tăng cường khả năng ép hạt sương thuốc phủ đều bề mặt hoa màu và cây ăn trái thân cao.</p><ul><li><strong>Kích thước:</strong> 40 inch</li><li><strong>Tương thích:</strong> XAG V40</li><li><strong>Chất liệu:</strong> Composite Carbon kháng hóa chất</li></ul>', 
 900000, 1150000, 35, 'images/products/canh_quat_v40.jpg', 2, 'XAG V40', 88, 14),

-- --- DANH MỤC 3: PIN THÔNG MINH ---
(7, 'Pin thông minh XAG B13960S (Dung lượng 962Wh - P100 / P40 / V40)', 
 '<p><strong>Pin siêu sạc thông minh XAG B13960S</strong> công nghệ Lithium-ion cao cấp với hệ thống quản lý nguồn BMS thế hệ 5. Hỗ trợ sạc siêu tốc bằng thùng làm mát làm nguội nước độc quyền, chỉ mất 9-11 phút/lần sạc đầy để bay liên tục.</p><ul><li><strong>Mã sản phẩm:</strong> B13960S Smart Battery</li><li><strong>Dung lượng:</strong> 20.000mAh (962 Wh - 48.1V)</li><li><strong>Tương thích:</strong> XAG P100, P40, V40</li><li><strong>Tuổi thọ:</strong> Hơn 1.000 chu kỳ sạc xả</li><li><strong>Cảm biến:</strong> Báo dung lượng, nhiệt độ từng cell pin qua App</li></ul>', 
 22000000, 25500000, 20, 'images/products/pin_xag_b13960s.jpg', 3, 'XAG P100, XAG P40, XAG V40', 340, 19),

(8, 'Pin siêu nạp dung lượng lớn XAG B13970 (Dành cho P100 Pro)', 
 '<p><strong>Dòng pin công suất khủng nhất của XAG</strong> phục vụ khối lượng tải 50kg của P100 Pro. Vỏ nhôm nguyên khối tản nhiệt cực nhanh, tự cân bằng cell và công nghệ bảo vệ chống cháy nổ an toàn tuyệt đối khi bay dưới trời nắng nóng 40°C.</p><ul><li><strong>Mã sản phẩm:</strong> B13970 SuperCharge Battery</li><li><strong>Dung lượng:</strong> 975 Wh (48.1V)</li><li><strong>Tương thích:</strong> XAG P100 Pro</li><li><strong>Thời gian sạc:</strong> 8 - 10 phút với máy phát sạc hơi nước GC4000+</li></ul>', 
 26500000, 29800000, 12, 'images/products/pin_xag_b13970.jpg', 3, 'XAG P100 Pro', 270, 15),

(9, 'Pin thông minh XAG B13860S (Dùng cho XAG P40 & Robot R150)', 
 '<p><strong>Pin thông minh XAG B13860S</strong> kích thước nhỏ gọn, trọng lượng nhẹ, cung cấp nguồn năng lượng dồi dào, bền bỉ cho máy bay XAG P40 và robot nông nghiệp mặt đất tự hành XAG R150.</p><ul><li><strong>Mã sản phẩm:</strong> B13860S</li><li><strong>Dung lượng:</strong> 12.000mAh (600 Wh)</li><li><strong>Chuẩn sạc:</strong> XAG SuperCharge nhanh và an toàn</li><li><strong>Bảo vệ:</strong> Chống quá dòng, đoản mạch, tự động xả bảo dưỡng khi lưu kho</li></ul>', 
 15500000, 18200000, 16, 'images/products/pin_xag_b13860s.jpg', 3, 'XAG P40, XAG R150', 115, 9),

-- --- DANH MỤC 4: BƠM NHU ĐỘNG ---
(10, 'Cụm bơm nhu động thông minh XAG SuperX4 (Lưu lượng 12L/phút)', 
 '<p><strong>Cụm bơm nhu động đôi thế hệ SuperX4</strong> chống tắc nghẹt thuốc tối đa. Thuốc không tiếp xúc trực tiếp với chi tiết kim loại hay trục cơ khí của máy bơm, chống ăn mòn hóa chất và cho phép phun mượt mà các loại phân bón vi sinh dạng bột hòa tan.</p><ul><li><strong>Lưu lượng:</strong> 12 Lít/phút</li><li><strong>Tương thích:</strong> XAG P100, XAG P40</li><li><strong>Đặc tính:</strong> Dễ dàng thay thế ống dẫn silicone định kỳ</li><li><strong>Độ chính xác:</strong> Liều lượng phun đạt chuẩn >98%</li></ul>', 
 3500000, 4200000, 30, 'images/products/bom_nhu_dong_superx4.jpg', 4, 'XAG P100, XAG P40', 145, 18),

(11, 'Bơm nhu động kép siêu lưu lượng XAG P100 Pro (22L/phút)', 
 '<p><strong>Bơm kép Mega-flow công suất lớn</strong> tích hợp trên hệ thống phun Revospray 3 của máy bay XAG P100 Pro. Hỗ trợ tốc độ bay cao với lượng phun cực lớn lên đến 22L/phút, phù hợp dập dịch nhanh trên các cánh đồng lớn.</p><ul><li><strong>Lưu lượng tối đa:</strong> 22 Lít/phút</li><li><strong>Tương thích:</strong> XAG P100 Pro MegaFlow</li><li><strong>Cảm biến:</strong> Báo cạn thuốc thông minh, chống chạy khan bảo vệ bơm</li></ul>', 
 4600000, 5400000, 22, 'images/products/bom_nhu_dong_p100pro.jpg', 4, 'XAG P100 Pro', 180, 21),

(12, 'Dây ống dẫn silicone chịu hóa chất cho bơm nhu động XAG (Bộ 4 chiếc)', 
 '<p><strong>Ruột ống bơm nhu động bằng silicone y tế cao cấp</strong> chịu được hóa chất nông dược đậm đặc, thuốc bột hòa tan và dung môi nhũ dầu EC. Tính đàn hồi vượt trội, không bị xẹp lép hay nứt rách sau hơn 200 giờ phun liên tục.</p><ul><li><strong>Quy cách:</strong> Bộ 4 ống chính hãng XAG</li><li><strong>Chất liệu:</strong> High-grade Silicone kháng mài mòn</li><li><strong>Tương thích:</strong> Dùng chung cho bơm P100, P100 Pro, P40, V40, R150</li><li><strong>Thay thế:</strong> Thao tác nhanh không cần dụng cụ chuyên dụng</li></ul>', 
 320000, 450000, 100, 'images/products/ong_silicone_bom_xag.jpg', 4, 'P100, P100 Pro, P40, V40, R150', 310, 85),

-- --- DANH MỤC 5: VÒI PHUN LY TÂM ---
(13, 'Vòi phun ly tâm kép XAG RevoSpray 2 (Hạt sương 60-400 micron)', 
 '<p><strong>Cụm đầu phun ly tâm kép RevoSpray 2</strong> công nghệ phun nguyên tử hóa. Cho phép phi công dễ dàng điều chỉnh kích thước hạt sương từ 60 đến 400 micron ngay trên điện thoại hoặc remote điều khiển mà không cần tháo lắp thay đổi béc phun cơ học.</p><ul><li><strong>Tốc độ quay đĩa:</strong> 1.000 - 16.000 vòng/phút</li><li><strong>Vệt phun hiệu quả:</strong> Độ rộng 6 - 10 mét</li><li><strong>Chống nhỏ giọt:</strong> Van khóa từ tính tự động ngắt tức thì</li><li><strong>Tương thích:</strong> XAG P100, XAG P40, XAG V40</li></ul>', 
 2800000, 3350000, 35, 'images/products/voi_phun_revospray2.jpg', 5, 'XAG P100, XAG P40, XAG V40', 210, 28),

(14, 'Cụm đĩa phun ly tâm RevoSpray 3 XAG P100 Pro', 
 '<p><strong>Vòi phun ly tâm RevoSpray thế hệ thứ 3</strong> trang bị đĩa răng cưa kép đa tầng, tạo ra luồng hạt thuốc mịn màng đồng nhất, tăng độ bám dính trên lá và hạn chế tối đa hiện tượng trôi dạt thuốc theo gió.</p><ul><li><strong>Công nghệ:</strong> Đĩa phun kép răng cưa đa tầng Vortex</li><li><strong>Tương thích:</strong> XAG P100 Pro</li><li><strong>Động cơ đĩa quay:</strong> Chống nước chuẩn IPX7, motor không chổi than</li></ul>', 
 3400000, 4100000, 28, 'images/products/voi_phun_revospray3.jpg', 5, 'XAG P100 Pro', 190, 24),

(15, 'Đĩa quay ly tâm thay thế cho vòi phun XAG (Hợp kim & Carbon)', 
 '<p><strong>Đĩa xé sương ly tâm chính hãng XAG</strong> dùng thay thế định kỳ khi đĩa cũ bị bám cặn vôi hóa chất hoặc mòn răng. Đảm bảo góc phun và kích thước hạt luôn đạt chuẩn kỹ thuật của hãng.</p><ul><li><strong>Vật liệu:</strong> Polymer chống bám dính + Vành hợp kim nhôm CNC</li><li><strong>Tương thích:</strong> Vòi phun RevoSpray 1 và RevoSpray 2</li><li><strong>Đóng gói:</strong> Bộ 1 cặp đĩa phun</li></ul>', 
 450000, 600000, 80, 'images/products/dia_ly_tam_xag.jpg', 5, 'XAG P100, P40, V40', 175, 42),

-- --- DANH MỤC 6: MẠCH ĐIỀU KHIỂN (FC/ESC) ---
(16, 'Mạch điều khiển trung tâm SuperX 4 Pro Flight Controller', 
 '<p><strong>Bộ vi xử lý trung tâm (Não bộ) SuperX 4 Pro</strong> tích hợp bộ xử lý AI độc lập và thuật toán điều khiển bay tự hành chuẩn xác từng centimet. Tích hợp mô-đun định vị vệ tinh RTK kép giúp máy bay vận hành an toàn trong mọi địa hình phức tạp.</p><ul><li><strong>Model:</strong> SuperX 4 Pro FC</li><li><strong>Cảm biến:</strong> Hệ thống IMU 3 kênh dự phòng nóng tự động chuyển mạch</li><li><strong>Định vị:</strong> Chuẩn RTK sai số dưới 2cm</li><li><strong>Tương thích:</strong> XAG P100, XAG P100 Pro</li></ul>', 
 18000000, 21500000, 10, 'images/products/mach_fc_superx4_pro.jpg', 6, 'XAG P100, XAG P100 Pro', 130, 6),

(17, 'Bộ điều tốc ESC thông minh FOC XAG 100A (Chống nước IP67)', 
 '<p><strong>Mạch điều tốc ESC công nghệ điều khiển vector sóng sin FOC</strong> phản hồi ga tức thì, tái tạo năng lượng khi hãm động cơ, giúp hệ thống hoạt động mát hơn 10°C so với ESC truyền thống và tiết kiệm đáng kể năng lượng pin.</p><ul><li><strong>Dòng tải:</strong> 100A liên tục (Đỉnh tải 120A)</li><li><strong>Điện áp làm việc:</strong> 12S - 14S LiPo</li><li><strong>Tản nhiệt:</strong> Nhôm đúc nguyên khối, bọc keo chống nước tuyệt đối</li><li><strong>Tương thích:</strong> XAG P100, XAG P100 Pro, XAG P40</li></ul>', 
 2900000, 3600000, 25, 'images/products/esc_foc_xag_100a.jpg', 6, 'XAG P100, XAG P100 Pro, XAG P40', 110, 14),

(18, 'Mô-đun thu phát RTK & Truyền thông 4G trên máy bay XAG', 
 '<p><strong>Mô-đun RTK 4G tích hợp</strong> giúp kết nối trạm sóng Cors vệ tinh và mạng 4G di động nội địa. Đảm bảo dữ liệu bay, bản đồ số hóa ruộng vườn và trạng thái linh kiện được đồng bộ liên tục về điện thoại người vận hành.</p><ul><li><strong>Hỗ trợ hệ vệ tinh:</strong> GPS, GLONASS, BeiDou, Galileo đa băng tần</li><li><strong>Ăng-ten:</strong> Gốm xoắn ốc chống nhiễu sóng cao tần</li><li><strong>Tương thích:</strong> P100, P100 Pro, P40, V40, R150</li></ul>', 
 6200000, 7500000, 14, 'images/products/rtk_module_xag.jpg', 6, 'P100, P100 Pro, P40, V40, R150', 98, 11),

-- --- DANH MỤC 7: KHUNG SƯỜN & CÀNG ---
(19, 'Càng đáp & Tay đòn sợi carbon XAG P100 (Arm Carbon 50mm)', 
 '<p><strong>Ống tay đòn (Arm) bằng sợi carbon 3K</strong> đường kính 50mm gia cường chịu lực xoắn vặn và va đập mạnh. Đi kèm ngàm khóa cơ khí bằng hợp kim nhôm hàng không giúp gập mở máy bay nhanh chỉ trong vài giây.</p><ul><li><strong>Chất liệu:</strong> 100% Carbon dệt chéo 3K đúc chân không</li><li><strong>Đường kính:</strong> 50mm - Chiều dài tiêu chuẩn XAG P100</li><li><strong>Đặc tính:</strong> Nhẹ, triệt tiêu độ rung của motor công suất lớn</li><li><strong>Tương thích:</strong> XAG P100, XAG P100 Pro</li></ul>', 
 1850000, 2300000, 30, 'images/products/tay_don_carbon_p100.jpg', 7, 'XAG P100, XAG P100 Pro', 155, 23),

(20, 'Bộ khung gầm trung tâm & Giá đỡ bình thuốc XAG P40', 
 '<p><strong>Khung xương trung tâm máy bay XAG P40</strong> đúc từ hợp kim magiê-nhôm siêu bền, nhẹ hơn nhôm thông thường 30%. Có khớp tháo lắp nhanh hỗ trợ thay đổi nhanh chóng giữa thùng thuốc phun và thùng rải phân bón RevoCast.</p><ul><li><strong>Tương thích:</strong> XAG P40</li><li><strong>Tính năng:</strong> Khóa chốt cơ khí an toàn cho thùng tải 40kg</li><li><strong>Bảo vệ:</strong> Cụm đỡ bảo vệ an toàn cho khối pin và bo mạch chủ khi tiếp đất</li></ul>', 
 5800000, 6900000, 8, 'images/products/khung_gam_xag_p40.jpg', 7, 'XAG P40', 82, 4),

(21, 'Bánh xe gai địa hình & Trục truyền động Robot XAG R150', 
 '<p><strong>Cụm bánh xe cao su gai địa hình chuyên dụng</strong> trang bị trục truyền động vi sai chống trượt cho robot nông nghiệp mặt đất tự hành XAG R150. Vượt chướng ngại vật sình lầy ruộng lúa và đất dốc vườn cây ăn quả dễ dàng.</p><ul><li><strong>Đường kính bánh:</strong> 400mm</li><li><strong>Chất liệu:</strong> Cao su tự nhiên đúc đặc kháng mòn và chịu hóa chất</li><li><strong>Tương thích:</strong> Robot nông nghiệp XAG R150</li><li><strong>Khả năng:</strong> Lội sình lầy độ sâu lên đến 20cm</li></ul>', 
 2100000, 2650000, 15, 'images/products/banh_xe_xag_r150.jpg', 7, 'XAG R150', 65, 7);

-- ----------------------------------------------------------------------------
-- 4. CẤU TRÚC BẢNG: diachi (Địa chỉ nhận hàng của khách hàng)
-- ----------------------------------------------------------------------------
CREATE TABLE `diachi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoidung_id` int(11) NOT NULL,
  `diachi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `macdinh` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `nguoidung_id` (`nguoidung_id`),
  CONSTRAINT `diachi_ibfk_1` FOREIGN KEY (`nguoidung_id`) REFERENCES `nguoidung` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- DỮ LIỆU BẢNG: diachi
-- ----------------------------------------------------------------------------
INSERT INTO `diachi` (`id`, `nguoidung_id`, `diachi`, `macdinh`) VALUES
(1, 4, 'Ấp Bình Hòa, Xã Bình Thạnh Đông, Huyện Phú Tân, Tỉnh An Giang', 1),
(2, 5, 'Ấp Thạnh Lợi, Xã Thạnh Lộc, Huyện Cai Lậy, Tỉnh Tiền Giang', 1);

-- ----------------------------------------------------------------------------
-- 5. CẤU TRÚC BẢNG: donhang (Đơn đặt hàng linh kiện)
-- ----------------------------------------------------------------------------
CREATE TABLE `donhang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoidung_id` int(11) NOT NULL,
  `diachi_id` int(11) DEFAULT NULL,
  `ngay` datetime NOT NULL DEFAULT current_timestamp(),
  `tongtien` float NOT NULL DEFAULT 0,
  `ghichu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nguoidung_id` (`nguoidung_id`),
  KEY `diachi_id` (`diachi_id`),
  CONSTRAINT `donhang_ibfk_1` FOREIGN KEY (`nguoidung_id`) REFERENCES `nguoidung` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `donhang_ibfk_2` FOREIGN KEY (`diachi_id`) REFERENCES `diachi` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- DỮ LIỆU BẢNG: donhang (Đơn hàng mẫu thực tế)
-- ----------------------------------------------------------------------------
INSERT INTO `donhang` (`id`, `nguoidung_id`, `diachi_id`, `ngay`, `tongtien`, `ghichu`) VALUES
(1, 4, 1, '2026-11-10 09:30:00', 26850000, 'Giao gấp ra bến xe Miền Tây để kịp bảo dưỡng máy P100 vụ Đông Xuân'),
(2, 5, 2, '2026-11-15 14:15:00', 9600000, 'Kiểm tra kỹ cánh quạt và vòi phun trước khi đóng gói gửi chành xe');

-- ----------------------------------------------------------------------------
-- 6. CẤU TRÚC BẢNG: donhangct (Chi tiết đơn đặt hàng linh kiện)
-- ----------------------------------------------------------------------------
CREATE TABLE `donhangct` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `donhang_id` int(11) NOT NULL,
  `mathang_id` int(11) NOT NULL,
  `dongia` float NOT NULL DEFAULT 0,
  `soluong` int(11) NOT NULL DEFAULT 1,
  `thanhtien` float NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `donhang_id` (`donhang_id`),
  KEY `mathang_id` (`mathang_id`),
  CONSTRAINT `donhangct_ibfk_1` FOREIGN KEY (`donhang_id`) REFERENCES `donhang` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `donhangct_ibfk_2` FOREIGN KEY (`mathang_id`) REFERENCES `mathang` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- DỮ LIỆU BẢNG: donhangct (Chi tiết mặt hàng trong đơn hàng)
-- ----------------------------------------------------------------------------
INSERT INTO `donhangct` (`id`, `donhang_id`, `mathang_id`, `dongia`, `soluong`, `thanhtien`) VALUES
-- Đơn hàng 1: 1 Pin B13960S (25.500.000) + 1 Cặp cánh quạt P100 Pro (1.350.000) = 26.850.000
(1, 1, 7, 25500000, 1, 25500000),
(2, 1, 4, 1350000, 1, 1350000),
-- Đơn hàng 2: 2 Cặp cánh quạt P40 (2 x 1.200.000) + 1 Vòi phun RevoSpray 2 (3.350.000) + 1 Cụm bơm SuperX4 (4.200.000) = 9.950.000
(3, 2, 5, 1200000, 2, 2400000),
(4, 2, 13, 3350000, 1, 3350000),
(5, 2, 10, 4200000, 1, 4200000);

-- ----------------------------------------------------------------------------
-- KHÔI PHỤC KIỂM TRA KHÓA NGOẠI & KẾT THÚC TRANSACTION
-- ----------------------------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
