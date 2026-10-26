<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TienDoBaiHoc extends Model
{
    protected $table = 'lesson_progress';

    protected $fillable = ['user_id', 'lesson_id', 'is_completed', 'completed_at'];

    protected function casts(): array
    {
        return ['is_completed' => 'boolean', 'completed_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(BaiHoc::class, 'lesson_id');
    }
}
