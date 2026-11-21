<?php
/**
 * admin/inc/layout_footer.php
 * Khung chân trang chuẩn cho toàn bộ trang Admin XAG Store
 */
?>
        </main>

        <!-- Chân trang Admin -->
        <footer class="py-4 px-6 text-center text-xs text-slate-400 border-t border-slate-200 bg-white">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 max-w-7xl mx-auto">
                <p>© <?= date('Y') ?> <strong class="text-slate-600 font-semibold">XAG STORE</strong> - Hệ thống quản lý phân phối linh kiện & máy bay nông nghiệp XAG Việt Nam.</p>
                <p class="text-[11px] text-slate-400">Phiên bản Hệ thống <strong>v2.5 Modern SaaS</strong> • Backend Service Layer Powered</p>
            </div>
        </footer>

    </div><!-- /Wrapper -->

    <!-- Admin App Scripts -->
    <script src="assets/js/admin-app.js"></script>
    <script>
        // Kích hoạt Lucide icons sau khi DOM đã render
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</body>
</html>
