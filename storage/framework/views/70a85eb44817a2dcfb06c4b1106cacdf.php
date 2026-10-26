<?php $__env->startSection('title', 'Dashboard quản trị'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-heading"><div class="eyebrow">Trung tâm điều hành</div><h1>Dashboard quản trị</h1><p class="text-muted mb-0">Theo dõi toàn cảnh hoạt động của nền tảng.</p></div>
<?php
    $cards = [
        ['Người dùng', $stats['users'], 'people', 'primary', $stats['verified_users'].' hoạt động · '.$stats['pending_verification'].' chờ xác minh'],
        ['Khóa học', $stats['courses'], 'journal-richtext', 'success', $stats['published'].' đang công khai'],
        ['Lượt đăng ký', $stats['enrollments'], 'person-plus', 'info', 'tổng đăng ký khóa học'],
        ['Bài kiểm tra đã nộp', $stats['attempts'], 'patch-check', 'warning',
            $stats['attempts'] ? 'TB '.number_format($stats['avg_score'], 1).' điểm · đạt '.$stats['pass_rate'].'%' : 'chưa có lượt nộp'],
    ];
?>
<div class="row g-3 mb-4">
    <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$label, $value, $icon, $color, $sub]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-sm-6 col-lg-3"><div class="card border-0 h-100 dashboard-card"><div class="card-body">
            <div class="d-flex justify-content-between">
                <div><div class="text-muted"><?php echo e($label); ?></div><div class="display-6 fw-bold"><?php echo e($value); ?></div></div>
                <span class="dashboard-icon"><i class="bi bi-<?php echo e($icon); ?> fs-4 text-<?php echo e($color); ?>"></i></span>
            </div>
            <div class="small text-muted"><?php echo e($sub); ?></div>
        </div></div></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="row g-4">
    <?php echo $__env->make('partials.dashboard-charts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm"><div class="card-header bg-white fw-semibold">Đăng ký gần đây</div>
            <ul class="list-group list-group-flush">
                <?php $__empty_1 = true; $__currentLoopData = $recentEnrollments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <li class="list-group-item small"><?php echo e($e->user->name); ?> → <strong><?php echo e($e->course->title); ?></strong>
                        <span class="text-muted float-end"><?php echo e($e->enrolled_at?->diffForHumans()); ?></span></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><li class="list-group-item text-muted">Chưa có đăng ký.</li><?php endif; ?>
            </ul></div>
    </div>
    <?php echo $__env->make('partials.bang-diem-hoc-vien', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="col-12">
        <div class="card border-0 shadow-sm"><div class="card-header bg-white fw-semibold">Kết quả kiểm tra gần đây</div>
            <table class="table mb-0 small">
                <thead><tr><th>Học viên</th><th>Khóa học</th><th>Bài kiểm tra</th><th>Điểm</th><th>Kết quả</th><th>Thời điểm</th></tr></thead>
                <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $recentAttempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr><td><?php echo e($a->user->name); ?></td><td><?php echo e($a->quiz->course->title); ?></td><td><?php echo e($a->quiz->title); ?></td>
                        <td><?php echo e(rtrim(rtrim(number_format($a->score, 2), '0'), '.')); ?></td>
                        <td><span class="badge text-bg-<?php echo e($a->passed ? 'success' : 'danger'); ?>"><?php echo e($a->passed ? 'Đạt' : 'Chưa đạt'); ?></span></td>
                        <td><?php echo e($a->submitted_at->format('d/m/Y H:i')); ?></td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6" class="text-muted text-center py-3">Chưa có lượt nộp bài.</td></tr><?php endif; ?>
                </tbody>
            </table></div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>