<?php

namespace Tests\Feature;

use App\Models\BaiKiemTra;
use App\Models\LuotLamBai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\TaoDuLieu;
use Tests\TestCase;

class BaiKiemTraTest extends TestCase
{
    use TaoDuLieu, RefreshDatabase;

    private function setupQuiz(int $pass = 50, int $minutes = 10): array
    {
        $inst = $this->giangVien();
        $course = $this->khoaHocCoBaiHoc($inst, 1);
        $quiz = $this->taoBaiKiemTra($course, $pass, $minutes);
        $student = $this->hocVien();
        $this->dangKyKhoaHoc($student, $course);

        return [$inst, $course, $quiz, $student];
    }

    /** Tạo mảng answers[question_id] = answer_id với $correct câu đúng. */
    private function picks(BaiKiemTra $quiz, int $correct): array
    {
        $out = [];
        foreach ($quiz->questions->values() as $i => $q) {
            $answer = $i < $correct ? $q->answers->firstWhere('is_correct', true) : $q->answers->firstWhere('is_correct', false);
            $out[$q->id] = $answer->id;
        }

        return $out;
    }

    public function test_full_flow_grades_correctly_and_stores_history(): void
    {
        [, , $quiz, $student] = $this->setupQuiz(pass: 60);

        $this->actingAs($student)->post(route('quizzes.start', $quiz))->assertRedirect();
        $attempt = LuotLamBai::firstOrFail();

        $this->actingAs($student)->get(route('attempts.take', $attempt))->assertOk()->assertSee('Câu 1.');

        $this->actingAs($student)->post(route('attempts.submit', $attempt), ['answers' => $this->picks($quiz, 3)])
            ->assertRedirect(route('attempts.result', $attempt));

        $attempt->refresh();
        $this->assertSame(3, $attempt->correct_count);
        $this->assertSame(4, $attempt->total_questions);
        $this->assertEquals(75.0, $attempt->score);
        $this->assertTrue($attempt->passed);
        $this->assertNotNull($attempt->submitted_at);
        $this->assertDatabaseCount('quiz_attempt_answers', 4);

        $this->actingAs($student)->get(route('attempts.result', $attempt))->assertOk()->assertSee('ĐẠT')->assertSee('Đúng 3/4 câu');
    }

    public function test_failing_score_and_boundary_pass_mark(): void
    {
        [, , $quiz, $student] = $this->setupQuiz(pass: 50);

        // 2/4 = 50% => đạt (>= điểm đạt)
        $a = LuotLamBai::create(['quiz_id' => $quiz->id, 'user_id' => $student->id, 'started_at' => now()]);
        $this->actingAs($student)->post(route('attempts.submit', $a), ['answers' => $this->picks($quiz, 2)]);
        $this->assertTrue($a->fresh()->passed);

        // 1/4 = 25% => chưa đạt
        $b = LuotLamBai::create(['quiz_id' => $quiz->id, 'user_id' => $student->id, 'started_at' => now()]);
        $this->actingAs($student)->post(route('attempts.submit', $b), ['answers' => $this->picks($quiz, 1)]);
        $this->assertFalse($b->fresh()->passed);
        $this->assertEquals(25.0, $b->fresh()->score);
    }

    public function test_unanswered_questions_score_zero(): void
    {
        [, , $quiz, $student] = $this->setupQuiz();
        $a = LuotLamBai::create(['quiz_id' => $quiz->id, 'user_id' => $student->id, 'started_at' => now()]);

        $this->actingAs($student)->post(route('attempts.submit', $a), [])->assertRedirect();

        $this->assertEquals(0.0, $a->fresh()->score);
        $this->assertSame(0, $a->fresh()->correct_count);
    }

    public function test_answer_from_another_question_does_not_count(): void
    {
        [, , $quiz, $student] = $this->setupQuiz();
        $a = LuotLamBai::create(['quiz_id' => $quiz->id, 'user_id' => $student->id, 'started_at' => now()]);
        $q1 = $quiz->questions[0];
        $q2 = $quiz->questions[1];

        // Gửi đáp án đúng của câu 2 cho câu 1 => không tính điểm, không lưu.
        $this->actingAs($student)->post(route('attempts.submit', $a), [
            'answers' => [$q1->id => $q2->answers->firstWhere('is_correct', true)->id],
        ]);

        $this->assertSame(0, $a->fresh()->correct_count);
        $this->assertDatabaseMissing('quiz_attempt_answers', ['attempt_id' => $a->id, 'answer_id' => $q2->answers->firstWhere('is_correct', true)->id]);
    }

    public function test_cannot_submit_twice(): void
    {
        [, , $quiz, $student] = $this->setupQuiz();
        $a = LuotLamBai::create(['quiz_id' => $quiz->id, 'user_id' => $student->id, 'started_at' => now()]);

        $this->actingAs($student)->post(route('attempts.submit', $a), ['answers' => $this->picks($quiz, 1)]);
        $this->actingAs($student)->post(route('attempts.submit', $a), ['answers' => $this->picks($quiz, 4)]);

        $this->assertSame(1, $a->fresh()->correct_count);
        $this->assertDatabaseCount('quiz_attempt_answers', 4);
    }

    public function test_late_submission_is_not_credited(): void
    {
        [, , $quiz, $student] = $this->setupQuiz(minutes: 10);
        $a = LuotLamBai::create(['quiz_id' => $quiz->id, 'user_id' => $student->id, 'started_at' => now()->subMinutes(20)]);

        $this->actingAs($student)->post(route('attempts.submit', $a), ['answers' => $this->picks($quiz, 4)]);

        $this->assertSame(0, $a->fresh()->correct_count);
    }

    public function test_expired_attempt_is_auto_closed_on_open(): void
    {
        [, , $quiz, $student] = $this->setupQuiz(minutes: 10);
        $a = LuotLamBai::create(['quiz_id' => $quiz->id, 'user_id' => $student->id, 'started_at' => now()->subMinutes(30)]);

        $this->actingAs($student)->get(route('attempts.take', $a))->assertRedirect(route('attempts.result', $a));
        $this->assertNotNull($a->fresh()->submitted_at);
    }

    public function test_start_resumes_unfinished_attempt(): void
    {
        [, , $quiz, $student] = $this->setupQuiz();

        $this->actingAs($student)->post(route('quizzes.start', $quiz));
        $this->actingAs($student)->post(route('quizzes.start', $quiz));

        $this->assertSame(1, LuotLamBai::count());
    }

    public function test_attempt_page_does_not_leak_correct_answers(): void
    {
        [, , $quiz, $student] = $this->setupQuiz();
        $a = LuotLamBai::create(['quiz_id' => $quiz->id, 'user_id' => $student->id, 'started_at' => now()]);

        $html = $this->actingAs($student)->get(route('attempts.take', $a))->getContent();
        $this->assertStringNotContainsString('is_correct', $html);
        $this->assertStringNotContainsString('bg-success-subtle', $html);
    }

    public function test_other_students_cannot_touch_attempt(): void
    {
        [, $course, $quiz, $student] = $this->setupQuiz();
        $other = $this->hocVien();
        $this->dangKyKhoaHoc($other, $course);
        $a = LuotLamBai::create(['quiz_id' => $quiz->id, 'user_id' => $student->id, 'started_at' => now()]);

        $this->actingAs($other)->get(route('attempts.take', $a))->assertForbidden();
        $this->actingAs($other)->post(route('attempts.submit', $a), [])->assertForbidden();

        $a->update(['submitted_at' => now(), 'score' => 0]);
        $this->actingAs($other)->get(route('attempts.result', $a))->assertForbidden();
    }

    public function test_unenrolled_student_cannot_take_quiz(): void
    {
        [, , $quiz] = $this->setupQuiz();
        $outsider = $this->hocVien();

        $this->actingAs($outsider)->get(route('quizzes.show', $quiz))->assertForbidden();
        $this->actingAs($outsider)->post(route('quizzes.start', $quiz))->assertForbidden();
        $this->assertDatabaseCount('quiz_attempts', 0);
    }

    public function test_instructor_cannot_start_attempt_but_can_view_results(): void
    {
        [$inst, , $quiz, $student] = $this->setupQuiz();
        $this->actingAs($inst)->post(route('quizzes.start', $quiz))->assertForbidden();

        $a = LuotLamBai::create(['quiz_id' => $quiz->id, 'user_id' => $student->id, 'started_at' => now(),
            'submitted_at' => now(), 'score' => 50, 'correct_count' => 2, 'total_questions' => 4, 'passed' => true]);

        $this->actingAs($inst)->get(route('attempts.result', $a))->assertOk();
        $this->actingAs($inst)->get(route('instructor.quizzes.results', $quiz))->assertOk()->assertSee($student->name);
        $this->actingAs($this->giangVien())->get(route('instructor.quizzes.results', $quiz))->assertForbidden();
    }

    public function test_quiz_without_questions_cannot_start(): void
    {
        $inst = $this->giangVien();
        $course = $this->taoKhoaHoc($inst);
        $quiz = $course->quizzes()->create(['title' => 'Rỗng', 'duration_minutes' => 5, 'pass_score' => 50]);
        $student = $this->hocVien();
        $this->dangKyKhoaHoc($student, $course);

        $this->actingAs($student)->post(route('quizzes.start', $quiz))->assertRedirect()->assertSessionHas('error');
        $this->assertDatabaseCount('quiz_attempts', 0);
    }

    // ---------- Soạn đề ----------

    public function test_instructor_creates_quiz_and_question(): void
    {
        $inst = $this->giangVien();
        $course = $this->taoKhoaHoc($inst);

        $this->actingAs($inst)->post(route('instructor.quizzes.store', $course), [
            'title' => 'Bài 1', 'duration_minutes' => 20, 'pass_score' => 70,
        ])->assertRedirect();
        $quiz = BaiKiemTra::firstOrFail();

        $this->actingAs($inst)->post(route('instructor.questions.store', $quiz), [
            'question_text' => '1+1=?',
            'answers' => [['text' => '1'], ['text' => '2'], ['text' => ''], ['text' => '3']],
            'correct' => 1,
        ])->assertRedirect();

        $q = $quiz->questions()->with('answers')->first();
        $this->assertCount(3, $q->answers);
        $this->assertSame('2', $q->answers->firstWhere('is_correct', true)->answer_text);
        $this->assertSame(1, $q->answers->where('is_correct', true)->count());
    }

    public function test_question_requires_two_answers_and_valid_correct(): void
    {
        $inst = $this->giangVien();
        $quiz = $this->taoBaiKiemTra($this->taoKhoaHoc($inst));

        $this->actingAs($inst)->post(route('instructor.questions.store', $quiz), [
            'question_text' => 'Q', 'answers' => [['text' => 'chỉ một'], ['text' => '']], 'correct' => 0,
        ])->assertSessionHasErrors('answers');

        // Đáp án đúng trỏ vào ô trống
        $this->actingAs($inst)->post(route('instructor.questions.store', $quiz), [
            'question_text' => 'Q', 'answers' => [['text' => 'A'], ['text' => 'B'], ['text' => '']], 'correct' => 2,
        ])->assertSessionHasErrors('correct');
    }

    public function test_update_question_keeps_existing_answer_ids(): void
    {
        $inst = $this->giangVien();
        $quiz = $this->taoBaiKiemTra($this->taoKhoaHoc($inst));
        $q = $quiz->questions->first();
        $ids = $q->answers->pluck('id')->all();

        $this->actingAs($inst)->put(route('instructor.questions.update', $q), [
            'question_text' => 'Đã sửa',
            'answers' => [
                ['id' => $ids[0], 'text' => 'A mới'],
                ['id' => $ids[1], 'text' => 'B mới'],
                ['id' => $ids[2], 'text' => ''], // xóa
            ],
            'correct' => 1,
        ])->assertRedirect();

        $q->refresh()->load('answers');
        $this->assertSame('Đã sửa', $q->question_text);
        $this->assertSame([$ids[0], $ids[1]], $q->answers->pluck('id')->all());
        $this->assertSame($ids[1], $q->answers->firstWhere('is_correct', true)->id);
    }

    public function test_cannot_hijack_answer_of_another_question_via_update(): void
    {
        $inst = $this->giangVien();
        $quiz = $this->taoBaiKiemTra($this->taoKhoaHoc($inst));
        [$q1, $q2] = [$quiz->questions[0], $quiz->questions[1]];
        $foreign = $q2->answers->first();

        $this->actingAs($inst)->put(route('instructor.questions.update', $q1), [
            'question_text' => 'Q1',
            'answers' => [['id' => $foreign->id, 'text' => 'chiếm đoạt'], ['text' => 'B']],
            'correct' => 0,
        ])->assertRedirect();

        $this->assertSame('Đúng', $foreign->fresh()->answer_text);
        $this->assertSame($q2->id, $foreign->fresh()->question_id);
    }

    public function test_other_instructor_cannot_edit_quiz(): void
    {
        $owner = $this->giangVien();
        $quiz = $this->taoBaiKiemTra($this->taoKhoaHoc($owner));
        $q = $quiz->questions->first();
        $intruder = $this->giangVien();

        $this->actingAs($intruder)->get(route('instructor.quizzes.edit', $quiz))->assertForbidden();
        $this->actingAs($intruder)->put(route('instructor.quizzes.update', $quiz), ['title' => 'x', 'duration_minutes' => 1, 'pass_score' => 1])->assertForbidden();
        $this->actingAs($intruder)->delete(route('instructor.quizzes.destroy', $quiz))->assertForbidden();
        $this->actingAs($intruder)->delete(route('instructor.questions.destroy', $q))->assertForbidden();
        $this->actingAs($intruder)->get(route('instructor.questions.edit', $q))->assertForbidden();
    }

    public function test_quiz_validation_ranges(): void
    {
        $inst = $this->giangVien();
        $course = $this->taoKhoaHoc($inst);

        $this->actingAs($inst)->post(route('instructor.quizzes.store', $course), [
            'title' => 'x', 'duration_minutes' => 0, 'pass_score' => 150,
        ])->assertSessionHasErrors(['duration_minutes', 'pass_score']);
    }
}
