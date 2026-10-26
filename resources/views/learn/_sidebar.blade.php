{{-- Mục lục khóa học khi học. Biến: $course, $completedIds, $laHocVien, $progress, $current (lesson hiện tại, tùy chọn) --}}
<div class="card shadow-sm border-0 sticky-side">
    <div class="card-body">
        <a href="{{ route('learn.show', $course) }}" class="fw-bold text-decoration-none">{{ $course->title }}</a>
        @if($laHocVien)
            <div class="progress my-2"><div class="progress-bar" style="width:{{ $progress }}%"></div></div>
            <div class="small text-muted">{{ $progress }}% hoàn thành</div>
        @endif
    </div>
    @foreach($course->sections as $section)
        <div class="px-3 py-2 bg-light fw-semibold small border-top">{{ $section->title }}</div>
        <div class="list-group list-group-flush">
            @foreach($section->lessons as $l)
                <a href="{{ route('learn.lesson', [$course, $l]) }}"
                   class="list-group-item list-group-item-action small @if(isset($current) && $current->id === $l->id) active @endif">
                    @if(in_array($l->id, $completedIds)) <i class="bi bi-check-circle-fill text-success"></i>
                    @else <i class="bi bi-circle"></i> @endif
                    {{ $l->title }}
                </a>
            @endforeach
        </div>
    @endforeach
    @if($course->quizzes->isNotEmpty())
        <div class="px-3 py-2 bg-light fw-semibold small border-top">Kiểm tra</div>
        <div class="list-group list-group-flush">
            @foreach($course->quizzes as $q)
                <a href="{{ route('quizzes.show', $q) }}" class="list-group-item list-group-item-action small">
                    <i class="bi bi-patch-question"></i> {{ $q->title }}</a>
            @endforeach
        </div>
    @endif
</div>
