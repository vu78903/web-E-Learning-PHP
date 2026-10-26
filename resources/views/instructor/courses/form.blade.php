@extends('layouts.app')
@section('title', $course->exists ? 'Sửa khóa học' : 'Tạo khóa học')
@section('content')
@php($statuses = auth()->user()->laQuanTri() ? \App\Models\KhoaHoc::STATUSES : array_diff_key(\App\Models\KhoaHoc::STATUSES, ['hidden' => 1]))
<div class="row justify-content-center"><div class="col-lg-8">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h3 class="mb-3">{{ $course->exists ? 'Sửa khóa học' : 'Tạo khóa học mới' }}</h3>
        <form method="POST" enctype="multipart/form-data"
              action="{{ $course->exists ? route('instructor.courses.update', $course) : route('instructor.courses.store') }}">
            @csrf @if($course->exists) @method('PUT') @endif
            <div class="mb-3"><label class="form-label">Tên khóa học</label>
                <input name="title" value="{{ old('title', $course->title) }}" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Danh mục</label>
                <select name="category_id" class="form-select" required>
                    <option value="">-- Chọn danh mục --</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $course->category_id) == $c->id)>{{ $c->name }}</option>@endforeach
                </select></div>
            <div class="mb-3"><label class="form-label">Mô tả</label>
                <textarea name="description" rows="5" class="form-control">{{ old('description', $course->description) }}</textarea></div>
            <div class="mb-3"><label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    @foreach($statuses as $k => $label)<option value="{{ $k }}" @selected(old('status', $course->status) === $k)>{{ $label }}</option>@endforeach
                </select>
                <div class="form-text">Chỉ khóa học "Công khai" mới hiển thị cho học viên.</div></div>
            <div class="mb-3"><label class="form-label">Ảnh bìa (tùy chọn, tối đa 2MB)</label>
                <input type="file" name="thumbnail" accept="image/*" class="form-control">
                @if($course->duongDanAnhBia())<img src="{{ $course->duongDanAnhBia() }}" class="mt-2 rounded" style="max-height:100px" alt="">@endif</div>
            <button class="btn btn-primary">Lưu</button>
            <a href="{{ route('instructor.courses.index') }}" class="btn btn-link">Hủy</a>
        </form>
    </div></div>
</div></div>
@endsection
