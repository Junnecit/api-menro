<?php

namespace App\Services;

use App\Models\Request as PlantingRequest;
use App\Support\TagoloanLocation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PlantingRequestPdfService
{
    /**
     * Build the official blank / fillable Planting Request Template PDF.
     *
     * @param array<string, mixed> $defaultFields Optional pre-filled values
     */
    public function makeBlankTemplatePdf(array $defaultFields = []): \Barryvdh\DomPDF\PDF
    {
        $this->ensureTempDir();

        return Pdf::setOptions([
            'temp_dir' => storage_path('app/pdf-temp'),
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
        ], true)->loadView('reports.planting-request-template', [
            'fields' => $defaultFields,
            'headerDataUri' => $this->imageToDataUri(public_path('images/menro-header.png')),
            'menroSealDataUri' => $this->imageToDataUri(public_path('images/menro-seal.png')),
            'provinceSealDataUri' => $this->imageToDataUri(public_path('images/province-seal.png')),
        ])->setPaper('legal', 'portrait');
    }

    /**
     * Build the PDF document for an individual submitted Planting Request.
     */
    public function makeRequestPdf(PlantingRequest $request): \Barryvdh\DomPDF\PDF
    {
        $this->ensureTempDir();

        $request->loadMissing(['agency', 'user']);

        $barangayName = $request->barangay_code
            ? TagoloanLocation::barangayName($request->barangay_code)
            : ($request->location ?? 'Tagoloan');

        return Pdf::setOptions([
            'temp_dir' => storage_path('app/pdf-temp'),
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
        ], true)->loadView('reports.planting-request-document', [
            'request' => $request,
            'barangayName' => $barangayName,
            'generatedAt' => now(),
            'headerDataUri' => $this->imageToDataUri(public_path('images/menro-header.png')),
            'menroSealDataUri' => $this->imageToDataUri(public_path('images/menro-seal.png')),
            'provinceSealDataUri' => $this->imageToDataUri(public_path('images/province-seal.png')),
        ])->setPaper('legal', 'portrait');
    }

    /**
     * Build a consolidated summary PDF report for filtered planting requests.
     *
     * @param array{search?:string|null,agency_id?:int|null,status?:string|null,date_from?:string|null,date_to?:string|null} $filterMeta
     */
    public function makeRequestsSummaryPdf(Builder $query, array $filterMeta = []): \Barryvdh\DomPDF\PDF
    {
        $this->ensureTempDir();

        $roots = (clone $query)
            ->with([
                'agency',
                'user',
                'children' => fn ($c) => $c->with(['agency', 'user'])->orderByDesc('request_date')->orderByDesc('id'),
            ])
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->get();

        $rows = $this->buildSummaryRows($roots, $filterMeta);
        $totals = $this->computeTotalsFromRows($rows);

        return Pdf::setOptions([
            'temp_dir' => storage_path('app/pdf-temp'),
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
        ], true)->loadView('reports.planting-requests-summary', [
            'rows' => $rows,
            'totals' => $totals,
            'filterMeta' => $filterMeta,
            'filterNote' => $this->filterNote($filterMeta),
            'generatedAt' => now(),
            'headerDataUri' => $this->imageToDataUri(public_path('images/menro-header.png')),
            'menroSealDataUri' => $this->imageToDataUri(public_path('images/menro-seal.png')),
            'provinceSealDataUri' => $this->imageToDataUri(public_path('images/province-seal.png')),
        ])->setPaper('legal', 'portrait');
    }

    /**
     * Build flattened hierarchical list of display rows (parent roots and sub-children).
     *
     * @param Collection<int, PlantingRequest> $roots
     * @param array<string, mixed> $filterMeta
     * @return list<object{request: PlantingRequest, is_sub_request: bool, depth: int, parent_request_no: ?string, parent_id: ?int, has_children: bool, children_count: int, is_parent_shell: bool}>
     */
    public function buildSummaryRows(Collection $roots, array $filterMeta = []): array
    {
        $rows = [];

        foreach ($roots as $root) {
            $children = $root->children ?? collect();
            $rootMatches = $this->requestMatchesFilters($root, $filterMeta);

            $matchingChildren = $children->filter(
                fn (PlantingRequest $child) => $this->requestMatchesFilters($child, $filterMeta)
            );

            $includeRoot = $rootMatches && ! $root->isEmptyShell();

            $childrenToInclude = empty($filterMeta['status'])
                ? ($rootMatches ? $children : $matchingChildren)
                : $matchingChildren;

            if ($includeRoot) {
                $rows[] = (object) [
                    'request' => $root,
                    'is_sub_request' => false,
                    'depth' => 0,
                    'parent_request_no' => null,
                    'parent_id' => null,
                    'has_children' => $childrenToInclude->isNotEmpty(),
                    'children_count' => $childrenToInclude->count(),
                    'is_parent_shell' => $root->isEmptyShell(),
                ];
            }

            foreach ($childrenToInclude as $child) {
                $rows[] = (object) [
                    'request' => $child,
                    'is_sub_request' => true,
                    'depth' => 1,
                    'parent_request_no' => $root->request_no ?: ('REQ-'.$root->id),
                    'parent_id' => $root->id,
                    'has_children' => false,
                    'children_count' => 0,
                    'is_parent_shell' => false,
                ];
            }
        }

        return $rows;
    }

    /**
     * Check whether an individual request matches active filter criteria.
     */
    public function requestMatchesFilters(PlantingRequest $req, array $filterMeta = []): bool
    {
        if (! empty($filterMeta['status'])) {
            if (strcasecmp((string) $req->status, (string) $filterMeta['status']) !== 0) {
                return false;
            }
        }

        if (! empty($filterMeta['agency_id'])) {
            if ((int) $req->agency_id !== (int) $filterMeta['agency_id']) {
                return false;
            }
        }

        if (! empty($filterMeta['date_from'])) {
            $date = $req->request_date ?? $req->created_at;
            if ($date && Carbon::parse($date)->startOfDay()->lt(Carbon::parse($filterMeta['date_from'])->startOfDay())) {
                return false;
            }
        }

        if (! empty($filterMeta['date_to'])) {
            $date = $req->request_date ?? $req->created_at;
            if ($date && Carbon::parse($date)->endOfDay()->gt(Carbon::parse($filterMeta['date_to'])->endOfDay())) {
                return false;
            }
        }

        if (! empty($filterMeta['search'])) {
            $search = mb_strtolower(trim((string) $filterMeta['search']));
            $haystack = mb_strtolower(implode(' ', array_filter([
                $req->request_no,
                $req->project_name,
                $req->location,
                $req->custom_address,
                $req->requester_name,
                $req->document_name,
                $req->barangay_code ? TagoloanLocation::barangayName($req->barangay_code) : null,
                $req->agency?->name,
                $req->user?->name,
                $req->user?->email,
            ])));

            if (! str_contains($haystack, $search)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Compute totals directly from the generated display rows.
     *
     * @param list<object{request: PlantingRequest, is_sub_request: bool, depth: int, parent_request_no: ?string, parent_id: ?int, has_children: bool, children_count: int, is_parent_shell: bool}> $rows
     */
    public function computeTotalsFromRows(array $rows): array
    {
        $targetTrees = 0;
        $approved = 0;
        $pending = 0;
        $inProgress = 0;
        $completed = 0;
        $rejected = 0;
        $subCount = 0;
        $parentCount = 0;

        foreach ($rows as $row) {
            $req = $row->request;
            $targetTrees += (int) $req->target_trees;

            $status = strtolower((string) $req->status);
            if ($status === 'approved') {
                $approved++;
            } elseif ($status === 'pending') {
                $pending++;
            } elseif ($status === 'in progress' || $status === 'in_progress') {
                $inProgress++;
            } elseif ($status === 'completed') {
                $completed++;
            } elseif ($status === 'rejected') {
                $rejected++;
            }

            if ($row->is_sub_request) {
                $subCount++;
            } else {
                $parentCount++;
            }
        }

        return [
            'total_requests' => count($rows),
            'target_trees' => $targetTrees,
            'approved_requests' => $approved,
            'pending_requests' => $pending,
            'in_progress_requests' => $inProgress,
            'completed_requests' => $completed,
            'rejected_requests' => $rejected,
            'parent_requests_count' => $parentCount,
            'sub_requests_count' => $subCount,
        ];
    }

    /**
     * Backward-compatible computeTotals method if needed.
     */
    public function computeTotals(Builder $query): array
    {
        $roots = (clone $query)->with(['children'])->get();
        $rows = $this->buildSummaryRows($roots);

        return $this->computeTotalsFromRows($rows);
    }

    private function filterNote(array $filterMeta): ?string
    {
        $parts = [];

        if (! empty($filterMeta['search'])) {
            $parts[] = 'Search: '.$filterMeta['search'];
        }
        if (! empty($filterMeta['status'])) {
            $parts[] = 'Status: '.ucfirst($filterMeta['status']);
        }
        if (! empty($filterMeta['agency_id'])) {
            $parts[] = 'Agency ID: '.$filterMeta['agency_id'];
        }
        if (! empty($filterMeta['date_from']) || ! empty($filterMeta['date_to'])) {
            $from = ! empty($filterMeta['date_from'])
                ? Carbon::parse($filterMeta['date_from'])->format('M d, Y')
                : '…';
            $to = ! empty($filterMeta['date_to'])
                ? Carbon::parse($filterMeta['date_to'])->format('M d, Y')
                : '…';
            $parts[] = "Period: {$from} - {$to}";
        }

        return $parts === [] ? null : implode(' | ', $parts);
    }

    public function imageToDataUri(string $path): ?string
    {
        if (! file_exists($path)) {
            return null;
        }

        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'png');
        if ($type === 'jpg') {
            $type = 'jpeg';
        }

        return 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($path));
    }

    private function ensureTempDir(): void
    {
        $tempDir = storage_path('app/pdf-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
    }
}

