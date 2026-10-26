<?php

namespace Tests\Feature;

use App\Models\LuotLamBai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\TaoDuLieu;
use Tests\TestCase;

class BangDiemDashboardTest extends TestCase
{
    use TaoDuLieu, RefreshDatabase;

    private function nop($quiz, $student, float $score, ?int $pass = 50): void
    {
        LuotLamBai::create(['quiz_id' => $quiz->id, 'user_id' => $student->id, 'started_at' => now(), 'submitted_at' => now(),
            'score' => $score, 'correct_count' => 0, 'total_questions' => 4, 'passed' => $score >= $pass]);
    }

    public function test_bang_diem_theo_khoa_hoc_lay_diem_cao_nhat_moi_bai(): void
    {
        $gv = $this->giangVien();
        $khoa = $this->taoKhoaHoc($gv);
        $bai1 = $this->taoBaiKiemTra($khoa);
        $bai2 = $this->taoBaiKiemTra($khoa);
        [$an, $binh, $chua] = [$this->hocVien(), $this->hocVien(), $this->hocVien()];
        foreach ([$an, $binh, $chua] as $hv) {
            $this->dangKyKhoaHoc($hv, $khoa);
        }
        $this->nop($bai1, $an, 40);
        $this->nop($bai1, $an, 80);
        $this->nop($bai2, $an, 60);
        $this->nop($bai1, $binh, 30);
        $this->nop($bai1, $this->hocVien(), 100); // không đăng ký khóa: không tính

        $bangDiem = $this->actingAs($gv)->get(route('instructor.dashboard', ['course_id' => $khoa->id]))
            ->assertOk()->assertSee('Bảng điểm học viên')->assertSee($chua->name)
            ->viewData('bangDiem');

        $this->assertTrue($bangDiem['course']->is($khoa));
        $this->assertNull($bangDiem['quiz']);
        $this->assertCount(2, $bangDiem['quizzes']);
        $this->assertSame(80.0, $bangDiem['scores'][$an->id.'-'.$bai1->id]['best']);
        $this->assertSame(2, $bangDiem['scores'][$an->id.'-'.$bai1->id]['attempts']);
        $this->assertFalse($bangDiem['scores'][$binh->id.'-'.$bai1->id]['passed']);
        $this->assertSame(3, $bangDiem['summary']['students']);
        $this->assertSame(2, $bangDiem['summary']['done']);
        $this->assertSame(4, $bangDiem['summary']['attempts']);
        $this->assertEqualsWithDelta((80 + 60 + 30) / 3, $bangDiem['summary']['avg'], 0.001);
        $this->assertSame(67, $bangDiem['summary']['pass_rate']);
    }

    public function test_bang_diem_theo_bai_kiem_tra_suy_ra_khoa_hoc(): void
    {
        $gv = $this->giangVien();
        $this->taoKhoaHoc($gv);
        $khoa = $this->taoKhoaHoc($gv);
        $bai1 = $this->taoBaiKiemTra($khoa);
        $bai2 = $this->taoBaiKiemTra($khoa);
        $hv = $this->hocVien();
        $this->dangKyKhoaHoc($hv, $khoa);
        $this->nop($bai1, $hv, 90);
        $this->nop($bai2, $hv, 10);

        $bangDiem = $this->actingAs($gv)->get(route('instructor.dashboard', ['quiz_id' => $bai2->id]))
            ->assertOk()->assertSee(route('instructor.quizzes.results', $bai2), false)
            ->viewData('bangDiem');

        $this->assertTrue($bangDiem['course']->is($khoa));
        $this->assertTrue($bangDiem['quiz']->is($bai2));
        $this->assertSame([$hv->id.'-'.$bai2->id], $bangDiem['scores']->keys()->all());
        $this->assertSame(0, $bangDiem['summary']['pass_rate']);
    }

    public function test_giang_vien_khong_xem_duoc_bang_diem_khoa_nguoi_khac_nhung_admin_xem_duoc(): void
    {
        $gv = $this->giangVien();
        $cuaToi = $this->taoKhoaHoc($gv);
        $khac = $this->taoKhoaHoc($this->giangVien());
        $baiKhac = $this->taoBaiKiemTra($khac);
        $hv = $this->hocVien();
        $this->dangKyKhoaHoc($hv, $khac);
        $this->nop($baiKhac, $hv, 70);

        $bangDiem = $this->actingAs($gv)->get(route('instructor.dashboard', ['course_id' => $khac->id, 'quiz_id' => $baiKhac->id]))
            ->assertOk()->assertDontSee($hv->email)->viewData('bangDiem');
        $this->assertTrue($bangDiem['course']->is($cuaToi));
        $this->assertNull($bangDiem['quiz']);

        $this->actingAs($this->quanTri())->get(route('admin.dashboard', ['quiz_id' => $baiKhac->id]))
            ->assertOk()->assertSee($hv->email)->assertSee('Bảng điểm học viên');
    }

    public function test_khoa_hoc_chua_co_bai_kiem_tra_hien_thong_bao(): void
    {
        $gv = $this->giangVien();
        $this->taoKhoaHoc($gv);

        $this->actingAs($gv)->get(route('instructor.dashboard'))->assertOk()
            ->assertSee('Khóa học này chưa có bài kiểm tra.');
    }
}
