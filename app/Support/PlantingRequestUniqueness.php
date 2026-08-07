<?php

namespace App\Support;

use App\Models\Request as PlantingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

class PlantingRequestUniqueness
{
    public static function hashFile(UploadedFile $file): ?string
    {
        $path = $file->getRealPath();
        if ($path === false || $path === '') {
            return null;
        }

        $hash = hash_file('sha256', $path);

        return is_string($hash) ? $hash : null;
    }

    public static function projectNameTaken(string $projectName, ?int $ignoreId = null): bool
    {
        $normalized = mb_strtolower(trim($projectName));
        if ($normalized === '') {
            return false;
        }

        $query = PlantingRequest::query()
            ->whereRaw('LOWER(TRIM(project_name)) = ?', [$normalized]);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public static function documentHashTaken(string $hash, ?int $ignoreId = null): bool
    {
        if ($hash === '') {
            return false;
        }

        $query = PlantingRequest::query()->where('document_hash', $hash);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public static function validateProjectName(Validator $validator, ?int $ignoreId = null): void
    {
        if ($validator->errors()->has('project_name')) {
            return;
        }

        $projectName = $validator->getData()['project_name'] ?? null;
        if (! is_string($projectName) || trim($projectName) === '') {
            return;
        }

        if (self::projectNameTaken($projectName, $ignoreId)) {
            $validator->errors()->add(
                'project_name',
                'A planting request with this project name already exists.'
            );
        }
    }

    public static function validateDocument(Validator $validator, string $field = 'document', ?int $ignoreId = null): void
    {
        if ($validator->errors()->has($field)) {
            return;
        }

        $file = $validator->getData()[$field] ?? null;
        if (! $file instanceof UploadedFile) {
            return;
        }

        $hash = self::hashFile($file);
        if ($hash === null) {
            return;
        }

        if (self::documentHashTaken($hash, $ignoreId)) {
            $validator->errors()->add(
                $field,
                'This document was already uploaded for another planting request.'
            );
        }
    }
}
