<?php $__env->startSection('title', 'Xác thực chứng chỉ'); ?>
<?php $__env->startSection('content'); ?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 text-center">
                <i class="bi bi-patch-check-fill text-success" style="font-size:3rem"></i>
                <h3 class="mt-2">Chứng chỉ hợp lệ</h3>
                <p class="text-muted">Chứng chỉ này do <?php echo e(config('app.name')); ?> cấp và đang có hiệu lực.</p>
                <table class="table text-start mt-3 mb-0">
                    <tr><th style="width:40%">Mã chứng chỉ</th><td><code><?php echo e($chungChi->ma_chung_chi); ?></code></td></tr>
                    <tr><th>Học viên</th><td><?php echo e($chungChi->user->name); ?></td></tr>
                    <tr><th>Khóa học</th><td><?php echo e($chungChi->course->title); ?></td></tr>
                    <tr><th>Giảng viên</th><td><?php echo e($chungChi->course->instructor->name); ?></td></tr>
                    <tr><th>Ngày cấp</th><td><?php echo e($chungChi->cap_luc->format('d/m/Y H:i')); ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/chung-chi/xac-thuc.blade.php ENDPATH**/ ?>