<?php 
/**
 * admin/ktnguoidung/index.php
 * Controller Đăng nhập & Xác thực Quản trị viên & Nhân viên (loai = 1, 2)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../model/database.php";
require_once __DIR__ . "/../../model/nguoidung.php";
require_once __DIR__ . "/../includes/auth_middleware.php";

$action = $_REQUEST["action"] ?? (isAuthenticated() ? "macdinh" : "dangnhap");

$nd = new NGUOIDUNG();

switch ($action) {
    case "macdinh":               
        header("Location: ../index.php");
        exit;
        break;

    case "dangnhap":
        if (isAuthenticated()) {
            header("Location: ../index.php");
            exit;
        }
        header("Location: ../../public/index.php?action=dangnhap");
        exit;
        break;

    case "xldangnhap":
        $email = trim($_POST["txtemail"] ?? "");
        $matkhau = trim($_POST["txtmatkhau"] ?? "");

        // Chỉ cho phép loai = 1 (SuperAdmin) và loai = 2 (Nhân viên / Kỹ thuật viên)
        $auth = $nd->authenticate($email, $matkhau, [1, 2]);

        if ($auth['success']) {
            session_regenerate_id(true);
            $_SESSION['admin'] = $auth['user'];
            $_SESSION['nguoidung'] = $auth['user'];

            // Điều hướng thẳng vào Dashboard quản trị
            header("Location: ../index.php");
            exit;
        } else {
            $_SESSION['tb_loi_dn'] = $auth['message'];
            header("Location: ../../public/index.php?action=dangnhap");
            exit;
        }
        break;

    case "dangxuat":
        // Xóa sạch session quản trị
        unset($_SESSION["admin"], $_SESSION["nguoidung"]);
        header("Location: ../../public/index.php?action=dangnhap");
        exit;
        break;  

    case "hoso":               
    case "xlhoso":
    case "doimatkhau":
        header("Location: ../profile.php");
        exit;
        break;

    default:
        header("Location: ../index.php");
        exit;
        break;
}
