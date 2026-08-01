<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/**
 * Analyze planting-request uploads (documents + photos) into labeled components.
 */
class PlantingRequestDocumentAnalyzer
{
    public function __construct(
        private PlantingRequestSeedlingExtractor $extractor,
        private PlantingRequestImageQualityService $imageQuality,
        private PlantingRequestOcrSpaceService $ocrSpace,
    ) {}

    /**
     * @return array{
     *   components: list<array{key: string, label: string, found: bool, value: mixed}>,
     *   meta: array<string, mixed>,
     *   complete: bool,
     *   barangay_code: string|null,
     *   quality: array{accepted: bool, score: float|null, reasons: list<string>}|null,
     *   source: string
     * }
     */
    public function analyze(UploadedFile $file): array
    {
        if ($this->isImage($file)) {
            return $this->analyzeImage($file);
        }

        $result = $this->extractor->analyzeFile($file);
        if ($result === null) {
            throw ValidationException::withMessages([
                'document' => ['Could not read text from this document. Use the official DOCX template or a clear PDF.'],
            ]);
        }

        return $this->withBarangay($result + [
            'quality' => null,
            'source' => 'document',
        ]);
    }

    /**
     * Same as analyze(), but for store: images that fail quality/OCR hard-fail.
     *
     * @return array{meta: array<string, mixed>}
     */
    public function extractForStore(UploadedFile $file): array
    {
        if ($this->isImage($file)) {
            $analysis = $this->analyzeImage($file);

            return [
                'meta' => $analysis['meta'],
            ];
        }

        $extracted = $this->extractor->extract($file);

        return [
            'meta' => $extracted['meta'] ?? [],
        ];
    }

    public function isImage(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: '');
        $mime = strtolower((string) $file->getClientMimeType());

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)
            || str_starts_with($mime, 'image/');
    }

    /**
     * @return array{
     *   components: list<array{key: string, label: string, found: bool, value: mixed}>,
     *   meta: array<string, mixed>,
     *   complete: bool,
     *   barangay_code: string|null,
     *   quality: array{accepted: bool, score: float|null, reasons: list<string>},
     *   source: string
     * }
     */
    private function analyzeImage(UploadedFile $file): array
    {
        $quality = $this->imageQuality->assess($file);
        if (! $quality['accepted']) {
            throw ValidationException::withMessages([
                'document' => $quality['reasons'] !== []
                    ? $quality['reasons']
                    : ['Photo was rejected for quality reasons.'],
            ]);
        }

        try {
            $text = $this->ocrSpace->extractText($file);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'document' => [$e->getMessage()],
            ]);
        }

        if (trim($text) === '' || mb_strlen(trim($text)) < 20) {
            throw ValidationException::withMessages([
                'document' => ['Photo is not readable — ensure the form text is clear and well-lit, then retake.'],
            ]);
        }

        $result = $this->extractor->analyzeText($text);
        $foundCount = count(array_filter($result['components'], fn (array $c) => $c['found']));

        if ($foundCount === 0) {
            throw ValidationException::withMessages([
                'document' => ['Photo is not readable as the planting request template — no labeled fields were found. Retake a clear photo of the filled form.'],
            ]);
        }

        return $this->withBarangay($result + [
            'quality' => $quality,
            'source' => 'photo',
        ]);
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function withBarangay(array $result): array
    {
        $barangayName = $result['meta']['barangay'] ?? null;
        $result['barangay_code'] = is_string($barangayName) && $barangayName !== ''
            ? $this->resolveBarangayCode($barangayName)
            : null;

        return $result;
    }

    private function resolveBarangayCode(string $name): ?string
    {
        $needle = mb_strtolower(trim($name));
        if ($needle === '') {
            return null;
        }

        $needle = preg_replace('/\s*,\s*tagoloan.*$/i', '', $needle) ?? $needle;
        $needle = preg_replace('/[^a-z0-9\s]/u', ' ', $needle) ?? $needle;
        $needle = preg_replace('/\s+/', ' ', trim($needle)) ?? $needle;
        if ($needle === '') {
            return null;
        }

        foreach (config('tagoloan.barangays', []) as $code => $barangayName) {
            $candidate = mb_strtolower((string) $barangayName);
            if ($candidate === $needle) {
                return (string) $code;
            }
        }

        foreach (config('tagoloan.barangays', []) as $code => $barangayName) {
            $candidate = mb_strtolower((string) $barangayName);
            if (str_contains($candidate, $needle) || str_contains($needle, $candidate)) {
                return (string) $code;
            }
        }

        return null;
    }
}
