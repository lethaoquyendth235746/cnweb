<?php 
include("inc/top.php"); 
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb bg-white p-3 rounded-3 shadow-sm border">
        <li class="breadcrumb-item"><a href="index.php" class="text-danger"><i class="bi bi-house-door-fill"></i> Trang chủ</a></li>
        <li class="breadcrumb-item active" aria-current="page">Lịch sử đơn hàng</li>
    </ol>
</nav>

<div class="row g-4 mb-5">
    <?php if(isset($_SESSION["khachhang"])): ?>
    <!-- Thông tin khách hàng -->
    <div class="col-lg-4">
        <div class="bg-white p-4 rounded-4 shadow-sm border">
            <div class="text-center mb-3">
                <div class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center mb-2" style="width: 70px; height: 70px; font-size: 1.8rem;">
                    <i class="bi bi-person-fill"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($_SESSION["khachhang"]["hoten"]); ?></h4>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Khách hàng thành viên</span>
            </div>
            
            <hr>

            <div class="small">
                <div class="mb-2"><strong><i class="bi bi-envelope text-danger me-2"></i>Email:</strong> <?php echo htmlspecialchars($_SESSION["khachhang"]["email"]); ?></div>
                <div class="mb-2"><strong><i class="bi bi-telephone text-danger me-2"></i>Điện thoại:</strong> <?php echo htmlspecialchars($_SESSION["khachhang"]["sodienthoai"]); ?></div>
            </div>

            <div class="d-grid mt-4">
                <a href="index.php?action=dangxuat" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất tài khoản
                </a>
            </div>
        </div>
    </div>

    <!-- Danh sách đơn hàng -->
    <div class="col-lg-8">
        <div class="bg-white p-4 rounded-4 shadow-sm border">
            <h3 class="h5 fw-bold text-dark mb-3">
                <i class="bi bi-bag-check-fill text-danger me-2"></i>ĐƠN ĐẶT HÀNG LINH KIỆN ĐÃ ĐẶT
            </h3>

            <?php if(!empty($donhang) && is_array($donhang)): $i = 1; ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Mã ĐH</th>
                            <th>Ngày đặt</th>
                            <th class="text-end">Tổng tiền</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($donhang as $d): ?>
                        <tr>
                            <td class="fw-bold text-danger">#XAG-<?php echo $d["id"]; ?></td>
                            <td><?php echo date("d/m/Y H:i", strtotime($d["ngay"])); ?></td>
                            <td class="text-end fw-bold text-dark"><?php echo number_format($d["tongtien"]); ?> đ</td>
                            <td class="text-center">
                                <a href="index.php?action=thongtin&dhid=<?php echo $d["id"]; ?>" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-eye me-1"></i> Xem chi tiết
                                </a>
                            </td>
                        </tr>

                        <!-- Chi tiết đơn hàng khi click -->
                        <?php if(isset($donhangct) && !empty($donhangct) && ($d["id"] == $donhangct[0]["donhang_id"])): ?>
                        <tr>
                            <td colspan="4" class="p-0">
                                <div class="p-3 bg-light border-top border-bottom">
                                    <h6 class="fw-bold text-dark mb-2">Chi tiết mặt hàng trong đơn #XAG-<?php echo $d["id"]; ?>:</h6>
                                    <table class="table table-sm table-bordered bg-white mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Linh kiện</th>
                                                <th class="text-end">Đơn giá</th>
                                                <th class="text-center">Số lượng</th>
                                                <th class="text-end">Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($donhangct as $dhct): ?>
                                            <tr>
                                                <td>
                                                    <a href="index.php?action=detail&id=<?php echo $dhct["id"]; ?>" class="text-dark fw-semibold text-decoration-none">
                                                        <img src="../<?php echo htmlspecialchars($dhct["hinhanh"]); ?>" width="35" height="35" class="rounded border me-2" style="object-fit: cover;">
                                                        <?php echo htmlspecialchars($dhct["tenmathang"]); ?>
                                                    </a>
                                                </td>
                                                <td class="text-end"><?php echo number_format($dhct["dongia"]); ?> đ</td>
                                                <td class="text-center"><?php echo $dhct["soluong"]; ?></td>
                                                <td class="text-end fw-bold text-danger"><?php echo number_format($dhct["thanhtien"]); ?> đ</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php else: ?>
            <p class="text-muted">Quý khách chưa có đơn đặt hàng nào.</p>
            <a href="index.php" class="btn btn-danger btn-sm">Mua linh kiện ngay</a>
            <?php endif; ?>
        </div>
    </div>

    <?php else: ?>
    <div class="col-12 text-center py-5">
        <div class="bg-white p-5 rounded-4 shadow-sm border mx-auto" style="max-width: 480px;">
            <i class="bi bi-lock-fill text-muted display-3 mb-3"></i>
            <h4 class="fw-bold">Bạn chưa đăng nhập</h4>
            <p class="text-muted">Vui lòng đăng nhập để xem thông tin tài khoản và danh sách đơn hàng đã đặt.</p>
            <a href="index.php?action=dangnhap" class="btn btn-danger rounded-pill px-4">Đăng nhập tài khoản</a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include("inc/bottom.php"); ?>
