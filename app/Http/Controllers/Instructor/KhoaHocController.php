<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\DanhMuc;
use App\Models\KhoaHoc;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Giảng viên quản lý khóa học của mình (admin quản lý được mọi khóa học). */
class KhoaHocController extends Controller
{
    public function danhSach(Request $request): View
    {
        $user = $request->user();

        $courses = KhoaHoc::with(['category', 'instructor'])
            ->withCount(['enrollments', 'lessons', 'quizzes'])
            ->when(! $user->laQuanTri(), fn ($q) => $q->where('instructor_id', $user->id))
            ->latest()
            ->paginate(10);

        return view('instructor.courses.index', compact('courses'));
    }

    public function taoMoi(): View
    {
        return view('instructor.courses.form', [
            'course' => new KhoaHoc(['status' => KhoaHoc::STATUS_DRAFT]),
            'categories' => DanhMuc::orderBy('name')->get(),
        ]);
    }

    public function luu(Request $request): RedirectResponse
    {
        $data = $this->duLieuHopLe($request);

        $course = new KhoaHoc(collect($data)->except('thumbnail')->all());
        $course->instructor_id = $request->user()->id;
        if ($request->hasFile('thumbnail')) {
            $course->thumbnail = $request->file('thumbnail')->store('thumbnails', 'public');
        }
        $course->save();

        return redirect()->route('instructor.curriculum', $course)
            ->with('success', 'Đã tạo khóa học. Hãy thêm chương và bài học.');
    }

    public function chinhSua(KhoaHoc $course): View
    {
        Gate::authorize('quanLy', $course);

        return view('instructor.courses.form', [
            'course' => $course,
            'categories' => DanhMuc::orderBy('name')->get(),
        ]);
    }

    public function capNhat(Request $request, KhoaHoc $course): RedirectResponse
    {
        Gate::authorize('quanLy', $course);

        $data = $this->duLieuHopLe($request);

        // Khóa học bị admin ẩn thì giảng viên không tự công khai lại được.
        if ($course->status === KhoaHoc::STATUS_HIDDEN && ! $request->user()->laQuanTri()) {
            $data['status'] = KhoaHoc::STATUS_HIDDEN;
        }

        $course->fill(collect($data)->except('thumbnail')->all());
        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $course->thumbnail = $request->file('thumbnail')->store('thumbnails', 'public');
        }
        $course->save();

        return redirect()->route('instructor.courses.index')->with('success', 'Đã cập nhật khóa học.');
    }

    public function xoa(KhoaHoc $course): RedirectResponse
    {
        Gate::authorize('quanLy', $course);

        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }
        $course->delete();

        return redirect()->route('instructor.courses.index')->with('success', 'Đã xóa khóa học.');
    }

    public function hocVien(KhoaHoc $course): View
    {
        Gate::authorize('quanLy', $course);

        $students = $course->enrollments()->with('user')->latest('enrolled_at')->get()
            ->each(fn ($e) => $e->progress = $course->tienDoCua($e->user));

        return view('instructor.courses.students', compact('course', 'students'));
    }

    private function duLieuHopLe(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            // Trạng thái "ẩn" do admin quyết định; giảng viên chỉ chọn nháp/công khai.
            'status' => ['required', Rule::in(
                $request->user()->laQuanTri()
                    ? array_keys(KhoaHoc::STATUSES)
                    : [KhoaHoc::STATUS_DRAFT, KhoaHoc::STATUS_PUBLISHED]
            )],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
        ]);
    }
}
