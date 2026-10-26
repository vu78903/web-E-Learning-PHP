@extends('layouts.app')
@section('title', 'Kết quả: '.$quiz->title)
@section('content')
<nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('learn.show', $course) }}">{{ $course->title }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('quizzes.show', $quiz) }}">{{ $quiz->title }}</a></li>
    <li class="breadcrumb-item active">Kết quả</li>
</ol></nav>

<div class="card border-0 shadow-sm mb-4 text-center"><div class="card-body p-4">
    <div class="text-muted">{{ $attempt->user->name }} · nộp lúc {{ $attempt->submitted_at->format('d/m/Y H:i') }}</div>
    <div class="display-3 fw-bold text-{{ $attempt->passed ? 'success' : 'danger' }}">{{ rtrim(rtrim(number_format($attempt->score, 2), '0'), '.') }}<small class="fs-4">/100</small></div>
    <div class="fs-5 mb-2">Đúng {{ $attempt->correct_count }}/{{ $attempt->total_questions }} câu</div>
    <span class="badge fs-6 text-bg-{{ $attempt->passed ? 'success' : 'danger' }}">{{ $attempt->passed ? 'ĐẠT' : 'CHƯA ĐẠT' }} (yêu cầu {{ $quiz->pass_score }}%)</span>
    @php $remaining = $quiz->soLuotConLaiCua($attempt->user); @endphp
    @if(auth()->id() === $attempt->user_id && $remaining === 0)
        <div class="mt-3 text-muted">Bạn đã dùng hết {{ $quiz->max_attempts }} lượt làm bài này.</div>
    @elseif(auth()->id() === $attempt->user_id)
        <div class="mt-3">
            <form method="POST" action="{{ route('quizzes.start', $quiz) }}" class="d-inline">@csrf
                <button class="btn btn-outline-primary">Làm lại</button></form>
            @if($remaining !== null)<div class="small text-muted mt-1">Còn {{ $remaining }} lượt</div>@endif
        </div>
    @endif
</div></div>

@foreach($quiz->questions as $i => $question)
    @php
        $idDapAnDaChon = $picked[$question->id] ?? [];
        $correctIds = $question->answers->where('is_correct', true)->pluck('id')->sort()->values()->all();
        $isRight = $idDapAnDaChon && collect($idDapAnDaChon)->sort()->values()->all() === $correctIds;
        $mine = auth()->id() === $attempt->user_id;
    @endphp
    <div class="card border-0 shadow-sm mb-3 border-start border-4 border-{{ $isRight ? 'success' : 'danger' }}"><div class="card-body">
        <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
            <div class="fw-semibold">Câu {{ $i + 1 }}. {{ $question->question_text }}
                @if($question->laNhieuDapAn())<span class="badge text-bg-warning fw-normal">Nhiều đáp án</span>@endif</div>
            <span class="badge align-self-start text-bg-{{ $isRight ? 'success' : 'danger' }}">{{ $isRight ? 'Đúng' : 'Sai' }}</span>
        </div>
        @foreach($question->answers as $answer)
            @php $isPicked = in_array($answer->id, $idDapAnDaChon, true); @endphp
            <div class="px-2 py-1 rounded mb-1
                {{ $answer->is_correct ? 'bg-success-subtle' : ($isPicked ? 'bg-danger-subtle' : '') }}">
                @if($answer->is_correct)<i class="bi bi-check-circle-fill text-success"></i>
                @elseif($isPicked)<i class="bi bi-x-circle-fill text-danger"></i>
                @else<i class="bi bi-circle text-muted"></i>@endif
                {{ $answer->answer_text }}
                @if($isPicked)<span class="small text-muted">({{ $mine ? 'bạn chọn' : 'học viên chọn' }})</span>@endif
            </div>
        @endforeach
        @if(! $idDapAnDaChon)<div class="small text-danger mt-1">{{ $mine ? 'Bạn' : 'Học viên' }} chưa trả lời câu này.</div>
        @elseif($question->laNhieuDapAn() && ! $isRight)<div class="small text-danger mt-1">Câu nhiều đáp án chỉ tính đúng khi chọn đủ tất cả đáp án đúng và không chọn thừa.</div>@endif
    </div></div>
@endforeach
@endsection
