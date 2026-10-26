<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChuongHoc extends Model
{
    protected $table = 'sections';

    protected $fillable = ['course_id', 'title', 'sort_order'];

    protected static function booted(): void
    {
        // Bài học bị xóa dây chuyền ở CSDL nên cần dọn file tài liệu trước.
        static::deleting(fn (ChuongHoc $section) => TaiLieuBaiHoc::xoaFileCuaBaiHoc($section->lessons()->pluck('id')));
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(KhoaHoc::class, 'course_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(BaiHoc::class, 'section_id')->orderBy('sort_order')->orderBy('id');
    }
}
