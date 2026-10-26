<?php $__env->startSection('title', 'Chương & bài học'); ?>
<?php $__env->startSection('content'); ?>
<nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?php echo e(route('instructor.courses.index')); ?>">Khóa học</a></li>
    <li class="breadcrumb-item active"><?php echo e($course->title); ?></li>
</ol></nav>
<div class="d-flex justify-content-between mb-3">
    <h3 class="mb-0">Chương & bài học</h3>
    <a class="btn btn-outline-success" href="<?php echo e(route('instructor.quizzes.index', $course)); ?>">Quản lý bài kiểm tra</a>
</div>

<?php $__currentLoopData = $course->sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white">
            <form method="POST" action="<?php echo e(route('instructor.sections.update', $section)); ?>" class="row g-2 align-items-center">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="col"><input name="title" value="<?php echo e($section->title); ?>" class="form-control form-control-sm fw-semibold" required></div>
                <div class="col-auto" style="width:90px"><input type="number" name="sort_order" value="<?php echo e($section->sort_order); ?>" class="form-control form-control-sm" title="Thứ tự"></div>
                <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Lưu</button></div>
                <div class="col-auto">
                    <button form="del-section-<?php echo e($section->id); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa chương và toàn bộ bài học trong chương?')">Xóa</button>
                </div>
            </form>
            <form id="del-section-<?php echo e($section->id); ?>" method="POST" action="<?php echo e(route('instructor.sections.destroy', $section)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?></form>
        </div>
        <ul class="list-group list-group-flush">
            <?php $__currentLoopData = $section->lessons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lesson): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-play-circle text-primary"></i> <?php echo e($lesson->title); ?>

                        <?php if($lesson->video_url): ?><i class="bi bi-camera-video text-muted ms-1"></i><?php endif; ?>
                        <?php if($lesson->document_url): ?><i class="bi bi-file-earmark-text text-muted ms-1"></i><?php endif; ?>
                        <?php if($lesson->attachments_count): ?><span class="badge text-bg-light border ms-1" title="Tài liệu PDF"><i class="bi bi-file-earmark-pdf text-danger"></i> <?php echo e($lesson->attachments_count); ?></span><?php endif; ?></span>
                    <span class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="<?php echo e(route('instructor.lessons.edit', $lesson)); ?>">Sửa</a>
                        <form method="POST" action="<?php echo e(route('instructor.lessons.destroy', $lesson)); ?>" class="d-inline" onsubmit="return confirm('Xóa bài học này?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="btn btn-sm btn-outline-danger">Xóa</button></form>
                    </span>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <li class="list-group-item"><a href="<?php echo e(route('instructor.lessons.create', $section)); ?>" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Thêm bài học</a></li>
        </ul>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<div class="card border-0 shadow-sm"><div class="card-body">
    <form method="POST" action="<?php echo e(route('instructor.sections.store', $course)); ?>" class="row g-2"><?php echo csrf_field(); ?>
        <div class="col"><input name="title" class="form-control" placeholder="Tên chương mới..." required></div>
        <div class="col-auto"><button class="btn btn-success"><i class="bi bi-plus-lg"></i> Thêm chương</button></div>
    </form>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/do an/elearning-hoan-thien/resources/views/instructor/curriculum/index.blade.php ENDPATH**/ ?>