<?php

namespace App\Notifications;

use App\Models\LuotLamBai;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** File: CoKetQuaBaiThiNotification.php - Email điểm sau khi một lượt thi được chấm. */
class CoKetQuaBaiThiNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly LuotLamBai $attempt) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $quiz = $this->attempt->quiz;
        $ketQua = $this->attempt->passed ? 'ĐẠT' : 'CHƯA ĐẠT';

        return (new MailMessage)
            ->subject('Kết quả bài kiểm tra: '.$quiz->title)
            ->greeting('Xin chào '.$notifiable->name.'!')
            ->line('Bài kiểm tra "'.$quiz->title.'" của khóa học "'.$quiz->course->title.'" đã được chấm.')
            ->line('Điểm: '.number_format((float) $this->attempt->score, 2).'/100 — Kết quả: '.$ketQua.'.')
            ->line('Số câu đúng: '.$this->attempt->correct_count.'/'.$this->attempt->total_questions.'.')
            ->action('Xem kết quả chi tiết', route('attempts.result', $this->attempt))
            ->line('Bạn có thể làm lại bài nếu muốn cải thiện kết quả.');
    }
}
