@extends('layouts.app')
@section('title', 'Đăng ký')
@section('content')
<div class="auth-shell">
    <div class="card auth-card border-0"><div class="row g-0">
    <div class="col-lg-5 d-none d-lg-block"><div class="auth-side">
        <i class="bi bi-stars auth-icon"></i>
        <h2 class="mt-4">Bắt đầu một điều mới.</h2>
        <p class="opacity-75">Tạo tài khoản để tiếp cận bài học, bài kiểm tra và chứng chỉ trên cùng một nền tảng.</p>
        <ul class="mt-4"><li><i class="bi bi-check-circle-fill"></i> Đăng ký hoàn toàn miễn phí</li><li><i class="bi bi-check-circle-fill"></i> Kho học liệu dễ truy cập</li><li><i class="bi bi-check-circle-fill"></i> Lộ trình rõ ràng</li></ul>
    </div></div>
    <div class="col-lg-7"><div class="auth-form">
        <div class="hero-eyebrow">Tham gia cộng đồng</div>
        <h2 class="mt-2 mb-1">Tạo tài khoản</h2>
        <p class="text-muted mb-4">Chỉ mất một phút để bắt đầu.</p>
        <form method="POST" action="{{ route('register') }}">@csrf
            <div class="mb-3">
                <label class="form-label">Họ và tên</label>
                <input name="name" value="{{ old('name') }}" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Số điện thoại</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" required
                       inputmode="numeric" maxlength="10" pattern="0[35789][0-9]{8}" placeholder="VD: 0912345678" title="Đúng 10 chữ số, bắt đầu bằng 03, 05, 07, 08 hoặc 09">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="8" maxlength="64" aria-describedby="pwhelp">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nhập lại mật khẩu</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
            <p id="pwhelp" class="form-text mt-0">Mật khẩu 8–64 ký tự, gồm chữ hoa, chữ thường, chữ số và ký tự đặc biệt (ví dụ: <code>Hoc@2026</code>).</p>
            <div class="mb-3">
                <label class="form-label">Bạn đăng ký với vai trò</label>
                <select name="role" class="form-select">
                    <option value="student" @selected(old('role', 'student') === 'student')>Học viên</option>
                    <option value="instructor" @selected(old('role') === 'instructor')>Giảng viên</option>
                </select>
            </div>
            <button class="btn btn-primary btn-lg w-100">Tạo tài khoản <i class="bi bi-arrow-right ms-1"></i></button>
        </form>
        <p class="mt-3 mb-0 small">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
    </div></div></div></div>
</div>
@endsection
