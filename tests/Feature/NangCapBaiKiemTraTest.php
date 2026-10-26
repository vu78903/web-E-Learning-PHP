<?php

namespace Tests\Feature;

use App\Models\CauHoi;
use App\Models\BaiKiemTra;
use App\Models\LuotLamBai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\TaoDuLieu;
use Tests\TestCase;

/** Xáo trộn câu hỏi, giới hạn số lần làm, câu nhiều đáp án đúng. */
class NangCapBaiKiemTraTest extends TestCase
{
    use TaoDuLieu, RefreshDatabase;

    private function setupQuiz(array $attrs = []): array
    {
        $inst = $this->giangVien();
        $course = $this->khoaHocCoBaiHoc($inst, 1);
        $quiz = $this->taoBaiKiemTra($course);
        $quiz->update($attrs);
        $student = $this->hocVien();
        $this->dangKyKhoaHoc($student, $course);

        return [$inst, $quiz, $student];
    }

    /** Thêm câu nhiều đáp án: A, B đúng; C sai. */
    private function addMultiple(BaiKiemTra $quiz): CauHoi
    {
        $q = $quiz->questions()->create(['question_text' => 'Nhiều?', 'type' => CauHoi::MULTIPLE]);
        foreach (['A' => true, 'B' => true, 'C' => false] as $text => $ok) {
            $q->answers()->create(['answer_text' => $text, 'is_correct' => $ok]);
        }

        return $q->load('answers');
    }

    public function test_old_questions_default_to_single_choice(): void
    {
        [, $quiz] = $this->setupQuiz();
        $this->assertSame(CauHoi::SINGLE, $quiz->questions->first()->fresh()->type);
    }

    public function test_multiple_choice_requires_exact_set(): void
    {
        [, $quiz, $student] = $this->setupQuiz();
        $multi = $this->addMultiple($quiz);
        $ids = $multi->answers->pluck('id', 'answer_text');
        $singles = $quiz->questions()->where('type', CauHoi::SINGLE)->with('answers')->get()
            ->mapWithKeys(fn ($q) => [$q->id => $q->answers->firstWhere('is_correct', true)->id])->all();

        $cases = [
            [[$ids['A'], $ids['B']], 5],             // đủ, không thừa => đúng
            [[$ids['A']], 4],                        // thiếu
            [[$ids['A'], $ids['B'], $ids['C']], 4],  // thừa
            [[], 4],                                 // bỏ trống
        ];
        foreach ($cases as [$pick, $expected]) {
            $a = LuotLamBai::create(['quiz_id' => $quiz->id, 'user_id' => $student->id, 'started_at' => now()]);
            $this->actingAs($student)->post(route('attempts.submit', $a), ['answers' => $singles + [$multi->id => $pick]]);
            $this->assertSame($expected, $a->fresh()->correct_count, json_encode($pick));
        }

        $this->actingAs($student)->get(route('attempts.result', $a))->assertOk()->assertSee('Nhiều đáp án');
    }

    public function test_instructor_validation_for_question_types(): void
    {
        [$inst, $quiz] = $this->setupQuiz();
        $answers = [['text' => 'A'], ['text' => 'B'], ['text' => 'C']];

        $this->actingAs($inst)->post(route('instructor.questions.store', $quiz), [
            'question_text' => 'Q', 'type' => 'single', 'answers' => $answers, 'correct' => [0, 1],
        ])->assertSessionHasErrors('correct');

        $this->actingAs($inst)->post(route('instructor.questions.store', $quiz), [
            'question_text' => 'Q', 'type' => 'multiple', 'answers' => $answers, 'correct' => [],
        ])->assertSessionHasErrors('correct');

        $this->actingAs($inst)->post(route('instructor.questions.store', $quiz), [
            'question_text' => 'Nhiều', 'type' => 'multiple', 'answers' => $answers, 'correct' => [0, 2],
        ])->assertSessionHasNoErrors();

        $q = $quiz->questions()->where('question_text', 'Nhiều')->first();
        $this->assertSame(CauHoi::MULTIPLE, $q->type);
        $this->assertSame(['A', 'C'], $q->answers()->where('is_correct', true)->pluck('answer_text')->all());
    }

    public function test_attempt_limit_blocks_new_attempt_but_resumes_in_progress(): void
    {
        [, $quiz, $student] = $this->setupQuiz(['max_attempts' => 2]);

        $this->actingAs($student)->post(route('quizzes.start', $quiz));
        $this->actingAs($student)->post(route('quizzes.start', $quiz)); // tiếp tục lượt đang làm
        $this->assertSame(1, LuotLamBai::count());

        LuotLamBai::query()->update(['submitted_at' => now(), 'score' => 0]);
        $this->actingAs($student)->post(route('quizzes.start', $quiz));
        $this->assertSame(2, LuotLamBai::count());
        LuotLamBai::query()->update(['submitted_at' => now(), 'score' => 0]);

        $this->actingAs($student)->post(route('quizzes.start', $quiz))
            ->assertRedirect(route('quizzes.show', $quiz))->assertSessionHas('error');
        $this->assertSame(2, LuotLamBai::count());

        $this->actingAs($student)->get(route('quizzes.show', $quiz))->assertSee('Đã hết lượt làm bài');
    }

    public function test_shuffle_order_is_stored_per_attempt_and_stable(): void
    {
        [, $quiz, $student] = $this->setupQuiz(['shuffle_questions' => true]);

        $this->actingAs($student)->post(route('quizzes.start', $quiz));
        $attempt = LuotLamBai::firstOrFail();
        $order = $attempt->question_order;

        $this->assertEqualsCanonicalizing($quiz->questions->pluck('id')->all(), $order['questions']);
        $this->assertCount(4, $order['answers']);

        $first = $this->actingAs($student)->get(route('attempts.take', $attempt))->getContent();
        $second = $this->actingAs($student)->get(route('attempts.take', $attempt))->getContent();
        $strip = fn ($html) => preg_replace('/name="_token" value="[^"]*"|let remaining = [\d.]+/', '', $html);
        $this->assertSame($strip($first), $strip($second));
    }

    public function test_seeder_has_demo_quiz_for_new_features(): void
    {
        $this->seed();

        $quiz = BaiKiemTra::where('title', 'Kiểm tra Chương 2: Eloquent và Blade')->firstOrFail();
        $this->assertTrue($quiz->shuffle_questions);
        $this->assertSame(2, $quiz->max_attempts);
        $this->assertSame(2, $quiz->questions()->where('type', CauHoi::MULTIPLE)->count());
        $this->assertSame(CauHoi::SINGLE, BaiKiemTra::orderBy('id')->first()->questions()->first()->type);
    }

    public function test_no_shuffle_keeps_null_order(): void
    {
        [, $quiz, $student] = $this->setupQuiz();
        $this->actingAs($student)->post(route('quizzes.start', $quiz));
        $this->assertNull(LuotLamBai::firstOrFail()->question_order);
    }
}
