<?php 
/**
 * admin/ktnguoidung/index.php
 * Controller Đ�fng nhập & Xác thực Quản tr�< - Tương thích ngược Giai �'oạn 3
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require("../../model/database.php");
require("../../model/nguoidung.php");

// Biến $isLogin cho biết người dùng �'�fng nhập chưa
$isLogin = isset($_SESSION["nguoidung"]);

// Xét xem có thao tác nào �'ược chọn
if (isset($_REQUEST["action"])) {
    $action = $_REQUEST["action"];
} elseif ($isLogin == FALSE) {  // chưa �'�fng nhập
    $action = "dangnhap";
} else {   // mặc �'�<nh
    $action = "macdinh";
}

$nd = new NGUOIDUNG();

switch ($action) {
    case "macdinh":               
        // Chuy�fn hư�>ng sang Bảng �'iều khi�fn hi�?n �'ại
        header("Location: ../index.php");
        exit;
        break;
    case "dangnhap":
        include("login.php");
        break;
    case "xldangnhap":
        $email = $_REQUEST["txtemail"];
        $matkhau = $_REQUEST["txtmatkhau"];
        if ($nd->kiemtranguoidunghople($email, $matkhau) == TRUE) {
            $_SESSION["nguoidung"] = $nd->laythongtinnguoidung($email); // �'ặt biến session
            // Đ�fng nhập thành công -> chuy�fn thẳng t�>i Dashboard m�>i
            header("Location: ../index.php");
            exit;
        } else {
            include("login.php");
        }
        break;
    case "dangxuat":
        unset($_SESSION["nguoidung"]);  // hủy biến session
        header("Location: ../../public/index.php"); // chuy�fn hư�>ng ra trang chủ khách
        exit;
        break;  
    case "hoso":               
        header("Location: ../profile.php");
        exit;
        break; 
    case "xlhoso":
        header("Location: ../profile.php");
        exit;
        break;
    case "doimatkhau":
        header("Location: ../profile.php");
        exit;
        break;
    default:
        header("Location: ../index.php");
        exit;
        break;
}
?>

