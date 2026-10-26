<?php

namespace App\Http\Controllers;

use App\Models\ChungChi;
use App\ChucNang\XuatBaoCaoCsv;
use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\TienDoBaiHoc;
use App\Models\BaiKiemTra;
use App\Models\LuotLamBai;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * File: XuatBaoCaoController.php
 * Xuất báo cáo CSV cho giảng viên sở hữu khóa học hoặc admin (ChinhSachKhoaHoc::manage).
 */
class XuatBaoCaoController extends Controller
{
    /** Danh sách học viên của khóa học kèm tiến độ và mã chứng chỉ. */
    public function hocVien(KhoaHoc $course): StreamedResponse
    {
        Gate::authorize('quanLy', $course);

        $lessonIds = $course->lessons()->pluck('lessons.id');
        $tongBai = $lessonIds->count();

        $soBaiXong = TienDoBaiHoc::whereIn('lesson_id', $lessonIds)
            ->where('is_completed', true)
            ->selectRaw('user_id, COUNT(*) as so_bai')
            ->groupBy('user_id')
            ->pluck('so_bai', 'user_id');

        $chungChi = ChungChi::where('course_id', $course->id)->pluck('ma_chung_chi', 'user_id');

        $cacDong = $course->enrollments()->with('user')->orderBy('enrolled_at')->get()
            ->values()
            ->map(function ($e, $i) use ($soBaiXong, $tongBai, $chungChi) {
                $xong = (int) ($soBaiXong[$e->user_id] ?? 0);

                return [
                    $i + 1,
                    $e->user->name,
                    $e->user->email,
                    $e->user->phone ?? '',
                    $e->enrolled_at?->format('d/m/Y H:i') ?? '',
                    $e->status === 'active' ? 'Đang học' : $e->status,
                    $xong,
                    $tongBai,
                    $tongBai > 0 ? (int) round($xong / $tongBai * 100) : 0,
                    $chungChi[$e->user_id] ?? '',
                ];
            });

        return XuatBaoCaoCsv::taiVe('hoc-vien-'.$course->title, [
            'STT', 'Họ tên', 'Email', 'Số điện thoại', 'Ngày đăng ký', 'Trạng thái',
            'Bài đã hoàn thành', 'Tổng số bài', 'Tiến độ (%)', 'Mã chứng chỉ',
        ], $cacDong);
    }

    /** Bảng điểm: mỗi học viên một dòng, mỗi bài kiểm tra lấy điểm cao nhất và kết quả. */
    public function bangDiem(KhoaHoc $course): StreamedResponse
    {
        Gate::authorize('quanLy', $course);

        $quizzes = $course->quizzes()->orderBy('id')->get();

        // Gom các lượt đã nộp theo (học viên, bài kiểm tra) bằng một truy vấn.
        $luotNop = LuotLamBai::whereIn('quiz_id', $quizzes->pluck('id'))
            ->whereNotNull('submitted_at')
            ->get(['user_id', 'quiz_id', 'score', 'passed'])
            ->groupBy(fn ($a) => $a->user_id.'-'.$a->quiz_id);

        $tieuDe = ['STT', 'Họ tên', 'Email'];
        foreach ($quizzes as $quiz) {
            $tieuDe[] = "Điểm cao nhất - {$quiz->title}";
            $tieuDe[] = "Số lần làm - {$quiz->title}";
            $tieuDe[] = "Kết quả - {$quiz->title}";
        }
        array_push($tieuDe, 'Số bài đạt', 'Điểm trung bình');

        $cacDong = $course->enrollments()->with('user')->orderBy('enrolled_at')->get()
            ->values()
            ->map(function ($e, $i) use ($quizzes, $luotNop) {
                $dong = [$i + 1, $e->user->name, $e->user->email];
                $soBaiDat = 0;
                $cacDiem = [];

                foreach ($quizzes as $quiz) {
                    $cacLuot = $luotNop->get($e->user_id.'-'.$quiz->id, collect());
                    if ($cacLuot->isEmpty()) {
                        array_push($dong, '', 0, 'Chưa làm');

                        continue;
                    }
                    $dat = $cacLuot->contains('passed', true);
                    $soBaiDat += $dat ? 1 : 0;
                    $cacDiem[] = $cacLuot->max('score');
                    array_push($dong, XuatBaoCaoCsv::diem($cacLuot->max('score')), $cacLuot->count(), $dat ? 'Đạt' : 'Chưa đạt');
                }

                $dong[] = $soBaiDat.'/'.$quizzes->count();
                $dong[] = $cacDiem === [] ? '' : XuatBaoCaoCsv::diem(array_sum($cacDiem) / count($cacDiem));

                return $dong;
            });

        return XuatBaoCaoCsv::taiVe('bang-diem-'.$course->title, $tieuDe, $cacDong);
    }

    /** Toàn bộ lượt nộp của một bài kiểm tra. */
    public function ketQuaBaiKiemTra(BaiKiemTra $quiz): StreamedResponse
    {
        Gate::authorize('quanLy', $quiz->course);

        $cacDong = $quiz->attempts()->whereNotNull('submitted_at')->with('user')->orderBy('submitted_at')->get()
            ->values()
            ->map(fn (LuotLamBai $a, $i) => [
                $i + 1,
                $a->user->name,
                $a->user->email,
                $a->started_at?->format('d/m/Y H:i:s') ?? '',
                $a->submitted_at->format('d/m/Y H:i:s'),
                $a->started_at ? round($a->started_at->diffInSeconds($a->submitted_at) / 60, 1) : '',
                $a->correct_count,
                $a->total_questions,
                XuatBaoCaoCsv::diem($a->score),
                $a->passed ? 'Đạt' : 'Chưa đạt',
            ]);

        return XuatBaoCaoCsv::taiVe('ket-qua-'.$quiz->title, [
            'STT', 'Họ tên', 'Email', 'Bắt đầu', 'Nộp lúc', 'Thời gian làm (phút)',
            'Số câu đúng', 'Tổng số câu', 'Điểm (thang 100)', 'Kết quả',
        ], $cacDong);
    }
}
