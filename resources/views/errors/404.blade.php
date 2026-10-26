@extends('layouts.app')
@section('title', 'Không tìm thấy trang')
@section('content')
<div class="text-center py-5">
    <div class="display-1 text-secondary"><i class="bi bi-compass"></i></div>
    <h2>404 — Không tìm thấy trang</h2>
    <p class="text-muted">Trang bạn tìm không tồn tại hoặc đã bị ẩn.</p>
    <a href="{{ route('home') }}" class="btn btn-primary">Về trang chủ</a>
</div>
@endsection
