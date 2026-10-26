<?php

namespace Tests\Feature;

use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\TaoDuLieu;
use Tests\TestCase;

class TaiKhoanVaPhanQuyenTest extends TestCase
{
    use TaoDuLieu, RefreshDatabase;

    public function test_public_pages_load(): void
    {
        $this->get('/')->assertOk();
        $this->get('/courses')->assertOk();
        $this->get('/login')->assertOk();
        $this->get('/register')->assertOk();
    }

    public function test_user_can_register_as_student_and_is_logged_in(): void
    {
        $this->post('/register', [
            'name' => 'Học Viên', 'email' => 'hv@example.com',
            'phone' => '0912345678', 'password' => 'Matkhau@123', 'password_confirmation' => 'Matkhau@123', 'role' => 'student',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->assertSame('student', NguoiDung::first()->role);
        $this->assertNotSame('Matkhau@123', NguoiDung::first()->password);
    }

    public function test_cannot_register_as_admin(): void
    {
        $this->post('/register', [
            'name' => 'Hacker', 'email' => 'h@example.com',
            'phone' => '0912345678', 'password' => 'Matkhau@123', 'password_confirmation' => 'Matkhau@123', 'role' => 'admin',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_email_must_be_unique(): void
    {
        NguoiDung::factory()->create(['email' => 'dup@example.com']);

        $this->post('/register', [
            'name' => 'X', 'email' => 'dup@example.com',
            'phone' => '0912345678', 'password' => 'Matkhau@123', 'password_confirmation' => 'Matkhau@123', 'role' => 'student',
        ])->assertSessionHasErrors('email');
    }

    public function test_login_logout_and_wrong_password(): void
    {
        $user = NguoiDung::factory()->create(['email' => 'a@example.com']);

        $this->post('/login', ['email' => 'a@example.com', 'password' => 'sai-mat-khau'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => 'a@example.com', 'password' => 'password'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        NguoiDung::factory()->inactive()->create(['email' => 'locked@example.com']);

        $this->post('/login', ['email' => 'locked@example.com', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_dashboard_redirects_by_role(): void
    {
        $this->actingAs($this->quanTri())->get('/dashboard')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($this->giangVien())->get('/dashboard')->assertRedirect(route('instructor.dashboard'));
        $this->actingAs($this->hocVien())->get('/dashboard')->assertRedirect(route('my-courses'));
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/my-courses')->assertRedirect('/login');
        $this->get('/instructor/courses')->assertRedirect('/login');
    }

    public function test_non_admin_cannot_access_admin_area(): void
    {
        foreach (['/admin', '/admin/users', '/admin/categories', '/admin/courses'] as $url) {
            $this->actingAs($this->hocVien())->get($url)->assertForbidden();
            $this->actingAs($this->giangVien())->get($url)->assertForbidden();
            $this->actingAs($this->quanTri())->get($url)->assertOk();
        }
    }

    public function test_student_cannot_access_instructor_area(): void
    {
        $this->actingAs($this->hocVien())->get('/instructor/courses')->assertForbidden();
        $this->actingAs($this->hocVien())->get('/instructor/courses/create')->assertForbidden();
    }

    public function test_instructor_cannot_access_student_only_actions(): void
    {
        $inst = $this->giangVien();
        $course = $this->taoKhoaHoc($inst);
        $this->actingAs($inst)->post(route('courses.enroll', $course))->assertForbidden();
        $this->actingAs($inst)->get('/my-courses')->assertForbidden();
    }

    public function test_profile_update_and_cannot_escalate_role(): void
    {
        $u = $this->hocVien();

        $this->actingAs($u)->put('/profile', [
            'name' => 'Tên mới', 'email' => $u->email, 'bio' => 'Xin chào', 'role' => 'admin',
        ])->assertRedirect();

        $u->refresh();
        $this->assertSame('Tên mới', $u->name);
        $this->assertSame('student', $u->role);
    }

    public function test_profile_password_change_requires_current_password(): void
    {
        $u = $this->hocVien();

        $this->actingAs($u)->put('/profile', [
            'name' => $u->name, 'email' => $u->email,
            'current_password' => 'sai', 'password' => 'Matkhau@moi1', 'password_confirmation' => 'Matkhau@moi1',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($u)->put('/profile', [
            'name' => $u->name, 'email' => $u->email,
            'current_password' => 'password', 'password' => 'Matkhau@moi1', 'password_confirmation' => 'Matkhau@moi1',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(\Hash::check('Matkhau@moi1', $u->fresh()->password));
    }

    // ---- Ràng buộc bảo mật khi tạo tài khoản ----

    private function registerData(array $over = []): array
    {
        return array_merge([
            'name' => 'Nguyễn Văn An', 'email' => 'an@example.com', 'phone' => '0912345678',
            'password' => 'Matkhau@123', 'password_confirmation' => 'Matkhau@123', 'role' => 'student',
        ], $over);
    }

    public function test_register_rejects_invalid_email(): void
    {
        foreach (['khongco-a-cong', 'a@b', 'a@b.', '@example.com', 'a b@example.com', 'a@@example.com'] as $bad) {
            $this->post('/register', $this->registerData(['email' => $bad]))->assertSessionHasErrors('email');
        }
        $this->assertDatabaseCount('users', 0);
    }

    public function test_email_is_normalized_to_lowercase_and_uniqueness_ignores_case(): void
    {
        $this->post('/register', $this->registerData(['email' => '  An@Example.COM ']))->assertRedirect('/dashboard');
        $this->assertSame('an@example.com', NguoiDung::first()->email);

        auth()->logout();
        $this->post('/register', $this->registerData(['email' => 'AN@example.com', 'phone' => '0987654321']))->assertSessionHasErrors('email');
    }

    public function test_register_rejects_invalid_phone(): void
    {
        foreach (['', '091234567', '09123456789', '0912 34 567a', 'abcdefghij', '0112345678', '1912345678', '+84912345678'] as $bad) {
            $this->post('/register', $this->registerData(['phone' => $bad]))->assertSessionHasErrors('phone');
        }
        $this->assertDatabaseCount('users', 0);
    }

    public function test_phone_with_spaces_or_dashes_is_normalized_and_must_be_unique(): void
    {
        $this->post('/register', $this->registerData(['phone' => '0912 345-678']))->assertRedirect('/dashboard');
        $this->assertSame('0912345678', NguoiDung::first()->phone);

        auth()->logout();
        $this->post('/register', $this->registerData(['email' => 'b@example.com']))->assertSessionHasErrors('phone');
    }

    public function test_register_password_complexity(): void
    {
        $weak = ['Ab@1' => 'quá ngắn', 'matkhau@123' => 'thiếu chữ hoa', 'MATKHAU@123' => 'thiếu chữ thường',
            'Matkhau@abc' => 'thiếu chữ số', 'Matkhau1234' => 'thiếu ký tự đặc biệt'];
        foreach (array_keys($weak) as $pw) {
            $this->post('/register', $this->registerData(['password' => $pw, 'password_confirmation' => $pw]))
                ->assertSessionHasErrors('password');
        }
        $this->assertDatabaseCount('users', 0);

        $this->post('/register', $this->registerData(['password_confirmation' => 'Khac@12345']))->assertSessionHasErrors('password');
        $this->post('/register', $this->registerData())->assertSessionHasNoErrors();
    }

    public function test_register_rejects_invalid_name(): void
    {
        foreach (['A', '1234', '<script>x</script>', 'An@@', ''] as $bad) {
            $this->post('/register', $this->registerData(['name' => $bad]))->assertSessionHasErrors('name');
        }
    }

    public function test_profile_phone_and_new_password_rules(): void
    {
        $u = $this->hocVien();
        $base = ['name' => $u->name, 'email' => $u->email];

        $this->actingAs($u)->put('/profile', $base + ['phone' => '123'])->assertSessionHasErrors('phone');
        $this->actingAs($u)->put('/profile', $base + ['phone' => '0356789012'])->assertSessionHasNoErrors();
        $this->assertSame('0356789012', $u->fresh()->phone);

        $this->actingAs($u)->put('/profile', $base + ['current_password' => 'password', 'password' => 'yeuyeuyeu1', 'password_confirmation' => 'yeuyeuyeu1'])
            ->assertSessionHasErrors('password');
    }

    public function test_login_is_rate_limited(): void
    {
        NguoiDung::factory()->create(['email' => 'rl@example.com']);
        for ($i = 0; $i < 10; $i++) {
            $this->post('/login', ['email' => 'rl@example.com', 'password' => 'sai'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => 'rl@example.com', 'password' => 'password'])->assertStatus(429);
    }
}
