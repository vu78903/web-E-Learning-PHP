<?php

namespace App\ChucNang;

use App\Models\KhoaHoc;
use App\Models\DangKyKhoaHoc;
use App\Models\LuotLamBai;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Dữ liệu 3 biểu đồ dashboard (đăng ký theo tuần, phân bố điểm, top khóa học).
 * Truyền $instructorId để chỉ tính các khóa học của một giảng viên; null = toàn hệ thống (admin).
 */
class DuLieuBieuDo
{
    /** Số tuần gần nhất hiển thị trên biểu đồ đăng ký. */
    public const SO_TUAN = 12;

    public function __construct(private ?int $instructorId = null) {}

    public function thanhMang(): array
    {
        return [
            'weekly' => $this->dangKyTheoTuan(),
            'scores' => $this->phanBoDiem(),
            'top' => $this->khoaHocNoiBat(),
        ];
    }

    /** Lượt đăng ký theo tuần (tuần bắt đầu thứ Hai), đủ WEEKS tuần kể cả tuần không có đăng ký. */
    private function dangKyTheoTuan(): array
    {
        $start = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks(self::SO_TUAN - 1);

        // Đếm theo ngày bằng SQL chuẩn (chạy được cả MySQL lẫn SQLite khi test), rồi gộp tuần ở PHP: tối đa WEEKS*7 dòng.
        $perDay = DangKyKhoaHoc::where('enrolled_at', '>=', $start)
            ->when($this->instructorId, fn (Builder $q) => $q->whereHas('course', $this->khoaHocCuaGiangVien(...)))
            ->selectRaw('DATE(enrolled_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $data = array_fill(0, self::SO_TUAN, 0);
        foreach ($perDay as $day => $total) {
            $index = intdiv((int) $start->diffInDays(Carbon::parse($day)), 7);
            if ($index >= 0 && $index < self::SO_TUAN) {
                $data[$index] += (int) $total;
            }
        }

        $labels = [];
        for ($i = 0; $i < self::SO_TUAN; $i++) {
            $week = $start->copy()->addWeeks($i);
            $labels[] = $week->format('d/m').'–'.$week->copy()->endOfWeek(Carbon::SUNDAY)->format('d/m');
        }

        return compact('labels', 'data');
    }

    /** Phân bố điểm các lượt nộp bài, quy về thang 10 (score lưu theo thang 100). */
    private function phanBoDiem(): array
    {
        $buckets = LuotLamBai::whereNotNull('submitted_at')->whereNotNull('score')
            ->when($this->instructorId, fn (Builder $q) => $q->whereHas('quiz.course', $this->khoaHocCuaGiangVien(...)))
            ->selectRaw('CASE WHEN score < 20 THEN 0 WHEN score < 40 THEN 1 WHEN score < 60 THEN 2 WHEN score < 80 THEN 3 ELSE 4 END as bucket, COUNT(*) as total')
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $labels = ['0 – <2', '2 – <4', '4 – <6', '6 – <8', '8 – 10'];
        $data = array_map(fn ($b) => (int) ($buckets[$b] ?? 0), range(0, 4));

        return compact('labels', 'data');
    }

    /** Tối đa 10 khóa học có nhiều lượt đăng ký nhất (bỏ khóa chưa có ai đăng ký). */
    private function khoaHocNoiBat(): array
    {
        $courses = KhoaHoc::with('instructor')->withCount('enrollments')
            ->when($this->instructorId, $this->khoaHocCuaGiangVien(...))
            ->whereHas('enrollments')
            ->orderByDesc('enrollments_count')->orderBy('title')->take(10)->get();

        return array_filter([
            'labels' => $courses->pluck('title'),
            // Dashboard giảng viên chỉ có khóa của chính họ nên không cần tên giảng viên trong tooltip.
            'instructors' => $this->instructorId ? null : $courses->pluck('instructor.name'),
            'data' => $courses->pluck('enrollments_count'),
        ], fn ($v) => $v !== null);
    }

    private function khoaHocCuaGiangVien(Builder $query): Builder
    {
        return $query->where('instructor_id', $this->instructorId);
    }
}
