<?php
include("inc/top.php");
?>

<!-- Breadcrumb -->
<section class="py-3 bg-light border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="collection-breadcrumb mb-0">
            <a href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a>
            <span class="mx-1">/</span>
            <span class="text-dark fw-semibold">Giỏ hàng của bạn</span>
        </nav>
    </div>
</section>

<div class="container mb-5">
    <?php if(demhangtronggio() == 0): ?>
    <div class="text-center py-5">
        <div class="bg-white p-5 rounded border mx-auto" style="max-width: 480px;">
            <i class="bi bi-bag-x text-muted display-3 mb-3"></i>
            <h3 class="h4 fw-bold text-dark">Giỏ hàng của bạn đang trống</h3>
            <p class="text-muted small">Bạn chưa thêm sản phẩm phụ tùng nào vào giỏ. Hãy tham khảo danh mục sản phẩm để bảo dưỡng máy bay XAG.</p>
            <a href="index.php" class="btn btn-danger px-4 py-2 fw-bold mt-2">
                <i class="bi bi-arrow-left me-1"></i> Khám phá sản phẩm ngay
            </a>
        </div>
    </div>

    <?php else: ?>
    <div class="row g-4">
        <!-- Bảng danh sách mặt hàng -->
        <div class="col-lg-8">
            <div class="bg-white p-4 rounded border">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h2 class="h5 fw-bold text-dark mb-0">
                        Giỏ hàng (<?php echo demhangtronggio(); ?> sản phẩm)
                    </h2>
                    <a href="index.php?action=xoagiohang" class="text-danger small" onclick="return confirm('Bạn có chắc muốn xóa tất cả sản phẩm khỏi giỏ hàng?');">
                        <i class="bi bi-trash3 me-1"></i> Xóa tất cả
                    </a>
                </div>

                <form action="index.php" method="get">
                    <input type="hidden" name="action" value="capnhatgio">

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr class="text-secondary small text-uppercase">
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Đơn giá</th>
                                    <th class="text-center" style="width: 110px;">Số lượng</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($giohang as $id => $mh): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?php echo lay_hinhanh_mathang($mh["hinhanh"] ?? ""); ?>" 
                                                 alt="<?php echo htmlspecialchars($mh["tenmathang"]); ?>" 
                                                 class="rounded border bg-light" 
                                                 style="width: 60px; height: 60px; object-fit: contain; padding: 4px;">
                                            <div>
                                                <a href="index.php?action=detail&id=<?php echo $id; ?>" class="fw-bold text-dark text-decoration-none small d-block">
                                                    <?php echo htmlspecialchars($mh["tenmathang"]); ?>
                                                </a>
                                                <span class="text-muted" style="font-size: 0.75rem;">Mã SP: #XAG-<?php echo $id; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center text-nowrap small fw-semibold">
                                        <?php echo number_format($mh["giaban"]); ?> đ
                                    </td>
                                    <td class="text-center">
                                        <input type="number" 
                                               name="mh[<?php echo $id; ?>]" 
                                               value="<?php echo $mh["soluong"]; ?>" 
                                               min="0" 
                                               class="form-control form-control-sm text-center fw-bold mx-auto" 
                                               style="width: 70px;">
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
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Tiếp tục mua thêm
                        </a>
                        <button type="submit" class="btn btn-outline-dark btn-sm fw-bold">
                            <i class="bi bi-arrow-clockwise me-1"></i> Cập nhật giỏ hàng
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tóm tắt đơn hàng -->
        <div class="col-lg-4">
            <div class="bg-white p-4 rounded border">
                <h3 class="h6 fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">TÓM TẮT ĐƠN HÀNG</h3>
                
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-secondary">Tổng số món:</span>
                    <span class="fw-semibold text-dark"><?php echo demhangtronggio(); ?> sản phẩm</span>
                </div>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-secondary">Tạm tính:</span>
                    <span class="fw-semibold text-dark"><?php echo number_format(tinhtiengiohang()); ?> đ</span>
                </div>
                <div class="d-flex justify-content-between mb-3 small">
                    <span class="text-secondary">Phí vận chuyển:</span>
                    <span class="text-success fw-bold">Tính theo chành / bến</span>
                </div>
                
                <hr>

                <div class="d-flex justify-content-between align-items-baseline mb-4">
                    <span class="fw-bold text-dark">TỔNG THANH TOÁN:</span>
                    <span class="fs-4 fw-bolder text-danger"><?php echo number_format(tinhtiengiohang()); ?> đ</span>
                </div>

                <div class="d-grid gap-2">
                    <a href="index.php?action=thanhtoan" class="btn btn-danger btn-lg fw-bold py-3 text-uppercase" style="letter-spacing: 0.5px;">
                        Tiến hành đặt hàng
                    </a>
                </div>

                <div class="mt-4 p-3 bg-light rounded small text-secondary">
                    <div class="mb-2"><i class="bi bi-shield-check text-success me-1"></i> Cam kết 100% sản phẩm XAG chính hãng</div>
                    <div class="mb-2"><i class="bi bi-truck text-dark me-1"></i> Giao hỏa tốc chành xe trong ngày</div>
                    <div><i class="bi bi-telephone-fill text-danger me-1"></i> Hotline: <strong>0833 609 329</strong></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
include("inc/bottom.php");
?>
