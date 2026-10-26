@extends('layouts.app')
@section('title', $quiz->title)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 sticky-top bg-white shadow-sm rounded p-3" style="top:.5rem;z-index:10">
    <h4 class="mb-0">{{ $quiz->title }}</h4>
    <div class="fs-5"><i class="bi bi-clock"></i> <span id="timer" class="fw-bold">--:--</span></div>
</div>

<form method="POST" action="{{ route('attempts.submit', $attempt) }}" id="quiz-form">@csrf
    @foreach($quiz->questions as $i => $question)
        <div class="card border-0 shadow-sm mb-3"><div class="card-body">
            <div class="fw-semibold mb-2">Câu {{ $i + 1 }}. {{ $question->question_text }}</div>
            @if($question->laNhieuDapAn())<div class="small text-primary mb-2"><i class="bi bi-check2-square"></i> Chọn tất cả phương án đúng</div>@endif
            @foreach($question->answers as $answer)
                <div class="form-check">
                    @if($question->laNhieuDapAn())
                        <input class="form-check-input" type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $answer->id }}" id="a{{ $answer->id }}">
                    @else
                        <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" value="{{ $answer->id }}" id="a{{ $answer->id }}">
                    @endif
                    <label class="form-check-label" for="a{{ $answer->id }}">{{ $answer->answer_text }}</label>
                </div>
            @endforeach
        </div></div>
    @endforeach
    <button class="btn btn-primary btn-lg" onclick="return confirm('Bạn chắc chắn muốn nộp bài?')">Nộp bài</button>
</form>
@endsection

@push('scripts')
<script>
    // Đồng hồ đếm ngược dựa trên giờ máy chủ; hết giờ tự động nộp bài.
    let remaining = {{ max(0, now()->diffInSeconds($hanNop, false)) }};
    const el = document.getElementById('timer');
    const form = document.getElementById('quiz-form');
    function tick() {
        const m = String(Math.floor(remaining / 60)).padStart(2, '0');
        const s = String(Math.floor(remaining % 60)).padStart(2, '0');
        el.textContent = m + ':' + s;
        if (remaining <= 60) el.classList.add('text-danger');
        if (remaining <= 0) { form.querySelector('button').removeAttribute('onclick'); form.submit(); return; }
        remaining -= 1;
        setTimeout(tick, 1000);
    }
    tick();
</script>
@endpush
