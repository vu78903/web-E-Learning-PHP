@extends('layouts.app')
@section('title', $course->title)
@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <nav aria-label="breadcrumb"><ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">Khóa học</a></li>
            <li class="breadcrumb-item"><a href="{{ route('courses.index', ['category' => $course->category_id]) }}">{{ $course->category->name }}</a></li>
        </ol></nav>
        <div class="page-heading">
        <div class="eyebrow">{{ $course->category->name }}</div>
        <h1>{{ $course->title }}
            @unless($course->daCongKhai())<span class="badge text-bg-warning fs-6">{{ $course->tenTrangThai() }}</span>@endunless
        </h1>
        <p class="text-muted"><i class="bi bi-person"></i> Giảng viên: <strong>{{ $course->instructor->name }}</strong>
            · <i class="bi bi-people"></i> {{ $course->enrollments_count }} học viên</p>
        </div>
        <p style="white-space:pre-line">{{ $course->description }}</p>

        <h4 class="mt-5 mb-3">Nội dung khóa học</h4>
        @forelse($course->sections as $section)
            <div class="card mb-3 border-0 section-card">
                <div class="card-header bg-white fw-semibold">{{ $section->title }}
                    <span class="text-muted small">({{ $section->lessons->count() }} bài)</span></div>
                <ul class="list-group list-group-flush">
                    @foreach($section->lessons as $lesson)
                        <li class="list-group-item"><i class="bi bi-play-circle text-primary"></i> {{ $lesson->title }}</li>
                    @endforeach
                </ul>
            </div>
        @empty
            <p class="text-muted">Khóa học chưa có nội dung.</p>
        @endforelse

        @if($course->quizzes->isNotEmpty())
            <h4 class="mt-4">Bài kiểm tra</h4>
            <ul class="list-group">
                @foreach($course->quizzes as $quiz)
                    <li class="list-group-item"><i class="bi bi-patch-question text-success"></i> {{ $quiz->title }}
                        <span class="text-muted small">· {{ $quiz->duration_minutes }} phút · đạt từ {{ $quiz->pass_score }}%</span></li>
                @endforeach
            </ul>
        @endif

        @include('danh-gia.khu-vuc')
    </div>

    <div class="col-lg-4">
        <div class="card border-0 sticky-side"><div class="card-body p-4">
            <div class="d-flex align-items-center gap-2 mb-3"><span class="stat-icon"><i class="bi bi-rocket-takeoff"></i></span><div><small class="text-muted">Sẵn sàng?</small><h5 class="mb-0">Bắt đầu học ngay</h5></div></div>
            @guest
                <p>Đăng nhập để đăng ký khóa học này.</p>
                <a href="{{ route('login') }}" class="btn btn-primary w-100">Đăng nhập</a>
            @else
                @if(auth()->user()->laHocVien())
                    @if($enrolled)
                        <div class="mb-2">Tiến độ: <strong>{{ $progress }}%</strong></div>
                        <div class="progress mb-3"><div class="progress-bar" style="width:{{ $progress }}%"></div></div>
                        <a href="{{ route('learn.show', $course) }}" class="btn btn-success w-100">Vào học</a>
                    @elseif($course->daCongKhai())
                        <form method="POST" action="{{ route('courses.enroll', $course) }}">@csrf
                            <button class="btn btn-primary w-100 btn-lg">Đăng ký học</button>
                        </form>
                    @endif
                @endif
                @if($canManage)
                    <a href="{{ route('learn.show', $course) }}" class="btn btn-outline-success w-100 mb-2">Xem nội dung học</a>
                    <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-outline-primary w-100 mb-2">Sửa khóa học</a>
                    <a href="{{ route('instructor.curriculum', $course) }}" class="btn btn-outline-secondary w-100">Quản lý chương/bài học</a>
                @endif
            @endguest
        </div></div>
    </div>
</div>
@endsection
