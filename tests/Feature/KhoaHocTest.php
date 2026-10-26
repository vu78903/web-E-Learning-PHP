<?php

namespace Tests\Feature;

use App\Models\DanhMuc;
use App\Models\KhoaHoc;
use App\Models\DangKyKhoaHoc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\TaoDuLieu;
use Tests\TestCase;

class KhoaHocTest extends TestCase
{
    use TaoDuLieu, RefreshDatabase;

    public function test_listing_shows_only_published_and_supports_search_and_filter(): void
    {
        $inst = $this->giangVien();
        $a = $this->taoKhoaHoc($inst);
        $a->update(['title' => 'Laravel nâng cao']);
        $b = $this->taoKhoaHoc($inst);
        $b->update(['title' => 'Python']);
        $draft = $this->taoKhoaHoc($inst, KhoaHoc::STATUS_DRAFT);
        $draft->update(['title' => 'Bản nháp bí mật']);

        $this->get('/courses')->assertSee('Laravel nâng cao')->assertSee('Python')->assertDontSee('Bản nháp bí mật');
        $this->get('/courses?q=Laravel')->assertSee('Laravel nâng cao')->assertDontSee('Python');

        $other = DanhMuc::create(['name' => 'Khác']);
        $b->update(['category_id' => $other->id]);
        $this->get('/courses?category='.$other->id)->assertSee('Python')->assertDontSee('Laravel nâng cao');
    }

    public function test_draft_course_visible_only_to_owner_and_admin(): void
    {
        $inst = $this->giangVien();
        $draft = $this->taoKhoaHoc($inst, KhoaHoc::STATUS_DRAFT);

        $this->get(route('courses.show', $draft))->assertNotFound();
        $this->actingAs($this->hocVien())->get(route('courses.show', $draft))->assertNotFound();
        $this->actingAs($this->giangVien())->get(route('courses.show', $draft))->assertNotFound();
        $this->actingAs($inst)->get(route('courses.show', $draft))->assertOk();
        $this->actingAs($this->quanTri())->get(route('courses.show', $draft))->assertOk();
    }

    public function test_student_can_enroll_and_duplicate_enrollment_is_prevented(): void
    {
        $student = $this->hocVien();
        $course = $this->taoKhoaHoc($this->giangVien());

        $this->actingAs($student)->post(route('courses.enroll', $course))->assertRedirect(route('learn.show', $course));
        $this->actingAs($student)->post(route('courses.enroll', $course))->assertRedirect(route('courses.show', $course));

        $this->assertSame(1, DangKyKhoaHoc::where('user_id', $student->id)->where('course_id', $course->id)->count());
    }

    public function test_database_unique_key_blocks_duplicate_enrollment(): void
    {
        $student = $this->hocVien();
        $course = $this->taoKhoaHoc($this->giangVien());
        $this->dangKyKhoaHoc($student, $course);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->dangKyKhoaHoc($student, $course);
    }

    public function test_cannot_enroll_in_unpublished_course(): void
    {
        $course = $this->taoKhoaHoc($this->giangVien(), KhoaHoc::STATUS_DRAFT);

        $this->actingAs($this->hocVien())->post(route('courses.enroll', $course))->assertNotFound();
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_instructor_creates_course_owned_by_self(): void
    {
        $inst = $this->giangVien();
        $cat = DanhMuc::create(['name' => 'Web']);

        $this->actingAs($inst)->post('/instructor/courses', [
            'title' => 'Khóa mới', 'category_id' => $cat->id, 'description' => 'Mô tả', 'status' => 'published',
            'instructor_id' => 999, // phải bị bỏ qua
        ])->assertRedirect();

        $course = KhoaHoc::firstOrFail();
        $this->assertSame($inst->id, $course->instructor_id);
        $this->assertSame('published', $course->status);
    }

    public function test_instructor_cannot_self_set_hidden_status(): void
    {
        $inst = $this->giangVien();
        $cat = DanhMuc::create(['name' => 'Web']);

        $this->actingAs($inst)->post('/instructor/courses', [
            'title' => 'X', 'category_id' => $cat->id, 'status' => 'hidden',
        ])->assertSessionHasErrors('status');
    }

    public function test_instructor_can_edit_own_course_but_not_others(): void
    {
        $owner = $this->giangVien();
        $other = $this->giangVien();
        $course = $this->taoKhoaHoc($owner);

        $payload = ['title' => 'Tên đã sửa', 'category_id' => $course->category_id, 'description' => 'x', 'status' => 'published'];

        $this->actingAs($other)->get(route('instructor.courses.edit', $course))->assertForbidden();
        $this->actingAs($other)->put(route('instructor.courses.update', $course), $payload)->assertForbidden();
        $this->actingAs($other)->delete(route('instructor.courses.destroy', $course))->assertForbidden();

        $this->actingAs($owner)->put(route('instructor.courses.update', $course), $payload)->assertRedirect();
        $this->assertSame('Tên đã sửa', $course->fresh()->title);

        $this->actingAs($this->quanTri())->put(route('instructor.courses.update', $course), array_merge($payload, ['title' => 'Admin sửa']))->assertRedirect();
        $this->assertSame('Admin sửa', $course->fresh()->title);
    }

    public function test_instructor_cannot_unhide_course_hidden_by_admin(): void
    {
        $inst = $this->giangVien();
        $course = $this->taoKhoaHoc($inst, KhoaHoc::STATUS_HIDDEN);

        $this->actingAs($inst)->put(route('instructor.courses.update', $course), [
            'title' => $course->title, 'category_id' => $course->category_id, 'status' => 'published',
        ])->assertRedirect();

        $this->assertSame('hidden', $course->fresh()->status);
    }

    public function test_thumbnail_upload(): void
    {
        Storage::fake('public');
        $inst = $this->giangVien();
        $cat = DanhMuc::create(['name' => 'Web']);

        $this->actingAs($inst)->post('/instructor/courses', [
            'title' => 'Có ảnh', 'category_id' => $cat->id, 'status' => 'draft',
            'thumbnail' => UploadedFile::fake()->image('cover.jpg'),
        ])->assertRedirect();

        $course = KhoaHoc::firstOrFail();
        Storage::disk('public')->assertExists($course->thumbnail);
    }

    public function test_my_courses_lists_enrolled_with_progress(): void
    {
        $student = $this->hocVien();
        $course = $this->khoaHocCoBaiHoc($this->giangVien(), 4);
        $this->dangKyKhoaHoc($student, $course);

        $this->actingAs($student)->get('/my-courses')->assertOk()->assertSee($course->title)->assertSee('0% hoàn thành');
    }

    public function test_instructor_sees_only_own_students(): void
    {
        $inst = $this->giangVien();
        $course = $this->taoKhoaHoc($inst);
        $student = $this->hocVien();
        $this->dangKyKhoaHoc($student, $course);

        $this->actingAs($inst)->get(route('instructor.courses.students', $course))->assertOk()->assertSee($student->email);
        $this->actingAs($this->giangVien())->get(route('instructor.courses.students', $course))->assertForbidden();
    }
}
