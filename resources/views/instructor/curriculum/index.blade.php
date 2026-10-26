@extends('layouts.app')
@section('title', 'Chương & bài học')
@section('content')
<nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('instructor.courses.index') }}">Khóa học</a></li>
    <li class="breadcrumb-item active">{{ $course->title }}</li>
</ol></nav>
<div class="d-flex justify-content-between mb-3">
    <h3 class="mb-0">Chương & bài học</h3>
    <a class="btn btn-outline-success" href="{{ route('instructor.quizzes.index', $course) }}">Quản lý bài kiểm tra</a>
</div>

@foreach($course->sections as $section)
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white">
            <form method="POST" action="{{ route('instructor.sections.update', $section) }}" class="row g-2 align-items-center">
                @csrf @method('PUT')
                <div class="col"><input name="title" value="{{ $section->title }}" class="form-control form-control-sm fw-semibold" required></div>
                <div class="col-auto" style="width:90px"><input type="number" name="sort_order" value="{{ $section->sort_order }}" class="form-control form-control-sm" title="Thứ tự"></div>
                <div class="col-auto"><button class="btn btn-sm btn-outline-primary">Lưu</button></div>
                <div class="col-auto">
                    <button form="del-section-{{ $section->id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Xóa chương và toàn bộ bài học trong chương?')">Xóa</button>
                </div>
            </form>
            <form id="del-section-{{ $section->id }}" method="POST" action="{{ route('instructor.sections.destroy', $section) }}">@csrf @method('DELETE')</form>
        </div>
        <ul class="list-group list-group-flush">
            @foreach($section->lessons as $lesson)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-play-circle text-primary"></i> {{ $lesson->title }}
                        @if($lesson->video_url)<i class="bi bi-camera-video text-muted ms-1"></i>@endif
                        @if($lesson->document_url)<i class="bi bi-file-earmark-text text-muted ms-1"></i>@endif
                        @if($lesson->attachments_count)<span class="badge text-bg-light border ms-1" title="Tài liệu PDF"><i class="bi bi-file-earmark-pdf text-danger"></i> {{ $lesson->attachments_count }}</span>@endif</span>
                    <span class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('instructor.lessons.edit', $lesson) }}">Sửa</a>
                        <form method="POST" action="{{ route('instructor.lessons.destroy', $lesson) }}" class="d-inline" onsubmit="return confirm('Xóa bài học này?')">
                            @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Xóa</button></form>
                    </span>
                </li>
            @endforeach
            <li class="list-group-item"><a href="{{ route('instructor.lessons.create', $section) }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Thêm bài học</a></li>
        </ul>
    </div>
@endforeach

<div class="card border-0 shadow-sm"><div class="card-body">
    <form method="POST" action="{{ route('instructor.sections.store', $course) }}" class="row g-2">@csrf
        <div class="col"><input name="title" class="form-control" placeholder="Tên chương mới..." required></div>
        <div class="col-auto"><button class="btn btn-success"><i class="bi bi-plus-lg"></i> Thêm chương</button></div>
    </form>
</div></div>
@endsection
