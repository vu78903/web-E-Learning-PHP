<?php $__env->startSection('title', $course->title); ?>
<?php $__env->startSection('content'); ?>
<?php ($first = $course->sections->flatMap->lessons->first()); ?>
<div class="row g-4">
    <div class="col-lg-4"><?php echo $__env->make('learn._sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
    <div class="col-lg-8">
        <div class="card shadow-sm border-0"><div class="card-body p-4">
            <h3><?php echo e($course->title); ?></h3>
            <p class="text-muted mb-3">Giảng viên: <?php echo e($course->instructor->name); ?></p>
            <p style="white-space:pre-line"><?php echo e($course->description); ?></p>
            <?php if($laHocVien): ?>
                <p>Tiến độ hiện tại: <strong><?php echo e($progress); ?>%</strong></p>
            <?php endif; ?>
            <?php if($first): ?>
                <a class="btn btn-primary" href="<?php echo e(route('learn.lesson', [$course, $first])); ?>">Bắt đầu học</a>
            <?php else: ?>
                <div class="alert alert-light border mb-0">Khóa học chưa có bài học nào.</div>
            <?php endif; ?>
        </div></div>

        <?php if($laHocVien && $progress === 100): ?>
            <div class="alert alert-success mt-3 mb-0">
                <i class="bi bi-trophy-fill"></i>
                Bạn đã hoàn thành khóa học. Hãy để lại đánh giá để giúp giảng viên cải thiện nội dung.
            </div>
        <?php endif; ?>

        <?php echo $__env->make('partials.the-chung-chi', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('danh-gia.khu-vuc', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/learn/show.blade.php ENDPATH**/ ?>