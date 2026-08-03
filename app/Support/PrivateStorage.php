<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Central private-disk helpers. Sensitive uploads live on the `local` disk
 * (storage/app/private), never on the world-readable public disk.
 */
class PrivateStorage
{
    public const DISK = 'local';

    public static function store($file, string $directory): string
    {
        return $file->store($directory, self::DISK);
    }

    public static function put(string $path, string $contents): bool
    {
        return Storage::disk(self::DISK)->put($path, $contents);
    }

    public static function delete(?string $path): void
    {
        if ($path && Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    public static function get(?string $path): ?string
    {
        if (! $path || ! Storage::disk(self::DISK)->exists($path)) {
            return null;
        }

        return Storage::disk(self::DISK)->get($path);
    }

    public static function exists(?string $path): bool
    {
        return $path && Storage::disk(self::DISK)->exists($path);
    }

    public static function streamDownload(?string $path, string $filename, ?string $mime = null): StreamedResponse
    {
        abort_unless(self::exists($path), 404);

        return response()->streamDownload(function () use ($path) {
            echo self::get($path);
        }, $filename, [
            'Content-Type' => $mime ?: 'application/octet-stream',
        ]);
    }

    public static function absolutePath(string $path): string
    {
        return Storage::disk(self::DISK)->path($path);
    }
}
