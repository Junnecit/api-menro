<?php

namespace App\Services;

use App\Support\SimpleZip;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use ZipArchive;

/**
 * Extract planting-request fields from the official template and free-form fills.
 *
 * Official template labels (in order):
 *   Project Name, Target Trees, Type of Seedling, Barangay, Municipality
 *
 * Supports:
 * - Same-line values: "Project Name: Coastal Restoration"
 * - Values written over / after underscores
 * - Values on the next line after a blank label
 * - Flattened PDF/OCR text with no newlines
 * - Common free-form label aliases
 */
class PlantingRequestSeedlingExtractor
{
    /**
     * Template + free-form field definitions, in document order.
     *
     * @var array<string, array{label: string, aliases: list<string>, required: bool}>
     */
    private const FIELDS = [
        'project_name' => [
            'label' => 'Project Name',
            'aliases' => [
                'Project Name',
                'Project',
                'Name of Project',
                'Project Title',
            ],
            'required' => true,
        ],
        'target_trees' => [
            'label' => 'Target Trees',
            'aliases' => [
                'Target Trees',
                'No(?:\.|)\s*of\s*Trees?',
                'Number of Trees',
                'Total Trees',
                'Trees to Plant',
            ],
            'required' => true,
        ],
        'species' => [
            'label' => 'Type of Seedling',
            'aliases' => [
                'Type of Seedlings?',
                'Seedling Type',
                'Type of Seedling Planted',
                'Tree Species',
                'Species of Seedlings?',
                'Seedlings?',
            ],
            'required' => false,
        ],
        'barangay' => [
            'label' => 'Barangay',
            'aliases' => [
                'Barangay',
                'Brgy\.?',
                'Baranggay',
            ],
            'required' => true,
        ],
        'municipality' => [
            'label' => 'Municipality',
            'aliases' => [
                'Municipality',
                'City\s*/\s*Municipality',
                'City or Municipality',
            ],
            'required' => true,
        ],
    ];

    /**
     * Extra stop markers that are not value fields (template boilerplate).
     *
     * @var list<string>
     */
    private const EXTRA_STOPS = [
        'Notes\s*(?:\/\s*)?Purpose',
        'Notes',
        'Purpose',
        'Fill in the blanks',
        'MENRO\s+TAGOLOAN',
    ];

    /**
     * @return array{meta: array<string, mixed>, seedling_draft: array<string, mixed>|null}|null
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
     * @return array{
     *   components: list<array{key: string, label: string, found: bool, value: mixed}>,
     *   meta: array<string, mixed>,
     *   seedling_draft: array<string, mixed>|null,
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
     *   seedling_draft: array<string, mixed>|null,
     *   complete: bool
     * }
     */
    public function analyzeText(string $text): array
    {
        $parsed = $this->parseLabeledFields($text) ?? [
            'meta' => [],
            'seedling_draft' => null,
        ];

        // Planting sites are always Tagoloan — fill municipality when the
        // document omits the line or OCR skips the pre-printed template value.
        if (empty($parsed['meta']['municipality'])) {
            $parsed['meta']['municipality'] = 'Tagoloan';
        }

        $components = $this->buildComponents($parsed['meta']);
        $requiredFound = [];
        foreach (self::FIELDS as $key => $field) {
            if (! $field['required']) {
                continue;
            }
            $value = $parsed['meta'][$key] ?? null;
            $requiredFound[] = $value !== null && $value !== '';
        }
        $complete = $requiredFound !== [] && ! in_array(false, $requiredFound, true);

        return [
            'components' => $components,
            'meta' => $parsed['meta'],
            'seedling_draft' => $parsed['seedling_draft'] ?? null,
            'complete' => $complete,
        ];
    }

    /**
     * @return array{meta: array<string, mixed>, seedling_draft: array<string, mixed>|null}|null
     */
    public function parseLabeledFields(string $text): ?array
    {
        $normalized = $this->normalizeText($text);
        $rawValues = $this->extractAllFieldValues($normalized);

        $meta = [];
        $seedlingDraft = null;

        foreach ($rawValues as $key => $raw) {
            if ($key === 'species') {
                $species = $this->splitSpeciesList($raw);
                if ($species === []) {
                    continue;
                }
                $seedlingDraft = [
                    'species' => $species,
                    'raw' => $raw,
                    'source' => 'document',
                ];
                $meta['species'] = implode(', ', $species);
                continue;
            }

            if ($key === 'target_trees') {
                $count = $this->parseCount($raw);
                if ($count !== null) {
                    $meta[$key] = $count;
                }
                continue;
            }

            if ($key === 'municipality') {
                $meta[$key] = $this->normalizeMunicipality($raw);
                continue;
            }

            $meta[$key] = preg_replace('/\s+/', ' ', $raw) ?? $raw;
        }

        // Always present for Tagoloan planting requests.
        if (empty($meta['municipality'])) {
            $meta['municipality'] = 'Tagoloan';
        }

        if (count($meta) === 1 && isset($meta['municipality']) && $seedlingDraft === null) {
            // Only the default municipality — treat as "nothing extracted".
            return null;
        }

        return [
            'meta' => $meta,
            'seedling_draft' => $seedlingDraft,
        ];
    }

    /**
     * @return array<string, string> key => cleaned raw value
     */
    private function extractAllFieldValues(string $normalized): array
    {
        // Prefer structured line parsing when the document still has lines
        // (DOCX template / clear OCR). Fall back to flattened regex for PDFs.
        $fromLines = $this->extractFromLines($normalized);
        $fromFlat = $this->extractFromFlatText($normalized);

        $merged = [];
        foreach (array_keys(self::FIELDS) as $key) {
            $value = $fromLines[$key] ?? $fromFlat[$key] ?? null;
            if ($value !== null && $value !== '') {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Walk the document line-by-line using template field order.
     *
     * @return array<string, string>
     */
    private function extractFromLines(string $normalized): array
    {
        $lines = preg_split('/\n+/', $normalized) ?: [];
        $lines = array_values(array_map(fn (string $line) => trim($line), $lines));
        $found = [];

        foreach ($lines as $index => $line) {
            if ($line === '' || $this->isBoilerplate($line)) {
                continue;
            }

            foreach (self::FIELDS as $key => $field) {
                if (isset($found[$key])) {
                    continue;
                }

                $matched = $this->matchLabelAtStart($line, $field['aliases']);
                if ($matched === null) {
                    continue;
                }

                $sameLine = $this->cleanRaw($this->truncateAtNextLabel($matched['remainder']));
                if ($sameLine !== null) {
                    $found[$key] = $sameLine;
                    break;
                }

                // Free-form / filled blank: value on the next non-empty line.
                $nextValue = $this->nextValueLine($lines, $index + 1);
                if ($nextValue !== null) {
                    $found[$key] = $nextValue;
                }
                break;
            }
        }

        return $found;
    }

    /**
     * Flattened PDF/OCR text: capture each field until the next known label.
     *
     * @return array<string, string>
     */
    private function extractFromFlatText(string $normalized): array
    {
        // Collapse newlines so one-line PDF streams still parse.
        $flat = preg_replace('/\s*\n\s*/', ' ', $normalized) ?? $normalized;
        $flat = preg_replace('/[ \t]+/', ' ', $flat) ?? $flat;
        $found = [];
        $stop = $this->stopLookahead();

        foreach (self::FIELDS as $key => $field) {
            $start = $this->aliasAlternation($field['aliases']).'\s*[:\-]?\s*';
            $pattern = '~'.$start.'(.+?)(?='.$stop.'|$)~is';

            if (! preg_match($pattern, $flat, $matches)) {
                continue;
            }

            $raw = $this->cleanRaw($matches[1] ?? '');
            if ($raw !== null) {
                $found[$key] = $raw;
            }
        }

        return $found;
    }

    /**
     * @param  list<string>  $aliases
     * @return array{remainder: string}|null
     */
    private function matchLabelAtStart(string $line, array $aliases): ?array
    {
        $pattern = '~^'.$this->aliasAlternation($aliases).'\s*[:\-]?\s*(.*)$~iu';
        if (! preg_match($pattern, $line, $matches)) {
            return null;
        }

        return ['remainder' => (string) ($matches[1] ?? '')];
    }

    /**
     * Cut a captured value before the next template/free-form label.
     * Needed when PDF/OCR flattens several fields onto one line.
     */
    private function truncateAtNextLabel(string $value): string
    {
        $stop = $this->stopLookahead();
        if (preg_match('~^(.*?)(?='.$stop.')~is', $value, $matches)) {
            return trim((string) $matches[1]);
        }

        return $value;
    }

    /**
     * @param  list<string>  $lines
     */
    private function nextValueLine(array $lines, int $from): ?string
    {
        for ($i = $from; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if ($line === '' || $this->isBoilerplate($line)) {
                continue;
            }
            // Stop if the next line is itself another field label.
            if ($this->lineStartsWithAnyField($line)) {
                return null;
            }

            return $this->cleanRaw($this->truncateAtNextLabel($line));
        }

        return null;
    }

    private function lineStartsWithAnyField(string $line): bool
    {
        foreach (self::FIELDS as $field) {
            if ($this->matchLabelAtStart($line, $field['aliases']) !== null) {
                return true;
            }
        }

        return false;
    }

    private function isBoilerplate(string $line): bool
    {
        $patterns = [
            '~^MENRO\s+TAGOLOAN~iu',
            '~^PLANTING\s+REQUEST$~iu',
            '~^Fill in the blanks~iu',
            '~^Keep the labels~iu',
            '~^_+$~u',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<string>  $aliases
     */
    private function aliasAlternation(array $aliases): string
    {
        $parts = [];
        foreach ($aliases as $alias) {
            // Allow flexible whitespace inside multi-word aliases that already
            // use \s; plain phrases get spaces turned into \s+.
            $parts[] = preg_replace('/\s+/', '\s+', $alias) ?? $alias;
        }

        return '(?:'.implode('|', $parts).')';
    }

    private function stopLookahead(): string
    {
        $labels = [];
        foreach (self::FIELDS as $field) {
            foreach ($field['aliases'] as $alias) {
                $labels[] = (preg_replace('/\s+/', '\s+', $alias) ?? $alias).'\s*[:\-]';
            }
        }
        foreach (self::EXTRA_STOPS as $stop) {
            $labels[] = $stop.'\s*[:\-]?';
        }

        return '\s*(?:'.implode('|', $labels).')';
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return list<array{key: string, label: string, found: bool, value: mixed}>
     */
    private function buildComponents(array $meta): array
    {
        $components = [];

        foreach (self::FIELDS as $key => $field) {
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
     * @return list<string>
     */
    private function splitSpeciesList(string $raw): array
    {
        $parts = preg_split('/\s*(?:,|;|\/|\band\b|&)\s*/i', $raw) ?: [];
        $names = [];

        foreach ($parts as $part) {
            $cleaned = $this->cleanRaw((string) $part);
            if ($cleaned === null) {
                continue;
            }
            $cleaned = preg_replace('/\s+/', ' ', $cleaned) ?? $cleaned;
            if ($cleaned === '') {
                continue;
            }
            $exists = false;
            foreach ($names as $existing) {
                if (mb_strtolower($existing) === mb_strtolower($cleaned)) {
                    $exists = true;
                    break;
                }
            }
            if (! $exists) {
                $names[] = $cleaned;
            }
        }

        return $names;
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        // Soft hyphen / odd OCR separators → space
        $text = str_replace(["\u{00AD}", "\u{200B}", "\u{FEFF}"], '', $text);
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        // Collapse runs of blank lines but keep single newlines for line parsing.
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function normalizeMunicipality(string $raw): string
    {
        $value = preg_replace('/\s+/', ' ', trim($raw)) ?? trim($raw);
        // Accept OCR variants; planting is always within Tagoloan.
        if ($value === '' || preg_match('/tagoloan/i', $value)) {
            return 'Tagoloan';
        }

        return $value;
    }

    private function cleanRaw(string $raw): ?string
    {
        $raw = trim($raw);
        // Template underscore blanks (before or after a typed value).
        $raw = preg_replace('/_+/', ' ', $raw) ?? $raw;
        // Dotted / dashed blanks sometimes used in free-form letters.
        $raw = preg_replace('/\.{3,}/', ' ', $raw) ?? $raw;
        $raw = preg_replace('/-{3,}/', ' ', $raw) ?? $raw;
        $raw = preg_replace('/\s+/', ' ', $raw) ?? $raw;
        $raw = trim($raw, " \t.:");

        if (
            $raw === ''
            || preg_match('/^_+$/', $raw)
            || in_array(mb_strtolower($raw), ['-', 'n/a', 'na', 'none', 'no data'], true)
        ) {
            return null;
        }

        return $raw;
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

        // Preserve paragraph and table-row breaks so template lines stay intact.
        $xml = preg_replace('/<\/w:p>/', "\n", $xml) ?? $xml;
        $xml = preg_replace('/<\/w:tr>/', "\n", $xml) ?? $xml;
        $xml = preg_replace('/<w:br[^>]*\/?>/', "\n", $xml) ?? $xml;
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

        if ($chunks === []) {
            return null;
        }

        // Join PDF text operators with newlines when a chunk looks like a label
        // or ends a field, otherwise with a space — keeps both layouts readable.
        $text = '';
        foreach ($chunks as $i => $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') {
                continue;
            }
            if ($text === '') {
                $text = $chunk;
                continue;
            }

            $prevEndsLabel = (bool) preg_match('/[:\-]\s*$/', $text);
            $chunkIsLabel = $this->lineStartsWithAnyField($chunk)
                || (bool) preg_match('/^(?:Notes|Purpose|MENRO)/iu', $chunk);

            $text .= ($prevEndsLabel || $chunkIsLabel ? "\n" : ' ').$chunk;
        }

        $text = preg_replace("/\n{2,}/", "\n", trim($text)) ?? trim($text);

        return $text !== '' ? $text : null;
    }

    private function unescapePdfString(string $value): string
    {
        return str_replace(
            ['\\n', '\\r', '\\t', '\\(', '\\)', '\\\\'],
            ["\n", "\r", "\t", '(', ')', '\\'],
            $value
        );
    }

    private function parseCount(string $value): ?int
    {
        if (preg_match('/-?\d+/', str_replace([',', ' '], '', $value), $matches)) {
            return max(0, (int) $matches[0]);
        }

        return null;
    }
}
