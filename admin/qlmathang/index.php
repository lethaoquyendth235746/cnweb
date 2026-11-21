<?php
/**
 * Chuy�fn hư�>ng tương thích ngược từ qlmathang sang products.php
 * H�? th�'ng Quản tr�< XAG Store - Giai �'oạn 3
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Giữ lại tham s�' tìm kiếm hoặc danh mục nếu có
$queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header("Location: ../products.php{$queryString}", true, 302);
exit;

