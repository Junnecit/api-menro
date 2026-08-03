<?php

namespace App\Http\Controllers;

use App\Models\TreePhoto;
use App\Models\User;
use App\Support\PrivateStorage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Temporary signed media URLs for display in <img> tags (no Bearer header).
 */
class MediaController extends Controller
{
    public function profilePhoto(Request $request, User $user): StreamedResponse
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless(PrivateStorage::exists($user->profile_photo_path), 404);

        $mime = 'image/jpeg';
        $ext = strtolower(pathinfo($user->profile_photo_path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };

        return response()->stream(function () use ($user) {
            echo PrivateStorage::get($user->profile_photo_path);
        }, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    public function treePhoto(Request $request, TreePhoto $photo): StreamedResponse
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless(PrivateStorage::exists($photo->path), 404);

        $ext = strtolower(pathinfo($photo->path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };

        return response()->stream(function () use ($photo) {
            echo PrivateStorage::get($photo->path);
        }, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
