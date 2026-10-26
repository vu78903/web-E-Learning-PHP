<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CauHoi extends Model
{
    protected $table = 'questions';

    public const SINGLE = 'single';
    public const MULTIPLE = 'multiple';

    protected $fillable = ['quiz_id', 'question_text', 'type'];

    protected $attributes = ['type' => self::SINGLE];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(BaiKiemTra::class, 'quiz_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(DapAn::class, 'question_id')->orderBy('id');
    }

    public function laNhieuDapAn(): bool
    {
        return $this->type === self::MULTIPLE;
    }
}
