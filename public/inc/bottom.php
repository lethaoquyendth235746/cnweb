    <!-- FLOATING HOTLINE PILL -->
    <a href="tel:0833609329" class="floating-hotline-pill text-decoration-none shadow" title="Gọi Hotline kỹ thuật LPT DRONE">
        <i class="bi bi-telephone-inbound-fill"></i>
        <span>0833 609 329</span>
    </a>

    <!-- SITE FOOTER (DARK CARBON #18181B VỚI ĐIỂM NHẤN ĐỎ TRẦM #B91C1C) -->
    <footer class="site-footer">
        <div class="container">
            <div class="row g-4 pb-4">
                <!-- Cột 1: Logo & Giới thiệu -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <img src="../images/logo.png" alt="LPT DRONE Logo" style="height: 48px; border-radius: 6px;" />
                        <div>
                            <h5 class="text-white fw-bold mb-0">LPT <span class="text-danger">DRONE</span></h5>
                            <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Sản Phẩm & Linh kiện Drone XAG</small>
                        </div>
                    </div>
                    <p class="small text-secondary mb-3">
                        Hệ thống cung cấp phụ tùng, sản phẩm máy bay nông nghiệp XAG chính hãng chuyên nghiệp. Sẵn sàng đồng hành cùng phi công và các trạm dịch vụ trong từng mùa vụ.
                    </p>
                    <div class="small text-secondary">
                        <div class="mb-1"><i class="bi bi-check2-circle text-danger me-1"></i> Linh kiện chuẩn xuất xưởng 100%</div>
                        <div><i class="bi bi-truck text-danger me-1"></i> Gửi xe khách, chành xe hỏa tốc trong 24h</div>
                    </div>
                </div>

                <!-- Cột 2: Danh mục sản phẩm (Động) -->
                <div class="col-6 col-md-3 col-lg-2">
                    <h6 class="footer-heading">DANH MỤC LINH KIỆN</h6>
                    <ul class="footer-links-list">
                        <?php if(isset($danhmuc) && is_array($danhmuc)): ?>
                            <?php foreach ($danhmuc as $d): ?>
                            <li><a href="index.php?action=group&id=<?php echo $d["id"]; ?>"><?php echo htmlspecialchars($d["tendanhmuc"]); ?></a></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><a href="index.php?action=group&id=1">Động cơ (Motor)</a></li>
                            <li><a href="index.php?action=group&id=2">Cánh quạt</a></li>
                            <li><a href="index.php?action=group&id=3">Pin thông minh</a></li>
                            <li><a href="index.php?action=group&id=4">Bơm nhu động</a></li>
                            <li><a href="index.php?action=group&id=5">Vòi phun ly tâm</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Cột 3: Hỗ trợ khách hàng -->
                <div class="col-6 col-md-3 col-lg-3">
                    <h6 class="footer-heading">THÔNG TIN & HỖ TRỢ</h6>
                    <ul class="footer-links-list">
                        <li><a href="index.php?action=gioithieu">Về chúng tôi (LPT DRONE)</a></li>
                        <li><a href="index.php?action=group">Tất cả sản phẩm & Phụ tùng</a></li>
                        <li><a href="index.php?action=lienhe">Liên hệ trạm kỹ thuật</a></li>
                        <li><a href="#">Chính sách bảo hành 1 đổi 1</a></li>
                        <li><a href="#">Quy định kiểm tra hàng khi nhận (COD)</a></li>
                    </ul>
                </div>

                <!-- Cột 4: Liên hệ trực tiếp -->
                <div class="col-12 col-md-6 col-lg-3">
                    <h6 class="footer-heading">LIÊN HỆ TRỰC TIẾP</h6>
                    <div class="small text-secondary mb-2">
                        <span class="text-white fw-bold d-block">Hotline kỹ thuật & Đặt hàng:</span>
                        <a href="tel:0833609329" class="text-danger fw-bolder fs-5 text-decoration-none">0833 609 329</a>
                    </div>
                    <div class="small text-secondary mb-2">
                        <span class="text-white fw-bold d-block">Kho hàng & Trạm bảo dưỡng:</span>
                        QL91, TT. Cái Dầu, Châu Phú, Tỉnh An Giang
                    </div>
                    <div class="small text-secondary mb-2">
                        <span class="text-white fw-bold d-block">Thời gian phục vụ:</span>
                        7:30 - 21:00 (Hỗ trợ khẩn cấp mùa vụ 24/7)
                    </div>
                    <div class="small text-secondary">
                        <span class="text-white fw-bold d-block">Email:</span>
                        hotro@lptdrone.vn
                    </div>
                </div>
            </div>
        </div>

        <!-- Bản quyền chân trang -->
        <div class="footer-bottom-bar text-center">
            <div class="container">
                <p class="mb-0 text-muted">
                    Bản quyền &copy; <?php echo date("Y"); ?> <strong>LPT DRONE</strong>. Tất cả quyền được bảo lưu. Cung cấp sản phẩm phụ tùng XAG chính hãng.
                </p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
