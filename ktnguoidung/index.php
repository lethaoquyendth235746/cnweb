<?php
/**
 * ktnguoidung/index.php (Fallback Redirector)
 * Tự động chuyển hướng các truy cập cũ tới admin/ktnguoidung/index.php
 */
$queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
header("Location: ../admin/ktnguoidung/index.php{$queryString}");
exit;
