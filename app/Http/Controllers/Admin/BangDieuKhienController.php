<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\DangKyKhoaHoc;
use App\Models\LuotLamBai;
use App\Models\NguoiDung;
use App\ChucNang\BangDiemHocVien;
use App\ChucNang\DuLieuBieuDo;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Dashboard thống kê tổng quan dành cho quản trị viên. */
class BangDieuKhienController extends Controller
{
    public function hienThi(Request $request): View
    {
        $submitted = LuotLamBai::whereNotNull('submitted_at');

        return view('admin.dashboard', [
            'stats' => [
                'users' => NguoiDung::count(),
                'verified_users' => NguoiDung::where('is_active', true)->whereNotNull('email_verified_at')->count(),
                'pending_verification' => NguoiDung::where('is_active', true)->whereNull('email_verified_at')->count(),
                'students' => NguoiDung::where('role', NguoiDung::ROLE_STUDENT)->count(),
                'instructors' => NguoiDung::where('role', NguoiDung::ROLE_INSTRUCTOR)->count(),
                'courses' => KhoaHoc::count(),
                'published' => KhoaHoc::congKhai()->count(),
                'enrollments' => DangKyKhoaHoc::count(),
                'attempts' => (clone $submitted)->count(),
                'avg_score' => (clone $submitted)->avg('score'),
                'pass_rate' => ($n = (clone $submitted)->count())
                    ? round((clone $submitted)->where('passed', true)->count() / $n * 100) : null,
            ],
            'charts' => (new DuLieuBieuDo)->thanhMang(),
            'bangDiem' => (new BangDiemHocVien)->thanhMang($request),
            'recentEnrollments' => DangKyKhoaHoc::with(['user', 'course'])->latest('enrolled_at')->latest('id')->take(8)->get(),
            'recentAttempts' => LuotLamBai::with(['user', 'quiz.course'])->whereNotNull('submitted_at')
                ->latest('submitted_at')->take(8)->get(),
        ]);
    }
}
