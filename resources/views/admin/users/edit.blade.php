@extends('layouts.app')
@section('title', 'Sửa tài khoản')
@section('content')
<div class="row justify-content-center"><div class="col-lg-6">
    <div class="card border-0 shadow-sm"><div class="card-body p-4">
        <h3 class="mb-3">Sửa tài khoản #{{ $user->id }}</h3>
        @if(! $user->hasVerifiedEmail())
            <div class="alert alert-warning"><i class="bi bi-envelope-exclamation"></i> Email của tài khoản này chưa được xác minh. Bật “Tài khoản đang hoạt động” không thay thế bước xác minh email.</div>
        @endif
        <form method="POST" action="{{ route('admin.users.update', $user) }}">@csrf @method('PUT')
            <div class="mb-3"><label class="form-label">Họ tên</label><input name="name" value="{{ old('name', $user->name) }}" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Số điện thoại</label><input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control @error('phone') is-invalid @enderror" maxlength="10" pattern="0[35789][0-9]{8}">@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label">Vai trò</label>
                <select name="role" class="form-select">
                    @foreach(\App\Models\NguoiDung::ROLES as $k => $label)<option value="{{ $k }}" @selected(old('role', $user->role) === $k)>{{ $label }}</option>@endforeach
                </select></div>
            <div class="form-check form-switch mb-3">
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $user->is_active))>
                <label class="form-check-label" for="is_active">Tài khoản đang hoạt động</label></div>
            <div class="mb-3"><label class="form-label">Đặt lại mật khẩu (bỏ trống nếu không đổi)</label><input type="password" name="password" class="form-control" autocomplete="new-password"></div>
            <button class="btn btn-primary">Lưu</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-link">Hủy</a>
        </form>
    </div></div>
</div></div>
@endsection
