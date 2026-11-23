<?php
include("inc/top.php");
$userHoten = isset($_SESSION['khachhang']['hoten']) ? $_SESSION['khachhang']['hoten'] : '';
$userDienthoai = isset($_SESSION['khachhang']['dienthoai']) ? $_SESSION['khachhang']['dienthoai'] : (isset($_SESSION['khachhang']['sodienthoai']) ? $_SESSION['khachhang']['sodienthoai'] : '');
?>

<!-- Breadcrumb -->
<section class="py-3 bg-light border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="collection-breadcrumb mb-0">
            <a href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a>
            <span class="mx-1">/</span>
            <span class="text-dark fw-semibold">Liên hệ & Hỗ trợ kỹ thuật</span>
        </nav>
    </div>
</section>

<div class="container mb-5">
    <div class="row g-4">
        <!-- Thông tin liên hệ -->
        <div class="col-lg-5">
            <div class="bg-white p-4 p-md-5 rounded border h-100">
                <span class="badge bg-light text-danger border border-danger-subtle px-2 py-1 small fw-bold mb-2">HỖ TRỢ TRỰC TIẾP</span>
                <h1 class="h4 fw-bold text-dark text-uppercase mb-4">TRUNG TÂM PHỤ TÙNG LPT DRONE</h1>
                
                <div class="mb-4">
                    <span class="text-secondary small d-block">Hotline tư vấn kỹ thuật & Đặt hàng:</span>
                    <a href="tel:0833609329" class="fs-4 text-danger fw-bolder text-decoration-none">0833 609 329</a>
                    <div class="text-muted small">Phục vụ 24/7 trong các vụ mùa phun xịt cao điểm</div>
                </div>

                <div class="mb-4">
                    <span class="text-secondary small d-block">Kho sản phẩm & Trạm bảo dưỡng:</span>
                    <strong class="text-dark">Quốc lộ 91, Thị trấn Cái Dầu, Châu Phú, Tỉnh An Giang</strong>
                    <div class="text-muted small">Trung tâm ĐBSCL - Thuận tiện kết nối gửi xe khách đi các tỉnh</div>
                </div>

                <div class="mb-4">
                    <span class="text-secondary small d-block">Email hỗ trợ:</span>
                    <strong class="text-dark">hotro@lptdrone.vn</strong>
                </div>

                <hr>

                <div class="p-3 bg-light rounded small text-secondary">
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-wrench-adjustable text-danger me-1"></i> Dịch vụ sửa chữa & cân chỉnh:</div>
                    <div>Hỗ trợ chuẩn đoán mã lỗi, cân chỉnh la bàn, cài đặt mô-đun RTK và vòi phun ly tâm qua Video call trực tiếp.</div>
                </div>
            </div>
        </div>

        <!-- Form gửi câu hỏi kỹ thuật -->
        <div class="col-lg-7">
            <div class="bg-white p-4 p-md-5 rounded border h-100">
                <h2 class="h5 fw-bold text-dark text-uppercase mb-2">GỬI YÊU CẦU TƯ VẤN SẢN PHẨM</h2>
                <p class="text-muted small mb-4">Để lại thông tin về mã sản phẩm phụ tùng cần tìm hoặc mô tả tình trạng máy bay của bạn:</p>

                <?php if (isset($_SESSION['lienhe_flash'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['lienhe_flash']['type']; ?> alert-dismissible fade show mb-4" role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi <?php echo $_SESSION['lienhe_flash']['type'] === 'success' ? 'bi-check-circle-fill text-success fs-5' : 'bi-exclamation-triangle-fill text-danger fs-5'; ?>"></i>
                            <div><?php echo htmlspecialchars($_SESSION['lienhe_flash']['message']); ?></div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['lienhe_flash']); ?>
                <?php endif; ?>

                <form action="index.php?action=guilienhe" method="POST">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Họ và tên của bạn: <span class="text-danger">*</span></label>
                            <input type="text" name="hoten" class="form-control" placeholder="Ví dụ: Anh Ba An Giang" value="<?php echo htmlspecialchars($userHoten); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Số điện thoại liên hệ: <span class="text-danger">*</span></label>
                            <input type="tel" name="sodienthoai" class="form-control" placeholder="0833 xxx xxx" value="<?php echo htmlspecialchars($userDienthoai); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Dòng máy bay bạn đang vận hành:</label>
                        <select name="dongmay" class="form-select">
                            <?php if (!empty($dongmay_list) && is_array($dongmay_list)): ?>
                                <?php foreach ($dongmay_list as $dm_item): ?>
                                    <option value="XAG <?php echo htmlspecialchars($dm_item['tendongmay']); ?>">XAG <?php echo htmlspecialchars($dm_item['tendongmay']); ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="XAG P100 Pro">XAG P100 Pro</option>
                                <option value="XAG P100">XAG P100</option>
                                <option value="XAG P40">XAG P40</option>
                                <option value="XAG V40">XAG V40</option>
                                <option value="Robot tự hành XAG R150">Robot tự hành XAG R150</option>
                            <?php endif; ?>
                            <option value="Khác">Dòng máy bay khác...</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Sản phẩm cần tìm hoặc tình trạng sự cố: <span class="text-danger">*</span></label>
                        <textarea name="noidung" class="form-control" rows="4" placeholder="Ví dụ: Cần mua cánh 47 inch cho P100 Pro hoặc cần kiểm tra bo mạch SuperX 4..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold py-3 text-uppercase">
                        Gửi yêu cầu kỹ thuật
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include("inc/bottom.php");
?>
