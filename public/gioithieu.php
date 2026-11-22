<?php
include("inc/top.php");
?>

<!-- Breadcrumb -->
<section class="py-3 bg-light border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="collection-breadcrumb mb-0">
            <a href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a>
            <span class="mx-1">/</span>
            <span class="text-dark fw-semibold">Giới thiệu LPT DRONE</span>
        </nav>
    </div>
</section>

<div class="container mb-5">
    <!-- PHẦN 1: TỔNG QUAN & SỨ MỆNH LPT DRONE -->
    <section class="bg-white p-4 p-md-5 rounded-3 border mb-5 shadow-none">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge bg-danger text-white px-3 py-1 mb-2 text-uppercase fw-semibold" style="letter-spacing: 0.5px;">Về Thương Hiệu</span>
                <h1 class="h2 fw-bold text-dark mb-3">LPT DRONE - GIẢI PHÁP &amp; PHỤ TÙNG MÁY BAY NÔNG NGHIỆP XAG CHÍNH HÃNG</h1>
                <p class="lead text-secondary fs-6">
                    <strong>LPT DRONE</strong> tự hào là trung tâm cung cấp giải pháp máy bay không người lái (Drone) phun thuốc, rải phân bón và hạt giống, cùng hệ thống kho phụ tùng thay thế chính hãng XAG phục vụ nông nghiệp công nghệ cao hàng đầu tại khu vực Đồng bằng sông Cửu Long (ĐBSCL) và trên toàn quốc.
                </p>
                <p class="text-secondary small mb-3">
                    Trong bối cảnh nền nông nghiệp hiện đại đòi hỏi tốc độ xử lý sâu bệnh nhanh chóng, hạn chế tiếp xúc hóa chất độc hại và tối ưu chi phí phân bón, máy bay nông nghiệp đã trở thành trợ thủ đắc lực không thể thiếu của bà con nông dân và các tổ bay chuyên nghiệp.
                </p>
                <p class="text-secondary small mb-0">
                    Thấu hiểu sâu sắc rằng <em>"Một ngày dừng bay mùa vụ là cả mùa màng bị ảnh hưởng"</em>, LPT DRONE đặt sứ mệnh đảm bảo nguồn cung linh kiện luôn sẵn có, giải quyết nhanh nhất các sự cố hư hao trong quá trình vận hành, giúp phi công yên tâm cất cánh.
                </p>
            </div>
            <div class="col-lg-5 text-center">
                <div class="p-3 bg-light rounded-3 border">
                    <img src="../images/logo.png" alt="LPT DRONE XAG" class="img-fluid rounded" style="max-height: 240px; object-fit: contain;">
                    <div class="mt-3">
                        <span class="fw-bold text-dark d-block">TRUNG TÂM PHỤ TÙNG LPT DRONE</span>
                        <small class="text-muted">Đồng hành cùng nền nông nghiệp công nghệ cao Việt Nam</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PHẦN 2: CÁC DÒNG MÁY HỖ TRỢ CHUYÊN SÂU (TẢI ĐỘNG TỪ BẢNG DONGMAY) -->
    <section class="mb-5">
        <div class="text-center mb-4">
            <span class="badge bg-danger-subtle text-danger px-3 py-1 text-uppercase fw-semibold">Hệ Thống Thiết Bị</span>
            <h2 class="h3 fw-bold text-dark mt-2 mb-2">CÁC DÒNG MÁY DRONE HỖ TRỢ CHUYÊN SÂU</h2>
            <p class="text-secondary small max-w-700 mx-auto">
                Chúng tôi cung cấp đầy đủ phụ tùng từ phần cứng khung sườn, mô-đun điện tử FC/ESC, động cơ lực kéo đến hệ thống phun xịt, rải hạt chuyên dụng chuẩn xác theo từng đời máy:
            </p>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
            <?php 
            if(isset($dongmay_list) && is_array($dongmay_list) && !empty($dongmay_list)):
                foreach($dongmay_list as $dm_item):
                    $ten_dm = htmlspecialchars($dm_item['tendongmay']);
            ?>
            <div class="col">
                <div class="card h-100 border rounded-3 p-3 bg-white">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="bg-danger-subtle text-danger rounded p-2">
                            <i class="bi bi-airplane-engines fs-5"></i>
                        </div>
                        <div>
                            <h3 class="h6 fw-bold mb-0 text-dark">XAG <?php echo $ten_dm; ?></h3>
                            <small class="text-muted">Dòng máy chủ lực nông nghiệp</small>
                        </div>
                    </div>
                    <p class="small text-secondary mb-3">
                        Kho sẵn sàng đầy đủ: Cánh quạt, động cơ tải, cụm bơm nhu động, vòi phun ly tâm, cáp nguồn, module định vị RTK và phụ kiện thay thế định kỳ cho <strong>XAG <?php echo $ten_dm; ?></strong>.
                    </p>
                    <div class="mt-auto">
                        <a href="index.php?action=dongmay&model=<?php echo urlencode($dm_item['tendongmay']); ?>" class="btn btn-outline-danger btn-sm w-100 fw-semibold">
                            Xem linh kiện dòng <?php echo $ten_dm; ?> <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php 
                endforeach;
            else: 
            ?>
            <div class="col">
                <div class="card h-100 border rounded-3 p-3 bg-white">
                    <h3 class="h6 fw-bold text-dark">XAG P100 Pro</h3>
                    <p class="small text-secondary">Phụ tùng chính hãng cho dòng siêu tải trọng 50kg, hệ thống phun RevoSpray 3.</p>
                    <a href="index.php?action=dongmay&model=P100 Pro" class="btn btn-outline-danger btn-sm mt-auto">Xem linh kiện</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- PHẦN 3: 4 GIÁ TRỊ CỐT LÕI & QUY TRÌNH KIỂM ĐỊNH KỸ THUẬT -->
    <section class="mb-5">
        <div class="row g-4">
            <!-- Cột trái: 4 Giá trị cốt lõi -->
            <div class="col-lg-6">
                <div class="bg-white p-4 rounded-3 border h-100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="bg-danger text-white rounded px-2 py-1 small fw-bold">CAM KẾT</div>
                        <h2 class="h5 fw-bold text-dark mb-0">4 GIÁ TRỊ CỐT LÕI TẠI LPT DRONE</h2>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex gap-3">
                            <div class="text-danger fs-4 flex-shrink-0"><i class="bi bi-shield-check"></i></div>
                            <div>
                                <h4 class="h6 fw-bold text-dark mb-1">1. Linh Kiện Chính Hãng 100%</h4>
                                <p class="small text-secondary mb-0">Đầy đủ mã xuất xưởng, nguyên seal từ nhà máy XAG, tương thích hoàn hảo và an toàn tuyệt đối khi bay.</p>
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <div class="text-danger fs-4 flex-shrink-0"><i class="bi bi-boxes"></i></div>
                            <div>
                                <h4 class="h6 fw-bold text-dark mb-1">2. Kho Hàng Trực Tiếp Sẵn Sàng</h4>
                                <p class="small text-secondary mb-0">Hàng ngàn mã phụ tùng luôn lưu trữ tại trạm kỹ thuật Châu Phú - An Giang, không để khách hàng chờ đợi nhập khẩu.</p>
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <div class="text-danger fs-4 flex-shrink-0"><i class="bi bi-lightning-charge"></i></div>
                            <div>
                                <h4 class="h6 fw-bold text-dark mb-1">3. Giao Hỏa Tốc Đáp Ứng Mùa Vụ</h4>
                                <p class="small text-secondary mb-0">Hỗ trợ gửi chành xe, xe khách liên tỉnh ĐBSCL nhận trong ngày và ship hỏa tốc toàn quốc có số vận đơn theo dõi.</p>
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <div class="text-danger fs-4 flex-shrink-0"><i class="bi bi-wrench-adjustable-circle"></i></div>
                            <div>
                                <h4 class="h6 fw-bold text-dark mb-1">4. Hỗ Trợ Kỹ Thuật Tận Tâm 24/7</h4>
                                <p class="small text-secondary mb-0">Tư vấn chuẩn đoán mã lỗi máy bay từ xa qua Video call, hướng dẫn tháo lắp và cài đặt thông số chi tiết.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Quy trình kiểm định kỹ thuật 4 bước -->
            <div class="col-lg-6">
                <div class="bg-white p-4 rounded-3 border h-100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="bg-dark text-white rounded px-2 py-1 small fw-bold">QUY TRÌNH</div>
                        <h2 class="h5 fw-bold text-dark mb-0">QUY TRÌNH KIỂM ĐỊNH KỸ THUẬT NGHIÊM NGẶT</h2>
                    </div>

                    <p class="small text-secondary mb-3">
                        Trước khi xuất kho bàn giao cho đơn vị vận chuyển hoặc giao cho khách, mỗi linh kiện tại LPT DRONE đều trải qua quy trình kiểm tra 4 bước:
                    </p>

                    <div class="position-relative ps-4 border-start border-2 border-danger d-flex flex-column gap-3">
                        <div>
                            <span class="badge bg-dark text-white me-1">Bước 1</span>
                            <strong class="text-dark small">Kiểm tra ngoại quan &amp; Tem chống giả</strong>
                            <p class="text-secondary small mb-0">Xác thực tem vỡ niêm phong, kiểm tra tình trạng chân cắm, ron chống nước IP và bề mặt linh kiện.</p>
                        </div>
                        <div>
                            <span class="badge bg-dark text-white me-1">Bước 2</span>
                            <strong class="text-dark small">Đo kiểm thông số trên tải giả chuyên dụng</strong>
                            <p class="text-secondary small mb-0">Đối với bo mạch FC, ESC, Motor và Pin thông minh: Kỹ sư tiến hành kiểm tra xung điện áp và dòng nạp xả chuẩn.</p>
                        </div>
                        <div>
                            <span class="badge bg-dark text-white me-1">Bước 3</span>
                            <strong class="text-dark small">Dán tem kỹ thuật bảo hành LPT DRONE</strong>
                            <p class="text-secondary small mb-0">Ghi nhận số Serial, kích hoạt bảo hành điện tử và dán tem bảo chứng trách nhiệm.</p>
                        </div>
                        <div>
                            <span class="badge bg-dark text-white me-1">Bước 4</span>
                            <strong class="text-dark small">Đóng gói chống sốc &amp; Bàn giao nhà xe</strong>
                            <p class="text-secondary small mb-0">Bọc xốp khí đa lớp, dán nhãn hàng dễ vỡ và gửi chành xe chỉ định kèm mã gửi hàng cho khách theo dõi.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PHẦN 4: THÔNG TIN LIÊN HỆ KHO HÀNG & NÚT CHUYỂN SANG TRANG SẢN PHẨM -->
    <section class="bg-light p-4 p-md-5 rounded-3 border">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <span class="badge bg-danger text-white px-3 py-1 mb-2 text-uppercase fw-semibold">Kết Nối Với Chúng Tôi</span>
                <h3 class="h4 fw-bold text-dark mb-2">TRẠM BẢO DƯỠNG &amp; TỔNG KHO PHỤ TÙNG LPT DRONE</h3>
                <div class="small text-secondary mt-3">
                    <div class="mb-2"><i class="bi bi-geo-alt-fill text-danger me-2"></i><strong>Địa chỉ kho hàng:</strong> Quốc Lộ 91, Thị trấn Cái Dầu, Huyện Châu Phú, Tỉnh An Giang</div>
                    <div class="mb-2"><i class="bi bi-telephone-fill text-danger me-2"></i><strong>Hotline Kỹ thuật &amp; Bán hàng:</strong> <a href="tel:0833609329" class="text-danger fw-bold text-decoration-none">0833 609 329</a></div>
                    <div class="mb-2"><i class="bi bi-envelope-fill text-danger me-2"></i><strong>Email liên hệ:</strong> hotro@lptdrone.vn</div>
                    <div><i class="bi bi-clock-fill text-danger me-2"></i><strong>Thời gian làm việc:</strong> 7:30 - 21:00 hàng ngày (Trực điện thoại sự cố mùa vụ 24/7)</div>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="d-flex flex-column gap-2">
                    <a href="index.php?action=group" class="btn btn-danger btn-lg fw-bold py-3">
                        <i class="bi bi-grid-fill me-2"></i> Khám phá tất cả sản phẩm
                    </a>
                    <a href="tel:0833609329" class="btn btn-outline-dark btn-md fw-semibold">
                        <i class="bi bi-telephone-inbound me-2"></i> Gọi ngay: 0833 609 329
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
include("inc/bottom.php");
?>
