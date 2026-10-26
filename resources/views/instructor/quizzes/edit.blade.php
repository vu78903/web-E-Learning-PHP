@extends('layouts.app')
@section('title', 'Soạn bài kiểm tra')
@section('content')
<nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('instructor.quizzes.index', $course) }}">{{ $course->title }}</a></li>
    <li class="breadcrumb-item active">{{ $quiz->title }}</li>
</ol></nav>

<div class="card border-0 shadow-sm mb-4"><div class="card-body">
    <form method="POST" action="{{ route('instructor.quizzes.update', $quiz) }}" class="row g-2 align-items-end">@csrf @method('PUT')
        <div class="col-md-6"><label class="form-label">Tên</label><input name="title" value="{{ old('title', $quiz->title) }}" class="form-control" required></div>
        <div class="col-6 col-md-3"><label class="form-label">Phút</label><input type="number" name="duration_minutes" value="{{ old('duration_minutes', $quiz->duration_minutes) }}" min="1" class="form-control" required></div>
        <div class="col-6 col-md-3"><label class="form-label">Điểm đạt %</label><input type="number" name="pass_score" value="{{ old('pass_score', $quiz->pass_score) }}" min="0" max="100" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Số lần làm tối đa</label>
            <input type="number" name="max_attempts" value="{{ old('max_attempts', $quiz->max_attempts) }}" min="0" max="100" class="form-control" placeholder="Không giới hạn"></div>
        <div class="col-md-5 pb-md-2"><div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" role="switch" name="shuffle_questions" value="1" id="shuffle_questions" @checked(old('shuffle_questions', $quiz->shuffle_questions))>
            <label class="form-check-label" for="shuffle_questions">Xáo trộn thứ tự câu hỏi và phương án</label></div></div>
        <div class="col-md-3 d-grid"><button class="btn btn-primary">Lưu</button></div>
        <div class="col-12 form-text mt-0">Số lần làm tối đa: để trống hoặc 0 = không giới hạn. Khi xáo trộn, mỗi lượt làm có thứ tự riêng và giữ nguyên khi tải lại trang.</div>
    </form>
    @if($errors->hasAny(['title', 'duration_minutes', 'pass_score', 'max_attempts']))
        <div class="text-danger small mt-2">{{ $errors->first('title') ?: ($errors->first('duration_minutes') ?: ($errors->first('pass_score') ?: $errors->first('max_attempts'))) }}</div>
    @endif
</div></div>

<h4>Câu hỏi ({{ $quiz->questions->count() }})</h4>
@foreach($quiz->questions as $i => $question)
    <div class="card border-0 shadow-sm mb-3"><div class="card-body">
        <div class="d-flex justify-content-between flex-wrap gap-2">
            <div class="fw-semibold">Câu {{ $i + 1 }}. {{ $question->question_text }}
                <span class="badge text-bg-{{ $question->laNhieuDapAn() ? 'warning' : 'light' }} fw-normal">{{ $question->laNhieuDapAn() ? 'Nhiều đáp án' : 'Một đáp án' }}</span></div>
            <div class="text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('instructor.questions.edit', $question) }}">Sửa</a>
                <form method="POST" action="{{ route('instructor.questions.destroy', $question) }}" class="d-inline" onsubmit="return confirm('Xóa câu hỏi này?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Xóa</button></form>
            </div>
        </div>
        <ul class="list-unstyled mb-0 mt-2">
            @foreach($question->answers as $a)
                <li>@if($a->is_correct)<i class="bi bi-check-circle-fill text-success"></i> <strong>{{ $a->answer_text }}</strong>
                    @else<i class="bi bi-circle text-muted"></i> {{ $a->answer_text }}@endif</li>
            @endforeach
        </ul>
    </div></div>
@endforeach

<div class="card border-0 shadow-sm"><div class="card-body">
    <h5>Thêm câu hỏi mới</h5>
    <form method="POST" action="{{ route('instructor.questions.store', $quiz) }}">@csrf
        @include('instructor.quizzes._answers', ['question' => null])
        <button class="btn btn-success"><i class="bi bi-plus-lg"></i> Thêm câu hỏi</button>
    </form>
</div></div>
@endsection
