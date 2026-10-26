<?php $__env->startSection('title', $quiz->title); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3 sticky-top bg-white shadow-sm rounded p-3" style="top:.5rem;z-index:10">
    <h4 class="mb-0"><?php echo e($quiz->title); ?></h4>
    <div class="fs-5"><i class="bi bi-clock"></i> <span id="timer" class="fw-bold">--:--</span></div>
</div>

<form method="POST" action="<?php echo e(route('attempts.submit', $attempt)); ?>" id="quiz-form"><?php echo csrf_field(); ?>
    <?php $__currentLoopData = $quiz->questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="card border-0 shadow-sm mb-3"><div class="card-body">
            <div class="fw-semibold mb-2">Câu <?php echo e($i + 1); ?>. <?php echo e($question->question_text); ?></div>
            <?php if($question->laNhieuDapAn()): ?><div class="small text-primary mb-2"><i class="bi bi-check2-square"></i> Chọn tất cả phương án đúng</div><?php endif; ?>
            <?php $__currentLoopData = $question->answers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $answer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="form-check">
                    <?php if($question->laNhieuDapAn()): ?>
                        <input class="form-check-input" type="checkbox" name="answers[<?php echo e($question->id); ?>][]" value="<?php echo e($answer->id); ?>" id="a<?php echo e($answer->id); ?>">
                    <?php else: ?>
                        <input class="form-check-input" type="radio" name="answers[<?php echo e($question->id); ?>]" value="<?php echo e($answer->id); ?>" id="a<?php echo e($answer->id); ?>">
                    <?php endif; ?>
                    <label class="form-check-label" for="a<?php echo e($answer->id); ?>"><?php echo e($answer->answer_text); ?></label>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <button class="btn btn-primary btn-lg" onclick="return confirm('Bạn chắc chắn muốn nộp bài?')">Nộp bài</button>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Đồng hồ đếm ngược dựa trên giờ máy chủ; hết giờ tự động nộp bài.
    let remaining = <?php echo e(max(0, now()->diffInSeconds($hanNop, false))); ?>;
    const el = document.getElementById('timer');
    const form = document.getElementById('quiz-form');
    function tick() {
        const m = String(Math.floor(remaining / 60)).padStart(2, '0');
        const s = String(Math.floor(remaining % 60)).padStart(2, '0');
        el.textContent = m + ':' + s;
        if (remaining <= 60) el.classList.add('text-danger');
        if (remaining <= 0) { form.querySelector('button').removeAttribute('onclick'); form.submit(); return; }
        remaining -= 1;
        setTimeout(tick, 1000);
    }
    tick();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/quizzes/take.blade.php ENDPATH**/ ?>