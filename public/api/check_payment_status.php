<?php
/**
 * API REALTIME POLLING KIỂM TRA TRẠNG THÁI THANH TOÁN ĐƠN HÀNG
 * Đường dẫn: public/api/check_payment_status.php
 * Trả về JSON cho Frontend Polling (mỗi 2-3 giây)
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

require_once __DIR__ . '/../../model/database.php';
require_once __DIR__ . '/../../config/payment_config.php';

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : (isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0);

if ($orderId <= 0) {
    echo json_encode([
        'success' => false,
        'paid'    => false,
        'message' => 'Mã đơn hàng không hợp lệ'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = DATABASE::connect();
    $stmt = $db->prepare("SELECT id, tongtien, trangthai, trangthaithanhtoan, phuongthucthanhtoan, ngay FROM donhang WHERE id = :id");
    $stmt->bindValue(':id', $orderId, PDO::PARAM_INT);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode([
            'success' => false,
            'paid'    => false,
            'message' => 'Không tìm thấy đơn hàng #' . $orderId
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $isPaid = ((int)$order['trangthaithanhtoan'] === 1);
    $status = (int)$order['trangthai'];

    echo json_encode([
        'success'             => true,
        'order_id'            => (int)$order['id'],
        'paid'                => $isPaid,
        'trangthaithanhtoan'  => (int)$order['trangthaithanhtoan'],
        'trangthai'           => $status,
        'tongtien'            => (float)$order['tongtien'],
        'phuongthuc'          => $order['phuongthucthanhtoan'],
        'message'             => $isPaid ? 'Đơn hàng đã được thanh toán thành công!' : 'Đang chờ khách hàng chuyển khoản...'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'paid'    => false,
        'message' => 'Lỗi kết nối cơ sở dữ liệu: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
exit;
