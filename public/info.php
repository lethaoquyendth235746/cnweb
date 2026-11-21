<?php 
include("inc/top.php"); 
?>

<!-- Breadcrumb -->
<section class="py-3 bg-light border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="collection-breadcrumb mb-0">
            <a href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a>
            <span class="mx-1">/</span>
            <span class="text-dark fw-semibold">Lịch sử đơn hàng</span>
        </nav>
    </div>
</section>

<div class="container mb-5">
    <div class="row g-4">
        <?php if(isset($_SESSION["khachhang"])): ?>
        <div class="col-lg-4">
            <div class="bg-white p-4 rounded border">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-light border text-danger d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <h4 class="h6 fw-bold text-dark mb-0"><?php echo htmlspecialchars($_SESSION["khachhang"]["hoten"]); ?></h4>
                        <span class="badge bg-light text-secondary border">Thành viên</span>
                    </div>
                </div>
                
                <hr>

                <div class="small text-secondary mb-3">
                    <div class="mb-2"><strong class="text-dark">Email:</strong> <?php echo htmlspecialchars($_SESSION["khachhang"]["email"]); ?></div>
                    <div><strong class="text-dark">Số điện thoại:</strong> <?php echo htmlspecialchars($_SESSION["khachhang"]["sodienthoai"]); ?></div>
                </div>

                <a href="index.php?action=dangxuat" class="btn btn-outline-danger btn-sm w-100">
                    <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
                </a>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="bg-white p-4 rounded border">
                <h3 class="h6 fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">
                    Danh sách đơn đặt hàng linh kiện
                </h3>

                <?php if(!empty($donhang) && is_array($donhang)): $i = 1; ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr class="text-secondary small text-uppercase">
                                <th>Mã đơn</th>
                                <th>Ngày đặt</th>
                                <th class="text-end">Tổng tiền</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($donhang as $d): ?>
                            <tr>
                                <td class="fw-bold text-danger">#XAG-<?php echo $d["id"]; ?></td>
                                <td class="small"><?php echo date("d/m/Y H:i", strtotime($d["ngay"])); ?></td>
                                <td class="text-end fw-bold text-dark"><?php echo number_format($d["tongtien"]); ?> đ</td>
                                <td class="text-center">
                                    <a href="index.php?action=thongtin&dhid=<?php echo $d["id"]; ?>" class="btn btn-outline-secondary btn-sm">
                                        Xem chi tiết
                                    </a>
                                </td>
                            </tr>

                            <?php if(isset($donhangct) && !empty($donhangct) && ($d["id"] == $donhangct[0]["donhang_id"])): ?>
                            <tr>
                                <td colspan="4" class="p-0">
                                    <div class="p-3 bg-light border-top border-bottom">
                                        <h6 class="fw-bold text-dark mb-2 small text-uppercase">Chi tiết đơn hàng #XAG-<?php echo $d["id"]; ?>:</h6>
                                        <table class="table table-sm table-bordered bg-white mb-0">
                                            <thead class="table-light">
                                                <tr class="small">
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
                                                        <a href="index.php?action=detail&id=<?php echo $dhct["id"]; ?>" class="text-dark small text-decoration-none">
                                                            <img src="../<?php echo htmlspecialchars($dhct["hinhanh"]); ?>" width="30" height="30" class="rounded border me-2" style="object-fit: cover;">
                                                            <?php echo htmlspecialchars($dhct["tenmathang"]); ?>
                                                        </a>
                                                    </td>
                                                    <td class="text-end small"><?php echo number_format($dhct["dongia"]); ?> đ</td>
                                                    <td class="text-center small"><?php echo $dhct["soluong"]; ?></td>
                                                    <td class="text-end small fw-bold text-danger"><?php echo number_format($dhct["thanhtien"]); ?> đ</td>
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
                <p class="text-muted small">Bạn chưa có đơn đặt hàng nào.</p>
                <a href="index.php" class="btn btn-danger btn-sm">Khám phá phụ tùng</a>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>
        <div class="col-12 text-center py-5">
            <div class="bg-white p-5 rounded border mx-auto" style="max-width: 440px;">
                <i class="bi bi-lock text-muted display-4 mb-3"></i>
                <h4 class="h5 fw-bold">Bạn chưa đăng nhập</h4>
                <p class="text-muted small">Vui lòng đăng nhập để xem thông tin tài khoản và danh sách đơn hàng đã mua.</p>
                <a href="index.php?action=dangnhap" class="btn btn-danger btn-sm px-4">Đăng nhập tài khoản</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include("inc/bottom.php"); ?>
