<?php $__env->startSection('title', 'Không có quyền truy cập'); ?>
<?php $__env->startSection('content'); ?>
<div class="text-center py-5">
    <div class="display-1 text-danger"><i class="bi bi-shield-lock"></i></div>
    <h2>403 — Không có quyền truy cập</h2>
    <p class="text-muted"><?php echo e($exception->getMessage() ?: 'Bạn không có quyền thực hiện chức năng này.'); ?></p>
    <a href="<?php echo e(route('home')); ?>" class="btn btn-primary">Về trang chủ</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/errors/403.blade.php ENDPATH**/ ?>