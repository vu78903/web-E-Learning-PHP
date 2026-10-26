<?php $__empty_1 = true; $__currentLoopData = $danhGia; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="border-bottom py-3">
        <div class="d-flex justify-content-between gap-3">
            <strong><?php echo e($item->user->name); ?></strong>
            <small class="text-muted"><?php echo e($item->updated_at->format('d/m/Y H:i')); ?></small>
        </div>
        <div class="text-warning" aria-label="<?php echo e($item->so_sao); ?> trên 5 sao">
            <?php for($i = 1; $i <= 5; $i++): ?><i class="bi <?php echo e($i <= $item->so_sao ? 'bi-star-fill' : 'bi-star'); ?>"></i><?php endfor; ?>
        </div>
        <div class="mt-1" style="white-space:pre-line"><?php echo e($item->binh_luan); ?></div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <p class="text-muted mb-0">Chưa có đánh giá nào cho khóa học này.</p>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/do an/elearning-hoan-thien/resources/views/danh-gia/danh-sach.blade.php ENDPATH**/ ?>