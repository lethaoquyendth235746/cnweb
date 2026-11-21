<?php 
include("inc/top.php"); 
?>

<!-- Breadcrumb -->
<section class="py-3 bg-light border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="collection-breadcrumb mb-0">
            <a href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a>
            <span class="mx-1">/</span>
            <span class="text-dark fw-semibold">Đặt hàng thành công</span>
        </nav>
    </div>
</section>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="bg-white p-4 p-md-5 rounded border text-center">
                <i class="bi bi-check-circle-fill text-success display-3 mb-3 d-block"></i>
                
                <h1 class="h4 fw-bold text-dark text-uppercase mb-2">ĐẶT HÀNG THÀNH CÔNG!</h1>
                <p class="text-muted small">Cảm ơn quý khách <strong><?php echo htmlspecialchars($hoten); ?></strong> đã tin tưởng lựa chọn sản phẩm tại <strong>LPT DRONE</strong>.</p>
                
                <div class="p-3 bg-light rounded my-4 text-start small border">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Mã đơn hàng:</span>
                        <strong class="text-danger">#XAG-<?php echo $donhang_id; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Tổng thanh toán:</span>
                        <strong class="text-dark fs-6"><?php echo number_format($tongtien); ?> đ</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary">Trạng thái:</span>
                        <span class="badge bg-warning text-dark">Đang đóng gói xuất kho</span>
                    </div>
                </div>

                <p class="small text-secondary mb-4">
                    Kỹ thuật viên LPT DRONE sẽ liên hệ qua điện thoại để xác nhận đơn hàng và điều phối xe khách / chành xe gần nhất gửi đến quý khách.
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <a href="index.php" class="btn btn-outline-dark btn-sm px-3">
                        <i class="bi bi-arrow-left me-1"></i> Trang chủ
                    </a>
                    <a href="index.php?action=thongtin" class="btn btn-danger btn-sm px-3">
                        <i class="bi bi-box-seam me-1"></i> Quản lý đơn hàng
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("inc/bottom.php"); ?>
