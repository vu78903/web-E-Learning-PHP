<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CauTraLoi extends Model
{
    protected $table = 'quiz_attempt_answers';

    protected $fillable = ['attempt_id', 'question_id', 'answer_id', 'answer_ids'];

    protected function casts(): array
    {
        return ['answer_ids' => 'array'];
    }

    /** Các phương án đã chọn (tương thích dữ liệu cũ chỉ có answer_id). */
    public function idDapAnDaChon(): array
    {
        return array_map('intval', $this->answer_ids ?? ($this->answer_id ? [$this->answer_id] : []));
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(LuotLamBai::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(CauHoi::class, 'question_id');
    }

    public function answer(): BelongsTo
    {
        return $this->belongsTo(DapAn::class, 'answer_id');
    }
}
