<?php $__env->startSection('title', $quiz->title); ?>
<?php $__env->startSection('content'); ?>
<nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?php echo e(route('learn.show', $course)); ?>"><?php echo e($course->title); ?></a></li>
    <li class="breadcrumb-item active"><?php echo e($quiz->title); ?></li>
</ol></nav>
<div class="card shadow-sm border-0 mb-4"><div class="card-body p-4">
    <h3><?php echo e($quiz->title); ?></h3>
    <ul class="list-unstyled text-muted">
        <li><i class="bi bi-list-ol"></i> <?php echo e($quiz->questions_count); ?> câu hỏi</li>
        <li><i class="bi bi-clock"></i> Thời gian: <?php echo e($quiz->duration_minutes); ?> phút</li>
        <li><i class="bi bi-trophy"></i> Điểm đạt: <?php echo e($quiz->pass_score); ?>%</li>
        <li><i class="bi bi-arrow-repeat"></i> Số lần làm: <?php echo e($quiz->coGioiHanLuotLam() ? 'tối đa '.$quiz->max_attempts.' lần' : 'không giới hạn'); ?></li>
        <?php if($quiz->shuffle_questions): ?><li><i class="bi bi-shuffle"></i> Câu hỏi và phương án được xáo trộn mỗi lượt</li><?php endif; ?>
    </ul>
    <?php if(auth()->user()->laHocVien()): ?>
        <?php ($inProgress = $attempts->contains(fn ($a) => ! $a->daNop())); ?>
        <?php if($quiz->coGioiHanLuotLam()): ?>
            <div class="alert alert-<?php echo e($remaining > 0 || $inProgress ? 'info' : 'warning'); ?> py-2">
                Bạn đã làm <strong><?php echo e($attempts->count()); ?>/<?php echo e($quiz->max_attempts); ?></strong> lượt — còn lại <strong><?php echo e($remaining); ?></strong> lượt.
                <?php if($remaining === 0 && ! $inProgress): ?> Bạn đã hết lượt làm bài này. <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if($quiz->questions_count > 0): ?>
            <?php if($inProgress || $remaining === null || $remaining > 0): ?>
                <form method="POST" action="<?php echo e(route('quizzes.start', $quiz)); ?>"><?php echo csrf_field(); ?>
                    <button class="btn btn-primary btn-lg"><?php echo e($inProgress ? 'Tiếp tục làm bài' : 'Bắt đầu làm bài'); ?></button>
                </form>
            <?php else: ?>
                <button class="btn btn-secondary btn-lg" disabled>Đã hết lượt làm bài</button>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-warning mb-0">Bài kiểm tra chưa có câu hỏi.</div>
        <?php endif; ?>
    <?php else: ?>
        <a class="btn btn-outline-primary" href="<?php echo e(route('instructor.quizzes.edit', $quiz)); ?>">Quản lý bài kiểm tra</a>
    <?php endif; ?>
</div></div>

<?php if(auth()->user()->laHocVien()): ?>
<h5>Lịch sử làm bài</h5>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>#</th><th>Nộp lúc</th><th>Số câu đúng</th><th>Điểm</th><th>Kết quả</th><th></th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $attempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td><?php echo e($attempts->count() - $i); ?></td>
            <td><?php echo e($a->submitted_at?->format('d/m/Y H:i') ?? 'Chưa nộp'); ?></td>
            <td><?php echo e($a->daNop() ? $a->correct_count.'/'.$a->total_questions : '—'); ?></td>
            <td><?php echo e($a->daNop() ? rtrim(rtrim(number_format($a->score, 2), '0'), '.') : '—'); ?></td>
            <td><?php if($a->daNop()): ?><span class="badge text-bg-<?php echo e($a->passed ? 'success' : 'danger'); ?>"><?php echo e($a->passed ? 'Đạt' : 'Chưa đạt'); ?></span><?php else: ?><span class="badge text-bg-secondary">Đang làm</span><?php endif; ?></td>
            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?php echo e($a->daNop() ? route('attempts.result', $a) : route('attempts.take', $a)); ?>"><?php echo e($a->daNop() ? 'Xem' : 'Làm tiếp'); ?></a></td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="6" class="text-center text-muted py-3">Bạn chưa làm bài này.</td></tr>
    <?php endif; ?>
    </tbody>
</table></div></div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/quizzes/show.blade.php ENDPATH**/ ?>