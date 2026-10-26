<?php

namespace Tests\Feature;

use App\Models\NguoiDung;
use App\Notifications\DatLaiMatKhauNotification;
use App\Notifications\XacMinhEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/** Kiểm thử đầy đủ xác minh email đăng ký và khôi phục mật khẩu. */
class XacMinhEmailVaMatKhauTest extends TestCase
{
    use RefreshDatabase;

    private function nguoiDungChuaXacMinh(array $thayDoi = []): NguoiDung
    {
        return NguoiDung::factory()->create(array_merge([
            'email_verified_at' => null,
        ], $thayDoi));
    }

    private function linkXacMinh(NguoiDung $nguoiDung, int $soPhut = 60): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addMinutes($soPhut), [
            'id' => $nguoiDung->id,
            'hash' => sha1($nguoiDung->getEmailForVerification()),
        ]);
    }

    public function test_gui_lai_email_xac_minh_va_noi_dung_co_link_da_ky(): void
    {
        Notification::fake();
        $nguoiDung = $this->nguoiDungChuaXacMinh();

        $this->actingAs($nguoiDung)->post(route('verification.send'))
            ->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo($nguoiDung, XacMinhEmailNotification::class, function ($thongBao) use ($nguoiDung) {
            $thu = $thongBao->toMail($nguoiDung);

            return str_contains($thu->subject, 'Xác minh đăng ký tài khoản')
                && URL::hasValidSignature(Request::create($thu->actionUrl));
        });
    }

    public function test_link_xac_minh_sai_hoac_het_han_bi_tu_choi(): void
    {
        $nguoiDung = $this->nguoiDungChuaXacMinh();
        $link = $this->linkXacMinh($nguoiDung, 1);

        $this->get($link.'&gia_mao=1')->assertForbidden();
        $this->assertNull($nguoiDung->fresh()->email_verified_at);

        Carbon::setTestNow(now()->addMinutes(2));
        $this->get($link)->assertForbidden();
        $this->assertNull($nguoiDung->fresh()->email_verified_at);
        Carbon::setTestNow();
    }

    public function test_link_xac_minh_hop_le_chi_xac_minh_dung_tai_khoan(): void
    {
        $nguoiDung = $this->nguoiDungChuaXacMinh();
        $nguoiDungKhac = $this->nguoiDungChuaXacMinh();

        $this->get($this->linkXacMinh($nguoiDung))
            ->assertRedirect(route('login'))
            ->assertSessionHas('success');

        $this->assertNotNull($nguoiDung->fresh()->email_verified_at);
        $this->assertNull($nguoiDungKhac->fresh()->email_verified_at);
    }

    public function test_tab_dang_ky_tu_nhan_biet_khi_email_duoc_xac_minh_o_thiet_bi_khac(): void
    {
        $nguoiDung = $this->nguoiDungChuaXacMinh();

        $this->actingAs($nguoiDung)->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('const endpoint', false)
            ->assertSee('Tôi đã xác minh');

        $this->actingAs($nguoiDung)->getJson(route('verification.status'))
            ->assertOk()
            ->assertJson(['da_xac_minh' => false, 'chuyen_huong' => null]);

        // Giả lập link trong email được mở trên điện thoại/máy khác.
        $nguoiDung->markEmailAsVerified();

        $this->actingAs($nguoiDung)->getJson(route('verification.status'))
            ->assertOk()
            ->assertJson([
                'da_xac_minh' => true,
                'chuyen_huong' => route('dashboard'),
            ]);
    }

    public function test_khach_khong_duoc_xem_trang_thai_xac_minh_cua_tai_khoan(): void
    {
        $this->getJson(route('verification.status'))->assertUnauthorized();
    }

    public function test_quen_mat_khau_gui_dung_token_va_dang_nhap_duoc_bang_mat_khau_moi(): void
    {
        Notification::fake();
        $nguoiDung = NguoiDung::factory()->student()->create(['email' => 'hocvien@example.com']);
        $token = null;
        $link = null;

        $this->post(route('password.email'), ['email' => '  HOCVIEN@EXAMPLE.COM  '])
            ->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo($nguoiDung, DatLaiMatKhauNotification::class, function ($thongBao) use ($nguoiDung, &$token, &$link) {
            $token = $thongBao->token;
            $link = $thongBao->toMail($nguoiDung)->actionUrl;

            return str_contains($link, route('password.reset', $token));
        });

        $this->get($link)->assertOk()->assertSee('hocvien@example.com')->assertSee($token, false);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'HOCVIEN@EXAMPLE.COM',
            'password' => 'Matkhau@moi1',
            'password_confirmation' => 'Matkhau@moi1',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('Matkhau@moi1', $nguoiDung->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $nguoiDung->email]);

        $this->post(route('login'), [
            'email' => $nguoiDung->email,
            'password' => 'Matkhau@moi1',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($nguoiDung);
    }

    public function test_token_sai_hoac_da_dung_khong_the_dat_lai_mat_khau_lan_nua(): void
    {
        $nguoiDung = NguoiDung::factory()->student()->create();
        $duLieu = [
            'email' => $nguoiDung->email,
            'password' => 'Matkhau@moi1',
            'password_confirmation' => 'Matkhau@moi1',
        ];

        $this->post(route('password.update'), $duLieu + ['token' => 'token-khong-hop-le'])
            ->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('password', $nguoiDung->fresh()->password));

        $token = app('auth.password.broker')->createToken($nguoiDung);
        $this->post(route('password.update'), $duLieu + ['token' => $token])->assertRedirect(route('login'));
        $this->post(route('password.update'), $duLieu + ['token' => $token])->assertSessionHasErrors('email');
    }

    public function test_khong_gui_link_cho_email_la_tai_khoan_khoa_hoac_quan_tri(): void
    {
        Notification::fake();
        $biKhoa = NguoiDung::factory()->student()->inactive()->create();
        $quanTri = NguoiDung::factory()->admin()->create();

        foreach ([$biKhoa->email, $quanTri->email, 'khong-ton-tai@example.com'] as $email) {
            $this->post(route('password.email'), ['email' => $email])
                ->assertRedirect()
                ->assertSessionHas('success', 'Nếu email thuộc một tài khoản hợp lệ, liên kết đặt lại mật khẩu đã được gửi.');
        }

        Notification::assertNothingSent();
    }
}
