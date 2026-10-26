<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LuotLamBai extends Model
{
    protected $table = 'quiz_attempts';

    protected $fillable = [
        'quiz_id', 'user_id', 'score', 'correct_count', 'total_questions',
        'passed', 'started_at', 'submitted_at', 'question_order',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'float',
            'passed' => 'boolean',
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'question_order' => 'array',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(BaiKiemTra::class, 'quiz_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CauTraLoi::class, 'attempt_id');
    }

    public function daNop(): bool
    {
        return $this->submitted_at !== null;
    }

    /**
     * Sắp xếp câu hỏi (và phương án) theo thứ tự đã xáo của lượt làm.
     * Câu hỏi/phương án thêm sau khi bắt đầu lượt được đặt cuối.
     *
     * @param  \Illuminate\Support\Collection<int, CauHoi>  $questions
     */
    public function sapXepCauHoi($questions)
    {
        $order = $this->question_order;
        if (! $order) {
            return $questions->values();
        }

        $rank = fn (array $ids) => fn ($m) => ($pos = array_search($m->id, $ids)) === false ? PHP_INT_MAX : $pos;

        return $questions->sortBy($rank($order['questions'] ?? []))->values()->each(function (CauHoi $q) use ($order, $rank) {
            $ids = $order['answers'][$q->id] ?? null;
            if ($ids) {
                $q->setRelation('answers', $q->answers->sortBy($rank($ids))->values());
            }
        });
    }

    /** Thời điểm hết giờ làm bài. */
    public function hanNop()
    {
        return $this->started_at->copy()->addMinutes($this->quiz->duration_minutes);
    }
}
