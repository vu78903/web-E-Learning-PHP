@extends('layouts.app')
@section('title', 'Bài kiểm tra')
@section('content')
<nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('instructor.courses.index') }}">Khóa học</a></li>
    <li class="breadcrumb-item active">{{ $course->title }}</li>
</ol></nav>
<h3 class="mb-3">Bài kiểm tra</h3>
<div class="card border-0 shadow-sm mb-4"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Tên</th><th>Thời gian</th><th>Điểm đạt</th><th>Lượt tối đa</th><th>Câu hỏi</th><th>Lượt làm</th><th></th></tr></thead>
    <tbody>
    @forelse($quizzes as $q)
        <tr>
            <td class="fw-semibold">{{ $q->title }}@if($q->shuffle_questions) <i class="bi bi-shuffle text-muted" title="Xáo trộn câu hỏi"></i>@endif</td><td>{{ $q->duration_minutes }} phút</td><td>{{ $q->pass_score }}%</td><td>{{ $q->coGioiHanLuotLam() ? $q->max_attempts : '∞' }}</td>
            <td>{{ $q->questions_count }}</td><td>{{ $q->attempts_count }}</td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('instructor.quizzes.edit', $q) }}">Câu hỏi</a>
                <a class="btn btn-sm btn-outline-info" href="{{ route('instructor.quizzes.results', $q) }}">Kết quả</a>
                <form method="POST" action="{{ route('instructor.quizzes.destroy', $q) }}" class="d-inline" onsubmit="return confirm('Xóa bài kiểm tra và toàn bộ kết quả?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Xóa</button></form>
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-3">Chưa có bài kiểm tra.</td></tr>
    @endforelse
    </tbody>
</table></div></div>

<div class="card border-0 shadow-sm"><div class="card-body">
    <h5>Tạo bài kiểm tra mới</h5>
    <form method="POST" action="{{ route('instructor.quizzes.store', $course) }}" class="row g-2 align-items-end">@csrf
        <div class="col-md-6"><label class="form-label small mb-1" for="new_title">Tên bài kiểm tra</label>
            <input name="title" id="new_title" class="form-control" placeholder="Tên bài kiểm tra" required></div>
        <div class="col-6 col-md-3"><label class="form-label small mb-1" for="new_duration">Thời gian (phút)</label>
            <input type="number" name="duration_minutes" id="new_duration" value="15" min="1" class="form-control" placeholder="Thời gian (phút)" required></div>
        <div class="col-6 col-md-3"><label class="form-label small mb-1" for="new_pass">Điểm đạt (%)</label>
            <input type="number" name="pass_score" id="new_pass" value="50" min="0" max="100" class="form-control" placeholder="Điểm đạt (%)" required></div>
        <div class="col-md-4"><label class="form-label small mb-1" for="new_max">Số lần làm tối đa</label>
            <input type="number" name="max_attempts" id="new_max" min="0" max="100" class="form-control" placeholder="Trống = không giới hạn"></div>
        <div class="col-md-5 pb-md-2"><div class="form-check form-switch mb-0">
            <input class="form-check-input" type="checkbox" role="switch" name="shuffle_questions" value="1" id="new_shuffle">
            <label class="form-check-label" for="new_shuffle">Xáo trộn câu hỏi và phương án</label></div></div>
        <div class="col-md-3 d-grid"><button class="btn btn-success">Tạo</button></div>
    </form>
    <div class="form-text">Thời gian tính bằng phút; điểm đạt tính theo phần trăm (0–100); số lần làm để trống hoặc 0 = không giới hạn.</div>
</div></div>
@endsection
