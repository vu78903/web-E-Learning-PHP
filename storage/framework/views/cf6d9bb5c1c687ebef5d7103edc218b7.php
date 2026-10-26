
<div class="card course-card h-100 border-0 shadow-sm">
    <?php if($course->duongDanAnhBia()): ?>
        <img src="<?php echo e($course->duongDanAnhBia()); ?>" class="card-img-top course-thumb" alt="">
    <?php else: ?>
        <div class="thumb-fallback card-img-top"><i class="bi bi-journal-richtext"></i></div>
    <?php endif; ?>
    <div class="card-body d-flex flex-column">
        <span class="badge align-self-start mb-2" style="color:#4f45ca;background:#efedff"><?php echo e($course->category->name); ?></span>
        <h5 class="card-title"><a class="text-decoration-none stretched-link" href="<?php echo e(route('courses.show', $course)); ?>"><?php echo e($course->title); ?></a></h5>
        <p class="card-text text-muted small flex-grow-1"><?php echo e(\Illuminate\Support\Str::limit($course->description, 90)); ?></p>
        <div class="course-meta small text-muted">
            <span><i class="bi bi-person me-1"></i><?php echo e($course->instructor->name); ?></span>
            <span><i class="bi bi-people me-1"></i><?php echo e($course->enrollments_count ?? 0); ?></span>
        </div>
    </div>
</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/do an/elearning-hoan-thien/resources/views/partials/course-card.blade.php ENDPATH**/ ?>