<?php $__env->startSection('title', 'Quản lý khóa học (admin)'); ?>
<?php $__env->startSection('content'); ?>
<h2 class="mb-3">Quản lý khóa học</h2>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4"><input name="q" value="<?php echo e(request('q')); ?>" class="form-control" placeholder="Tìm theo tên khóa học"></div>
    <div class="col-md-3"><select name="status" class="form-select"><option value="">Mọi trạng thái</option>
        <?php $__currentLoopData = \App\Models\KhoaHoc::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(request('status') === $k): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
    <div class="col-md-3"><select name="category" class="form-select"><option value="">Mọi danh mục</option>
        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php if(request('category') == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
    <div class="col-md-2 d-grid"><button class="btn btn-primary">Lọc</button></div>
</form>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Khóa học</th><th>Danh mục</th><th>Giảng viên</th><th>HV</th><th style="width:230px">Trạng thái</th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td><a class="text-decoration-none fw-semibold" href="<?php echo e(route('courses.show', $c)); ?>"><?php echo e($c->title); ?></a></td>
            <td><?php echo e($c->category->name); ?></td><td><?php echo e($c->instructor->name); ?></td><td><?php echo e($c->enrollments_count); ?></td>
            <td>
                <form method="POST" action="<?php echo e(route('admin.courses.status', $c)); ?>" class="input-group input-group-sm">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <select name="status" class="form-select">
                        <?php $__currentLoopData = \App\Models\KhoaHoc::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if($c->status === $k): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button class="btn btn-outline-primary">Đổi</button>
                </form>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="5" class="text-center text-muted py-3">Không có khóa học.</td></tr><?php endif; ?>
    </tbody>
</table></div></div>
<div class="mt-3"><?php echo e($courses->links('pagination::bootstrap-5')); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/do an/elearning-hoan-thien/resources/views/admin/courses/index.blade.php ENDPATH**/ ?>