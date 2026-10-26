<?php

namespace App\Policies;

use App\Models\KhoaHoc;
use App\Models\NguoiDung;

class ChinhSachKhoaHoc
{
    /** Quản lý (sửa/xóa/thêm nội dung): admin hoặc giảng viên sở hữu khóa học. */
    public function quanLy(NguoiDung $user, KhoaHoc $course): bool
    {
        return $user->laQuanTri() || ($user->laGiangVien() && $course->instructor_id === $user->id);
    }

    /** Truy cập nội dung học: người quản lý khóa học hoặc học viên đã đăng ký hợp lệ. */
    public function truyCap(NguoiDung $user, KhoaHoc $course): bool
    {
        return $this->quanLy($user, $course) || $user->daDangKyKhoaHoc($course);
    }
}
