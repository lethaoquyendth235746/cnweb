<?php
/**
 * admin/inc/layout_header.php
 * Khung đầu trang chuẩn cho toàn bộ trang Admin XAG Store
 */

require_once __DIR__ . '/../includes/auth_middleware.php';
checkAdminAuth();

require_once __DIR__ . '/../config/db.php';

// Tự động nạp các services nếu cần
spl_autoload_register(function ($class) {
    $serviceFile = __DIR__ . '/../services/' . $class . '.php';
    if (file_exists($serviceFile)) {
        require_once $serviceFile;
    }
});

$pageDocTitle = isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | XAG Store Admin' : 'XAG Store - Cổng Quản Trị Linh Kiện Drone';
?>
<!DOCTYPE html>
<html lang="vi" class="h-full bg-stone-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageDocTitle ?></title>
    <link rel="shortcut icon" href="../images/logo.png" type="image/x-icon">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        xag: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            200: '#fecdd3',
                            300: '#fda4af',
                            400: '#fb7185',
                            500: '#f43f5e',
                            600: '#dc2626',
                            700: '#b91c1c',
                            800: '#991b1b',
                            900: '#7f1d1d',
                            950: '#450a0a',
                            orange: '#ea580c',
                            dark: '#111315',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Chart.js v4 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom CSS bổ trợ -->
    <link rel="stylesheet" href="assets/css/admin-custom.css">
</head>
<body class="h-full bg-stone-50 text-slate-800 antialiased font-sans flex flex-col">

    <!-- Sidebar cố định bên trái -->
    <?php include __DIR__ . '/sidebar.php'; ?>

    <!-- Khối Wrapper bên phải (chừa khoảng trống 64 = 16rem = 256px cho Sidebar trên Desktop) -->
    <div class="md:pl-64 flex flex-col min-h-screen">

        <!-- Topbar điều hướng trên cùng -->
        <?php include __DIR__ . '/topbar.php'; ?>

        <!-- Toast thông báo nổi PRG -->
        <?php include __DIR__ . '/toast.php'; ?>

        <!-- Khu vực nội dung chính của từng trang -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
