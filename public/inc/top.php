<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!function_exists('demhangtronggio')) {
    require_once("../model/giohang.php");
}
$soluong_giohang = demhangtronggio();
$active_action = isset($_GET["action"]) ? $_GET["action"] : "null";
$active_model = isset($_GET["model"]) ? $_GET["model"] : "";
$active_dmid = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>LPT DRONE - Cửa Hàng linh kiện & Sản Phẩm Drone XAG Chính Hãng</title>
    <meta name="description" content="Trung tâm sản phẩm, phụ tùng máy bay nông nghiệp XAG P100, P100 Pro, P40, V40, R150 chính hãng. Hotline: 0833 609 329." />
    <link rel="shortcut icon" href="../images/logo.png" type="image/x-icon" />

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />

    <!-- Shopify Minimalist Tech Storefront Theme -->
    <link href="css/style.css" rel="stylesheet" />
</head>
<body id="top">

    <!-- MAIN HEADER (CỐ ĐỊNH SÁT MÉP TRÊN CÙNG KHI CUỘN) -->
    <header class="site-header">
        <div class="container">
            <div class="header-container">
                <!-- 1. Trái: Logo LPT DRONE -->
                <a href="index.php" class="header-logo text-decoration-none">
                    <img src="../images/logo.png" alt="LPT DRONE Logo" />
                    <div class="header-logo-text">
                        <div class="header-logo-title">LPT <span>DRONE</span></div>
                        <div class="header-logo-sub">Linh kiện Drone XAG</div>
                    </div>
                </a>

                <!-- 2. Giữa: Menu điều hướng ngang tối giản -->
                <nav class="d-none d-xl-block">
                    <ul class="nav-menu">
                        <li>
                            <a href="index.php" class="nav-menu-link <?php if($active_action=='null' && empty($_GET['id']) && empty($_GET['model'])) echo 'active'; ?>">
                                Trang chủ
                            </a>
                        </li>
                        <li>
                            <a href="index.php?action=group" class="nav-menu-link <?php if($active_action=='group' && empty($_GET['id']) && empty($_GET['model'])) echo 'active'; ?>">
                                Tất cả sản phẩm
                            </a>
                        </li>
                        
                        <!-- Dropdown 7 Danh mục sản phẩm -->
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-menu-link dropdown-toggle <?php if($active_dmid > 0) echo 'active'; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                Danh mục sản phẩm
                            </a>
                            <ul class="dropdown-menu shadow-sm border" style="border-radius: 8px;">
                                <?php if(isset($danhmuc) && is_array($danhmuc)): ?>
                                    <?php foreach ($danhmuc as $d): ?>
                                    <li>
                                        <a class="dropdown-item py-2 px-3 small <?php if($active_dmid == $d['id']) echo 'fw-bold text-danger'; ?>" href="index.php?action=group&id=<?php echo $d["id"]; ?>">
                                            <?php echo htmlspecialchars($d["tendanhmuc"]); ?>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <!-- Dropdown Theo dòng máy XAG -->
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-menu-link dropdown-toggle <?php if(!empty($active_model)) echo 'active'; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                Theo dòng máy XAG
                            </a>
                            <ul class="dropdown-menu shadow-sm border" style="border-radius: 8px;">
                                <li><a class="dropdown-item py-2 px-3 small" href="index.php?action=dongmay&model=P100 Pro"><strong>XAG P100 Pro</strong> (50kg)</a></li>
                                <li><a class="dropdown-item py-2 px-3 small" href="index.php?action=dongmay&model=P100"><strong>XAG P100</strong> (40kg)</a></li>
                                <li><a class="dropdown-item py-2 px-3 small" href="index.php?action=dongmay&model=P40"><strong>XAG P40</strong> (Đa năng)</a></li>
                                <li><a class="dropdown-item py-2 px-3 small" href="index.php?action=dongmay&model=V40"><strong>XAG V40</strong> (2 cánh nâng)</a></li>
                                <li><a class="dropdown-item py-2 px-3 small" href="index.php?action=dongmay&model=R150"><strong>Robot R150</strong> (Mặt đất)</a></li>
                            </ul>
                        </li>

                        <li>
                            <a href="index.php?action=lienhe" class="nav-menu-link <?php if($active_action=='lienhe') echo 'active'; ?>">
                                Liên hệ
                            </a>
                        </li>
                    </ul>
                </nav>

                <!-- 3. Khung ô tìm kiếm trực tiếp trên Header (Cân đối giữa Menu và Cụm Icon) -->
                <form action="index.php" method="get" class="header-search-form d-none d-md-block">
                    <input type="hidden" name="action" value="search">
                    <input type="text" name="tukhoa" class="form-control" 
                           placeholder="Tìm kiếm sản phẩm, mã phụ tùng..." 
                           value="<?php echo isset($_GET['tukhoa']) ? htmlspecialchars($_GET['tukhoa']) : ''; ?>" required>
                    <button type="submit" class="btn-search-header" title="Tìm kiếm">
                        <i class="bi bi-search"></i>
                    </button>
                </form>

                <!-- 4. Phải: Action Icons (Tài khoản, Giỏ hàng, Menu Mobile) -->
                <div class="header-actions">
                    <!-- Icon Tài khoản -->
                    <?php if (isset($_SESSION["khachhang"])): ?>
                    <div class="dropdown">
                        <button class="header-icon-btn dropdown-toggle border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Tài khoản: <?php echo htmlspecialchars($_SESSION["khachhang"]["hoten"]); ?>">
                            <i class="bi bi-person-fill text-danger"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border" style="border-radius: 8px;">
                            <li><h6 class="dropdown-header"><?php echo htmlspecialchars($_SESSION["khachhang"]["hoten"]); ?></h6></li>
                            <li><a class="dropdown-item small" href="index.php?action=thongtin"><i class="bi bi-box-seam me-2"></i> Lịch sử đơn hàng</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item small text-danger" href="index.php?action=dangxuat"><i class="bi bi-box-arrow-right me-2"></i> Đăng xuất</a></li>
                        </ul>
                    </div>
                    <?php else: ?>
                    <a href="index.php?action=dangnhap" class="header-icon-btn text-decoration-none" title="Đăng nhập tài khoản">
                        <i class="bi bi-person"></i>
                    </a>
                    <?php endif; ?>

                    <!-- Icon Giỏ hàng -->
                    <a href="index.php?action=giohang" class="header-icon-btn text-decoration-none" title="Giỏ hàng">
                        <i class="bi bi-bag"></i>
                        <span class="cart-count-badge"><?php echo $soluong_giohang; ?></span>
                    </a>

                    <!-- Nút mở Menu Mobile -->
                    <button class="header-icon-btn d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNavDrawer" aria-controls="mobileNavDrawer" title="Menu">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Khung tìm kiếm hiển thị sẵn trên màn hình nhỏ di động (dưới 768px) -->
        <div class="mobile-search-row d-md-none">
            <div class="container">
                <form action="index.php" method="get" class="header-search-form w-100 max-w-none">
                    <input type="hidden" name="action" value="search">
                    <input type="text" name="tukhoa" class="form-control" 
                           placeholder="Tìm kiếm sản phẩm, mã phụ tùng..." 
                           value="<?php echo isset($_GET['tukhoa']) ? htmlspecialchars($_GET['tukhoa']) : ''; ?>" required>
                    <button type="submit" class="btn-search-header" title="Tìm kiếm">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation Drawer -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileNavDrawer" aria-labelledby="mobileNavDrawerLabel">
        <div class="offcanvas-header border-bottom">
            <div class="d-flex align-items-center gap-2">
                <img src="../images/logo.png" alt="Logo" style="height: 38px; border-radius: 6px;">
                <h5 class="offcanvas-title fw-bold" id="mobileNavDrawerLabel">LPT <span class="text-danger">DRONE</span></h5>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="list-unstyled">
                <li class="py-2 border-bottom"><a href="index.php" class="fw-bold text-dark d-block">Trang chủ</a></li>
                <li class="py-2 border-bottom"><a href="index.php?action=group" class="fw-bold text-dark d-block">Tất cả sản phẩm</a></li>
                <li class="py-2 border-bottom">
                    <span class="fw-bold text-dark d-block mb-1">Danh mục sản phẩm:</span>
                    <ul class="list-unstyled ps-3 small">
                        <?php if(isset($danhmuc) && is_array($danhmuc)): ?>
                            <?php foreach ($danhmuc as $d): ?>
                            <li class="py-1"><a href="index.php?action=group&id=<?php echo $d["id"]; ?>" class="text-secondary"><?php echo htmlspecialchars($d["tendanhmuc"]); ?></a></li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </li>
                <li class="py-2 border-bottom">
                    <span class="fw-bold text-dark d-block mb-1">Theo dòng máy:</span>
                    <ul class="list-unstyled ps-3 small">
                        <li class="py-1"><a href="index.php?action=dongmay&model=P100 Pro" class="text-secondary">XAG P100 Pro</a></li>
                        <li class="py-1"><a href="index.php?action=dongmay&model=P100" class="text-secondary">XAG P100</a></li>
                        <li class="py-1"><a href="index.php?action=dongmay&model=P40" class="text-secondary">XAG P40</a></li>
                        <li class="py-1"><a href="index.php?action=dongmay&model=V40" class="text-secondary">XAG V40</a></li>
                        <li class="py-1"><a href="index.php?action=dongmay&model=R150" class="text-secondary">Robot R150</a></li>
                    </ul>
                </li>
                <li class="py-2"><a href="index.php?action=lienhe" class="fw-bold text-dark d-block">Hỗ trợ kỹ thuật: 0833 609 329</a></li>
            </ul>
        </div>
    </div>
