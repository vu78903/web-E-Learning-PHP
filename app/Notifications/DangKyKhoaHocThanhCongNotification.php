<?php

namespace App\Notifications;

use App\Models\KhoaHoc;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** File: DangKyKhoaHocThanhCongNotification.php - Email xác nhận đăng ký khóa học. */
class DangKyKhoaHocThanhCongNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly KhoaHoc $course) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Đăng ký khóa học thành công')
            ->greeting('Xin chào '.$notifiable->name.'!')
            ->line('Bạn đã đăng ký thành công khóa học: '.$this->course->title.'.')
            ->line('Giảng viên: '.$this->course->instructor->name.'.')
            ->action('Bắt đầu học', route('learn.show', $this->course))
            ->line('Chúc bạn học tập hiệu quả!');
    }
}
