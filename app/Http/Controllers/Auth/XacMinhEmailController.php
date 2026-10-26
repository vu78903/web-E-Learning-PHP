<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

/** File: XacMinhEmailController.php - Tiếp nhận và gửi lại liên kết xác minh email. */
class XacMinhEmailController extends Controller
{
    public function thongBao(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        return view('auth.xac-minh-email');
    }

    /**
     * Cho tab đăng ký ban đầu kiểm tra trạng thái sau khi người dùng bấm link
     * xác minh trên điện thoại hoặc một máy khác.
     */
    public function trangThai(Request $request): JsonResponse
    {
        $daXacMinh = $request->user()->refresh()->hasVerifiedEmail();

        return response()->json([
            'da_xac_minh' => $daXacMinh,
            'chuyen_huong' => $daXacMinh ? route('dashboard') : null,
        ]);
    }

    /**
     * Xác minh bằng URL có chữ ký, kể cả khi Gmail mở sang trình duyệt khác.
     * Không tự đăng nhập ở trình duyệt lạ để tránh biến link xác minh thành link đăng nhập.
     */
    public function xacMinh(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = NguoiDung::findOrFail($id);

        abort_unless(
            hash_equals(sha1($user->getEmailForVerification()), $hash),
            403,
            'Liên kết xác minh không hợp lệ.'
        );

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        if (Auth::id() === $user->id) {
            return redirect()->route('dashboard')
                ->with('success', 'Email đã được xác minh. Bạn đã hoàn tất đăng ký tài khoản.');
        }

        return redirect()->route('login')
            ->with('success', 'Xác minh email thành công. Bạn đã hoàn tất đăng ký và có thể đăng nhập.');
    }

    public function guiLai(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Chưa thể gửi email xác minh. Vui lòng kiểm tra cấu hình email hoặc thử lại sau.');
        }

        return back()->with('success', 'Đã gửi lại liên kết xác minh. Vui lòng kiểm tra hộp thư và thư rác.');
    }
}
