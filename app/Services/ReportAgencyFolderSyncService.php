<?php

namespace App\Services;

use App\Models\Agency;
use App\Models\PlantingMonitoring;
use App\Models\ReportFile;
use App\Models\ReportFolder;

class ReportAgencyFolderSyncService
{
    private const AREA_DATA_FOLDER_NAME = 'Area Data';

    public function __construct(
        private ReportFileService $fileService,
        private MonitoringReportPdfService $reportPdf,
    ) {}

    /**
     * Create one root folder per agency, ensure an Area Data child folder,
     * then place a PDF for each monitoring record inside Area Data.
     *
     * @return array{folders_created:int,folders_reused:int,files_created:int,files_skipped:int,files_moved:int}
     */
    public function sync(?int $userId = null): array
    {
        $stats = [
            'folders_created' => 0,
            'folders_reused' => 0,
            'files_created' => 0,
            'files_skipped' => 0,
            'files_moved' => 0,
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
            $agencyFolder = $this->findOrCreateAgencyFolder($agency, $userId, $stats);
            $areaDataFolder = $this->findOrCreateChildFolder(
                $agencyFolder,
                self::AREA_DATA_FOLDER_NAME,
                $userId,
                $stats,
            );

            $this->migrateAgencyRootMonitoringPdfs($agencyFolder, $areaDataFolder, $stats);

            $monitorings = PlantingMonitoring::query()
                ->with('request.agency')
                ->whereHas('request', fn ($q) => $q->where('agency_id', $agency->id))
                ->orderBy('id')
                ->get();

            foreach ($monitorings as $monitoring) {
                $sourceKey = 'monitoring:'.$monitoring->id;
                $existing = ReportFile::query()
                    ->where('folder_id', $areaDataFolder->id)
                    ->where('source_key', $sourceKey)
                    ->first();

                if ($existing) {
                    $stats['files_skipped']++;
                    continue;
                }

                $pdfBinary = $this->reportPdf->output(
                    PlantingMonitoring::query()
                        ->with('request.agency')
                        ->whereKey($monitoring->id)
                );

                $requestNo = $monitoring->request?->request_no ?? ('#'.$monitoring->id);
                $seedling = $monitoring->seedling_type ?: 'Record';
                $safeSeedling = preg_replace('/[\\\\\/:*?"<>|]+/', '-', $seedling) ?: 'Record';
                $name = trim($requestNo.' - '.$safeSeedling).'.pdf';

                $this->fileService->storeGeneratedPdf(
                    $areaDataFolder->id,
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
            $agencyFolder = $this->findOrCreateAgencyFolder($agency, $userId, $stats);
            $this->findOrCreateChildFolder(
                $agencyFolder,
                self::AREA_DATA_FOLDER_NAME,
                $userId,
                $stats,
            );
        }

        return $stats;
    }

    /**
     * Move legacy monitoring PDFs that still sit directly under the agency
     * folder into that agency's Area Data folder.
     */
    private function migrateAgencyRootMonitoringPdfs(
        ReportFolder $agencyFolder,
        ReportFolder $areaDataFolder,
        array &$stats,
    ): void {
        $legacyFiles = ReportFile::query()
            ->where('folder_id', $agencyFolder->id)
            ->where(function ($q) {
                $q->where('source', 'monitoring-pdf')
                    ->orWhere('source_key', 'like', 'monitoring:%');
            })
            ->get();

        foreach ($legacyFiles as $file) {
            $file->update(['folder_id' => $areaDataFolder->id]);
            $stats['files_moved']++;
        }
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

    private function findOrCreateChildFolder(
        ReportFolder $parent,
        string $name,
        ?int $userId,
        array &$stats,
    ): ReportFolder {
        $folder = ReportFolder::query()
            ->where('parent_id', $parent->id)
            ->where('name', $name)
            ->first();

        if ($folder) {
            $stats['folders_reused']++;

            return $folder;
        }

        $stats['folders_created']++;

        return ReportFolder::create([
            'name' => $name,
            'parent_id' => $parent->id,
            'agency_id' => $parent->agency_id,
            'created_by' => $userId,
        ]);
    }
}
