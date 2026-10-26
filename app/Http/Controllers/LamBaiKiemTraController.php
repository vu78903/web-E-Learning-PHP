<?php

namespace App\Http\Controllers;

use App\Models\BaiKiemTra;
use App\Models\LuotLamBai;
use App\ChucNang\GuiEmailHocTap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Học viên làm bài, hệ thống tự chấm điểm và lưu lịch sử. */
class LamBaiKiemTraController extends Controller
{
    /** Thời gian cấp thêm (giây) cho độ trễ mạng khi nộp bài. */
    private const GRACE_SECONDS = 60;

    public function chiTiet(Request $request, BaiKiemTra $quiz): View
    {
        Gate::authorize('truyCap', $quiz->course);

        return view('quizzes.show', [
            'quiz' => $quiz->loadCount('questions'),
            'course' => $quiz->course,
            'attempts' => $request->user()->quizAttempts()
                ->where('quiz_id', $quiz->id)->latest('id')->get(),
            'remaining' => $quiz->soLuotConLaiCua($request->user()),
        ]);
    }

    public function batDau(Request $request, BaiKiemTra $quiz): RedirectResponse
    {
        Gate::authorize('truyCap', $quiz->course);
        abort_unless($request->user()->laHocVien(), 403, 'Chỉ học viên mới được làm bài kiểm tra.');

        if ($quiz->questions()->count() === 0) {
            return back()->with('error', 'Bài kiểm tra chưa có câu hỏi.');
        }

        // Tiếp tục lượt làm dở còn thời gian, nếu không thì tạo lượt mới.
        $attempt = $request->user()->quizAttempts()
            ->where('quiz_id', $quiz->id)->whereNull('submitted_at')->latest('id')->first();

        if ($attempt && now()->greaterThan($attempt->hanNop()->addSeconds(self::GRACE_SECONDS))) {
            $this->chamDiem($attempt, []); // lượt cũ đã quá hạn: chốt điểm 0
            GuiEmailHocTap::guiKetQuaBaiThi($attempt);
            $attempt = null;
        }

        if (! $attempt) {
            // Giới hạn số lần làm: tính cả các lượt đã nộp và lượt quá hạn vừa chốt.
            if ($quiz->soLuotConLaiCua($request->user()) === 0) {
                return redirect()->route('quizzes.show', $quiz)
                    ->with('error', "Bạn đã dùng hết {$quiz->max_attempts} lượt làm bài kiểm tra này.");
            }

            $attempt = LuotLamBai::create([
                'quiz_id' => $quiz->id,
                'user_id' => $request->user()->id,
                'total_questions' => $quiz->questions()->count(),
                'question_order' => $quiz->shuffle_questions ? $this->taoThuTuNgauNhien($quiz) : null,
                'started_at' => now(),
            ]);
        }

        return redirect()->route('attempts.take', $attempt);
    }

    public function lamBai(Request $request, LuotLamBai $attempt): View|RedirectResponse
    {
        $this->kiemTraChuSoHuu($request, $attempt);

        if ($attempt->daNop()) {
            return redirect()->route('attempts.result', $attempt);
        }

        if (now()->greaterThan($attempt->hanNop()->addSeconds(self::GRACE_SECONDS))) {
            $this->chamDiem($attempt, []);
            GuiEmailHocTap::guiKetQuaBaiThi($attempt);

            return redirect()->route('attempts.result', $attempt)->with('error', 'Đã hết thời gian làm bài.');
        }

        // Không đưa is_correct ra giao diện làm bài.
        $quiz = $attempt->quiz->load(['questions.answers' => fn ($q) => $q->select('id', 'question_id', 'answer_text')]);

        $quiz->setRelation('questions', $attempt->sapXepCauHoi($quiz->questions));

        return view('quizzes.take', [
            'attempt' => $attempt,
            'quiz' => $quiz,
            'hanNop' => $attempt->hanNop(),
        ]);
    }

    public function nopBai(Request $request, LuotLamBai $attempt): RedirectResponse
    {
        $this->kiemTraChuSoHuu($request, $attempt);

        $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable'], // id phương án (một đáp án) hoặc mảng id (nhiều đáp án)
            'answers.*.*' => ['integer'],
        ]);

        $chosen = $request->input('answers', []);

        $moiChamDiem = false;
        $attempt = DB::transaction(function () use ($attempt, $chosen, &$moiChamDiem) {
            // Khóa dòng để tránh nộp bài hai lần đồng thời.
            $locked = LuotLamBai::whereKey($attempt->id)->lockForUpdate()->first();
            if ($locked->daNop()) {
                return $locked;
            }

            $late = now()->greaterThan($locked->hanNop()->addSeconds(self::GRACE_SECONDS));
            $moiChamDiem = true;

            return $this->chamDiem($locked, $late ? [] : $chosen);
        });

        if ($moiChamDiem) {
            GuiEmailHocTap::guiKetQuaBaiThi($attempt);
        }

        return redirect()->route('attempts.result', $attempt)->with('success', 'Đã nộp bài và chấm điểm.');
    }

    public function ketQua(Request $request, LuotLamBai $attempt): View|RedirectResponse
    {
        $user = $request->user();
        $quiz = $attempt->quiz;

        abort_unless($attempt->user_id === $user->id || $user->can('quanLy', $quiz->course), 403);

        if (! $attempt->daNop()) {
            return redirect()->route('attempts.take', $attempt);
        }

        $quiz->load('questions.answers');
        $quiz->setRelation('questions', $attempt->sapXepCauHoi($quiz->questions));

        return view('quizzes.result', [
            'attempt' => $attempt->load('user'),
            'quiz' => $quiz,
            'course' => $quiz->course,
            'picked' => $attempt->answers->mapWithKeys(fn ($row) => [$row->question_id => $row->idDapAnDaChon()]),
        ]);
    }

    private function kiemTraChuSoHuu(Request $request, LuotLamBai $attempt): void
    {
        abort_unless($attempt->user_id === $request->user()->id, 403);
    }

    /** Thứ tự xáo ngẫu nhiên cho một lượt làm, lưu lại để tải lại trang không đổi. */
    private function taoThuTuNgauNhien(BaiKiemTra $quiz): array
    {
        $questions = $quiz->questions()->with('answers:id,question_id')->get()->shuffle();

        return [
            'questions' => $questions->pluck('id')->all(),
            'answers' => $questions->mapWithKeys(fn ($q) => [$q->id => $q->answers->pluck('id')->shuffle()->all()])->all(),
        ];
    }

    /**
     * Chấm điểm: chỉ tính phương án thuộc đúng câu hỏi của đúng bài kiểm tra.
     * Câu nhiều đáp án chỉ đúng khi chọn đúng toàn bộ tập đáp án đúng, không thừa không thiếu.
     *
     * @param  array<int|string, mixed>  $chosen  [question_id => answer_id | answer_id[]]
     */
    private function chamDiem(LuotLamBai $attempt, array $chosen): LuotLamBai
    {
        $quiz = $attempt->quiz()->with('questions.answers')->first();
        $correct = 0;
        $rows = [];

        foreach ($quiz->questions as $question) {
            $raw = $chosen[$question->id] ?? null;
            $raw = $question->laNhieuDapAn() ? (array) $raw : (is_array($raw) ? null : $raw);

            // Bỏ qua phương án không thuộc câu hỏi.
            $picked = collect((array) $raw)->map(fn ($id) => (int) $id)->unique()
                ->filter(fn ($id) => $question->answers->contains('id', $id))->sort()->values();

            $expected = $question->answers->where('is_correct', true)->pluck('id')->map(fn ($id) => (int) $id)->sort()->values();

            if ($picked->isNotEmpty() && $picked->all() === $expected->all()) {
                $correct++;
            }

            $rows[] = [
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'answer_id' => $question->laNhieuDapAn() ? null : $picked->first(),
                'answer_ids' => $picked->isEmpty() ? null : json_encode($picked->all()),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $total = $quiz->questions->count();
        $score = $total > 0 ? round($correct / $total * 100, 2) : 0;

        DB::transaction(function () use ($attempt, $rows, $correct, $total, $score, $quiz) {
            $attempt->answers()->delete();
            DB::table('quiz_attempt_answers')->insert($rows);
            $attempt->update([
                'score' => $score,
                'correct_count' => $correct,
                'total_questions' => $total,
                'passed' => $score >= $quiz->pass_score,
                'submitted_at' => now(),
            ]);
        });

        return $attempt->refresh();
    }
}
