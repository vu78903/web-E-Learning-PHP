<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DapAn extends Model
{
    protected $table = 'answers';

    protected $fillable = ['question_id', 'answer_text', 'is_correct'];

    protected function casts(): array
    {
        return ['is_correct' => 'boolean'];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(CauHoi::class, 'question_id');
    }
}
