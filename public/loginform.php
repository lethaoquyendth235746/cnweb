<?php
include("inc/top.php");

$active_tab = isset($active_tab) ? $active_tab : (isset($_GET['tab']) && $_GET['tab'] === 'dangky' ? 'dangky' : 'dangnhap');
?>

<!-- Breadcrumb -->
<section class="py-3 bg-light border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="collection-breadcrumb mb-0">
            <a href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a>
            <span class="mx-1">/</span>
            <span class="text-dark fw-semibold">Tài khoản khách hàng</span>
        </nav>
    </div>
</section>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="bg-white p-4 p-md-5 rounded border shadow-sm">
                <div class="text-center mb-4">
                    <img src="../images/logo.png" alt="LPT DRONE" class="img-fluid mb-2" style="max-height: 52px; border-radius: 6px;">
                    <h1 class="h5 fw-bold text-dark text-uppercase mb-1">Tài khoản LPT DRONE</h1>
                    <p class="text-muted small">Đăng nhập hệ thống khách hàng &amp; quản trị viên</p>
                </div>

                <!-- 2 TAB CHUYỂN ĐỔI: ĐĂNG NHẬP / ĐĂNG KÝ -->
                <ul class="nav nav-pills nav-fill p-1 bg-light rounded mb-4" id="authTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold <?php echo ($active_tab !== 'dangky') ? 'active' : ''; ?>" 
                                id="login-tab" data-bs-toggle="pill" data-bs-target="#pills-login" type="button" role="tab">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold <?php echo ($active_tab === 'dangky') ? 'active' : ''; ?>" 
                                id="register-tab" data-bs-toggle="pill" data-bs-target="#pills-register" type="button" role="tab">
                            <i class="bi bi-person-plus me-1"></i> Đăng ký mới
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="authTabContent">
                    
                    <!-- TAB 1: FORM ĐĂNG NHẬP -->
                    <div class="tab-pane fade <?php echo ($active_tab !== 'dangky') ? 'show active' : ''; ?>" id="pills-login" role="tabpanel">
                        <?php if(!empty($tb_loi_dn)): ?>
                        <div class="alert alert-danger alert-dismissible fade show small py-2 mb-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo htmlspecialchars($tb_loi_dn); ?>
                            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <form method="post" action="index.php">
                            <input type="hidden" name="action" value="xldangnhap">

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Địa chỉ Email:</label>
                                <input class="form-control" type="email" name="txtemail" placeholder="khachhang@xag.vn" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-secondary">Mật khẩu:</label>
                                <input class="form-control" type="password" name="txtmatkhau" placeholder="Nhập mật khẩu" required>
                            </div>

                            <button class="btn btn-danger w-100 fw-bold py-2 mb-3 text-uppercase" type="submit">
                                Đăng nhập
                            </button>
                        </form>

                        <div class="p-3 bg-light rounded small text-secondary mb-3 border">
                            <div class="fw-bold text-dark mb-1">Gợi ý tài khoản thử nghiệm:</div>
                            <div class="mb-1"><span class="badge bg-danger-subtle text-danger border border-danger-subtle me-1">Khách hàng</span> Email: <code>khachhang@xag.vn</code> | Pass: <code>123456</code></div>
                            <div><span class="badge bg-dark-subtle text-dark border border-dark-subtle me-1">Quản trị</span> Email: <code>admin@xag.vn</code> | Pass: <code>123456</code></div>
                        </div>
                    </div>

                    <!-- TAB 2: FORM ĐĂNG KÝ TÀI KHOẢN MỚI -->
                    <div class="tab-pane fade <?php echo ($active_tab === 'dangky') ? 'show active' : ''; ?>" id="pills-register" role="tabpanel">
                        <?php if(!empty($tb_loi_dk)): ?>
                        <div class="alert alert-danger alert-dismissible fade show small py-2 mb-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo htmlspecialchars($tb_loi_dk); ?>
                            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <form method="post" action="index.php">
                            <input type="hidden" name="action" value="xldangky">

                            <!-- Họ và tên -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Họ và tên <span class="text-danger">*</span>:</label>
                                <input class="form-control form-control-sm" type="text" name="txthoten" 
                                       placeholder="Ví dụ: Nguyễn Văn A" 
                                       value="<?php echo htmlspecialchars($_POST['txthoten'] ?? ''); ?>" required>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Địa chỉ Email <span class="text-danger">*</span>:</label>
                                <input class="form-control form-control-sm" type="email" name="txtemail" 
                                       placeholder="email@domain.vn" 
                                       value="<?php echo htmlspecialchars($_POST['txtemail'] ?? ''); ?>" required>
                            </div>

                            <!-- Số điện thoại -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Số điện thoại <span class="text-danger">*</span> (10 chữ số):</label>
                                <input class="form-control form-control-sm" type="tel" name="txtsodienthoai" 
                                       pattern="[0-9]{10}" maxlength="10" title="Vui lòng nhập số điện thoại gồm đúng 10 chữ số" 
                                       placeholder="Ví dụ: 0912345678" 
                                       value="<?php echo htmlspecialchars($_POST['txtsodienthoai'] ?? ''); ?>" required>
                            </div>

                            <!-- Mật khẩu (tối thiểu 6 ký tự) -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-secondary">Mật khẩu <span class="text-danger">*</span> (Tối thiểu 6 ký tự):</label>
                                <input class="form-control form-control-sm" type="password" name="txtmatkhau" 
                                       minlength="6" placeholder="Nhập mật khẩu (từ 6 ký tự trở lên)" required>
                            </div>

                            <!-- Địa chỉ giao hàng mặc định -->
                            <div class="mb-4">
                                <label class="form-label small fw-semibold text-secondary">Địa chỉ giao hàng mặc định <span class="text-danger">*</span>:</label>
                                <textarea class="form-control form-control-sm" name="txtdiachi" rows="2" 
                                          placeholder="Số nhà, tên đường, ấp/thôn, xã/phường, quận/huyện, tỉnh/thành..." required><?php echo htmlspecialchars($_POST['txtdiachi'] ?? ''); ?></textarea>
                            </div>

                            <button class="btn btn-danger w-100 fw-bold py-2 mb-3 text-uppercase" type="submit">
                                <i class="bi bi-person-check me-1"></i> Đăng ký tài khoản
                            </button>
                        </form>
                    </div>

                </div>

                <div class="text-center small text-muted pt-2 border-top">
                    <a href="index.php" class="text-secondary">&larr; Quay lại cửa hàng</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#authTab .nav-link {
    color: #52525B;
    border-radius: 6px;
    font-size: 0.88rem;
    transition: all 0.2s ease;
}
#authTab .nav-link.active {
    background-color: #B91C1C !important;
    color: #FFFFFF !important;
}
</style>

<?php
include("inc/bottom.php");
?>
