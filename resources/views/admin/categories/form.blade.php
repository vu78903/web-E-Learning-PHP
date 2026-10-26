@extends('layouts.app')
@section('title', $category->exists ? 'Sửa danh mục' : 'Thêm danh mục')
@section('content')
<div class="row justify-content-center"><div class="col-lg-6">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h3 class="mb-3">{{ $category->exists ? 'Sửa danh mục' : 'Thêm danh mục' }}</h3>
        <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
            @csrf @if($category->exists) @method('PUT') @endif
            <div class="mb-3"><label class="form-label">Tên danh mục</label><input name="name" value="{{ old('name', $category->name) }}" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Mô tả</label><textarea name="description" rows="3" class="form-control">{{ old('description', $category->description) }}</textarea></div>
            <button class="btn btn-primary">Lưu</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-link">Hủy</a>
        </form>
    </div></div>
</div></div>
@endsection
