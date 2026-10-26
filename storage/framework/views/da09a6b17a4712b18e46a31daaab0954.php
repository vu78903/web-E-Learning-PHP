<?php $__env->startSection('title', $category->exists ? 'Sửa danh mục' : 'Thêm danh mục'); ?>
<?php $__env->startSection('content'); ?>
<div class="row justify-content-center"><div class="col-lg-6">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h3 class="mb-3"><?php echo e($category->exists ? 'Sửa danh mục' : 'Thêm danh mục'); ?></h3>
        <form method="POST" action="<?php echo e($category->exists ? route('admin.categories.update', $category) : route('admin.categories.store')); ?>">
            <?php echo csrf_field(); ?> <?php if($category->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
            <div class="mb-3"><label class="form-label">Tên danh mục</label><input name="name" value="<?php echo e(old('name', $category->name)); ?>" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Mô tả</label><textarea name="description" rows="3" class="form-control"><?php echo e(old('description', $category->description)); ?></textarea></div>
            <button class="btn btn-primary">Lưu</button>
            <a href="<?php echo e(route('admin.categories.index')); ?>" class="btn btn-link">Hủy</a>
        </form>
    </div></div>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/do an/elearning-hoan-thien/resources/views/admin/categories/form.blade.php ENDPATH**/ ?>