
<?php
    ['courses' => $courses, 'course' => $course, 'quiz' => $quiz, 'students' => $students, 'scores' => $scores, 'summary' => $summary] = $bangDiem;
    $quizzes = $bangDiem['quizzes'] ?? collect();
    $diem = fn ($d) => \App\ChucNang\XuatBaoCaoCsv::diem($d);
?>
<div class="col-12" id="bang-diem">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="fw-semibold"><i class="bi bi-table"></i> Bảng điểm học viên</span>
            <?php if($courses->isNotEmpty()): ?>
                <form method="GET" action="<?php echo e(url()->current()); ?>#bang-diem" class="d-flex flex-wrap gap-2" id="loc-bang-diem">
                    <select name="course_id" class="form-select form-select-sm" style="max-width:280px" aria-label="Khóa học"
                            onchange="this.form.quiz_id.value = ''; this.form.submit()">
                        <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c->id); ?>" <?php if($course?->id === $c->id): echo 'selected'; endif; ?>><?php echo e($c->title); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select name="quiz_id" class="form-select form-select-sm" style="max-width:240px" aria-label="Bài kiểm tra" onchange="this.form.submit()">
                        <option value="">Tất cả bài kiểm tra</option>
                        <?php $__currentLoopData = $course?->quizzes ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($q->id); ?>" <?php if($quiz?->id === $q->id): echo 'selected'; endif; ?>><?php echo e($q->title); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <noscript><button class="btn btn-sm btn-primary">Xem</button></noscript>
                </form>
            <?php endif; ?>
        </div>

        <?php if(! $course): ?>
            <div class="card-body text-muted">Chưa có khóa học nào.</div>
        <?php elseif($course->quizzes->isEmpty()): ?>
            <div class="card-body text-muted">Khóa học này chưa có bài kiểm tra.</div>
        <?php else: ?>
            <div class="card-body pb-2">
                <div class="row g-2 text-center small mb-2">
                    <div class="col-6 col-md-3"><div class="bg-light rounded p-2"><div class="text-muted">Học viên đã làm</div>
                        <div class="fs-5 fw-bold"><?php echo e($summary['done']); ?>/<?php echo e($summary['students']); ?></div></div></div>
                    <div class="col-6 col-md-3"><div class="bg-light rounded p-2"><div class="text-muted">Lượt nộp</div>
                        <div class="fs-5 fw-bold"><?php echo e($summary['attempts']); ?></div></div></div>
                    <div class="col-6 col-md-3"><div class="bg-light rounded p-2"><div class="text-muted">Điểm TB (cao nhất)</div>
                        <div class="fs-5 fw-bold"><?php echo e($summary['avg'] !== null ? number_format($summary['avg'], 1) : '—'); ?></div></div></div>
                    <div class="col-6 col-md-3"><div class="bg-light rounded p-2"><div class="text-muted">Tỷ lệ đạt</div>
                        <div class="fs-5 fw-bold"><?php echo e($summary['pass_rate'] !== null ? $summary['pass_rate'].'%' : '—'); ?></div></div></div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="<?php echo e(route('instructor.csv.bang_diem', $course)); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-filetype-csv"></i> Bảng điểm cả khóa (CSV)</a>
                    <?php if($quiz): ?>
                        <a href="<?php echo e(route('instructor.csv.ket_qua', $quiz)); ?>" class="btn btn-outline-success btn-sm"><i class="bi bi-filetype-csv"></i> Kết quả bài này (CSV)</a>
                        <a href="<?php echo e(route('instructor.quizzes.results', $quiz)); ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-list-check"></i> Từng lượt nộp</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="table-responsive"><table class="table table-hover align-middle mb-0 small">
                <?php if($quiz): ?>
                    <thead><tr><th>Học viên</th><th>Email</th><th class="text-center">Số lượt</th><th class="text-center">Điểm cao nhất</th><th>Kết quả</th><th>Nộp lần cuối</th></tr></thead>
                    <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $o = $scores->get($s->id.'-'.$quiz->id); ?>
                        <tr><td><?php echo e($s->name); ?></td><td class="text-muted"><?php echo e($s->email); ?></td>
                            <td class="text-center"><?php echo e($o['attempts'] ?? 0); ?></td>
                            <td class="text-center fw-semibold"><?php echo e($o ? $diem($o['best']) : '—'); ?></td>
                            <td><?php if($o): ?><span class="badge text-bg-<?php echo e($o['passed'] ? 'success' : 'danger'); ?>"><?php echo e($o['passed'] ? 'Đạt' : 'Chưa đạt'); ?></span>
                                <?php else: ?><span class="badge text-bg-light text-muted">Chưa làm</span><?php endif; ?></td>
                            <td><?php echo e($o ? \Illuminate\Support\Carbon::parse($o['last'])->format('d/m/Y H:i') : ''); ?></td></tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="6" class="text-muted text-center py-3">Khóa học chưa có học viên.</td></tr><?php endif; ?>
                    </tbody>
                <?php else: ?>
                    <thead><tr><th>Học viên</th>
                        <?php $__currentLoopData = $quizzes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="text-center" style="min-width:110px"><a href="<?php echo e(request()->fullUrlWithQuery(['quiz_id' => $q->id, 'trang_diem' => null])); ?>#bang-diem"
                                class="text-reset" title="Xem riêng bài <?php echo e($q->title); ?>"><?php echo e(\Illuminate\Support\Str::limit($q->title, 28)); ?></a></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <th class="text-center">Điểm TB</th><th class="text-center">Số bài đạt</th></tr></thead>
                    <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $tb = \App\ChucNang\BangDiemHocVien::diemTrungBinh($scores, $s->id, $quizzes); $dat = 0; ?>
                        <tr><td><div><?php echo e($s->name); ?></div><div class="text-muted"><?php echo e($s->email); ?></div></td>
                            <?php $__currentLoopData = $quizzes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $o = $scores->get($s->id.'-'.$q->id); $dat += ($o['passed'] ?? false) ? 1 : 0; ?>
                                <td class="text-center">
                                    <?php if($o): ?>
                                        <span class="fw-semibold text-<?php echo e($o['passed'] ? 'success' : 'danger'); ?>"><?php echo e($diem($o['best'])); ?></span>
                                        <div class="text-muted" style="font-size:.75rem"><?php echo e($o['attempts']); ?> lượt</div>
                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <td class="text-center fw-semibold"><?php echo e($tb !== null ? number_format($tb, 1) : '—'); ?></td>
                            <td class="text-center"><?php echo e($dat); ?>/<?php echo e($quizzes->count()); ?></td></tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><tr><td colspan="<?php echo e($quizzes->count() + 3); ?>" class="text-muted text-center py-3">Khóa học chưa có học viên.</td></tr><?php endif; ?>
                    </tbody>
                <?php endif; ?>
            </table></div>
            <div class="card-footer bg-white small text-muted d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span>Điểm thang 100, lấy lượt cao nhất của mỗi học viên; xanh = đạt, đỏ = chưa đạt.</span>
                <?php if($students->hasPages()): ?><?php echo e($students->links('pagination::bootstrap-5')); ?><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/partials/bang-diem-hoc-vien.blade.php ENDPATH**/ ?>