<?php

namespace App\Services;

use App\Models\ReportFile;
use App\Support\PrivateStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ReportFileService
{
    public function storeUpload(?int $folderId, UploadedFile $file, ?string $name, ?int $userId): ReportFile
    {
        $folderKey = $folderId ? (string) $folderId : 'root';
        $path = PrivateStorage::store($file, "report-files/{$folderKey}");

        return ReportFile::create([
            'folder_id' => $folderId,
            'name' => $name ?: $file->getClientOriginalName(),
            'path' => $path,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize() ?: 0,
            'source' => 'upload',
            'created_by' => $userId,
        ]);
    }

    public function storeGeneratedPdf(
        ?int $folderId,
        string $contents,
        string $name,
        ?int $userId,
        string $source = 'monitoring-pdf',
        ?string $sourceKey = null,
    ): ReportFile {
        $folderKey = $folderId ? (string) $folderId : 'root';
        $filename = Str::uuid()->toString().'.pdf';
        $path = "report-files/{$folderKey}/{$filename}";

        PrivateStorage::put($path, $contents);

        return ReportFile::create([
            'folder_id' => $folderId,
            'name' => $name,
            'path' => $path,
            'mime' => 'application/pdf',
            'size' => strlen($contents),
            'source' => $source,
            'source_key' => $sourceKey,
            'created_by' => $userId,
        ]);
    }

    public function deleteFile(ReportFile $file): void
    {
        PrivateStorage::delete($file->path);
    }
}
