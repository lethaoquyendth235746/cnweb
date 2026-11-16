<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Đảm bảo các hàm giỏ hàng sẵn sàng nếu gọi trực tiếp
if (!function_exists('demhangtronggio')) {
    require_once("../model/giohang.php");
}
$soluong_giohang = demhangtronggio();
$active_model = isset($_GET["model"]) ? $_GET["model"] : "";
$active_dmid = isset($_GET["id"]) && isset($_GET["action"]) && $_GET["action"] == "group" ? $_GET["id"] : "";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>LPT DRONE - Linh Kiện & Phụ Tùng Drone Nông Nghiệp XAG Chính Hãng</title>
    <meta name="description" content="Chuyên phân phối sỉ lẻ linh kiện, phụ tùng máy bay nông nghiệp XAG chính hãng: P100, P100 Pro, P40, V40, R150. Hotline: 0833 609 329." />
    <link rel="shortcut icon" href="../images/logo.png" type="image/x-icon" />

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />

    <!-- Custom Storefront Stylesheet -->
    <link href="css/style.css" rel="stylesheet" />
</head>
<body id="top">

    <!-- 1. TOP-BAR (Nền đen Carbon, chữ sáng) -->
    <div class="top-bar">
        <div class="container d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <span><i class="bi bi-shield-check text-danger me-1"></i> Cam kết 100% linh kiện XAG chính hãng</span>
                <span class="d-none d-md-inline">|</span>
                <span class="d-none d-md-inline"><i class="bi bi-truck text-danger me-1"></i> Giao hàng hỏa tốc - Ship COD toàn quốc</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="d-none d-lg-inline"><i class="bi bi-clock me-1"></i> 7h30 - 21h00 (T2 - CN)</span>
                <a href="tel:0833609329" class="hotline-badge text-decoration-none">
                    <i class="bi bi-telephone-fill me-1"></i> Hotline: 0833 609 329
                </a>
            </div>
        </div>
    </div>

    <!-- 2. MAIN HEADER (Logo, Search lớn, Giỏ hàng, Tài khoản) -->
    <header class="main-header sticky-top">
        <div class="container">
            <div class="row align-items-center">
                <!-- Logo -->
                <div class="col-6 col-lg-3">
                    <a href="index.php" class="brand-logo text-decoration-none">
                        <img src="../images/logo.png" alt="LPT DRONE Logo" />
                        <div>
                            <div class="brand-name">LPT <span>DRONE</span></div>
                            <div class="brand-slogan">Linh Kiện XAG Chính Hãng</div>
                        </div>
                    </a>
                </div>

                <!-- Thanh tìm kiếm lớn (Desktop/Tablet) -->
                <div class="col-12 col-lg-6 my-2 my-lg-0 order-3 order-lg-2">
                    <form action="index.php" method="get" class="search-box mx-auto">
                        <input type="hidden" name="action" value="search">
                        <input type="text" name="tukhoa" class="form-control" 
                               placeholder="Tìm tên linh kiện, mã phụ tùng hoặc dòng máy (P100, P40...)" 
                               value="<?php echo isset($_GET['tukhoa']) ? htmlspecialchars($_GET['tukhoa']) : ''; ?>" required>
                        <button type="submit" class="btn-search">
                            <i class="bi bi-search"></i> <span class="d-none d-sm-inline">Tìm kiếm</span>
                        </button>
                    </form>
                    <div class="search-tags text-center d-none d-md-block">
                        <span class="me-1">Gợi ý tìm kiếm:</span>
                        <a href="index.php?action=dongmay&model=P100 Pro">XAG P100 Pro</a>
                        <a href="index.php?action=dongmay&model=P100">XAG P100</a>
                        <a href="index.php?action=dongmay&model=P40">XAG P40</a>
                        <a href="index.php?action=dongmay&model=V40">XAG V40</a>
                        <a href="index.php?action=search&tukhoa=Pin B13960S">Pin B13960S</a>
                        <a href="index.php?action=search&tukhoa=Cánh quạt">Cánh quạt</a>
                        <a href="index.php?action=search&tukhoa=Bơm">Bơm nhu động</a>
                    </div>
                </div>

                <!-- Tài khoản & Giỏ hàng -->
                <div class="col-6 col-lg-3 d-flex justify-content-end align-items-center gap-2 order-2 order-lg-3">
                    <!-- Dropdown Tài khoản -->
                    <?php if (isset($_SESSION["khachhang"])): ?>
                    <div class="dropdown">
                        <button class="header-action-btn dropdown-toggle border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-check-fill text-success"></i>
                            <span class="d-none d-xl-inline"><?php echo htmlspecialchars($_SESSION["khachhang"]["hoten"]); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="index.php?action=thongtin"><i class="bi bi-bag-check me-2"></i> Đơn hàng của tôi</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="index.php?action=dangxuat"><i class="bi bi-box-arrow-right me-2"></i> Đăng xuất</a></li>
                        </ul>
                    </div>
                    <?php else: ?>
                    <a href="index.php?action=dangnhap" class="header-action-btn text-decoration-none">
                        <i class="bi bi-person-circle"></i>
                        <span class="d-none d-xl-inline">Tài khoản</span>
                    </a>
                    <?php endif; ?>

                    <!-- Nút Giỏ hàng -->
                    <a href="index.php?action=giohang" class="header-action-btn text-decoration-none position-relative">
                        <i class="bi bi-cart3"></i>
                        <span class="d-none d-sm-inline">Giỏ hàng</span>
                        <span class="cart-badge ms-1"><?php echo $soluong_giohang; ?></span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- 3. NAVIGATION BAR (Menu ngang chuẩn E-commerce) -->
    <nav class="navbar navbar-expand-lg main-navbar py-0">
        <div class="container">
            <button class="navbar-toggler text-white border-0 py-2" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list fs-3"></i> <span class="fs-6 align-middle ms-1">DANH MỤC & MENU</span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php if(!isset($_GET['action']) || $_GET['action']=='null') echo 'active'; ?>" href="index.php">
                            <i class="bi bi-house-door-fill"></i> Trang chủ
                        </a>
                    </li>

                    <!-- Dropdown 7 Danh mục linh kiện -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php if($active_dmid != '') echo 'active'; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-grid-fill"></i> Danh mục linh kiện (7)
                        </a>
                        <ul class="dropdown-menu">
                            <?php if (isset($danhmuc) && is_array($danhmuc)): ?>
                                <?php foreach ($danhmuc as $d): ?>
                                <li>
                                    <a class="dropdown-item <?php if($active_dmid == $d['id']) echo 'fw-bold text-danger'; ?>" href="index.php?action=group&id=<?php echo $d["id"]; ?>">
                                        <i class="bi bi-chevron-right text-danger me-2 small"></i><?php echo htmlspecialchars($d["tendanhmuc"]); ?>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </li>

                    <!-- Dropdown Dòng máy XAG -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php if($active_model != '') echo 'active'; ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-cpu-fill"></i> Dòng máy Drone XAG
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?action=dongmay&model=P100 Pro"><strong>XAG P100 Pro</strong> (50kg / MegaFlow)</a></li>
                            <li><a class="dropdown-item" href="index.php?action=dongmay&model=P100"><strong>XAG P100</strong> (40kg / SuperX4)</a></li>
                            <li><a class="dropdown-item" href="index.php?action=dongmay&model=P40"><strong>XAG P40</strong> (Linh hoạt đa năng)</a></li>
                            <li><a class="dropdown-item" href="index.php?action=dongmay&model=V40"><strong>XAG V40</strong> (Thiết kế 2 cánh nâng)</a></li>
                            <li><a class="dropdown-item" href="index.php?action=dongmay&model=R150"><strong>XAG R150</strong> (Robot tự hành mặt đất)</a></li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=search&tukhoa=Pin">
                            <i class="bi bi-battery-charging"></i> Pin B13960S / B13970
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=gioithieu">
                            <i class="bi bi-info-circle-fill"></i> Giới thiệu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=lienhe">
                            <i class="bi bi-headset"></i> Hỗ trợ kỹ thuật
                        </a>
                    </li>
                </ul>

                <div class="d-none d-xl-flex align-items-center text-white small gap-2 py-2">
                    <i class="bi bi-whatsapp text-success fs-5"></i>
                    <span>Tư vấn kỹ thuật: <strong class="text-danger">0833 609 329</strong></span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="py-4">
        <div class="container">
