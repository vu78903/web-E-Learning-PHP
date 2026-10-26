<?php $__env->startSection('title', 'Khóa học'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-heading">
    <div class="eyebrow">Kho tri thức</div>
    <h1>Khám phá khóa học</h1>
    <p class="text-muted mb-0">Tìm nội dung phù hợp với mục tiêu và nhịp học của bạn.</p>
</div>
<form method="GET" class="row g-2 surface-panel filter-panel">
    <div class="col-md-5"><input name="q" value="<?php echo e(request('q')); ?>" class="form-control" placeholder="Tìm theo tên hoặc mô tả..."></div>
    <div class="col-md-3">
        <select name="category" class="form-select">
            <option value="">Tất cả danh mục</option>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>" <?php if(request('category') == $c->id): echo 'selected'; endif; ?>><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="sort" class="form-select">
            <option value="newest" <?php if(request('sort') === 'newest'): echo 'selected'; endif; ?>>Mới nhất</option>
            <option value="popular" <?php if(request('sort') === 'popular'): echo 'selected'; endif; ?>>Nhiều học viên</option>
            <option value="title" <?php if(request('sort') === 'title'): echo 'selected'; endif; ?>>Theo tên A-Z</option>
        </select>
    </div>
    <div class="col-md-2 d-grid"><button class="btn btn-primary"><i class="bi bi-search me-1"></i> Tìm kiếm</button></div>
</form>

<div id="course-list"><?php echo $__env->make('courses._list', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>

<script>
// AJAX: tìm kiếm/lọc/phân trang không tải lại trang (vẫn hoạt động bình thường nếu tắt JavaScript).
(function () {
    const form = document.querySelector('form[method="GET"]');
    const box = document.getElementById('course-list');
    let timer;
    async function load(url) {
        box.style.opacity = .5;
        try {
            const res = await fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
            if (!res.ok) throw new Error(res.status);
            box.innerHTML = await res.text();
            history.replaceState(null, '', url);
        } catch (e) { window.location = url; }
        box.style.opacity = 1;
    }
    function submit() {
        const qs = new URLSearchParams(new FormData(form)).toString();
        load(form.action.split('?')[0] + '?' + qs);
    }
    form.addEventListener('submit', e => { e.preventDefault(); submit(); });
    form.querySelector('input[name=q]').addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(submit, 350); });
    form.querySelectorAll('select').forEach(el => el.addEventListener('change', submit));
    box.addEventListener('click', e => {
        const a = e.target.closest('.pagination a');
        if (a) { e.preventDefault(); load(a.href); }
    });
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/do an/elearning-hoan-thien/resources/views/courses/index.blade.php ENDPATH**/ ?>