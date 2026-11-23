<?php
include("inc/top.php");
?>

<!-- Breadcrumb -->
<section class="py-3 bg-light border-bottom mb-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="collection-breadcrumb mb-0">
            <a href="index.php"><i class="bi bi-house-door-fill"></i> Trang chủ</a>
            <span class="mx-1">/</span>
            <span class="text-dark fw-semibold">Giỏ hàng của bạn</span>
        </nav>
    </div>
</section>

<div class="container mb-5">
    <?php if(demhangtronggio() == 0): ?>
    <div class="text-center py-5">
        <div class="bg-white p-5 rounded border mx-auto" style="max-width: 480px;">
            <i class="bi bi-bag-x text-muted display-3 mb-3"></i>
            <h3 class="h4 fw-bold text-dark">Giỏ hàng của bạn đang trống</h3>
            <p class="text-muted small">Bạn chưa thêm sản phẩm phụ tùng nào vào giỏ. Hãy tham khảo danh mục sản phẩm để bảo dưỡng máy bay XAG.</p>
            <a href="index.php" class="btn btn-danger px-4 py-2 fw-bold mt-2">
                <i class="bi bi-arrow-left me-1"></i> Khám phá sản phẩm ngay
            </a>
        </div>
    </div>

    <?php else: ?>
    <style>
    .item-checkbox, #select-all-items {
        accent-color: #B91C1C;
        cursor: pointer;
        width: 18px;
        height: 18px;
    }
    .btn-delete-item {
        color: #94a3b8;
        padding: 6px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    .btn-delete-item:hover {
        color: #B91C1C !important;
        background-color: #FEF2F2;
    }
    </style>

    <form action="index.php" method="post" id="cart-form">
        <div class="row g-4">
            <!-- Bảng danh sách mặt hàng -->
            <div class="col-lg-8">
                <div class="bg-white p-4 rounded border">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h2 class="h5 fw-bold text-dark mb-0">
                            Giỏ hàng (<span id="cart-header-count"><?php echo demhangtronggio(); ?></span> sản phẩm)
                        </h2>
                        <a href="index.php?action=xoagiohang" class="text-danger small" onclick="return confirm('Bạn có chắc muốn xóa tất cả sản phẩm khỏi giỏ hàng?');">
                            <i class="bi bi-trash3 me-1"></i> Xóa tất cả
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr class="text-secondary small text-uppercase">
                                    <th class="text-center" style="width: 44px;">
                                        <input type="checkbox" id="select-all-items" class="form-check-input" style="cursor: pointer; accent-color: #B91C1C;" checked title="Chọn tất cả / Bỏ chọn tất cả">
                                    </th>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Đơn giá</th>
                                    <th class="text-center" style="width: 110px;">Số lượng</th>
                                    <th class="text-end">Thành tiền</th>
                                    <th class="text-center" style="width: 50px;">Xóa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($giohang as $id => $mh): ?>
                                <tr id="cart-row-<?php echo $id; ?>" class="cart-item-row align-middle">
                                    <td class="text-center">
                                        <input type="checkbox" 
                                               class="form-check-input item-checkbox" 
                                               name="selected_items[]" 
                                               value="<?php echo $id; ?>" 
                                               data-id="<?php echo $id; ?>" 
                                               data-price="<?php echo $mh['giaban']; ?>"
                                               data-qty="<?php echo $mh['soluong']; ?>"
                                               data-subtotal="<?php echo $mh['thanhtien']; ?>" 
                                               style="cursor: pointer; accent-color: #B91C1C;" 
                                               checked>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?php echo lay_hinhanh_mathang($mh["hinhanh"] ?? ""); ?>" 
                                                 alt="<?php echo htmlspecialchars($mh["tenmathang"]); ?>" 
                                                 class="rounded border bg-light" 
                                                 style="width: 60px; height: 60px; object-fit: contain; padding: 4px;">
                                            <div>
                                                <a href="index.php?action=detail&id=<?php echo $id; ?>" class="fw-bold text-dark text-decoration-none small d-block">
                                                    <?php echo htmlspecialchars($mh["tenmathang"]); ?>
                                                </a>
                                                <span class="text-muted" style="font-size: 0.75rem;">Mã SP: #XAG-<?php echo $id; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center text-nowrap small fw-semibold">
                                        <?php echo number_format($mh["giaban"]); ?> đ
                                    </td>
                                    <td class="text-center">
                                        <input type="number" 
                                               name="mh[<?php echo $id; ?>]" 
                                               value="<?php echo $mh["soluong"]; ?>" 
                                               min="0" 
                                               class="form-control form-control-sm text-center fw-bold mx-auto item-qty-input" 
                                               data-id="<?php echo $id; ?>"
                                               style="width: 70px;">
                                    </td>
                                    <td class="text-end text-nowrap fw-bold text-danger item-subtotal-cell">
                                        <?php echo number_format($mh["thanhtien"]); ?> đ
                                    </td>
                                    <td class="text-center">
                                        <a href="index.php?action=xoamathang&id=<?php echo $id; ?>" 
                                           class="btn-delete-item text-secondary text-decoration-none" 
                                           title="Xóa sản phẩm này khỏi giỏ hàng" 
                                           onclick="return confirm('Bạn có chắc muốn bỏ sản phẩm này khỏi giỏ hàng?');">
                                            <i class="bi bi-trash3 fs-6"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Tiếp tục mua thêm
                        </a>
                        <button type="submit" name="action" value="capnhatgio" class="btn btn-outline-dark btn-sm fw-bold">
                            <i class="bi bi-arrow-clockwise me-1"></i> Cập nhật giỏ hàng
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tóm tắt đơn hàng -->
            <div class="col-lg-4">
                <div class="bg-white p-4 rounded border">
                    <h3 class="h6 fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">TÓM TẮT ĐƠN HÀNG</h3>
                    
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-secondary">Tổng số món đã chọn:</span>
                        <span class="fw-semibold text-dark" id="summary-total-qty"><?php echo demhangtronggio(); ?> sản phẩm</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-secondary">Tạm tính:</span>
                        <span class="fw-semibold text-dark" id="summary-subtotal"><?php echo number_format(tinhtiengiohang()); ?> đ</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 small">
                        <span class="text-secondary">Phí vận chuyển:</span>
                        <span class="text-success fw-bold">Tính theo chành / bến</span>
                    </div>
                    
                    <hr>

                    <div class="d-flex justify-content-between align-items-baseline mb-3">
                        <span class="fw-bold text-dark">TỔNG THANH TOÁN:</span>
                        <span class="fs-4 fw-bolder text-danger" id="summary-grand-total"><?php echo number_format(tinhtiengiohang()); ?> đ</span>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" 
                                name="action" 
                                value="thanhtoan" 
                                id="btn-checkout" 
                                class="btn btn-danger btn-lg fw-bold py-3 text-uppercase" 
                                style="letter-spacing: 0.5px;">
                            Tiến hành đặt hàng
                        </button>
                    </div>

                    <div id="checkout-warning" class="alert alert-warning py-2 px-3 mt-3 mb-0 small text-center" style="display: none; border-radius: 6px;">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Vui lòng tích chọn ít nhất 1 sản phẩm để đặt hàng
                    </div>

                    <div class="mt-4 p-3 bg-light rounded small text-secondary">
                        <div class="mb-2"><i class="bi bi-shield-check text-success me-1"></i> Cam kết 100% sản phẩm XAG chính hãng</div>
                        <div class="mb-2"><i class="bi bi-truck text-dark me-1"></i> Giao hỏa tốc chành xe trong ngày</div>
                        <div><i class="bi bi-telephone-fill text-danger me-1"></i> Hotline: <strong>0833 609 329</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all-items');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const summaryTotalQty = document.getElementById('summary-total-qty');
        const summarySubtotal = document.getElementById('summary-subtotal');
        const summaryGrandTotal = document.getElementById('summary-grand-total');
        const btnCheckout = document.getElementById('btn-checkout');
        const checkoutWarning = document.getElementById('checkout-warning');
        const cartForm = document.getElementById('cart-form');

        function formatNumberVND(num) {
            return new Intl.NumberFormat('vi-VN').format(Math.round(num)) + ' đ';
        }

        function updateCartSummary() {
            let totalCount = 0;
            let totalPrice = 0;
            let checkedCount = 0;

            itemCheckboxes.forEach(function(cb) {
                if (cb.checked) {
                    checkedCount++;
                    const subtotal = parseFloat(cb.getAttribute('data-subtotal')) || 0;
                    totalPrice += subtotal;
                    totalCount++;
                }
            });

            // Cập nhật giao diện Tóm tắt đơn hàng
            if (summaryTotalQty) {
                summaryTotalQty.textContent = totalCount + ' sản phẩm';
            }
            if (summarySubtotal) {
                summarySubtotal.textContent = formatNumberVND(totalPrice);
            }
            if (summaryGrandTotal) {
                summaryGrandTotal.textContent = formatNumberVND(totalPrice);
            }

            // Kiểm tra trạng thái có chọn ít nhất 1 món
            if (checkedCount === 0) {
                if (btnCheckout) {
                    btnCheckout.disabled = true;
                    btnCheckout.classList.remove('btn-danger');
                    btnCheckout.classList.add('btn-secondary');
                    btnCheckout.style.opacity = '0.6';
                    btnCheckout.style.cursor = 'not-allowed';
                }
                if (checkoutWarning) {
                    checkoutWarning.style.display = 'block';
                }
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                }
            } else {
                if (btnCheckout) {
                    btnCheckout.disabled = false;
                    btnCheckout.classList.remove('btn-secondary');
                    btnCheckout.classList.add('btn-danger');
                    btnCheckout.style.opacity = '1';
                    btnCheckout.style.cursor = 'pointer';
                }
                if (checkoutWarning) {
                    checkoutWarning.style.display = 'none';
                }

                if (selectAllCheckbox) {
                    if (checkedCount === itemCheckboxes.length) {
                        selectAllCheckbox.checked = true;
                        selectAllCheckbox.indeterminate = false;
                    } else {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = true;
                    }
                }
            }
        }

        // Sự kiện Chọn tất cả / Bỏ chọn tất cả
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;
                itemCheckboxes.forEach(function(cb) {
                    cb.checked = isChecked;
                });
                updateCartSummary();
            });
        }

        // Sự kiện khi thay đổi từng checkbox
        itemCheckboxes.forEach(function(cb) {
            cb.addEventListener('change', function() {
                updateCartSummary();
            });
        });

        // Sự kiện khi người dùng thay đổi số lượng trực tiếp trong ô input
        const qtyInputs = document.querySelectorAll('.item-qty-input');
        qtyInputs.forEach(function(input) {
            input.addEventListener('input', function() {
                const id = this.getAttribute('data-id');
                const qty = Math.max(0, parseInt(this.value) || 0);
                const cb = document.querySelector(`.item-checkbox[data-id="${id}"]`);
                if (cb) {
                    const price = parseFloat(cb.getAttribute('data-price')) || 0;
                    const newSubtotal = price * qty;
                    cb.setAttribute('data-qty', qty);
                    cb.setAttribute('data-subtotal', newSubtotal);

                    const row = document.getElementById('cart-row-' + id);
                    if (row) {
                        const cellSubtotal = row.querySelector('.item-subtotal-cell');
                        if (cellSubtotal) {
                            cellSubtotal.textContent = formatNumberVND(newSubtotal);
                        }
                    }
                    updateCartSummary();
                }
            });
        });

        // Chặn submit đặt hàng nếu không có món nào được chọn
        if (cartForm) {
            cartForm.addEventListener('submit', function(e) {
                const submitter = e.submitter;
                if (submitter && submitter.value === 'thanhtoan') {
                    const checkedCount = document.querySelectorAll('.item-checkbox:checked').length;
                    if (checkedCount === 0) {
                        e.preventDefault();
                        if (checkoutWarning) checkoutWarning.style.display = 'block';
                        alert('Vui lòng tích chọn ít nhất 1 sản phẩm để tiến hành đặt hàng!');
                        return false;
                    }
                }
            });
        }

        // Khởi tạo tính toán ban đầu
        updateCartSummary();
    });
    </script>
    <?php endif; ?>
</div>

<?php
include("inc/bottom.php");
?>
