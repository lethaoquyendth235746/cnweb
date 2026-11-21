<?php
include("inc/top.php");
?>

<!-- Breadcrumb -->
<section class="py-3 bg-light border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="collection-breadcrumb mb-0">
            <a href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a>
            <span class="mx-1">/</span>
            <span class="text-dark fw-semibold">Đăng nhập tài khoản</span>
        </nav>
    </div>
</section>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="bg-white p-4 p-md-5 rounded border">
                <div class="text-center mb-4">
                    <img src="../images/logo.png" alt="LPT DRONE" class="img-fluid mb-2" style="max-height: 55px; border-radius: 6px;">
                    <h1 class="h5 fw-bold text-dark text-uppercase mb-1">Đăng nhập tài khoản</h1>
                    <p class="text-muted small">Theo dõi đơn đặt hàng linh kiện XAG của bạn</p>
                </div>

                <form method="post" action="index.php">
                    <input type="hidden" name="action" value="xldangnhap">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Địa chỉ Email:</label>
                        <input class="form-control" type="email" name="txtemail" placeholder="khachhang@xag.vn" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Mật khẩu:</label>
                        <input class="form-control" type="password" name="txtmatkhau" placeholder="Nhập mật khẩu" required>
                    </div>

                    <button class="btn btn-danger w-100 fw-bold py-2 mb-3 text-uppercase" type="submit">
                        Đăng nhập
                    </button>
                </form>

                <div class="p-3 bg-light rounded small text-secondary mb-3">
                    <div class="fw-bold text-dark mb-1">Tài khoản khách hàng thử nghiệm:</div>
                    Email: <code>khachhang@xag.vn</code> | Pass: <code>123456</code>
                </div>

                <div class="text-center small text-muted">
                    <a href="index.php" class="text-secondary">&larr; Quay lại cửa hàng</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include("inc/bottom.php");
?>
