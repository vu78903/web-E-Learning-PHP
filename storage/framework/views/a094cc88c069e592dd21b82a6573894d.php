<?php $__env->startSection('title', 'Học viên: '.$course->title); ?>
<?php $__env->startSection('content'); ?>
<nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?php echo e(route('instructor.courses.index')); ?>">Khóa học</a></li>
    <li class="breadcrumb-item active"><?php echo e($course->title); ?></li>
</ol></nav>
<h3 class="mb-3">Học viên đã đăng ký (<?php echo e($students->count()); ?>)</h3>
<?php echo $__env->make('partials.nut-csv-khoa-hoc', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="card border-0 shadow-sm"><table class="table mb-0 align-middle">
    <thead><tr><th>Họ tên</th><th>Email</th><th>Ngày đăng ký</th><th style="width:25%">Tiến độ</th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td><?php echo e($e->user->name); ?></td><td><?php echo e($e->user->email); ?></td>
            <td><?php echo e($e->enrolled_at?->format('d/m/Y')); ?></td>
            <td><div class="progress"><div class="progress-bar" style="width:<?php echo e($e->progress); ?>%"><?php echo e($e->progress); ?>%</div></div></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="4" class="text-center text-muted py-3">Chưa có học viên nào.</td></tr>
    <?php endif; ?>
    </tbody>
</table></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/instructor/courses/students.blade.php ENDPATH**/ ?>