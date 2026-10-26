<?php

namespace App\Http\Controllers;

use App\Models\KhoaHoc;
use App\Models\DangKyKhoaHoc;
use App\ChucNang\GuiEmailHocTap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DangKyKhoaHocController extends Controller
{
    /** Danh sách khóa học đã đăng ký kèm tiến độ. */
    public function khoaHocCuaToi(Request $request): View
    {
        $user = $request->user();

        $courses = $user->enrolledCourses()
            ->wherePivot('status', 'active')
            ->with(['category', 'instructor'])
            ->orderByPivot('enrolled_at', 'desc')
            ->get()
            ->each(fn (KhoaHoc $c) => $c->progress = $c->tienDoCua($user));

        return view('my-courses', ['courses' => $courses]);
    }

    public function dangKy(Request $request, KhoaHoc $course): RedirectResponse
    {
        abort_unless($course->daCongKhai(), 404);

        $user = $request->user();

        // firstOrCreate + unique key (user_id, course_id) => không bao giờ đăng ký trùng.
        $enrollment = DangKyKhoaHoc::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['enrolled_at' => now(), 'status' => 'active']
        );

        if (! $enrollment->wasRecentlyCreated) {
            if ($enrollment->status !== 'active') {
                $enrollment->update(['status' => 'active']);
                GuiEmailHocTap::guiDangKyKhoaHoc($user, $course);

                return redirect()->route('learn.show', $course)->with('success', 'Đã kích hoạt lại đăng ký khóa học.');
            }

            return redirect()->route('courses.show', $course)->with('info', 'Bạn đã đăng ký khóa học này rồi.');
        }

        GuiEmailHocTap::guiDangKyKhoaHoc($user, $course);

        return redirect()->route('learn.show', $course)->with('success', 'Đăng ký khóa học thành công. Chúc bạn học tốt!');
    }
}
