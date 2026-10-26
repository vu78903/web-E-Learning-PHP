<div class="row g-4">
    @forelse($courses as $course)
        <div class="col-md-6 col-lg-4">@include('partials.course-card', ['course' => $course])</div>
    @empty
        <div class="col-12"><div class="alert alert-light border">Không tìm thấy khóa học phù hợp.</div></div>
    @endforelse
</div>
<div class="mt-4">{{ $courses->links('pagination::bootstrap-5') }}</div>
