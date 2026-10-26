<?php $__env->startSection('title', $course->title); ?>
<?php $__env->startSection('content'); ?>
<div class="row g-4">
    <div class="col-lg-8">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo e(route('courses.index')); ?>">Khóa học</a></li>
            <li class="breadcrumb-item"><a href="<?php echo e(route('courses.index', ['category' => $course->category_id])); ?>"><?php echo e($course->category->name); ?></a></li>
        </ol></nav>
        <div class="page-heading">
        <div class="eyebrow"><?php echo e($course->category->name); ?></div>
        <h1><?php echo e($course->title); ?>

            <?php if (! ($course->daCongKhai())): ?><span class="badge text-bg-warning fs-6"><?php echo e($course->tenTrangThai()); ?></span><?php endif; ?>
        </h1>
        <p class="text-muted"><i class="bi bi-person"></i> Giảng viên: <strong><?php echo e($course->instructor->name); ?></strong>
            · <i class="bi bi-people"></i> <?php echo e($course->enrollments_count); ?> học viên</p>
        </div>
        <p style="white-space:pre-line"><?php echo e($course->description); ?></p>

        <h4 class="mt-5 mb-3">Nội dung khóa học</h4>
        <?php $__empty_1 = true; $__currentLoopData = $course->sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="card mb-3 border-0 section-card">
                <div class="card-header bg-white fw-semibold"><?php echo e($section->title); ?>

                    <span class="text-muted small">(<?php echo e($section->lessons->count()); ?> bài)</span></div>
                <ul class="list-group list-group-flush">
                    <?php $__currentLoopData = $section->lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="list-group-item"><i class="bi bi-play-circle text-primary"></i> <?php echo e($lesson->title); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-muted">Khóa học chưa có nội dung.</p>
        <?php endif; ?>

        <?php if($course->quizzes->isNotEmpty()): ?>
            <h4 class="mt-4">Bài kiểm tra</h4>
            <ul class="list-group">
                <?php $__currentLoopData = $course->quizzes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quiz): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="list-group-item"><i class="bi bi-patch-question text-success"></i> <?php echo e($quiz->title); ?>

                        <span class="text-muted small">· <?php echo e($quiz->duration_minutes); ?> phút · đạt từ <?php echo e($quiz->pass_score); ?>%</span></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        <?php endif; ?>

        <?php echo $__env->make('danh-gia.khu-vuc', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 sticky-side"><div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-3"><span class="stat-icon"><i class="bi bi-rocket-takeoff"></i></span><div><small class="text-muted">Sẵn sàng?</small><h5 class="mb-0">Bắt đầu học ngay</h5></div></div>
            <?php if(auth()->guard()->guest()): ?>
                <p>Đăng nhập để đăng ký khóa học này.</p>
                <a href="<?php echo e(route('login')); ?>" class="btn btn-primary w-100">Đăng nhập</a>
            <?php else: ?>
                <?php if(auth()->user()->laHocVien()): ?>
                    <?php if($enrolled): ?>
                        <div class="mb-2">Tiến độ: <strong><?php echo e($progress); ?>%</strong></div>
                        <div class="progress mb-3"><div class="progress-bar" style="width:<?php echo e($progress); ?>%"></div></div>
                        <a href="<?php echo e(route('learn.show', $course)); ?>" class="btn btn-success w-100">Vào học</a>
                    <?php elseif($course->daCongKhai()): ?>
                        <form method="POST" action="<?php echo e(route('courses.enroll', $course)); ?>"><?php echo csrf_field(); ?>
                            <button class="btn btn-primary w-100 btn-lg">Đăng ký học</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if($canManage): ?>
                    <a href="<?php echo e(route('learn.show', $course)); ?>" class="btn btn-outline-success w-100 mb-2">Xem nội dung học</a>
                    <a href="<?php echo e(route('instructor.courses.edit', $course)); ?>" class="btn btn-outline-primary w-100 mb-2">Sửa khóa học</a>
                    <a href="<?php echo e(route('instructor.curriculum', $course)); ?>" class="btn btn-outline-secondary w-100">Quản lý chương/bài học</a>
                <?php endif; ?>
            <?php endif; ?>
        </div></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/courses/show.blade.php ENDPATH**/ ?>