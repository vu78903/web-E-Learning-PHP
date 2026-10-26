@extends('layouts.app')
@section('title', 'Quản lý khóa học (admin)')
@section('content')
<h2 class="mb-3">Quản lý khóa học</h2>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Tìm theo tên khóa học"></div>
    <div class="col-md-3"><select name="status" class="form-select"><option value="">Mọi trạng thái</option>
        @foreach(\App\Models\KhoaHoc::STATUSES as $k => $label)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-3"><select name="category" class="form-select"><option value="">Mọi danh mục</option>
        @foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach</select></div>
    <div class="col-md-2 d-grid"><button class="btn btn-primary">Lọc</button></div>
</form>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Khóa học</th><th>Danh mục</th><th>Giảng viên</th><th>HV</th><th style="width:230px">Trạng thái</th></tr></thead>
    <tbody>
    @forelse($courses as $c)
        <tr>
            <td><a class="text-decoration-none fw-semibold" href="{{ route('courses.show', $c) }}">{{ $c->title }}</a></td>
            <td>{{ $c->category->name }}</td><td>{{ $c->instructor->name }}</td><td>{{ $c->enrollments_count }}</td>
            <td>
                <form method="POST" action="{{ route('admin.courses.status', $c) }}" class="input-group input-group-sm">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select">
                        @foreach(\App\Models\KhoaHoc::STATUSES as $k => $label)<option value="{{ $k }}" @selected($c->status === $k)>{{ $label }}</option>@endforeach
                    </select>
                    <button class="btn btn-outline-primary">Đổi</button>
                </form>
            </td>
        </tr>
    @empty<tr><td colspan="5" class="text-center text-muted py-3">Không có khóa học.</td></tr>@endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $courses->links('pagination::bootstrap-5') }}</div>
@endsection
