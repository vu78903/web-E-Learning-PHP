<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\DanhGiaKhoaHoc;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** File: DanhGiaKhoaHocController.php - Lưu/cập nhật sao và bình luận bằng AJAX. */
class DanhGiaKhoaHocController extends Controller
{
    public function luuDanhGia(Request $request, KhoaHoc $course): JsonResponse
    {
        $user = $request->user();

        abort_unless($course->daCongKhai(), 404);
        abort_unless($user->daDangKyKhoaHoc($course), 403, 'Bạn cần đăng ký khóa học trước khi đánh giá.');

        $data = $request->validate([
            'so_sao' => ['required', 'integer', 'between:1,5'],
            'binh_luan' => ['required', 'string', 'min:2', 'max:1000'],
        ]);

        DanhGiaKhoaHoc::updateOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['so_sao' => $data['so_sao'], 'binh_luan' => trim($data['binh_luan'])]
        );

        $course->load(['danhGia' => fn ($query) => $query->with('user')->latest()]);

        return response()->json([
            'thong_bao' => 'Đã lưu đánh giá của bạn.',
            'so_luong' => $course->danhGia->count(),
            'trung_binh' => round((float) $course->danhGia->avg('so_sao'), 1),
            'html' => view('danh-gia.danh-sach', ['danhGia' => $course->danhGia])->render(),
        ]);
    }
}
