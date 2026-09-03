<?php

namespace App\Notifications;

use App\Mail\EmbedsMenroLogo;
use App\Models\Role;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRoleUpdated extends Notification
{
    public function __construct(
        public Role $newRole,
        public string $oldRoleName,
        public ?User $actor = null,
    ) {}

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
                ->subject('Your MENRO Account Role Has Been Updated')
                ->markdown('emails.user-role-updated', [
                    'userName' => $userName,
                    'newRoleName' => $this->newRole->name,
                    'oldRoleName' => $this->oldRoleName,
                    'newRoleSlug' => $this->newRole->slug,
                    'actorName' => $this->actor?->name ?? 'Administrator',
                    'frontendUrl' => $frontendUrl,
                    'supportEmail' => $supportEmail,
                ])
        );
    }
}
