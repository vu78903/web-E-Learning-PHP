
<?php
    $chartCards = [
        ['weekly', 'col-lg-8', 'graph-up', 'Lượt đăng ký theo tuần', count($charts['weekly']['labels']).' tuần gần nhất', array_sum($charts['weekly']['data']) > 0],
        ['scores', 'col-lg-4', 'bar-chart', 'Phân bố điểm', 'thang 10 · theo lượt nộp bài', array_sum($charts['scores']['data']) > 0],
        ['top', 'col-lg-6', 'trophy', 'Top khóa học nhiều lượt đăng ký', 'tối đa 10 khóa học', count($charts['top']['data']) > 0],
    ];
?>
<?php $__currentLoopData = $chartCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$key, $col, $icon, $title, $sub, $hasData]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="<?php echo e($col); ?>">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-baseline gap-1">
                <span class="fw-semibold"><i class="bi bi-<?php echo e($icon); ?> me-1"></i><?php echo e($title); ?></span>
                <span class="small text-muted"><?php echo e($sub); ?></span>
            </div>
            <div class="card-body">
                <?php if($hasData): ?>
                    <div class="chart-box chart-box-<?php echo e($key); ?>"><canvas id="chart-<?php echo e($key); ?>" role="img" aria-label="<?php echo e($title); ?>"></canvas></div>
                <?php else: ?>
                    <div class="chart-box d-flex flex-column align-items-center justify-content-center text-muted">
                        <i class="bi bi-inbox fs-1"></i><span>Chưa có dữ liệu.</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .chart-box { position: relative; height: 280px; }
    @media (max-width: 575.98px) { .chart-box { height: 240px; } }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
    if (typeof Chart === 'undefined') return;
    const charts = <?php echo json_encode($charts, 15, 512) ?>;
    const intTicks = { precision: 0 };
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.plugins.legend.display = false;

    const weekly = document.getElementById('chart-weekly');
    if (weekly) new Chart(weekly, {
        type: 'line',
        data: { labels: charts.weekly.labels, datasets: [{
            label: 'Lượt đăng ký', data: charts.weekly.data, cubicInterpolationMode: 'monotone', fill: true,
            borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,.12)', pointRadius: 3,
        }] },
        options: { maintainAspectRatio: false, scales: {
            y: { beginAtZero: true, ticks: intTicks, title: { display: true, text: 'Lượt đăng ký' } },
            x: { ticks: { maxRotation: 45, autoSkip: true } },
        } },
    });

    const scores = document.getElementById('chart-scores');
    if (scores) new Chart(scores, {
        type: 'bar',
        data: { labels: charts.scores.labels, datasets: [{
            label: 'Lượt nộp bài', data: charts.scores.data, categoryPercentage: 1, barPercentage: .95,
            backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#198754'],
        }] },
        options: { maintainAspectRatio: false, scales: {
            y: { beginAtZero: true, ticks: intTicks, title: { display: true, text: 'Số lượt' } },
            x: { title: { display: true, text: 'Khoảng điểm' } },
        } },
    });

    const top = document.getElementById('chart-top');
    if (top) new Chart(top, {
        type: 'bar',
        data: { labels: charts.top.labels, datasets: [{
            label: 'Lượt đăng ký', data: charts.top.data, backgroundColor: '#6610f2', maxBarThickness: 28,
        }] },
        options: {
            indexAxis: 'y', maintainAspectRatio: false,
            scales: {
                x: { beginAtZero: true, ticks: intTicks },
                y: { ticks: { callback(v) {
                    const t = this.getLabelForValue(v), max = window.innerWidth < 576 ? 16 : 30;
                    return t.length > max ? t.slice(0, max - 1) + '…' : t;
                } } },
            },
            plugins: { tooltip: { callbacks: {
                afterLabel: ctx => charts.top.instructors ? 'Giảng viên: ' + (charts.top.instructors[ctx.dataIndex] ?? '—') : '',
            } } },
        },
    });
})();
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\laragon\www\web-E-Learning-PHP\resources\views/partials/dashboard-charts.blade.php ENDPATH**/ ?>