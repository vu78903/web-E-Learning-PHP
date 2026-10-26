<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DanhMuc;
use App\Models\KhoaHoc;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin duyệt/ẩn khóa học trên toàn hệ thống. */
class KhoaHocController extends Controller
{
    public function danhSach(Request $request): View
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_keys(KhoaHoc::STATUSES))],
            'category' => ['nullable', 'integer'],
        ]);

        $courses = KhoaHoc::with(['category', 'instructor'])->withCount('enrollments')
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%'.trim($request->query('q')).'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.courses.index', [
            'courses' => $courses,
            'categories' => DanhMuc::orderBy('name')->get(),
        ]);
    }

    public function capNhatTrangThai(Request $request, KhoaHoc $course): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(KhoaHoc::STATUSES))]]);
        $course->update($data);

        return back()->with('success', 'Đã đổi trạng thái khóa học thành "'.$course->tenTrangThai().'".');
    }
}
