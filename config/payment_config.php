<?php
/**
 * CẤU HÌNH CỔNG THANH TOÁN TỰ ĐỘNG VIETQR & WEBHOOK - LPT DRONE
 * Đường dẫn: config/payment_config.php
 * Thông tin tài khoản ngân hàng thụ hưởng chính thức của chủ cửa hàng
 */

define('VIETQR_BANK_ID', 'MB');                           // Mã ngân hàng chuẩn VietQR
define('VIETQR_BANK_NAME', 'MB Bank - Viettel Money');    // Tên ngân hàng hiển thị
define('VIETQR_ACCOUNT_NO', '9704229203418916105');       // Số tài khoản ngân hàng nhận tiền
define('VIETQR_ACCOUNT_NAME', 'LE THAO QUYEN');           // Tên chủ tài khoản (viết hoa không dấu)
define('VIETQR_TEMPLATE', 'compact2');                    // Giao diện QR chuẩn VietQR (compact, compact2, qr_only, print)
define('PAYMENT_PREFIX', 'LPTDH');                        // Tiền tố mã giao dịch (VD: LPTDH15)
define('WEBHOOK_API_KEY', 'LPT_SECRET_2026');             // Mã bí mật xác thực Webhook từ SePay / Casso
