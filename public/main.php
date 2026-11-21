<?php
include("inc/top.php");

// Trích xuất các tham số bộ lọc hiện hành
$cur_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$cur_model = isset($_GET["model"]) ? trim($_GET["model"]) : "";
$cur_mucgia = isset($_GET["mucgia"]) ? trim($_GET["mucgia"]) : "";
$cur_sort = isset($_GET["sort"]) ? trim($_GET["sort"]) : "";
$cur_tukhoa = isset($_GET["tukhoa"]) ? trim($_GET["tukhoa"]) : "";

function build_main_filter_url($override = []) {
    $params = $_GET;
    foreach($override as $k => $v) {
        if($v === null || $v === '') {
            unset($params[$k]);
        } else {
            $params[$k] = $v;
        }
    }
    return "index.php?" . http_build_query($params);
}
?>

<div class="container">
    <!-- 1. HERO BANNER NỀN TỐI ÁNH ĐỎ HIỆN ĐẠI (CHÍNH NGAY DƯỚI HEADER) -->
    <section class="hero-banner">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="hero-subtitle">
                    <i class="bi bi-shield-fill-check me-1"></i> TRUNG TÂM PHỤ TÙNG XAG VIỆT NAM
                </span>
                <h1 class="hero-title">
                    SẢN PHẨM PHỤ TÙNG DRONE NÔNG NGHIỆP <span>XAG CHÍNH HÃNG</span>
                </h1>
                <p class="hero-desc">
                    Cung cấp đầy đủ phụ tùng thay thế chuẩn nhà sản xuất cho các dòng máy bay <strong>XAG P100, P100 Pro, P40, V40 và Robot R150</strong>. Đảm bảo năng suất mùa vụ, hỗ trợ kỹ thuật và giao hàng hỏa tốc toàn quốc.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="#sanpham-section" class="hero-btn-primary text-decoration-none">
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

    <!-- 2. BỐ CỤC 2 CỘT (SIDEBAR FILTER 25% + PRODUCT GRID 75%) -->
    <div id="sanpham-section" class="row g-4 mb-5">
        <!-- CỘT TRÁI: SIDEBAR BỘ LỌC (25% TRÊN DESKTOP) -->
        <aside class="col-lg-3 d-none d-lg-block">
            <div class="filter-sidebar">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <span class="fw-bold text-dark text-uppercase small"><i class="bi bi-funnel-fill text-danger me-1"></i> Bộ lọc sản phẩm</span>
                    <?php if($cur_id > 0 || !empty($cur_model) || !empty($cur_mucgia) || !empty($cur_sort) || !empty($cur_tukhoa)): ?>
                    <a href="index.php" class="btn-clear-filters">Xóa lọc</a>
                    <?php endif; ?>
                </div>

                <!-- Nhóm 1: Danh mục sản phẩm (7 nhóm) -->
                <div class="filter-group">
                    <div class="filter-group-title">Danh mục sản phẩm</div>
                    <ul class="filter-list">
                        <li class="filter-item">
                            <a href="<?php echo build_main_filter_url(['id' => null]); ?>" 
                               class="filter-link <?php if($cur_id == 0) echo 'active'; ?>">
                                <span>Tất cả sản phẩm</span>
                            </a>
                        </li>
                        <?php if(isset($danhmuc) && is_array($danhmuc)): ?>
                            <?php foreach($danhmuc as $d): ?>
                            <li class="filter-item">
                                <a href="<?php echo build_main_filter_url(['id' => $d['id']]); ?>" 
                                   class="filter-link <?php if($cur_id == $d['id']) echo 'active'; ?>">
                                    <span><?php echo htmlspecialchars($d["tendanhmuc"]); ?></span>
                                    <?php if($cur_id == $d['id']): ?>
                                    <i class="bi bi-check text-danger fw-bold fs-5"></i>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Nhóm 2: Lọc theo Dòng máy tương thích -->
                <div class="filter-group">
                    <div class="filter-group-title">Dòng máy tương thích</div>
                    <div class="d-flex flex-column gap-2">
                        <?php 
                        $models = ['P100 Pro', 'P100', 'P40', 'V40', 'R150'];
                        foreach($models as $m_name): 
                            $is_checked = ($cur_model == $m_name);
                            $target_url = $is_checked ? build_main_filter_url(['model' => null]) : build_main_filter_url(['model' => $m_name]);
                        ?>
                        <label class="filter-checkbox-label">
                            <input type="checkbox" <?php if($is_checked) echo 'checked'; ?> onchange="location.href='<?php echo $target_url; ?>';">
                            <span class="<?php if($is_checked) echo 'fw-bold text-danger'; ?>">XAG <?php echo $m_name; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Nhóm 3: Lọc theo Khoảng giá -->
                <div class="filter-group">
                    <div class="filter-group-title">Khoảng giá</div>
                    <div class="d-flex flex-column gap-2">
                        <?php
                        $price_ranges = [
                            'duoi_2tr' => 'Dưới 2.000.000 đ',
                            '2tr_5tr'  => '2.000.000 đ - 5.000.000 đ',
                            '5tr_10tr' => '5.000.000 đ - 10.000.000 đ',
                            'tren_10tr'=> 'Trên 10.000.000 đ'
                        ];
                        foreach($price_ranges as $p_key => $p_label):
                            $is_p_checked = ($cur_mucgia == $p_key);
                            $target_p_url = $is_p_checked ? build_main_filter_url(['mucgia' => null]) : build_main_filter_url(['mucgia' => $p_key]);
                        ?>
                        <label class="filter-checkbox-label">
                            <input type="checkbox" <?php if($is_p_checked) echo 'checked'; ?> onchange="location.href='<?php echo $target_p_url; ?>';">
                            <span class="<?php if($is_p_checked) echo 'fw-bold text-danger'; ?>"><?php echo $p_label; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Nhóm 4: Tình trạng kho & Cam kết -->
                <div class="filter-group">
                    <div class="filter-group-title">Tình trạng kho hàng</div>
                    <div class="small text-secondary">
                        <div class="mb-2"><i class="bi bi-check-circle-fill text-success me-1"></i> Có sẵn tại kho Châu Phú, An Giang</div>
                        <div class="mb-2"><i class="bi bi-shield-check text-danger me-1"></i> Đầy đủ tem niêm phong XAG</div>
                        <div><i class="bi bi-truck text-dark me-1"></i> Ship COD kiểm tra trước khi nhận</div>
                    </div>
                </div>

                <!-- Hotline hỗ trợ phụ tùng -->
                <div class="p-3 bg-light rounded text-center border">
                    <small class="text-muted d-block mb-1">Cần hỗ trợ kỹ thuật khẩn cấp?</small>
                    <a href="tel:0833609329" class="text-danger fw-bold text-decoration-none">
                        <i class="bi bi-telephone-fill me-1"></i> 0833 609 329
                    </a>
                </div>
            </div>
        </aside>

        <!-- CỘT PHẢI: KHU VỰC HIỂN THỊ SẢN PHẨM (75% TRÊN DESKTOP) -->
        <main class="col-lg-9">
            <!-- TOOLBAR TRÊN CÙNG -->
            <div class="collection-toolbar">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-dark d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterDrawer">
                        <i class="bi bi-funnel-fill text-danger me-1"></i> Bộ lọc
                    </button>
                    <span class="toolbar-count">
                        Hiển thị <strong><?php echo is_array($mathang) ? count($mathang) : 0; ?></strong> sản phẩm
                    </span>
                </div>

                <!-- Sắp xếp theo -->
                <div class="toolbar-sort">
                    <label for="mainSortSelect">Sắp xếp:</label>
                    <select id="mainSortSelect" onchange="location.href=this.value;">
                        <option value="<?php echo build_main_filter_url(['sort' => null]); ?>" <?php if($cur_sort=='') echo 'selected'; ?>>Mới nhất</option>
                        <option value="<?php echo build_main_filter_url(['sort' => 'gia_tang']); ?>" <?php if($cur_sort=='gia_tang') echo 'selected'; ?>>Giá: Thấp đến Cao</option>
                        <option value="<?php echo build_main_filter_url(['sort' => 'gia_giam']); ?>" <?php if($cur_sort=='gia_giam') echo 'selected'; ?>>Giá: Cao đến Thấp</option>
                        <option value="<?php echo build_main_filter_url(['sort' => 'banchay']); ?>" <?php if($cur_sort=='banchay') echo 'selected'; ?>>Bán chạy nhất</option>
                    </select>
                </div>
            </div>

            <!-- LƯỚI THẺ SẢN PHẨM (SHOPIFY PRODUCT GRID - 3 CỘT TRÊN DESKTOP) -->
            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-3 g-3">
                <?php 
                if(!empty($mathang) && is_array($mathang)):
                    foreach($mathang as $m):
                        $model_tag = "XAG Drone";
                        if(!empty($m["dongmay"])) {
                            $model_tag = $m["dongmay"];
                        } elseif(strpos($m["tenmathang"], "P100 Pro") !== false) {
                            $model_tag = "XAG P100 Pro";
                        } elseif(strpos($m["tenmathang"], "P100") !== false) {
                            $model_tag = "XAG P100";
                        } elseif(strpos($m["tenmathang"], "P40") !== false) {
                            $model_tag = "XAG P40";
                        } elseif(strpos($m["tenmathang"], "V40") !== false) {
                            $model_tag = "XAG V40";
                        } elseif(strpos($m["tenmathang"], "R150") !== false) {
                            $model_tag = "XAG R150";
                        }
                ?>
                <div class="col">
                    <div class="shopify-card">
                        <!-- Badge trên góc trái ảnh -->
                        <span class="card-badge-top badge-oem">Chính hãng XAG</span>

                        <!-- Badge giảm giá góc phải (nếu có) -->
                        <?php if ($m["giagoc"] > $m["giaban"]): 
                            $percent = round((($m["giagoc"] - $m["giaban"]) / $m["giagoc"]) * 100);
                        ?>
                        <span class="badge-discount">-<?php echo $percent; ?>%</span>
                        <?php endif; ?>

                        <!-- Khung ảnh tỷ lệ vuông 1:1 trên nền sáng #F9FAFB -->
                        <a href="index.php?action=detail&id=<?php echo $m["id"]; ?>" class="card-media">
                            <img src="../<?php echo htmlspecialchars($m["hinhanh"]); ?>" 
                                 alt="<?php echo htmlspecialchars($m["tenmathang"]); ?>" 
                                 loading="lazy" />
                        </a>

                        <!-- Nội dung thẻ sản phẩm -->
                        <div class="card-content">
                            <!-- Dòng máy tương thích -->
                            <div class="card-model-tag"><?php echo htmlspecialchars($model_tag); ?></div>

                            <!-- Tên sản phẩm in đậm tối đa 2 dòng -->
                            <h3 class="card-product-title">
                                <a href="index.php?action=detail&id=<?php echo $m["id"]; ?>">
                                    <?php echo htmlspecialchars($m["tenmathang"]); ?>
                                </a>
                            </h3>

                            <!-- Giá bán màu đỏ #D90429 in đậm -->
                            <div class="card-price-row">
                                <span class="card-price-sale"><?php echo number_format($m["giaban"]); ?> đ</span>
                                <?php if ($m["giagoc"] > $m["giaban"]): ?>
                                <span class="card-price-regular"><?php echo number_format($m["giagoc"]); ?> đ</span>
                                <?php endif; ?>
                            </div>

                            <!-- Nút hành động full-width dưới đáy thẻ -->
                            <a href="index.php?action=chovaogio&id=<?php echo $m["id"]; ?>" class="btn-card-action">
                                <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                            </a>
                        </div>
                    </div>
                </div>
                <?php 
                    endforeach; 
                else:
                ?>
                <div class="col-12 py-5 text-center">
                    <div class="p-5 bg-light rounded-3 border mx-auto" style="max-width: 500px;">
                        <i class="bi bi-search text-muted display-4 mb-3"></i>
                        <h5 class="fw-bold text-dark">Chưa có sản phẩm phù hợp</h5>
                        <p class="text-muted small">Vui lòng xóa bộ lọc hoặc liên hệ trực tiếp với kỹ thuật viên LPT DRONE để được hỗ trợ kiểm tra hàng tại kho.</p>
                        <a href="index.php" class="btn btn-danger btn-sm">Xem tất cả sản phẩm</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- 3. THANH CAM KẾT CHÍNH HÃNG DƯỚI ĐÁY TRANG -->
    <section class="py-4 bg-light border rounded mb-5">
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <i class="bi bi-award-fill text-danger fs-3 mb-2 d-block"></i>
                <div class="fw-bold small text-dark">100% Phụ tùng chính hãng XAG</div>
                <small class="text-muted">Đầy đủ mã xuất xưởng</small>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-truck text-danger fs-3 mb-2 d-block"></i>
                <div class="fw-bold small text-dark">Giao nhanh toàn quốc</div>
                <small class="text-muted">Gửi xe khách, chành xe lấy liền</small>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-headset text-danger fs-3 mb-2 d-block"></i>
                <div class="fw-bold small text-dark">Kỹ thuật viên 24/7</div>
                <small class="text-muted">Hotline: 0833 609 329</small>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-shield-check text-danger fs-3 mb-2 d-block"></i>
                <div class="fw-bold small text-dark">Kiểm tra trước khi trả tiền</div>
                <small class="text-muted">Ship COD an tâm tuyệt đối</small>
            </div>
        </div>
    </section>
</div>

<!-- OFFCANVAS FILTER CHO GIAO DIỆN DI ĐỘNG TRÊN TRANG CHỦ -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="filterDrawer" aria-labelledby="filterDrawerLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="filterDrawerLabel"><i class="bi bi-funnel-fill text-danger me-1"></i> Bộ lọc sản phẩm</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="filter-group">
            <div class="filter-group-title">Danh mục sản phẩm</div>
            <ul class="filter-list">
                <li class="filter-item">
                    <a href="<?php echo build_main_filter_url(['id' => null]); ?>" class="filter-link <?php if($cur_id == 0) echo 'active'; ?>">Tất cả sản phẩm</a>
                </li>
                <?php if(isset($danhmuc) && is_array($danhmuc)): ?>
                    <?php foreach($danhmuc as $d): ?>
                    <li class="filter-item">
                        <a href="<?php echo build_main_filter_url(['id' => $d['id']]); ?>" class="filter-link <?php if($cur_id == $d['id']) echo 'active'; ?>">
                            <?php echo htmlspecialchars($d["tendanhmuc"]); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <div class="filter-group">
            <div class="filter-group-title">Dòng máy tương thích</div>
            <div class="d-flex flex-column gap-2">
                <?php foreach(['P100 Pro', 'P100', 'P40', 'V40', 'R150'] as $m_name): 
                    $is_checked = ($cur_model == $m_name);
                    $target_url = $is_checked ? build_main_filter_url(['model' => null]) : build_main_filter_url(['model' => $m_name]);
                ?>
                <label class="filter-checkbox-label">
                    <input type="checkbox" <?php if($is_checked) echo 'checked'; ?> onchange="location.href='<?php echo $target_url; ?>';">
                    <span>XAG <?php echo $m_name; ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mt-4">
            <a href="index.php" class="btn btn-outline-danger w-100 btn-sm">Xóa tất cả bộ lọc</a>
        </div>
    </div>
</div>

<?php
include("inc/bottom.php");
?>
