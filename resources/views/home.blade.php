@extends('layouts.app')
@section('title', 'Trang chủ')
@section('content')
<div class="hero mb-4">
    <div class="hero-content">
        <div class="hero-eyebrow">Nền tảng học tập thế hệ mới</div>
        <h1>Biến tò mò thành năng lực.</h1>
        <p>Khám phá lộ trình học rõ ràng, video trực quan, tài liệu đầy đủ và bài kiểm tra giúp bạn nhìn thấy tiến bộ qua từng ngày.</p>
        <div class="d-flex flex-wrap gap-2 mt-4">
            <a href="{{ route('courses.index') }}" class="btn btn-light btn-lg">Khám phá khóa học <i class="bi bi-arrow-right ms-1"></i></a>
            @guest<a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">Tạo tài khoản miễn phí</a>@endguest
        </div>
        <div class="hero-trust">
            <span><i class="bi bi-check-circle-fill me-1"></i> Học theo tiến độ riêng</span>
            <span><i class="bi bi-check-circle-fill me-1"></i> Kiểm tra trực tuyến</span>
            <span><i class="bi bi-check-circle-fill me-1"></i> Chứng chỉ hoàn thành</span>
        </div>
    </div>
</div>

<div class="row g-3 mb-5">
    @foreach([['Khóa học', $stats['courses'], 'journal-richtext'], ['Học viên', $stats['students'], 'people'], ['Giảng viên', $stats['instructors'], 'person-workspace']] as [$label, $n, $icon])
        <div class="col-md-4 stat-card"><div class="card border-0 h-100"><div class="card-body d-flex align-items-center gap-3">
            <span class="stat-icon"><i class="bi bi-{{ $icon }}"></i></span>
            <div><div class="fs-3 fw-bold lh-1">{{ $n }}</div><div class="text-muted mt-1">{{ $label }} đang hoạt động</div></div>
        </div></div></div>
    @endforeach
</div>

<div class="page-heading">
    <div class="eyebrow">Học theo sở thích</div>
    <h2>Danh mục nổi bật</h2>
</div>
<div class="mb-5">
    @foreach($categories as $c)
        <a class="category-pill" href="{{ route('courses.index', ['category' => $c->id]) }}"><i class="bi bi-grid"></i>{{ $c->name }} <span class="text-muted">{{ $c->courses_count }}</span></a>
    @endforeach
</div>

<div class="d-flex justify-content-between align-items-end mb-3">
    <div class="page-heading mb-0"><div class="eyebrow">Vừa cập nhật</div><h2 class="mb-0">Khóa học mới nhất</h2></div>
    <a class="text-decoration-none fw-semibold" href="{{ route('courses.index') }}">Xem tất cả <i class="bi bi-arrow-right"></i></a>
</div>
<div class="row g-4">
    @forelse($courses as $course)
        <div class="col-md-6 col-lg-4">@include('partials.course-card', ['course' => $course])</div>
    @empty
        <p class="text-muted">Chưa có khóa học nào được công khai.</p>
    @endforelse
</div>
@endsection
