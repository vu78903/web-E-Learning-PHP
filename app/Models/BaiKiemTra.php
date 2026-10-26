<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaiKiemTra extends Model
{
    protected $table = 'quizzes';

    protected $fillable = ['course_id', 'title', 'duration_minutes', 'pass_score', 'shuffle_questions', 'max_attempts'];

    protected function casts(): array
    {
        return ['shuffle_questions' => 'boolean', 'max_attempts' => 'integer'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'course_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(CauHoi::class, 'quiz_id')->orderBy('id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(LuotLamBai::class, 'quiz_id');
    }

    public function coGioiHanLuotLam(): bool
    {
        return $this->max_attempts !== null && $this->max_attempts > 0;
    }

    /** Số lượt còn lại của học viên; null = không giới hạn. */
    public function soLuotConLaiCua(NguoiDung $user): ?int
    {
        if (! $this->coGioiHanLuotLam()) {
            return null;
        }

        return max(0, $this->max_attempts - $this->attempts()->where('user_id', $user->id)->count());
    }
}
