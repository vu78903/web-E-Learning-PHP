<?php

namespace Tests\Feature;

use App\Models\LuotLamBai;
use App\Models\NguoiDung;
use App\Notifications\CoKetQuaBaiThiNotification;
use App\Notifications\DangKyKhoaHocThanhCongNotification;
use App\Notifications\DatLaiMatKhauNotification;
use App\Notifications\XacMinhEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\Concerns\TaoDuLieu;
use Tests\TestCase;

/** File: Vu2001231056Test.php - Kiểm thử ba nhóm chức năng của module. */
class DanhGiaVaEmailTest extends TestCase
{
    use TaoDuLieu, RefreshDatabase;

    public function test_dang_ky_gui_email_va_bat_buoc_xac_minh(): void
    {
        Notification::fake();

        $this->post(route('register'), [
            'name' => 'Học Viên Mới',
            'email' => 'moi@example.com',
            'phone' => '0912345678',
            'password' => 'Matkhau@123',
            'password_confirmation' => 'Matkhau@123',
            'role' => NguoiDung::ROLE_STUDENT,
        ])->assertRedirect(route('dashboard'));

        $user = NguoiDung::where('email', 'moi@example.com')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, XacMinhEmailNotification::class);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('verification.notice'));

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(10), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);
        // Giả lập Gmail mở liên kết ở một trình duyệt khác, không có phiên đăng nhập.
        $this->post(route('logout'));
        $this->get($url)
            ->assertRedirect(route('login'))
            ->assertSessionHas('success', 'Xác minh email thành công. Bạn đã hoàn tất đăng ký và có thể đăng nhập.');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_hoc_vien_co_the_dat_lai_mat_khau_qua_email(): void
    {
        Notification::fake();
        $user = $this->hocVien();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()->assertSessionHas('success');
        Notification::assertSentTo($user, DatLaiMatKhauNotification::class);

        $token = Password::createToken($user);
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Matkhau@moi1',
            'password_confirmation' => 'Matkhau@moi1',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('Matkhau@moi1', $user->fresh()->password));
    }

    public function test_hoc_vien_da_dang_ky_co_the_danh_gia_bang_ajax(): void
    {
        $course = $this->taoKhoaHoc($this->giangVien());
        $student = $this->hocVien();
        $this->dangKyKhoaHoc($student, $course);

        $this->actingAs($student)->get(route('learn.show', $course))
            ->assertOk()
            ->assertSee('form-danh-gia', false);

        $this->actingAs($student)->postJson(route('courses.reviews.store', $course), [
            'so_sao' => 5,
            'binh_luan' => 'Khóa học rất dễ hiểu.',
        ])->assertOk()->assertJsonPath('so_luong', 1)->assertJsonPath('trung_binh', 5);

        $this->actingAs($student)->postJson(route('courses.reviews.store', $course), [
            'so_sao' => 4,
            'binh_luan' => 'Nội dung tốt, cần thêm bài tập.',
        ])->assertOk()->assertJsonPath('so_luong', 1)->assertJsonPath('trung_binh', 4);

        $this->assertDatabaseHas('course_reviews', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'so_sao' => 4,
        ]);
        $this->assertDatabaseCount('course_reviews', 1);

        $this->actingAs($this->hocVien())->postJson(route('courses.reviews.store', $course), [
            'so_sao' => 5,
            'binh_luan' => 'Không hợp lệ vì chưa đăng ký.',
        ])->assertForbidden();
    }

    public function test_gui_email_khi_dang_ky_khoa_hoc_va_co_ket_qua_thi(): void
    {
        Notification::fake();
        $course = $this->khoaHocCoBaiHoc($this->giangVien(), 1);
        $quiz = $this->taoBaiKiemTra($course);
        $student = $this->hocVien();

        $this->actingAs($student)->post(route('courses.enroll', $course))->assertRedirect();
        Notification::assertSentTo($student, DangKyKhoaHocThanhCongNotification::class);

        $this->actingAs($student)->post(route('quizzes.start', $quiz));
        $attempt = LuotLamBai::firstOrFail();
        $answers = $quiz->questions->mapWithKeys(
            fn ($question) => [$question->id => $question->answers->firstWhere('is_correct', true)->id]
        )->all();

        $this->actingAs($student)->post(route('attempts.submit', $attempt), ['answers' => $answers])
            ->assertRedirect(route('attempts.result', $attempt));
        Notification::assertSentTo($student, CoKetQuaBaiThiNotification::class);
    }
}
