<?php

namespace App\Services;

use App\Models\Request as PlantingRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PlantingRequestDocumentService
{
    public function store(PlantingRequest $plantingRequest, UploadedFile $file): void
    {
        $this->deleteFile($plantingRequest);

        $path = $file->store('planting-request-docs', 'public');

        $plantingRequest->update([
            'document_path' => $path,
            'document_name' => $file->getClientOriginalName(),
            'document_mime' => $file->getClientMimeType(),
        ]);
    }

    public function deleteFile(PlantingRequest $plantingRequest): void
    {
        if ($plantingRequest->document_path) {
            Storage::disk('public')->delete($plantingRequest->document_path);
        }
    }

    public function delete(PlantingRequest $plantingRequest): void
    {
        $this->deleteFile($plantingRequest);

        $plantingRequest->update([
            'document_path' => null,
            'document_name' => null,
            'document_mime' => null,
        ]);
    }
}
