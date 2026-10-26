<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/** File: DatLaiMatKhauNotification.php - Nội dung email quên mật khẩu bằng tiếng Việt. */
class DatLaiMatKhauNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Đặt lại mật khẩu '.config('app.name'))
            ->greeting('Xin chào '.$notifiable->name.'!')
            ->line('Hệ thống nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.')
            ->action('Đặt lại mật khẩu', $this->resetUrl($notifiable))
            ->line('Liên kết hết hạn sau '.config('auth.passwords.users.expire').' phút.')
            ->line('Nếu bạn không gửi yêu cầu này, hãy bỏ qua email.');
    }
}
