<?php 
include("inc/top.php"); 

// Cấu hình thông tin tài khoản ngân hàng thụ hưởng VietQR LPT DRONE
require_once __DIR__ . '/../config/payment_config.php';
if (!defined('VIETQR_BANK_NAME')) define('VIETQR_BANK_NAME', 'MB Bank - Viettel Money');

// Tính tổng tiền & số món cho các mặt hàng được chọn thanh toán
$tong_tien_don = 0;
if(isset($giohang) && is_array($giohang)){
    foreach($giohang as $mh_item){
        $tong_tien_don += (int)($mh_item['thanhtien'] ?? ($mh_item['giaban'] * $mh_item['soluong']));
    }
} else {
    $tong_tien_don = (int)tinhtiengiohang();
}
$soluong_mon_checkout = isset($giohang) ? count($giohang) : demhangtronggio();

$phone_khach = $_SESSION["khachhang"]["sodienthoai"] ?? "";
$noi_dung_ck_tam = PAYMENT_PREFIX . " " . (!empty($phone_khach) ? preg_replace('/[^0-9]/', '', $phone_khach) : "DH");
$vietqr_preview_url = "https://img.vietqr.io/image/" . VIETQR_BANK_ID . "-" . VIETQR_ACCOUNT_NO . "-" . VIETQR_TEMPLATE . ".png?amount=" . $tong_tien_don . "&addInfo=" . urlencode($noi_dung_ck_tam) . "&accountName=" . urlencode(VIETQR_ACCOUNT_NAME);
?>

<style>
.payment-method-card {
    transition: all 0.2s ease;
    cursor: pointer;
    background: #ffffff;
}
.payment-method-card:hover {
    border-color: #fca5a5 !important;
    background-color: #fffafb;
}
.payment-method-card.active {
    border-color: #B91C1C !important;
    background-color: #FEF2F2;
}
</style>

<!-- Breadcrumb -->
<section class="py-3 bg-light border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="collection-breadcrumb mb-0">
            <a href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a>
            <span class="mx-1">/</span>
            <a href="index.php?action=giohang">Giỏ hàng</a>
            <span class="mx-1">/</span>
            <span class="text-dark fw-semibold">Thanh toán & Đặt hàng</span>
        </nav>
    </div>
</section>

<div class="container mb-5">
    <div class="row g-4">
        <!-- Cột trái: Thông tin nhận hàng -->
        <div class="col-lg-7">
            <div class="bg-white p-4 p-md-5 rounded border">
                <h2 class="h5 fw-bold text-dark text-uppercase border-bottom pb-3 mb-4">
                    <i class="bi bi-truck text-danger me-2"></i>Thông tin nhận hàng
                </h2>

                <?php if(!empty($tb_loi)): ?>
                <div class="alert alert-danger d-flex align-items-center mb-4 py-3" role="alert">
                    <i class="bi bi-exclamation-octagon-fill fs-4 me-3 flex-shrink-0 text-danger"></i>
                    <div>
                        <strong class="d-block mb-1">Không thể hoàn tất đặt hàng:</strong>
                        <span class="small"><?php echo htmlspecialchars($tb_loi); ?></span>
                    </div>
                </div>
                <?php endif; ?>

                <?php if(isset($_SESSION["khachhang"])): ?>
                <div class="alert alert-light border py-2 mb-4 d-flex align-items-center">
                    <i class="bi bi-check-circle-fill text-success fs-5 me-2"></i>
                    <div class="small">Tài khoản thành viên: <strong><?php echo htmlspecialchars($_SESSION["khachhang"]["hoten"]); ?></strong></div>
                </div>

                <form method="post" action="index.php">
                    <input type="hidden" name="action" value="luudonhang">
                    <input type="hidden" name="txtid" value="<?php echo $_SESSION["khachhang"]["id"]; ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Địa chỉ Email:</label>
                        <input type="email" class="form-control" name="txtemail" value="<?php echo htmlspecialchars($_SESSION["khachhang"]["email"]); ?>" readonly>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Họ và tên người nhận:</label>
                            <input type="text" class="form-control" name="txthoten" value="<?php echo htmlspecialchars($_SESSION["khachhang"]["hoten"]); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Số điện thoại:</label>
                            <input type="text" class="form-control" name="txtsodienthoai" value="<?php echo htmlspecialchars($_SESSION["khachhang"]["sodienthoai"]); ?>" readonly>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Địa chỉ giao hàng / Nhà xe nhận hàng:</label>
                        <textarea class="form-control" name="txtdiachi" rows="3" placeholder="Nhập địa chỉ nhận hàng hoặc thông tin nhà xe, chành xe..." required><?php echo isset($diachi["diachi"]) ? htmlspecialchars($diachi["diachi"]) : ''; ?></textarea>
                        <small class="text-muted">Ví dụ: Chành xe Tô Châu (Trạm Long Xuyên) hoặc địa chỉ nhà riêng...</small>
                    </div>

                    <!-- PHƯƠNG THỨC THANH TOÁN -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark text-uppercase mb-2">
                            <i class="bi bi-credit-card-2-front text-danger me-1"></i> Phương thức thanh toán:
                        </label>
                        
                        <div class="d-flex flex-column gap-2">
                            <!-- 1. COD -->
                            <label class="payment-method-card p-3 rounded border d-flex align-items-center justify-content-between active" for="mem_pay_cod">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input mt-0 fs-5" type="radio" name="opt_thanhtoan" id="mem_pay_cod" value="COD" checked onchange="togglePaymentMethod('COD', 'mem_')">
                                    <div>
                                        <div class="fw-bold text-dark small">Thanh toán tiền mặt khi nhận hàng (COD)</div>
                                        <div class="text-muted small">Thanh toán cho bên vận chuyển sau khi mở bưu kiện kiểm tra đúng linh kiện.</div>
                                    </div>
                                </div>
                                <span class="badge bg-light text-secondary border">Mặc định</span>
                            </label>

                            <!-- 2. VietQR -->
                            <label class="payment-method-card p-3 rounded border d-flex align-items-center justify-content-between" for="mem_pay_vietqr">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input mt-0 fs-5" type="radio" name="opt_thanhtoan" id="mem_pay_vietqr" value="VIETQR" onchange="togglePaymentMethod('VIETQR', 'mem_')">
                                    <div>
                                        <div class="fw-bold text-danger small d-flex align-items-center gap-2">
                                            <i class="bi bi-qr-code-scan"></i> Chuyển khoản ngân hàng nhanh qua mã QR (VietQR 24/7)
                                            <span class="badge bg-danger text-white">Khuyên dùng</span>
                                        </div>
                                        <div class="text-muted small">Quét mã QR tự động điền số tài khoản, số tiền và nội dung, xử lý đơn ưu tiên.</div>
                                    </div>
                                </div>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">24/7</span>
                            </label>
                        </div>

                        <!-- Khung hướng dẫn QR mở rộng khi chọn VietQR -->
                        <div id="mem_vietqr-box" class="mt-3 p-3 bg-light rounded border border-danger-subtle" style="display: none;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold text-danger small">
                                    <i class="bi bi-qr-code text-danger me-1"></i> Quét mã QR thanh toán nhanh:
                                </span>
                                <span class="badge bg-white text-secondary border">Tự động điền thông tin</span>
                            </div>

                            <div class="row g-3 align-items-center">
                                <div class="col-sm-5 text-center">
                                    <div class="p-2 bg-white rounded border d-inline-block shadow-sm">
                                        <img src="<?php echo $vietqr_preview_url; ?>" alt="VietQR LPT DRONE" class="img-fluid rounded" style="max-height: 200px;">
                                        <div class="text-muted small mt-1" style="font-size: 11px;"><i class="bi bi-camera me-1"></i>Quét bằng App Ngân hàng</div>
                                    </div>
                                </div>
                                <div class="col-sm-7">
                                    <div class="bg-white rounded border overflow-hidden">
                                        <table class="table table-sm table-borderless mb-0" style="font-size: 12px;">
                                            <tbody>
                                                <tr class="border-bottom">
                                                    <td class="text-muted py-1 ps-2" style="width: 100px;">Ngân hàng:</td>
                                                    <td class="fw-bold text-dark py-1 pe-2"><?php echo VIETQR_BANK_NAME; ?></td>
                                                </tr>
                                                <tr class="border-bottom">
                                                    <td class="text-muted py-1 ps-2">Số tài khoản:</td>
                                                    <td class="py-1 pe-2">
                                                        <strong class="text-danger font-monospace fs-6 me-2"><?php echo trim(chunk_split(VIETQR_ACCOUNT_NO, 4, ' ')); ?></strong>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 11px;" onclick="copyValue('<?php echo VIETQR_ACCOUNT_NO; ?>', this)">
                                                            <i class="bi bi-copy"></i> Copy
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr class="border-bottom">
                                                    <td class="text-muted py-1 ps-2">Chủ TK:</td>
                                                    <td class="fw-bold text-dark py-1 pe-2"><?php echo VIETQR_ACCOUNT_NAME; ?></td>
                                                </tr>
                                                <tr class="border-bottom">
                                                    <td class="text-muted py-1 ps-2">Số tiền:</td>
                                                    <td class="py-1 pe-2">
                                                        <strong class="text-danger fs-6 me-2"><?php echo number_format($tong_tien_don); ?> đ</strong>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 11px;" onclick="copyValue('<?php echo $tong_tien_don; ?>', this)">
                                                            <i class="bi bi-copy"></i> Copy
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted py-1 ps-2">Nội dung:</td>
                                                    <td class="py-1 pe-2">
                                                        <span class="font-monospace text-dark fw-bold me-2"><?php echo $noi_dung_ck_tam; ?></span>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 11px;" onclick="copyValue('<?php echo $noi_dung_ck_tam; ?>', this)">
                                                            <i class="bi bi-copy"></i> Copy
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-muted mt-2" style="font-size: 11px;">
                                        <i class="bi bi-info-circle text-danger me-1"></i> Sau khi bấm <strong>"Xác nhận đặt mua"</strong>, hệ thống sẽ cấp mã đơn hàng chính thức và hiển thị mã QR kèm nội dung chính xác <code>LPTDH[Mã Đơn]</code>.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold py-3 text-uppercase">
                        Xác nhận đặt mua sản phẩm
                    </button>
                </form>

                <?php else: ?>
                <div class="alert alert-light border mb-4 d-flex justify-content-between align-items-center small">
                    <span>Đã có tài khoản thành viên LPT DRONE?</span>
                    <a href="index.php?action=dangnhap&redirect=checkout" class="text-danger fw-bold">Đăng nhập ngay</a>
                </div>

                <form method="post" action="index.php">
                    <input type="hidden" name="action" value="luudonhang">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Email nhận thông báo đơn hàng <span class="text-danger">*</span>:</label>
                        <input type="email" class="form-control" name="txtemail" placeholder="email@gmail.com" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Họ và tên người nhận <span class="text-danger">*</span>:</label>
                            <input type="text" class="form-control" name="txthoten" placeholder="Nguyễn Văn A" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Số điện thoại liên hệ <span class="text-danger">*</span>:</label>
                            <input type="tel" class="form-control" name="txtsodienthoai" placeholder="0988xxxxxx" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Địa chỉ giao hàng / Thông tin chành xe <span class="text-danger">*</span>:</label>
                        <textarea class="form-control" name="txtdiachi" rows="3" placeholder="Nhập địa chỉ nhà riêng hoặc nhà xe gửi hàng mong muốn..." required></textarea>
                    </div>

                    <!-- PHƯƠNG THỨC THANH TOÁN (KHÁCH VÃNG LAI) -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark text-uppercase mb-2">
                            <i class="bi bi-credit-card-2-front text-danger me-1"></i> Phương thức thanh toán:
                        </label>
                        
                        <div class="d-flex flex-column gap-2">
                            <label class="payment-method-card p-3 rounded border d-flex align-items-center justify-content-between active" for="guest_pay_cod">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input mt-0 fs-5" type="radio" name="opt_thanhtoan" id="guest_pay_cod" value="COD" checked onchange="togglePaymentMethod('COD', 'guest_')">
                                    <div>
                                        <div class="fw-bold text-dark small">Thanh toán tiền mặt khi nhận hàng (COD)</div>
                                        <div class="text-muted small">Thanh toán cho bên vận chuyển sau khi kiểm tra đúng linh kiện.</div>
                                    </div>
                                </div>
                                <span class="badge bg-light text-secondary border">Mặc định</span>
                            </label>

                            <label class="payment-method-card p-3 rounded border d-flex align-items-center justify-content-between" for="guest_pay_vietqr">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input mt-0 fs-5" type="radio" name="opt_thanhtoan" id="guest_pay_vietqr" value="VIETQR" onchange="togglePaymentMethod('VIETQR', 'guest_')">
                                    <div>
                                        <div class="fw-bold text-danger small d-flex align-items-center gap-2">
                                            <i class="bi bi-qr-code-scan"></i> Chuyển khoản ngân hàng nhanh qua mã QR (VietQR 24/7)
                                            <span class="badge bg-danger text-white">Khuyên dùng</span>
                                        </div>
                                        <div class="text-muted small">Quét mã QR tự động điền số tài khoản, số tiền và nội dung, xử lý nhanh.</div>
                                    </div>
                                </div>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">24/7</span>
                            </label>
                        </div>

                        <!-- Khung hướng dẫn QR mở rộng khi chọn VietQR -->
                        <div id="guest_vietqr-box" class="mt-3 p-3 bg-light rounded border border-danger-subtle" style="display: none;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fw-bold text-danger small">
                                    <i class="bi bi-qr-code text-danger me-1"></i> Quét mã QR thanh toán nhanh:
                                </span>
                                <span class="badge bg-white text-secondary border">Tự động điền thông tin</span>
                            </div>

                            <div class="row g-3 align-items-center">
                                <div class="col-sm-5 text-center">
                                    <div class="p-2 bg-white rounded border d-inline-block shadow-sm">
                                        <img src="<?php echo $vietqr_preview_url; ?>" alt="VietQR LPT DRONE" class="img-fluid rounded" style="max-height: 200px;">
                                        <div class="text-muted small mt-1" style="font-size: 11px;"><i class="bi bi-camera me-1"></i>Quét bằng App Ngân hàng</div>
                                    </div>
                                </div>
                                <div class="col-sm-7">
                                    <div class="bg-white rounded border overflow-hidden">
                                        <table class="table table-sm table-borderless mb-0" style="font-size: 12px;">
                                            <tbody>
                                                <tr class="border-bottom">
                                                    <td class="text-muted py-1 ps-2" style="width: 100px;">Ngân hàng:</td>
                                                    <td class="fw-bold text-dark py-1 pe-2"><?php echo VIETQR_BANK_NAME; ?></td>
                                                </tr>
                                                <tr class="border-bottom">
                                                    <td class="text-muted py-1 ps-2">Số tài khoản:</td>
                                                    <td class="py-1 pe-2">
                                                        <strong class="text-danger font-monospace fs-6 me-2"><?php echo trim(chunk_split(VIETQR_ACCOUNT_NO, 4, ' ')); ?></strong>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 11px;" onclick="copyValue('<?php echo VIETQR_ACCOUNT_NO; ?>', this)">
                                                            <i class="bi bi-copy"></i> Copy
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr class="border-bottom">
                                                    <td class="text-muted py-1 ps-2">Chủ TK:</td>
                                                    <td class="fw-bold text-dark py-1 pe-2"><?php echo VIETQR_ACCOUNT_NAME; ?></td>
                                                </tr>
                                                <tr class="border-bottom">
                                                    <td class="text-muted py-1 ps-2">Số tiền:</td>
                                                    <td class="py-1 pe-2">
                                                        <strong class="text-danger fs-6 me-2"><?php echo number_format($tong_tien_don); ?> đ</strong>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 11px;" onclick="copyValue('<?php echo $tong_tien_don; ?>', this)">
                                                            <i class="bi bi-copy"></i> Copy
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted py-1 ps-2">Nội dung:</td>
                                                    <td class="py-1 pe-2">
                                                        <span class="font-monospace text-dark fw-bold me-2"><?php echo $noi_dung_ck_tam; ?></span>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 11px;" onclick="copyValue('<?php echo $noi_dung_ck_tam; ?>', this)">
                                                            <i class="bi bi-copy"></i> Copy
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-muted mt-2" style="font-size: 11px;">
                                        <i class="bi bi-info-circle text-danger me-1"></i> Sau khi bấm <strong>"Xác nhận đặt mua"</strong>, hệ thống sẽ cấp mã đơn hàng chính thức và hiển thị mã QR kèm nội dung chính xác <code>LPTDH[Mã Đơn]</code>.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold py-3 text-uppercase">
                        Xác nhận đặt mua sản phẩm
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cột phải: Xem lại đơn hàng -->
        <div class="col-lg-5">
            <div class="bg-white p-4 rounded border">
                <h3 class="h6 fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">
                    Chi tiết đơn hàng (<?php echo $soluong_mon_checkout; ?> sản phẩm)
                </h3>

                <div class="table-responsive mb-3">
                    <table class="table table-sm align-middle">
                        <tbody>
                            <?php foreach($giohang as $id => $mh): ?>
                            <tr>
                                <td style="width: 50px;">
                                    <img src="<?php echo lay_hinhanh_mathang($mh["hinhanh"] ?? ""); ?>" 
                                         alt="<?php echo htmlspecialchars($mh["tenmathang"]); ?>" 
                                         class="rounded border bg-light" 
                                         style="width: 44px; height: 44px; object-fit: contain; padding: 2px;">
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark text-truncate" style="max-width: 180px;">
                                        <?php echo htmlspecialchars($mh["tenmathang"]); ?>
                                    </div>
                                    <small class="text-muted"><?php echo number_format($mh["giaban"]); ?> đ x <?php echo $mh["soluong"]; ?></small>
                                </td>
                                <td class="text-end fw-bold text-dark small text-nowrap">
                                    <?php echo number_format($mh["thanhtien"]); ?> đ
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-3 bg-light rounded small mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-secondary">Tạm tính:</span>
                        <strong class="text-dark"><?php echo number_format($tong_tien_don); ?> đ</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Phương thức:</span>
                        <span class="text-dark fw-semibold">Ship COD / Chuyển khoản</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-baseline">
                        <span class="fw-bold text-dark">TỔNG TIỀN:</span>
                        <span class="fs-4 fw-bolder text-danger"><?php echo number_format($tong_tien_don); ?> đ</span>
                    </div>
                </div>

                <div class="small text-secondary">
                    <i class="bi bi-shield-check text-success me-1"></i> Quý khách được mở bưu kiện kiểm tra sản phẩm chuẩn thông số trước khi thanh toán tiền cho bên giao nhận.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePaymentMethod(val, prefix) {
    const parentForm = document.getElementById(prefix + 'pay_cod').closest('form');
    if (parentForm) {
        parentForm.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('active'));
    }
    const targetLabel = document.querySelector(`label[for="${prefix}pay_${val.toLowerCase()}"]`);
    if (targetLabel) {
        targetLabel.classList.add('active');
    }
    const box = document.getElementById(prefix + 'vietqr-box');
    if (box) {
        box.style.display = (val === 'VIETQR') ? 'block' : 'none';
    }
}

function copyValue(text, btn) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => showCopiedState(btn)).catch(() => fallbackCopy(text, btn));
    } else {
        fallbackCopy(text, btn);
    }
}

function fallbackCopy(text, btn) {
    const tempInput = document.createElement("input");
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    try {
        document.execCommand("copy");
        showCopiedState(btn);
    } catch (e) {
        alert("Copy: " + text);
    }
    document.body.removeChild(tempInput);
}

function showCopiedState(btn) {
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check2"></i> Đã copy';
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('btn-success');
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-secondary');
    }, 2000);
}
</script>

<?php include("inc/bottom.php"); ?>
