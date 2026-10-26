
<div class="card shadow-sm border-0 sticky-side">
    <div class="card-body">
        <a href="<?php echo e(route('learn.show', $course)); ?>" class="fw-bold text-decoration-none"><?php echo e($course->title); ?></a>
        <?php if($laHocVien): ?>
            <div class="progress my-2"><div class="progress-bar" style="width:<?php echo e($progress); ?>%"></div></div>
            <div class="small text-muted"><?php echo e($progress); ?>% hoàn thành</div>
        <?php endif; ?>
    </div>
    <?php $__currentLoopData = $course->sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="px-3 py-2 bg-light fw-semibold small border-top"><?php echo e($section->title); ?></div>
        <div class="list-group list-group-flush">
            <?php $__currentLoopData = $section->lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('learn.lesson', [$course, $l])); ?>"
                   class="list-group-item list-group-item-action small <?php if(isset($current) && $current->id === $l->id): ?> active <?php endif; ?>">
                    <?php if(in_array($l->id, $completedIds)): ?> <i class="bi bi-check-circle-fill text-success"></i>
                    <?php else: ?> <i class="bi bi-circle"></i> <?php endif; ?>
                    <?php echo e($l->title); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php if($course->quizzes->isNotEmpty()): ?>
        <div class="px-3 py-2 bg-light fw-semibold small border-top">Kiểm tra</div>
        <div class="list-group list-group-flush">
            <?php $__currentLoopData = $course->quizzes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('quizzes.show', $q)); ?>" class="list-group-item list-group-item-action small">
                    <i class="bi bi-patch-question"></i> <?php echo e($q->title); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/learn/_sidebar.blade.php ENDPATH**/ ?>