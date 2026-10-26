<section class="mt-5" id="khu-vuc-danh-gia">
    <div class="d-flex align-items-center gap-2 mb-3">
        <h4 class="mb-0">Đánh giá khóa học</h4>
        <span class="badge text-bg-warning" id="diem-danh-gia"><?php echo e(number_format((float) ($diemDanhGia ?? 0), 1)); ?> ★</span>
        <span class="text-muted small" id="so-luong-danh-gia">(<?php echo e($soLuongDanhGia ?? 0); ?> đánh giá)</span>
    </div>

    <?php if(auth()->guard()->check()): ?>
        <?php if(auth()->user()->laHocVien() && $enrolled): ?>
            <form id="form-danh-gia" action="<?php echo e(route('courses.reviews.store', $course)); ?>" class="card border-0 bg-light mb-3">
                <?php echo csrf_field(); ?>
                <div class="card-body">
                    <h6><?php echo e($danhGiaCuaToi ? 'Cập nhật đánh giá của bạn' : 'Viết đánh giá của bạn'); ?></h6>
                    <div class="mb-3">
                        <label class="form-label">Số sao</label>
                        <select name="so_sao" class="form-select" required>
                            <?php for($sao = 5; $sao >= 1; $sao--): ?>
                                <option value="<?php echo e($sao); ?>" <?php if(old('so_sao', $danhGiaCuaToi?->so_sao ?? 5) == $sao): echo 'selected'; endif; ?>><?php echo e($sao); ?> sao</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bình luận</label>
                        <textarea name="binh_luan" class="form-control" rows="3" maxlength="1000" required><?php echo e($danhGiaCuaToi?->binh_luan); ?></textarea>
                        <div class="invalid-feedback" id="loi-binh-luan"></div>
                    </div>
                    <button class="btn btn-primary" type="submit">Lưu đánh giá</button>
                    <span class="ms-2 small" id="thong-bao-danh-gia" role="status"></span>
                </div>
            </form>
        <?php endif; ?>
    <?php endif; ?>

    <div id="danh-sach-danh-gia">
        <?php echo $__env->make('danh-gia.danh-sach', ['danhGia' => $danhGia], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</section>

<?php if(auth()->user()?->laHocVien() && $enrolled): ?>
<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('form-danh-gia')?.addEventListener('submit', async function (event) {
    event.preventDefault();
    const form = event.currentTarget;
    const button = form.querySelector('button[type="submit"]');
    const notice = document.getElementById('thong-bao-danh-gia');
    button.disabled = true;
    notice.className = 'ms-2 small text-muted';
    notice.textContent = 'Đang lưu...';

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            body: new FormData(form),
        });
        const data = await response.json();
        if (!response.ok) throw data;

        document.getElementById('danh-sach-danh-gia').innerHTML = data.html;
        document.getElementById('diem-danh-gia').textContent = Number(data.trung_binh).toFixed(1) + ' ★';
        document.getElementById('so-luong-danh-gia').textContent = '(' + data.so_luong + ' đánh giá)';
        notice.className = 'ms-2 small text-success';
        notice.textContent = data.thong_bao;
    } catch (error) {
        const errors = error.errors ? Object.values(error.errors).flat().join(' ') : 'Không thể lưu đánh giá. Vui lòng thử lại.';
        notice.className = 'ms-2 small text-danger';
        notice.textContent = errors;
    } finally {
        button.disabled = false;
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php endif; ?>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/do an/elearning-hoan-thien/resources/views/danh-gia/khu-vuc.blade.php ENDPATH**/ ?>