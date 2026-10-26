<?php $__env->startSection('title', 'Trang chủ'); ?>
<?php $__env->startSection('content'); ?>
<div class="hero mb-4">
    <div class="hero-content">
        <div class="hero-eyebrow">Nền tảng học tập thế hệ mới</div>
        <h1>Biến tò mò thành năng lực.</h1>
        <p>Khám phá lộ trình học rõ ràng, video trực quan, tài liệu đầy đủ và bài kiểm tra giúp bạn nhìn thấy tiến bộ qua từng ngày.</p>
        <div class="d-flex flex-wrap gap-2 mt-4">
            <a href="<?php echo e(route('courses.index')); ?>" class="btn btn-light btn-lg">Khám phá khóa học <i class="bi bi-arrow-right ms-1"></i></a>
            <?php if(auth()->guard()->guest()): ?><a href="<?php echo e(route('register')); ?>" class="btn btn-outline-light btn-lg">Tạo tài khoản miễn phí</a><?php endif; ?>
        </div>
        <div class="hero-trust">
            <span><i class="bi bi-check-circle-fill me-1"></i> Học theo tiến độ riêng</span>
            <span><i class="bi bi-check-circle-fill me-1"></i> Kiểm tra trực tuyến</span>
            <span><i class="bi bi-check-circle-fill me-1"></i> Chứng chỉ hoàn thành</span>
        </div>
    </div>
</div>

<div class="row g-3 mb-5">
    <?php $__currentLoopData = [['Khóa học', $stats['courses'], 'journal-richtext'], ['Học viên', $stats['students'], 'people'], ['Giảng viên', $stats['instructors'], 'person-workspace']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $n, $icon]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-4 stat-card"><div class="card border-0 h-100"><div class="card-body d-flex align-items-center gap-3">
            <span class="stat-icon"><i class="bi bi-<?php echo e($icon); ?>"></i></span>
            <div><div class="fs-3 fw-bold lh-1"><?php echo e($n); ?></div><div class="text-muted mt-1"><?php echo e($label); ?> đang hoạt động</div></div>
        </div></div></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="page-heading">
    <div class="eyebrow">Học theo sở thích</div>
    <h2>Danh mục nổi bật</h2>
</div>
<div class="mb-5">
    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a class="category-pill" href="<?php echo e(route('courses.index', ['category' => $c->id])); ?>"><i class="bi bi-grid"></i><?php echo e($c->name); ?> <span class="text-muted"><?php echo e($c->courses_count); ?></span></a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="d-flex justify-content-between align-items-end mb-3">
    <div class="page-heading mb-0"><div class="eyebrow">Vừa cập nhật</div><h2 class="mb-0">Khóa học mới nhất</h2></div>
    <a class="text-decoration-none fw-semibold" href="<?php echo e(route('courses.index')); ?>">Xem tất cả <i class="bi bi-arrow-right"></i></a>
</div>
<div class="row g-4">
    <?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-md-6 col-lg-4"><?php echo $__env->make('partials.course-card', ['course' => $course], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p class="text-muted">Chưa có khóa học nào được công khai.</p>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/home.blade.php ENDPATH**/ ?>