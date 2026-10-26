{{-- Trường nhập câu hỏi + phương án. Biến: $question (có thể rỗng, kèm answers) --}}
@php
    $existing = isset($question) ? $question->answers->values() : collect();
    $type = old('type', $question->type ?? \App\Models\CauHoi::SINGLE);
    $isMulti = $type === \App\Models\CauHoi::MULTIPLE;
    $defaultCorrect = $existing->isEmpty() ? [0] : $existing->keys()->filter(fn ($k) => $existing[$k]->is_correct)->values()->all();
    $correctIdx = array_map('intval', (array) (old('question_text') !== null ? old('correct', []) : $defaultCorrect));
    $formId = 'qf'.($question->id ?? 'new');
@endphp
<div id="{{ $formId }}" class="question-fields">
<div class="mb-3"><label class="form-label">Nội dung câu hỏi</label>
    <textarea name="question_text" rows="2" class="form-control" required>{{ old('question_text', $question->question_text ?? '') }}</textarea></div>
<div class="mb-3">
    <label class="form-label d-block">Loại câu hỏi</label>
    <div class="btn-group flex-wrap" role="group">
        <input type="radio" class="btn-check js-qtype" name="type" id="{{ $formId }}-single" value="single" @checked(! $isMulti)>
        <label class="btn btn-outline-primary" for="{{ $formId }}-single"><i class="bi bi-record-circle"></i> Một đáp án</label>
        <input type="radio" class="btn-check js-qtype" name="type" id="{{ $formId }}-multiple" value="multiple" @checked($isMulti)>
        <label class="btn btn-outline-primary" for="{{ $formId }}-multiple"><i class="bi bi-check2-square"></i> Nhiều đáp án</label>
    </div>
</div>
<label class="form-label">Phương án trả lời <span class="js-qhint text-muted small">({{ $isMulti ? 'đánh dấu tất cả phương án đúng' : 'chọn một đáp án đúng' }})</span></label>
@error('correct')<div class="text-danger small mb-1">{{ $message }}</div>@enderror
@for($i = 0; $i < 4 || $i < $existing->count(); $i++)
    @php $a = $existing[$i] ?? null; @endphp
    <div class="input-group mb-2">
        <div class="input-group-text">
            <input class="form-check-input mt-0 js-correct" type="{{ $isMulti ? 'checkbox' : 'radio' }}" name="correct[]" value="{{ $i }}" @checked(in_array($i, $correctIdx, true)) title="Đáp án đúng">
        </div>
        @if($a)<input type="hidden" name="answers[{{ $i }}][id]" value="{{ $a->id }}">@endif
        <input name="answers[{{ $i }}][text]" value="{{ old("answers.$i.text", $a->answer_text ?? '') }}" class="form-control" placeholder="Phương án {{ chr(65 + $i) }}">
    </div>
@endfor
<div class="form-text mb-3">Để trống phương án không dùng. Cần tối thiểu 2 phương án. Câu nhiều đáp án chỉ được tính đúng khi học viên chọn đủ và đúng tất cả đáp án đúng.</div>
</div>

@once
@push('scripts')
<script>
    // Đổi loại câu hỏi: radio (một đáp án) <-> checkbox (nhiều đáp án).
    document.querySelectorAll('.question-fields').forEach(function (box) {
        box.querySelectorAll('.js-qtype').forEach(function (r) {
            r.addEventListener('change', function () {
                const multi = r.value === 'multiple';
                let firstChecked = false;
                box.querySelectorAll('.js-correct').forEach(function (c) {
                    const wasChecked = c.checked;
                    c.type = multi ? 'checkbox' : 'radio';
                    c.checked = multi ? wasChecked : (wasChecked && !firstChecked);
                    if (c.checked) firstChecked = true;
                });
                box.querySelector('.js-qhint').textContent = multi ? '(đánh dấu tất cả phương án đúng)' : '(chọn một đáp án đúng)';
            });
        });
    });
</script>
@endpush
@endonce
