<?php 
include("inc/top.php"); 
?>

<div class="row justify-content-center my-5">
    <div class="col-md-8 col-lg-6">
        <div class="bg-white p-4 p-md-5 rounded-4 shadow-sm border text-center">
            <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                <i class="bi bi-check2-circle"></i>
            </div>
            
            <h2 class="fw-bold text-dark mb-2">ĐẶT HÀNG THÀNH CÔNG!</h2>
            <p class="text-muted">Cảm ơn quý khách <strong><?php echo htmlspecialchars($hoten); ?></strong> đã tin tưởng lựa chọn phụ tùng tại <strong>LPT DRONE</strong>.</p>
            
            <div class="p-3 bg-light rounded-3 my-4 text-start small">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Mã đơn hàng:</span>
                    <strong class="text-danger">#XAG-<?php echo $donhang_id; ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tổng giá trị đơn:</span>
                    <strong class="text-dark fs-6"><?php echo number_format($tongtien); ?> đ</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Trạng thái:</span>
                    <span class="badge bg-warning text-dark">Đang chuẩn bị hàng tại kho</span>
                </div>
            </div>

            <p class="small text-secondary mb-4">
                Nhân viên kỹ thuật LPT DRONE sẽ liên hệ qua số điện thoại để xác nhận đơn hàng và điều phối chuyến xe khách / chành xe gần nhất gửi đến quý khách.
            </p>

            <div class="d-flex justify-content-center gap-3">
                <a href="index.php" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                    <i class="bi bi-house-door me-1"></i> Trang chủ
                </a>
                <a href="index.php?action=thongtin" class="btn btn-danger px-4 py-2 rounded-pill">
                    <i class="bi bi-receipt me-1"></i> Xem đơn hàng
                </a>
            </div>
        </div>
    </div>
</div>

<?php include("inc/bottom.php"); ?>
