<?php

namespace App\Http\Controllers;

use App\ChucNang\QuyTacTaiKhoan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HoSoController extends Controller
{
    public function chinhSua(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function capNhat(Request $request): RedirectResponse
    {
        $user = $request->user();
        $emailCu = $user->email;

        QuyTacTaiKhoan::chuanHoa($request);

        $data = $request->validate([
            'name' => QuyTacTaiKhoan::hoTen(),
            'email' => QuyTacTaiKhoan::email($user->id),
            'phone' => QuyTacTaiKhoan::soDienThoai(batBuoc: false, boQuaNguoiDungId: $user->id),
            'bio' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', 'different:email', 'different:current_password', QuyTacTaiKhoan::matKhau()],
        ]);

        $user->fill(collect($data)->only(['name', 'email', 'phone', 'bio'])->all());
        if ($request->hasFile('avatar')) {
            $this->xoaAnhDaiDien($user->avatar);
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        } elseif ($request->boolean('remove_avatar')) {
            $this->xoaAnhDaiDien($user->avatar);
            $user->avatar = null;
        }
        if (! empty($data['password'])) {
            $user->password = $data['password']; // cast 'hashed' tự băm mật khẩu
        }
        $user->save();

        // Đổi địa chỉ email thì học viên/giảng viên phải xác minh lại địa chỉ mới.
        if ($emailCu !== $user->email && ! $user->laQuanTri()) {
            $user->markEmailAsUnverified();
            $user->sendEmailVerificationNotification();

            return redirect()->route('verification.notice')
                ->with('success', 'Email đã thay đổi. Vui lòng xác minh địa chỉ email mới.');
        }

        return back()->with('success', 'Đã cập nhật hồ sơ cá nhân.');
    }

    private function xoaAnhDaiDien(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
