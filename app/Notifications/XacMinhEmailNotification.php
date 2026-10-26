<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

/** File: XacMinhEmailNotification.php - Nội dung email xác minh đăng ký bằng tiếng Việt. */
class XacMinhEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Xác minh đăng ký tài khoản '.config('app.name'))
            ->greeting('Xin chào '.$notifiable->name.'!')
            ->line('Bạn vừa đăng ký tài khoản học viên hoặc giảng viên trên '.config('app.name').'.')
            ->line('Vui lòng bấm nút bên dưới để xác minh email và hoàn tất đăng ký.')
            ->action('Xác minh email', $this->verificationUrl($notifiable))
            ->line('Liên kết có hiệu lực trong 60 phút. Nếu bạn không đăng ký tài khoản, hãy bỏ qua email này.');
    }
}
