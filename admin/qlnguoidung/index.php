<?php
/**
 * Chuyển hướng tương thích ngược từ qlnguoidung sang users.php
 * Hệ thống Quản trị XAG Store - Giai đoạn 3
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header("Location: ../users.php{$queryString}", true, 302);
exit;
