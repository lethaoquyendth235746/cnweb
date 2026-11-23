<?php 
/**
 * TRANG XÁC NHẬN ĐƠN HÀNG & CỔNG THANH TOÁN TỰ ĐỘNG VIETQR
 * Giao diện LPT DRONE: Đỏ trầm (#B91C1C) - Đen than chì (#18181B) - Phẳng lì hiện đại
 */

include("inc/top.php");
require_once __DIR__ . '/../config/payment_config.php';
require_once __DIR__ . '/../model/donhang.php';

// Kiểm tra và lấy thông tin đơn hàng từ CSDL
$donhang_id = isset($donhang_id) ? (int)$donhang_id : (isset($_GET['dhid']) ? (int)$_GET['dhid'] : 0);
$order = null;

if ($donhang_id > 0) {
    $dh = new DONHANG();
    $order = $dh->laydonhangtheoid($donhang_id);
    if ($order) {
        $tongtien = (float)$order['tongtien'];
        $phuongthucthanhtoan = $order['phuongthucthanhtoan'] ?? 'COD';
        $trangthaithanhtoan = (int)$order['trangthaithanhtoan'];
        $trangthai = (int)$order['trangthai'];
    }
}

if (!isset($tongtien)) $tongtien = 0;
if (!isset($phuongthucthanhtoan)) $phuongthucthanhtoan = 'COD';
if (!isset($trangthaithanhtoan)) $trangthaithanhtoan = 0;
if (!isset($hoten)) $hoten = $_SESSION["khachhang"]["hoten"] ?? "Quý khách";

$isVietQR = ($phuongthucthanhtoan === 'VIETQR');
$transferContent = PAYMENT_PREFIX . $donhang_id;
$vietqr_url = "https://img.vietqr.io/image/" . VIETQR_BANK_ID . "-" . VIETQR_ACCOUNT_NO . "-" . VIETQR_TEMPLATE . ".png?amount=" . (int)$tongtien . "&addInfo=" . urlencode($transferContent) . "&accountName=" . urlencode(VIETQR_ACCOUNT_NAME);
?>

<style>
:root {
    --brand-red: #B91C1C;
    --brand-dark: #18181B;
    --brand-gray: #F8FAFC;
}
.vietqr-card {
    border-top: 4px solid var(--brand-red);
    background: #ffffff;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}
.qr-frame {
    position: relative;
    border: 2px dashed #fca5a5;
    background: #ffffff;
    padding: 12px;
    border-radius: 12px;
    transition: all 0.3s ease;
}
.qr-frame:hover {
    border-color: var(--brand-red);
    box-shadow: 0 8px 24px rgba(185, 28, 28, 0.12);
}
.live-pulse-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: var(--brand-red);
    display: inline-block;
    animation: pulseAnimation 1.5s infinite;
}
@keyframes pulseAnimation {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(185, 28, 28, 0.7); }
    70% { transform: scale(1.1); box-shadow: 0 0 0 8px rgba(185, 28, 28, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(185, 28, 28, 0); }
}
.copy-btn {
    transition: all 0.2s ease;
}
.copy-btn:hover {
    background-color: #f1f5f9;
}
.success-checkmark {
    animation: scaleUp 0.4s ease-out forwards;
}
@keyframes scaleUp {
    from { transform: scale(0.6); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}
</style>

<!-- Breadcrumb -->
<section class="py-3 bg-light border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="collection-breadcrumb mb-0">
            <a href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a>
            <span class="mx-1">/</span>
            <a href="index.php?action=thongtin">Đơn hàng</a>
            <span class="mx-1">/</span>
            <span class="text-dark fw-semibold"><?php echo $isVietQR ? 'Cổng thanh toán VietQR' : 'Đặt hàng thành công'; ?></span>
        </nav>
    </div>
</section>

<div class="container mb-5">
    <?php if ($isVietQR): ?>
    <!-- ========================================================================= -->
    <!-- GIAO DIỆN CỔNG THANH TOÁN TỰ ĐỘNG VIETQR (REALTIME POLLING & WEBHOOK) -->
    <!-- ========================================================================= -->
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="card border rounded-3 vietqr-card overflow-hidden">
                <!-- Header Card -->
                <div class="card-header bg-white border-bottom p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-danger text-uppercase px-2 py-1" style="background-color: #B91C1C !important;">VietQR 24/7</span>
                            <span class="badge bg-light text-dark border">Tự động duyệt 1 - 3s</span>
                        </div>
                        <h1 class="h4 fw-bold text-dark mb-0">THANH TOÁN ĐƠN HÀNG #XAG-<?php echo $donhang_id; ?></h1>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Tổng thanh toán:</small>
                        <span class="fs-4 fw-bolder text-danger" id="order-amount-display"><?php echo number_format($tongtien); ?> đ</span>
                    </div>
                </div>

                <div class="card-body p-4 p-md-5">
                    
                    <!-- BOX TRẠNG THÁI: ĐÃ THANH TOÁN THÀNH CÔNG (ẨN/HIỆN QUA JS HOẶC PHP) -->
                    <div id="payment-success-box" class="<?php echo ($trangthaithanhtoan === 1) ? '' : 'd-none'; ?> text-center py-4">
                        <div class="success-checkmark mb-3">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        </div>
                        <h2 class="h3 fw-bold text-dark text-uppercase mb-2">GIAO DỊCH ĐÃ ĐƯỢC XÁC NHẬN THÀNH CÔNG!</h2>
                        <p class="text-muted mb-4 lead fs-6">
                            Hệ thống <strong>LPT DRONE</strong> đã ghi nhận số tiền thanh toán <strong><?php echo number_format($tongtien); ?> đ</strong> từ tài khoản của bạn.
                        </p>

                        <div class="alert alert-success border-success-subtle bg-success-subtle text-success-emphasis p-3 rounded-3 text-start mx-auto mb-4" style="max-width: 600px;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-shield-fill-check fs-4 me-2"></i>
                                <strong class="fs-6">Đơn hàng #XAG-<?php echo $donhang_id; ?> đã được tự động duyệt</strong>
                            </div>
                            <div class="small">
                                Bộ phận Kỹ thuật & Kho linh kiện đang chuẩn bị kiểm tra thông số và đóng gói cẩn thận để gửi cho bên vận chuyển / chành xe sớm nhất.
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-3">
                            <a href="index.php?action=thongtin&dhid=<?php echo $donhang_id; ?>" class="btn btn-danger px-4 py-2 fw-semibold" style="background-color: #B91C1C; border-color: #B91C1C;">
                                <i class="bi bi-box-seam me-1"></i> Quản lý đơn hàng
                            </a>
                            <a href="index.php" class="btn btn-outline-dark px-4 py-2">
                                <i class="bi bi-house-door me-1"></i> Trang chủ LPT DRONE
                            </a>
                        </div>
                    </div>

                    <!-- BOX THANH TOÁN: MÃ QR & REALTIME POLLING (HIỆN KHI CHƯA THANH TOÁN) -->
                    <div id="payment-pending-box" class="<?php echo ($trangthaithanhtoan === 1) ? 'd-none' : ''; ?>">
                        
                        <!-- Thanh trạng thái Realtime Polling -->
                        <div class="alert alert-light border border-danger-subtle d-flex align-items-center justify-content-between p-3 rounded-3 mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <span class="live-pulse-dot"></span>
                                <span class="fw-bold text-dark small" id="polling-status-text">
                                    Đang chờ hệ thống ngân hàng ghi nhận tiền vào tài khoản...
                                </span>
                            </div>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle small font-monospace" id="polling-timer">
                                <i class="bi bi-arrow-repeat spin-icon me-1"></i>Auto-sync 2.5s
                            </span>
                        </div>

                        <div class="row g-4 align-items-center">
                            <!-- Cột trái: Mã QR Code chuẩn VietQR -->
                            <div class="col-md-5 text-center">
                                <div class="qr-frame d-inline-block shadow-sm">
                                    <div class="d-flex align-items-center justify-content-between px-2 mb-2">
                                        <span class="badge bg-danger text-white small" style="background-color: #B91C1C !important;">VietQR</span>
                                        <span class="small fw-semibold text-muted">MB BANK - VIETTEL MONEY</span>
                                    </div>
                                    <img src="<?php echo $vietqr_url; ?>" alt="Mã VietQR thanh toán LPT DRONE" class="img-fluid rounded" style="max-height: 280px; width: auto;" id="vietqr-img">
                                    <div class="mt-2 text-muted small" style="font-size: 11px;">
                                        <i class="bi bi-camera me-1"></i>Mở App Ngân hàng bất kỳ để quét mã
                                    </div>
                                </div>

                                <div class="mt-3 d-flex justify-content-center gap-2">
                                    <a href="<?php echo $vietqr_url; ?>" download="VietQR_LPTDH<?php echo $donhang_id; ?>.png" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-download me-1"></i> Tải ảnh QR
                                    </a>
                                    <a href="<?php echo $vietqr_url; ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrows-fullscreen me-1"></i> Phóng to
                                    </a>
                                </div>
                            </div>

                            <!-- Cột phải: Thông tin chuyển khoản & Nút Copy -->
                            <div class="col-md-7">
                                <div class="bg-light p-3 p-md-4 rounded-3 border">
                                    <h6 class="fw-bold text-dark small text-uppercase mb-3 d-flex align-items-center">
                                        <i class="bi bi-bank2 text-danger me-2"></i>Thông tin chuyển khoản thủ công:
                                    </h6>

                                    <table class="table table-borderless table-sm mb-3" style="font-size: 13px;">
                                        <tbody>
                                            <tr class="border-bottom">
                                                <td class="text-secondary py-2" style="width: 120px;">Ngân hàng:</td>
                                                <td class="fw-bold text-dark py-2"><?php echo VIETQR_BANK_NAME; ?></td>
                                            </tr>
                                            <tr class="border-bottom">
                                                <td class="text-secondary py-2">Số tài khoản:</td>
                                                <td class="py-2">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <strong class="text-danger font-monospace fs-5"><?php echo trim(chunk_split(VIETQR_ACCOUNT_NO, 4, ' ')); ?></strong>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2 copy-btn" onclick="copyData('<?php echo VIETQR_ACCOUNT_NO; ?>', this)">
                                                            <i class="bi bi-copy"></i> Sao chép
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="border-bottom">
                                                <td class="text-secondary py-2">Chủ tài khoản:</td>
                                                <td class="fw-bold text-dark py-2"><?php echo VIETQR_ACCOUNT_NAME; ?></td>
                                            </tr>
                                            <tr class="border-bottom">
                                                <td class="text-secondary py-2">Số tiền:</td>
                                                <td class="py-2">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <strong class="text-danger fs-5"><?php echo number_format($tongtien); ?> đ</strong>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2 copy-btn" onclick="copyData('<?php echo (int)$tongtien; ?>', this)">
                                                            <i class="bi bi-copy"></i> Sao chép
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-secondary py-2">Nội dung CK:</td>
                                                <td class="py-2">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="font-monospace fw-bold text-dark bg-white border border-danger px-2 py-1 rounded fs-6">
                                                            <?php echo $transferContent; ?>
                                                        </span>
                                                        <button type="button" class="btn btn-danger btn-sm py-1 px-2 copy-btn" style="background-color: #B91C1C; border-color: #B91C1C;" onclick="copyData('<?php echo $transferContent; ?>', this)">
                                                            <i class="bi bi-copy"></i> Sao chép
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <div class="alert alert-warning border-warning-subtle py-2 px-3 mb-0 small text-dark d-flex align-items-start">
                                        <i class="bi bi-exclamation-triangle-fill text-warning me-2 mt-1"></i>
                                        <div>
                                            <strong>LƯU Ý QUAN TRỌNG:</strong> Quý khách vui lòng giữ nguyên nội dung <code><?php echo $transferContent; ?></code> để hệ thống tự động nhận diện và xác nhận đơn ngay lập tức.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer phụ trợ -->
                        <div class="mt-4 pt-3 border-top d-flex flex-wrap align-items-center justify-content-between gap-3 text-muted small">
                            <div>
                                <i class="bi bi-headset me-1 text-danger"></i> Cần trợ giúp gấp? Hotline / Zalo Kỹ thuật: <strong>0833 609 329</strong>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="index.php?action=thongtin" class="text-secondary text-decoration-none">
                                    <i class="bi bi-arrow-left me-1"></i> Lịch sử đơn hàng
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT REALTIME POLLING TỰ ĐỘNG BẰNG AJAX -->
    <script>
    (function() {
        const orderId = <?php echo $donhang_id; ?>;
        let isPaid = <?php echo ($trangthaithanhtoan === 1) ? 'true' : 'false'; ?>;
        let pollingInterval = null;
        let retryCount = 0;
        const maxRetries = 240; // Polling trong tối đa 10 phút (240 * 2.5s)

        // Âm thanh thông báo giao dịch thành công (Synthesizer Web Audio API chuẩn)
        function playSuccessSound() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const now = audioCtx.currentTime;
                
                // Nốt thứ nhất: 587.33 Hz (D5)
                const osc1 = audioCtx.createOscillator();
                const gain1 = audioCtx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(587.33, now);
                gain1.gain.setValueAtTime(0.2, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.2);
                osc1.connect(gain1);
                gain1.connect(audioCtx.destination);
                osc1.start(now);
                osc1.stop(now + 0.2);

                // Nốt thứ hai: 880 Hz (A5)
                const osc2 = audioCtx.createOscillator();
                const gain2 = audioCtx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(880, now + 0.15);
                gain2.gain.setValueAtTime(0.3, now + 0.15);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
                osc2.connect(gain2);
                gain2.connect(audioCtx.destination);
                osc2.start(now + 0.15);
                osc2.stop(now + 0.5);
            } catch (e) {
                // Trình duyệt chặn autoplay audio, bỏ qua
            }
        }

        function triggerSuccessUI() {
            isPaid = true;
            if (pollingInterval) clearInterval(pollingInterval);

            // Chạy âm thanh
            playSuccessSound();

            // Chuyển giao diện mượt mà
            const pendingBox = document.getElementById('payment-pending-box');
            const successBox = document.getElementById('payment-success-box');
            if (pendingBox && successBox) {
                pendingBox.classList.add('d-none');
                successBox.classList.remove('d-none');
            }
        }

        function checkPaymentStatus() {
            if (isPaid) return;
            retryCount++;

            // Gọi API kiểm tra trạng thái thanh toán
            fetch('api/check_payment_status.php?order_id=' + orderId, {
                method: 'GET',
                cache: 'no-store'
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success && data.paid) {
                    triggerSuccessUI();
                }
            })
            .catch(err => {
                console.warn('Lỗi kết nối kiểm tra thanh toán:', err);
            });

            if (retryCount >= maxRetries) {
                clearInterval(pollingInterval);
                const statusEl = document.getElementById('polling-status-text');
                if (statusEl) {
                    statusEl.innerText = "Phiên kiểm tra tự động đã tạm dừng. Bạn có thể bấm F5 để cập nhật lại trạng thái.";
                }
            }
        }

        if (!isPaid && orderId > 0) {
            // Polling mỗi 2.5 giây
            pollingInterval = setInterval(checkPaymentStatus, 2500);
        }
    })();

    // Hàm sao chép thông tin nhanh vào bộ nhớ tạm
    function copyData(text, btn) {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => showCopiedBadge(btn)).catch(() => fallbackCopyText(text, btn));
        } else {
            fallbackCopyText(text, btn);
        }
    }

    function fallbackCopyText(text, btn) {
        const temp = document.createElement("input");
        temp.value = text;
        document.body.appendChild(temp);
        temp.select();
        try {
            document.execCommand("copy");
            showCopiedBadge(btn);
        } catch (e) {
            alert("Đã sao chép: " + text);
        }
        document.body.removeChild(temp);
    }

    function showCopiedBadge(btn) {
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2"></i> Đã chép';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-secondary', 'btn-danger');
        setTimeout(() => {
            btn.innerHTML = oldHtml;
            btn.classList.remove('btn-success');
            if (btn.classList.contains('copy-btn')) {
                // khôi phục trạng thái ban đầu
            }
        }, 2000);
    }
    </script>

    <?php else: ?>
    <!-- ========================================================================= -->
    <!-- GIAO DIỆN XÁC NHẬN ĐƠN HÀNG COD THÔNG THƯỜNG -->
    <!-- ========================================================================= -->
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="bg-white p-4 p-md-5 rounded-3 border text-center shadow-sm">
                <i class="bi bi-check-circle-fill text-success display-3 mb-3 d-block"></i>
                
                <h1 class="h4 fw-bold text-dark text-uppercase mb-2">ĐẶT HÀNG THÀNH CÔNG!</h1>
                <p class="text-muted small">Cảm ơn quý khách <strong><?php echo htmlspecialchars($hoten); ?></strong> đã tin tưởng lựa chọn phụ tùng tại <strong>LPT DRONE</strong>.</p>
                
                <div class="p-3 bg-light rounded my-4 text-start small border">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Mã đơn hàng:</span>
                        <strong class="text-danger">#XAG-<?php echo $donhang_id; ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Tổng thanh toán:</span>
                        <strong class="text-dark fs-6"><?php echo number_format($tongtien); ?> đ</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-secondary">Phương thức:</span>
                        <span class="badge bg-light text-dark border">COD - Thanh toán khi nhận hàng</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-secondary">Trạng thái:</span>
                        <span class="badge bg-warning text-dark">Chờ xuất kho</span>
                    </div>
                </div>

                <p class="small text-secondary mb-4">
                    Kỹ thuật viên LPT DRONE sẽ liên hệ qua điện thoại để xác nhận đơn hàng và điều phối xe khách / chành xe gần nhất gửi đến quý khách.
                </p>

                <div class="d-flex justify-content-center gap-2">
                    <a href="index.php" class="btn btn-outline-dark btn-sm px-3">
                        <i class="bi bi-arrow-left me-1"></i> Trang chủ
                    </a>
                    <a href="index.php?action=thongtin" class="btn btn-danger btn-sm px-3" style="background-color: #B91C1C; border-color: #B91C1C;">
                        <i class="bi bi-box-seam me-1"></i> Quản lý đơn hàng
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include("inc/bottom.php"); ?>
