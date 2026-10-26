{{-- 3 thẻ biểu đồ dashboard (đặt trong một .row). Cần biến $charts từ App\ChucNang\DuLieuBieuDo. --}}
@php
    $chartCards = [
        ['weekly', 'col-lg-8', 'graph-up', 'Lượt đăng ký theo tuần', count($charts['weekly']['labels']).' tuần gần nhất', array_sum($charts['weekly']['data']) > 0],
        ['scores', 'col-lg-4', 'bar-chart', 'Phân bố điểm', 'thang 10 · theo lượt nộp bài', array_sum($charts['scores']['data']) > 0],
        ['top', 'col-lg-6', 'trophy', 'Top khóa học nhiều lượt đăng ký', 'tối đa 10 khóa học', count($charts['top']['data']) > 0],
    ];
@endphp
@foreach($chartCards as [$key, $col, $icon, $title, $sub, $hasData])
    <div class="{{ $col }}">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-baseline gap-1">
                <span class="fw-semibold"><i class="bi bi-{{ $icon }} me-1"></i>{{ $title }}</span>
                <span class="small text-muted">{{ $sub }}</span>
            </div>
            <div class="card-body">
                @if($hasData)
                    <div class="chart-box chart-box-{{ $key }}"><canvas id="chart-{{ $key }}" role="img" aria-label="{{ $title }}"></canvas></div>
                @else
                    <div class="chart-box d-flex flex-column align-items-center justify-content-center text-muted">
                        <i class="bi bi-inbox fs-1"></i><span>Chưa có dữ liệu.</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endforeach

@push('styles')
<style>
    .chart-box { position: relative; height: 280px; }
    @media (max-width: 575.98px) { .chart-box { height: 240px; } }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
    if (typeof Chart === 'undefined') return;
    const charts = @json($charts);
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
@endpush
