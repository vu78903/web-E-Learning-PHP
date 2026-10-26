@extends('layouts.app')
@section('title', 'Hồ sơ cá nhân')
@section('content')
<div class="row justify-content-center"><div class="col-md-7">
    <div class="card shadow-sm border-0"><div class="card-body p-4">
        <h3 class="mb-3">Hồ sơ cá nhân <span class="badge text-bg-secondary fs-6">{{ $user->tenVaiTro() }}</span></h3>
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">@csrf @method('PUT')
            <div class="mb-3 d-flex align-items-center gap-3">
                @if($user->duongDanAnhDaiDien())
                    <img src="{{ $user->duongDanAnhDaiDien() }}" alt="Ảnh đại diện" class="rounded-circle border" width="72" height="72" style="object-fit:cover">
                @else
                    <i class="bi bi-person-circle text-secondary" style="font-size:4.5rem;line-height:1"></i>
                @endif
                <div class="flex-grow-1">
                    <label class="form-label mb-1">Ảnh đại diện (JPG/PNG/WEBP, tối đa 2 MB)</label>
                    <input type="file" name="avatar" accept="image/*" class="form-control @error('avatar') is-invalid @enderror">
                    @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($user->avatar)
                        <div class="form-check mt-1"><input class="form-check-input" type="checkbox" name="remove_avatar" value="1" id="rm"><label class="form-check-label small" for="rm">Xóa ảnh hiện tại</label></div>
                    @endif
                </div>
            </div>
            <div class="mb-3"><label class="form-label">Họ và tên</label>
                <input name="name" value="{{ old('name', $user->name) }}" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Số điện thoại</label>
                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control @error('phone') is-invalid @enderror"
                       inputmode="numeric" maxlength="10" pattern="0[35789][0-9]{8}" placeholder="VD: 0912345678">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label">Giới thiệu</label>
                <textarea name="bio" rows="3" class="form-control">{{ old('bio', $user->bio) }}</textarea></div>
            <hr>
            <p class="text-muted small">Chỉ điền phần dưới nếu muốn đổi mật khẩu (8–64 ký tự, gồm chữ hoa, chữ thường, chữ số và ký tự đặc biệt).</p>
            <div class="mb-3"><label class="form-label">Mật khẩu hiện tại</label>
                <input type="password" name="current_password" class="form-control"></div>
            <div class="row">
                <div class="col-md-6 mb-3"><label class="form-label">Mật khẩu mới</label>
                    <input type="password" name="password" class="form-control"></div>
                <div class="col-md-6 mb-3"><label class="form-label">Nhập lại mật khẩu mới</label>
                    <input type="password" name="password_confirmation" class="form-control"></div>
            </div>
            <button class="btn btn-primary">Lưu thay đổi</button>
        </form>
    </div></div>
</div></div>
@endsection
