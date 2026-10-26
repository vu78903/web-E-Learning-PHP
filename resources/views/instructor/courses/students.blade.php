@extends('layouts.app')
@section('title', 'Học viên: '.$course->title)
@section('content')
<nav aria-label="breadcrumb"><ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ route('instructor.courses.index') }}">Khóa học</a></li>
    <li class="breadcrumb-item active">{{ $course->title }}</li>
</ol></nav>
<h3 class="mb-3">Học viên đã đăng ký ({{ $students->count() }})</h3>
@include('partials.nut-csv-khoa-hoc')
<div class="card border-0 shadow-sm"><table class="table mb-0 align-middle">
    <thead><tr><th>Họ tên</th><th>Email</th><th>Ngày đăng ký</th><th style="width:25%">Tiến độ</th></tr></thead>
    <tbody>
    @forelse($students as $e)
        <tr>
            <td>{{ $e->user->name }}</td><td>{{ $e->user->email }}</td>
            <td>{{ $e->enrolled_at?->format('d/m/Y') }}</td>
            <td><div class="progress"><div class="progress-bar" style="width:{{ $e->progress }}%">{{ $e->progress }}%</div></div></td>
        </tr>
    @empty
        <tr><td colspan="4" class="text-center text-muted py-3">Chưa có học viên nào.</td></tr>
    @endforelse
    </tbody>
</table></div>
@endsection
