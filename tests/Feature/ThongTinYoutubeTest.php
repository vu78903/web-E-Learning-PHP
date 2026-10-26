<?php

namespace Tests\Feature;

use App\ChucNang\LayThongTinYoutube;
use App\Models\BaiHoc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\TaoDuLieu;
use Tests\TestCase;

class ThongTinYoutubeTest extends TestCase
{
    use TaoDuLieu, RefreshDatabase;

    private function giaLapOEmbed(): void
    {
        Http::fake(['www.youtube.com/oembed*' => Http::response([
            'title' => 'Laravel cho người mới',
            'thumbnail_url' => 'https://i.ytimg.com/vi/AbCdEf12345/hqdefault.jpg',
            'author_name' => 'E-Learning Việt',
        ])]);
    }

    public function test_nhan_dang_cac_dang_link_youtube_pho_bien(): void
    {
        $chucNang = app(LayThongTinYoutube::class);

        $this->assertSame('AbCdEf12345', $chucNang->layIdVideo('https://www.youtube.com/watch?v=AbCdEf12345'));
        $this->assertSame('AbCdEf12345', $chucNang->layIdVideo('https://youtu.be/AbCdEf12345?t=10'));
        $this->assertSame('AbCdEf12345', $chucNang->layIdVideo('https://youtube.com/shorts/AbCdEf12345'));
        $this->assertSame('AbCdEf12345', $chucNang->layIdVideo('https://youtube.com/embed/AbCdEf12345'));
        $this->assertNull($chucNang->layIdVideo('https://youtube.com.evil.test/watch?v=AbCdEf12345'));
        $this->assertNull($chucNang->layIdVideo('https://example.com/video'));
    }

    public function test_goi_oembed_va_chuan_hoa_du_lieu_tra_ve(): void
    {
        $this->giaLapOEmbed();

        $thongTin = app(LayThongTinYoutube::class)->lay('https://www.youtube.com/watch?v=AbCdEf12345');

        $this->assertSame('AbCdEf12345', $thongTin['id_video']);
        $this->assertSame('Laravel cho người mới', $thongTin['tieu_de']);
        $this->assertSame('https://i.ytimg.com/vi/AbCdEf12345/hqdefault.jpg', $thongTin['anh_dai_dien']);
        $this->assertSame('E-Learning Việt', $thongTin['tac_gia']);
        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'https://www.youtube.com/oembed?')
            && $request['format'] === 'json'
            && $request['url'] === 'https://www.youtube.com/watch?v=AbCdEf12345');
    }

    public function test_link_rut_gon_va_shorts_duoc_chuan_hoa_truoc_khi_goi_oembed(): void
    {
        $this->giaLapOEmbed();

        app(LayThongTinYoutube::class)->lay('https://youtube.com/shorts/AbCdEf12345?feature=share');

        Http::assertSent(fn ($request) => $request['url'] === 'https://www.youtube.com/watch?v=AbCdEf12345');
    }

    public function test_tao_link_nhung_an_toan_cho_moi_dang_link_youtube(): void
    {
        foreach ([
            'https://www.youtube.com/watch?v=AbCdEf12345',
            'https://youtu.be/AbCdEf12345?t=10',
            'https://youtube.com/shorts/AbCdEf12345',
            'https://youtube.com/live/AbCdEf12345',
        ] as $url) {
            $baiHoc = new BaiHoc(['video_url' => $url]);
            $this->assertSame('https://www.youtube.com/embed/AbCdEf12345', $baiHoc->duongDanNhungVideo());
        }

        $this->assertNull((new BaiHoc(['video_url' => 'https://youtube.com.evil.test/watch?v=AbCdEf12345']))->duongDanNhungVideo());
    }

    public function test_api_noi_bo_tra_thong_tin_cho_giao_dien(): void
    {
        $this->giaLapOEmbed();

        $this->actingAs($this->giangVien())
            ->getJson(route('instructor.youtube.thong-tin', ['url' => 'https://youtu.be/AbCdEf12345']))
            ->assertOk()
            ->assertJson([
                'id_video' => 'AbCdEf12345',
                'tieu_de' => 'Laravel cho người mới',
                'tac_gia' => 'E-Learning Việt',
            ]);
    }

    public function test_luu_bai_hoc_tu_dong_lay_tieu_de_va_anh_dai_dien(): void
    {
        $this->giaLapOEmbed();
        $giangVien = $this->giangVien();
        $khoaHoc = $this->taoKhoaHoc($giangVien);
        $chuong = $khoaHoc->sections()->create(['title' => 'Chương 1', 'sort_order' => 1]);

        $this->actingAs($giangVien)->post(route('instructor.lessons.store', $chuong), [
            'title' => '',
            'video_url' => 'https://www.youtube.com/watch?v=AbCdEf12345',
        ])->assertRedirect(route('instructor.curriculum', $khoaHoc));

        $this->assertDatabaseHas('lessons', [
            'section_id' => $chuong->id,
            'title' => 'Laravel cho người mới',
            'video_title' => 'Laravel cho người mới',
            'video_thumbnail' => 'https://i.ytimg.com/vi/AbCdEf12345/hqdefault.jpg',
        ]);
    }

    public function test_tu_choi_khi_oembed_khong_tim_thay_video(): void
    {
        Http::fake(['www.youtube.com/oembed*' => Http::response([], 404)]);
        $giangVien = $this->giangVien();
        $khoaHoc = $this->taoKhoaHoc($giangVien);
        $chuong = $khoaHoc->sections()->create(['title' => 'Chương 1', 'sort_order' => 1]);

        $this->actingAs($giangVien)->post(route('instructor.lessons.store', $chuong), [
            'title' => 'Video lỗi',
            'video_url' => 'https://youtu.be/AbCdEf12345',
        ])->assertSessionHasErrors('video_url');

        $this->assertDatabaseCount('lessons', 0);
    }

    public function test_giu_tieu_de_nhap_tay_va_xoa_metadata_khi_doi_sang_video_khac(): void
    {
        $this->giaLapOEmbed();
        $giangVien = $this->giangVien();
        $khoaHoc = $this->taoKhoaHoc($giangVien);
        $chuong = $khoaHoc->sections()->create(['title' => 'Chương 1', 'sort_order' => 1]);

        $this->actingAs($giangVien)->post(route('instructor.lessons.store', $chuong), [
            'title' => 'Tiêu đề do giảng viên nhập',
            'video_url' => 'https://youtu.be/AbCdEf12345',
        ])->assertSessionHasNoErrors();

        $baiHoc = $chuong->lessons()->firstOrFail();
        $this->assertSame('Tiêu đề do giảng viên nhập', $baiHoc->title);
        $this->assertSame('Laravel cho người mới', $baiHoc->video_title);

        $this->actingAs($giangVien)->put(route('instructor.lessons.update', $baiHoc), [
            'title' => $baiHoc->title,
            'video_url' => 'https://vimeo.com/12345678',
        ])->assertSessionHasNoErrors();

        $baiHoc->refresh();
        $this->assertNull($baiHoc->video_title);
        $this->assertNull($baiHoc->video_thumbnail);
    }

    public function test_api_tu_choi_url_khong_phai_youtube(): void
    {
        $this->actingAs($this->giangVien())
            ->getJson(route('instructor.youtube.thong-tin', ['url' => 'https://example.com/video']))
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Không lấy được thông tin video. Hãy kiểm tra link YouTube và thử lại.');

        Http::assertNothingSent();
    }

    public function test_khach_va_hoc_vien_khong_duoc_goi_api_quan_tri(): void
    {
        $url = route('instructor.youtube.thong-tin', ['url' => 'https://youtu.be/AbCdEf12345']);

        $this->getJson($url)->assertUnauthorized();
        $this->actingAs($this->hocVien())->getJson($url)->assertForbidden();
    }
}
