<?php

namespace App\ChucNang;

use App\Models\BaiKiemTra;
use App\Models\KhoaHoc;
use App\Models\LuotLamBai;
use App\Models\NguoiDung;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Bảng điểm học viên trên dashboard, lọc theo khóa học hoặc theo một bài kiểm tra.
 * Mỗi (học viên, bài kiểm tra) lấy điểm cao nhất, số lượt nộp và đạt nếu có ít nhất một lượt đạt
 * — cùng quy ước với file CSV bảng điểm. Chỉ tính học viên đang đăng ký khóa học.
 * Truyền $instructorId để chỉ xem khóa học của một giảng viên; null = toàn hệ thống (admin).
 */
class BangDiemHocVien
{
    public const MOI_TRANG = 20;

    public function __construct(private ?int $instructorId = null) {}

    public function thanhMang(Request $request): array
    {
        $courses = KhoaHoc::query()
            ->when($this->instructorId, fn (Builder $q) => $q->where('instructor_id', $this->instructorId))
            ->with(['quizzes' => fn ($q) => $q->orderBy('id')])
            ->orderBy('title')->get(['id', 'title', 'instructor_id']);

        // Chọn bài kiểm tra thì khóa học suy ra từ bài đó; mặc định là khóa đầu tiên có bài kiểm tra.
        $quiz = $courses->flatMap->quizzes->firstWhere('id', $request->integer('quiz_id'));
        $course = $quiz ? $courses->firstWhere('id', $quiz->course_id)
            : ($courses->firstWhere('id', $request->integer('course_id'))
                ?? $courses->first(fn (KhoaHoc $c) => $c->quizzes->isNotEmpty()) ?? $courses->first());

        $data = ['courses' => $courses, 'course' => $course, 'quiz' => $quiz, 'students' => null, 'scores' => collect(), 'summary' => null];
        if (! $course) {
            return $data;
        }

        $quizzes = $quiz ? collect([$quiz]) : $course->quizzes;
        $scores = $this->diemCaoNhat($course, $quizzes);

        return [
            ...$data,
            'quizzes' => $quizzes,
            'students' => NguoiDung::whereHas('enrollments', fn (Builder $q) => $q->where('course_id', $course->id))
                ->orderBy('name')->orderBy('id')
                ->paginate(self::MOI_TRANG, ['id', 'name', 'email'], 'trang_diem')
                ->withQueryString()->fragment('bang-diem'),
            'scores' => $scores,
            'summary' => [
                'students' => $course->enrollments()->count(),
                'done' => $scores->pluck('user_id')->unique()->count(),
                'attempts' => (int) $scores->sum('attempts'),
                'avg' => $scores->avg('best'),
                'pass_rate' => $scores->count() ? (int) round($scores->where('passed', true)->count() / $scores->count() * 100) : null,
            ],
        ];
    }

    /**
     * Gom các lượt đã nộp theo (học viên, bài kiểm tra) bằng một truy vấn.
     *
     * @return Collection<string, array{user_id: int, quiz_id: int, best: float, attempts: int, passed: bool, last: string}> khóa "user_id-quiz_id"
     */
    private function diemCaoNhat(KhoaHoc $course, Collection $quizzes): Collection
    {
        return LuotLamBai::whereIn('quiz_id', $quizzes->pluck('id'))
            ->whereNotNull('submitted_at')->whereNotNull('score')
            ->whereIn('user_id', $course->enrollments()->select('user_id'))
            ->selectRaw('user_id, quiz_id, MAX(score) as best, COUNT(*) as attempts, MAX(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as passed, MAX(submitted_at) as last')
            ->groupBy('user_id', 'quiz_id')
            ->toBase()->get()
            ->mapWithKeys(fn ($r) => [$r->user_id.'-'.$r->quiz_id => [
                'user_id' => (int) $r->user_id,
                'quiz_id' => (int) $r->quiz_id,
                'best' => (float) $r->best,
                'attempts' => (int) $r->attempts,
                'passed' => (bool) $r->passed,
                'last' => $r->last,
            ]]);
    }

    /** Điểm trung bình (theo điểm cao nhất từng bài) của một học viên trên các bài đã làm. */
    public static function diemTrungBinh(Collection $scores, int $userId, Collection $quizzes): ?float
    {
        $cuaHocVien = $quizzes->map(fn (BaiKiemTra $q) => $scores->get($userId.'-'.$q->id))->filter();

        return $cuaHocVien->isEmpty() ? null : $cuaHocVien->avg('best');
    }
}
