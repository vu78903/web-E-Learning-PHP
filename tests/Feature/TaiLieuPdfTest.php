<?php

namespace Tests\Feature;

use App\Models\TaiLieuBaiHoc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\TaoDuLieu;
use Tests\TestCase;

class TaiLieuPdfTest extends TestCase
{
    use TaoDuLieu, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(TaiLieuBaiHoc::DISK);
    }

    private function pdf(string $ten = 'tai-lieu.pdf'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $ten,
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<<>>\n%%EOF"
        );
    }

    private function fileGiaPdf(): UploadedFile
    {
        $duongDan = tempnam(sys_get_temp_dir(), 'fake-pdf-');
        file_put_contents($duongDan, 'day khong phai noi dung pdf');

        return new UploadedFile($duongDan, 'nguy-hiem.pdf', 'application/pdf', null, true);
    }

    /** @return array{0: \App\Models\NguoiDung, 1: \App\Models\KhoaHoc, 2: \App\Models\ChuongHoc} */
    private function duLieuKhoaHoc(): array
    {
        $giangVien = $this->giangVien();
        $khoaHoc = $this->taoKhoaHoc($giangVien);
        $chuong = $khoaHoc->sections()->create(['title' => 'Chương tài liệu', 'sort_order' => 1]);

        return [$giangVien, $khoaHoc, $chuong];
    }

    public function test_giang_vien_upload_nhieu_pdf_va_luu_tren_o_rieng_tu(): void
    {
        [$giangVien, $khoaHoc, $chuong] = $this->duLieuKhoaHoc();

        $this->actingAs($giangVien)->post(route('instructor.lessons.store', $chuong), [
            'title' => 'Bài có tài liệu',
            'attachments' => [$this->pdf('giao-trinh.pdf'), $this->pdf('bai-tap.pdf')],
        ])->assertRedirect(route('instructor.curriculum', $khoaHoc));

        $baiHoc = $chuong->lessons()->firstOrFail();
        $this->assertCount(2, $baiHoc->attachments);
        $this->assertDatabaseHas('lesson_attachments', [
            'lesson_id' => $baiHoc->id,
            'original_name' => 'giao-trinh.pdf',
            'mime_type' => 'application/pdf',
        ]);
        foreach ($baiHoc->attachments as $taiLieu) {
            Storage::disk(TaiLieuBaiHoc::DISK)->assertExists($taiLieu->path);
            $this->assertStringStartsWith($baiHoc->id.'/', $taiLieu->path);
        }
    }

    public function test_co_the_bo_sung_pdf_khi_cap_nhat_bai_hoc(): void
    {
        [$giangVien, $khoaHoc, $chuong] = $this->duLieuKhoaHoc();
        $baiHoc = $chuong->lessons()->create(['title' => 'Bài 1']);

        $this->actingAs($giangVien)->put(route('instructor.lessons.update', $baiHoc), [
            'title' => 'Bài 1 đã cập nhật',
            'attachments' => [$this->pdf('bo-sung.pdf')],
        ])->assertRedirect(route('instructor.curriculum', $khoaHoc));

        $this->assertSame('Bài 1 đã cập nhật', $baiHoc->fresh()->title);
        $taiLieu = $baiHoc->attachments()->firstOrFail();
        Storage::disk(TaiLieuBaiHoc::DISK)->assertExists($taiLieu->path);
    }

    public function test_tu_choi_file_gia_pdf_va_file_pdf_sai_duoi(): void
    {
        [$giangVien, , $chuong] = $this->duLieuKhoaHoc();
        $giaPdf = $this->fileGiaPdf();

        $this->actingAs($giangVien)->post(route('instructor.lessons.store', $chuong), [
            'title' => 'Bài lỗi', 'attachments' => [$giaPdf],
        ])->assertSessionHasErrors('attachments.0');

        $this->actingAs($giangVien)->post(route('instructor.lessons.store', $chuong), [
            'title' => 'Bài lỗi', 'attachments' => [$this->pdf('tai-lieu.txt')],
        ])->assertSessionHasErrors('attachments.0');

        $this->assertDatabaseCount('lessons', 0);
        $this->assertDatabaseCount('lesson_attachments', 0);
    }

    public function test_tu_choi_file_qua_10mb_va_qua_10_file_moi_lan(): void
    {
        [$giangVien, , $chuong] = $this->duLieuKhoaHoc();
        $quaLon = UploadedFile::fake()->create('qua-lon.pdf', TaiLieuBaiHoc::MAX_KB + 1, 'application/pdf');

        $this->actingAs($giangVien)->post(route('instructor.lessons.store', $chuong), [
            'title' => 'Bài lỗi', 'attachments' => [$quaLon],
        ])->assertSessionHasErrors('attachments.0');

        $quaNhieu = [];
        for ($i = 1; $i <= TaiLieuBaiHoc::MAX_FILES + 1; $i++) {
            $quaNhieu[] = $this->pdf("tai-lieu-{$i}.pdf");
        }
        $this->actingAs($giangVien)->post(route('instructor.lessons.store', $chuong), [
            'title' => 'Bài lỗi', 'attachments' => $quaNhieu,
        ])->assertSessionHasErrors('attachments');

        $this->assertDatabaseCount('lesson_attachments', 0);
    }

    public function test_chi_nguoi_co_quyen_moi_xem_va_tai_pdf(): void
    {
        [$giangVien, $khoaHoc, $chuong] = $this->duLieuKhoaHoc();
        $baiHoc = $chuong->lessons()->create(['title' => 'Bài 1']);
        $duongDan = Storage::disk(TaiLieuBaiHoc::DISK)->putFile((string) $baiHoc->id, $this->pdf());
        $taiLieu = $baiHoc->attachments()->create([
            'original_name' => 'tai-lieu.pdf', 'path' => $duongDan, 'size' => 68, 'mime_type' => 'application/pdf',
        ]);
        $hocVien = $this->hocVien();
        $this->dangKyKhoaHoc($hocVien, $khoaHoc);

        $this->get(route('attachments.view', $taiLieu))->assertRedirect('/login');
        $this->actingAs($this->hocVien())->get(route('attachments.view', $taiLieu))->assertForbidden();
        $this->actingAs($hocVien)->get(route('attachments.view', $taiLieu))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->actingAs($hocVien)->get(route('attachments.download', $taiLieu))
            ->assertOk()->assertDownload('tai-lieu.pdf');
        $this->actingAs($giangVien)->get(route('attachments.view', $taiLieu))->assertOk();
        $this->actingAs($this->quanTri())->get(route('attachments.download', $taiLieu))->assertOk();
    }

    public function test_xoa_tai_lieu_xoa_ca_ban_ghi_va_file_nhung_nguoi_la_bi_chan(): void
    {
        [$giangVien, , $chuong] = $this->duLieuKhoaHoc();
        $baiHoc = $chuong->lessons()->create(['title' => 'Bài 1']);
        $duongDan = Storage::disk(TaiLieuBaiHoc::DISK)->putFile((string) $baiHoc->id, $this->pdf());
        $taiLieu = $baiHoc->attachments()->create([
            'original_name' => 'tai-lieu.pdf', 'path' => $duongDan, 'size' => 68, 'mime_type' => 'application/pdf',
        ]);

        $this->actingAs($this->giangVien())->delete(route('instructor.attachments.destroy', $taiLieu))->assertForbidden();
        Storage::disk(TaiLieuBaiHoc::DISK)->assertExists($duongDan);

        $this->actingAs($giangVien)->delete(route('instructor.attachments.destroy', $taiLieu))->assertRedirect();
        $this->assertDatabaseMissing('lesson_attachments', ['id' => $taiLieu->id]);
        Storage::disk(TaiLieuBaiHoc::DISK)->assertMissing($duongDan);
    }

    public function test_xoa_bai_hoc_tu_dong_don_toan_bo_thu_muc_pdf(): void
    {
        [$giangVien, , $chuong] = $this->duLieuKhoaHoc();
        $baiHoc = $chuong->lessons()->create(['title' => 'Bài 1']);
        Storage::disk(TaiLieuBaiHoc::DISK)->put($baiHoc->id.'/a.pdf', '%PDF-1.4');
        $baiHoc->attachments()->create([
            'original_name' => 'a.pdf', 'path' => $baiHoc->id.'/a.pdf', 'size' => 8, 'mime_type' => 'application/pdf',
        ]);

        $this->actingAs($giangVien)->delete(route('instructor.lessons.destroy', $baiHoc))->assertRedirect();

        Storage::disk(TaiLieuBaiHoc::DISK)->assertMissing($baiHoc->id.'/a.pdf');
        $this->assertDatabaseMissing('lesson_attachments', ['lesson_id' => $baiHoc->id]);
    }

    public function test_xoa_chuong_hoac_khoa_hoc_tu_dong_don_pdf_cua_cac_bai(): void
    {
        [$giangVien, $khoaHoc, $chuong] = $this->duLieuKhoaHoc();
        $baiA = $chuong->lessons()->create(['title' => 'Bài A']);
        Storage::disk(TaiLieuBaiHoc::DISK)->put($baiA->id.'/a.pdf', '%PDF-1.4');

        $this->actingAs($giangVien)->delete(route('instructor.sections.destroy', $chuong))->assertRedirect();
        Storage::disk(TaiLieuBaiHoc::DISK)->assertDirectoryEmpty((string) $baiA->id);

        $chuongMoi = $khoaHoc->sections()->create(['title' => 'Chương mới']);
        $baiB = $chuongMoi->lessons()->create(['title' => 'Bài B']);
        Storage::disk(TaiLieuBaiHoc::DISK)->put($baiB->id.'/b.pdf', '%PDF-1.4');

        $this->actingAs($giangVien)->delete(route('instructor.courses.destroy', $khoaHoc))->assertRedirect();
        Storage::disk(TaiLieuBaiHoc::DISK)->assertDirectoryEmpty((string) $baiB->id);
    }
}
