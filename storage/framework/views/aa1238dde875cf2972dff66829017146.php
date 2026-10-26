<?php $__env->startSection('title', $course->exists ? 'Sửa khóa học' : 'Tạo khóa học'); ?>
<?php $__env->startSection('content'); ?>
<?php ($statuses = auth()->user()->laQuanTri() ? \App\Models\KhoaHoc::STATUSES : array_diff_key(\App\Models\KhoaHoc::STATUSES, ['hidden' => 1])); ?>
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h3 class="mb-3"><?php echo e($course->exists ? 'Sửa khóa học' : 'Tạo khóa học mới'); ?></h3>
        <form method="POST" enctype="multipart/form-data"
              action="<?php echo e($course->exists ? route('instructor.courses.update', $course) : route('instructor.courses.store')); ?>">
            <?php echo csrf_field(); ?> <?php if($course->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
            <div class="mb-3"><label class="form-label">Tên khóa học</label>
                <input name="title" value="<?php echo e(old('title', $course->title)); ?>" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Danh mục</label>
                <select name="category_id" class="form-select" required>
                    <option value="">-- Chọn danh mục --</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php if(old('category_id', $course->category_id) == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select></div>
            <div class="mb-3"><label class="form-label">Mô tả</label>
                <textarea name="description" rows="5" class="form-control"><?php echo e(old('description', $course->description)); ?></textarea></div>
            <div class="mb-3"><label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <?php $__currentLoopData = $statuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php if(old('status', $course->status) === $k): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <div class="form-text">Chỉ khóa học "Công khai" mới hiển thị cho học viên.</div></div>
            <div class="mb-3"><label class="form-label">Ảnh bìa (tùy chọn, tối đa 2MB)</label>
                <input type="file" name="thumbnail" accept="image/*" class="form-control">
                <?php if($course->duongDanAnhBia()): ?><img src="<?php echo e($course->duongDanAnhBia()); ?>" class="mt-2 rounded" style="max-height:100px" alt=""><?php endif; ?></div>
            <button class="btn btn-primary">Lưu</button>
            <a href="<?php echo e(route('instructor.courses.index')); ?>" class="btn btn-link">Hủy</a>
        </form>
    </div></div>
</div></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/do an/elearning-hoan-thien/resources/views/instructor/courses/form.blade.php ENDPATH**/ ?>