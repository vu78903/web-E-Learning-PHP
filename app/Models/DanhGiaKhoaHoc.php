<?php

namespace App\Models;

use App\Models\KhoaHoc;
use App\Models\NguoiDung;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** File: DanhGiaKhoaHoc.php - Model một đánh giá khóa học của học viên. */
class DanhGiaKhoaHoc extends Model
{
    protected $table = 'course_reviews';

    protected $fillable = ['user_id', 'course_id', 'so_sao', 'binh_luan'];

    protected function casts(): array
    {
        return ['so_sao' => 'integer'];
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
