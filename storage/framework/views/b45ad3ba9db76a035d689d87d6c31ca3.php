
<?php
    $nguoiDung = auth()->user();
    $chungChiCuaToi = $nguoiDung?->laHocVien()
        ? \App\Models\ChungChi::where('user_id', $nguoiDung->id)->where('course_id', $course->id)->first()
        : null;
    $dieuKienCC = $nguoiDung?->laHocVien() && ! $chungChiCuaToi
        ? \App\ChucNang\KiemTraDieuKienChungChi::kiemTra($nguoiDung, $course)
        : null;
?>
<?php if($nguoiDung?->laHocVien()): ?>
<div class="card border-0 shadow-sm mt-3" id="chung-chi">
    <div class="card-body">
        <h5 class="mb-2"><i class="bi bi-award text-warning"></i> Chứng chỉ hoàn thành</h5>
        <?php if($chungChiCuaToi): ?>
            <p class="mb-2">Bạn đã nhận chứng chỉ ngày <?php echo e($chungChiCuaToi->cap_luc->format('d/m/Y')); ?>

                (mã <code><?php echo e($chungChiCuaToi->ma_chung_chi); ?></code>).</p>
            <a href="<?php echo e(route('chung_chi.xem', $chungChiCuaToi)); ?>" class="btn btn-success"><i class="bi bi-printer"></i> Xem / In chứng chỉ</a>
        <?php elseif($dieuKienCC['du_dieu_kien']): ?>
            <p class="mb-2">Bạn đã đủ điều kiện nhận chứng chỉ cho khóa học này.</p>
            <form method="POST" action="<?php echo e(route('chung_chi.nhan', $course)); ?>"><?php echo csrf_field(); ?>
                <button class="btn btn-warning"><i class="bi bi-award-fill"></i> Nhận chứng chỉ</button>
            </form>
        <?php else: ?>
            <p class="small text-muted mb-2">Hoàn thành các điều kiện sau để nhận chứng chỉ (in được hoặc lưu PDF):</p>
            <ul class="list-unstyled small mb-0">
                <li>
                    <i class="bi <?php echo e($dieuKienCC['tien_do'] === 100 ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary'); ?>"></i>
                    Hoàn thành 100% bài học (hiện tại <?php echo e($dieuKienCC['tien_do']); ?>%)
                </li>
                <?php $__currentLoopData = $dieuKienCC['bai_kiem_tra']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bai): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <i class="bi <?php echo e($bai['dat'] ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary'); ?>"></i>
                        Đạt bài kiểm tra "<?php echo e($bai['ten']); ?>"
                        <?php if($bai['diem'] !== null): ?><span class="text-muted">(điểm cao nhất <?php echo e(rtrim(rtrim(number_format($bai['diem'], 2), '0'), '.')); ?>)</span><?php endif; ?>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/partials/the-chung-chi.blade.php ENDPATH**/ ?>