@extends('layouts.app')
@section('title', 'Khóa học của tôi')
@section('content')
<div class="page-heading"><div class="eyebrow">Không gian học tập</div><h1>Khóa học của tôi</h1><p class="text-muted mb-0">Tiếp tục từ nơi bạn đã dừng lại.</p></div>
<div class="row g-4">
    @forelse($courses as $course)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm"><div class="card-body d-flex flex-column">
                <span class="badge text-bg-light align-self-start mb-2">{{ $course->category->name }}</span>
                <h5>{{ $course->title }}</h5>
                <div class="small text-muted mb-2"><i class="bi bi-person"></i> {{ $course->instructor->name }}</div>
                <div class="progress mb-1"><div class="progress-bar" style="width:{{ $course->progress }}%"></div></div>
                <div class="small mb-3">{{ $course->progress }}% hoàn thành</div>
                <a href="{{ route('learn.show', $course) }}" class="btn btn-primary mt-auto">Tiếp tục học</a>
                @include('partials.nut-chung-chi')
            </div></div>
        </div>
    @empty
        <div class="col-12"><div class="alert alert-light border">Bạn chưa đăng ký khóa học nào. <a href="{{ route('courses.index') }}">Khám phá khóa học</a>.</div></div>
    @endforelse
</div>
@endsection
