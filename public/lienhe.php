<?php
include("inc/top.php");
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border">
        <li class="breadcrumb-item"><a href="index.php" class="text-danger"><i class="bi bi-house-door-fill"></i> Trang chủ</a></li>
        <li class="breadcrumb-item active" aria-current="page">Liên hệ & Hỗ trợ kỹ thuật</li>
    </ol>
</nav>

<div class="row g-4 mb-5">
    <div class="col-lg-6">
        <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border h-100">
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 mb-2">HOTLINE 24/7</span>
            <h2 class="h3 fw-bold text-dark mb-4">TRUNG TÂM KỸ THUẬT LPT DRONE</h2>
            
            <div class="d-flex align-items-start gap-3 mb-4">
                <div class="rounded-circle bg-danger-subtle text-danger p-3 fs-4">
                    <i class="bi bi-telephone-fill"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Hotline Kỹ thuật & Bán hàng</h5>
                    <a href="tel:0833609329" class="fs-4 text-danger fw-bolder text-decoration-none">0833 609 329</a>
                    <p class="text-muted small mb-0">Hỗ trợ khẩn cấp mùa vụ 24/7 (Gọi điện / Zalo)</p>
                </div>
            </div>

            <div class="d-flex align-items-start gap-3 mb-4">
                <div class="rounded-circle bg-danger-subtle text-danger p-3 fs-4">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Kho Linh Kiện & Trạm Kỹ Thuật</h5>
                    <p class="text-secondary mb-1">Quốc lộ 91, Thị trấn Cái Dầu, Huyện Châu Phú, Tỉnh An Giang</p>
                    <p class="text-muted small mb-0">Giao hàng và hỗ trợ kỹ thuật trên toàn bộ 63 tỉnh thành</p>
                </div>
            </div>

            <div class="d-flex align-items-start gap-3">
                <div class="rounded-circle bg-danger-subtle text-danger p-3 fs-4">
                    <i class="bi bi-envelope-fill"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Thư Điện Tử (Email)</h5>
                    <p class="text-secondary mb-0">hotro@lptdrone.vn | linhkienxag@gmail.com</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border h-100">
            <h3 class="h4 fw-bold text-dark mb-3">GỬI YÊU CẦU TƯ VẤN LINH KIỆN</h3>
            <p class="text-muted small mb-4">Quý khách cần tìm mã linh kiện hiếm hoặc cần kỹ thuật viên hỗ trợ kiểm tra tình trạng máy bay? Vui lòng để lại thông tin dưới đây:</p>

            <form onsubmit="alert('Cảm ơn bạn! Kỹ thuật viên LPT DRONE sẽ liên hệ lại qua số điện thoại trong 10 phút.'); return false;">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Họ và tên của bạn:</label>
                    <input type="text" class="form-control" placeholder="Ví dụ: Anh Ba An Giang" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Số điện thoại liên hệ:</label>
                    <input type="tel" class="form-control" placeholder="0833 xxx xxx" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Dòng máy bay bạn đang dùng:</label>
                    <select class="form-select">
                        <option>XAG P100 Pro</option>
                        <option>XAG P100</option>
                        <option>XAG P40</option>
                        <option>XAG V40</option>
                        <option>Robot mặt đất XAG R150</option>
                        <option>Khác...</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Linh kiện cần tìm hoặc mô tả tình trạng lỗi:</label>
                    <textarea class="form-control" rows="3" placeholder="Ví dụ: Cần mua cụm bơm nhu động SuperX4 hoặc lỗi báo quá nhiệt motor số 2..." required></textarea>
                </div>
                <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold">
                    <i class="bi bi-send-fill me-2"></i> GỬI YÊU CẦU KỸ THUẬT
                </button>
            </form>
        </div>
    </div>
</div>

<?php
include("inc/bottom.php");
?>
