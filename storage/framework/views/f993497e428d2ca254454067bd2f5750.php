<?php $__env->startSection('title', 'Kết quả bài kiểm tra'); ?>
<?php $__env->startSection('content'); ?>
<nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?php echo e(route('instructor.quizzes.index', $course)); ?>"><?php echo e($course->title); ?></a></li>
    <li class="breadcrumb-item active"><?php echo e($quiz->title); ?></li>
</ol></nav>
<h3 class="mb-3">Kết quả: <?php echo e($quiz->title); ?></h3>
<?php echo $__env->make('partials.nut-csv-bai-kiem-tra', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card border-0 shadow-sm text-center"><div class="card-body"><div class="text-muted">Lượt nộp</div><div class="fs-3 fw-bold"><?php echo e($attempts->count()); ?></div></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm text-center"><div class="card-body"><div class="text-muted">Điểm trung bình</div><div class="fs-3 fw-bold"><?php echo e($avg !== null ? number_format($avg, 1) : '—'); ?></div></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm text-center"><div class="card-body"><div class="text-muted">Tỷ lệ đạt</div><div class="fs-3 fw-bold"><?php echo e($passRate !== null ? $passRate.'%' : '—'); ?></div></div></div></div>
</div>
<div class="card border-0 shadow-sm"><table class="table mb-0">
    <thead><tr><th>Học viên</th><th>Nộp lúc</th><th>Đúng</th><th>Điểm</th><th>Kết quả</th><th></th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $attempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td><?php echo e($a->user->name); ?></td><td><?php echo e($a->submitted_at->format('d/m/Y H:i')); ?></td>
            <td><?php echo e($a->correct_count); ?>/<?php echo e($a->total_questions); ?></td>
            <td><?php echo e(rtrim(rtrim(number_format($a->score, 2), '0'), '.')); ?></td>
            <td><span class="badge text-bg-<?php echo e($a->passed ? 'success' : 'danger'); ?>"><?php echo e($a->passed ? 'Đạt' : 'Chưa đạt'); ?></span></td>
            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('attempts.result', $a)); ?>">Chi tiết</a></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="6" class="text-center text-muted py-3">Chưa có lượt làm bài nào.</td></tr>
    <?php endif; ?>
    </tbody>
</table></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/instructor/quizzes/results.blade.php ENDPATH**/ ?>