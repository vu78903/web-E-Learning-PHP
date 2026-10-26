
<?php
    $nguoiDung = auth()->user();
    $chungChiCuaToi = \App\Models\ChungChi::where('user_id', $nguoiDung->id)->where('course_id', $course->id)->first();
    $duDieuKienCC = ! $chungChiCuaToi && \App\ChucNang\KiemTraDieuKienChungChi::kiemTra($nguoiDung, $course)['du_dieu_kien'];
?>
<?php if($chungChiCuaToi): ?>
    <a href="<?php echo e(route('chung_chi.xem', $chungChiCuaToi)); ?>" class="btn btn-outline-success btn-sm mt-2"><i class="bi bi-award"></i> Xem chứng chỉ</a>
<?php elseif($duDieuKienCC): ?>
    <form method="POST" action="<?php echo e(route('chung_chi.nhan', $course)); ?>" class="mt-2"><?php echo csrf_field(); ?>
        <button class="btn btn-warning btn-sm w-100"><i class="bi bi-award-fill"></i> Nhận chứng chỉ</button>
    </form>
<?php elseif($course->progress === 100): ?>
    <a href="<?php echo e(route('learn.show', $course)); ?>#chung-chi" class="small text-muted mt-2">Còn bài kiểm tra chưa đạt để nhận chứng chỉ</a>
<?php endif; ?>
<?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/partials/nut-chung-chi.blade.php ENDPATH**/ ?>