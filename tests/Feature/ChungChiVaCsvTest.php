<?php

namespace Tests\Feature;

use App\Models\ChungChi;
use App\Models\KhoaHoc;
use App\Models\TienDoBaiHoc;
use App\Models\BaiKiemTra;
use App\Models\LuotLamBai;
use App\Models\NguoiDung;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\TaoDuLieu;
use Tests\TestCase;

/** File: HaiDangTest.php - Kiểm thử chứng chỉ hoàn thành và xuất báo cáo CSV. */
class ChungChiVaCsvTest extends TestCase
{
    use TaoDuLieu, RefreshDatabase;

    private function hoanThanhTatCaBai(NguoiDung $hocVien, KhoaHoc $course): void
    {
        foreach ($course->lessons as $lesson) {
            TienDoBaiHoc::create(['user_id' => $hocVien->id, 'lesson_id' => $lesson->id, 'is_completed' => true, 'completed_at' => now()]);
        }
    }

    private function nopBai(NguoiDung $hocVien, BaiKiemTra $quiz, float $diem): LuotLamBai
    {
        return LuotLamBai::create([
            'quiz_id' => $quiz->id, 'user_id' => $hocVien->id, 'score' => $diem,
            'correct_count' => (int) round($diem / 25), 'total_questions' => 4,
            'passed' => $diem >= $quiz->pass_score,
            'started_at' => now()->subMinutes(5), 'submitted_at' => now(),
        ]);
    }

    // ---------------- Chứng chỉ hoàn thành ----------------

    public function test_chua_hoan_thanh_thi_khong_nhan_duoc_chung_chi(): void
    {
        $course = $this->khoaHocCoBaiHoc($this->giangVien(), 3);
        $hocVien = $this->hocVien();
        $this->dangKyKhoaHoc($hocVien, $course);

        $this->actingAs($hocVien)->get(route('learn.show', $course))
            ->assertOk()->assertSee('Chứng chỉ hoàn thành')->assertSee('hiện tại 0%');

        $this->actingAs($hocVien)->from(route('learn.show', $course))
            ->post(route('chung_chi.nhan', $course))
            ->assertRedirect(route('learn.show', $course))
            ->assertSessionHas('error');
        $this->assertDatabaseCount('certificates', 0);
    }

    public function test_phai_dat_bai_kiem_tra_moi_nhan_duoc_chung_chi(): void
    {
        $course = $this->khoaHocCoBaiHoc($this->giangVien(), 2);
        $quiz = $this->taoBaiKiemTra($course, 50);
        $hocVien = $this->hocVien();
        $this->dangKyKhoaHoc($hocVien, $course);
        $this->hoanThanhTatCaBai($hocVien, $course);
        $this->nopBai($hocVien, $quiz, 25);

        $this->actingAs($hocVien)->post(route('chung_chi.nhan', $course))->assertSessionHas('error');
        $this->assertDatabaseCount('certificates', 0);

        $this->nopBai($hocVien, $quiz, 75);
        $this->actingAs($hocVien)->get(route('learn.show', $course))->assertSee('Nhận chứng chỉ');
        $response = $this->actingAs($hocVien)->post(route('chung_chi.nhan', $course));

        $chungChi = ChungChi::firstOrFail();
        $response->assertRedirect(route('chung_chi.xem', $chungChi));
        $this->assertMatchesRegularExpression('/^ELN-\d{4}-[A-Z0-9]{8}$/', $chungChi->ma_chung_chi);

        // Nhận lần hai không tạo thêm bản ghi.
        $this->actingAs($hocVien)->post(route('chung_chi.nhan', $course))->assertRedirect(route('chung_chi.xem', $chungChi));
        $this->assertDatabaseCount('certificates', 1);

        $this->actingAs($hocVien)->get(route('chung_chi.xem', $chungChi))
            ->assertOk()
            ->assertSee('CHỨNG CHỈ HOÀN THÀNH')
            ->assertSee($hocVien->name)
            ->assertSee($course->title)
            ->assertSee($chungChi->ma_chung_chi)
            ->assertSee('75/100');
    }

    public function test_quyen_xem_chung_chi_va_trang_xac_thuc_cong_khai(): void
    {
        $giangVien = $this->giangVien();
        $course = $this->khoaHocCoBaiHoc($giangVien, 1);
        $hocVien = $this->hocVien();
        $this->dangKyKhoaHoc($hocVien, $course);
        $this->hoanThanhTatCaBai($hocVien, $course);
        $this->actingAs($hocVien)->post(route('chung_chi.nhan', $course));
        $chungChi = ChungChi::firstOrFail();

        $this->actingAs($this->hocVien())->get(route('chung_chi.xem', $chungChi))->assertForbidden();
        $this->actingAs($this->giangVien())->get(route('chung_chi.xem', $chungChi))->assertForbidden();
        $this->actingAs($giangVien)->get(route('chung_chi.xem', $chungChi))->assertOk();
        $this->actingAs($this->quanTri())->get(route('chung_chi.xem', $chungChi))->assertOk();

        $this->post(route('logout'));
        $this->get(route('chung_chi.xem', $chungChi))->assertRedirect('/login');
        $this->get(route('chung_chi.xac_thuc', $chungChi))
            ->assertOk()->assertSee('Chứng chỉ hợp lệ')->assertSee($hocVien->name)->assertDontSee($hocVien->email);
        $this->get('/xac-thuc-chung-chi/ELN-0000-KHONGCO1')->assertNotFound();
    }

    public function test_giang_vien_khong_nhan_chung_chi_va_trang_khoa_hoc_cua_toi_hien_nut(): void
    {
        $course = $this->khoaHocCoBaiHoc($this->giangVien(), 1);
        $this->actingAs($this->giangVien())->post(route('chung_chi.nhan', $course))->assertForbidden();

        $hocVien = $this->hocVien();
        $this->dangKyKhoaHoc($hocVien, $course);
        $this->hoanThanhTatCaBai($hocVien, $course);
        $this->actingAs($hocVien)->get(route('my-courses'))->assertOk()->assertSee('Nhận chứng chỉ');
    }

    // ---------------- Xuất CSV ----------------

    public function test_xuat_csv_danh_sach_hoc_vien(): void
    {
        $giangVien = $this->giangVien();
        $course = $this->khoaHocCoBaiHoc($giangVien, 4);
        $hv1 = NguoiDung::factory()->student()->create(['name' => 'Nguyễn Thị Lan']);
        $hv2 = NguoiDung::factory()->student()->create(['name' => '=HYPERLINK("x")']);
        $this->dangKyKhoaHoc($hv1, $course);
        $this->dangKyKhoaHoc($hv2, $course);
        $this->hoanThanhTatCaBai($hv1, $course);
        TienDoBaiHoc::create(['user_id' => $hv2->id, 'lesson_id' => $course->lessons->first()->id, 'is_completed' => true]);

        $this->actingAs($giangVien)->get(route('instructor.courses.students', $course))->assertSee('Xuất danh sách học viên (CSV)');

        $response = $this->actingAs($giangVien)->get(route('instructor.csv.hoc_vien', $course));
        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename=hoc-vien-', $response->headers->get('content-disposition'));

        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('STT,"Họ tên",Email', $csv);
        $this->assertStringContainsString('"Nguyễn Thị Lan",'.$hv1->email, $csv);
        $this->assertStringContainsString(',4,4,100,', $csv);
        $this->assertStringContainsString(',1,4,25,', $csv);
        // Chống CSV injection: tên bắt đầu bằng "=" được thêm dấu '.
        $this->assertStringContainsString('"\'=HYPERLINK(""x"")"', $csv);
    }

    public function test_xuat_csv_bang_diem_va_ket_qua_bai_kiem_tra(): void
    {
        $giangVien = $this->giangVien();
        $course = $this->khoaHocCoBaiHoc($giangVien, 1);
        $quiz = $this->taoBaiKiemTra($course, 50);
        $quiz->update(['title' => 'Giữa kỳ']);
        $hv1 = $this->hocVien();
        $hv2 = $this->hocVien();
        $this->dangKyKhoaHoc($hv1, $course);
        $this->dangKyKhoaHoc($hv2, $course);
        $this->nopBai($hv1, $quiz, 25);
        $this->nopBai($hv1, $quiz, 75);

        $bangDiem = $this->actingAs($giangVien)->get(route('instructor.csv.bang_diem', $course))->streamedContent();
        $this->assertStringContainsString('"Điểm cao nhất - Giữa kỳ","Số lần làm - Giữa kỳ","Kết quả - Giữa kỳ","Số bài đạt","Điểm trung bình"', $bangDiem);
        $this->assertStringContainsString($hv1->email.',75,2,Đạt,1/1,75', $bangDiem);
        $this->assertStringContainsString($hv2->email.',,0,"Chưa làm",0/1,', $bangDiem);

        $this->actingAs($giangVien)->get(route('instructor.quizzes.results', $quiz))->assertSee('Xuất kết quả bài này (CSV)');
        $ketQua = $this->actingAs($giangVien)->get(route('instructor.csv.ket_qua', $quiz))->streamedContent();
        $this->assertSame(3, substr_count(trim($ketQua), "\n") + 1); // tiêu đề + 2 lượt nộp
        $this->assertStringContainsString(',25,"Chưa đạt"', $ketQua);
        $this->assertStringContainsString(',75,Đạt', $ketQua);
    }

    public function test_chi_giang_vien_so_huu_hoac_admin_duoc_xuat_csv(): void
    {
        $course = $this->khoaHocCoBaiHoc($this->giangVien(), 1);
        $quiz = $this->taoBaiKiemTra($course);

        foreach ([route('instructor.csv.hoc_vien', $course), route('instructor.csv.bang_diem', $course), route('instructor.csv.ket_qua', $quiz)] as $url) {
            $this->actingAs($this->hocVien())->get($url)->assertForbidden();
            $this->actingAs($this->giangVien())->get($url)->assertForbidden();
            $this->actingAs($this->quanTri())->get($url)->assertOk();
        }
    }
}
