@extends('layouts.app')
@section('title', 'Kết quả bài kiểm tra')
@section('content')
<nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('instructor.quizzes.index', $course) }}">{{ $course->title }}</a></li>
    <li class="breadcrumb-item active">{{ $quiz->title }}</li>
</ol></nav>
<h3 class="mb-3">Kết quả: {{ $quiz->title }}</h3>
@include('partials.nut-csv-bai-kiem-tra')
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card border-0 shadow-sm text-center"><div class="card-body"><div class="text-muted">Lượt nộp</div><div class="fs-3 fw-bold">{{ $attempts->count() }}</div></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm text-center"><div class="card-body"><div class="text-muted">Điểm trung bình</div><div class="fs-3 fw-bold">{{ $avg !== null ? number_format($avg, 1) : '—' }}</div></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm text-center"><div class="card-body"><div class="text-muted">Tỷ lệ đạt</div><div class="fs-3 fw-bold">{{ $passRate !== null ? $passRate.'%' : '—' }}</div></div></div></div>
</div>
<div class="card border-0 shadow-sm"><table class="table mb-0">
    <thead><tr><th>Học viên</th><th>Nộp lúc</th><th>Đúng</th><th>Điểm</th><th>Kết quả</th><th></th></tr></thead>
    <tbody>
    @forelse($attempts as $a)
        <tr>
            <td>{{ $a->user->name }}</td><td>{{ $a->submitted_at->format('d/m/Y H:i') }}</td>
            <td>{{ $a->correct_count }}/{{ $a->total_questions }}</td>
            <td>{{ rtrim(rtrim(number_format($a->score, 2), '0'), '.') }}</td>
            <td><span class="badge text-bg-{{ $a->passed ? 'success' : 'danger' }}">{{ $a->passed ? 'Đạt' : 'Chưa đạt' }}</span></td>
            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('attempts.result', $a) }}">Chi tiết</a></td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-3">Chưa có lượt làm bài nào.</td></tr>
    @endforelse
    </tbody>
</table></div>
@endsection
