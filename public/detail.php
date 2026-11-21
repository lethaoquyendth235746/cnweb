<?php 
include("inc/top.php"); 

$model_tag = "XAG Drone";
if(!empty($mhct["dongmay"])) {
    $model_tag = $mhct["dongmay"];
} elseif(strpos($mhct["tenmathang"], "P100 Pro") !== false) {
    $model_tag = "XAG P100 Pro";
} elseif(strpos($mhct["tenmathang"], "P100") !== false) {
    $model_tag = "XAG P100";
} elseif(strpos($mhct["tenmathang"], "P40") !== false) {
    $model_tag = "XAG P40";
} elseif(strpos($mhct["tenmathang"], "V40") !== false) {
    $model_tag = "XAG V40";
} elseif(strpos($mhct["tenmathang"], "R150") !== false) {
    $model_tag = "XAG R150";
}
?>

<!-- Breadcrumb -->
<section class="py-3 bg-light border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="collection-breadcrumb mb-0">
            <a href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a>
            <span class="mx-1">/</span>
            <a href="index.php?action=group&id=<?php echo $mhct["danhmuc_id"]; ?>">Sản phẩm cùng loại</a>
            <span class="mx-1">/</span>
            <span class="text-dark fw-semibold text-truncate d-inline-block align-bottom" style="max-width: 400px;">
                <?php echo htmlspecialchars($mhct["tenmathang"]); ?>
            </span>
        </nav>
    </div>
</section>

<div class="container mb-5">
    <!-- PRODUCT DETAIL BOX -->
    <div class="pdp-box">
        <div class="row g-4 align-items-start">
            <!-- Cột trái: Khung ảnh 1:1 tỉ lệ vuông chuẩn Shopify -->
            <div class="col-lg-5">
                <div class="pdp-gallery-frame">
                    <img src="../<?php echo htmlspecialchars($mhct["hinhanh"]); ?>" 
                         alt="<?php echo htmlspecialchars($mhct["tenmathang"]); ?>" 
                         class="img-fluid" />
                </div>

                <!-- Cam kết chất lượng chuẩn XAG -->
                <div class="p-3 bg-light rounded mt-3 border">
                    <div class="small text-secondary">
                        <div class="mb-2"><i class="bi bi-shield-check text-danger me-2"></i><strong>Sản phẩm chính hãng XAG 100%</strong> - Đầy đủ tem niêm phong</div>
                        <div class="mb-2"><i class="bi bi-truck text-danger me-2"></i><strong>Giao nhanh toàn quốc</strong> - Hỗ trợ gửi chành xe lấy ngay</div>
                        <div class="mb-2"><i class="bi bi-arrow-repeat text-danger me-2"></i><strong>Bảo hành 1 đổi 1</strong> nếu có lỗi từ nhà sản xuất</div>
                        <div><i class="bi bi-cash-coin text-danger me-2"></i><strong>Thanh toán khi nhận hàng (COD)</strong> - Kiểm tra trước khi trả tiền</div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Thông số, Giá bán & Nút Đặt mua -->
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="pdp-model-badge"><?php echo htmlspecialchars($model_tag); ?></span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                        <i class="bi bi-check-circle-fill me-1"></i> Còn hàng (Kho: <?php echo $mhct["soluongton"]; ?>)
                    </span>
                    <span class="text-muted small ms-auto"><i class="bi bi-eye"></i> <?php echo $mhct["luotxem"]; ?> lượt xem</span>
                </div>

                <h1 class="pdp-title"><?php echo htmlspecialchars($mhct["tenmathang"]); ?></h1>

                <div class="text-muted small mb-3">
                    Mã sản phẩm: <strong class="text-dark">#XAG-<?php echo $mhct["id"]; ?></strong> | Thương hiệu: <strong class="text-danger">XAG Official Parts</strong>
                </div>

                <!-- Hộp giá bán (Industrial clean box) -->
                <div class="pdp-price-wrap">
                    <div class="d-flex align-items-baseline gap-3">
                        <span class="pdp-price-current"><?php echo number_format($mhct["giaban"]); ?> đ</span>
                        <?php if ($mhct["giagoc"] > $mhct["giaban"]): 
                            $tietkiem = $mhct["giagoc"] - $mhct["giaban"];
                        ?>
                        <span class="text-muted text-decoration-line-through fs-5"><?php echo number_format($mhct["giagoc"]); ?> đ</span>
                        <span class="badge bg-danger">Tiết kiệm: <?php echo number_format($tietkiem); ?> đ</span>
                        <?php endif; ?>
                    </div>
                    <small class="text-secondary d-block mt-1">Đã bao gồm đóng gói chống va đập tiêu chuẩn linh kiện điện tử</small>
                </div>

                <!-- Form đặt mua -->
                <form method="post" action="index.php" class="mb-4">
                    <input type="hidden" name="action" value="chovaogio">
                    <input type="hidden" name="id" value="<?php echo $mhct["id"]; ?>">

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <label class="form-label fw-bold mb-0 text-dark small text-uppercase">Số lượng:</label>
                        <div class="input-group" style="width: 130px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="var q=document.getElementById('soluong'); if(q.value>1) q.value--;">-</button>
                            <input type="number" id="soluong" name="soluong" class="form-control text-center fw-bold" value="1" min="1" max="<?php echo max(1, $mhct["soluongton"]); ?>">
                            <button class="btn btn-outline-secondary" type="button" onclick="var q=document.getElementById('soluong'); q.value++;">+</button>
                        </div>
                    </div>

                    <div class="d-grid d-md-flex gap-3">
                        <button type="submit" class="btn-pdp-cart flex-grow-1">
                            <i class="bi bi-cart-plus me-2"></i> THÊM VÀO GIỎ HÀNG
                        </button>
                        <a href="tel:0833609329" class="btn btn-outline-dark fw-bold px-4 py-3">
                            <i class="bi bi-telephone-fill text-danger me-2"></i> Hotline: 0833 609 329
                        </a>
                    </div>
                </form>

                <!-- Box kỹ thuật hỗ trợ mùa vụ -->
                <div class="p-3 bg-light rounded border d-flex align-items-center gap-3">
                    <i class="bi bi-wrench-adjustable-circle fs-2 text-danger"></i>
                    <div>
                        <div class="fw-bold text-dark small">Hỗ trợ kỹ thuật lắp ráp & cân chỉnh miễn phí</div>
                        <div class="text-muted small">Kỹ thuật viên LPT DRONE hỗ trợ kết nối video call trực tiếp 24/7 để hướng dẫn thay thế.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MÔ TẢ KỸ THUẬT & THÔNG SỐ -->
    <div class="bg-white p-4 p-md-5 rounded border mb-5">
        <h3 class="h5 fw-bold text-dark text-uppercase border-bottom pb-3 mb-4">
            <i class="bi bi-cpu text-danger me-2"></i>Thông số kỹ thuật & Mô tả chi tiết
        </h3>
        <div class="lh-lg text-secondary">
            <?php echo $mhct["mota"]; ?>
        </div>
    </div>

    <!-- SẢN PHẨM LIÊN QUAN -->
    <section>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="h5 fw-bold text-dark text-uppercase mb-0">
                Sản phẩm liên quan
            </h3>
            <a href="index.php?action=group&id=<?php echo $mhct["danhmuc_id"]; ?>" class="text-danger small fw-bold">
                Xem tất cả <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
            <?php 
            $rel_count = 0;
            foreach($mathang as $m):  
                if($m["id"] != $mhct["id"]):
                    $rel_count++;
                    if($rel_count > 4) break;

                    $m_model = !empty($m["dongmay"]) ? $m["dongmay"] : "XAG Drone";
            ?>
            <div class="col">
                <div class="shopify-card">
                    <span class="card-badge-top badge-oem">Chính hãng</span>

                    <?php if ($m["giagoc"] > $m["giaban"]): 
                        $percent = round((($m["giagoc"] - $m["giaban"]) / $m["giagoc"]) * 100);
                    ?>
                    <span class="badge-discount">-<?php echo $percent; ?>%</span>
                    <?php endif; ?>

                    <a href="index.php?action=detail&id=<?php echo $m["id"]; ?>" class="card-media">
                        <img src="../<?php echo htmlspecialchars($m["hinhanh"]); ?>" 
                             alt="<?php echo htmlspecialchars($m["tenmathang"]); ?>" 
                             loading="lazy" />
                    </a>

                    <div class="card-content">
                        <div class="card-model-tag"><?php echo htmlspecialchars($m_model); ?></div>
                        <h4 class="card-product-title">
                            <a href="index.php?action=detail&id=<?php echo $m["id"]; ?>">
                                <?php echo htmlspecialchars($m["tenmathang"]); ?>
                            </a>
                        </h4>
                        <div class="card-price-row">
                            <span class="card-price-sale"><?php echo number_format($m["giaban"]); ?> đ</span>
                            <?php if ($m["giagoc"] > $m["giaban"]): ?>
                            <span class="card-price-regular"><?php echo number_format($m["giagoc"]); ?> đ</span>
                            <?php endif; ?>
                        </div>
                        <a href="index.php?action=chovaogio&id=<?php echo $m["id"]; ?>" class="btn-card-action">
                            <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                        </a>
                    </div>
                </div>
            </div>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>
    </section>
</div>

<?php include("inc/bottom.php"); ?>
