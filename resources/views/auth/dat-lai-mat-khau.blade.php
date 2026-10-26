@extends('layouts.app')
@section('title', 'Đặt lại mật khẩu')
@section('content')
<div class="row justify-content-center"><div class="col-md-6 col-lg-5">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h3>Đặt lại mật khẩu</h3>
        <form method="POST" action="{{ route('password.update') }}">@csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $email) }}" class="form-control @error('email') is-invalid @enderror" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Mật khẩu mới</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">8–64 ký tự, có chữ hoa, chữ thường, số và ký tự đặc biệt.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Nhập lại mật khẩu mới</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100">Lưu mật khẩu mới</button>
        </form>
    </div></div>
</div></div>
@endsection
