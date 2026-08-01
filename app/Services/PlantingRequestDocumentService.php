<?php

namespace App\Services;

use App\Models\Request as PlantingRequest;
use App\Support\TagoloanLocation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PlantingRequestDocumentService
{
    public function __construct(
        private PlantingRequestDocumentAnalyzer $documentAnalyzer,
    ) {}

    public function store(PlantingRequest $plantingRequest, UploadedFile $file): void
    {
        // Analyze before storing so rejected photos never leave an orphan file.
        $extracted = $this->documentAnalyzer->extractForStore($file);

        $this->deleteFile($plantingRequest);

        $path = $file->store('planting-request-docs', 'public');

        $updates = [
            'document_path' => $path,
            'document_name' => $file->getClientOriginalName(),
            'document_mime' => $file->getClientMimeType(),
            'seedling_draft' => null,
        ];

        $metaBackfill = $this->metaBackfill($plantingRequest, $extracted['meta'] ?? []);
        if ($metaBackfill !== []) {
            $updates = array_merge($updates, $metaBackfill);
        }

        $plantingRequest->update($updates);
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
            'seedling_draft' => null,
        ]);
    }

    /**
     * Apply extracted header fields only when the request column is empty.
     * Never writes status. Municipality is ignored for DB writes.
     *
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function metaBackfill(PlantingRequest $plantingRequest, array $meta): array
    {
        if ($meta === []) {
            return [];
        }

        $updates = [];

        if ($this->isEmpty($plantingRequest->project_name) && ! empty($meta['project_name'])) {
            $updates['project_name'] = $meta['project_name'];
        }

        if ($this->isEmpty($plantingRequest->target_trees) && array_key_exists('target_trees', $meta)) {
            $updates['target_trees'] = $meta['target_trees'];
        }

        if ($this->isEmpty($plantingRequest->barangay_code) && ! empty($meta['barangay'])) {
            $code = $this->resolveBarangayCode((string) $meta['barangay']);
            if ($code !== null) {
                $updates['barangay_code'] = $code;
                $updates['location'] = TagoloanLocation::formatLocation($code);
            }
        }

        return $updates;
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '';
    }

    private function resolveBarangayCode(string $name): ?string
    {
        $needle = mb_strtolower(trim($name));
        if ($needle === '') {
            return null;
        }

        // Strip trailing location suffix if the user pasted a full address line.
        $needle = preg_replace('/\s*,\s*tagoloan.*$/i', '', $needle) ?? $needle;

        foreach (config('tagoloan.barangays', []) as $code => $barangayName) {
            if (mb_strtolower((string) $barangayName) === $needle) {
                return (string) $code;
            }
        }

        return null;
    }
}
