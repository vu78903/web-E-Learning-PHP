<?php

namespace App\ChucNang;

use App\Models\KhoaHoc;
use App\Models\LuotLamBai;
use App\Models\NguoiDung;
use App\Notifications\CoKetQuaBaiThiNotification;
use App\Notifications\DangKyKhoaHocThanhCongNotification;
use Throwable;

/**
 * File: GuiEmailHocTap.php
 * Tập trung việc gửi email nghiệp vụ và không làm hỏng giao dịch chính khi SMTP gặp lỗi.
 */
class GuiEmailHocTap
{
    public static function guiDangKyKhoaHoc(NguoiDung $user, KhoaHoc $course): void
    {
        try {
            $course->loadMissing('instructor');
            $user->notify(new DangKyKhoaHocThanhCongNotification($course));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public static function guiKetQuaBaiThi(LuotLamBai $attempt): void
    {
        try {
            $attempt->loadMissing(['user', 'quiz.course']);
            $attempt->user->notify(new CoKetQuaBaiThiNotification($attempt));
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
