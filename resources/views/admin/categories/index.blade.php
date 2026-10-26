@extends('layouts.app')
@section('title', 'Danh mục')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h2 class="mb-0">Danh mục khóa học</h2>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm danh mục</a>
</div>
<div class="card border-0 shadow-sm"><table class="table align-middle mb-0">
    <thead><tr><th>Tên</th><th>Mô tả</th><th>Số khóa học</th><th></th></tr></thead>
    <tbody>
    @forelse($categories as $c)
        <tr><td class="fw-semibold">{{ $c->name }}</td><td class="text-muted">{{ $c->description }}</td><td>{{ $c->courses_count }}</td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.categories.edit', $c) }}">Sửa</a>
                <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Xóa danh mục này?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Xóa</button></form>
            </td></tr>
    @empty<tr><td colspan="4" class="text-center text-muted py-3">Chưa có danh mục.</td></tr>@endforelse
    </tbody>
</table></div>
@endsection
