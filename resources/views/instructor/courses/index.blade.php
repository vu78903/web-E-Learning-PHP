@extends('layouts.app')
@section('title', 'Quản lý khóa học')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">{{ auth()->user()->laQuanTri() ? 'Tất cả khóa học (nội dung)' : 'Khóa học của tôi' }}</h2>
    @if(auth()->user()->laGiangVien())
        <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tạo khóa học</a>
    @endif
</div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>Khóa học</th><th>Danh mục</th>@if(auth()->user()->laQuanTri())<th>Giảng viên</th>@endif<th>Trạng thái</th><th>HV</th><th>Bài học</th><th>Kiểm tra</th><th></th></tr></thead>
    <tbody>
    @forelse($courses as $c)
        <tr>
            <td><a href="{{ route('courses.show', $c) }}" class="fw-semibold text-decoration-none">{{ $c->title }}</a></td>
            <td>{{ $c->category->name }}</td>
            @if(auth()->user()->laQuanTri())<td>{{ $c->instructor->name }}</td>@endif
            <td><span class="badge text-bg-{{ ['draft' => 'secondary', 'published' => 'success', 'hidden' => 'dark'][$c->status] ?? 'light' }}">{{ $c->tenTrangThai() }}</span></td>
            <td>{{ $c->enrollments_count }}</td><td>{{ $c->lessons_count }}</td><td>{{ $c->quizzes_count }}</td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('instructor.curriculum', $c) }}">Bài học</a>
                <a class="btn btn-sm btn-outline-success" href="{{ route('instructor.quizzes.index', $c) }}">Kiểm tra</a>
                <a class="btn btn-sm btn-outline-info" href="{{ route('instructor.courses.students', $c) }}">Học viên</a>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('instructor.courses.edit', $c) }}">Sửa</a>
                <form method="POST" action="{{ route('instructor.courses.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Xóa khóa học này cùng toàn bộ nội dung?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Xóa</button></form>
            </td>
        </tr>
    @empty
        <tr><td colspan="8" class="text-center text-muted py-4">Chưa có khóa học nào.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-3">{{ $courses->links('pagination::bootstrap-5') }}</div>
@endsection
