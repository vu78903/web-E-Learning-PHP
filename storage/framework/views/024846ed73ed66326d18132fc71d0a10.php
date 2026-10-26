<?php $__env->startSection('title', $lesson->title); ?>
<?php $__env->startSection('content'); ?>
<?php ($current = $lesson); ?>
<div class="row g-4">
    <div class="col-lg-4"><?php echo $__env->make('learn._sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
    <div class="col-lg-8">
        <div class="card shadow-sm border-0"><div class="card-body p-4">
            <div class="text-muted small mb-1"><?php echo e($lesson->section->title); ?></div>
            <h3 class="mb-3"><?php echo e($lesson->title); ?></h3>

            <?php if($lesson->duongDanNhungVideo()): ?>
                <div class="ratio ratio-16x9 mb-3">
                    <iframe src="<?php echo e($lesson->duongDanNhungVideo()); ?>" allowfullscreen loading="lazy" title="<?php echo e($lesson->title); ?>"></iframe>
                </div>
            <?php elseif($lesson->video_url): ?>
                <p><a href="<?php echo e($lesson->video_url); ?>" target="_blank" rel="noopener noreferrer"><i class="bi bi-camera-video"></i> Mở video bài học</a></p>
            <?php endif; ?>

            <?php if($lesson->content): ?>
                <div class="lesson-content"><?php echo nl2br(e($lesson->content)); ?></div>
            <?php endif; ?>

            <?php if($lesson->document_url): ?>
                <p class="mt-3"><a class="btn btn-outline-secondary btn-sm" href="<?php echo e($lesson->document_url); ?>" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-file-earmark-text"></i> Tài liệu đính kèm</a></p>
            <?php endif; ?>

            <?php if($lesson->attachments->isNotEmpty()): ?>
                <h5 class="mt-4 mb-2"><i class="bi bi-file-earmark-pdf text-danger"></i> Tài liệu bài học</h5>
                <ul class="list-group mb-3">
                    <?php $__currentLoopData = $lesson->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="list-group-item">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <div class="flex-grow-1 text-break">
                                    <i class="bi bi-file-earmark-pdf text-danger"></i> <?php echo e($attachment->original_name); ?>

                                    <span class="text-muted small">(<?php echo e($attachment->dungLuongDeDoc()); ?>)</span>
                                </div>
                                <div class="d-flex flex-wrap gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary d-none d-md-inline-block js-pdf-toggle"
                                            data-bs-toggle="collapse" data-bs-target="#pdf-preview-<?php echo e($attachment->id); ?>" aria-expanded="false">
                                        <i class="bi bi-layout-text-window"></i> Xem nhanh</button>
                                    <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(route('attachments.view', $attachment)); ?>" target="_blank" rel="noopener">
                                        <i class="bi bi-box-arrow-up-right"></i> Mở</a>
                                    <a class="btn btn-sm btn-primary" href="<?php echo e(route('attachments.download', $attachment)); ?>">
                                        <i class="bi bi-download"></i> Tải về</a>
                                </div>
                            </div>
                            <div class="collapse mt-2" id="pdf-preview-<?php echo e($attachment->id); ?>">
                                <div class="ratio" style="--bs-aspect-ratio: 120%;">
                                    <iframe data-src="<?php echo e(route('attachments.view', $attachment)); ?>" title="<?php echo e($attachment->original_name); ?>" class="border rounded"></iframe>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>

            <hr>
            <div class="d-flex flex-wrap gap-2 justify-content-between">
                <div>
                    <?php if($prev): ?><a class="btn btn-outline-secondary" href="<?php echo e(route('learn.lesson', [$course, $prev])); ?>">&laquo; Bài trước</a><?php endif; ?>
                </div>
                <div class="d-flex gap-2">
                    <?php if($laHocVien): ?>
                        <?php ($done = in_array($lesson->id, $completedIds)); ?>
                        <form method="POST" action="<?php echo e(route('lessons.complete', $lesson)); ?>"><?php echo csrf_field(); ?>
                            <button class="btn <?php echo e($done ? 'btn-success' : 'btn-outline-success'); ?>">
                                <i class="bi bi-check2-circle"></i> <?php echo e($done ? 'Đã hoàn thành' : 'Đánh dấu hoàn thành'); ?>

                            </button>
                        </form>
                    <?php endif; ?>
                    <?php if($next): ?><a class="btn btn-primary" href="<?php echo e(route('learn.lesson', [$course, $next])); ?>">Bài tiếp &raquo;</a><?php endif; ?>
                </div>
            </div>
        </div></div>

        <?php if($laHocVien && $progress === 100): ?>
            <div class="alert alert-success mt-3">
                <i class="bi bi-trophy-fill"></i>
                Bạn đã hoàn thành khóa học.
                <a class="alert-link" href="<?php echo e(route('learn.show', $course)); ?>#khu-vuc-danh-gia">Đánh giá khóa học ngay</a>.
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    // Chỉ tải PDF vào khung xem khi người dùng mở "Xem nhanh".
    document.querySelectorAll('[id^="pdf-preview-"]').forEach(function (el) {
        el.addEventListener('show.bs.collapse', function () {
            var frame = el.querySelector('iframe[data-src]');
            if (frame && !frame.getAttribute('src')) frame.setAttribute('src', frame.dataset.src);
        });
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/learn/lesson.blade.php ENDPATH**/ ?>