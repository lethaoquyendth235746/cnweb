<?php 
include("inc/top.php"); 

// Trích xuất các tham số bộ lọc hiện hành
$cur_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
$cur_model = isset($_GET["model"]) ? trim($_GET["model"]) : "";
$cur_mucgia = isset($_GET["mucgia"]) ? trim($_GET["mucgia"]) : "";
$cur_sort = isset($_GET["sort"]) ? trim($_GET["sort"]) : "";
$cur_tukhoa = isset($_GET["tukhoa"]) ? trim($_GET["tukhoa"]) : "";

// Xác định tiêu đề trang
$page_title = "Tất cả sản phẩm & Phụ tùng Drone XAG";
if($cur_id > 0 && isset($tendm)) {
    $page_title = $tendm;
} elseif(!empty($cur_model)) {
    $page_title = "Linh kiện tương thích: XAG " . $cur_model;
} elseif(!empty($cur_tukhoa)) {
    $page_title = "Tìm kiếm: \"" . $cur_tukhoa . "\"";
}

// Xử lý danh sách dòng máy đơn chuẩn (bỏ tiền tố XAG/Robot để lấy tên gốc, trim, khử trùng lặp không phân biệt hoa thường)
$clean_models = [];
$seen_models = [];
if(isset($dongmay_list) && is_array($dongmay_list)) {
    foreach($dongmay_list as $dm_item) {
        $raw_name = isset($dm_item['tendongmay']) ? $dm_item['tendongmay'] : '';
        $tokens = explode(',', $raw_name);
        foreach($tokens as $t) {
            $t = trim($t);
            if(empty($t)) continue;

            // Bỏ tiền tố XAG để chuẩn hóa về tên máy gốc
            $norm = preg_replace('/^XAG\s+/i', '', $t);
            $norm = trim($norm);
            if(empty($norm)) continue;

            $key = mb_strtolower($norm, 'UTF-8');
            if(!isset($seen_models[$key])) {
                $seen_models[$key] = true;
                $clean_models[] = $norm;
            }
        }
    }
}
if(empty($clean_models)) {
    $clean_models = ['P100 Pro', 'P100', 'P40', 'V40', 'R150'];
}

function build_page_filter_url($override = []) {
    $params = $_GET;
    foreach($override as $k => $v) {
        if($v === null || $v === '') {
            unset($params[$k]);
        } else {
            $params[$k] = $v;
        }
    }
    if(!isset($params['action'])) {
        $params['action'] = 'group';
    }
    return "index.php?" . http_build_query($params);
}
?>

<style>
/* ==========================================
   LPT CATALOG LAYOUT & WIDGETS
   ========================================== */
.lpt-catalog-container {
    max-width: 1320px;
    margin: 28px auto 48px;
    padding: 0 16px;
    display: flex;
    gap: 24px;
    align-items: flex-start;
}

.lpt-catalog-sidebar {
    width: 280px;
    flex-shrink: 0;
}

.lpt-catalog-main {
    flex: 1;
    min-width: 0;
}

/* Sidebar Widget Boxes */
.lpt-widget-box {
    background: #FFFFFF;
    border: 1px solid #E4E4E7;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 20px;
}

.lpt-widget-header {
    background-color: #B91C1C;
    color: #FFFFFF;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    text-align: center;
    padding: 10px 14px;
    letter-spacing: 0.5px;
}

.lpt-widget-header.header-dark {
    background-color: #18181B;
}

.lpt-widget-body {
    padding: 10px;
}

.lpt-widget-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.lpt-widget-item {
    border-bottom: 1px solid #F1F5F9;
}

.lpt-widget-item:last-child {
    border-bottom: none;
}

.lpt-widget-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 10px;
    font-size: 13px;
    color: #18181B;
    text-decoration: none;
    border-radius: 4px;
    transition: all 0.15s ease-in-out;
}

.lpt-widget-link:hover {
    background-color: #FEF2F2;
    color: #B91C1C;
}

.lpt-widget-link.active {
    background-color: #FEF2F2;
    color: #B91C1C;
    font-weight: 700;
}

.lpt-widget-link .bullet-icon {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    border: 1.5px solid #94A3B8;
    flex-shrink: 0;
    transition: all 0.15s ease-in-out;
}

.lpt-widget-link:hover .bullet-icon,
.lpt-widget-link.active .bullet-icon {
    background-color: #B91C1C;
    border-color: #B91C1C;
}

/* Form lọc nhanh */
.lpt-filter-form-label {
    font-size: 12px;
    font-weight: 600;
    color: #52525B;
    margin-bottom: 4px;
    display: block;
}

.lpt-btn-filter-apply {
    width: 100%;
    background-color: #B91C1C;
    color: #FFFFFF !important;
    font-size: 13px;
    font-weight: 600;
    padding: 9px;
    border: none;
    border-radius: 6px;
    transition: background-color 0.15s ease;
}

.lpt-btn-filter-apply:hover {
    background-color: #991B1B;
}

/* Product Grid */
.lpt-product-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

/* Product Card */
.lpt-product-card {
    background: #FFFFFF;
    border: 1px solid #E4E4E7;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.lpt-product-card:hover {
    border-color: #CBD5E1;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
}

.lpt-card-badges {
    position: absolute;
    top: 10px;
    left: 10px;
    right: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 2;
    pointer-events: none;
}

.lpt-badge-oem {
    background: #18181B;
    color: #FFFFFF;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    padding: 3px 7px;
    border-radius: 4px;
    letter-spacing: 0.3px;
}

.lpt-badge-discount {
    background: #B91C1C;
    color: #FFFFFF;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 6px;
    border-radius: 4px;
    margin-left: auto;
}

.lpt-card-media {
    position: relative;
    display: block;
    width: 100%;
    padding-top: 85%;
    background: #FFFFFF;
    overflow: hidden;
}

.lpt-card-media img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 16px;
    transition: transform 0.3s ease;
}

.lpt-product-card:hover .lpt-card-media img {
    transform: scale(1.04);
}

.lpt-card-body {
    padding: 14px 16px 16px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    border-top: 1px solid #F4F4F5;
}

.lpt-card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    font-size: 11px;
}

.lpt-model-tag {
    font-weight: 700;
    color: #52525B;
    background: #F4F4F5;
    padding: 2px 6px;
    border-radius: 4px;
    text-transform: uppercase;
}

.lpt-stock-in {
    color: #15803D;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 3px;
}

.lpt-stock-out {
    color: #B91C1C;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 3px;
}

.lpt-card-title {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.4;
    height: 40px;
    margin: 0 0 10px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.lpt-card-title a {
    color: #18181B;
    text-decoration: none;
}

.lpt-card-title a:hover {
    color: #B91C1C;
}

.lpt-card-price {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 12px;
}

.lpt-price-sale {
    font-size: 16px;
    font-weight: 700;
    color: #B91C1C;
}

.lpt-price-regular {
    font-size: 13px;
    color: #94A3B8;
    text-decoration: line-through;
}

.lpt-card-actions {
    display: flex;
    gap: 6px;
    margin-top: auto;
}

.lpt-btn-addcart {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background-color: #FFFFFF;
    color: #B91C1C !important;
    border: 1px solid #FECACA;
    font-size: 13px;
    font-weight: 500;
    padding: 8px 12px;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.2s ease-in-out;
}

.lpt-btn-addcart:hover {
    background-color: #FEF2F2;
    border-color: #B91C1C;
    color: #991B1B !important;
}

.lpt-btn-quickview {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: 1px solid #E4E4E7;
    background: #FFFFFF;
    color: #52525B;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.15s ease;
}

.lpt-btn-quickview:hover {
    border-color: #B91C1C;
    color: #B91C1C;
    background-color: #FEF2F2;
}

/* Responsive */
@media (max-width: 991px) {
    .lpt-catalog-container {
        flex-direction: column;
        margin-top: 18px;
    }
    .lpt-catalog-sidebar {
        width: 100%;
    }
    .lpt-product-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
}

@media (max-width: 576px) {
    .lpt-product-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }
    .lpt-card-body {
        padding: 10px;
    }
    .lpt-card-title {
        font-size: 13px;
        height: 36px;
    }
    .lpt-price-sale {
        font-size: 14px;
    }
    .lpt-btn-addcart {
        font-size: 12px;
        padding: 6px 8px;
    }
}
</style>

<!-- =========================================================================
     1. BANNER TIÊU ĐỀ ĐẦU TRANG (FULL-WIDTH HERO BANNER, NẰM NGOÀI KHUNG 2 CỘT)
     ========================================================================= -->
<section style="width: 100%; min-height: 180px; background: linear-gradient(rgba(17, 19, 21, 0.78), rgba(17, 19, 21, 0.85)), url('../images/products/mach_fc_superx4_pro.jpg') center/cover no-repeat, #18181B; display: flex; align-items: center; justify-content: center; text-align: center; padding: 40px 16px;">
    <div style="max-width: 920px; margin: 0 auto;">
        <h1 style="color: #FFFFFF; font-size: 30px; font-weight: 800; margin-bottom: 10px; letter-spacing: -0.5px; text-transform: uppercase;">
            <?php echo htmlspecialchars($page_title); ?>
        </h1>
        <nav aria-label="breadcrumb">
            <ol style="list-style: none; display: flex; justify-content: center; align-items: center; gap: 8px; margin: 0; padding: 0; font-size: 14px; color: #D4D4D8; flex-wrap: wrap;">
                <li><a href="index.php" style="color: #E4E4E7; text-decoration: none;"><i class="bi bi-house-door-fill me-1"></i>Trang chủ</a></li>
                <li style="color: #71717A;">/</li>
                <li><a href="index.php?action=group" style="color: #E4E4E7; text-decoration: none;">Sản phẩm</a></li>
                <?php if($cur_id > 0 || !empty($cur_model) || !empty($cur_tukhoa)): ?>
                <li style="color: #71717A;">/</li>
                <li style="color: #FFFFFF; font-weight: 600;"><?php echo htmlspecialchars($page_title); ?></li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</section>

<!-- =========================================================================
     2. KHUNG BAO BỐ CỤC 2 CỘT CHUẨN (SIDEBAR TRÁI 280PX + SẢN PHẨM PHẢI)
     ========================================================================= -->
<div class="lpt-catalog-container">

    <!-- CỘT TRÁI (SIDEBAR 280PX) -->
    <aside class="lpt-catalog-sidebar" aria-label="Bộ lọc sản phẩm">
        
        <!-- HỘP 1: DÒNG MÁY DRONE -->
        <div class="lpt-widget-box">
            <div class="lpt-widget-header">
                DÒNG MÁY DRONE
            </div>
            <div class="lpt-widget-body">
                <ul class="lpt-widget-list">
                    <li class="lpt-widget-item">
                        <a href="<?php echo build_page_filter_url(['model' => null, 'trang' => 1]); ?>" 
                           class="lpt-widget-link <?php if(empty($cur_model)) echo 'active'; ?>">
                            <span class="bullet-icon"></span>
                            <span>TẤT CẢ DÒNG MÁY</span>
                        </a>
                    </li>
                    <?php foreach($clean_models as $m_item): 
                        $is_active_model = (!empty($cur_model) && (
                            strcasecmp($cur_model, $m_item) === 0 || 
                            strcasecmp($cur_model, 'XAG ' . $m_item) === 0 ||
                            strcasecmp(preg_replace('/^XAG\s+/i', '', $cur_model), $m_item) === 0
                        ));
                        $label_model = (stripos($m_item, 'Robot') !== false) ? $m_item : 'XAG ' . $m_item;
                    ?>
                    <li class="lpt-widget-item">
                        <a href="<?php echo build_page_filter_url(['model' => $m_item, 'trang' => 1]); ?>" 
                           class="lpt-widget-link <?php if($is_active_model) echo 'active'; ?>">
                            <span class="bullet-icon"></span>
                            <span><?php echo htmlspecialchars($label_model); ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- HỘP 2: DANH MỤC SẢN PHẨM -->
        <div class="lpt-widget-box">
            <div class="lpt-widget-header">
                DANH MỤC SẢN PHẨM
            </div>
            <div class="lpt-widget-body">
                <ul class="lpt-widget-list">
                    <li class="lpt-widget-item">
                        <a href="<?php echo build_page_filter_url(['id' => 0, 'trang' => 1]); ?>" 
                           class="lpt-widget-link <?php if($cur_id <= 0) echo 'active'; ?>">
                            <span class="bullet-icon"></span>
                            <span>TẤT CẢ SẢN PHẨM</span>
                        </a>
                    </li>
                    <?php if(isset($danhmuc) && is_array($danhmuc)): ?>
                        <?php foreach($danhmuc as $d): 
                            $is_active_cat = ($cur_id == $d['id']);
                        ?>
                        <li class="lpt-widget-item">
                            <a href="<?php echo build_page_filter_url(['id' => $d['id'], 'trang' => 1]); ?>" 
                               class="lpt-widget-link <?php if($is_active_cat) echo 'active'; ?>">
                                <span class="bullet-icon"></span>
                                <span><?php echo htmlspecialchars($d['tendanhmuc']); ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- HỘP 3: TÌM KIẾM & KHOẢNG GIÁ -->
        <div class="lpt-widget-box">
            <div class="lpt-widget-header">
                TÌM KIẾM &amp; KHOẢNG GIÁ
            </div>
            <div class="lpt-widget-body">
                <form action="index.php" method="get">
                    <input type="hidden" name="action" value="group">
                    <?php if($cur_id > 0): ?>
                    <input type="hidden" name="id" value="<?php echo $cur_id; ?>">
                    <?php endif; ?>
                    <?php if(!empty($cur_model)): ?>
                    <input type="hidden" name="model" value="<?php echo htmlspecialchars($cur_model); ?>">
                    <?php endif; ?>
                    <?php if(!empty($cur_sort)): ?>
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($cur_sort); ?>">
                    <?php endif; ?>

                    <!-- Từ khóa -->
                    <div class="mb-3">
                        <label for="filter_tukhoa" class="lpt-filter-form-label">Từ khóa tìm kiếm:</label>
                        <input type="text" id="filter_tukhoa" name="tukhoa" class="form-control form-control-sm" 
                               placeholder="Tên linh kiện, mã phụ tùng..." 
                               value="<?php echo htmlspecialchars($cur_tukhoa); ?>">
                    </div>

                    <!-- Mức giá -->
                    <div class="mb-3">
                        <label for="filter_mucgia" class="lpt-filter-form-label">Khoảng giá:</label>
                        <select id="filter_mucgia" name="mucgia" class="form-select form-select-sm">
                            <option value="" <?php if($cur_mucgia == '') echo 'selected'; ?>>-- Tất cả mức giá --</option>
                            <option value="duoi_1tr" <?php if($cur_mucgia == 'duoi_1tr') echo 'selected'; ?>>Dưới 1 triệu</option>
                            <option value="1tr_5tr" <?php if($cur_mucgia == '1tr_5tr') echo 'selected'; ?>>1 – 5 triệu</option>
                            <option value="5tr_15tr" <?php if($cur_mucgia == '5tr_15tr') echo 'selected'; ?>>5 – 15 triệu</option>
                            <option value="tren_15tr" <?php if($cur_mucgia == 'tren_15tr') echo 'selected'; ?>>Trên 15 triệu</option>
                        </select>
                    </div>

                    <!-- Nút áp dụng lọc -->
                    <button type="submit" class="lpt-btn-filter-apply">
                        <i class="bi bi-funnel me-1"></i> Áp dụng bộ lọc
                    </button>

                    <!-- Link xóa lọc -->
                    <a href="index.php?action=group" class="d-block text-center mt-2 small text-secondary text-decoration-none">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Xóa tất cả lọc
                    </a>
                </form>
            </div>
        </div>

    </aside>

    <!-- CỘT PHẢI (DANH SÁCH SẢN PHẨM & TOOLBAR) -->
    <div class="lpt-catalog-main">
        
        <!-- TOOLBAR: TỔNG SẢN PHẨM & SẮP XẾP -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #E4E4E7; flex-wrap: wrap; gap: 12px;">
            <div style="font-size: 14px; color: #52525B;">
                Tổng sản phẩm: <strong style="color: #18181B;"><?php echo isset($tongsomathang) ? (int)$tongsomathang : (is_array($mathang) ? count($mathang) : 0); ?></strong> linh kiện
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <label for="catalogSortSelect" style="font-size: 13px; color: #52525B; margin: 0; white-space: nowrap;">Sắp xếp theo:</label>
                <select id="catalogSortSelect" class="form-select form-select-sm" style="width: auto; min-width: 170px; font-size: 13px;" onchange="location.href=this.value;">
                    <option value="<?php echo build_page_filter_url(['sort' => null, 'trang' => 1]); ?>" <?php if(empty($cur_sort)) echo 'selected'; ?>>Mới nhất</option>
                    <option value="<?php echo build_page_filter_url(['sort' => 'gia_tang', 'trang' => 1]); ?>" <?php if($cur_sort == 'gia_tang') echo 'selected'; ?>>Giá: Thấp đến cao</option>
                    <option value="<?php echo build_page_filter_url(['sort' => 'gia_giam', 'trang' => 1]); ?>" <?php if($cur_sort == 'gia_giam') echo 'selected'; ?>>Giá: Cao đến thấp</option>
                    <option value="<?php echo build_page_filter_url(['sort' => 'banchay', 'trang' => 1]); ?>" <?php if($cur_sort == 'banchay') echo 'selected'; ?>>Bán chạy nhất</option>
                </select>
            </div>
        </div>

        <!-- ACTIVE FILTER CHIPS (NẾU ĐANG CÓ LỌC) -->
        <?php if($cur_id > 0 || !empty($cur_model) || !empty($cur_mucgia) || !empty($cur_tukhoa)): ?>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <span class="small text-muted fw-semibold">Đang lọc theo:</span>
            <?php if(!empty($cur_tukhoa)): ?>
            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2">
                Từ khóa: "<?php echo htmlspecialchars($cur_tukhoa); ?>"
                <a href="<?php echo build_page_filter_url(['tukhoa' => null, 'trang' => 1]); ?>" class="text-danger ms-1 text-decoration-none fw-bold" title="Bỏ lọc từ khóa">&times;</a>
            </span>
            <?php endif; ?>
            <?php if($cur_id > 0): ?>
            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2">
                Danh mục: <?php echo htmlspecialchars($page_title); ?>
                <a href="<?php echo build_page_filter_url(['id' => null, 'trang' => 1]); ?>" class="text-danger ms-1 text-decoration-none fw-bold" title="Bỏ lọc danh mục">&times;</a>
            </span>
            <?php endif; ?>
            <?php if(!empty($cur_model)): ?>
            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2">
                Dòng máy: XAG <?php echo htmlspecialchars($cur_model); ?>
                <a href="<?php echo build_page_filter_url(['model' => null, 'trang' => 1]); ?>" class="text-danger ms-1 text-decoration-none fw-bold" title="Bỏ lọc dòng máy">&times;</a>
            </span>
            <?php endif; ?>
            <?php if(!empty($cur_mucgia)): 
                $price_labels = [
                    'duoi_1tr' => 'Dưới 1 triệu',
                    '1tr_5tr' => '1 – 5 triệu',
                    '5tr_15tr' => '5 – 15 triệu',
                    'tren_15tr' => 'Trên 15 triệu'
                ];
                $mucgia_text = isset($price_labels[$cur_mucgia]) ? $price_labels[$cur_mucgia] : $cur_mucgia;
            ?>
            <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-1 py-1 px-2">
                Giá: <?php echo htmlspecialchars($mucgia_text); ?>
                <a href="<?php echo build_page_filter_url(['mucgia' => null, 'trang' => 1]); ?>" class="text-danger ms-1 text-decoration-none fw-bold" title="Bỏ lọc mức giá">&times;</a>
            </span>
            <?php endif; ?>
            <a href="index.php?action=group" class="small text-danger ms-2 text-decoration-none fw-semibold">
                <i class="bi bi-x-circle me-1"></i> Xóa tất cả
            </a>
        </div>
        <?php endif; ?>

        <!-- LƯỚI SẢN PHẨM 3 CỘT (PRODUCT GRID) -->
        <div class="lpt-product-grid">
            <?php 
            if(!empty($mathang) && is_array($mathang)):
                foreach($mathang as $m):
                    $model_tag = !empty($m["dongmay"]) ? $m["dongmay"] : "XAG Drone";
                    $is_in_stock = isset($m["soluongton"]) && ((int)$m["soluongton"] > 0);
                    $img_src = lay_hinhanh_mathang($m["hinhanh"] ?? "");
            ?>
            <div class="lpt-product-card">
                <!-- Badges -->
                <div class="lpt-card-badges">
                    <span class="lpt-badge-oem">Chính hãng XAG</span>
                    <?php if ($m["giagoc"] > $m["giaban"]): 
                        $percent = round((($m["giagoc"] - $m["giaban"]) / $m["giagoc"]) * 100);
                    ?>
                    <span class="lpt-badge-discount">-<?php echo $percent; ?>%</span>
                    <?php endif; ?>
                </div>

                <!-- Khung ảnh tỷ lệ chuẩn -->
                <a href="index.php?action=detail&id=<?php echo $m["id"]; ?>" class="lpt-card-media">
                    <img src="<?php echo $img_src; ?>" 
                         alt="<?php echo htmlspecialchars($m["tenmathang"]); ?>" 
                         loading="lazy" />
                </a>

                <!-- Thân nội dung thẻ -->
                <div class="lpt-card-body">
                    <!-- Dòng máy & Tồn kho -->
                    <div class="lpt-card-meta">
                        <span class="lpt-model-tag"><?php echo htmlspecialchars($model_tag); ?></span>
                        <?php if($is_in_stock): ?>
                        <span class="lpt-stock-in"><i class="bi bi-check2"></i> Còn hàng</span>
                        <?php else: ?>
                        <span class="lpt-stock-out"><i class="bi bi-dash-circle"></i> Tạm hết</span>
                        <?php endif; ?>
                    </div>

                    <!-- Tên linh kiện (2 dòng) -->
                    <h3 class="lpt-card-title">
                        <a href="index.php?action=detail&id=<?php echo $m["id"]; ?>" title="<?php echo htmlspecialchars($m["tenmathang"]); ?>">
                            <?php echo htmlspecialchars($m["tenmathang"]); ?>
                        </a>
                    </h3>

                    <!-- Giá bán đỏ trầm #B91C1C -->
                    <div class="lpt-card-price">
                        <span class="lpt-price-sale"><?php echo number_format($m["giaban"]); ?> đ</span>
                        <?php if ($m["giagoc"] > $m["giaban"]): ?>
                        <span class="lpt-price-regular"><?php echo number_format($m["giagoc"]); ?> đ</span>
                        <?php endif; ?>
                    </div>

                    <!-- Nút hành động Thêm giỏ & Xem chi tiết -->
                    <div class="lpt-card-actions">
                        <a href="index.php?action=chovaogio&id=<?php echo $m["id"]; ?>" class="lpt-btn-addcart">
                            <i class="bi bi-cart-plus me-1"></i> Thêm vào giỏ
                        </a>
                        <a href="index.php?action=detail&id=<?php echo $m["id"]; ?>" class="lpt-btn-quickview" title="Xem chi tiết">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php 
                endforeach; 
            else:
            ?>
            <div style="grid-column: 1 / -1; padding: 48px 16px; text-align: center;">
                <div class="p-4 p-md-5 bg-white rounded-3 border mx-auto" style="max-width: 520px;">
                    <i class="bi bi-search text-muted display-4 mb-3 d-block"></i>
                    <h5 class="fw-bold text-dark mb-2">Chưa tìm thấy linh kiện phù hợp</h5>
                    <p class="text-secondary small mb-3">Vui lòng điều chỉnh lại bộ lọc, xóa bớt điều kiện hoặc liên hệ trực tiếp với kỹ thuật viên LPT DRONE để tra cứu kho hàng thực tế.</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="index.php?action=group" class="btn btn-danger btn-sm px-3">Xóa tất cả bộ lọc</a>
                        <a href="tel:0833609329" class="btn btn-outline-dark btn-sm px-3"><i class="bi bi-telephone-fill me-1"></i> 0833 609 329</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- PHÂN TRANG (PAGINATION) -->
        <?php if(isset($tongsotrang) && $tongsotrang > 1): ?>
        <div class="d-flex justify-content-center my-4">
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-custom mb-0">
                    <!-- Nút Trang trước -->
                    <li class="page-item <?php if($trang <= 1) echo 'disabled'; ?>">
                        <a class="page-link" href="<?php echo build_page_filter_url(['trang' => max(1, $trang - 1)]); ?>" aria-label="Trang trước">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>

                    <!-- Các trang số -->
                    <?php for($p = 1; $p <= $tongsotrang; $p++): ?>
                    <li class="page-item <?php if($p == $trang) echo 'active'; ?>">
                        <a class="page-link" href="<?php echo build_page_filter_url(['trang' => $p]); ?>"><?php echo $p; ?></a>
                    </li>
                    <?php endfor; ?>

                    <!-- Nút Trang kế -->
                    <li class="page-item <?php if($trang >= $tongsotrang) echo 'disabled'; ?>">
                        <a class="page-link" href="<?php echo build_page_filter_url(['trang' => min($tongsotrang, $trang + 1)]); ?>" aria-label="Trang kế">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>

    </div> <!-- ĐÓNG CỘT PHẢI .lpt-catalog-main -->

</div> <!-- ĐÓNG KHUNG BAO .lpt-catalog-container -->

<?php 
// GỌI FOOTER NẰM HOÀN TOÀN NGOÀI KHUNG BỐ CỤC 2 CỘT
include("inc/bottom.php"); 
?>
