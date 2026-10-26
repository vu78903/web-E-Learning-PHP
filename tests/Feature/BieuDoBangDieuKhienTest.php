<?php

namespace Tests\Feature;

use App\Models\LuotLamBai;
use App\ChucNang\DuLieuBieuDo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\TaoDuLieu;
use Tests\TestCase;

class BieuDoBangDieuKhienTest extends TestCase
{
    use TaoDuLieu, RefreshDatabase;

    private function submit($quiz, $student, float $score): void
    {
        LuotLamBai::create(['quiz_id' => $quiz->id, 'user_id' => $student->id, 'started_at' => now(), 'submitted_at' => now(),
            'score' => $score, 'correct_count' => 0, 'total_questions' => 4, 'passed' => $score >= 50]);
    }

    public function test_admin_charts_cover_whole_system(): void
    {
        $gv1 = $this->giangVien();
        $gv2 = $this->giangVien();
        $a = $this->taoKhoaHoc($gv1);
        $b = $this->taoKhoaHoc($gv2);
        $this->taoKhoaHoc($gv2); // chưa ai đăng ký: không vào top
        [$s1, $s2, $s3] = [$this->hocVien(), $this->hocVien(), $this->hocVien()];
        $this->dangKyKhoaHoc($s1, $a);
        $this->dangKyKhoaHoc($s2, $a);
        $this->dangKyKhoaHoc($s3, $b)->forceFill(['enrolled_at' => now()->subWeeks(2)])->save();
        $this->dangKyKhoaHoc($s1, $b)->forceFill(['enrolled_at' => now()->subWeeks(20)])->save(); // ngoài 12 tuần
        $this->submit($this->taoBaiKiemTra($a), $s1, 100);
        $this->submit($quizB = $this->taoBaiKiemTra($b), $s3, 0);
        $this->submit($quizB, $s1, 59.5);

        $charts = $this->actingAs($this->quanTri())->get('/admin')->assertOk()
            ->assertSee('chart.js@4.4.1', false)->viewData('charts');

        $this->assertCount(DuLieuBieuDo::SO_TUAN, $charts['weekly']['data']);
        $this->assertSame(2, end($charts['weekly']['data']));
        $this->assertSame(1, $charts['weekly']['data'][DuLieuBieuDo::SO_TUAN - 3]);
        $this->assertSame(3, array_sum($charts['weekly']['data']));
        $this->assertSame([1, 0, 1, 0, 1], $charts['scores']['data']);
        $this->assertSame([$a->title, $b->title], $charts['top']['labels']->all());
        $this->assertSame([2, 2], $charts['top']['data']->all());
        $this->assertSame([$gv1->name, $gv2->name], $charts['top']['instructors']->all());
    }

    public function test_instructor_dashboard_only_counts_own_courses(): void
    {
        $gv1 = $this->giangVien();
        $gv2 = $this->giangVien();
        $mine = $this->taoKhoaHoc($gv1);
        $other = $this->taoKhoaHoc($gv2);
        $s = $this->hocVien();
        $this->dangKyKhoaHoc($s, $mine);
        $this->dangKyKhoaHoc($this->hocVien(), $other);
        $this->dangKyKhoaHoc($this->hocVien(), $other);
        $this->submit($this->taoBaiKiemTra($mine), $s, 90);
        $this->submit($this->taoBaiKiemTra($other), $s, 10);

        $response = $this->actingAs($gv1)->get(route('instructor.dashboard'))->assertOk()
            ->assertSee($mine->title)->assertDontSee($other->title);
        $charts = $response->viewData('charts');
        $stats = $response->viewData('stats');

        $this->assertSame(1, array_sum($charts['weekly']['data']));
        $this->assertSame([0, 0, 0, 0, 1], $charts['scores']['data']);
        $this->assertSame([$mine->title], $charts['top']['labels']->all());
        $this->assertArrayNotHasKey('instructors', $charts['top']);
        $this->assertSame(1, $stats['courses']);
        $this->assertSame(1, $stats['enrollments']);
        $this->assertSame(1, $stats['attempts']);
    }

    public function test_empty_dashboard_shows_placeholder(): void
    {
        $this->actingAs($this->giangVien())->get(route('instructor.dashboard'))->assertOk()
            ->assertSee('Chưa có dữ liệu.')->assertDontSee('<canvas', false);
    }

    public function test_only_instructors_can_open_instructor_dashboard(): void
    {
        $this->actingAs($this->hocVien())->get(route('instructor.dashboard'))->assertForbidden();
        $this->actingAs($this->quanTri())->get(route('instructor.dashboard'))->assertForbidden();
    }
}
