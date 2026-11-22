<?php 
include("inc/top.php"); 
?>

<!-- Breadcrumb -->
<section class="py-3 bg-light border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="collection-breadcrumb mb-0">
            <a href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a>
            <span class="mx-1">/</span>
            <a href="index.php?action=giohang">Giỏ hàng</a>
            <span class="mx-1">/</span>
            <span class="text-dark fw-semibold">Thanh toán & Đặt hàng</span>
        </nav>
    </div>
</section>

<div class="container mb-5">
    <div class="row g-4">
        <!-- Cột trái: Thông tin nhận hàng -->
        <div class="col-lg-7">
            <div class="bg-white p-4 p-md-5 rounded border">
                <h2 class="h5 fw-bold text-dark text-uppercase border-bottom pb-3 mb-4">
                    <i class="bi bi-truck text-danger me-2"></i>Thông tin nhận hàng
                </h2>

                <?php if(!empty($tb_loi)): ?>
                <div class="alert alert-danger d-flex align-items-center mb-4 py-3" role="alert">
                    <i class="bi bi-exclamation-octagon-fill fs-4 me-3 flex-shrink-0 text-danger"></i>
                    <div>
                        <strong class="d-block mb-1">Không thể hoàn tất đặt hàng:</strong>
                        <span class="small"><?php echo htmlspecialchars($tb_loi); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if(isset($_SESSION["khachhang"])): ?>
                <div class="alert alert-light border py-2 mb-4 d-flex align-items-center">
                    <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                    <div class="small">Tài khoản thành viên: <strong><?php echo htmlspecialchars($_SESSION["khachhang"]["hoten"]); ?></strong></div>
                </div>

                <form method="post" action="index.php">
                    <input type="hidden" name="action" value="luudonhang">
                    <input type="hidden" name="txtid" value="<?php echo $_SESSION["khachhang"]["id"]; ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Địa chỉ Email:</label>
                        <input type="email" class="form-control" name="txtemail" value="<?php echo htmlspecialchars($_SESSION["khachhang"]["email"]); ?>" readonly>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Họ và tên người nhận:</label>
                            <input type="text" class="form-control" name="txthoten" value="<?php echo htmlspecialchars($_SESSION["khachhang"]["hoten"]); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Số điện thoại:</label>
                            <input type="text" class="form-control" name="txtsodienthoai" value="<?php echo htmlspecialchars($_SESSION["khachhang"]["sodienthoai"]); ?>" readonly>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Địa chỉ giao hàng / Nhà xe nhận hàng:</label>
                        <textarea class="form-control" name="txtdiachi" rows="3" placeholder="Nhập địa chỉ nhận hàng hoặc thông tin nhà xe, chành xe..." required><?php echo isset($diachi["diachi"]) ? htmlspecialchars($diachi["diachi"]) : ''; ?></textarea>
                        <small class="text-muted">Ví dụ: Chành xe Tô Châu (Trạm Long Xuyên) hoặc địa chỉ nhà riêng...</small>
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold py-3 text-uppercase">
                        Xác nhận đặt mua sản phẩm
                    </button>
                </form>

                <?php else: ?>
                <div class="alert alert-light border mb-4 d-flex justify-content-between align-items-center small">
                    <span>Đã có tài khoản thành viên LPT DRONE?</span>
                    <a href="index.php?action=dangnhap" class="text-danger fw-bold">Đăng nhập</a>
                </div>

                <form method="post" action="index.php">
                    <input type="hidden" name="action" value="luudonhang">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Email nhận thông báo đơn hàng <span class="text-danger">*</span>:</label>
                        <input type="email" class="form-control" name="txtemail" placeholder="email@gmail.com" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Họ và tên người nhận <span class="text-danger">*</span>:</label>
                            <input type="text" class="form-control" name="txthoten" placeholder="Nguyễn Văn A" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Số điện thoại liên hệ <span class="text-danger">*</span>:</label>
                            <input type="tel" class="form-control" name="txtsodienthoai" placeholder="0988xxxxxx" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Địa chỉ giao hàng / Thông tin chành xe <span class="text-danger">*</span>:</label>
                        <textarea class="form-control" name="txtdiachi" rows="3" placeholder="Nhập địa chỉ nhà riêng hoặc nhà xe gửi hàng mong muốn..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold py-3 text-uppercase">
                        Xác nhận đặt mua sản phẩm
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cột phải: Xem lại đơn hàng -->
        <div class="col-lg-5">
            <div class="bg-white p-4 rounded border">
                <h3 class="h6 fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">
                    Chi tiết đơn hàng (<?php echo demhangtronggio(); ?> sản phẩm)
                </h3>

                <div class="table-responsive mb-3">
                    <table class="table table-sm align-middle">
                        <tbody>
                            <?php foreach($giohang as $id => $mh): ?>
                            <tr>
                                <td style="width: 50px;">
                                    <img src="../<?php echo htmlspecialchars($mh["hinhanh"]); ?>" 
                                         alt="<?php echo htmlspecialchars($mh["tenmathang"]); ?>" 
                                         class="rounded border bg-light" 
                                         style="width: 44px; height: 44px; object-fit: contain; padding: 2px;">
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark text-truncate" style="max-width: 180px;">
                                        <?php echo htmlspecialchars($mh["tenmathang"]); ?>
                                    </div>
                                    <small class="text-muted"><?php echo number_format($mh["giaban"]); ?> đ x <?php echo $mh["soluong"]; ?></small>
                                </td>
                                <td class="text-end fw-bold text-dark small text-nowrap">
                                    <?php echo number_format($mh["thanhtien"]); ?> đ
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-3 bg-light rounded small mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-secondary">Tạm tính:</span>
                        <strong class="text-dark"><?php echo number_format(tinhtiengiohang()); ?> đ</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Phương thức:</span>
                        <span class="text-dark fw-semibold">Ship COD / Chuyển khoản</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-baseline">
                        <span class="fw-bold text-dark">TỔNG TIỀN:</span>
                        <span class="fs-4 fw-bolder text-danger"><?php echo number_format(tinhtiengiohang()); ?> đ</span>
                    </div>
                </div>

                <div class="small text-secondary">
                    <i class="bi bi-shield-check text-success me-1"></i> Quý khách được mở bưu kiện kiểm tra sản phẩm chuẩn thông số trước khi thanh toán tiền cho bên giao nhận.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("inc/bottom.php"); ?>
