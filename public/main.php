<?php
include("inc/top.php");

// Mảng icon tương ứng cho 7 danh mục chuyên biệt
$category_icons = [
    1 => 'bi-gear-wide-connected', // Động cơ (Motor)
    2 => 'bi-fan',                 // Cánh quạt
    3 => 'bi-battery-charging',    // Pin thông minh
    4 => 'bi-droplet-half',        // Bơm nhu động
    5 => 'bi-water',               // Vòi phun ly tâm
    6 => 'bi-motherboard',         // Mạch điều khiển
    7 => 'bi-bounding-box'         // Khung sườn & Càng
];
?>

<!-- 1. HERO BANNER CÔNG NGHỆ DRONE -->
<section class="hero-banner">
    <div class="row align-items-center">
        <div class="col-lg-7">
            <span class="hero-subtitle">
                <i class="bi bi-shield-fill-check me-1"></i> TRUNG TÂM PHỤ TÙNG XAG VIỆT NAM
            </span>
            <h1 class="hero-title">
                LINH KIỆN DRONE NÔNG NGHIỆP <span>XAG CHÍNH HÃNG</span>
            </h1>
            <p class="hero-desc">
                Cung cấp đầy đủ phụ tùng chuẩn xưởng thay thế nhanh cho các dòng máy bay <strong>XAG P100, P100 Pro, P40, V40 và Robot R150</strong>. Đảm bảo năng suất mùa vụ, ship hỏa tốc toàn quốc.
            </p>
            <div class="d-flex flex-wrap gap-3">
                <a href="#danhmuc-section" class="hero-btn-primary text-decoration-none">
                    <i class="bi bi-cart-fill"></i> Mua sắm ngay
                </a>
                <a href="tel:0833609329" class="hero-btn-outline text-decoration-none">
                    <i class="bi bi-telephone-fill"></i> Tư vấn kỹ thuật: 0833 609 329
                </a>
            </div>
        </div>
        <div class="col-lg-5 text-center mt-4 mt-lg-0">
            <img src="../images/logo.png" alt="LPT DRONE XAG" class="hero-logo-img img-fluid" />
        </div>
    </div>
</section>

<!-- 2. THANH CAM KẾT DỊCH VỤ (4 CỘT) -->
<section class="commitment-bar">
    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <div class="commitment-item">
                <div class="commitment-icon"><i class="bi bi-award-fill"></i></div>
                <div>
                    <h6 class="commitment-title">100% Chính Hãng</h6>
                    <p class="commitment-desc">Linh kiện chuẩn hãng XAG</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="commitment-item">
                <div class="commitment-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                <div>
                    <h6 class="commitment-title">Giao Hỏa Tốc</h6>
                    <p class="commitment-desc">Gửi xe khách, chành xe 24h</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="commitment-item">
                <div class="commitment-icon"><i class="bi bi-tools"></i></div>
                <div>
                    <h6 class="commitment-title">Hỗ Trợ Kỹ Thuật</h6>
                    <p class="commitment-desc">Tư vấn lắp đặt từ xa 24/7</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="commitment-item">
                <div class="commitment-icon"><i class="bi bi-arrow-repeat"></i></div>
                <div>
                    <h6 class="commitment-title">Đổi Trả Uy Tín</h6>
                    <p class="commitment-desc">Bảo hành 1 đổi 1 nếu lỗi</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 3. DANH MỤC NỔI BẬT (7 DANH MỤC DẠNG THẺ BO GÓC) -->
<section id="danhmuc-section" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="section-title mb-0">DANH MỤC LINH KIỆN CHÍNH</h2>
        <span class="text-muted small d-none d-md-block">Đầy đủ 7 hệ thống linh kiện XAG</span>
    </div>

    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-7 g-3">
        <?php foreach($danhmuc as $d): 
            $icon = isset($category_icons[$d['id']]) ? $category_icons[$d['id']] : 'bi-gear';
        ?>
        <div class="col">
            <a href="index.php?action=group&id=<?php echo $d["id"]; ?>" class="category-card text-decoration-none">
                <div class="category-icon-box">
                    <i class="bi <?php echo $icon; ?>"></i>
                </div>
                <h6 class="category-name"><?php echo htmlspecialchars($d["tendanhmuc"]); ?></h6>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- 4. LỌC THEO DÒNG MÁY XAG PHỔ BIẾN -->
<section class="mb-4">
    <div class="p-3 bg-white rounded-3 shadow-sm border d-flex flex-wrap align-items-center gap-2">
        <span class="fw-bold text-dark me-2"><i class="bi bi-funnel-fill text-danger me-1"></i> Lọc theo dòng máy:</span>
        <a href="index.php" class="model-pill active text-decoration-none">Tất cả</a>
        <a href="index.php?action=dongmay&model=P100 Pro" class="model-pill text-decoration-none">XAG P100 Pro</a>
        <a href="index.php?action=dongmay&model=P100" class="model-pill text-decoration-none">XAG P100</a>
        <a href="index.php?action=dongmay&model=P40" class="model-pill text-decoration-none">XAG P40</a>
        <a href="index.php?action=dongmay&model=V40" class="model-pill text-decoration-none">XAG V40</a>
        <a href="index.php?action=dongmay&model=R150" class="model-pill text-decoration-none">Robot R150</a>
    </div>
</section>

<!-- 5. DANH SÁCH MẶT HÀNG PHÂN THEO TỪNG DANH MỤC (LƯỚI 4 CỘT DESKTOP, 2 CỘT MOBILE) -->
<?php 
foreach($danhmuc as $d): 
    // Lọc các mặt hàng thuộc danh mục hiện tại (tối đa 4 sản phẩm tiêu biểu trên trang chủ)
    $items_in_category = [];
    foreach($mathang as $m) {
        if($m["danhmuc_id"] == $d["id"]) {
            $items_in_category[] = $m;
        }
    }
    if(count($items_in_category) == 0) continue;
?>
<section class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="section-title mb-0">
            <i class="bi <?php echo isset($category_icons[$d['id']]) ? $category_icons[$d['id']] : 'bi-tag-fill'; ?> text-danger"></i>
            <?php echo htmlspecialchars($d["tendanhmuc"]); ?>
        </h3>
        <a href="index.php?action=group&id=<?php echo $d["id"]; ?>" class="btn btn-sm btn-outline-danger rounded-pill fw-semibold">
            Xem tất cả (<?php echo count($items_in_category); ?>) <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3 g-lg-4">
        <?php 
        $count = 0;
        foreach($items_in_category as $m): 
            $count++;
            if($count > 4) break; // Hiển thị 4 sản phẩm mỗi mục trên trang chủ

            // Trích xuất tag dòng máy (nếu có cột dongmay hoặc từ tên sản phẩm)
            $model_tag = "XAG Drone";
            if(!empty($m["dongmay"])) {
                $model_tag = $m["dongmay"];
            } elseif(strpos($m["tenmathang"], "P100 Pro") !== false) {
                $model_tag = "P100 Pro";
            } elseif(strpos($m["tenmathang"], "P100") !== false) {
                $model_tag = "XAG P100";
            } elseif(strpos($m["tenmathang"], "P40") !== false) {
                $model_tag = "XAG P40";
            } elseif(strpos($m["tenmathang"], "V40") !== false) {
                $model_tag = "XAG V40";
            } elseif(strpos($m["tenmathang"], "R150") !== false) {
                $model_tag = "Robot R150";
            }
        ?>
        <div class="col">
            <div class="product-card">
                <!-- Tag dòng máy tương thích -->
                <span class="badge-model"><?php echo htmlspecialchars($model_tag); ?></span>

                <!-- Badge giảm giá nếu có -->
                <?php if ($m["giagoc"] > $m["giaban"]): 
                    $percent = round((($m["giagoc"] - $m["giaban"]) / $m["giagoc"]) * 100);
                ?>
                <span class="badge-sale">-<?php echo $percent; ?>%</span>
                <?php endif; ?>

                <!-- Ảnh sản phẩm -->
                <div class="product-thumb">
                    <a href="index.php?action=detail&id=<?php echo $m["id"]; ?>">
                        <img src="../<?php echo htmlspecialchars($m["hinhanh"]); ?>" 
                             alt="<?php echo htmlspecialchars($m["tenmathang"]); ?>" 
                             loading="lazy" />
                    </a>
                </div>

                <!-- Thông tin sản phẩm -->
                <div class="product-body">
                    <h4 class="product-title">
                        <a href="index.php?action=detail&id=<?php echo $m["id"]; ?>">
                            <?php echo htmlspecialchars($m["tenmathang"]); ?>
                        </a>
                    </h4>

                    <!-- Giá bán -->
                    <div class="product-price-box">
                        <span class="price-current"><?php echo number_format($m["giaban"]); ?> đ</span>
                        <?php if ($m["giagoc"] > $m["giaban"]): ?>
                        <span class="price-old"><?php echo number_format($m["giagoc"]); ?> đ</span>
                        <?php endif; ?>
                    </div>

                    <!-- Nút thao tác nhanh -->
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
        <?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>

<?php
include("inc/bottom.php");
?>
