<?php 
/**
 * admin/ktnguoidung/index.php
 * Controller Đăng nhập & Xác thực Quản trị - Tương thích ngược Giai đoạn 3
 */

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require("../../model/database.php");
require("../../model/nguoidung.php");

// Biến $isLogin cho biết người dùng đăng nhập chưa
$isLogin = isset($_SESSION["nguoidung"]);

// Xét xem có thao tác nào được chọn
if (isset($_REQUEST["action"])) {
    $action = $_REQUEST["action"];
} elseif ($isLogin == FALSE) {  // chưa đăng nhập
    $action = "dangnhap";
} else {   // mặc định
    $action = "macdinh";
}

$nd = new NGUOIDUNG();

switch ($action) {
    case "macdinh":               
        // Chuyển hướng sang Bảng điều khiển hiện đại
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
            $_SESSION["nguoidung"] = $nd->laythongtinnguoidung($email); // đặt biến session
            // Đăng nhập thành công -> chuyển thẳng tới Dashboard mới
            header("Location: ../index.php");
            exit;
        } else {
            include("login.php");
        }
        break;
    case "dangxuat":
        unset($_SESSION["nguoidung"]);  // hủy biến session
        header("Location: ../../public/index.php"); // chuyển hướng ra trang chủ khách
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
