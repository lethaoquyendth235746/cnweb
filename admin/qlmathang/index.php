<?php
/**
 * Chuyển hướng tương thích ngược từ qlmathang sang products.php
 * Hệ thống Quản trị XAG Store - Giai đoạn 3
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Giữ lại tham số tìm kiếm hoặc danh mục nếu có
$queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header("Location: ../products.php{$queryString}", true, 302);
exit;
