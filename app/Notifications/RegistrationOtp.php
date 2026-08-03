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

        return EmbedsMenroLogo::embed(
            (new MailMessage)
                ->subject('Your verification code')
                ->greeting('Verify your email')
                ->line("Hi {$notifiable->name},")
                ->line('Use this code to verify your email for MENRO Tree Planting Monitoring:')
                ->line("**{$this->code}**")
                ->line('This code expires in 10 minutes.')
                ->line('If you did not create an account, you can ignore this email.')
                ->line('---')
                ->line("Having trouble with your account? [Contact us](mailto:{$supportEmail})")
                ->salutation("Best,\nMENRO Tagoloan team")
        );
    }
}
