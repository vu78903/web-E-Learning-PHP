@extends('layouts.app')
@section('title', 'Sửa câu hỏi')
@section('content')
<nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('instructor.quizzes.edit', $quiz) }}">{{ $quiz->title }}</a></li>
    <li class="breadcrumb-item active">Sửa câu hỏi</li>
</ol></nav>
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h3 class="mb-3">Sửa câu hỏi</h3>
        <form method="POST" action="{{ route('instructor.questions.update', $question) }}">@csrf @method('PUT')
            @include('instructor.quizzes._answers')
            <button class="btn btn-primary">Lưu</button>
            <a href="{{ route('instructor.quizzes.edit', $quiz) }}" class="btn btn-link">Hủy</a>
        </form>
    </div></div>
</div></div>
@endsection
