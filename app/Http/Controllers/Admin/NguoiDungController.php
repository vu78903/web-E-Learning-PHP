<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\ChucNang\QuyTacTaiKhoan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class NguoiDungController extends Controller
{
    public function danhSach(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(array_keys(NguoiDung::ROLES))],
        ]);

        $users = NguoiDung::query()
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $term = '%'.trim($request->query('q')).'%';
                $w->where('name', 'like', $term)->orWhere('email', 'like', $term);
            }))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->query('role')))
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function chinhSua(NguoiDung $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function capNhat(Request $request, NguoiDung $user): RedirectResponse
    {
        QuyTacTaiKhoan::chuanHoa($request);

        $data = $request->validate([
            'name' => QuyTacTaiKhoan::hoTen(),
            'email' => QuyTacTaiKhoan::email($user->id),
            'phone' => QuyTacTaiKhoan::soDienThoai(batBuoc: false, boQuaNguoiDungId: $user->id),
            'role' => ['required', Rule::in(array_keys(NguoiDung::ROLES))],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', QuyTacTaiKhoan::matKhau()],
        ]);

        $isSelf = $user->id === $request->user()->id;
        if ($isSelf && ($data['role'] !== NguoiDung::ROLE_ADMIN || ! $request->boolean('is_active'))) {
            return back()->withErrors(['role' => 'Bạn không thể tự hạ quyền hoặc khóa chính tài khoản của mình.'])->withInput();
        }

        $user->fill(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null]);
        $user->role = $data['role'];
        $user->is_active = $request->boolean('is_active');
        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Đã cập nhật tài khoản.');
    }

    public function xoa(Request $request, NguoiDung $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Bạn không thể xóa chính tài khoản của mình.');
        }
        if ($user->taughtCourses()->exists()) {
            return back()->with('error', 'Giảng viên này còn khóa học. Hãy xóa hoặc chuyển khóa học trước, hoặc chỉ khóa tài khoản.');
        }

        $user->delete();

        return back()->with('success', 'Đã xóa tài khoản.');
    }
}
