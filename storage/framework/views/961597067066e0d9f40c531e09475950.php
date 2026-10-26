<?php $__env->startSection('title', 'Không tìm thấy trang'); ?>
<?php $__env->startSection('content'); ?>
<div class="text-center py-5">
    <div class="display-1 text-secondary"><i class="bi bi-compass"></i></div>
    <h2>404 — Không tìm thấy trang</h2>
    <p class="text-muted">Trang bạn tìm không tồn tại hoặc đã bị ẩn.</p>
    <a href="<?php echo e(route('home')); ?>" class="btn btn-primary">Về trang chủ</a>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/do an/elearning-hoan-thien/resources/views/errors/404.blade.php ENDPATH**/ ?>