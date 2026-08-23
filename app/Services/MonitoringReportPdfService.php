<?php

namespace App\Services;

use App\Support\TagoloanLocation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MonitoringReportPdfService
{
    /**
     * Build the planting/monitoring DomPDF instance for the given filtered query.
     *
     * @param  array{search?:string|null,agency_id?:int|null,date_from?:string|null,date_to?:string|null}  $filterMeta
     */
    public function make(Builder $query, array $filterMeta = []): \Barryvdh\DomPDF\PDF
    {
        $records = (clone $query)
            ->with('request.agency')
            ->orderByDesc('date_monitoring')
            ->orderByDesc('id')
            ->get();

        $totals = $this->computeTotals($query);
        $summary = $this->buildSummary($records, $totals);

        $recordCount = $records->count();
        $isSinglePage = $recordCount <= 5;

        $tempDir = storage_path('app/pdf-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        return Pdf::setOptions([
            // sys_get_temp_dir() can resolve to an unwritable system path
            // on some Windows setups; point dompdf at Laravel storage instead.
            'temp_dir' => $tempDir,
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
        ], true)->loadView('reports.planting-monitoring', [
            'records' => $records,
            'totals' => $totals,
            'summary' => $summary,
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
     * Render PDF binary output (for save-to-folder / agency sync).
     *
     * @param  array{search?:string|null,agency_id?:int|null,date_from?:string|null,date_to?:string|null}  $filterMeta
     */
    public function output(Builder $query, array $filterMeta = []): string
    {
        return $this->make($query, $filterMeta)->output();
    }

    public function computeTotals(Builder $query): array
    {
        $sums = (clone $query)->toBase()->reorder()->selectRaw('
            COALESCE(SUM(seedlings_planted), 0) as seedlings_planted,
            COALESCE(SUM(replanted_count), 0) as replanted_count,
            COALESCE(SUM(survived_count), 0) as survived_count,
            COALESCE(SUM(died_count), 0) as died_count
        ')->first();

        $seedlingsPlanted = (int) $sums->seedlings_planted;
        $survived = (int) $sums->survived_count;

        return [
            'seedlings_planted' => $seedlingsPlanted,
            'replanted_count' => (int) $sums->replanted_count,
            'survived_count' => $survived,
            'died_count' => (int) $sums->died_count,
            'survival_rate' => $seedlingsPlanted > 0
                ? round($survived / $seedlingsPlanted * 100, 2)
                : 0.0,
        ];
    }

    /**
     * @param  Collection<int, \App\Models\PlantingMonitoring>  $records
     */
    public function buildSummary(Collection $records, array $totals): array
    {
        $recordCount = $records->count();
        $rate = (float) $totals['survival_rate'];

        $agencies = $records
            ->map(fn ($r) => $r->request?->agency?->name ?: $r->request?->requester_name)
            ->filter()
            ->unique()
            ->values();

        $sites = $records
            ->map(fn ($r) => $this->areaLabel($r))
            ->filter()
            ->unique()
            ->values();

        $dates = $records
            ->pluck('date_monitoring')
            ->filter()
            ->sort()
            ->values();

        $dateFrom = $dates->first();
        $dateTo = $dates->last();

        $byArea = $records
            ->groupBy(fn ($r) => $this->areaLabel($r) ?: 'Unspecified')
            ->map(function (Collection $group, string $area) {
                $planted = (int) $group->sum('seedlings_planted');
                $survived = (int) $group->sum('survived_count');

                return [
                    'area' => $area,
                    'records' => $group->count(),
                    'seedlings_planted' => $planted,
                    'survived_count' => $survived,
                    'died_count' => (int) $group->sum('died_count'),
                    'survival_rate' => $planted > 0
                        ? round($survived / $planted * 100, 2)
                        : 0.0,
                ];
            })
            ->sortByDesc('seedlings_planted')
            ->values();

        $bySeedling = $records
            ->groupBy(fn ($r) => $r->seedling_type ?: 'Unspecified')
            ->map(function (Collection $group, string $type) {
                $planted = (int) $group->sum('seedlings_planted');
                $survived = (int) $group->sum('survived_count');

                return [
                    'type' => $type,
                    'seedlings_planted' => $planted,
                    'survival_rate' => $planted > 0
                        ? round($survived / $planted * 100, 2)
                        : 0.0,
                ];
            })
            ->sortByDesc('seedlings_planted')
            ->values();

        return [
            'record_count' => $recordCount,
            'agency_count' => $agencies->count(),
            'site_count' => $sites->count(),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'survival_band' => $this->survivalBand($rate),
            'survival_band_label' => $this->survivalBandLabel($rate),
            'findings' => $this->buildFindings($records, $totals, $byArea, $bySeedling),
            'areas' => $byArea->take(8)->values()->all(),
        ];
    }

    /**
     * @param  Collection<int, \App\Models\PlantingMonitoring>  $records
     * @param  Collection<int, array<string, mixed>>  $byArea
     * @param  Collection<int, array<string, mixed>>  $bySeedling
     * @return list<string>
     */
    private function buildFindings(
        Collection $records,
        array $totals,
        Collection $byArea,
        Collection $bySeedling
    ): array {
        if ($records->isEmpty()) {
            return ['No monitoring records match the selected filters.'];
        }

        $findings = [];

        $findings[] = sprintf(
            'Overall survival rate is %s%% (%s of %s seedlings), rated %s.',
            number_format($totals['survival_rate'], 2),
            number_format($totals['survived_count']),
            number_format($totals['seedlings_planted']),
            $this->survivalBandLabel($totals['survival_rate'])
        );

        if ($totals['replanted_count'] > 0) {
            $findings[] = sprintf(
                'A total of %s seedlings were re-planted across monitored sites.',
                number_format($totals['replanted_count'])
            );
        }

        $rankedAreas = $byArea
            ->filter(fn (array $row) => $row['seedlings_planted'] > 0)
            ->sortByDesc('survival_rate')
            ->values();

        if ($rankedAreas->count() >= 2) {
            $best = $rankedAreas->first();
            $worst = $rankedAreas->last();
            $findings[] = sprintf(
                'Highest survival by area: %s (%s%%). Lowest: %s (%s%%).',
                $best['area'],
                number_format($best['survival_rate'], 2),
                $worst['area'],
                number_format($worst['survival_rate'], 2)
            );
        } elseif ($rankedAreas->count() === 1) {
            $only = $rankedAreas->first();
            $findings[] = sprintf(
                'Primary monitored area: %s with %s%% survival.',
                $only['area'],
                number_format($only['survival_rate'], 2)
            );
        }

        $topSeedling = $bySeedling->first();
        if ($topSeedling && $bySeedling->count() > 1) {
            $findings[] = sprintf(
                'Most planted seedling type: %s (%s seedlings, %s%% survival).',
                $topSeedling['type'],
                number_format($topSeedling['seedlings_planted']),
                number_format($topSeedling['survival_rate'], 2)
            );
        }

        $zeroSurvival = $records->filter(
            fn ($r) => (int) $r->seedlings_planted > 0 && (int) $r->survived_count === 0
        )->count();

        if ($zeroSurvival > 0) {
            $findings[] = sprintf(
                '%s monitoring record(s) reported zero surviving seedlings and may need follow-up.',
                $zeroSurvival
            );
        }

        return array_slice($findings, 0, 5);
    }

    private function areaLabel(mixed $record): string
    {
        $request = $record->request;
        if (! $request) {
            return '';
        }

        if (! empty($request->barangay_code)) {
            $name = TagoloanLocation::barangayName($request->barangay_code);
            if ($name) {
                return $name;
            }
        }

        return trim((string) ($request->location ?? '')) ?: '';
    }

    private function survivalBand(float $rate): string
    {
        if ($rate >= 85) {
            return 'excellent';
        }
        if ($rate >= 70) {
            return 'good';
        }

        return 'attention';
    }

    private function survivalBandLabel(float $rate): string
    {
        return match ($this->survivalBand($rate)) {
            'excellent' => 'Excellent',
            'good' => 'Good',
            default => 'Needs Attention',
        };
    }

    /**
     * @param  array{search?:string|null,agency_id?:int|null,date_from?:string|null,date_to?:string|null}  $filterMeta
     */
    private function filterNote(array $filterMeta): ?string
    {
        $parts = [];

        if (! empty($filterMeta['search'])) {
            $parts[] = 'Search: '.$filterMeta['search'];
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
            $parts[] = "Monitoring period: {$from} - {$to}";
        }

        return $parts === [] ? null : implode(' | ', $parts);
    }

    public function imageToDataUri(string $path): ?string
    {
        // dompdf misparses Windows drive-letter paths; data URIs avoid that.
        if (! file_exists($path)) {
            return null;
        }

        $type = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'png');
        if ($type === 'jpg') {
            $type = 'jpeg';
        }

        return 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($path));
    }
}
