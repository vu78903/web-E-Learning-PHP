<?php $__env->startSection('title', 'Khóa học của tôi'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-heading"><div class="eyebrow">Không gian học tập</div><h1>Khóa học của tôi</h1><p class="text-muted mb-0">Tiếp tục từ nơi bạn đã dừng lại.</p></div>
<div class="row g-4">
    <?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm"><div class="card-body d-flex flex-column">
                <span class="badge text-bg-light align-self-start mb-2"><?php echo e($course->category->name); ?></span>
                <h5><?php echo e($course->title); ?></h5>
                <div class="small text-muted mb-2"><i class="bi bi-person"></i> <?php echo e($course->instructor->name); ?></div>
                <div class="progress mb-1"><div class="progress-bar" style="width:<?php echo e($course->progress); ?>%"></div></div>
                <div class="small mb-3"><?php echo e($course->progress); ?>% hoàn thành</div>
                <a href="<?php echo e(route('learn.show', $course)); ?>" class="btn btn-primary mt-auto">Tiếp tục học</a>
                <?php echo $__env->make('partials.nut-chung-chi', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div></div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12"><div class="alert alert-light border">Bạn chưa đăng ký khóa học nào. <a href="<?php echo e(route('courses.index')); ?>">Khám phá khóa học</a>.</div></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/my-courses.blade.php ENDPATH**/ ?>