<?php $__currentLoopData = ['success' => 'success', 'error' => 'danger', 'info' => 'info']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php if(session($key)): ?>
        <div class="alert alert-<?php echo e($type); ?> alert-dismissible fade show" role="alert">
            <?php echo e(session($key)); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php if($errors->any()): ?>
    <div class="alert alert-danger">
        <strong>Vui lòng kiểm tra lại:</strong>
        <ul class="mb-0">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/partials/flash.blade.php ENDPATH**/ ?>