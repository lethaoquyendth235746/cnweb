<?php
/**
 * CÔNG CỤ MÔ PHỎNG THANH TOÁN TEST WEBHOOK & REALTIME POLLING
 * Đường dẫn: api/simulate_payment.php
 * Hỗ trợ Chủ cửa hàng / Lập trình viên kiểm tra luồng Webhook và Polling mà không cần tài khoản ngân hàng thật
 */

require_once __DIR__ . '/../config/payment_config.php';
require_once __DIR__ . '/../model/database.php';

$msg = '';
$msgType = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $customAmount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;
    
    if ($orderId > 0) {
        $db = DATABASE::connect();
        $stmt = $db->prepare("SELECT id, tongtien, trangthaithanhtoan FROM donhang WHERE id = :id");
        $stmt->bindValue(':id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            $msg = "Đơn hàng #{$orderId} không tồn tại!";
            $msgType = "danger";
        } else {
            $amountToSend = ($customAmount > 0) ? $customAmount : (float)$order['tongtien'];
            
            // Giả lập Payload chuẩn SePay
            $webhookUrl = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/weblinhkiendrone/api/webhook_payment.php';
            $payload = [
                'id'              => rand(100000, 999999),
                'gateway'         => 'MBBank',
                'transactionDate' => date('Y-m-d H:i:s'),
                'accountNumber'   => VIETQR_ACCOUNT_NO,
                'content'         => PAYMENT_PREFIX . $orderId . ' TEST SIMULATION',
                'transferType'    => 'in',
                'description'     => PAYMENT_PREFIX . $orderId . ' TEST SIMULATION',
                'transferAmount'  => $amountToSend,
                'referenceCode'   => 'MB' . strtoupper(bin2hex(random_bytes(4))),
                'api_key'         => WEBHOOK_API_KEY
            ];

            // Gửi cURL nội bộ tới webhook_payment.php
            $ch = curl_init($webhookUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Apikey ' . WEBHOOK_API_KEY
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = json_decode($response, true);
            if ($httpCode === 200 && ($result['success'] ?? false)) {
                $msg = "Mô phỏng thanh toán thành công cho đơn #{$orderId}! Webhook đã cập nhật cơ sở dữ liệu. Trang xác nhận đơn sẽ tự động chuyển trạng thái ngay lập tức.";
                $msgType = "success";
            } else {
                $msg = "Phản hồi từ Webhook ({$httpCode}): " . ($response ?: 'Không có phản hồi');
                $msgType = "warning";
            }
        }
    } else {
        $msg = "Vui lòng nhập mã đơn hàng hợp lệ!";
        $msgType = "danger";
    }
}

// Lấy danh sách 5 đơn hàng mới nhất chưa thanh toán để dễ test
$db = DATABASE::connect();
$stmtRecent = $db->query("SELECT id, ngay, tongtien, phuongthucthanhtoan, trangthai, trangthaithanhtoan FROM donhang ORDER BY id DESC LIMIT 6");
$recentOrders = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Công cụ Mô phỏng Thanh toán VietQR & Webhook - LPT DRONE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; }
        .theme-card { border-top: 4px solid #B91C1C; }
        .text-brand { color: #B91C1C !important; }
        .btn-brand { background-color: #B91C1C; color: #fff; }
        .btn-brand:hover { background-color: #991b1b; color: #fff; }
    </style>
</head>
<body class="py-5">
<div class="container" style="max-width: 800px;">
    <div class="card shadow-sm border theme-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
                <div>
                    <h3 class="h5 fw-bold text-dark mb-1">
                        <i class="bi bi-cpu text-brand me-2"></i>Mô phỏng Thanh toán VietQR & Webhook
                    </h3>
                    <p class="text-muted small mb-0">Hỗ trợ test tính năng tự động ghi nhận tiền và Realtime Polling</p>
                </div>
                <a href="../public/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Trang chủ LPT DRONE
                </a>
            </div>

            <?php if(!empty($msg)): ?>
            <div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle me-2"></i><?php echo htmlspecialchars($msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <form method="post" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Mã đơn hàng (Order ID):</label>
                        <input type="number" name="order_id" class="form-control font-monospace" placeholder="VD: 15" required value="<?php echo isset($_GET['order_id']) ? (int)$_GET['order_id'] : ''; ?>">
                        <small class="text-muted">Hệ thống sẽ tạo nội dung chuyển tiền <code><?php echo PAYMENT_PREFIX; ?>[ID]</code></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Số tiền (để trống sẽ lấy đúng tổng tiền đơn):</label>
                        <input type="number" name="amount" class="form-control" placeholder="Tùy chọn">
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-brand fw-bold px-4">
                        <i class="bi bi-send-check me-2"></i>Bắn Webhook Báo Có Tiền
                    </button>
                    <a href="webhook_payment.php" target="_blank" class="btn btn-outline-secondary">
                        <i class="bi bi-code-slash me-1"></i> Kiểm tra Endpoint Webhook
                    </a>
                </div>
            </form>

            <h6 class="fw-bold text-dark small text-uppercase border-bottom pb-2 mb-3">
                <i class="bi bi-clock-history me-1"></i> Danh sách đơn hàng gần đây:
            </h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle text-center mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th>Tổng tiền</th>
                            <th>Phương thức</th>
                            <th>TT Thanh toán</th>
                            <th>Thao tác nhanh</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentOrders as $ro): ?>
                        <tr>
                            <td class="fw-bold font-monospace">#<?php echo $ro['id']; ?></td>
                            <td class="text-muted"><?php echo date('H:i d/m', strtotime($ro['ngay'])); ?></td>
                            <td class="fw-bold text-end pe-3"><?php echo number_format($ro['tongtien']); ?> đ</td>
                            <td><span class="badge bg-light text-dark border"><?php echo $ro['phuongthucthanhtoan']; ?></span></td>
                            <td>
                                <?php if($ro['trangthaithanhtoan'] == 1): ?>
                                    <span class="badge bg-success"><i class="bi bi-check2"></i> Đã thanh toán</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> Chưa thanh toán</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" class="d-inline m-0">
                                    <input type="hidden" name="order_id" value="<?php echo $ro['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 11px;">
                                        Mô phỏng Báo Có
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4 p-3 bg-light rounded border small">
                <div class="fw-bold text-dark mb-1"><i class="bi bi-gear-wide-connected text-brand me-1"></i> Thông số kết nối SePay / Casso thực tế:</div>
                <ul class="mb-0 ps-3 text-secondary">
                    <li>URL Webhook: <code>http://domain-cua-ban.com/api/webhook_payment.php</code></li>
                    <li>API Key xác thực: <code><?php echo WEBHOOK_API_KEY; ?></code></li>
                    <li>Cú pháp nhận diện: Tiền tố <code><?php echo PAYMENT_PREFIX; ?></code> + Mã đơn hàng (Ví dụ: <code><?php echo PAYMENT_PREFIX; ?>15</code>)</li>
                </ul>
            </div>
        </div>
    </div>
</div>
</body>
</html>
