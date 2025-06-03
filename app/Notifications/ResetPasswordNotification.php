<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    protected string $resetToken;
    protected string $resetUrl;
    protected string $userName;

    public function __construct(string $resetToken, string $userName)
    {
        $this->resetToken = $resetToken;
        $this->userName = $userName;
        // Tạo URL với UUID cho frontend React
        $this->resetUrl = config('app.frontend_url', 'https://localhost:3000') . '/store/' . $this->resetToken . '/reset-password';
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🔐 Đặt lại mật khẩu - Pharmacity Store')
            ->view('emails.reset-password', [
                'userName' => $this->userName,
                'resetUrl' => $this->resetUrl,
                'resetToken' => $this->resetToken,
            ]);
    }
}
