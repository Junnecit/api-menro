<?php

namespace App\Notifications;

use App\Mail\EmbedsMenroLogo;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserApproved extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $supportEmail = config('mail.from.address');
        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');
        $userName = $notifiable->name ?? 'User';

        return EmbedsMenroLogo::embed(
            (new MailMessage)
                ->subject('Your MENRO Account Has Been Approved')
                ->markdown('emails.user-approved', [
                    'userName' => $userName,
                    'frontendUrl' => $frontendUrl,
                    'supportEmail' => $supportEmail,
                ])
        );
    }
}
