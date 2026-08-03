<?php

namespace App\Services;

use App\Models\Agency;
use App\Support\PrivateStorage;
use Illuminate\Http\UploadedFile;

class AgencySoilDocumentService
{
    public function store(Agency $agency, UploadedFile $file): void
    {
        $this->deleteFile($agency);

        $path = PrivateStorage::store($file, 'agency-soil-docs');

        $agency->update([
            'soil_document_path' => $path,
            'soil_document_name' => $file->getClientOriginalName(),
            'soil_document_mime' => $file->getClientMimeType(),
        ]);
    }

    public function deleteFile(Agency $agency): void
    {
        PrivateStorage::delete($agency->soil_document_path);
    }

    public function delete(Agency $agency): void
    {
        $this->deleteFile($agency);

        $agency->update([
            'soil_document_path' => null,
            'soil_document_name' => null,
            'soil_document_mime' => null,
        ]);
    }
}
