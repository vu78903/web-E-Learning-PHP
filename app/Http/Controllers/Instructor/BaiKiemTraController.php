<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\KhoaHoc;
use App\Models\CauHoi;
use App\Models\BaiKiemTra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Quản lý bài kiểm tra trắc nghiệm (một hoặc nhiều đáp án đúng), câu hỏi và phương án. */
class BaiKiemTraController extends Controller
{
    public function danhSach(KhoaHoc $course): View
    {
        Gate::authorize('quanLy', $course);

        return view('instructor.quizzes.index', [
            'course' => $course,
            'quizzes' => $course->quizzes()->withCount(['questions', 'attempts'])->get(),
        ]);
    }

    public function luu(Request $request, KhoaHoc $course): RedirectResponse
    {
        Gate::authorize('quanLy', $course);

        $quiz = $course->quizzes()->create($this->duLieuBaiKiemTraHopLe($request));

        return redirect()->route('instructor.quizzes.edit', $quiz)->with('success', 'Đã tạo bài kiểm tra. Hãy thêm câu hỏi.');
    }

    public function chinhSua(BaiKiemTra $quiz): View
    {
        Gate::authorize('quanLy', $quiz->course);

        return view('instructor.quizzes.edit', [
            'quiz' => $quiz->load('questions.answers'),
            'course' => $quiz->course,
        ]);
    }

    public function capNhat(Request $request, BaiKiemTra $quiz): RedirectResponse
    {
        Gate::authorize('quanLy', $quiz->course);
        $quiz->update($this->duLieuBaiKiemTraHopLe($request));

        return back()->with('success', 'Đã cập nhật bài kiểm tra.');
    }

    public function xoa(BaiKiemTra $quiz): RedirectResponse
    {
        Gate::authorize('quanLy', $quiz->course);
        $course = $quiz->course;
        $quiz->delete();

        return redirect()->route('instructor.quizzes.index', $course)->with('success', 'Đã xóa bài kiểm tra.');
    }

    public function ketQua(BaiKiemTra $quiz): View
    {
        Gate::authorize('quanLy', $quiz->course);

        $attempts = $quiz->attempts()->whereNotNull('submitted_at')->with('user')->latest('submitted_at')->get();

        return view('instructor.quizzes.results', [
            'quiz' => $quiz,
            'course' => $quiz->course,
            'attempts' => $attempts,
            'avg' => $attempts->avg('score'),
            'passRate' => $attempts->count() ? round($attempts->where('passed', true)->count() / $attempts->count() * 100) : null,
        ]);
    }

    // ---------- Câu hỏi ----------

    public function luuCauHoi(Request $request, BaiKiemTra $quiz): RedirectResponse
    {
        Gate::authorize('quanLy', $quiz->course);
        [$text, $type, $answers, $correct] = $this->duLieuCauHoiHopLe($request);

        DB::transaction(function () use ($quiz, $text, $type, $answers, $correct) {
            $question = $quiz->questions()->create(['question_text' => $text, 'type' => $type]);
            foreach ($answers as $i => $a) {
                $question->answers()->create(['answer_text' => $a['text'], 'is_correct' => in_array($i, $correct, true)]);
            }
        });

        return back()->with('success', 'Đã thêm câu hỏi.');
    }

    public function chinhSuaCauHoi(CauHoi $question): View
    {
        $quiz = $question->quiz;
        Gate::authorize('quanLy', $quiz->course);

        return view('instructor.quizzes.question-form', [
            'question' => $question->load('answers'),
            'quiz' => $quiz,
            'course' => $quiz->course,
        ]);
    }

    public function capNhatCauHoi(Request $request, CauHoi $question): RedirectResponse
    {
        $quiz = $question->quiz;
        Gate::authorize('quanLy', $quiz->course);
        [$text, $type, $answers, $correct] = $this->duLieuCauHoiHopLe($request);

        DB::transaction(function () use ($question, $text, $type, $answers, $correct) {
            $question->update(['question_text' => $text, 'type' => $type]);

            $keep = [];
            foreach ($answers as $i => $a) {
                // Chỉ cập nhật phương án thuộc đúng câu hỏi này; id lạ được xem như phương án mới.
                $existing = ! empty($a['id']) ? $question->answers()->whereKey($a['id'])->first() : null;
                $payload = ['answer_text' => $a['text'], 'is_correct' => in_array($i, $correct, true)];
                $row = $existing ?? $question->answers()->make();
                $row->fill($payload)->save();
                $keep[] = $row->id;
            }
            $question->answers()->whereNotIn('id', $keep)->delete();
        });

        return redirect()->route('instructor.quizzes.edit', $quiz)->with('success', 'Đã cập nhật câu hỏi.');
    }

    public function xoaCauHoi(CauHoi $question): RedirectResponse
    {
        $quiz = $question->quiz;
        Gate::authorize('quanLy', $quiz->course);
        $question->delete();

        return back()->with('success', 'Đã xóa câu hỏi.');
    }

    private function duLieuBaiKiemTraHopLe(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:300'],
            'pass_score' => ['required', 'integer', 'min:0', 'max:100'],
            'max_attempts' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $data['shuffle_questions'] = $request->boolean('shuffle_questions');
        $data['max_attempts'] = ((int) ($data['max_attempts'] ?? 0)) ?: null; // trống/0 = không giới hạn

        return $data;
    }

    /**
     * @return array{0: string, 1: string, 2: list<array{id?: mixed, text: string}>, 3: list<int>}
     */
    private function duLieuCauHoiHopLe(Request $request): array
    {
        $data = $request->validate([
            'question_text' => ['required', 'string', 'max:2000'],
            'answers' => ['required', 'array', 'min:2', 'max:6'],
            'answers.*.id' => ['nullable', 'integer'],
            'answers.*.text' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in([CauHoi::SINGLE, CauHoi::MULTIPLE])],
            'correct' => ['nullable'], // vị trí phương án đúng: một số hoặc mảng số
            'correct.*' => ['integer'],
        ]);

        $type = $data['type'] ?? CauHoi::SINGLE;
        $picked = array_map('intval', (array) ($data['correct'] ?? []));

        // Bỏ phương án trống nhưng giữ vị trí để xác định đáp án đúng.
        $answers = [];
        $correct = [];
        $pickedBlank = false;
        foreach (array_values($data['answers']) as $i => $a) {
            $text = trim((string) ($a['text'] ?? ''));
            if ($text === '') {
                $pickedBlank = $pickedBlank || in_array($i, $picked, true);
                continue;
            }
            if (in_array($i, $picked, true)) {
                $correct[] = count($answers);
            }
            $answers[] = ['id' => $a['id'] ?? null, 'text' => $text];
        }

        if (count($answers) < 2) {
            throw ValidationException::withMessages(['answers' => 'Cần ít nhất 2 phương án trả lời không để trống.']);
        }
        if ($pickedBlank) {
            throw ValidationException::withMessages(['correct' => 'Phương án được đánh dấu đúng không được để trống.']);
        }
        if ($type === CauHoi::SINGLE && count($correct) !== 1) {
            throw ValidationException::withMessages(['correct' => 'Câu một đáp án phải có đúng 1 phương án đúng.']);
        }
        if ($type === CauHoi::MULTIPLE && count($correct) < 1) {
            throw ValidationException::withMessages(['correct' => 'Câu nhiều đáp án phải có ít nhất 1 phương án đúng.']);
        }

        return [$data['question_text'], $type, $answers, $correct];
    }
}
