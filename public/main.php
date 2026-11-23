<?php
include("inc/top.php");
?>

<div class="container my-3">
    <!-- 1. HERO BANNER / CAROUSEL GIỚI THIỆU HỆ SINH THÁI LPT DRONE -->
    <?php include("inc/carousel.php"); ?>

    <!-- 2. KHỐI GIỚI THIỆU NHANH & CAM KẾT DỊCH VỤ (VALUE PROPOSITIONS - 4 THẺ NGANG GỌN GÀNG) -->
    <section class="mb-5">
        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="value-prop-card">
                    <div class="value-prop-icon">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <div>
                        <div class="value-prop-title">Chính Hãng 100%</div>
                        <p class="value-prop-desc">Linh kiện chuẩn xuất xưởng XAG, nguyên seal bảo chứng chất lượng.</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="value-prop-card">
                    <div class="value-prop-icon">
                        <i class="bi bi-cpu-fill"></i>
                    </div>
                    <div>
                        <div class="value-prop-title">Tương Thích Chuẩn Đời Máy</div>
                        <p class="value-prop-desc">Đầy đủ phụ tùng cho từng thế hệ P100 Pro, P100, P40, V40, R150.</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="value-prop-card">
                    <div class="value-prop-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <div>
                        <div class="value-prop-title">Giao Hỏa Tốc Chành Xe</div>
                        <p class="value-prop-desc">Gửi hỏa tốc bưu cục hoặc nhà xe lấy trong ngày kịp thời vụ phun.</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="value-prop-card">
                    <div class="value-prop-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <div>
                        <div class="value-prop-title">Kỹ Thuật Hỗ Trợ 24/7</div>
                        <p class="value-prop-desc">Kỹ sư am hiểu máy bay trực tiếp hỗ trợ chuẩn đoán lỗi và lắp đặt.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. HIỂN THỊ DANH MỤC THEO TỪNG PHẦN (TUẦN PHẦN / CATEGORY SECTIONS) -->
    <!-- (Ẩn hoàn toàn bộ lọc khỏi Trang chủ để giao diện thoáng, tinh gọn) -->
    <div class="category-sections-wrapper">
        <?php 
        $has_any_category_products = false;
        if(isset($danhmuc) && is_array($danhmuc)):
            foreach($danhmuc as $d):
                // Lấy tối đa 4 sản phẩm tiêu biểu của từng danh mục
                $items = $mh->laymathangtheodanhmucgioihan($d["id"], 4);
                if(!empty($items)):
                    $has_any_category_products = true;
        ?>
        <section class="category-section">
            <!-- Header phân khu: Tiêu đề có thanh đỏ trầm bên trái + Nút Xem tất cả -->
            <div class="category-section-header">
                <h2 class="category-section-title">
                    <?php echo htmlspecialchars($d["tendanhmuc"]); ?>
                </h2>
                <a href="index.php?action=group&id=<?php echo $d["id"]; ?>" class="category-section-link">
                    Xem tất cả <?php echo htmlspecialchars($d["tendanhmuc"]); ?> <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <!-- Lưới hiển thị 4 sản phẩm tiêu biểu của danh mục -->
            <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3">
                <?php 
                foreach($items as $m): 
                    $model_tag = !empty($m["dongmay"]) ? $m["dongmay"] : "XAG Drone";
                    $is_in_stock = isset($m["soluongton"]) && ((int)$m["soluongton"] > 0);
                    $img_src = lay_hinhanh_mathang($m["hinhanh"] ?? "");
                ?>
                <div class="col">
                    <div class="shopify-card">
                        <!-- Badge giảm giá nếu có -->
                        <?php if ($m["giagoc"] > $m["giaban"]): 
                            $percent = round((($m["giagoc"] - $m["giaban"]) / $m["giagoc"]) * 100);
                        ?>
                        <span class="badge-discount">-<?php echo $percent; ?>%</span>
                        <?php endif; ?>

                        <!-- Ảnh sản phẩm tỉ lệ vuông 1:1 -->
                        <a href="index.php?action=detail&id=<?php echo $m["id"]; ?>" class="card-media">
                            <img src="<?php echo $img_src; ?>" 
                                 alt="<?php echo htmlspecialchars($m["tenmathang"]); ?>" 
                                 loading="lazy" />
                        </a>

                        <!-- Nội dung thẻ sản phẩm -->
                        <div class="card-content d-flex flex-column justify-content-between flex-grow-1">
                            <div>
                                <!-- Dòng máy tương thích & Tình trạng kho -->
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="card-model-tag"><?php echo htmlspecialchars($model_tag); ?></span>
                                    <?php if($is_in_stock): ?>
                                    <span class="stock-badge-in">
                                        <i class="bi bi-check2"></i> Còn hàng
                                    </span>
                                    <?php else: ?>
                                    <span class="stock-badge-out">
                                        <i class="bi bi-dash-circle"></i> Tạm hết
                                    </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Tên linh kiện -->
                                <h3 class="card-product-title">
                                    <a href="index.php?action=detail&id=<?php echo $m["id"]; ?>" title="<?php echo htmlspecialchars($m["tenmathang"]); ?>">
                                        <?php echo htmlspecialchars($m["tenmathang"]); ?>
                                    </a>
                                </h3>

                                <!-- Giá bán đỏ đậm -->
                                <div class="card-price-row mb-2">
                                    <span class="card-price-sale"><?php echo number_format($m["giaban"]); ?> đ</span>
                                    <?php if ($m["giagoc"] > $m["giaban"]): ?>
                                    <span class="card-price-regular"><?php echo number_format($m["giagoc"]); ?> đ</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Nhóm nút hành động -->
                            <div class="d-flex gap-1 mt-2">
                                <a href="index.php?action=chovaogio&id=<?php echo $m["id"]; ?>" class="btn-card-action flex-grow-1">
                                    <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                                </a>
                                <a href="index.php?action=detail&id=<?php echo $m["id"]; ?>" class="btn btn-sm btn-outline-secondary px-2 d-inline-flex align-items-center justify-content-center" title="Xem chi tiết">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php 
                endif;
            endforeach;
        endif; 

        if(!$has_any_category_products):
        ?>
        <div class="py-5 text-center">
            <div class="p-5 bg-light rounded border mx-auto" style="max-width: 500px;">
                <i class="bi bi-box text-muted display-4 mb-3"></i>
                <h5 class="fw-bold">Kho hàng đang được cập nhật</h5>
                <p class="text-muted small">Vui lòng quay lại sau hoặc liên hệ Hotline để tra cứu linh kiện.</p>
                <a href="index.php?action=group" class="btn btn-danger btn-sm">Xem tất cả sản phẩm</a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- 4. KHỐI LIÊN HỆ KHO HÀNG & TƯ VẤN KỸ THUẬT CUỐI TRANG -->
    <section class="p-4 p-md-5 bg-light rounded-3 border my-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-danger text-white px-3 py-1 mb-2 text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Tư Vấn Kỹ Thuật Máy Bay</span>
                <h3 class="fw-bold text-dark mb-2">CẦN TƯ VẤN LINH KIỆN ĐÚNG MODEL &amp; BẮT BỆNH DRONE?</h3>
                <p class="text-secondary mb-0 small">
                    Đội ngũ kỹ thuật viên trạm bảo dưỡng LPT DRONE sẵn sàng hỗ trợ trực tuyến 24/7 qua Zalo hoặc Hotline. Bắt bệnh chuẩn xác, hướng dẫn thay thế tại nhà, giao hàng lấy ngay trong ngày tại khu vực ĐBSCL.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="tel:0833609329" class="btn btn-danger btn-lg fw-bold px-4 shadow-sm text-nowrap">
                    <i class="bi bi-telephone-fill me-2"></i> 0833 609 329
                </a>
            </div>
        </div>
    </section>
</div>

<?php
include("inc/bottom.php");
?>
