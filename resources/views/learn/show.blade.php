@extends('layouts.app')
@section('title', $course->title)
@section('content')
@php($first = $course->sections->flatMap->lessons->first())
<div class="row g-4">
    <div class="col-lg-4">@include('learn._sidebar')</div>
    <div class="col-lg-8">
        <div class="card shadow-sm border-0"><div class="card-body p-4">
            <h3>{{ $course->title }}</h3>
            <p class="text-muted mb-3">Giảng viên: {{ $course->instructor->name }}</p>
            <p style="white-space:pre-line">{{ $course->description }}</p>
            @if($laHocVien)
                <p>Tiến độ hiện tại: <strong>{{ $progress }}%</strong></p>
            @endif
            @if($first)
                <a class="btn btn-primary" href="{{ route('learn.lesson', [$course, $first]) }}">Bắt đầu học</a>
            @else
                <div class="alert alert-light border mb-0">Khóa học chưa có bài học nào.</div>
            @endif
        </div></div>

        @if($laHocVien && $progress === 100)
            <div class="alert alert-success mt-3 mb-0">
                <i class="bi bi-trophy-fill"></i>
                Bạn đã hoàn thành khóa học. Hãy để lại đánh giá để giúp giảng viên cải thiện nội dung.
            </div>
        @endif

        @include('partials.the-chung-chi')

        @include('danh-gia.khu-vuc')
    </div>
</div>
@endsection
