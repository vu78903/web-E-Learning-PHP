<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DangKyKhoaHoc extends Model
{
    protected $table = 'enrollments';

    protected $fillable = ['user_id', 'course_id', 'enrolled_at', 'status'];

    protected function casts(): array
    {
        return ['enrolled_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'course_id');
    }
}
