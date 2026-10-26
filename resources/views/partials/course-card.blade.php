{{-- Thẻ khóa học dùng chung. Biến: $course (kèm category, instructor, enrollments_count) --}}
<div class="card course-card h-100 border-0 shadow-sm">
    @if($course->duongDanAnhBia())
        <img src="{{ $course->duongDanAnhBia() }}" class="card-img-top course-thumb" alt="">
    @else
        <div class="thumb-fallback card-img-top"><i class="bi bi-journal-richtext"></i></div>
    @endif
    <div class="card-body d-flex flex-column">
        <span class="badge align-self-start mb-2" style="color:#4f45ca;background:#efedff">{{ $course->category->name }}</span>
        <h5 class="card-title"><a class="text-decoration-none stretched-link" href="{{ route('courses.show', $course) }}">{{ $course->title }}</a></h5>
        <p class="card-text text-muted small flex-grow-1">{{ \Illuminate\Support\Str::limit($course->description, 90) }}</p>
        <div class="course-meta small text-muted">
            <span><i class="bi bi-person me-1"></i>{{ $course->instructor->name }}</span>
            <span><i class="bi bi-people me-1"></i>{{ $course->enrollments_count ?? 0 }}</span>
        </div>
    </div>
</div>
