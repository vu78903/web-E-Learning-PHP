<?php

namespace Tests\Feature;

use App\Models\TienDoBaiHoc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\TaoDuLieu;
use Tests\TestCase;

class HocTapTest extends TestCase
{
    use TaoDuLieu, RefreshDatabase;

    public function test_only_enrolled_student_can_view_content(): void
    {
        $inst = $this->giangVien();
        $course = $this->khoaHocCoBaiHoc($inst);
        $lesson = $course->lessons()->first();
        $enrolled = $this->hocVien();
        $this->dangKyKhoaHoc($enrolled, $course);

        $this->get(route('learn.lesson', [$course, $lesson]))->assertRedirect('/login');
        $this->actingAs($this->hocVien())->get(route('learn.show', $course))->assertForbidden();
        $this->actingAs($this->hocVien())->get(route('learn.lesson', [$course, $lesson]))->assertForbidden();
        $this->actingAs($this->giangVien())->get(route('learn.lesson', [$course, $lesson]))->assertForbidden();

        $this->actingAs($enrolled)->get(route('learn.show', $course))->assertOk();
        $this->actingAs($enrolled)->get(route('learn.lesson', [$course, $lesson]))->assertOk()->assertSee('Nội dung 1');
        $this->actingAs($inst)->get(route('learn.lesson', [$course, $lesson]))->assertOk();
        $this->actingAs($this->quanTri())->get(route('learn.lesson', [$course, $lesson]))->assertOk();
    }

    public function test_cancelled_enrollment_loses_access(): void
    {
        $course = $this->khoaHocCoBaiHoc($this->giangVien());
        $student = $this->hocVien();
        $this->dangKyKhoaHoc($student, $course)->update(['status' => 'cancelled']);

        $this->actingAs($student)->get(route('learn.show', $course))->assertForbidden();
    }

    public function test_lesson_must_belong_to_course(): void
    {
        $inst = $this->giangVien();
        $courseA = $this->khoaHocCoBaiHoc($inst);
        $courseB = $this->khoaHocCoBaiHoc($inst);
        $student = $this->hocVien();
        $this->dangKyKhoaHoc($student, $courseA);

        // Học viên chỉ đăng ký A không thể xem bài của B bằng cách ghép URL.
        $this->actingAs($student)->get(route('learn.lesson', [$courseA, $courseB->lessons()->first()]))->assertNotFound();
        $this->actingAs($student)->get(route('learn.lesson', [$courseB, $courseB->lessons()->first()]))->assertForbidden();
    }

    public function test_cannot_complete_lesson_of_unenrolled_course(): void
    {
        $course = $this->khoaHocCoBaiHoc($this->giangVien());
        $lesson = $course->lessons()->first();
        $student = $this->hocVien();

        $this->actingAs($student)->post(route('lessons.complete', $lesson))->assertForbidden();
        $this->assertDatabaseCount('lesson_progress', 0);
    }

    public function test_progress_changes_when_lessons_marked_complete(): void
    {
        $course = $this->khoaHocCoBaiHoc($this->giangVien(), 4);
        $student = $this->hocVien();
        $this->dangKyKhoaHoc($student, $course);
        $lessons = $course->lessons()->get();

        $this->assertSame(0, $course->tienDoCua($student));

        $this->actingAs($student)->post(route('lessons.complete', $lessons[0]))->assertRedirect();
        $this->assertSame(25, $course->tienDoCua($student));

        $this->actingAs($student)->post(route('lessons.complete', $lessons[1]))->assertRedirect();
        $this->assertSame(50, $course->tienDoCua($student));

        // Bấm lần nữa để bỏ đánh dấu.
        $this->actingAs($student)->post(route('lessons.complete', $lessons[1]));
        $this->assertSame(25, $course->tienDoCua($student));

        foreach ($lessons as $l) {
            TienDoBaiHoc::updateOrCreate(['user_id' => $student->id, 'lesson_id' => $l->id], ['is_completed' => true]);
        }
        $this->assertSame(100, $course->tienDoCua($student));
    }

    public function test_progress_is_per_student(): void
    {
        $course = $this->khoaHocCoBaiHoc($this->giangVien(), 2);
        $a = $this->hocVien();
        $b = $this->hocVien();
        $this->dangKyKhoaHoc($a, $course);
        $this->dangKyKhoaHoc($b, $course);

        $this->actingAs($a)->post(route('lessons.complete', $course->lessons()->first()));

        $this->assertSame(50, $course->tienDoCua($a));
        $this->assertSame(0, $course->tienDoCua($b));
    }

    public function test_progress_of_empty_course_is_zero(): void
    {
        $course = $this->taoKhoaHoc($this->giangVien());
        $this->assertSame(0, $course->tienDoCua($this->hocVien()));
    }

    public function test_youtube_links_are_embedded(): void
    {
        $course = $this->khoaHocCoBaiHoc($this->giangVien(), 1);
        $lesson = $course->lessons()->first();
        $lesson->update(['video_url' => 'https://www.youtube.com/watch?v=ImtZ5yENzgE']);
        $this->assertSame('https://www.youtube.com/embed/ImtZ5yENzgE', $lesson->duongDanNhungVideo());
        $lesson->update(['video_url' => 'https://youtu.be/ImtZ5yENzgE']);
        $this->assertSame('https://www.youtube.com/embed/ImtZ5yENzgE', $lesson->duongDanNhungVideo());
        $lesson->update(['video_url' => 'https://example.com/video.mp4']);
        $this->assertNull($lesson->duongDanNhungVideo());
    }

    public function test_lesson_content_is_escaped(): void
    {
        $course = $this->khoaHocCoBaiHoc($this->giangVien(), 1);
        $lesson = $course->lessons()->first();
        $lesson->update(['content' => '<script>alert(1)</script>']);
        $student = $this->hocVien();
        $this->dangKyKhoaHoc($student, $course);

        $this->actingAs($student)->get(route('learn.lesson', [$course, $lesson]))
            ->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;', false);
    }

    public function test_curriculum_crud_by_owner_only(): void
    {
        Http::fake(['www.youtube.com/oembed*' => Http::response([
            'title' => 'Video mẫu',
            'thumbnail_url' => 'https://i.ytimg.com/vi/abcdefghijk/hqdefault.jpg',
            'author_name' => 'Giảng viên',
        ])]);

        $owner = $this->giangVien();
        $course = $this->taoKhoaHoc($owner);

        $this->actingAs($owner)->post(route('instructor.sections.store', $course), ['title' => 'Chương A'])->assertRedirect();
        $section = $course->sections()->first();
        $this->assertSame(1, $section->sort_order);

        $this->actingAs($owner)->post(route('instructor.lessons.store', $section), [
            'title' => 'Bài 1', 'content' => 'abc', 'video_url' => 'https://youtu.be/abcdefghijk',
        ])->assertRedirect(route('instructor.curriculum', $course));
        $lesson = $section->lessons()->first();

        $this->actingAs($owner)->put(route('instructor.lessons.update', $lesson), ['title' => 'Bài 1 (sửa)'])->assertRedirect();
        $this->assertSame('Bài 1 (sửa)', $lesson->fresh()->title);

        $intruder = $this->giangVien();
        $this->actingAs($intruder)->get(route('instructor.curriculum', $course))->assertForbidden();
        $this->actingAs($intruder)->post(route('instructor.sections.store', $course), ['title' => 'X'])->assertForbidden();
        $this->actingAs($intruder)->post(route('instructor.lessons.store', $section), ['title' => 'X'])->assertForbidden();
        $this->actingAs($intruder)->put(route('instructor.lessons.update', $lesson), ['title' => 'X'])->assertForbidden();
        $this->actingAs($intruder)->delete(route('instructor.lessons.destroy', $lesson))->assertForbidden();
        $this->actingAs($intruder)->delete(route('instructor.sections.destroy', $section))->assertForbidden();

        $this->actingAs($owner)->delete(route('instructor.sections.destroy', $section))->assertRedirect();
        $this->assertDatabaseCount('lessons', 0);
    }

    public function test_lesson_url_validation(): void
    {
        $owner = $this->giangVien();
        $course = $this->taoKhoaHoc($owner);
        $section = $course->sections()->create(['title' => 'C', 'sort_order' => 1]);

        $this->actingAs($owner)->post(route('instructor.lessons.store', $section), ['title' => 'A', 'video_url' => 'khong-phai-url'])
            ->assertSessionHasErrors('video_url');
        // javascript: URL không hợp lệ
        $this->actingAs($owner)->post(route('instructor.lessons.store', $section), ['title' => 'A', 'document_url' => 'javascript:alert(1)'])
            ->assertSessionHasErrors('document_url');
    }
}
