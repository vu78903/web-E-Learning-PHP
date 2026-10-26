<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Điều hướng người dùng về trang chủ làm việc tương ứng với vai trò. */
class BangDieuKhienController extends Controller
{
    public function chuyenHuong(Request $request): RedirectResponse
    {
        $user = $request->user();

        return match (true) {
            $user->laQuanTri() => redirect()->route('admin.dashboard'),
            $user->laGiangVien() => redirect()->route('instructor.dashboard'),
            default => redirect()->route('my-courses'),
        };
    }
}
