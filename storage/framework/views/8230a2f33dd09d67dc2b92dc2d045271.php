<?php $__env->startSection('title', 'Kết quả: '.$quiz->title); ?>
<?php $__env->startSection('content'); ?>
<nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?php echo e(route('learn.show', $course)); ?>"><?php echo e($course->title); ?></a></li>
    <li class="breadcrumb-item"><a href="<?php echo e(route('quizzes.show', $quiz)); ?>"><?php echo e($quiz->title); ?></a></li>
    <li class="breadcrumb-item active">Kết quả</li>
</ol></nav>

<div class="card border-0 shadow-sm mb-4 text-center"><div class="card-body p-4">
    <div class="text-muted"><?php echo e($attempt->user->name); ?> · nộp lúc <?php echo e($attempt->submitted_at->format('d/m/Y H:i')); ?></div>
    <div class="display-3 fw-bold text-<?php echo e($attempt->passed ? 'success' : 'danger'); ?>"><?php echo e(rtrim(rtrim(number_format($attempt->score, 2), '0'), '.')); ?><small class="fs-4">/100</small></div>
    <div class="fs-5 mb-2">Đúng <?php echo e($attempt->correct_count); ?>/<?php echo e($attempt->total_questions); ?> câu</div>
    <span class="badge fs-6 text-bg-<?php echo e($attempt->passed ? 'success' : 'danger'); ?>"><?php echo e($attempt->passed ? 'ĐẠT' : 'CHƯA ĐẠT'); ?> (yêu cầu <?php echo e($quiz->pass_score); ?>%)</span>
    <?php $remaining = $quiz->soLuotConLaiCua($attempt->user); ?>
    <?php if(auth()->id() === $attempt->user_id && $remaining === 0): ?>
        <div class="mt-3 text-muted">Bạn đã dùng hết <?php echo e($quiz->max_attempts); ?> lượt làm bài này.</div>
    <?php elseif(auth()->id() === $attempt->user_id): ?>
        <div class="mt-3">
            <form method="POST" action="<?php echo e(route('quizzes.start', $quiz)); ?>" class="d-inline"><?php echo csrf_field(); ?>
                <button class="btn btn-outline-primary">Làm lại</button></form>
            <?php if($remaining !== null): ?><div class="small text-muted mt-1">Còn <?php echo e($remaining); ?> lượt</div><?php endif; ?>
        </div>
    <?php endif; ?>
</div></div>

<?php $__currentLoopData = $quiz->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $idDapAnDaChon = $picked[$question->id] ?? [];
        $correctIds = $question->answers->where('is_correct', true)->pluck('id')->sort()->values()->all();
        $isRight = $idDapAnDaChon && collect($idDapAnDaChon)->sort()->values()->all() === $correctIds;
        $mine = auth()->id() === $attempt->user_id;
    ?>
    <div class="card border-0 shadow-sm mb-3 border-start border-4 border-<?php echo e($isRight ? 'success' : 'danger'); ?>"><div class="card-body">
        <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
            <div class="fw-semibold">Câu <?php echo e($i + 1); ?>. <?php echo e($question->question_text); ?>

                <?php if($question->laNhieuDapAn()): ?><span class="badge text-bg-warning fw-normal">Nhiều đáp án</span><?php endif; ?></div>
            <span class="badge align-self-start text-bg-<?php echo e($isRight ? 'success' : 'danger'); ?>"><?php echo e($isRight ? 'Đúng' : 'Sai'); ?></span>
        </div>
        <?php $__currentLoopData = $question->answers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $answer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $isPicked = in_array($answer->id, $idDapAnDaChon, true); ?>
            <div class="px-2 py-1 rounded mb-1
                <?php echo e($answer->is_correct ? 'bg-success-subtle' : ($isPicked ? 'bg-danger-subtle' : '')); ?>">
                <?php if($answer->is_correct): ?><i class="bi bi-check-circle-fill text-success"></i>
                <?php elseif($isPicked): ?><i class="bi bi-x-circle-fill text-danger"></i>
                <?php else: ?><i class="bi bi-circle text-muted"></i><?php endif; ?>
                <?php echo e($answer->answer_text); ?>

                <?php if($isPicked): ?><span class="small text-muted">(<?php echo e($mine ? 'bạn chọn' : 'học viên chọn'); ?>)</span><?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php if(! $idDapAnDaChon): ?><div class="small text-danger mt-1"><?php echo e($mine ? 'Bạn' : 'Học viên'); ?> chưa trả lời câu này.</div>
        <?php elseif($question->laNhieuDapAn() && ! $isRight): ?><div class="small text-danger mt-1">Câu nhiều đáp án chỉ tính đúng khi chọn đủ tất cả đáp án đúng và không chọn thừa.</div><?php endif; ?>
    </div></div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/quizzes/result.blade.php ENDPATH**/ ?>