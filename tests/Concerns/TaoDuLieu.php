<?php

namespace Tests\Concerns;

use App\Models\DanhMuc;
use App\Models\KhoaHoc;
use App\Models\DangKyKhoaHoc;
use App\Models\BaiKiemTra;
use App\Models\NguoiDung;

trait TaoDuLieu
{
    protected function quanTri(): NguoiDung
    {
        return NguoiDung::factory()->admin()->create();
    }

    protected function giangVien(): NguoiDung
    {
        return NguoiDung::factory()->instructor()->create();
    }

    protected function hocVien(): NguoiDung
    {
        return NguoiDung::factory()->student()->create();
    }

    protected function taoKhoaHoc(NguoiDung $instructor, string $status = KhoaHoc::STATUS_PUBLISHED): KhoaHoc
    {
        $category = DanhMuc::firstOrCreate(['name' => 'Lập trình']);
        $course = new KhoaHoc(['category_id' => $category->id, 'title' => 'Khóa học '.uniqid(), 'description' => 'Mô tả', 'status' => $status]);
        $course->instructor_id = $instructor->id;
        $course->save();

        return $course;
    }

    /** Khóa học có 1 chương, $lessons bài học. */
    protected function khoaHocCoBaiHoc(NguoiDung $instructor, int $lessons = 4): KhoaHoc
    {
        $course = $this->taoKhoaHoc($instructor);
        $section = $course->sections()->create(['title' => 'Chương 1', 'sort_order' => 1]);
        for ($i = 1; $i <= $lessons; $i++) {
            $section->lessons()->create(['title' => "Bài $i", 'content' => "Nội dung $i", 'sort_order' => $i]);
        }

        return $course->fresh();
    }

    protected function dangKyKhoaHoc(NguoiDung $student, KhoaHoc $course): DangKyKhoaHoc
    {
        return DangKyKhoaHoc::create(['user_id' => $student->id, 'course_id' => $course->id, 'enrolled_at' => now(), 'status' => 'active']);
    }

    /** Bài kiểm tra 4 câu, mỗi câu 3 phương án, phương án đầu tiên là đúng. */
    protected function taoBaiKiemTra(KhoaHoc $course, int $pass = 50, int $minutes = 10): BaiKiemTra
    {
        $quiz = $course->quizzes()->create(['title' => 'Kiểm tra', 'duration_minutes' => $minutes, 'pass_score' => $pass]);
        for ($i = 1; $i <= 4; $i++) {
            $q = $quiz->questions()->create(['question_text' => "Câu $i?"]);
            foreach (['Đúng', 'Sai 1', 'Sai 2'] as $k => $text) {
                $q->answers()->create(['answer_text' => $text, 'is_correct' => $k === 0]);
            }
        }

        return $quiz->load('questions.answers');
    }
}
