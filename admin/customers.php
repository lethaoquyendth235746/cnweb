<?php
/**
 * admin/customers.php
 * Quản trị Khách hàng & Hợp tác xã Nông nghiệp (loai = 3) - XAG Store
 * Giai đoạn 3: Tailwind CSS + CustomerAdminService + PRG Pattern
 */

$pageTitle = 'Khách hàng & Hợp tác xã';
require_once __DIR__ . '/includes/auth_middleware.php';
checkAdminAuth();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/services/CustomerAdminService.php';

$customerService = new CustomerAdminService();

// ==========================================
// 1. AJAX LẤY ĐỊA CHỈ & LỊCH SỬ ĐƠN CỦA KHÁCH
// ==========================================
if (isset($_GET['ajax']) && $_GET['ajax'] === 'customer_detail') {
    header('Content-Type: application/json; charset=utf-8');
    $customerId = (int)($_GET['id'] ?? 0);
    $customer = $customerService->getCustomerById($customerId);

    if (!$customer) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy khách hàng']);
    } else {
        $addresses = $customerService->getCustomerAddresses($customerId);
        $orders = $customerService->getCustomerOrders($customerId, 10);
        echo json_encode([
            'success'   => true,
            'customer'  => $customer,
            'addresses' => $addresses,
            'orders'    => $orders
        ]);
    }
    exit;
}

// ==========================================
// 2. XỬ LÝ FORM POST THEO CHUẨN PRG
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Khóa / Mở khóa tài khoản khách hàng
    if ($action === 'toggle_status') {
        $id = (int)($_POST['customer_id'] ?? 0);
        $newStatus = (int)($_POST['new_status'] ?? 0);

        try {
            $customerService->toggleCustomerStatus($id, $newStatus);
            $msg = ($newStatus === 1) ? "Đã kích hoạt lại tài khoản khách hàng #{$id}." : "Đã khóa tài khoản khách hàng #{$id}.";
            setAdminFlash('success', $msg);
        } catch (Throwable $e) {
            setAdminFlash('danger', 'Lỗi thay đổi trạng thái: ' . $e->getMessage());
        }

        header('Location: customers.php');
        exit;
    }
}

// ==========================================
// 3. LẤY DANH SÁCH & BỘ LỌC KHÁCH HÀNG
// ==========================================
$search = trim($_GET['search'] ?? '');
$trangthai = (isset($_GET['trangthai']) && $_GET['trangthai'] !== '') ? (int)$_GET['trangthai'] : null;
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 15;
$offset = ($page - 1) * $limit;

$filters = [
    'search'    => $search,
    'trangthai' => $trangthai,
    'limit'     => $limit,
    'offset'    => $offset
];

$data = $customerService->getAllCustomers($filters);
$customers = $data['customers'];
$totalCustomers = $data['total'];
$totalPages = ceil($totalCustomers / $limit);

require_once __DIR__ . '/inc/layout_header.php';
?>

<div class="space-y-6">

    <!-- Tiêu đề trang -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Khách hàng & Hợp tác xã (HTX)</h1>
            <p class="text-sm text-slate-500 mt-1">
                Theo dõi hồ sơ chủ máy bay, đơn vị HTX nông nghiệp, chi tiêu tích lũy và sổ địa chỉ giao hàng.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold px-3 py-1.5 bg-slate-100 text-slate-600 rounded-xl">
                Tổng cộng: <strong class="text-slate-900 font-mono-num"><?= number_format($totalCustomers) ?></strong> thành viên
            </span>
        </div>
    </div>

    <!-- THANH TÌM KIẾM & BỘ LỌC -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm">
        <form action="customers.php" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tìm kiếm khách hàng</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tên khách hàng, email, SĐT..." class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:bg-white transition">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Trạng thái tài khoản</label>
                <select name="trangthai" class="w-full px-3 py-2 text-xs rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 focus:bg-white transition">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="1" <?= ($trangthai === 1) ? 'selected' : '' ?>>Đang hoạt động</option>
                    <option value="0" <?= ($trangthai === 0) ? 'selected' : '' ?>>Đang bị khóa</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span>Lọc danh sách</span>
                </button>
                <a href="customers.php" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition" title="Xóa bộ lọc">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </a>
            </div>
        </form>
    </div>

    <!-- BẢNG DANH SÁCH KHÁCH HÀNG -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">Khách hàng & Hợp tác xã</th>
                        <th class="py-3.5 px-4">Số điện thoại & Email</th>
                        <th class="py-3.5 px-4 text-center">Tổng số đơn hàng</th>
                        <th class="py-3.5 px-4 text-right">Chi tiêu tích lũy</th>
                        <th class="py-3.5 px-4 text-center">Trạng thái</th>
                        <th class="py-3.5 px-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                Không tìm thấy khách hàng nào khớp với điều kiện tìm kiếm.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($customers as $c): 
                            $isActive = ((int)$c['trangthai'] === 1);
                        ?>
                            <tr class="table-hover-row transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="../images/users/user.png" class="w-10 h-10 rounded-full object-cover border border-slate-200" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?= urlencode($c['hoten']) ?>&background=dc2626&color=fff';">
                                        <div>
                                            <div class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($c['hoten']) ?></div>
                                            <div class="text-[11px] text-slate-400 font-mono">ID: #<?= (int)$c['id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-800 font-mono-num"><?= htmlspecialchars($c['sodienthoai'] ?: 'Chưa có SĐT') ?></div>
                                    <div class="text-slate-400 text-[11px] mt-0.5"><?= htmlspecialchars($c['email']) ?></div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <a href="orders.php?search=<?= urlencode($c['hoten']) ?>" class="inline-flex items-center gap-1 font-bold px-2.5 py-1 rounded-full text-xs <?= ($c['tong_don'] > 0) ? 'bg-red-50 text-red-700' : 'bg-slate-100 text-slate-500' ?>">
                                        <?= (int)$c['tong_don'] ?> đơn
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-red-600 text-sm font-mono-num whitespace-nowrap">
                                    <?= $c['tong_chi_tieu_fmt'] ?>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?= $c['status_badge_class'] ?>">
                                        <?= htmlspecialchars($c['status_label']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" onclick="openCustomerDetail(<?= (int)$c['id'] ?>)" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition" title="Xem lịch sử và sổ địa chỉ">
                                            Hồ sơ
                                        </button>
                                        <form action="customers.php" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn <?= $isActive ? 'KHÓA' : 'MỞ KHÓA' ?> tài khoản này?');">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="customer_id" value="<?= (int)$c['id'] ?>">
                                            <input type="hidden" name="new_status" value="<?= $isActive ? 0 : 1 ?>">
                                            <button type="submit" class="px-2.5 py-1.5 <?= $isActive ? 'bg-rose-50 hover:bg-rose-100 text-rose-600' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700' ?> font-semibold rounded-lg transition">
                                                <?= $isActive ? 'Khóa' : 'Mở' ?>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
            <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-500">
                    Trang <strong><?= $page ?></strong> / <strong><?= $totalPages ?></strong> (Tổng <?= number_format($totalCustomers) ?> khách hàng)
                </span>
                <div class="flex items-center gap-1">
                    <?php for ($p = 1; $p <= $totalPages; $p++): 
                        $q = $_GET;
                        $q['page'] = $p;
                        $pageUrl = 'customers.php?' . http_build_query($q);
                    ?>
                        <a href="<?= $pageUrl ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition <?= ($p === $page) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ==========================================
     MODAL CHI TIẾT KHÁCH HÀNG & LỊCH SỬ MUA
     ========================================== -->
<div id="customer-detail-modal" class="modal-overlay modal-hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="modal-container bg-white w-full max-w-3xl rounded-3xl shadow-2xl border border-slate-100 overflow-hidden flex flex-col max-h-[90vh]">
        
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <h3 id="cmodal-name" class="text-base font-extrabold text-slate-900">Hồ sơ khách hàng</h3>
                <p id="cmodal-contact" class="text-xs text-slate-500 mt-0.5">SĐT: --- • Email: ---</p>
            </div>
            <button type="button" onclick="closeModal('customer-detail-modal')" class="p-2 text-slate-400 hover:text-slate-600 rounded-xl hover:bg-slate-200 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            
            <!-- Sổ địa chỉ giao hàng -->
            <div>
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2">Sổ địa chỉ nhận hàng</h4>
                <div id="cmodal-addresses-list" class="space-y-2">
                    <!-- JS populate -->
                </div>
            </div>

            <!-- Lịch sử 10 đơn hàng gần nhất -->
            <div>
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-2">Lịch sử đơn hàng gần nhất</h4>
                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3">Mã đơn</th>
                                <th class="py-2.5 px-3">Ngày đặt</th>
                                <th class="py-2.5 px-3 text-right">Tổng tiền</th>
                                <th class="py-2.5 px-3 text-center">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody id="cmodal-orders-tbody" class="divide-y divide-slate-100">
                            <!-- JS populate -->
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-end">
            <button type="button" onclick="closeModal('customer-detail-modal')" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition">
                Đóng cửa sổ
            </button>
        </div>

    </div>
</div>

<script>
function openCustomerDetail(customerId) {
    fetch('customers.php?ajax=customer_detail&id=' + customerId)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert('Không thể tải thông tin khách hàng: ' + data.message);
                return;
            }
            const c = data.customer;
            document.getElementById('cmodal-name').textContent = c.hoten + ' (ID: #' + c.id + ')';
            document.getElementById('cmodal-contact').textContent = 'SĐT: ' + (c.sodienthoai || '---') + ' • Email: ' + c.email;

            // Render danh sách địa chỉ
            const addrDiv = document.getElementById('cmodal-addresses-list');
            addrDiv.innerHTML = '';
            if (data.addresses && data.addresses.length > 0) {
                data.addresses.forEach(a => {
                    const el = document.createElement('div');
                    el.className = 'p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 flex items-center justify-between';
                    el.innerHTML = `
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400">📍</span>
                            <span>${a.diachi}</span>
                        </div>
                        ${a.macdinh == 1 ? '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Mặc định</span>' : ''}
                    `;
                    addrDiv.appendChild(el);
                });
            } else {
                addrDiv.innerHTML = '<p class="text-xs text-slate-400 italic">Khách hàng chưa lưu sổ địa chỉ nào.</p>';
            }

            // Render lịch sử đơn
            const tbody = document.getElementById('cmodal-orders-tbody');
            tbody.innerHTML = '';
            if (data.orders && data.orders.length > 0) {
                data.orders.forEach(o => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="py-2.5 px-3 font-mono font-bold text-slate-900">#${o.id}</td>
                        <td class="py-2.5 px-3 text-slate-500 font-mono-num">${o.ngay_fmt || o.ngay}</td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900 font-mono-num">${o.tongtien_fmt}</td>
                        <td class="py-2.5 px-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold ${o.status_meta.badge_class}">
                                ${o.status_meta.label}
                            </span>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="py-4 text-center text-slate-400">Chưa có đơn hàng nào.</td></tr>';
            }

            openModal('customer-detail-modal');
        })
        .catch(err => {
            alert('Lỗi kết nối tải khách hàng: ' + err);
        });
}
</script>

<?php require_once __DIR__ . '/inc/layout_footer.php'; ?>
