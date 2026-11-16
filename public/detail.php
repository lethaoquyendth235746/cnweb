<?php 
include("inc/top.php"); 

$model_tag = "XAG Drone";
if(!empty($mhct["dongmay"])) {
    $model_tag = $mhct["dongmay"];
} elseif(strpos($mhct["tenmathang"], "P100 Pro") !== false) {
    $model_tag = "P100 Pro";
} elseif(strpos($mhct["tenmathang"], "P100") !== false) {
    $model_tag = "XAG P100";
} elseif(strpos($mhct["tenmathang"], "P40") !== false) {
    $model_tag = "XAG P40";
} elseif(strpos($mhct["tenmathang"], "V40") !== false) {
    $model_tag = "XAG V40";
} elseif(strpos($mhct["tenmathang"], "R150") !== false) {
    $model_tag = "Robot R150";
}
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border">
        <li class="breadcrumb-item"><a href="index.php" class="text-danger"><i class="bi bi-house-door-fill"></i> Trang chủ</a></li>
        <li class="breadcrumb-item"><a href="index.php?action=group&id=<?php echo $mhct["danhmuc_id"]; ?>" class="text-secondary">Linh kiện cùng loại</a></li>
        <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 450px;">
            <?php echo htmlspecialchars($mhct["tenmathang"]); ?>
        </li>
    </ol>
</nav>

<!-- Chi tiết sản phẩm chính -->
<div class="detail-card">
    <div class="row g-4 align-items-start">
        <!-- Cột trái: Ảnh sản phẩm lớn -->
        <div class="col-lg-5">
            <div class="detail-img-box p-3 position-relative">
                <span class="badge bg-danger position-absolute top-0 start-0 m-3 p-2">
                    <i class="bi bi-shield-check me-1"></i> XAG CHÍNH HÃNG
                </span>
                <img src="../<?php echo htmlspecialchars($mhct["hinhanh"]); ?>" 
                     alt="<?php echo htmlspecialchars($mhct["tenmathang"]); ?>" 
                     class="img-fluid" />
            </div>

            <!-- Cam kết mua hàng -->
            <div class="trust-badges-box">
                <div class="trust-badge-item">
                    <i class="bi bi-check-circle-fill"></i>
                    <span><strong>100% Phụ tùng chính hãng XAG</strong> - Chuẩn xuất xưởng</span>
                </div>
                <div class="trust-badge-item">
                    <i class="bi bi-truck"></i>
                    <span><strong>Giao hàng toàn quốc</strong> - Gửi chành xe, xe khách lấy ngay</span>
                </div>
                <div class="trust-badge-item">
                    <i class="bi bi-arrow-repeat"></i>
                    <span><strong>Bảo hành đổi mới</strong> nếu phát hiện lỗi từ nhà sản xuất</span>
                </div>
                <div class="trust-badge-item">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span><strong>Kiểm tra hàng trước</strong> khi thanh toán tiền mặt (COD)</span>
                </div>
            </div>
        </div>

        <!-- Cột phải: Thông số, Giá bán & Nút Đặt mua -->
        <div class="col-lg-7">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-dark px-3 py-2 fs-6"><i class="bi bi-cpu-fill text-danger me-1"></i> <?php echo htmlspecialchars($model_tag); ?></span>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-6">
                    <i class="bi bi-box-seam-fill me-1"></i> Còn hàng (Kho: <?php echo $mhct["soluongton"]; ?>)
                </span>
                <span class="text-muted small ms-auto"><i class="bi bi-eye"></i> <?php echo $mhct["luotxem"]; ?> lượt xem</span>
            </div>

            <h1 class="detail-title"><?php echo htmlspecialchars($mhct["tenmathang"]); ?></h1>

            <!-- Hộp giá bán -->
            <div class="detail-price-box">
                <div class="d-flex align-items-baseline gap-3">
                    <span class="detail-price-current"><?php echo number_format($mhct["giaban"]); ?> đ</span>
                    <?php if ($mhct["giagoc"] > $mhct["giaban"]): 
                        $tietkiem = $mhct["giagoc"] - $mhct["giaban"];
                    ?>
                    <span class="text-muted text-decoration-line-through fs-5"><?php echo number_format($mhct["giagoc"]); ?> đ</span>
                    <span class="badge bg-danger">Tiết kiệm: <?php echo number_format($tietkiem); ?> đ</span>
                    <?php endif; ?>
                </div>
                <small class="text-secondary d-block mt-1">Đã bao gồm thuế GTGT & chi phí kiểm tra kỹ thuật tại kho</small>
            </div>

            <!-- Form đặt mua -->
            <form method="post" action="index.php" class="mb-4">
                <input type="hidden" name="action" value="chovaogio">
                <input type="hidden" name="id" value="<?php echo $mhct["id"]; ?>">

                <div class="d-flex align-items-center gap-3 mb-4">
                    <label class="form-label fw-bold mb-0 text-dark">Số lượng mua:</label>
                    <div class="input-group" style="width: 140px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="var q=document.getElementById('soluong'); if(q.value>1) q.value--;">-</button>
                        <input type="number" id="soluong" name="soluong" class="form-control text-center fw-bold" value="1" min="1" max="<?php echo max(1, $mhct["soluongton"]); ?>">
                        <button class="btn btn-outline-secondary" type="button" onclick="var q=document.getElementById('soluong'); q.value++;">+</button>
                    </div>
                </div>

                <div class="d-grid d-md-flex gap-3">
                    <button type="submit" class="btn btn-danger btn-lg px-4 py-3 fw-bold flex-grow-1 shadow-sm">
                        <i class="bi bi-cart-plus-fill me-2"></i> THÊM VÀO GIỎ HÀNG
                    </button>
                    <a href="tel:0833609329" class="btn btn-outline-dark btn-lg px-4 py-3 fw-bold">
                        <i class="bi bi-telephone-outbound-fill text-danger me-2"></i> TƯ VẤN: 0833 609 329
                    </a>
                </div>
            </form>

            <!-- Hỗ trợ kỹ thuật mùa vụ -->
            <div class="p-3 bg-light rounded-3 border">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-headset fs-2 text-danger"></i>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Cần hỗ trợ kỹ thuật hoặc thay thế khẩn cấp mùa vụ?</h6>
                        <small class="text-muted">Kỹ thuật viên LPT DRONE sẵn sàng video call hướng dẫn cân chỉnh bo mạch, motor, vòi phun 24/7.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab mô tả thông số kỹ thuật chi tiết -->
<div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border mb-5">
    <h3 class="section-title">THÔNG SỐ & MÔ TẢ KỸ THUẬT</h3>
    <div class="lh-lg text-dark mt-3">
        <?php echo $mhct["mota"]; ?>
    </div>
</div>

<!-- Sản phẩm cùng danh mục -->
<section class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="section-title mb-0">LINH KIỆN CÙNG DANH MỤC</h3>
        <a href="index.php?action=group&id=<?php echo $mhct["danhmuc_id"]; ?>" class="btn btn-sm btn-outline-danger rounded-pill fw-semibold">
            Xem tất cả <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3 g-lg-4">
        <?php 
        $rel_count = 0;
        foreach($mathang as $m):  
            if($m["id"] != $mhct["id"]):
                $rel_count++;
                if($rel_count > 4) break;

                $m_model = !empty($m["dongmay"]) ? $m["dongmay"] : "XAG Drone";
        ?>
        <div class="col">
            <div class="product-card">
                <span class="badge-model"><?php echo htmlspecialchars($m_model); ?></span>

                <?php if ($m["giagoc"] > $m["giaban"]): 
                    $percent = round((($m["giagoc"] - $m["giaban"]) / $m["giagoc"]) * 100);
                ?>
                <span class="badge-sale">-<?php echo $percent; ?>%</span>
                <?php endif; ?>

                <div class="product-thumb">
                    <a href="index.php?action=detail&id=<?php echo $m["id"]; ?>">
                        <img src="../<?php echo htmlspecialchars($m["hinhanh"]); ?>" 
                             alt="<?php echo htmlspecialchars($m["tenmathang"]); ?>" 
                             loading="lazy" />
                    </a>
                </div>

                <div class="product-body">
                    <h4 class="product-title">
                        <a href="index.php?action=detail&id=<?php echo $m["id"]; ?>">
                            <?php echo htmlspecialchars($m["tenmathang"]); ?>
                        </a>
                    </h4>

                    <div class="product-price-box">
                        <span class="price-current"><?php echo number_format($m["giaban"]); ?> đ</span>
                        <?php if ($m["giagoc"] > $m["giaban"]): ?>
                        <span class="price-old"><?php echo number_format($m["giagoc"]); ?> đ</span>
                        <?php endif; ?>
                    </div>

                    <div class="product-actions">
                        <a href="index.php?action=chovaogio&id=<?php echo $m["id"]; ?>" class="btn-buy-now text-decoration-none">
                            <i class="bi bi-cart-plus me-1"></i> Mua ngay
                        </a>
                        <a href="index.php?action=detail&id=<?php echo $m["id"]; ?>" class="btn-detail text-decoration-none">
                            Chi tiết
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php 
            endif;
        endforeach; 
        ?>
    </div>
</section>

<?php include("inc/bottom.php"); ?>
