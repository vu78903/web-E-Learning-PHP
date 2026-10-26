<?php

namespace App\ChucNang;

use App\Models\KhoaHoc;
use App\Models\BaiKiemTra;
use App\Models\NguoiDung;

/**
 * File: KiemTraDieuKienChungChi.php
 * Điều kiện nhận chứng chỉ:
 *  1. Là học viên đang đăng ký khóa học (status = active);
 *  2. Khóa học có bài học và học viên đã hoàn thành 100% bài học;
 *  3. Đạt tất cả bài kiểm tra có câu hỏi của khóa học (ít nhất một lần nộp đạt).
 */
class KiemTraDieuKienChungChi
{
    /**
     * @return array{du_dieu_kien: bool, tien_do: int, ly_do: list<string>, bai_kiem_tra: list<array{ten: string, dat: bool, diem: ?float}>}
     */
    public static function kiemTra(NguoiDung $user, KhoaHoc $course): array
    {
        $lyDo = [];

        if (! $user->laHocVien() || ! $user->daDangKyKhoaHoc($course)) {
            $lyDo[] = 'Bạn chưa đăng ký khóa học này.';
        }

        $tongBai = $course->lessons()->count();
        $tienDo = $course->tienDoCua($user);
        if ($tongBai === 0) {
            $lyDo[] = 'Khóa học chưa có bài học nào.';
        } elseif ($tienDo < 100) {
            $lyDo[] = "Bạn mới hoàn thành {$tienDo}% bài học (cần 100%).";
        }

        $baiKiemTra = $course->quizzes()->has('questions')->orderBy('id')->get()
            ->map(function (BaiKiemTra $quiz) use ($user) {
                $luotNop = $quiz->attempts()->where('user_id', $user->id)->whereNotNull('submitted_at');

                return [
                    'ten' => $quiz->title,
                    'dat' => (clone $luotNop)->where('passed', true)->exists(),
                    'diem' => (clone $luotNop)->max('score'),
                ];
            })->all();

        foreach ($baiKiemTra as $bai) {
            if (! $bai['dat']) {
                $lyDo[] = "Chưa đạt bài kiểm tra \"{$bai['ten']}\".";
            }
        }

        return [
            'du_dieu_kien' => $lyDo === [],
            'tien_do' => $tienDo,
            'ly_do' => $lyDo,
            'bai_kiem_tra' => $baiKiemTra,
        ];
    }
}
