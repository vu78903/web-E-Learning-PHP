<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\ChucNang\QuyTacTaiKhoan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class TaiKhoanController extends Controller
{
    public function hienThiDangKy(): View
    {
        return view('auth.register');
    }

    public function dangKy(Request $request): RedirectResponse
    {
        QuyTacTaiKhoan::chuanHoa($request);

        $data = $request->validate([
            'name' => QuyTacTaiKhoan::hoTen(),
            'email' => QuyTacTaiKhoan::email(),
            'phone' => QuyTacTaiKhoan::soDienThoai(batBuoc: true),
            'password' => ['required', 'confirmed', 'different:email', QuyTacTaiKhoan::matKhau()],
            // Chỉ cho tự chọn học viên hoặc giảng viên; vai trò admin chỉ do quản trị viên cấp.
            'role' => ['required', Rule::in([NguoiDung::ROLE_STUDENT, NguoiDung::ROLE_INSTRUCTOR])],
        ]);

        $user = new NguoiDung(collect($data)->only(['name', 'email', 'phone', 'password'])->all());
        $user->role = $data['role'];
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();
        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('verification.notice')->with(
                'error',
                'Tài khoản đã được tạo nhưng hệ thống chưa gửi được email. Vui lòng kiểm tra cấu hình email rồi bấm "Gửi lại email xác minh".'
            );
        }

        return redirect()->route('dashboard')->with('success', 'Đăng ký thành công. Vui lòng kiểm tra email để hoàn tất xác minh tài khoản.');
    }

    public function hienThiDangNhap(): View
    {
        return view('auth.login');
    }

    public function dangNhap(Request $request): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email hoặc mật khẩu không chính xác.'])->onlyInput('email');
        }

        if (! Auth::user()->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => 'Tài khoản của bạn đã bị khóa.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function dangXuat(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
