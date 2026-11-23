<?php
/**
 * CỔNG TIẾP NHẬN WEBHOOK TỰ ĐỘNG XÁC NHẬN THANH TOÁN (SEPAY / CASSO / CUSTOM)
 * Đường dẫn: api/webhook_payment.php
 * Tương thích chuẩn API Webhook SePay & Casso
 */

header('Content-Type: application/json; charset=utf-8');

// Nạp cấu hình thanh toán & cơ sở dữ liệu
require_once __DIR__ . '/../config/payment_config.php';
require_once __DIR__ . '/../model/database.php';

// Hàm ghi log webhook phục vụ kiểm tra và đối soát
function writeWebhookLog($data, $status = 'INFO') {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/payment_webhook.log';
    $timestamp = date('Y-m-d H:i:s');
    $content = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
    @file_put_contents($logFile, "[{$timestamp}] [{$status}] {$content}\n", FILE_APPEND);
}

// 1. XÁC THỰC BẢO MẬT WEBHOOK API KEY
function verifyWebhookApiKey() {
    $apiKeyHeader = '';

    // Lấy token từ HTTP Headers
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $apiKeyHeader = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $apiKeyHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (!empty($_SERVER['HTTP_SECURE_TOKEN'])) { // Chuẩn Casso
        $apiKeyHeader = $_SERVER['HTTP_SECURE_TOKEN'];
    } elseif (!empty($_SERVER['HTTP_X_API_KEY'])) {
        $apiKeyHeader = $_SERVER['HTTP_X_API_KEY'];
    }

    // Làm sạch Authorization (Apikey ..., Bearer ...)
    $cleanedToken = trim(preg_replace('/^(Apikey|Bearer)\s+/i', '', $apiKeyHeader));

    // Kiểm tra token từ header hoặc param GET/POST fallback
    $paramToken = $_GET['api_key'] ?? $_POST['api_key'] ?? '';

    if ($cleanedToken === WEBHOOK_API_KEY || $paramToken === WEBHOOK_API_KEY) {
        return true;
    }

    return false;
}

// 2. ĐỌC DỮ LIỆU GỬI ĐẾN (RAW JSON HOẶC POST)
$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput, true);

if (!$payload && !empty($_POST)) {
    $payload = $_POST;
}

// Kiểm tra bảo mật
if (!verifyWebhookApiKey()) {
    // Nếu payload có chứa secret/apiKey trực tiếp
    $payloadKey = $payload['api_key'] ?? $payload['secret'] ?? $payload['token'] ?? '';
    if ($payloadKey !== WEBHOOK_API_KEY) {
        http_response_code(401);
        $res = ['success' => false, 'message' => 'Unauthorized: Mã bí mật Webhook không chính xác'];
        writeWebhookLog(['error' => 'Unauthorized', 'ip' => $_SERVER['REMOTE_ADDR'] ?? '', 'headers' => function_exists('getallheaders') ? getallheaders() : []], 'ERROR');
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (empty($payload)) {
    http_response_code(400);
    $res = ['success' => false, 'message' => 'Dữ liệu webhook rỗng'];
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
    exit;
}

writeWebhookLog($payload, 'RECEIVED');

// 3. CHUẨN HÓA DỮ LIỆU TỪ CÁC ĐƠN VỊ CUNG CẤP (SEPAY / CASSO / DIRECT)
$transactions = [];

// Trường hợp Casso: dữ liệu nằm trong $payload['data']
if (isset($payload['data']) && is_array($payload['data'])) {
    foreach ($payload['data'] as $item) {
        $transactions[] = [
            'content'        => $item['description'] ?? '',
            'amount'         => (float)($item['amount'] ?? 0),
            'reference'      => $item['tid'] ?? ($item['id'] ?? ''),
            'gateway'        => $item['bank_sub_acc_id'] ?? 'CASSO',
            'transactionDate'=> $item['when'] ?? date('Y-m-d H:i:s')
        ];
    }
} else {
    // Trường hợp SePay hoặc Direct Webhook
    $transactions[] = [
        'content'        => $payload['content'] ?? $payload['description'] ?? '',
        'amount'         => (float)($payload['transferAmount'] ?? $payload['amount'] ?? 0),
        'reference'      => $payload['referenceCode'] ?? ($payload['id'] ?? ''),
        'gateway'        => $payload['gateway'] ?? 'SEPAY',
        'transactionDate'=> $payload['transactionDate'] ?? date('Y-m-d H:i:s'),
        'order_id'       => $payload['order_id'] ?? null
    ];
}

$processedOrders = [];
$db = DATABASE::connect();

foreach ($transactions as $tx) {
    $content = $tx['content'];
    $amount = (float)$tx['amount'];
    $reference = $tx['reference'];
    $orderId = null;

    // Trích xuất mã đơn hàng từ nội dung chuyển khoản
    // Mẫu chuẩn: LPTDH{orderId} hoặc PAYMENT_PREFIX{orderId}
    $prefixPattern = '/' . preg_quote(PAYMENT_PREFIX, '/') . '\s*([0-9]+)/i';
    if (preg_match($prefixPattern, $content, $matches)) {
        $orderId = (int)$matches[1];
    } elseif (!empty($tx['order_id'])) {
        $orderId = (int)$tx['order_id'];
    } elseif (preg_match('/XAG-([0-9]+)/i', $content, $matches)) {
        $orderId = (int)$matches[1];
    } elseif (preg_match('/DH\s*([0-9]+)/i', $content, $matches)) {
        $orderId = (int)$matches[1];
    }

    if (!$orderId) {
        writeWebhookLog(['notice' => 'Không tìm thấy mã đơn hàng hợp lệ trong nội dung', 'content' => $content], 'SKIPPED');
        continue;
    }

    try {
        // Khóa đơn hàng để xử lý an toàn
        $db->beginTransaction();

        $stmt = $db->prepare("SELECT id, tongtien, trangthai, trangthaithanhtoan, ghichu FROM donhang WHERE id = :id FOR UPDATE");
        $stmt->bindValue(':id', $orderId, PDO::PARAM_INT);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            $db->rollBack();
            writeWebhookLog(['error' => 'Đơn hàng không tồn tại', 'order_id' => $orderId], 'ERROR');
            continue;
        }

        // Kiểm tra xem đơn đã được thanh toán chưa
        if ((int)$order['trangthaithanhtoan'] === 1) {
            $db->rollBack();
            $processedOrders[] = [
                'order_id' => $orderId,
                'status'   => 'already_paid',
                'message'  => 'Đơn hàng đã được thanh toán trước đó'
            ];
            continue;
        }

        // Kiểm tra số tiền chuyển có đủ so với tổng giá trị đơn hàng
        $orderTotal = (float)$order['tongtien'];
        if ($amount < $orderTotal) {
            // Trường hợp khách chuyển thiếu
            $timeTag = date('H:i d/m/Y');
            $partialMsg = "[{$timeTag} - CẢNH BÁO WEBHOOK]: Khách chuyển thiếu tiền! Số tiền nhận: " . number_format($amount) . " đ / Tổng đơn: " . number_format($orderTotal) . " đ. Ref: {$reference}";
            $newNote = !empty($order['ghichu']) ? $order['ghichu'] . "\n" . $partialMsg : $partialMsg;

            $stmtNote = $db->prepare("UPDATE donhang SET ghichu = :ghichu WHERE id = :id");
            $stmtNote->bindValue(':ghichu', $newNote);
            $stmtNote->bindValue(':id', $orderId, PDO::PARAM_INT);
            $stmtNote->execute();

            $db->commit();
            writeWebhookLog(['warning' => 'Khách chuyển thiếu tiền', 'order_id' => $orderId, 'amount_received' => $amount, 'order_total' => $orderTotal], 'WARN');
            continue;
        }

        // CẬP NHẬT TRẠNG THÁI THANH TOÁN THÀNH CÔNG:
        // - trangthaithanhtoan = 1 (Đã thanh toán)
        // - Tự động nâng trangthai = 1 (Đã xác nhận & Chờ đóng gói) nếu đang là 0 (Chờ xác nhận)
        $currentStatus = (int)$order['trangthai'];
        $newStatus = ($currentStatus === 0) ? 1 : $currentStatus;

        $timeTag = date('H:i d/m/Y');
        $auditMsg = "[{$timeTag} - HỆ THỐNG VIETQR]: Xác nhận thanh toán tự động qua Webhook thành công. Số tiền: " . number_format($amount) . " đ. Mã GD: {$reference}. Nội dung: \"{$content}\".";
        $finalNote = !empty($order['ghichu']) ? $order['ghichu'] . "\n" . $auditMsg : $auditMsg;

        $stmtUpdate = $db->prepare("UPDATE donhang 
                                     SET trangthaithanhtoan = 1, 
                                         trangthai = :trangthai, 
                                         ghichu = :ghichu 
                                     WHERE id = :id");
        $stmtUpdate->bindValue(':trangthai', $newStatus, PDO::PARAM_INT);
        $stmtUpdate->bindValue(':ghichu', $finalNote, PDO::PARAM_STR);
        $stmtUpdate->bindValue(':id', $orderId, PDO::PARAM_INT);
        $stmtUpdate->execute();

        $db->commit();

        writeWebhookLog([
            'success'   => true,
            'order_id'  => $orderId,
            'amount'    => $amount,
            'reference' => $reference
        ], 'SUCCESS');

        $processedOrders[] = [
            'order_id' => $orderId,
            'status'   => 'success',
            'message'  => 'Xác nhận thanh toán tự động thành công'
        ];

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        writeWebhookLog(['exception' => $e->getMessage(), 'order_id' => $orderId], 'ERROR');
    }
}

// Trả về JSON chuẩn 200 OK
http_response_code(200);
echo json_encode([
    'success'          => true,
    'message'          => 'Webhook đã được tiếp nhận và xử lý thành công',
    'processed_orders' => $processedOrders
], JSON_UNESCAPED_UNICODE);
exit;
