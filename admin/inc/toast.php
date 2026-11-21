<?php
/**
 * admin/inc/toast.php
 * Hiển thị Flash Message nổi góc trên bên phải màn hình (PRG Pattern)
 */

if (!function_exists('getAdminFlash')) {
    require_once __DIR__ . '/../includes/auth_middleware.php';
}

$flashMessages = getAdminFlash();
if (empty($flashMessages)) {
    return;
}
?>
<div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col space-y-3 max-w-sm w-full pointer-events-none">
    <?php foreach ($flashMessages as $msg): 
        $type = $msg['type'] ?? 'info';
        $text = $msg['message'] ?? '';
        
        // Cấu hình màu sắc & icon theo loại thông báo
        switch ($type) {
            case 'success':
                $bgClass = 'bg-white border-l-4 border-red-500 shadow-lg';
                $textClass = 'text-slate-800';
                $iconClass = 'text-red-500';
                $iconSvg = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                $barClass = 'bg-red-500';
                $badgeTitle = 'Thành công';
                $textClass = 'text-slate-800';
                $iconClass = 'text-emerald-500';
                $iconSvg = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                $barClass = 'bg-emerald-500';
                $badgeTitle = 'Thành công';
                break;
            case 'danger':
            case 'error':
                $bgClass = 'bg-white border-l-4 border-rose-500 shadow-lg';
                $textClass = 'text-slate-800';
                $iconClass = 'text-rose-500';
                $iconSvg = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                $barClass = 'bg-rose-500';
                $badgeTitle = 'Lỗi xử lý';
                break;
            case 'warning':
                $bgClass = 'bg-white border-l-4 border-amber-500 shadow-lg';
                $textClass = 'text-slate-800';
                $iconClass = 'text-amber-500';
                $iconSvg = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>';
                $barClass = 'bg-amber-500';
                $badgeTitle = 'Cảnh báo';
                break;
            default:
                $bgClass = 'bg-white border-l-4 border-blue-500 shadow-lg';
                $textClass = 'text-slate-800';
                $iconClass = 'text-blue-500';
                $iconSvg = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                $barClass = 'bg-blue-500';
                $badgeTitle = 'Thông báo';
                break;
        }
    ?>
    <div class="toast-item pointer-events-auto rounded-xl p-4 <?= $bgClass ?> relative overflow-hidden border border-slate-100 flex items-start space-x-3">
        <div class="flex-shrink-0 mt-0.5 <?= $iconClass ?>">
            <?= $iconSvg ?>
        </div>
        <div class="flex-1 pr-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-0.5"><?= htmlspecialchars($badgeTitle) ?></h4>
            <p class="text-sm font-medium <?= $textClass ?>"><?= htmlspecialchars($text) ?></p>
        </div>
        <button type="button" class="toast-close-btn flex-shrink-0 text-slate-400 hover:text-slate-600 transition-colors p-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
        <div class="toast-progress-bar absolute bottom-0 left-0 h-1 <?= $barClass ?>"></div>
    </div>
    <?php endforeach; ?>
</div>
