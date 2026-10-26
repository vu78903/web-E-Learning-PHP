<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\ChucNang\QuyTacTaiKhoan;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** File: QuenMatKhauController.php - Gửi liên kết và xử lý đặt lại mật khẩu. */
class QuenMatKhauController extends Controller
{
    public function hienThiYeuCau(): View
    {
        return view('auth.quen-mat-khau');
    }

    public function guiLienKet(Request $request): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate(['email' => ['required', 'email']]);
        $email = $data['email'];

        // Chỉ học viên và giảng viên đang hoạt động được nhận email, nhưng luôn trả
        // cùng một thông báo để người lạ không dò được email có trong hệ thống.
        $duocPhep = NguoiDung::where('email', $email)
            ->where('is_active', true)
            ->whereIn('role', [NguoiDung::ROLE_STUDENT, NguoiDung::ROLE_INSTRUCTOR])
            ->exists();

        if ($duocPhep) {
            Password::sendResetLink(['email' => $email]);
        }

        return back()->with('success', 'Nếu email thuộc một tài khoản hợp lệ, liên kết đặt lại mật khẩu đã được gửi.');
    }

    public function hienThiDatLai(Request $request, string $token): View
    {
        return view('auth.dat-lai-mat-khau', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function datLaiMatKhau(Request $request): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'different:email', QuyTacTaiKhoan::matKhau()],
        ]);

        $user = NguoiDung::where('email', $data['email'])
            ->where('is_active', true)
            ->whereIn('role', [NguoiDung::ROLE_STUDENT, NguoiDung::ROLE_INSTRUCTOR])
            ->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Liên kết đặt lại mật khẩu không hợp lệ.']);
        }

        $status = Password::reset($data, function (NguoiDung $user, string $password): void {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();

            event(new PasswordReset($user));
        });

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($status)])->withInput($request->only('email'));
        }

        return redirect()->route('login')->with('success', 'Đặt lại mật khẩu thành công. Bạn có thể đăng nhập bằng mật khẩu mới.');
    }
}
