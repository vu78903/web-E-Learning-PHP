<?php $__env->startSection('title', 'Quản lý khóa học'); ?>
<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0"><?php echo e(auth()->user()->laQuanTri() ? 'Tất cả khóa học (nội dung)' : 'Khóa học của tôi'); ?></h2>
    <?php if(auth()->user()->laGiangVien()): ?>
        <a href="<?php echo e(route('instructor.courses.create')); ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tạo khóa học</a>
    <?php endif; ?>
</div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Khóa học</th><th>Danh mục</th><?php if(auth()->user()->laQuanTri()): ?><th>Giảng viên</th><?php endif; ?><th>Trạng thái</th><th>HV</th><th>Bài học</th><th>Kiểm tra</th><th></th></tr></thead>
    <tbody>
    <?php $__empty_1 = true; $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
            <td><a href="<?php echo e(route('courses.show', $c)); ?>" class="fw-semibold text-decoration-none"><?php echo e($c->title); ?></a></td>
            <td><?php echo e($c->category->name); ?></td>
            <?php if(auth()->user()->laQuanTri()): ?><td><?php echo e($c->instructor->name); ?></td><?php endif; ?>
            <td><span class="badge text-bg-<?php echo e(['draft' => 'secondary', 'published' => 'success', 'hidden' => 'dark'][$c->status] ?? 'light'); ?>"><?php echo e($c->tenTrangThai()); ?></span></td>
            <td><?php echo e($c->enrollments_count); ?></td><td><?php echo e($c->lessons_count); ?></td><td><?php echo e($c->quizzes_count); ?></td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(route('instructor.curriculum', $c)); ?>">Bài học</a>
                <a class="btn btn-sm btn-outline-success" href="<?php echo e(route('instructor.quizzes.index', $c)); ?>">Kiểm tra</a>
                <a class="btn btn-sm btn-outline-info" href="<?php echo e(route('instructor.courses.students', $c)); ?>">Học viên</a>
                <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('instructor.courses.edit', $c)); ?>">Sửa</a>
                <form method="POST" action="<?php echo e(route('instructor.courses.destroy', $c)); ?>" class="d-inline" onsubmit="return confirm('Xóa khóa học này cùng toàn bộ nội dung?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-sm btn-outline-danger">Xóa</button></form>
            </td>
        </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">Chưa có khóa học nào.</td></tr>
    <?php endif; ?>
    </tbody>
</table></div></div>
<div class="mt-3"><?php echo e($courses->links('pagination::bootstrap-5')); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/do an/elearning-hoan-thien/resources/views/instructor/courses/index.blade.php ENDPATH**/ ?>