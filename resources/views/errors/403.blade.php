@extends('layouts.app')
@section('title', 'Không có quyền truy cập')
@section('content')
<div class="text-center py-5">
    <div class="display-1 text-danger"><i class="bi bi-shield-lock"></i></div>
    <h2>403 — Không có quyền truy cập</h2>
    <p class="text-muted">{{ $exception->getMessage() ?: 'Bạn không có quyền thực hiện chức năng này.' }}</p>
    <a href="{{ route('home') }}" class="btn btn-primary">Về trang chủ</a>
</div>
@endsection
