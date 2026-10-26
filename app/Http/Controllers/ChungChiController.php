<?php

namespace App\Http\Controllers;

use App\Models\ChungChi;
use App\ChucNang\KiemTraDieuKienChungChi;
use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** File: ChungChiController.php - Cấp, xem/in và xác thực chứng chỉ hoàn thành. */
class ChungChiController extends Controller
{
    /** Học viên bấm "Nhận chứng chỉ": kiểm tra điều kiện rồi cấp (mỗi khóa một lần). */
    public function nhan(Request $request, KhoaHoc $course): RedirectResponse
    {
        $user = $request->user();

        $daCo = ChungChi::where('user_id', $user->id)->where('course_id', $course->id)->first();
        if ($daCo) {
            return redirect()->route('chung_chi.xem', $daCo);
        }

        $dieuKien = KiemTraDieuKienChungChi::kiemTra($user, $course);
        if (! $dieuKien['du_dieu_kien']) {
            return back()->with('error', 'Chưa đủ điều kiện nhận chứng chỉ. '.implode(' ', $dieuKien['ly_do']));
        }

        try {
            $chungChi = ChungChi::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'ma_chung_chi' => ChungChi::taoMaMoi(),
                'cap_luc' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            // Bấm hai lần liên tiếp: bản ghi đã được tạo ở lần trước.
            $chungChi = ChungChi::where('user_id', $user->id)->where('course_id', $course->id)->firstOrFail();
        }

        return redirect()->route('chung_chi.xem', $chungChi)
            ->with('success', 'Chúc mừng! Bạn đã nhận chứng chỉ hoàn thành khóa học.');
    }

    /** Trang chứng chỉ để in / lưu PDF: chủ chứng chỉ, giảng viên của khóa học hoặc admin. */
    public function xem(Request $request, ChungChi $chungChi): View
    {
        $user = $request->user();
        $chungChi->load(['user', 'course.instructor']);

        abort_unless($chungChi->user_id === $user->id || $user->can('quanLy', $chungChi->course), 403);

        return view('chung-chi.in', [
            'chungChi' => $chungChi,
            'soBaiHoc' => $chungChi->course->lessons()->count(),
            'diemKiemTra' => $this->diemTrungBinh($chungChi),
        ]);
    }

    /** Trang xác thực công khai: chỉ hiện thông tin tối thiểu, không lộ email. */
    public function xacThuc(ChungChi $chungChi): View
    {
        $chungChi->load(['user', 'course.instructor']);

        return view('chung-chi.xac-thuc', ['chungChi' => $chungChi]);
    }

    /** Trung bình điểm cao nhất của học viên ở từng bài kiểm tra (null nếu khóa không có bài kiểm tra). */
    private function diemTrungBinh(ChungChi $chungChi): ?float
    {
        $diemCaoNhat = $chungChi->course->quizzes()->has('questions')->get()
            ->map(fn ($quiz) => $quiz->attempts()
                ->where('user_id', $chungChi->user_id)
                ->whereNotNull('submitted_at')
                ->max('score'))
            ->filter(fn ($diem) => $diem !== null);

        return $diemCaoNhat->isEmpty() ? null : round($diemCaoNhat->avg(), 1);
    }
}
