<?php

namespace App\Support;

use App\Models\TreePhoto;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class SignedMediaUrl
{
    public static function profilePhoto(?User $user): ?string
    {
        if (! $user?->profile_photo_path) {
            return null;
        }

        return URL::temporarySignedRoute(
            'media.profile-photo',
            now()->addMinutes(60),
            ['user' => $user->id],
        );
    }

    public static function treePhoto(?TreePhoto $photo): ?string
    {
        if (! $photo?->path) {
            return null;
        }

        return URL::temporarySignedRoute(
            'media.tree-photo',
            now()->addMinutes(60),
            ['photo' => $photo->id],
        );
    }
}
