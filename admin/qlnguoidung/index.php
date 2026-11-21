<?php
/**
 * Chuy�fn hư�>ng tương thích ngược từ qlnguoidung sang users.php
 * H�? th�'ng Quản tr�< XAG Store - Giai �'oạn 3
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header("Location: ../users.php{$queryString}", true, 302);
exit;

