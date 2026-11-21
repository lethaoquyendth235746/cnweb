<?php
/**
 * admin/config/db.php
 * Cấu hình CSDL, nạp DatabaseService & Các hàm tiện ích dùng chung toàn bộ Admin XAG Store
 */

require_once __DIR__ . '/../services/DatabaseService.php';

if (!function_exists('formatVND')) {
    /**
     * Định dạng số tiền sang định dạng tiền tệ Việt Nam (VNĐ)
     * Ví dụ: 5500000 -> 5.500.000 đ
     *
     * @param float|int|string|null $amount
     * @return string
     */
    function formatVND($amount): string {
        if ($amount === null || $amount === '') {
            return '0 đ';
        }
        $val = (float)$amount;
        return number_format($val, 0, ',', '.') . ' đ';
    }
}

if (!function_exists('formatDateVN')) {
    /**
     * Định dạng ngày giờ theo chuẩn Việt Nam
     *
     * @param string|null $datetime Chuỗi datetime (ví dụ '2026-11-21 08:30:00')
     * @param bool $withTime Có hiển thị giờ phút không
     * @return string Ví dụ '21/11/2026' hoặc '08:30 21/11/2026'
     */
    function formatDateVN(?string $datetime, bool $withTime = false): string {
        if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
            return '---';
        }
        $timestamp = strtotime($datetime);
        if ($timestamp === false) {
            return htmlspecialchars($datetime);
        }
        return $withTime ? date('H:i d/m/Y', $timestamp) : date('d/m/Y', $timestamp);
    }
}

if (!function_exists('getOrderStatusMeta')) {
    /**
     * Lấy thông tin hiển thị (nhãn, class màu badge, dot) cho trạng thái đơn hàng
     * Khớp chính xác với State Machine 6 trạng thái trong DATABASE_GAP_ANALYSIS.md
     *
     * @param int|string $status Mã trạng thái (0..5)
     * @return array ['label', 'badge_class', 'dot_class', 'description', 'step']
     */
    function getOrderStatusMeta($status): array {
        $st = (int)$status;
        $map = [
            0 => [
                'label'       => 'Chờ xác nhận',
                'badge_class' => 'bg-amber-100 text-amber-800 border-amber-300',
                'dot_class'   => 'bg-amber-500',
                'description' => 'Đơn mới phát sinh, giữ chỗ tồn kho tạm thời. Chờ điện thoại xác minh model Drone.',
                'step'        => 1
            ],
            1 => [
                'label'       => 'Đã xác nhận & Chờ đóng gói',
                'badge_class' => 'bg-blue-100 text-blue-800 border-blue-300',
                'dot_class'   => 'bg-blue-500',
                'description' => 'Kỹ thuật viên kiểm tra seal, tem chống hàng giả XAG, in phiếu xuất kho.',
                'step'        => 2
            ],
            2 => [
                'label'       => 'Đang vận chuyển',
                'badge_class' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                'dot_class'   => 'bg-indigo-500',
                'description' => 'Hàng đã xuất kho vật lý, cập nhật mã vận đơn bưu cục hoặc tên tài xế chành xe.',
                'step'        => 3
            ],
            3 => [
                'label'       => 'Hoàn thành',
                'badge_class' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                'dot_class'   => 'bg-emerald-500',
                'description' => 'Giao hàng và thu tiền COD thành công; ghi nhận chính thức vào báo cáo doanh thu.',
                'step'        => 4
            ],
            4 => [
                'label'       => 'Đã hủy',
                'badge_class' => 'bg-rose-100 text-rose-800 border-rose-300',
                'dot_class'   => 'bg-rose-500',
                'description' => 'Đơn bị hủy; tự động hoàn lại số lượng tồn kho và giảm lượt mua.',
                'step'        => 0
            ],
            5 => [
                'label'       => 'Trả hàng / Hoàn tiền',
                'badge_class' => 'bg-purple-100 text-purple-800 border-purple-300',
                'dot_class'   => 'bg-purple-500',
                'description' => 'Nhận lại linh kiện hoàn trả, nhập lại kho phụ phẩm/hàng lỗi để bảo hành.',
                'step'        => 0
            ]
        ];

        return $map[$st] ?? [
            'label'       => 'Không xác định',
            'badge_class' => 'bg-gray-100 text-gray-800 border-gray-300',
            'dot_class'   => 'bg-gray-400',
            'description' => 'Trạng thái đơn hàng không hợp lệ.',
            'step'        => 0
        ];
    }
}
