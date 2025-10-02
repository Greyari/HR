<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url("/url-reset-password?token={$this->token}&email={$notifiable->email}");

        return (new MailMessage)
                    ->subject('Reset Password Anda')
                    ->view('emails.reset-password', [
                        'url' => $url,
                        'email' => $notifiable->email,
                    ]);
    }
}
