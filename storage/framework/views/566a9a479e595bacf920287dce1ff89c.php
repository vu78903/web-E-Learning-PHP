<?php $__env->startSection('title', 'Danh mục'); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between mb-3">
    <h2 class="mb-0">Danh mục khóa học</h2>
    <a href="<?php echo e(route('admin.categories.create')); ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm danh mục</a>
</div>
<div class="card border-0 shadow-sm"><table class="table align-middle mb-0">
    <thead><tr><th>Tên</th><th>Mô tả</th><th>Số khóa học</th><th></th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr><td class="fw-semibold"><?php echo e($c->name); ?></td><td class="text-muted"><?php echo e($c->description); ?></td><td><?php echo e($c->courses_count); ?></td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('admin.categories.edit', $c)); ?>">Sửa</a>
                <form method="POST" action="<?php echo e(route('admin.categories.destroy', $c)); ?>" class="d-inline" onsubmit="return confirm('Xóa danh mục này?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-sm btn-outline-danger">Xóa</button></form>
            </td></tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="4" class="text-center text-muted py-3">Chưa có danh mục.</td></tr><?php endif; ?>
    </tbody>
</table></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/admin/categories/index.blade.php ENDPATH**/ ?>