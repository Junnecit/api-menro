<?php

namespace App\Services;

use App\Support\SimpleZip;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use ZipArchive;

/**
 * Best-effort extraction of planting-request header fields from a labeled document.
 */
class PlantingRequestSeedlingExtractor
{
    /** @var array<string, array{label: string, pattern: string}> */
    private const META_FIELDS = [
        'project_name' => [
            'label' => 'Project Name',
            'pattern' => '/PROJECT\s*NAME\s*[:\-]?\s*([^\n]+)/i',
        ],
        'target_trees' => [
            'label' => 'Target Trees',
            'pattern' => '/TARGET\s*TREES\s*[:\-]?\s*([^\n]+)/i',
        ],
        'barangay' => [
            'label' => 'Barangay',
            'pattern' => '/BARANGAY\s*[:\-]?\s*([^\n]+)/i',
        ],
        'municipality' => [
            'label' => 'Municipality',
            'pattern' => '/MUNICIPALITY\s*[:\-]?\s*([^\n]+)/i',
        ],
    ];

    /**
     * @return array{meta: array<string, mixed>}|null
     */
    public function extract(UploadedFile $file): ?array
    {
        try {
            $text = $this->extractFileText($file);
            if ($text === null || trim($text) === '') {
                return null;
            }

            return $this->parseLabeledFields($text);
        } catch (\Throwable $e) {
            Log::warning('Planting request field extraction failed.', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Full component checklist analysis from an uploaded document (not images).
     *
     * @return array{
     *   components: list<array{key: string, label: string, found: bool, value: mixed}>,
     *   meta: array<string, mixed>,
     *   complete: bool
     * }|null
     */
    public function analyzeFile(UploadedFile $file): ?array
    {
        try {
            $text = $this->extractFileText($file);
            if ($text === null || trim($text) === '') {
                return null;
            }

            return $this->analyzeText($text);
        } catch (\Throwable $e) {
            Log::warning('Planting request document analysis failed.', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array{
     *   components: list<array{key: string, label: string, found: bool, value: mixed}>,
     *   meta: array<string, mixed>,
     *   complete: bool
     * }
     */
    public function analyzeText(string $text): array
    {
        $normalized = $this->normalizeText($text);
        $parsed = $this->parseLabeledFields($normalized) ?? [
            'meta' => [],
        ];

        $components = $this->buildComponents($parsed['meta']);
        $complete = $components !== [] && ! in_array(false, array_column($components, 'found'), true);

        return [
            'components' => $components,
            'meta' => $parsed['meta'],
            'complete' => $complete,
        ];
    }

    /**
     * @return array{meta: array<string, mixed>}|null
     */
    public function parseLabeledFields(string $text): ?array
    {
        $normalized = $this->normalizeText($text);
        $meta = $this->parseMetaFields($normalized);

        if ($meta === []) {
            return null;
        }

        return [
            'meta' => $meta,
        ];
    }

    private function normalizeText(string $text): string
    {
        return preg_replace("/[ \t]+/", ' ', str_replace(["\r\n", "\r"], "\n", $text)) ?? $text;
    }

    private function extractFileText(UploadedFile $file): ?string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: '');
        $mime = strtolower((string) $file->getClientMimeType());

        if ($extension === 'docx' || str_contains($mime, 'wordprocessingml')) {
            return $this->extractDocxText($file->getRealPath());
        }

        if ($extension === 'pdf' || $mime === 'application/pdf') {
            return $this->extractPdfText($file->getRealPath());
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return list<array{key: string, label: string, found: bool, value: mixed}>
     */
    private function buildComponents(array $meta): array
    {
        $components = [];

        foreach (self::META_FIELDS as $key => $field) {
            $value = $meta[$key] ?? null;
            $components[] = [
                'key' => $key,
                'label' => $field['label'],
                'found' => $value !== null && $value !== '',
                'value' => $value,
            ];
        }

        return $components;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseMetaFields(string $normalized): array
    {
        $meta = [];

        foreach (self::META_FIELDS as $key => $field) {
            if (! preg_match($field['pattern'], $normalized, $matches)) {
                continue;
            }

            $raw = $this->cleanRaw($matches[1] ?? '');
            if ($raw === null) {
                continue;
            }

            if ($key === 'project_name' || $key === 'barangay' || $key === 'municipality') {
                $meta[$key] = preg_replace('/\s+/', ' ', $raw);
                continue;
            }

            if ($key === 'target_trees') {
                $count = $this->parseCount($raw);
                if ($count !== null) {
                    $meta[$key] = $count;
                }
            }
        }

        return $meta;
    }

    private function cleanRaw(string $raw): ?string
    {
        $raw = trim($raw);
        $raw = trim($raw, " \t.:");
        if (
            $raw === ''
            || preg_match('/^_+$/', $raw)
            || in_array(strtolower($raw), ['-', 'n/a', 'na', 'none', 'no data'], true)
        ) {
            return null;
        }

        return $raw;
    }

    private function extractDocxText(string $path): ?string
    {
        $xml = null;

        if (class_exists(ZipArchive::class)) {
            $zip = new ZipArchive;
            if ($zip->open($path) === true) {
                $fromZip = $zip->getFromName('word/document.xml');
                $zip->close();
                $xml = $fromZip === false ? null : $fromZip;
            }
        }

        if ($xml === null || $xml === '') {
            $binary = @file_get_contents($path);
            if ($binary === false || $binary === '') {
                return null;
            }
            $xml = SimpleZip::read($binary, 'word/document.xml');
        }

        if ($xml === null || $xml === '') {
            return null;
        }

        $xml = preg_replace('/<\/w:p>/', "\n", $xml) ?? $xml;
        $xml = preg_replace('/<\/w:tr>/', "\n", $xml) ?? $xml;
        $xml = preg_replace('/<w:tab[^>]*\/>/', "\t", $xml) ?? $xml;
        $text = strip_tags($xml);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8');

        return trim($text);
    }

    private function extractPdfText(string $path): ?string
    {
        $binary = @file_get_contents($path);
        if ($binary === false || $binary === '') {
            return null;
        }

        $chunks = [];

        if (preg_match_all('/\((?:\\\\.|[^\\\\)])*\)\s*Tj/s', $binary, $matches)) {
            foreach ($matches[0] as $match) {
                if (preg_match('/^\((.*)\)\s*Tj$/s', $match, $inner)) {
                    $chunks[] = $this->unescapePdfString($inner[1]);
                }
            }
        }

        if (preg_match_all('/\[(.*?)\]\s*TJ/s', $binary, $arrayMatches)) {
            foreach ($arrayMatches[1] as $arrayBody) {
                if (preg_match_all('/\((?:\\\\.|[^\\\\)])*\)/s', $arrayBody, $parts)) {
                    foreach ($parts[0] as $part) {
                        $chunks[] = $this->unescapePdfString(substr($part, 1, -1));
                    }
                }
            }
        }

        $text = trim(implode(' ', $chunks));

        return $text !== '' ? $text : null;
    }

    private function unescapePdfString(string $value): string
    {
        $value = str_replace(['\\n', '\\r', '\\t', '\\(', '\\)', '\\\\'], ["\n", "\r", "\t", '(', ')', '\\'], $value);

        return $value;
    }

    private function parseCount(string $value): ?int
    {
        if (preg_match('/-?\d+/', str_replace([',', ' '], '', $value), $matches)) {
            return max(0, (int) $matches[0]);
        }

        return null;
    }
}
