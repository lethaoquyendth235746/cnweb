<?php 
include("inc/top.php"); 

if (!function_exists('render_order_status_badge')) {
    function render_order_status_badge($status) {
        $st = (int)$status;
        $map = [
            0 => [
                'label' => 'Chờ xác nhận', 
                'class' => 'bg-warning-subtle text-warning-emphasis border border-warning'
            ],
            1 => [
                'label' => 'Đã xác nhận', 
                'class' => 'bg-primary-subtle text-primary border border-primary-subtle'
            ],
            2 => [
                'label' => 'Đang vận chuyển', 
                'class' => 'bg-info-subtle text-info-emphasis border border-info-subtle'
            ],
            3 => [
                'label' => 'Hoàn thành', 
                'class' => 'bg-success-subtle text-success border border-success-subtle'
            ],
            4 => [
                'label' => 'Đã hủy', 
                'class' => 'bg-danger-subtle text-danger border border-danger-subtle'
            ],
            5 => [
                'label' => 'Trả hàng / Hoàn tiền', 
                'class' => 'bg-secondary-subtle text-secondary border border-secondary-subtle'
            ],
        ];
        $info = $map[$st] ?? [
            'label' => 'Không xác định', 
            'class' => 'bg-light text-muted border'
        ];
        return '<span class="badge ' . $info['class'] . ' px-2 py-1 fw-semibold small text-nowrap">' . $info['label'] . '</span>';
    }
}
?>

<!-- Breadcrumb -->
<section class="py-3 bg-light border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="collection-breadcrumb mb-0">
            <a href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a>
            <span class="mx-1">/</span>
            <span class="text-dark fw-semibold">Quản lý tài khoản &amp; Lịch sử đơn hàng</span>
        </nav>
    </div>
</section>

<div class="container mb-5">
    <div class="row g-4">
        <?php if(isset($_SESSION["khachhang"])): ?>
        <!-- Cột trái: Thông tin khách hàng -->
        <div class="col-lg-4">
            <div class="bg-white p-4 rounded border">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-light border text-danger d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <h4 class="h6 fw-bold text-dark mb-0"><?php echo htmlspecialchars($_SESSION["khachhang"]["hoten"]); ?></h4>
                        <span class="badge bg-light text-secondary border">Khách hàng thành viên</span>
                    </div>
                </div>
                
                <hr>

                <div class="small text-secondary mb-3">
                    <div class="mb-2"><strong class="text-dark">Email:</strong> <?php echo htmlspecialchars($_SESSION["khachhang"]["email"]); ?></div>
                    <div><strong class="text-dark">Số điện thoại:</strong> <?php echo htmlspecialchars($_SESSION["khachhang"]["sodienthoai"]); ?></div>
                </div>

                <div class="p-3 bg-light rounded border mb-3 small text-secondary">
                    <div class="fw-semibold text-dark mb-1"><i class="bi bi-shield-check text-danger me-1"></i> Quyền lợi thành viên LPT DRONE:</div>
                    <ul class="ps-3 mb-0">
                        <li>Theo dõi trạng thái đơn hàng thời gian thực</li>
                        <li>Chủ động hủy đơn khi đơn ở trạng thái <em>Chờ xác nhận</em></li>
                        <li>Được hỗ trợ kỹ thuật và bắt bệnh Drone ưu tiên</li>
                    </ul>
                </div>

                <a href="index.php?action=dangxuat" class="btn btn-outline-danger btn-sm w-100">
                    <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất tài khoản
                </a>
            </div>
        </div>

        <!-- Cột phải: Danh sách đơn hàng -->
        <div class="col-lg-8">
            <div class="bg-white p-4 rounded border">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                    <h3 class="h6 fw-bold text-dark text-uppercase mb-0">
                        <i class="bi bi-box-seam text-danger me-2"></i>Lịch sử đơn hàng linh kiện
                    </h3>
                    <span class="small text-muted">
                        Tổng cộng: <strong><?php echo is_array($donhang) ? count($donhang) : 0; ?></strong> đơn
                    </span>
                </div>

                <!-- Thông báo flash message nếu có -->
                <?php if(isset($_SESSION['flash_msg'])): ?>
                <div class="alert alert-<?php echo htmlspecialchars($_SESSION['flash_msg']['type']); ?> alert-dismissible fade show small mb-3" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['flash_msg']['text']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash_msg']); endif; ?>

                <?php if(!empty($donhang) && is_array($donhang)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr class="text-secondary small text-uppercase">
                                <th>Mã đơn</th>
                                <th>Ngày đặt</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-end">Tổng tiền</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($donhang as $d): 
                                $status = (int)$d["trangthai"];
                            ?>
                            <tr>
                                <td class="fw-bold text-danger text-nowrap">#XAG-<?php echo $d["id"]; ?></td>
                                <td class="small text-nowrap"><?php echo date("d/m/Y H:i", strtotime($d["ngay"])); ?></td>
                                <td class="text-center">
                                    <?php echo render_order_status_badge($status); ?>
                                </td>
                                <td class="text-end fw-bold text-dark text-nowrap"><?php echo number_format($d["tongtien"]); ?> đ</td>
                                <td class="text-center text-nowrap">
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <a href="index.php?action=thongtin&dhid=<?php echo $d["id"]; ?>" class="btn btn-outline-secondary btn-sm" title="Xem chi tiết đơn">
                                            Chi tiết
                                        </a>

                                        <!-- Nút Hủy đơn hàng: CHỈ HIỆN KHI TRẠNG THÁI = 0 (Chờ xác nhận) -->
                                        <?php if($status === 0): ?>
                                        <form method="post" action="index.php?action=huydonhang" 
                                              onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng #XAG-<?php echo $d['id']; ?>? Tồn kho linh kiện sẽ được tự động hoàn lại.');" 
                                              class="d-inline m-0">
                                            <input type="hidden" name="donhang_id" value="<?php echo $d["id"]; ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Hủy đơn hàng">
                                                <i class="bi bi-x-circle me-1"></i> Hủy
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>

                            <!-- Khối hiển thị chi tiết mặt hàng khi bấm Chi tiết -->
                            <?php if(isset($donhangct) && !empty($donhangct) && ($d["id"] == $donhangct[0]["donhang_id"])): ?>
                            <tr>
                                <td colspan="5" class="p-0">
                                    <div class="p-3 bg-light border-top border-bottom">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-bold text-dark mb-0 small text-uppercase">
                                                Chi tiết các linh kiện trong đơn #XAG-<?php echo $d["id"]; ?>:
                                            </h6>
                                            <span class="small text-muted">Trạng thái: <?php echo render_order_status_badge($status); ?></span>
                                        </div>
                                        <table class="table table-sm table-bordered bg-white mb-0">
                                            <thead class="table-light">
                                                <tr class="small text-secondary">
                                                    <th>Tên linh kiện</th>
                                                    <th class="text-end">Đơn giá</th>
                                                    <th class="text-center">Số lượng</th>
                                                    <th class="text-end">Thành tiền</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($donhangct as $dhct): ?>
                                                <tr>
                                                    <td>
                                                        <a href="index.php?action=detail&id=<?php echo $dhct["id"]; ?>" class="text-dark small text-decoration-none d-flex align-items-center">
                                                            <img src="../<?php echo htmlspecialchars($dhct["hinhanh"]); ?>" width="32" height="32" class="rounded border me-2" style="object-fit: cover;">
                                                            <span><?php echo htmlspecialchars($dhct["tenmathang"]); ?></span>
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
                <div class="text-center py-4">
                    <i class="bi bi-bag-x text-muted display-4 mb-3 d-block"></i>
                    <p class="text-secondary small mb-3">Bạn chưa có đơn đặt hàng nào trong hệ thống.</p>
                    <a href="index.php?action=group" class="btn btn-danger btn-sm">Khám phá phụ tùng Drone ngay</a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>
        <div class="col-12 text-center py-5">
            <div class="bg-white p-5 rounded border mx-auto" style="max-width: 440px;">
                <i class="bi bi-lock text-muted display-4 mb-3 d-block"></i>
                <h4 class="h5 fw-bold text-dark">Bạn chưa đăng nhập</h4>
                <p class="text-secondary small mb-4">Vui lòng đăng nhập tài khoản thành viên để theo dõi tình trạng đơn đặt hàng và quản lý tài khoản.</p>
                <a href="index.php?action=dangnhap" class="btn btn-danger btn-sm px-4">Đăng nhập tài khoản</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include("inc/bottom.php"); ?>
