<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require("../model/database.php");
require("../model/danhmuc.php");
require("../model/dongmay.php");
require("../model/mathang.php");
require("../model/giohang.php");
require("../model/khachhang.php");
require("../model/diachi.php");
require("../model/donhang.php");
require("../model/donhangct.php");

$dm = new DANHMUC();
$danhmuc = $dm->laydanhmuc();

$dmay = new DONGMAY();
$dongmay_list = $dmay->laydongmay();

$mh = new MATHANG();
$mathangxemnhieu = $mh->laymathangxemnhieu();

if(isset($_REQUEST["action"])){
    $action = $_REQUEST["action"];
}
else{
    $action="null"; 
}

switch($action){
    case "null": 	
        // Trang chủ LPT DRONE theo bố cục Giới thiệu + Danh mục tuần phần (Không hiện bộ lọc)
        include("main.php");
        break;

    case "tatcasanpham":
    case "group": 
    case "dongmay": 
    case "search":
        $madm = isset($_REQUEST["id"]) ? (int)$_REQUEST["id"] : null;
        $model = isset($_REQUEST["model"]) ? trim($_REQUEST["model"]) : null;
        $mucgia = isset($_REQUEST["mucgia"]) ? trim($_REQUEST["mucgia"]) : null;
        $sort = isset($_REQUEST["sort"]) ? trim($_REQUEST["sort"]) : null;
        $tukhoa = isset($_REQUEST["tukhoa"]) ? trim($_REQUEST["tukhoa"]) : null;

        if($madm){
            $dmuc = $dm->laydanhmuctheoid($madm);
            $tendm = ($dmuc && isset($dmuc["tendanhmuc"])) ? $dmuc["tendanhmuc"] : "Linh kiện Drone XAG";
        } elseif(!empty($model)) {
            $tendm = "Linh kiện tương thích dòng: XAG " . htmlspecialchars($model);
        } elseif(!empty($tukhoa)) {
            $tendm = "Kết quả tìm kiếm: \"" . htmlspecialchars($tukhoa) . "\"";
        } else {
            $tendm = "Tất cả sản phẩm & Phụ tùng Drone XAG";
        }

        // Xử lý Phân trang (Pagination) 9 sản phẩm / trang
        $limit = 9;
        $trang = isset($_GET["trang"]) ? max(1, (int)$_GET["trang"]) : 1;
        $tongsomathang = $mh->demlocmathang($madm, $model, $mucgia, $tukhoa);
        $tongsotrang = ceil($tongsomathang / $limit);
        if($tongsotrang < 1) $tongsotrang = 1;
        if($trang > $tongsotrang) $trang = $tongsotrang;
        $offset = ($trang - 1) * $limit;

        $mathang = $mh->locmathangphantrang($madm, $model, $mucgia, $sort, $tukhoa, $offset, $limit);
        include("group.php");
        break;

    case "gioithieu":
        include("gioithieu.php");
        break;

    case "lienhe":
        include("lienhe.php");
        break;

    case "detail": 
        if(isset($_GET["id"])){
            $mahang = $_GET["id"];
            // Tăng lượt xem lên 1
            $mh->tangluotxem($mahang);
            // Lấy thông tin chi tiết mặt hàng
            $mhct = $mh->laymathangtheoid($mahang);
            // Lấy các mặt hàng cùng danh mục
            $madm = $mhct["danhmuc_id"];
            $mathang = $mh->laymathangtheodanhmuc($madm);
            include("detail.php");
        }
        break;

    case "chovaogio":    
        $id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : null;
        $soluong = isset($_REQUEST["soluong"]) ? (int)$_REQUEST["soluong"] : 1;
        if($soluong < 1) $soluong = 1;

        if($id){
            if(isset($_SESSION['giohang'][$id])){
                $_SESSION['giohang'][$id] += $soluong;
            }
            else{
                themhangvaogio($id, $soluong);
            }
        }
        
        $giohang = laygiohang();   
        include("cart.php");
        break;

    case "giohang":  
        $giohang = laygiohang();   
        include("cart.php");
        break;

    case "capnhatgio":
        if(isset($_REQUEST["mh"])){
            $mh_post = $_REQUEST["mh"];
            foreach ($mh_post as $id => $soluong) {
                if((int)$soluong > 0)
                    capnhatsoluong($id, (int)$soluong);
                else 
                    xoamotmathang($id);                
            }
        }  
        $giohang = laygiohang();   
        include("cart.php");
        break;

    case "xoagiohang":  
        xoagiohang();
        $giohang = laygiohang();   
        include("cart.php");
        break;

    case "thanhtoan":        
        $giohang = laygiohang();
        if(empty($giohang)){
            header("Location: index.php?action=giohang");
            exit();
        }
        if(isset($_SESSION["khachhang"])){
            $dc = new DIACHI();
            $diachi = $dc->laydiachikhachhang($_SESSION["khachhang"]["id"]);
        }
        include("checkout.php");
        break;

    case "luudonhang": 
        $giohang = laygiohang();
        if(empty($giohang)){
            header("Location: index.php?action=giohang");
            exit();
        }

        $diachi = isset($_POST["txtdiachi"]) ? trim($_POST["txtdiachi"]) : "";
        if(empty($diachi)){
            $tb_loi = "Vui lòng nhập địa chỉ nhận hàng hoặc thông tin nhà xe gửi hàng!";
            include("checkout.php");
            break;
        }

        if(!isset($_SESSION["khachhang"])){ // Khách vãng lai (chưa đăng nhập)
            $email = trim($_POST["txtemail"] ?? "");
            $hoten = trim($_POST["txthoten"] ?? "");
            $sodienthoai = trim($_POST["txtsodienthoai"] ?? "");
            
            if(empty($email) || empty($hoten) || empty($sodienthoai)){
                $tb_loi = "Vui lòng điền đầy đủ họ tên, số điện thoại và email nhận thông báo!";
                include("checkout.php");
                break;
            }

            $kh = new KHACHHANG();
            $existing_kh = $kh->laythongtinkhachhang($email);
            if($existing_kh == null){
                $khachhang_id = $kh->themkhachhang($email, $sodienthoai, $hoten);
                $dc = new DIACHI();
                $diachi_id = $dc->themdiachi($khachhang_id, $diachi);
            }
            else{
                $khachhang_id = $existing_kh["id"];
                $dc = new DIACHI();
                $diachi_rec = $dc->laydiachikhachhang($khachhang_id);
                if($diachi_rec && isset($diachi_rec["id"])){
                    $diachi_id = $diachi_rec["id"];
                } else {
                    $diachi_id = $dc->themdiachi($khachhang_id, $diachi);
                }
            }            
        }
        else{ // Khách đã đăng nhập
            $khachhang_id = $_SESSION["khachhang"]["id"];           
            $hoten = $_SESSION["khachhang"]["hoten"];
            $dc = new DIACHI();
            $diachi_rec = $dc->laydiachikhachhang($khachhang_id);
            if($diachi_rec && isset($diachi_rec["id"])){
                $diachi_id = $diachi_rec["id"];
            } else {
                $diachi_id = $dc->themdiachi($khachhang_id, $diachi);
            }
        }
        
        // Đặt hàng an toàn bằng Transaction & Kiểm tra đủ tồn kho
        $dh = new DONHANG();
        $tongtien = tinhtiengiohang();
        $result = $dh->datHangAnToan($khachhang_id, $diachi_id, $tongtien, $giohang);
        
        if($result['success']){
            $donhang_id = $result['donhang_id'];
            // Xóa giỏ hàng
            xoagiohang();
            // Chuyển đến trang cảm ơn
            include("message.php");
        } else {
            $tb_loi = $result['message'];
            include("checkout.php");
        }
        break;

    case "huydonhang":
        if(isset($_SESSION["khachhang"]) && isset($_POST["donhang_id"])){
            $dh_id = (int)$_POST["donhang_id"];
            $kh_id = (int)$_SESSION["khachhang"]["id"];
            $dh = new DONHANG();
            $res = $dh->huyDonHangKhachHang($dh_id, $kh_id);
            if($res['success']){
                $_SESSION['flash_msg'] = [
                    'type' => 'success', 
                    'text' => $res['message']
                ];
            } else {
                $_SESSION['flash_msg'] = [
                    'type' => 'danger', 
                    'text' => $res['message']
                ];
            }
        }
        header("Location: index.php?action=thongtin");
        exit();
        break;

    case "dangnhap":
        include("loginform.php");
        break;

    case "xldangnhap":
        $email = trim($_POST["txtemail"] ?? "");
        $matkhau = trim($_POST["txtmatkhau"] ?? "");
        $kh = new KHACHHANG();
        if($kh->kiemtrakhachhanghople($email, $matkhau) == TRUE){
            $_SESSION["khachhang"] = $kh->laythongtinkhachhang($email);
            $dh = new DONHANG();
            $donhang = $dh->laydanhsachdonhangtheokh($_SESSION["khachhang"]["id"]);
            include("info.php");
        }
        else{
            include("loginform.php");
        }
        break;

    case "thongtin":
        if(isset($_SESSION["khachhang"])){
            $dh = new DONHANG();
            $donhang = $dh->laydanhsachdonhangtheokh($_SESSION["khachhang"]["id"]);
            
            if(isset($_REQUEST["dhid"])){
                $dhct = new DONHANGCT();
                $donhangct = $dhct->laychitietdonhang($_REQUEST["dhid"]);
            }
        }
        include("info.php");
        break;

    case "dangxuat":
        unset($_SESSION["khachhang"]);
        header("Location: index.php");
        exit();
        break;    

    default:
        include("main.php");
        break;
}
?>