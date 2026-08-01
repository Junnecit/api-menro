<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * OCR via OCR.space free/pro API (server-side; agency key in env).
 */
class PlantingRequestOcrSpaceService
{
    private const ENDPOINT = 'https://api.ocr.space/parse/image';

    public function extractText(UploadedFile $file): string
    {
        $apiKey = (string) config('services.ocr_space.api_key', '');
        if ($apiKey === '') {
            throw new RuntimeException(
                'Photo OCR is not configured. Set OCR_SPACE_API_KEY in the API .env file.'
            );
        }

        $binary = @file_get_contents($file->getRealPath());
        if ($binary === false || $binary === '') {
            throw new RuntimeException('Could not read the uploaded photo for OCR.');
        }

        $response = Http::timeout(90)
            ->asMultipart()
            ->attach(
                'file',
                $binary,
                $file->getClientOriginalName() ?: 'photo.jpg'
            )
            ->post(self::ENDPOINT, [
                'apikey' => $apiKey,
                'language' => 'eng',
                'isOverlayRequired' => 'false',
                'OCREngine' => '2',
                'scale' => 'true',
                'detectOrientation' => 'true',
            ]);

        if (! $response->successful()) {
            Log::warning('OCR.space HTTP failure.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException('OCR service is temporarily unavailable. Try again shortly.');
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException('OCR service returned an invalid response.');
        }

        if (! empty($payload['IsErroredOnProcessing'])) {
            $messages = $payload['ErrorMessage'] ?? 'OCR failed.';
            if (is_array($messages)) {
                $messages = implode(' ', $messages);
            }

            $lower = strtolower((string) $messages);
            if (str_contains($lower, 'api key') || str_contains($lower, 'e550')) {
                throw new RuntimeException('OCR API key is invalid. Check OCR_SPACE_API_KEY.');
            }
            if (str_contains($lower, 'limit') || str_contains($lower, 'rate')) {
                throw new RuntimeException('OCR daily limit reached. Try again tomorrow or upgrade the OCR plan.');
            }

            throw new RuntimeException('OCR failed: '.$messages);
        }

        $parts = [];
        foreach ($payload['ParsedResults'] ?? [] as $result) {
            $text = trim((string) ($result['ParsedText'] ?? ''));
            if ($text !== '') {
                $parts[] = $text;
            }
        }

        return trim(implode("\n", $parts));
    }
}
