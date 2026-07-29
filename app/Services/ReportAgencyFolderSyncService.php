<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\PlantingMonitoring;
use App\Models\ReportFile;
use App\Models\ReportFolder;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportAgencyFolderSyncService
{
    public function __construct(private ReportFileService $fileService) {}

    /**
     * Create one root folder per agency that has monitoring records, then
     * place a PDF for each monitoring record inside that agency folder.
     *
     * @return array{folders_created:int,folders_reused:int,files_created:int,files_skipped:int}
     */
    public function sync(?int $userId = null): array
    {
        $stats = [
            'folders_created' => 0,
            'folders_reused' => 0,
            'files_created' => 0,
            'files_skipped' => 0,
        ];

        $agencyIds = PlantingMonitoring::query()
            ->whereHas('request', fn ($q) => $q->whereNotNull('agency_id'))
            ->with('request:id,agency_id')
            ->get()
            ->pluck('request.agency_id')
            ->filter()
            ->unique()
            ->values();

        $agencies = Agency::query()
            ->whereIn('id', $agencyIds)
            ->orderBy('name')
            ->get();

        foreach ($agencies as $agency) {
            $folder = $this->findOrCreateAgencyFolder($agency, $userId, $stats);

            $monitorings = PlantingMonitoring::query()
                ->with('request.agency')
                ->whereHas('request', fn ($q) => $q->where('agency_id', $agency->id))
                ->orderBy('id')
                ->get();

            foreach ($monitorings as $monitoring) {
                $sourceKey = 'monitoring:'.$monitoring->id;
                $existing = ReportFile::query()
                    ->where('folder_id', $folder->id)
                    ->where('source_key', $sourceKey)
                    ->first();

                if ($existing) {
                    $stats['files_skipped']++;
                    continue;
                }

                $pdfBinary = $this->renderMonitoringPdf(
                    PlantingMonitoring::query()
                        ->with('request.agency')
                        ->whereKey($monitoring->id)
                );

                $requestNo = $monitoring->request?->request_no ?? ('#'.$monitoring->id);
                $seedling = $monitoring->seedling_type ?: 'Record';
                $safeSeedling = preg_replace('/[\\\\\/:*?"<>|]+/', '-', $seedling) ?: 'Record';
                $name = trim($requestNo.' - '.$safeSeedling).'.pdf';

                $this->fileService->storeGeneratedPdf(
                    $folder->id,
                    $pdfBinary,
                    $name,
                    $userId,
                    'monitoring-pdf',
                    $sourceKey,
                );

                $stats['files_created']++;
            }
        }

        // Also create folders for agencies that exist but have no records yet,
        // so the library is complete and ready for future uploads.
        $remaining = Agency::query()
            ->whereNotIn('id', $agencies->pluck('id'))
            ->orderBy('name')
            ->get();

        foreach ($remaining as $agency) {
            $this->findOrCreateAgencyFolder($agency, $userId, $stats);
        }

        return $stats;
    }

    private function findOrCreateAgencyFolder(Agency $agency, ?int $userId, array &$stats): ReportFolder
    {
        $folder = ReportFolder::query()
            ->whereNull('parent_id')
            ->where(function ($q) use ($agency) {
                $q->where('agency_id', $agency->id)
                    ->orWhere(function ($inner) use ($agency) {
                        $inner->whereNull('agency_id')->where('name', $agency->name);
                    });
            })
            ->first();

        if ($folder) {
            if (! $folder->agency_id) {
                $folder->update(['agency_id' => $agency->id, 'name' => $agency->name]);
            } elseif ($folder->name !== $agency->name) {
                $folder->update(['name' => $agency->name]);
            }
            $stats['folders_reused']++;

            return $folder;
        }

        $stats['folders_created']++;

        return ReportFolder::create([
            'name' => $agency->name,
            'parent_id' => null,
            'agency_id' => $agency->id,
            'created_by' => $userId,
        ]);
    }

    private function renderMonitoringPdf($query): string
    {
        $records = $query->orderByDesc('date_monitoring')->orderByDesc('id')->get();

        $sums = (clone $query)->toBase()->reorder()->selectRaw('
            COALESCE(SUM(seedlings_planted), 0) as seedlings_planted,
            COALESCE(SUM(replanted_count), 0) as replanted_count,
            COALESCE(SUM(survived_count), 0) as survived_count,
            COALESCE(SUM(died_count), 0) as died_count
        ')->first();

        $seedlingsPlanted = (int) $sums->seedlings_planted;
        $survived = (int) $sums->survived_count;
        $totals = [
            'seedlings_planted' => $seedlingsPlanted,
            'replanted_count' => (int) $sums->replanted_count,
            'survived_count' => $survived,
            'died_count' => (int) $sums->died_count,
            'survival_rate' => $seedlingsPlanted > 0
                ? round($survived / $seedlingsPlanted * 100, 2)
                : 0.0,
        ];

        $tempDir = storage_path('app/pdf-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        return Pdf::setOptions(['temp_dir' => $tempDir], true)->loadView('reports.planting-monitoring', [
            'records' => $records,
            'totals' => $totals,
            'generatedAt' => now(),
            'menroSealDataUri' => $this->imageToDataUri(public_path('images/menro-seal.png')),
            'provinceSealDataUri' => $this->imageToDataUri(public_path('images/province-seal.png')),
        ])->setPaper('legal', 'portrait')->output();
    }

    private function imageToDataUri(string $path): ?string
    {
        if (! file_exists($path)) {
            return null;
        }

        $type = pathinfo($path, PATHINFO_EXTENSION) ?: 'png';

        return 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($path));
    }
}
