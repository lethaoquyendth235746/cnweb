# Kế hoạch nâng cấp khu vực `admin/` của dự án XAG Store

> **Mục tiêu**
> 1. Đưa giao diện quản trị lên chuẩn hiện đại, responsive, sử dụng Tailwind CSS + Lucide Icons + Chart.js.
> 2. Xây dựng tầng Service (backend) an toàn, có transaction, state‑machine, phân quyền.
> 3. Thêm bảng quản lý kho (phiếu nhập) và đồng bộ với cơ sở dữ liệu hiện có.
> 4. Không làm gián đoạn phần `public/` đang chạy.

---

## Giai đoạn 1 – Khảo sát CSDL & Model (1‑2 ngày)
| Bước | Công việc | Đầu ra |
|------|-----------|--------|
| 1.1 | Đọc file `data/xag_store.sql` và các file trong `model/`. | Bảng tóm tắt cấu trúc (bảng, PK, FK, cột). |
| 1.2 | Kiểm tra tính đồng nhất giữa schema và các class model. | Danh sách **điểm lỗi/thiếu** (nếu có). |
| 1.3 | Định nghĩa bảng kho cần thiết (`phieunhap`, `ct_phieunhap`). | File migration SQL `data/update_admin_features.sql`. |
| 1.4 | Ghi lại đề xuất thay đổi (cột `trangthai` trong `donhang` đã có, chỉ cần chuẩn hoá giá trị). | Tài liệu “Database Gap Analysis”. |

---

## Giai đoạn 2 – Xây dựng tầng Service (Backend) (4‑5 ngày)
| Ngày | Nhiệm vụ | Chi tiết | Kiểm thử |
|------|----------|----------|----------|
| Ngày 1 | Tạo thư mục `admin/services/` và các file stub. | Tạo class rỗng: `DashboardService`, `OrderAdminService`, `InventoryAdminService`, `ProductAdminService`, `CategoryAdminService`, `UserAdminService`, `CustomerAdminService`. | `php -l` không lỗi cú pháp. |
| Ngày 2 | Cài đặt helper `DATABASE` để trả về PDO và hỗ trợ transaction. | Kiểm tra kết nối tới `xag_store`. |
| Ngày 3‑4 | Triển khai service thực tế: 
- **OrderAdminService** – trạng thái, transaction + `SELECT … FOR UPDATE` trên `mathang`. 
- **InventoryAdminService** – tạo phiếu nhập, cập nhật `soluongton` trong transaction. 
- **ProductAdminService** – CRUD + upload ảnh + validation. | Unit‑test (PHPUnit) cho mỗi method; chạy `php vendor/bin/phpunit`. |
| Ngày 5 | Thêm `ServiceException` và `FlashMessage` (sử dụng `$_SESSION['admin_flash']`). Đảm bảo mọi phương thức bắt `PDOException` → ném `ServiceException`. | Kiểm tra log `admin/logs/`. |

---

## Giai đoạn 3 – Thiết kế UI Modern (Tailwind + Chart.js) (5‑6 ngày)
| Ngày | Nội dung | Kết quả |
|------|----------|----------|
| Ngày 1 | **Cấu trúc thư mục UI**: `admin/inc/` (layout, sidebar, topbar, toast), `admin/assets/` (css/app.css, js/app.js). | Các file mẫu `layout.php`, `sidebar.php`, `topbar.php`, `toast.php`. |
| Ngày 2 | **Sidebar** – menu, badge đếm (đơn chờ, hàng sắp hết). Responsive cho mobile. |
| Ngày 3 | **Topbar** – breadcrumb, search, avatar + dropdown (đổi mật khẩu, logout, view public). |
| Ngày 4 | **Toast** – hiển thị flash message, tự ẩn 3 s, transition Tailwind. |
| Ngày 5‑6 | **Các trang chính**: 
- `admin/index.php` (Dashboard KPI + Chart.js). 
- `admin/orders.php` (bảng, tabs, modal chi tiết). 
- `admin/products.php` (bảng, modal add/edit). 
- `admin/categories.php`, `admin/inventory.php`, `admin/users.php`, `admin/customers.php`. 
Kết nối mỗi trang với Service tương ứng (PRG). |

---

## Giai đoạn 4 – Migration & Triển khai dần (3‑4 ngày)
| Bước | Công việc | Cách thực hiện |
|------|-----------|----------------|
| 4.1 | **Chạy migration**: thực thi `data/update_admin_features.sql` để tạo bảng `phieunhap` & `ct_phieunhap`. |
```powershell
php -r "require 'model/database.php'; (new DATABASE)->connect()->exec(file_get_contents('data/update_admin_features.sql'));"
``` |
| 4.2 | **Redirect các URL cũ** → `admin/redirect.php` hoặc `.htaccess` (`RewriteRule ^admin/(ktnguoidung|qldanhmuc|qlmathang|qlnguoidung)/(.*)$ /admin/index.php [R=302,L]`). |
| 4.3 | **Thay thế từng module cũ** (theo thứ tự): 
1) `qtmathang` → `admin/products.php`
2) `qldanhmuc` → `admin/categories.php`
3) `qlnguoidung` → `admin/users.php`
4) `ktnguoidung` (đăng nhập) → `admin/login.php` mới (sử dụng `UserAdminService`). |
| 4.4 | **Kiểm tra toàn bộ workflow**: 
- Đăng nhập admin → Dashboard → Đơn hàng → Thay đổi trạng thái → Kiểm tra tồn kho. 
- Tạo phiếu nhập → Xem lịch sử → Kiểm tra `soluongton` tăng đúng. |
| 4.5 | **Backup & Rollback**: `mysqldump -u root xag_store > backup_$(date +%Y%m%d).sql`. Lưu ở `data/backup/`. |
| 4.6 | **Đánh giá hiệu năng**: kiểm tra thời gian truy vấn các bảng lớn (`donhang`, `mathang`). Nếu cần, tạo index phụ (`idx_donhang_trangthai`, `idx_mathang_soluongton`). |

---

## Giai đoạn 5 – Kiểm thử cuối cùng & Bàn giao (1‑2 ngày)
| Hoạt động | Mô tả |
|----------|------|
| **Kiểm thử chức năng** | Đăng nhập, duyệt dashboard, quản lý đơn, tạo phiếu nhập, CRUD sản phẩm, quản lý người dùng. |
| **Kiểm thử bảo mật** | Xác minh tất cả query dùng prepared statements, XSS (`htmlspecialchars`), CSRF token trong form, mật khẩu hash. |
| **Kiểm thử tải** | Dùng `ab` hoặc `wrk` giả lập 50‑100 đồng thời truy cập dashboard, đo thời gian phản hồi < 2 s. |
| **Tài liệu cuối** | - **README_admin.md** (cài đặt, cấu hình Tailwind CDN, chạy migration). 
- **API Service Docs** (mô tả method, tham số, trả về). |
| **Bàn giao** | Đưa source lên `c:\xampp\htdocs\weblinhkiendrone\admin\`, cập nhật `.gitignore` nếu có, thông báo cho người dùng. |

---

## Tổng quan lịch trình (15‑19 ngày)
| Tuần | Giai đoạn |
|------|-----------|
| Ngày 1‑2 | Giai đoạn 1 – Khảo sát DB & Model |
| Ngày 3‑7 | Giai đoạn 2 – Service layer |
| Ngày 8‑13 | Giai đoạn 3 – UI modern |
| Ngày 14‑16 | Giai đoạn 4 – Migration, chuyển đổi từng module |
| Ngày 17‑18 | Giai đoạn 5 – Kiểm thử, tài liệu, bàn giao |

---

## Các công cụ hỗ trợ trong quá trình
| Công cụ | Mục đích |
|--------|----------|
| `php -r …` | Thực thi migration trong sandbox. |
| Tailwind CDN | Không cần build, giảm thời gian. |
| Chart.js CDN | Vẽ biểu đồ KPI. |
| PHPUnit | Unit test các Service. |
| Git | Quản lý commit từng giai đoạn, dễ rollback. |
| mysqldump | Backup DB trước migration. |

---

**Tiếp theo**
- Nếu đồng ý với kế hoạch này, mình sẽ tiếp tục tạo file migration `data/update_admin_features.sql` và các file service stub trong `admin/services/`. 
- Hoặc nếu cần chỉnh sửa gì (thêm/bớt bước, thay đổi thời gian), hãy cho mình biết để cập nhật ngay.
