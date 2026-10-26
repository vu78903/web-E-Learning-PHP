<?php

namespace App\Models;

use App\Models\KhoaHoc;
use App\Models\NguoiDung;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** File: ChungChi.php - Chứng chỉ hoàn thành khóa học của một học viên. */
class ChungChi extends Model
{
    protected $table = 'certificates';

    protected $fillable = ['user_id', 'course_id', 'ma_chung_chi', 'cap_luc'];

    protected function casts(): array
    {
        return ['cap_luc' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'course_id');
    }

    /** Sinh mã dạng ELN-2026-AB12CD34, không trùng với mã đã có. */
    public static function taoMaMoi(): string
    {
        do {
            $ma = 'ELN-'.now()->format('Y').'-'.strtoupper(Str::random(8));
        } while (static::where('ma_chung_chi', $ma)->exists());

        return $ma;
    }
}
