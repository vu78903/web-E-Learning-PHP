<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Tài liệu PDF đính kèm bài học. File nằm trên disk riêng tư "lesson_files"
 * (thư mục con theo lesson_id), chỉ phục vụ qua route có kiểm tra quyền.
 */
class TaiLieuBaiHoc extends Model
{
    protected $table = 'lesson_attachments';

    public const DISK = 'lesson_files';

    /** Kích thước tối đa mỗi file (KB) — dùng cho rule "max" khi validate. */
    public const MAX_KB = 10240;

    /** Số file tối đa trong một lần tải lên. */
    public const MAX_FILES = 10;

    protected $fillable = ['lesson_id', 'original_name', 'path', 'size', 'mime_type'];

    protected static function booted(): void
    {
        static::deleted(fn (TaiLieuBaiHoc $a) => Storage::disk(self::DISK)->delete($a->path));
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(BaiHoc::class, 'lesson_id');
    }

    /**
     * Xóa toàn bộ file trên đĩa của các bài học (dùng khi xóa bài học/chương/khóa học,
     * vì bản ghi bị xóa dây chuyền ở CSDL nên không phát sinh sự kiện Eloquent).
     */
    public static function xoaFileCuaBaiHoc(iterable $lessonIds): void
    {
        $disk = Storage::disk(self::DISK);
        foreach ($lessonIds as $id) {
            $disk->deleteDirectory((string) $id);
        }
    }

    public function dungLuongDeDoc(): string
    {
        $size = $this->size;
        if ($size >= 1048576) {
            return number_format($size / 1048576, 1, ',', '.').' MB';
        }

        return max(1, (int) round($size / 1024)).' KB';
    }
}
