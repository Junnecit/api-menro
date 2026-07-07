<?php

namespace App\Notifications;

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
        return (new MailMessage)
            ->subject('Your account has been approved')
            ->greeting("Hi {$notifiable->name},")
            ->line('Your MENRO Tree Planting Monitoring account has been approved.')
            ->line('You can now log in to the app using your email and password.')
            ->action('Log In', env('FRONTEND_URL', 'http://localhost:5173'))
            ->salutation('— MENRO Tagoloan Tree Planting Monitoring Team');
    }
}
