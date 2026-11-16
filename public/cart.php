<?php
include("inc/top.php");
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border">
        <li class="breadcrumb-item"><a href="index.php" class="text-danger"><i class="bi bi-house-door-fill"></i> Trang chủ</a></li>
        <li class="breadcrumb-item active" aria-current="page">Giỏ hàng của bạn</li>
    </ol>
</nav>

<?php if(demhangtronggio() == 0): ?>
<div class="text-center py-5">
    <div class="bg-white p-5 rounded-4 shadow-sm border mx-auto" style="max-width: 520px;">
        <i class="bi bi-cart-x text-muted display-2 mb-3"></i>
        <h3 class="fw-bold text-dark">Giỏ hàng hiện đang trống!</h3>
        <p class="text-muted">Quý khách chưa chọn linh kiện nào. Hãy dạo một vòng cửa hàng để tìm phụ tùng thay thế cho máy bay XAG của mình nhé.</p>
        <a href="index.php" class="btn btn-danger px-4 py-2 rounded-pill fw-bold mt-3">
            <i class="bi bi-arrow-left me-1"></i> Khám phá linh kiện ngay
        </a>
    </div>
</div>

<?php else: ?>
<div class="row g-4 mb-5">
    <!-- Danh sách sản phẩm trong giỏ -->
    <div class="col-lg-8">
        <div class="bg-white p-4 rounded-4 shadow-sm border">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h5 fw-bold text-dark mb-0">
                    <i class="bi bi-cart3 text-danger me-2"></i>Chi tiết linh kiện đã chọn (<?php echo demhangtronggio(); ?>)
                </h3>
                <a href="index.php?action=xoagiohang" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Bạn có chắc muốn xóa tất cả sản phẩm khỏi giỏ hàng?');">
                    <i class="bi bi-trash3 me-1"></i> Xóa tất cả
                </a>
            </div>

            <form action="index.php" method="get">
                <input type="hidden" name="action" value="capnhatgio">

                <div class="table-responsive">
                    <table class="table cart-table align-middle">
                        <thead>
                            <tr>
                                <th>Linh kiện</th>
                                <th class="text-center">Đơn giá</th>
                                <th class="text-center" style="width: 120px;">Số lượng</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($giohang as $id => $mh): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="../<?php echo htmlspecialchars($mh["hinhanh"]); ?>" 
                                             alt="<?php echo htmlspecialchars($mh["tenmathang"]); ?>" 
                                             class="rounded border" 
                                             style="width: 65px; height: 65px; object-fit: cover;">
                                        <div>
                                            <a href="index.php?action=detail&id=<?php echo $id; ?>" class="fw-bold text-dark text-decoration-none">
                                                <?php echo htmlspecialchars($mh["tenmathang"]); ?>
                                            </a>
                                            <div class="small text-muted">Mã SP: #XAG-<?php echo $id; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center text-nowrap fw-semibold">
                                    <?php echo number_format($mh["giaban"]); ?> đ
                                </td>
                                <td class="text-center">
                                    <input type="number" 
                                           name="mh[<?php echo $id; ?>]" 
                                           value="<?php echo $mh["soluong"]; ?>" 
                                           min="0" 
                                           class="form-control form-control-sm text-center fw-bold mx-auto" 
                                           style="width: 75px;">
                                </td>
                                <td class="text-end text-nowrap fw-bold text-danger">
                                    <?php echo number_format($mh["thanhtien"]); ?> đ
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Tiếp tục mua thêm
                    </a>
                    <button type="submit" class="btn btn-warning fw-bold">
                        <i class="bi bi-arrow-clockwise me-1"></i> Cập nhật giỏ hàng
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tóm tắt & Nút thanh toán -->
    <div class="col-lg-4">
        <div class="summary-card">
            <h4 class="h5 fw-bold text-dark mb-3">TÓM TẮT ĐƠN HÀNG</h4>
            
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Tổng số lượng:</span>
                <span class="fw-bold"><?php echo demhangtronggio(); ?> linh kiện</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Tạm tính:</span>
                <span class="fw-bold"><?php echo number_format(tinhtiengiohang()); ?> đ</span>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <span class="text-muted">Phí vận chuyển:</span>
                <span class="text-success fw-bold">Tính khi xác nhận chành/bến</span>
            </div>
            
            <hr>

            <div class="d-flex justify-content-between align-items-baseline mb-4">
                <span class="fs-6 fw-bold text-dark">TỔNG THANH TOÁN:</span>
                <span class="fs-4 fw-bolder text-danger"><?php echo number_format(tinhtiengiohang()); ?> đ</span>
            </div>

            <div class="d-grid gap-2">
                <a href="index.php?action=thanhtoan" class="btn btn-danger btn-lg fw-bold py-3">
                    <i class="bi bi-credit-card-fill me-2"></i> TIẾN HÀNH ĐẶT HÀNG
                </a>
            </div>

            <div class="mt-4 p-3 bg-light rounded-3 small text-secondary">
                <div class="mb-2"><i class="bi bi-shield-check text-success me-1"></i> Kiểm tra hàng chuẩn mã XAG trước khi nhận</div>
                <div class="mb-2"><i class="bi bi-truck text-danger me-1"></i> Hỗ trợ gửi chành xe, nhà xe lấy ngay trong ngày</div>
                <div><i class="bi bi-telephone-fill text-danger me-1"></i> Hotline: <strong>0833 609 329</strong></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
include("inc/bottom.php");
?>
