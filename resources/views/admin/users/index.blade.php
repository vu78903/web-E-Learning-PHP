@extends('layouts.app')
@section('title', 'Quản lý người dùng')
@section('content')
<h2 class="mb-3">Quản lý người dùng</h2>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-5"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Tìm theo tên hoặc email"></div>
    <div class="col-md-3"><select name="role" class="form-select">
        <option value="">Mọi vai trò</option>
        @foreach(\App\Models\NguoiDung::ROLES as $k => $label)<option value="{{ $k }}" @selected(request('role') === $k)>{{ $label }}</option>@endforeach
    </select></div>
    <div class="col-md-2 d-grid"><button class="btn btn-primary">Lọc</button></div>
</form>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table align-middle mb-0">
    <thead><tr><th>#</th><th>Họ tên</th><th>Email</th><th>Vai trò</th><th>Trạng thái</th><th>Ngày tạo</th><th></th></tr></thead>
    <tbody>
    @foreach($users as $u)
        <tr>
            <td>{{ $u->id }}</td><td>{{ $u->name }}</td><td>{{ $u->email }}</td>
            <td><span class="badge text-bg-{{ ['admin' => 'danger', 'instructor' => 'primary', 'student' => 'secondary'][$u->role] }}">{{ $u->tenVaiTro() }}</span></td>
            <td>
                @if(! $u->is_active)
                    <span class="badge text-bg-dark">Đã khóa</span>
                @elseif(! $u->hasVerifiedEmail())
                    <span class="badge text-bg-warning">Chờ xác minh</span>
                @else
                    <span class="badge text-bg-success">Hoạt động</span>
                @endif
            </td>
            <td>{{ $u->created_at->format('d/m/Y') }}</td>
            <td class="text-end text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit', $u) }}">Sửa</a>
                @if($u->id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Xóa tài khoản này?')">
                    @csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Xóa</button></form>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table></div></div>
<div class="mt-3">{{ $users->links('pagination::bootstrap-5') }}</div>
@endsection
