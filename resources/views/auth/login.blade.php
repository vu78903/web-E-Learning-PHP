@extends('layouts.app')
@section('title', 'Đăng nhập')
@section('content')
<div class="auth-shell">
    <div class="card auth-card border-0"><div class="row g-0">
    <div class="col-lg-5 d-none d-lg-block">
        <div class="auth-side">
            <i class="bi bi-lightning-charge-fill auth-icon"></i>
            <h2 class="mt-4">Tiếp tục hành trình của bạn.</h2>
            <p class="opacity-75">Mọi bài học, tiến độ và thành tích đều đang chờ bạn quay lại.</p>
            <ul class="mt-4"><li><i class="bi bi-check-circle-fill"></i> Học mọi lúc, mọi nơi</li><li><i class="bi bi-check-circle-fill"></i> Lưu tiến độ tự động</li><li><i class="bi bi-check-circle-fill"></i> Theo dõi kết quả rõ ràng</li></ul>
        </div>
    </div>
    <div class="col-lg-7"><div class="auth-form">
        <div class="hero-eyebrow">Chào mừng trở lại</div>
        <h2 class="mt-2 mb-1">Đăng nhập</h2>
        <p class="text-muted mb-4">Nhập thông tin tài khoản để tiếp tục học.</p>
        <form method="POST" action="{{ route('login') }}">@csrf
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Mật khẩu</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                <label for="remember" class="form-check-label">Ghi nhớ đăng nhập</label>
            </div>
            <button class="btn btn-primary btn-lg w-100">Đăng nhập <i class="bi bi-arrow-right ms-1"></i></button>
        </form>
        <div class="d-flex justify-content-between mt-3 small">
            <a href="{{ route('password.request') }}">Quên mật khẩu?</a>
            <span>Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a></span>
        </div>
    </div></div></div></div>
</div>
@endsection
