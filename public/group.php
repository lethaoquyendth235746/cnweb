<?php 
include("inc/top.php"); 

$cur_sort = isset($_GET["sort"]) ? $_GET["sort"] : "";
$current_url_params = $_GET;
unset($current_url_params["sort"]);
$base_query = http_build_query($current_url_params);
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border">
        <li class="breadcrumb-item"><a href="index.php" class="text-danger"><i class="bi bi-house-door-fill"></i> Trang chủ</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($tendm); ?></li>
    </ol>
</nav>

<!-- Thanh tiêu đề & Bộ lọc sắp xếp -->
<div class="bg-white p-3 p-md-4 rounded-3 shadow-sm border mb-4">
    <div class="row align-items-center g-3">
        <div class="col-md-7">
            <h2 class="h4 fw-bold text-dark mb-1">
                <i class="bi bi-cpu text-danger me-2"></i><?php echo htmlspecialchars($tendm); ?>
            </h2>
            <p class="text-muted small mb-0">
                Tìm thấy <strong><?php echo is_array($mathang) ? count($mathang) : 0; ?></strong> phụ tùng linh kiện phù hợp
            </p>
        </div>
        <div class="col-md-5 d-flex justify-content-md-end align-items-center gap-2">
            <label class="form-label text-nowrap mb-0 small fw-bold text-muted">Sắp xếp giá:</label>
            <select class="form-select form-select-sm w-auto" onchange="location = this.value;">
                <option value="index.php?<?php echo $base_query; ?>" <?php if($cur_sort=='') echo 'selected'; ?>>Mới nhất</option>
                <option value="index.php?<?php echo $base_query; ?>&sort=gia_tang" <?php if($cur_sort=='gia_tang') echo 'selected'; ?>>Giá: Thấp đến Cao</option>
                <option value="index.php?<?php echo $base_query; ?>&sort=gia_giam" <?php if($cur_sort=='gia_giam') echo 'selected'; ?>>Giá: Cao đến Thấp</option>
            </select>
        </div>
    </div>

    <!-- Bộ lọc nhanh dòng máy -->
    <hr class="my-3">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="small fw-bold text-muted me-1"><i class="bi bi-funnel text-danger"></i> Dòng máy XAG:</span>
        <a href="index.php" class="model-pill <?php if(!isset($_GET['model']) && !isset($_GET['id'])) echo 'active'; ?>">Tất cả</a>
        <a href="index.php?action=dongmay&model=P100 Pro" class="model-pill <?php if(isset($_GET['model']) && $_GET['model']=='P100 Pro') echo 'active'; ?>">XAG P100 Pro</a>
        <a href="index.php?action=dongmay&model=P100" class="model-pill <?php if(isset($_GET['model']) && $_GET['model']=='P100') echo 'active'; ?>">XAG P100</a>
        <a href="index.php?action=dongmay&model=P40" class="model-pill <?php if(isset($_GET['model']) && $_GET['model']=='P40') echo 'active'; ?>">XAG P40</a>
        <a href="index.php?action=dongmay&model=V40" class="model-pill <?php if(isset($_GET['model']) && $_GET['model']=='V40') echo 'active'; ?>">XAG V40</a>
        <a href="index.php?action=dongmay&model=R150" class="model-pill <?php if(isset($_GET['model']) && $_GET['model']=='R150') echo 'active'; ?>">Robot R150</a>
    </div>
</div>

<!-- Lưới sản phẩm -->
<div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3 g-lg-4 mb-5">
<?php 
if(!empty($mathang) && is_array($mathang)):
    foreach($mathang as $m):
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
            <span class="badge-model"><?php echo htmlspecialchars($model_tag); ?></span>

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
    endforeach; 
else:
?>
    <div class="col-12 text-center py-5">
        <div class="bg-white p-5 rounded-4 shadow-sm border mx-auto" style="max-width: 550px;">
            <i class="bi bi-search text-muted display-3 mb-3"></i>
            <h4 class="fw-bold text-dark">Chưa tìm thấy linh kiện phù hợp</h4>
            <p class="text-muted">Rất tiếc, hiện tại danh mục hoặc từ khóa này chưa có sản phẩm. Quý khách vui lòng thử tìm kiếm với từ khóa khác hoặc gọi Hotline để nhân viên kiểm tra kho.</p>
            <div class="d-flex justify-content-center gap-2 mt-4">
                <a href="index.php" class="btn btn-outline-danger px-4 rounded-pill">Về trang chủ</a>
                <a href="tel:0833609329" class="btn btn-danger px-4 rounded-pill"><i class="bi bi-telephone-fill me-1"></i> Gọi Hotline 0833 609 329</a>
            </div>
        </div>
    </div>
<?php endif; ?>
</div>

<?php include("inc/bottom.php"); ?>
