{{-- Bảng điểm học viên trên dashboard. Biến: $bangDiem (App\ChucNang\BangDiemHocVien::thanhMang) --}}
@php
    ['courses' => $courses, 'course' => $course, 'quiz' => $quiz, 'students' => $students, 'scores' => $scores, 'summary' => $summary] = $bangDiem;
    $quizzes = $bangDiem['quizzes'] ?? collect();
    $diem = fn ($d) => \App\ChucNang\XuatBaoCaoCsv::diem($d);
@endphp
<div class="col-12" id="bang-diem">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="fw-semibold"><i class="bi bi-table"></i> Bảng điểm học viên</span>
            @if($courses->isNotEmpty())
                <form method="GET" action="{{ url()->current() }}#bang-diem" class="d-flex flex-wrap gap-2" id="loc-bang-diem">
                    <select name="course_id" class="form-select form-select-sm" style="max-width:280px" aria-label="Khóa học"
                            onchange="this.form.quiz_id.value = ''; this.form.submit()">
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" @selected($course?->id === $c->id)>{{ $c->title }}</option>
                        @endforeach
                    </select>
                    <select name="quiz_id" class="form-select form-select-sm" style="max-width:240px" aria-label="Bài kiểm tra" onchange="this.form.submit()">
                        <option value="">Tất cả bài kiểm tra</option>
                        @foreach($course?->quizzes ?? [] as $q)
                            <option value="{{ $q->id }}" @selected($quiz?->id === $q->id)>{{ $q->title }}</option>
                        @endforeach
                    </select>
                    <noscript><button class="btn btn-sm btn-primary">Xem</button></noscript>
                </form>
            @endif
        </div>

        @if(! $course)
            <div class="card-body text-muted">Chưa có khóa học nào.</div>
        @elseif($course->quizzes->isEmpty())
            <div class="card-body text-muted">Khóa học này chưa có bài kiểm tra.</div>
        @else
            <div class="card-body pb-2">
                <div class="row g-2 text-center small mb-2">
                    <div class="col-6 col-md-3"><div class="bg-light rounded p-2"><div class="text-muted">Học viên đã làm</div>
                        <div class="fs-5 fw-bold">{{ $summary['done'] }}/{{ $summary['students'] }}</div></div></div>
                    <div class="col-6 col-md-3"><div class="bg-light rounded p-2"><div class="text-muted">Lượt nộp</div>
                        <div class="fs-5 fw-bold">{{ $summary['attempts'] }}</div></div></div>
                    <div class="col-6 col-md-3"><div class="bg-light rounded p-2"><div class="text-muted">Điểm TB (cao nhất)</div>
                        <div class="fs-5 fw-bold">{{ $summary['avg'] !== null ? number_format($summary['avg'], 1) : '—' }}</div></div></div>
                    <div class="col-6 col-md-3"><div class="bg-light rounded p-2"><div class="text-muted">Tỷ lệ đạt</div>
                        <div class="fs-5 fw-bold">{{ $summary['pass_rate'] !== null ? $summary['pass_rate'].'%' : '—' }}</div></div></div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('instructor.csv.bang_diem', $course) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-filetype-csv"></i> Bảng điểm cả khóa (CSV)</a>
                    @if($quiz)
                        <a href="{{ route('instructor.csv.ket_qua', $quiz) }}" class="btn btn-outline-success btn-sm"><i class="bi bi-filetype-csv"></i> Kết quả bài này (CSV)</a>
                        <a href="{{ route('instructor.quizzes.results', $quiz) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-list-check"></i> Từng lượt nộp</a>
                    @endif
                </div>
            </div>

            <div class="table-responsive"><table class="table table-hover align-middle mb-0 small">
                @if($quiz)
                    <thead><tr><th>Học viên</th><th>Email</th><th class="text-center">Số lượt</th><th class="text-center">Điểm cao nhất</th><th>Kết quả</th><th>Nộp lần cuối</th></tr></thead>
                    <tbody>
                    @forelse($students as $s)
                        @php $o = $scores->get($s->id.'-'.$quiz->id); @endphp
                        <tr><td>{{ $s->name }}</td><td class="text-muted">{{ $s->email }}</td>
                            <td class="text-center">{{ $o['attempts'] ?? 0 }}</td>
                            <td class="text-center fw-semibold">{{ $o ? $diem($o['best']) : '—' }}</td>
                            <td>@if($o)<span class="badge text-bg-{{ $o['passed'] ? 'success' : 'danger' }}">{{ $o['passed'] ? 'Đạt' : 'Chưa đạt' }}</span>
                                @else<span class="badge text-bg-light text-muted">Chưa làm</span>@endif</td>
                            <td>{{ $o ? \Illuminate\Support\Carbon::parse($o['last'])->format('d/m/Y H:i') : '' }}</td></tr>
                    @empty<tr><td colspan="6" class="text-muted text-center py-3">Khóa học chưa có học viên.</td></tr>@endforelse
                    </tbody>
                @else
                    <thead><tr><th>Học viên</th>
                        @foreach($quizzes as $q)
                            <th class="text-center" style="min-width:110px"><a href="{{ request()->fullUrlWithQuery(['quiz_id' => $q->id, 'trang_diem' => null]) }}#bang-diem"
                                class="text-reset" title="Xem riêng bài {{ $q->title }}">{{ \Illuminate\Support\Str::limit($q->title, 28) }}</a></th>
                        @endforeach
                        <th class="text-center">Điểm TB</th><th class="text-center">Số bài đạt</th></tr></thead>
                    <tbody>
                    @forelse($students as $s)
                        @php $tb = \App\ChucNang\BangDiemHocVien::diemTrungBinh($scores, $s->id, $quizzes); $dat = 0; @endphp
                        <tr><td><div>{{ $s->name }}</div><div class="text-muted">{{ $s->email }}</div></td>
                            @foreach($quizzes as $q)
                                @php $o = $scores->get($s->id.'-'.$q->id); $dat += ($o['passed'] ?? false) ? 1 : 0; @endphp
                                <td class="text-center">
                                    @if($o)
                                        <span class="fw-semibold text-{{ $o['passed'] ? 'success' : 'danger' }}">{{ $diem($o['best']) }}</span>
                                        <div class="text-muted" style="font-size:.75rem">{{ $o['attempts'] }} lượt</div>
                                    @else<span class="text-muted">—</span>@endif
                                </td>
                            @endforeach
                            <td class="text-center fw-semibold">{{ $tb !== null ? number_format($tb, 1) : '—' }}</td>
                            <td class="text-center">{{ $dat }}/{{ $quizzes->count() }}</td></tr>
                    @empty<tr><td colspan="{{ $quizzes->count() + 3 }}" class="text-muted text-center py-3">Khóa học chưa có học viên.</td></tr>@endforelse
                    </tbody>
                @endif
            </table></div>
            <div class="card-footer bg-white small text-muted d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span>Điểm thang 100, lấy lượt cao nhất của mỗi học viên; xanh = đạt, đỏ = chưa đạt.</span>
                @if($students->hasPages()){{ $students->links('pagination::bootstrap-5') }}@endif
            </div>
        @endif
    </div>
</div>
