<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationNotification extends Notification
{
    use Queueable;

    protected $verificationCode;

    public function __construct($verificationCode)
    {
        $this->verificationCode = $verificationCode;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Xác minh tài khoản Pharmacity - Mã OTP')
            ->view('emails.email-verification', [
                'user' => $notifiable,
                'verificationCode' => $this->verificationCode
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'verification_code' => $this->verificationCode,
            'user_id' => $notifiable->id,
            'email' => $notifiable->email,
        ];
    }
}
