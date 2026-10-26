@extends('layouts.app')
@section('title', 'Quên mật khẩu')
@section('content')
<div class="row justify-content-center"><div class="col-md-6 col-lg-5">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h3>Quên mật khẩu</h3>
        <p class="text-muted">Nhập email của tài khoản học viên hoặc giảng viên để nhận liên kết đặt lại mật khẩu.</p>
        <form method="POST" action="{{ route('password.email') }}">@csrf
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary w-100">Gửi liên kết đặt lại mật khẩu</button>
        </form>
    </div></div>
</div></div>
@endsection
