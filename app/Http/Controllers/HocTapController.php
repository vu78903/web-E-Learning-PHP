<?php

namespace App\Http\Controllers;

use App\Models\KhoaHoc;
use App\Models\BaiHoc;
use App\Models\TaiLieuBaiHoc;
use App\Models\TienDoBaiHoc;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Học nội dung khóa học. Quyền truy cập luôn được kiểm tra ở phía máy chủ
 * (ChinhSachKhoaHoc::access): chỉ học viên đã đăng ký hoặc người quản lý khóa học.
 */
class HocTapController extends Controller
{
    public function tongQuan(Request $request, KhoaHoc $course): View
    {
        Gate::authorize('truyCap', $course);

        $course->load([
            'sections.lessons', 'quizzes.questions',
            'danhGia' => fn ($query) => $query->with('user')->latest(),
        ]);

        $user = $request->user();
        $enrolled = $user->laHocVien() && $user->daDangKyKhoaHoc($course);

        return view('learn.show', $this->duLieuThanhBen($request, $course) + [
            'enrolled' => $enrolled,
            'danhGia' => $course->danhGia,
            'danhGiaCuaToi' => $enrolled ? $course->danhGia->firstWhere('user_id', $user->id) : null,
            'diemDanhGia' => $course->danhGia->avg('so_sao'),
            'soLuongDanhGia' => $course->danhGia->count(),
        ]);
    }

    public function baiHoc(Request $request, KhoaHoc $course, BaiHoc $lesson): View
    {
        Gate::authorize('truyCap', $course);
        abort_unless($lesson->section->course_id === $course->id, 404);

        $course->load(['sections.lessons', 'quizzes.questions']);
        $ordered = $course->sections->flatMap->lessons->values();
        $index = $ordered->search(fn (BaiHoc $l) => $l->id === $lesson->id);

        return view('learn.lesson', $this->duLieuThanhBen($request, $course) + [
            'lesson' => $lesson->load('attachments'),
            'prev' => $index > 0 ? $ordered[$index - 1] : null,
            'next' => $ordered[$index + 1] ?? null,
        ]);
    }

    /** Đánh dấu hoàn thành / bỏ đánh dấu một bài học. */
    public function danhDauHoanThanh(Request $request, BaiHoc $lesson): RedirectResponse
    {
        $course = $lesson->section->course;
        Gate::authorize('truyCap', $course);

        $progress = TienDoBaiHoc::firstOrNew([
            'user_id' => $request->user()->id,
            'lesson_id' => $lesson->id,
        ]);

        $done = ! ($progress->exists && $progress->is_completed);
        $progress->is_completed = $done;
        $progress->completed_at = $done ? now() : null;
        $progress->save();

        return back()->with('success', $done ? 'Đã đánh dấu hoàn thành bài học.' : 'Đã bỏ đánh dấu hoàn thành.');
    }

    /** Xem tài liệu PDF ngay trên trình duyệt (inline). */
    public function xemTaiLieu(TaiLieuBaiHoc $attachment): StreamedResponse
    {
        return $this->traTaiLieu($attachment, 'inline');
    }

    /** Tải tài liệu PDF về máy. */
    public function taiTaiLieu(TaiLieuBaiHoc $attachment): StreamedResponse
    {
        return $this->traTaiLieu($attachment, 'attachment');
    }

    /** Chỉ học viên đã đăng ký, giảng viên chủ khóa hoặc admin mới được truy cập file. */
    private function traTaiLieu(TaiLieuBaiHoc $attachment, string $disposition): StreamedResponse
    {
        Gate::authorize('truyCap', $attachment->lesson->section->course);

        $disk = Storage::disk(TaiLieuBaiHoc::DISK);
        abort_unless($disk->exists($attachment->path), 404, 'Không tìm thấy file tài liệu.');

        return $disk->response($attachment->path, $attachment->original_name, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], $disposition);
    }

    private function duLieuThanhBen(Request $request, KhoaHoc $course): array
    {
        $user = $request->user();
        $laHocVien = $user->laHocVien();

        return [
            'course' => $course,
            'laHocVien' => $laHocVien,
            'completedIds' => $laHocVien
                ? TienDoBaiHoc::where('user_id', $user->id)->where('is_completed', true)->pluck('lesson_id')->all()
                : [],
            'progress' => $laHocVien ? $course->tienDoCua($user) : 0,
        ];
    }
}
