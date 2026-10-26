<?php $__env->startSection('title', $lesson->exists ? 'Sửa bài học' : 'Thêm bài học'); ?>
<?php $__env->startSection('content'); ?>
<nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="<?php echo e(route('instructor.curriculum', $course)); ?>"><?php echo e($course->title); ?></a></li>
    <li class="breadcrumb-item"><?php echo e($section->title); ?></li>
</ol></nav>
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h3 class="mb-3"><?php echo e($lesson->exists ? 'Sửa bài học' : 'Thêm bài học'); ?></h3>
        <form method="POST" enctype="multipart/form-data" action="<?php echo e($lesson->exists ? route('instructor.lessons.update', $lesson) : route('instructor.lessons.store', $section)); ?>">
            <?php echo csrf_field(); ?> <?php if($lesson->exists): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>
            <div class="mb-3"><label class="form-label">Tiêu đề</label>
                <input id="lesson-title" name="title" value="<?php echo e(old('title', $lesson->title)); ?>" class="form-control" placeholder="Tự điền từ YouTube hoặc nhập thủ công"></div>
            <div class="mb-3"><label class="form-label">Nội dung văn bản</label>
                <textarea name="content" rows="8" class="form-control"><?php echo e(old('content', $lesson->content)); ?></textarea></div>
            <div class="mb-3"><label class="form-label">Liên kết video (YouTube/Vimeo)</label>
                <div class="input-group">
                    <input id="video-url" type="url" name="video_url" value="<?php echo e(old('video_url', $lesson->video_url)); ?>" class="form-control" placeholder="https://www.youtube.com/watch?v=...">
                    <button id="youtube-fetch" class="btn btn-outline-primary" type="button"><i class="bi bi-magic"></i> Lấy thông tin</button>
                </div>
                <div class="form-text">Link YouTube sẽ tự lấy tiêu đề và ảnh đại diện qua oEmbed, không cần API key.</div>
                <div id="youtube-error" class="text-danger small mt-1"></div>
            </div>
            <div id="youtube-preview" class="card border-0 bg-light mb-3 <?php echo e($lesson->video_thumbnail ? '' : 'd-none'); ?>">
                <div class="row g-0 align-items-center">
                    <div class="col-sm-4"><img id="youtube-thumbnail" src="<?php echo e($lesson->video_thumbnail); ?>" alt="" class="img-fluid rounded-start w-100" style="aspect-ratio:16/9;object-fit:cover"></div>
                    <div class="col-sm-8"><div class="card-body py-3">
                        <div class="small text-uppercase text-danger fw-semibold"><i class="bi bi-youtube"></i> YouTube</div>
                        <div id="youtube-title" class="fw-semibold"><?php echo e($lesson->video_title); ?></div>
                        <div id="youtube-author" class="small text-muted"></div>
                    </div></div>
                </div>
            </div>
            <div class="mb-3"><label class="form-label">Liên kết tài liệu (PDF, Google Drive...)</label>
                <input type="url" name="document_url" value="<?php echo e(old('document_url', $lesson->document_url)); ?>" class="form-control"></div>
            <div class="mb-3"><label class="form-label" for="attachments">Tài liệu PDF đính kèm</label>
                <input type="file" id="attachments" name="attachments[]" multiple accept="application/pdf,.pdf"
                       class="form-control <?php $__errorArgs = ['attachments'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> <?php $__errorArgs = ['attachments.*'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                <div class="form-text">Chọn một hoặc nhiều file PDF (tối đa <?php echo e(\App\Models\TaiLieuBaiHoc::MAX_FILES); ?> file/lần, mỗi file ≤ 10MB). Tài liệu chỉ học viên đã đăng ký mới xem được.</div>
                <?php $__errorArgs = ['attachments'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <?php $__currentLoopData = $errors->get('attachments.*'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $messages): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="invalid-feedback"><?php echo e($messages[0]); ?></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="mb-3" style="max-width:180px"><label class="form-label">Thứ tự</label>
                <input type="number" name="sort_order" value="<?php echo e(old('sort_order', $lesson->sort_order)); ?>" class="form-control" placeholder="Tự động"></div>
            <button class="btn btn-primary">Lưu</button>
            <a href="<?php echo e(route('instructor.curriculum', $course)); ?>" class="btn btn-link">Hủy</a>
        </form>
    </div></div>

    <?php if($lesson->exists): ?>
        <div class="card border-0 shadow-sm mt-3"><div class="card-body p-4">
            <h5 class="mb-3"><i class="bi bi-file-earmark-pdf text-danger"></i> Tài liệu đã đính kèm (<?php echo e($lesson->attachments->count()); ?>)</h5>
            <?php $__empty_1 = true; $__currentLoopData = $lesson->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="d-flex flex-wrap align-items-center gap-2 py-2 border-top">
                    <div class="flex-grow-1 text-break">
                        <i class="bi bi-file-earmark-pdf text-danger"></i> <?php echo e($attachment->original_name); ?>

                        <span class="text-muted small">(<?php echo e($attachment->dungLuongDeDoc()); ?>)</span>
                    </div>
                    <a class="btn btn-sm btn-outline-secondary" href="<?php echo e(route('attachments.view', $attachment)); ?>" target="_blank" rel="noopener">
                        <i class="bi bi-eye"></i> Xem</a>
                    <form method="POST" action="<?php echo e(route('instructor.attachments.destroy', $attachment)); ?>" class="d-inline"
                          onsubmit="return confirm('Xóa tài liệu này? Thao tác không thể hoàn tác.')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Xóa</button>
                    </form>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-muted mb-0">Chưa có tài liệu nào. Dùng ô "Tài liệu PDF đính kèm" ở trên để tải lên.</p>
            <?php endif; ?>
        </div></div>
    <?php endif; ?>
</div></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
(() => {
    const url = document.getElementById('video-url');
    const title = document.getElementById('lesson-title');
    const button = document.getElementById('youtube-fetch');
    const preview = document.getElementById('youtube-preview');
    const error = document.getElementById('youtube-error');
    let timer;

    async function layThongTin() {
        if (!url.value || !/(youtube\.com|youtu\.be)/i.test(url.value)) return;
        button.disabled = true;
        error.textContent = '';
        try {
            const endpoint = <?php echo json_encode(route('instructor.youtube.thong-tin'), 15, 512) ?> + '?url=' + encodeURIComponent(url.value);
            const response = await fetch(endpoint, {headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}});
            const data = await response.json();
            if (!response.ok) throw data;
            if (!title.value.trim()) title.value = data.tieu_de;
            document.getElementById('youtube-title').textContent = data.tieu_de;
            document.getElementById('youtube-thumbnail').src = data.anh_dai_dien;
            document.getElementById('youtube-author').textContent = data.tac_gia || '';
            preview.classList.remove('d-none');
        } catch (problem) {
            preview.classList.add('d-none');
            error.textContent = problem.message || 'Không lấy được thông tin video YouTube.';
        } finally {
            button.disabled = false;
        }
    }

    button.addEventListener('click', layThongTin);
    url.addEventListener('input', () => { clearTimeout(timer); timer = setTimeout(layThongTin, 500); });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/do an/elearning-hoan-thien/resources/views/instructor/curriculum/lesson-form.blade.php ENDPATH**/ ?>