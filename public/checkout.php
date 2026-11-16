<?php 
include("inc/top.php"); 
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border">
        <li class="breadcrumb-item"><a href="index.php" class="text-danger"><i class="bi bi-house-door-fill"></i> Trang chủ</a></li>
        <li class="breadcrumb-item"><a href="index.php?action=giohang" class="text-secondary">Giỏ hàng</a></li>
        <li class="breadcrumb-item active" aria-current="page">Thanh toán & Đặt hàng</li>
    </ol>
</nav>

<div class="row g-4 mb-5">
    <!-- Cột trái: Thông tin nhận hàng -->
    <div class="col-lg-7">
        <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">
            <h3 class="h4 fw-bold text-dark mb-4">
                <i class="bi bi-truck text-danger me-2"></i>THÔNG TIN GIAO NHẬN HÀNG
            </h3>

            <?php if(isset($_SESSION["khachhang"])): ?>
            <div class="alert alert-info py-2 mb-4 d-flex align-items-center">
                <i class="bi bi-info-circle-fill fs-5 me-2"></i>
                <div>Quý khách đang đăng nhập với tài khoản: <strong><?php echo htmlspecialchars($_SESSION["khachhang"]["hoten"]); ?></strong></div>
            </div>

            <form method="post" action="index.php">
                <input type="hidden" name="action" value="luudonhang">
                <input type="hidden" name="txtid" value="<?php echo $_SESSION["khachhang"]["id"]; ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Địa chỉ Email:</label>
                    <input type="email" class="form-control" name="txtemail" value="<?php echo htmlspecialchars($_SESSION["khachhang"]["email"]); ?>" readonly>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Họ và tên người nhận:</label>
                        <input type="text" class="form-control" name="txthoten" value="<?php echo htmlspecialchars($_SESSION["khachhang"]["hoten"]); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Số điện thoại liên hệ:</label>
                        <input type="text" class="form-control" name="txtsodienthoai" value="<?php echo htmlspecialchars($_SESSION["khachhang"]["sodienthoai"]); ?>" readonly>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Địa chỉ nhận hàng / Tên bến xe, chành xe nhận gửi:</label>
                    <textarea class="form-control" name="txtdiachi" rows="3" placeholder="Nhập địa chỉ nhận hàng chi tiết hoặc tên chành xe gửi hàng..." required><?php echo isset($diachi["diachi"]) ? htmlspecialchars($diachi["diachi"]) : ''; ?></textarea>
                    <small class="text-muted">Ví dụ: Nhà xe Tô Châu / Trạm xe Phương Trang / Địa chỉ nhà riêng...</small>
                </div>

                <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold py-3">
                    <i class="bi bi-check-circle-fill me-2"></i> HOÀN TẤT ĐẶT HÀNG
                </button>
            </form>

            <?php else: ?>
            <div class="alert alert-light border mb-4 d-flex justify-content-between align-items-center">
                <span>Đã có tài khoản thành viên LPT DRONE?</span>
                <a href="index.php?action=dangnhap" class="btn btn-sm btn-outline-danger fw-semibold">Đăng nhập ngay</a>
            </div>

            <form method="post" action="index.php">
                <input type="hidden" name="action" value="luudonhang">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Địa chỉ Email <span class="text-danger">*</span>:</label>
                    <input type="email" class="form-control" name="txtemail" placeholder="email@gmail.com" required>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Họ và tên người nhận <span class="text-danger">*</span>:</label>
                        <input type="text" class="form-control" name="txthoten" placeholder="Nguyễn Văn A" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Số điện thoại di động <span class="text-danger">*</span>:</label>
                        <input type="tel" class="form-control" name="txtsodienthoai" placeholder="0988xxxxxx" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Địa chỉ nhận hàng / Chành xe gửi nhận <span class="text-danger">*</span>:</label>
                    <textarea class="form-control" name="txtdiachi" rows="3" placeholder="Nhập địa chỉ nhà riêng hoặc nhà xe, chành xe mong muốn nhận..." required></textarea>
                </div>

                <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold py-3">
                    <i class="bi bi-check-circle-fill me-2"></i> HOÀN TẤT ĐẶT HÀNG
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cột phải: Xem lại đơn hàng -->
    <div class="col-lg-5">
        <div class="summary-card">
            <h4 class="h5 fw-bold text-dark mb-3">
                <i class="bi bi-receipt text-danger me-2"></i>ĐƠN HÀNG CỦA BẠN
            </h4>

            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <tbody>
                        <?php foreach($giohang as $id => $mh): ?>
                        <tr>
                            <td style="width: 55px;">
                                <img src="../<?php echo htmlspecialchars($mh["hinhanh"]); ?>" 
                                     alt="<?php echo htmlspecialchars($mh["tenmathang"]); ?>" 
                                     class="rounded border" 
                                     style="width: 48px; height: 48px; object-fit: cover;">
                            </td>
                            <td>
                                <div class="small fw-bold text-dark text-truncate" style="max-width: 200px;">
                                    <?php echo htmlspecialchars($mh["tenmathang"]); ?>
                                </div>
                                <small class="text-muted"><?php echo number_format($mh["giaban"]); ?> đ x <?php echo $mh["soluong"]; ?></small>
                            </td>
                            <td class="text-end fw-bold text-dark text-nowrap">
                                <?php echo number_format($mh["thanhtien"]); ?> đ
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <hr>

            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Tổng phụ tùng:</span>
                <span class="fw-bold"><?php echo demhangtronggio(); ?> món</span>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <span class="text-muted">Hình thức thanh toán:</span>
                <span class="fw-bold text-primary">COD / Chuyển khoản</span>
            </div>

            <div class="d-flex justify-content-between align-items-baseline mb-4 p-3 bg-light rounded-3">
                <span class="fs-6 fw-bold text-dark">TỔNG CỘNG:</span>
                <span class="fs-4 fw-bolder text-danger"><?php echo number_format(tinhtiengiohang()); ?> đ</span>
            </div>

            <div class="p-3 bg-light rounded-3 small text-secondary">
                <div class="fw-bold text-dark mb-1"><i class="bi bi-shield-check text-success me-1"></i> Chính sách bảo vệ người mua:</div>
                <div>Được mở gói hàng kiểm tra đúng linh kiện, đúng mã dòng máy XAG trước khi thanh toán cho đơn vị vận chuyển.</div>
            </div>
        </div>
    </div>
</div>

<?php include("inc/bottom.php"); ?>
