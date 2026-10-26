<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\DangKyKhoaHoc;
use App\Models\LuotLamBai;
use App\ChucNang\BangDiemHocVien;
use App\ChucNang\DuLieuBieuDo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Dashboard thống kê của giảng viên: chỉ tính các khóa học do chính giảng viên phụ trách. */
class BangDieuKhienController extends Controller
{
    public function hienThi(Request $request): View
    {
        $id = $request->user()->id;
        $own = fn (Builder $q) => $q->where('instructor_id', $id);

        $enrollments = DangKyKhoaHoc::whereHas('course', $own);
        $submitted = LuotLamBai::whereNotNull('submitted_at')->whereHas('quiz.course', $own);
        $attempts = (clone $submitted)->count();

        return view('instructor.dashboard', [
            'stats' => [
                'courses' => KhoaHoc::where('instructor_id', $id)->count(),
                'published' => KhoaHoc::where('instructor_id', $id)->congKhai()->count(),
                'enrollments' => (clone $enrollments)->count(),
                'students' => (clone $enrollments)->distinct()->count('user_id'),
                'attempts' => $attempts,
                'avg_score' => (clone $submitted)->avg('score'),
                'pass_rate' => $attempts ? round((clone $submitted)->where('passed', true)->count() / $attempts * 100) : null,
            ],
            'charts' => (new DuLieuBieuDo($id))->thanhMang(),
            'bangDiem' => (new BangDiemHocVien($id))->thanhMang($request),
            'recentEnrollments' => (clone $enrollments)->with(['user', 'course'])
                ->latest('enrolled_at')->latest('id')->take(8)->get(),
            'recentAttempts' => (clone $submitted)->with(['user', 'quiz.course'])
                ->latest('submitted_at')->take(8)->get(),
        ]);
    }
}
