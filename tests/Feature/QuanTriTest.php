<?php

namespace Tests\Feature;

use App\Models\DanhMuc;
use App\Models\KhoaHoc;
use App\Models\LuotLamBai;
use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\TaoDuLieu;
use Tests\TestCase;

class QuanTriTest extends TestCase
{
    use TaoDuLieu, RefreshDatabase;

    public function test_dashboard_shows_correct_numbers(): void
    {
        $admin = $this->quanTri();
        $inst = $this->giangVien();
        $course = $this->khoaHocCoBaiHoc($inst, 1);
        $this->taoKhoaHoc($inst, KhoaHoc::STATUS_DRAFT);
        $quiz = $this->taoBaiKiemTra($course);
        $s1 = $this->hocVien();
        $s2 = $this->hocVien();
        $this->dangKyKhoaHoc($s1, $course);
        $this->dangKyKhoaHoc($s2, $course);
        LuotLamBai::create(['quiz_id' => $quiz->id, 'user_id' => $s1->id, 'started_at' => now(), 'submitted_at' => now(),
            'score' => 100, 'correct_count' => 4, 'total_questions' => 4, 'passed' => true]);
        LuotLamBai::create(['quiz_id' => $quiz->id, 'user_id' => $s2->id, 'started_at' => now(), 'submitted_at' => now(),
            'score' => 25, 'correct_count' => 1, 'total_questions' => 4, 'passed' => false]);
        LuotLamBai::create(['quiz_id' => $quiz->id, 'user_id' => $s2->id, 'started_at' => now()]); // chưa nộp: không tính

        $response = $this->actingAs($admin)->get('/admin')->assertOk();
        $stats = $response->viewData('stats');

        $this->assertSame(4, $stats['users']);
        $this->assertSame(2, $stats['students']);
        $this->assertSame(1, $stats['instructors']);
        $this->assertSame(2, $stats['courses']);
        $this->assertSame(1, $stats['published']);
        $this->assertSame(2, $stats['enrollments']);
        $this->assertSame(2, $stats['attempts']);
        $this->assertEquals(62.5, $stats['avg_score']);
        $this->assertEquals(50, $stats['pass_rate']);
    }

    public function test_admin_manages_users(): void
    {
        $admin = $this->quanTri();
        $student = $this->hocVien();

        $this->actingAs($admin)->get('/admin/users?q='.urlencode($student->email))->assertOk()->assertSee($student->email);

        $this->actingAs($admin)->put(route('admin.users.update', $student), [
            'name' => $student->name, 'email' => $student->email, 'role' => 'instructor', 'is_active' => 0,
        ])->assertRedirect(route('admin.users.index'));

        $student->refresh();
        $this->assertSame('instructor', $student->role);
        $this->assertFalse($student->is_active);
    }

    public function test_locked_user_is_logged_out_on_next_request(): void
    {
        $student = $this->hocVien();

        $this->post('/login', ['email' => $student->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($student);
        $this->get('/my-courses')->assertOk();

        // Admin khóa tài khoản trong lúc người dùng vẫn đang đăng nhập.
        NguoiDung::whereKey($student->id)->update(['is_active' => false]);
        $this->app['auth']->forgetGuards(); // giả lập request mới: user được nạp lại từ DB

        $this->get('/my-courses')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_cannot_demote_lock_or_delete_self(): void
    {
        $admin = $this->quanTri();

        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'student', 'is_active' => 1,
        ])->assertSessionHasErrors('role');
        $this->actingAs($admin)->put(route('admin.users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'admin', 'is_active' => 0,
        ])->assertSessionHasErrors('role');
        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertSessionHas('error');

        $this->assertSame('admin', $admin->fresh()->role);
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_cannot_delete_instructor_who_owns_courses(): void
    {
        $inst = $this->giangVien();
        $this->taoKhoaHoc($inst);

        $this->actingAs($this->quanTri())->delete(route('admin.users.destroy', $inst))->assertSessionHas('error');
        $this->assertNotNull($inst->fresh());
    }

    public function test_category_crud_and_delete_protection(): void
    {
        $admin = $this->quanTri();

        $this->actingAs($admin)->post(route('admin.categories.store'), ['name' => 'Mới', 'description' => 'd'])->assertRedirect();
        $cat = DanhMuc::where('name', 'Mới')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.categories.store'), ['name' => 'Mới'])->assertSessionHasErrors('name');

        $this->actingAs($admin)->put(route('admin.categories.update', $cat), ['name' => 'Đổi tên'])->assertRedirect();
        $this->assertSame('Đổi tên', $cat->fresh()->name);

        $course = $this->taoKhoaHoc($this->giangVien());
        $course->update(['category_id' => $cat->id]);
        $this->actingAs($admin)->delete(route('admin.categories.destroy', $cat))->assertSessionHas('error');
        $this->assertNotNull($cat->fresh());

        $course->delete();
        $this->actingAs($admin)->delete(route('admin.categories.destroy', $cat))->assertSessionHas('success');
        $this->assertNull(DanhMuc::find($cat->id));
    }

    public function test_admin_can_hide_course_and_it_disappears_from_public_list(): void
    {
        $course = $this->taoKhoaHoc($this->giangVien());
        $course->update(['title' => 'Khóa sẽ bị ẩn']);

        $this->get('/courses')->assertSee('Khóa sẽ bị ẩn');

        $this->actingAs($this->quanTri())->patch(route('admin.courses.status', $course), ['status' => 'hidden'])->assertRedirect();

        // Học viên (không phải admin/chủ khóa học) không còn thấy khóa học.
        $student = $this->hocVien();
        $this->actingAs($student)->get('/courses')->assertDontSee('Khóa sẽ bị ẩn');
        $this->actingAs($student)->get(route('courses.show', $course))->assertNotFound();
    }

    public function test_non_admin_cannot_change_course_status_or_users(): void
    {
        $course = $this->taoKhoaHoc($inst = $this->giangVien());
        $victim = $this->hocVien();

        $this->actingAs($inst)->patch(route('admin.courses.status', $course), ['status' => 'hidden'])->assertForbidden();
        $this->actingAs($this->hocVien())->put(route('admin.users.update', $victim), [
            'name' => 'x', 'email' => 'x@x.com', 'role' => 'admin', 'is_active' => 1,
        ])->assertForbidden();
        $this->assertSame('student', $victim->fresh()->role);
    }

    public function test_csrf_is_enforced_outside_tests_config(): void
    {
        // Mọi form dùng @csrf: kiểm tra nhanh các view chính có token.
        $this->get('/login')->assertSee('name="_token"', false);
        $this->get('/register')->assertSee('name="_token"', false);
    }
}
