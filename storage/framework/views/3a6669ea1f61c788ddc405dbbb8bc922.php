<div class="row g-4">
    <?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-md-6 col-lg-4"><?php echo $__env->make('partials.course-card', ['course' => $course], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12"><div class="alert alert-light border">Không tìm thấy khóa học phù hợp.</div></div>
    <?php endif; ?>
</div>
<div class="mt-4"><?php echo e($courses->links('pagination::bootstrap-5')); ?></div>
<?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/courses/_list.blade.php ENDPATH**/ ?>