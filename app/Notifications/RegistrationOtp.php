<?php

namespace App\Notifications;

use App\Mail\EmbedsMenroLogo;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationOtp extends Notification
{
    public function __construct(private string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $supportEmail = config('mail.from.address');
        $userName = $notifiable->name ?? 'User';

        return EmbedsMenroLogo::embed(
            (new MailMessage)
                ->subject('Your MENRO Verification Code: '.$this->code)
                ->markdown('emails.registration-otp', [
                    'userName' => $userName,
                    'code' => $this->code,
                    'supportEmail' => $supportEmail,
                ])
        );
    }
}
