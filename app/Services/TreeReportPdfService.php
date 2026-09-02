<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;

class TreeReportPdfService
{
    /**
     * Build the Tree Report summary DomPDF instance for the given filtered query.
     */
    public function make(Builder $query, array $filterMeta = []): \Barryvdh\DomPDF\PDF
    {
        $reports = (clone $query)
            ->with(['tree', 'agency', 'reportedBy', 'resolvedBy'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $totals = $this->computeTotals($query);

        $tempDir = storage_path('app/pdf-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $recordCount = $reports->count();
        $isSinglePage = $recordCount <= 6;

        return Pdf::setOptions([
            'temp_dir' => $tempDir,
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
        ], true)->loadView('reports.tree-reports-summary', [
            'reports' => $reports,
            'totals' => $totals,
            'filterNote' => $this->filterNote($filterMeta),
            'generatedAt' => now(),
            'isSinglePage' => $isSinglePage,
            'recordCount' => $recordCount,
            'headerDataUri' => $this->imageToDataUri(public_path('images/menro-header.png')),
            'menroSealDataUri' => $this->imageToDataUri(public_path('images/menro-seal.png')),
            'provinceSealDataUri' => $this->imageToDataUri(public_path('images/province-seal.png')),
        ])->setPaper('legal', 'portrait');
    }

    /**
     * Render PDF binary output.
     */
    public function output(Builder $query, array $filterMeta = []): string
    {
        return $this->make($query, $filterMeta)->output();
    }

    private function computeTotals(Builder $query): array
    {
        $cloned = clone $query;
        $total = $cloned->count();

        $critical = (clone $query)->whereIn('severity', ['critical', 'high'])->count();
        $submitted = (clone $query)->where('status', 'submitted')->count();
        $underReview = (clone $query)->where('status', 'under_review')->count();
        $resolved = (clone $query)->where('status', 'resolved')->count();
        $needReplacement = (clone $query)->where(function ($q) {
            $q->where('report_type', 'replacement_needed')
                ->orWhere('tree_status_update', 'need_replacement');
        })->count();

        return [
            'total_reports' => $total,
            'critical_count' => $critical,
            'submitted_count' => $submitted,
            'under_review_count' => $underReview,
            'resolved_count' => $resolved,
            'need_replacement_count' => $needReplacement,
        ];
    }

    private function filterNote(array $meta): string
    {
        $parts = [];

        if (! empty($meta['status'])) {
            $parts[] = 'Status: '.ucwords(str_replace('_', ' ', $meta['status']));
        }
        if (! empty($meta['severity'])) {
            $parts[] = 'Severity: '.ucfirst($meta['severity']);
        }
        if (! empty($meta['report_type'])) {
            $parts[] = 'Type: '.ucwords(str_replace('_', ' ', $meta['report_type']));
        }
        if (! empty($meta['barangay'])) {
            $parts[] = 'Barangay: '.$meta['barangay'];
        }
        if (! empty($meta['date_from']) || ! empty($meta['date_to'])) {
            $from = $meta['date_from'] ?? 'Earliest';
            $to = $meta['date_to'] ?? 'Present';
            $parts[] = "Period: {$from} to {$to}";
        }

        return implode(' | ', $parts);
    }

    private function imageToDataUri(string $path): ?string
    {
        if (! file_exists($path) || ! is_readable($path)) {
            return null;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }
}
