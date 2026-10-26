<?php

namespace App\Http\Controllers;

use App\Models\DanhMuc;
use App\Models\KhoaHoc;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Danh sách và chi tiết khóa học dành cho mọi người (khách, học viên...). */
class KhoaHocController extends Controller
{
    public function danhSach(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer'],
            'sort' => ['nullable', 'in:newest,popular,title'],
        ]);

        $query = KhoaHoc::congKhai()->with(['category', 'instructor'])->withCount(['enrollments', 'lessons']);

        if ($q = trim((string) $request->query('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->integer('category'));
        }

        match ($request->query('sort')) {
            'popular' => $query->orderByDesc('enrollments_count'),
            'title' => $query->orderBy('title'),
            default => $query->latest(),
        };

        $courses = $query->paginate(9)->withQueryString();

        // AJAX: chỉ trả về phần danh sách để cập nhật không cần tải lại trang.
        if ($request->ajax()) {
            return view('courses._list', ['courses' => $courses]);
        }

        return view('courses.index', [
            'courses' => $courses,
            'categories' => DanhMuc::orderBy('name')->get(),
        ]);
    }

    public function chiTiet(Request $request, KhoaHoc $course): View
    {
        $user = $request->user();
        $canManage = $user && $user->can('quanLy', $course);

        // Khóa học chưa công khai chỉ chủ khóa học hoặc admin xem được.
        abort_unless($course->daCongKhai() || $canManage, 404);

        $course->load([
            'category', 'instructor', 'sections.lessons', 'quizzes',
            'danhGia' => fn ($query) => $query->with('user')->latest(),
        ])->loadCount('enrollments');

        $danhGiaCuaToi = $user?->laHocVien()
            ? $course->danhGia->firstWhere('user_id', $user->id)
            : null;

        return view('courses.show', [
            'course' => $course,
            'enrolled' => $user && $user->daDangKyKhoaHoc($course),
            'canManage' => $canManage,
            'progress' => $user && $user->laHocVien() ? $course->tienDoCua($user) : 0,
            'danhGia' => $course->danhGia,
            'danhGiaCuaToi' => $danhGiaCuaToi,
            'diemDanhGia' => $course->danhGia->avg('so_sao'),
            'soLuongDanhGia' => $course->danhGia->count(),
        ]);
    }
}
