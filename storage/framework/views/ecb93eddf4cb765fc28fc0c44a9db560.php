<?php $__env->startSection('title', 'Xác minh email'); ?>
<?php $__env->startSection('content'); ?>
<div class="row justify-content-center"><div class="col-md-7 col-lg-6">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h3><i class="bi bi-envelope-check text-primary"></i> Xác minh email</h3>
        <p>Một liên kết xác minh đã được gửi đến <strong><?php echo e(auth()->user()->email); ?></strong>.</p>
        <p class="text-muted">Bạn có thể mở email trên điện thoại hoặc máy tính khác. Sau khi bấm <strong>Xác minh email</strong>, trang này sẽ tự chuyển tiếp để bạn tiếp tục đăng nhập tại đây.</p>
        <div id="trang-thai-xac-minh" class="alert alert-light border d-flex align-items-center gap-2 py-2" role="status" aria-live="polite">
            <span class="spinner-border spinner-border-sm text-primary" aria-hidden="true"></span>
            <span>Đang chờ bạn xác minh email…</span>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button id="kiem-tra-xac-minh" type="button" class="btn btn-success">
                <i class="bi bi-arrow-clockwise"></i> Tôi đã xác minh
            </button>
            <form method="POST" action="<?php echo e(route('verification.send')); ?>"><?php echo csrf_field(); ?>
                <button class="btn btn-outline-primary">Gửi lại email xác minh</button>
            </form>
        </div>
        <p class="text-muted small mt-3 mb-0">Trang tự kiểm tra vài giây một lần. Bạn cũng nên kiểm tra thư mục Spam/Thư rác.</p>
    </div></div>
</div></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(() => {
    const hopTrangThai = document.getElementById('trang-thai-xac-minh');
    const nutKiemTra = document.getElementById('kiem-tra-xac-minh');
    const endpoint = <?php echo json_encode(route('verification.status'), 15, 512) ?>;
    let dangKiemTra = false;
    let daHoanTat = false;

    async function kiemTraTrangThai() {
        if (dangKiemTra || daHoanTat) return;
        dangKiemTra = true;
        nutKiemTra.disabled = true;

        try {
            const phanHoi = await fetch(endpoint, {
                credentials: 'same-origin',
                cache: 'no-store',
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            });

            if (!phanHoi.ok) throw new Error('Không kiểm tra được trạng thái xác minh.');
            const duLieu = await phanHoi.json();

            if (duLieu.da_xac_minh) {
                daHoanTat = true;
                hopTrangThai.className = 'alert alert-success d-flex align-items-center gap-2 py-2';
                hopTrangThai.innerHTML = '<i class="bi bi-check-circle-fill"></i><span>Email đã được xác minh. Đang chuyển vào hệ thống…</span>';
                window.location.assign(duLieu.chuyen_huong);
            }
        } catch (loi) {
            if (!daHoanTat) {
                hopTrangThai.className = 'alert alert-warning d-flex align-items-center gap-2 py-2';
                hopTrangThai.innerHTML = '<i class="bi bi-wifi-off"></i><span>' + loi.message + ' Hãy bấm “Tôi đã xác minh” để thử lại.</span>';
            }
        } finally {
            dangKiemTra = false;
            nutKiemTra.disabled = daHoanTat;
        }
    }

    nutKiemTra.addEventListener('click', kiemTraTrangThai);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) kiemTraTrangThai();
    });
    setInterval(() => {
        if (!document.hidden) kiemTraTrangThai();
    }, 4000);
    kiemTraTrangThai();
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/do an/elearning-hoan-thien/resources/views/auth/xac-minh-email.blade.php ENDPATH**/ ?>