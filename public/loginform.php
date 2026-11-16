<?php
include("inc/top.php");
?>

<div class="row justify-content-center my-5">
    <div class="col-md-6 col-lg-5">
        <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">
            <div class="text-center mb-4">
                <img src="../images/logo.png" alt="LPT DRONE" class="img-fluid mb-2" style="max-height: 70px; border-radius: 8px;">
                <h3 class="fw-bold text-dark">ĐĂNG NHẬP THÀNH VIÊN</h3>
                <p class="text-muted small">Quản lý lịch sử mua linh kiện và theo dõi đơn hàng XAG</p>
            </div>

            <form method="post" action="index.php">
                <input type="hidden" name="action" value="xldangnhap">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Địa chỉ Email đăng nhập:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                        <input class="form-control" type="email" name="txtemail" placeholder="Ví dụ: khachhang@xag.vn" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Mật khẩu:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                        <input class="form-control" type="password" name="txtmatkhau" placeholder="Nhập mật khẩu" required>
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button class="btn btn-danger btn-lg fw-bold py-2" type="submit">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập ngay
                    </button>
                </div>
            </form>

            <div class="p-3 bg-light rounded-3 small text-muted mb-3">
                <strong>Tài khoản khách hàng mẫu thử nghiệm:</strong><br>
                Email: <code>khachhang@xag.vn</code> | Pass: <code>123456</code>
            </div>

            <div class="text-center small text-muted">
                <span>Chưa có tài khoản? Khi đặt hàng tại trang Thanh toán, hệ thống sẽ tự động tạo thông tin cho quý khách.</span>
            </div>
        </div>
    </div>
</div>

<?php
include("inc/bottom.php");
?>
