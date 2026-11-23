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
require("../model/nguoidung.php");
require("../model/lienhe.php");
require_once("../config/payment_config.php");

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

    case "guilienhe":
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $hoten = trim($_POST["hoten"] ?? "");
            $sodienthoai = trim($_POST["sodienthoai"] ?? "");
            $dongmay = trim($_POST["dongmay"] ?? "");
            $noidung = trim($_POST["noidung"] ?? "");
            $nguoidung_id = isset($_SESSION["khachhang"]["id"]) ? (int)$_SESSION["khachhang"]["id"] : null;

            if (empty($hoten) || empty($sodienthoai) || empty($noidung)) {
                $_SESSION["lienhe_flash"] = [
                    "type" => "danger",
                    "message" => "Vui lòng nhập đầy đủ Họ tên, Số điện thoại và Nội dung yêu cầu kỹ thuật."
                ];
            } else {
                $lh = new LIENHE();
                $res = $lh->themlienhe($nguoidung_id, $hoten, $sodienthoai, $dongmay, $noidung);
                if ($res) {
                    $_SESSION["lienhe_flash"] = [
                        "type" => "success",
                        "message" => "Cảm ơn bạn! Yêu cầu tư vấn kỹ thuật đã được gửi thành công. Đội ngũ kỹ thuật LPT DRONE sẽ gọi lại hỗ trợ trong ít phút."
                    ];
                } else {
                    $_SESSION["lienhe_flash"] = [
                        "type" => "danger",
                        "message" => "Có lỗi xảy ra trong quá trình lưu thông tin. Quý khách vui lòng gọi trực tiếp hotline 0833 609 329 để được hỗ trợ tức thì."
                    ];
                }
            }
        }
        header("Location: index.php?action=lienhe");
        exit();
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

    case "xoamathang":
        if(isset($_REQUEST["id"])){
            xoamotmathang($_REQUEST["id"]);
        }
        header("Location: index.php?action=giohang");
        exit();
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
        header("Location: index.php?action=giohang");
        exit();
        break;

    case "xoagiohang":  
        xoagiohang();
        unset($_SESSION['checkout_items']);
        $giohang = laygiohang();   
        include("cart.php");
        break;

    case "thanhtoan":
        // Cập nhật số lượng nếu form giỏ hàng gửi kèm
        if(isset($_POST["mh"]) && is_array($_POST["mh"])){
            foreach ($_POST["mh"] as $id => $soluong) {
                if((int)$soluong > 0)
                    capnhatsoluong($id, (int)$soluong);
                else 
                    xoamotmathang($id);                
            }
        }

        $giohang_full = laygiohang();
        if(empty($giohang_full)){
            header("Location: index.php?action=giohang");
            exit();
        }

        // Lấy danh sách sản phẩm được chọn từ POST
        if(isset($_POST['selected_items']) && is_array($_POST['selected_items'])){
            $selected_ids = array_map('intval', $_POST['selected_items']);
            $_SESSION['checkout_items'] = $selected_ids;
        }

        // Lọc giỏ hàng thanh toán theo danh sách chọn
        $giohang = [];
        if(isset($_SESSION['checkout_items']) && !empty($_SESSION['checkout_items'])){
            foreach($_SESSION['checkout_items'] as $id){
                if(isset($giohang_full[$id])){
                    $giohang[$id] = $giohang_full[$id];
                }
            }
        }
        // Nếu không có danh sách lọc hoặc rỗng thì mặc định lấy toàn bộ giỏ hàng
        if(empty($giohang)){
            $giohang = $giohang_full;
            $_SESSION['checkout_items'] = array_keys($giohang_full);
        }

        // Ràng buộc yêu cầu đăng nhập theo Phương án A
        if(!isset($_SESSION["khachhang"])){
            $_SESSION['redirect_after_login'] = 'index.php?action=thanhtoan';
            header("Location: index.php?action=dangnhap");
            exit();
        }
        $dc = new DIACHI();
        $diachi = $dc->laydiachikhachhang($_SESSION["khachhang"]["id"]);
        include("checkout.php");
        break;

    case "luudonhang": 
        $giohang_full = laygiohang();
        if(empty($giohang_full)){
            header("Location: index.php?action=giohang");
            exit();
        }

        // Lọc danh sách mặt hàng được mua theo các món đã chọn
        $giohang = [];
        if(isset($_SESSION['checkout_items']) && !empty($_SESSION['checkout_items'])){
            foreach($_SESSION['checkout_items'] as $id){
                if(isset($giohang_full[$id])){
                    $giohang[$id] = $giohang_full[$id];
                }
            }
        }
        if(empty($giohang)){
            $giohang = $giohang_full;
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
        
        // Tính tổng tiền cho các mặt hàng được chọn
        $tongtien = 0;
        foreach($giohang as $mh_item){
            $tongtien += (int)($mh_item['thanhtien'] ?? ($mh_item['giaban'] * $mh_item['soluong']));
        }

        // Đặt hàng an toàn bằng Transaction & Kiểm tra đủ tồn kho
        $dh = new DONHANG();
        $phuongthuc = (isset($_POST["opt_thanhtoan"]) && $_POST["opt_thanhtoan"] === 'VIETQR') ? 'VIETQR' : 'COD';
        $result = $dh->datHangAnToan($khachhang_id, $diachi_id, $tongtien, $giohang, $phuongthuc, 0);
        
        if($result['success']){
            $donhang_id = $result['donhang_id'];
            // Chỉ xóa khỏi giỏ hàng những món vừa được đặt mua thành công
            foreach(array_keys($giohang) as $bought_id){
                xoamotmathang($bought_id);
            }
            unset($_SESSION['checkout_items']);

            // Chuyển đến trang xác nhận hóa đơn & mã QR nếu có
            $phuongthucthanhtoan = $phuongthuc;
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

    case "thanhtoan_vietqr":
        $donhang_id = isset($_GET["dhid"]) ? (int)$_GET["dhid"] : 0;
        if($donhang_id > 0){
            $dh = new DONHANG();
            $order = $dh->laydonhangtheoid($donhang_id);
            if($order){
                $tongtien = $order["tongtien"];
                $phuongthucthanhtoan = "VIETQR";
                $trangthaithanhtoan = (int)$order["trangthaithanhtoan"];
                include("message.php");
                break;
            }
        }
        header("Location: index.php?action=thongtin");
        exit();
        break;

    case "check_payment_status":
    case "kiemtrathanhtoan":
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
        $dh = new DONHANG();
        $order = ($orderId > 0) ? $dh->laydonhangtheoid($orderId) : null;
        if(!$order){
            echo json_encode(['success' => false, 'paid' => false, 'message' => 'Không tìm thấy đơn hàng']);
            exit();
        }
        $isPaid = ((int)$order['trangthaithanhtoan'] === 1);
        echo json_encode([
            'success'            => true,
            'order_id'           => $orderId,
            'paid'               => $isPaid,
            'trangthaithanhtoan' => (int)$order['trangthaithanhtoan'],
            'trangthai'          => (int)$order['trangthai'],
            'message'            => $isPaid ? 'Đã thanh toán thành công' : 'Chờ thanh toán'
        ]);
        exit();
        break;

    case "dangnhap":
        if(isset($_GET['redirect']) && $_GET['redirect'] === 'checkout'){
            $_SESSION['redirect_after_login'] = 'index.php?action=thanhtoan';
        }
        include("loginform.php");
        break;

    case "xldangnhap":
        $email = trim($_POST["txtemail"] ?? "");
        $matkhau = trim($_POST["txtmatkhau"] ?? "");
        
        $nd = new NGUOIDUNG();
        // Cho phép cả Quản trị viên (1), Nhân viên (2) và Khách hàng (3)
        $auth = $nd->authenticate($email, $matkhau, [1, 2, 3]);

        if($auth['success']){
            session_regenerate_id(true);
            $user = $auth['user'];

            // Tự động phân luồng theo vai trò người dùng
            if(in_array((int)$user['loai'], [1, 2], true)){
                $_SESSION["admin"] = $user;
                $_SESSION["nguoidung"] = $user;
                header("Location: ../admin/index.php");
                exit();
            } else {
                $_SESSION["khachhang"] = $user;
                $_SESSION["nguoidung"] = $user;

                // Điều hướng thông minh: Nếu có trang chuyển tiếp (ví dụ checkout)
                if(!empty($_SESSION['redirect_after_login'])){
                    $redirectUrl = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                    header("Location: " . $redirectUrl);
                    exit();
                }

                header("Location: index.php");
                exit();
            }
        }
        else{
            $tb_loi_dn = $auth['message'];
            $active_tab = "dangnhap";
            include("loginform.php");
        }
        break;

    case "xldangky":
        $hoten = trim($_POST["txthoten"] ?? "");
        $email = trim($_POST["txtemail"] ?? "");
        $sodienthoai = trim($_POST["txtsodienthoai"] ?? "");
        $matkhau = trim($_POST["txtmatkhau"] ?? "");
        $diachi = trim($_POST["txtdiachi"] ?? "");

        if(empty($hoten) || empty($email) || empty($sodienthoai) || empty($matkhau) || empty($diachi)){
            $tb_loi_dk = "Vui lòng nhập đầy đủ các trường thông tin bắt buộc.";
            $active_tab = "dangky";
            include("loginform.php");
            break;
        }

        if(!preg_match('/^[0-9]{10}$/', $sodienthoai)){
            $tb_loi_dk = "Số điện thoại phải bao gồm đúng 10 chữ số hợp lệ.";
            $active_tab = "dangky";
            include("loginform.php");
            break;
        }

        if(strlen($matkhau) < 6){
            $tb_loi_dk = "Mật khẩu phải có độ dài tối thiểu từ 6 ký tự trở lên.";
            $active_tab = "dangky";
            include("loginform.php");
            break;
        }

        $kh = new KHACHHANG();
        if($kh->kiemtraEmailTonTai($email)){
            $tb_loi_dk = "Địa chỉ email \"{$email}\" đã được đăng ký trong hệ thống. Vui lòng đăng nhập hoặc dùng email khác.";
            $active_tab = "dangky";
            include("loginform.php");
            break;
        }

        $newUserId = $kh->dangkyKhachHang($hoten, $email, $sodienthoai, $matkhau, $diachi);
        if($newUserId){
            session_regenerate_id(true);
            $user = $kh->laythongtinkhachhang($email);
            unset($user['matkhau']);
            $_SESSION["khachhang"] = $user;
            $_SESSION["nguoidung"] = $user;

            // Giữ nguyên giỏ hàng $_SESSION['giohang'], điều hướng
            if(!empty($_SESSION['redirect_after_login'])){
                $redirectUrl = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
                header("Location: " . $redirectUrl);
                exit();
            }

            header("Location: index.php");
            exit();
        } else {
            $tb_loi_dk = "Có lỗi xảy ra trong quá trình đăng ký. Vui lòng thử lại sau.";
            $active_tab = "dangky";
            include("loginform.php");
        }
        break;

    case "thongtin":
        // Ràng buộc bảo vệ trang quản lý tài khoản & đơn hàng
        if(!isset($_SESSION["khachhang"])){
            $_SESSION['redirect_after_login'] = 'index.php?action=thongtin';
            header("Location: index.php?action=dangnhap");
            exit();
        }

        $dh = new DONHANG();
        $donhang = $dh->laydanhsachdonhangtheokh($_SESSION["khachhang"]["id"]);
        
        if(isset($_REQUEST["dhid"])){
            $dhct = new DONHANGCT();
            $donhangct = $dhct->laychitietdonhang($_REQUEST["dhid"]);
        }

        $dc = new DIACHI();
        $diachi_macdinh = $dc->laydiachikhachhang($_SESSION["khachhang"]["id"]);
        include("info.php");
        break;

    case "capnhathoso":
        if(!isset($_SESSION["khachhang"])){
            header("Location: index.php?action=dangnhap");
            exit();
        }
        $hoten = trim($_POST['txthoten'] ?? '');
        $sodt = trim($_POST['txtsodienthoai'] ?? '');
        $diachi = trim($_POST['txtdiachi'] ?? '');

        if(empty($hoten) || empty($sodt)){
            $_SESSION['flash_msg'] = ['type' => 'danger', 'text' => 'Họ tên và số điện thoại không được để trống.'];
        } elseif(!preg_match('/^[0-9]{10}$/', $sodt)){
            $_SESSION['flash_msg'] = ['type' => 'danger', 'text' => 'Số điện thoại phải gồm đúng 10 chữ số.'];
        } else {
            $nd = new NGUOIDUNG();
            $nd->capNhatHoSoKhachHang((int)$_SESSION['khachhang']['id'], $hoten, $sodt, $diachi);
            $_SESSION['khachhang']['hoten'] = $hoten;
            $_SESSION['khachhang']['sodienthoai'] = $sodt;
            if(isset($_SESSION['nguoidung'])){
                $_SESSION['nguoidung']['hoten'] = $hoten;
                $_SESSION['nguoidung']['sodienthoai'] = $sodt;
            }
            $_SESSION['flash_msg'] = ['type' => 'success', 'text' => 'Cập nhật hồ sơ tài khoản thành công!'];
        }
        header("Location: index.php?action=thongtin");
        exit();
        break;

    case "doimatkhau":
        if(!isset($_SESSION["khachhang"])){
            header("Location: index.php?action=dangnhap");
            exit();
        }
        $mk_cu = trim($_POST['txtmatkhau_cu'] ?? '');
        $mk_moi = trim($_POST['txtmatkhau_moi'] ?? '');
        $mk_xacnhan = trim($_POST['txtmatkhau_xacnhan'] ?? '');

        if(empty($mk_cu) || empty($mk_moi) || empty($mk_xacnhan)){
            $_SESSION['flash_msg'] = ['type' => 'danger', 'text' => 'Vui lòng nhập đầy đủ các thông tin mật khẩu.'];
        } elseif(strlen($mk_moi) < 6){
            $_SESSION['flash_msg'] = ['type' => 'danger', 'text' => 'Mật khẩu mới phải có tối thiểu 6 ký tự.'];
        } elseif($mk_moi !== $mk_xacnhan){
            $_SESSION['flash_msg'] = ['type' => 'danger', 'text' => 'Xác nhận mật khẩu mới không trùng khớp.'];
        } else {
            $nd = new NGUOIDUNG();
            $res = $nd->doiMatKhauKhachHang((int)$_SESSION['khachhang']['id'], $mk_cu, $mk_moi);
            $_SESSION['flash_msg'] = [
                'type' => $res['success'] ? 'success' : 'danger',
                'text' => $res['message']
            ];
        }
        header("Location: index.php?action=thongtin");
        exit();
        break;

    case "dangxuat":
        // Chỉ xóa session của khách hàng, không ảnh hưởng đến session admin nếu đang mở tab khác
        unset($_SESSION["khachhang"]);
        unset($_SESSION["nguoidung"]);
        unset($_SESSION["redirect_after_login"]);
        header("Location: index.php");
        exit();
        break;    

    default:
        include("main.php");
        break;
}
?>