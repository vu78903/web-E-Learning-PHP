<?php

namespace App\Models;

use App\ChucNang\LayThongTinYoutube;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BaiHoc extends Model
{
    protected $table = 'lessons';

    protected $fillable = [
        'section_id', 'title', 'content', 'video_url', 'video_title', 'video_thumbnail',
        'document_url', 'sort_order',
    ];

    protected static function booted(): void
    {
        // Bản ghi lesson_attachments bị xóa dây chuyền ở CSDL; ở đây dọn file trên đĩa.
        static::deleting(fn (BaiHoc $lesson) => TaiLieuBaiHoc::xoaFileCuaBaiHoc([$lesson->id]));
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(ChuongHoc::class, 'section_id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(TienDoBaiHoc::class, 'lesson_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaiLieuBaiHoc::class, 'lesson_id')->orderBy('id');
    }

    public function course(): KhoaHoc
    {
        return $this->section->course;
    }

    /**
     * Chuyển liên kết YouTube/Vimeo thành URL nhúng (embed). Trả về null nếu không nhận dạng được.
     */
    public function duongDanNhungVideo(): ?string
    {
        $url = $this->video_url;
        if (! $url) {
            return null;
        }
        $idYoutube = app(LayThongTinYoutube::class)->layIdVideo($url);
        if ($idYoutube !== null) {
            return 'https://www.youtube.com/embed/'.$idYoutube;
        }
        if (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }
}
