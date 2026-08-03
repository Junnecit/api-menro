<?php

namespace App\Services;

use App\Models\User;
use App\Support\PrivateStorage;
use Illuminate\Http\UploadedFile;

class ProfilePhotoService
{
    public function upload(User $user, UploadedFile $file): string
    {
        $this->delete($user);

        $path = PrivateStorage::store($file, 'profile-photos');
        $user->update(['profile_photo_path' => $path]);

        return $path;
    }

    public function delete(User $user): void
    {
        if ($user->profile_photo_path) {
            PrivateStorage::delete($user->profile_photo_path);
            $user->update(['profile_photo_path' => null]);
        }
    }
}
