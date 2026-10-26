@extends('layouts.app')
@section('title', $quiz->title)
@section('content')
<nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('learn.show', $course) }}">{{ $course->title }}</a></li>
    <li class="breadcrumb-item active">{{ $quiz->title }}</li>
</ol></nav>
<div class="card shadow-sm border-0 mb-4"><div class="card-body p-4">
    <h3>{{ $quiz->title }}</h3>
    <ul class="list-unstyled text-muted">
        <li><i class="bi bi-list-ol"></i> {{ $quiz->questions_count }} câu hỏi</li>
        <li><i class="bi bi-clock"></i> Thời gian: {{ $quiz->duration_minutes }} phút</li>
        <li><i class="bi bi-trophy"></i> Điểm đạt: {{ $quiz->pass_score }}%</li>
        <li><i class="bi bi-arrow-repeat"></i> Số lần làm: {{ $quiz->coGioiHanLuotLam() ? 'tối đa '.$quiz->max_attempts.' lần' : 'không giới hạn' }}</li>
        @if($quiz->shuffle_questions)<li><i class="bi bi-shuffle"></i> Câu hỏi và phương án được xáo trộn mỗi lượt</li>@endif
    </ul>
    @if(auth()->user()->laHocVien())
        @php($inProgress = $attempts->contains(fn ($a) => ! $a->daNop()))
        @if($quiz->coGioiHanLuotLam())
            <div class="alert alert-{{ $remaining > 0 || $inProgress ? 'info' : 'warning' }} py-2">
                Bạn đã làm <strong>{{ $attempts->count() }}/{{ $quiz->max_attempts }}</strong> lượt — còn lại <strong>{{ $remaining }}</strong> lượt.
                @if($remaining === 0 && ! $inProgress) Bạn đã hết lượt làm bài này. @endif
            </div>
        @endif
        @if($quiz->questions_count > 0)
            @if($inProgress || $remaining === null || $remaining > 0)
                <form method="POST" action="{{ route('quizzes.start', $quiz) }}">@csrf
                    <button class="btn btn-primary btn-lg">{{ $inProgress ? 'Tiếp tục làm bài' : 'Bắt đầu làm bài' }}</button>
                </form>
            @else
                <button class="btn btn-secondary btn-lg" disabled>Đã hết lượt làm bài</button>
            @endif
        @else
            <div class="alert alert-warning mb-0">Bài kiểm tra chưa có câu hỏi.</div>
        @endif
    @else
        <a class="btn btn-outline-primary" href="{{ route('instructor.quizzes.edit', $quiz) }}">Quản lý bài kiểm tra</a>
    @endif
</div></div>

@if(auth()->user()->laHocVien())
<h5>Lịch sử làm bài</h5>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>#</th><th>Nộp lúc</th><th>Số câu đúng</th><th>Điểm</th><th>Kết quả</th><th></th></tr></thead>
    <tbody>
    @forelse($attempts as $i => $a)
        <tr>
            <td>{{ $attempts->count() - $i }}</td>
            <td>{{ $a->submitted_at?->format('d/m/Y H:i') ?? 'Chưa nộp' }}</td>
            <td>{{ $a->daNop() ? $a->correct_count.'/'.$a->total_questions : '—' }}</td>
            <td>{{ $a->daNop() ? rtrim(rtrim(number_format($a->score, 2), '0'), '.') : '—' }}</td>
            <td>@if($a->daNop())<span class="badge text-bg-{{ $a->passed ? 'success' : 'danger' }}">{{ $a->passed ? 'Đạt' : 'Chưa đạt' }}</span>@else<span class="badge text-bg-secondary">Đang làm</span>@endif</td>
            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ $a->daNop() ? route('attempts.result', $a) : route('attempts.take', $a) }}">{{ $a->daNop() ? 'Xem' : 'Làm tiếp' }}</a></td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-3">Bạn chưa làm bài này.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
@endif
@endsection
