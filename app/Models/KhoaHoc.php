<?php

namespace App\Models;

use App\Models\DanhGiaKhoaHoc;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Storage;

class KhoaHoc extends Model
{
    protected $table = 'courses';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_HIDDEN = 'hidden';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Bản nháp',
        self::STATUS_PUBLISHED => 'Công khai',
        self::STATUS_HIDDEN => 'Đã ẩn',
    ];

    protected $fillable = ['category_id', 'title', 'description', 'thumbnail', 'status'];

    protected static function booted(): void
    {
        // Chương/bài học bị xóa dây chuyền ở CSDL nên cần dọn file tài liệu trước.
        static::deleting(fn (KhoaHoc $course) => TaiLieuBaiHoc::xoaFileCuaBaiHoc($course->lessons()->pluck('lessons.id')));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DanhMuc::class, 'category_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'instructor_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ChuongHoc::class, 'course_id')->orderBy('sort_order')->orderBy('id');
    }

    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(BaiHoc::class, ChuongHoc::class, 'course_id', 'section_id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(BaiKiemTra::class, 'course_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(DangKyKhoaHoc::class, 'course_id');
    }

    /** Đánh giá sao và bình luận của học viên. */
    public function danhGia(): HasMany
    {
        return $this->hasMany(DanhGiaKhoaHoc::class, 'course_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(NguoiDung::class, 'enrollments', 'course_id', 'user_id')
            ->withPivot('enrolled_at', 'status')
            ->withTimestamps();
    }

    public function scopeCongKhai(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /** URL ảnh bìa (ảnh upload hoặc ảnh mặc định). */
    public function duongDanAnhBia(): ?string
    {
        return $this->thumbnail ? Storage::disk('public')->url($this->thumbnail) : null;
    }

    public function tenTrangThai(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function daCongKhai(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /** Phần trăm tiến độ (0-100) của một học viên trong khóa học này. */
    public function tienDoCua(NguoiDung $user): int
    {
        $total = $this->lessons()->count();
        if ($total === 0) {
            return 0;
        }

        $done = TienDoBaiHoc::where('user_id', $user->id)
            ->where('is_completed', true)
            ->whereIn('lesson_id', $this->lessons()->pluck('lessons.id'))
            ->count();

        return (int) round($done / $total * 100);
    }
}
