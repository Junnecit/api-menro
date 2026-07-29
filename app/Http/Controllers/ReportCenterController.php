<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportCenter\StoreReportFileRequest;
use App\Http\Requests\ReportCenter\StoreReportFolderRequest;
use App\Http\Requests\ReportCenter\UpdateReportFileRequest;
use App\Http\Requests\ReportCenter\UpdateReportFolderRequest;
use App\Http\Resources\ReportFileResource;
use App\Http\Resources\ReportFolderResource;
use App\Models\PlantingMonitoring;
use App\Models\ReportFile;
use App\Models\ReportFolder;
use App\Services\ReportAgencyFolderSyncService;
use App\Services\ReportFileService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportCenterController extends Controller
{
    public function __construct(
        private ReportFileService $fileService,
        private ReportAgencyFolderSyncService $agencyFolderSync,
    ) {}

    public function syncAgencyFolders(Request $request): JsonResponse
    {
        $this->authorize('create', ReportFolder::class);

        $stats = $this->agencyFolderSync->sync($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Agency folders synced successfully.',
            'data' => $stats,
        ]);
    }

    public function browse(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ReportFolder::class);

        $trashed = $request->boolean('trash');
        $folderId = $request->filled('folder_id') ? $request->integer('folder_id') : null;
        $search = $request->string('search')->toString();

        $current = null;
        $breadcrumbs = [];

        if ($folderId && ! $trashed) {
            $current = ReportFolder::query()->findOrFail($folderId);
            $breadcrumbs = $this->breadcrumbsFor($current);
        }

        $folderQuery = $trashed
            ? ReportFolder::onlyTrashed()->whereNull('parent_id')
            : ReportFolder::query()->where('parent_id', $folderId);

        $fileQuery = $trashed
            ? ReportFile::onlyTrashed()->whereNull('folder_id')
            : ReportFile::query()->where('folder_id', $folderId);

        // In trash, also include items deleted while inside a folder so users can restore them.
        if ($trashed) {
            $folderQuery = ReportFolder::onlyTrashed();
            $fileQuery = ReportFile::onlyTrashed();
        }

        if ($search !== '') {
            $folderQuery->where('name', 'like', "%{$search}%");
            $fileQuery->where('name', 'like', "%{$search}%");
        }

        $folders = $folderQuery
            ->withCount(['children', 'files'])
            ->orderBy('name')
            ->get();

        $files = $fileQuery
            ->orderByDesc('updated_at')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'folder' => $current ? new ReportFolderResource($current) : null,
                'breadcrumbs' => collect($breadcrumbs)->map(fn (ReportFolder $f) => [
                    'id' => $f->id,
                    'name' => $f->name,
                ])->values(),
                'folders' => ReportFolderResource::collection($folders),
                'files' => ReportFileResource::collection($files),
            ],
            'meta' => [
                'folder_count' => $folders->count(),
                'file_count' => $files->count(),
                'trash_count' => ReportFolder::onlyTrashed()->count() + ReportFile::onlyTrashed()->count(),
            ],
        ]);
    }

    public function storeFolder(StoreReportFolderRequest $request): JsonResponse
    {
        $this->authorize('create', ReportFolder::class);

        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $folder = ReportFolder::create($data)->loadCount(['children', 'files']);

        return response()->json([
            'success' => true,
            'message' => 'Folder created successfully.',
            'data' => new ReportFolderResource($folder),
        ], 201);
    }

    public function updateFolder(UpdateReportFolderRequest $request, ReportFolder $report_folder): JsonResponse
    {
        $this->authorize('update', $report_folder);

        $report_folder->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Folder renamed successfully.',
            'data' => new ReportFolderResource($report_folder->fresh()->loadCount(['children', 'files'])),
        ]);
    }

    public function destroyFolder(ReportFolder $report_folder): JsonResponse
    {
        $this->authorize('delete', $report_folder);

        DB::transaction(function () use ($report_folder) {
            $this->softDeleteFolderTree($report_folder);
        });

        return response()->json([
            'success' => true,
            'message' => 'Folder moved to trash.',
        ]);
    }

    public function restoreFolder(int $id): JsonResponse
    {
        $folder = ReportFolder::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $folder);

        $folder->restore();

        // If the parent is still trashed, lift the folder to root so it remains reachable.
        if ($folder->parent_id && ReportFolder::onlyTrashed()->whereKey($folder->parent_id)->exists()) {
            $folder->update(['parent_id' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Folder restored successfully.',
            'data' => new ReportFolderResource($folder->fresh()->loadCount(['children', 'files'])),
        ]);
    }

    public function forceDestroyFolder(int $id): JsonResponse
    {
        $folder = ReportFolder::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $folder);

        DB::transaction(function () use ($folder) {
            $this->forceDeleteFolderTree($folder);
        });

        return response()->json([
            'success' => true,
            'message' => 'Folder permanently deleted.',
        ]);
    }

    public function storeFile(StoreReportFileRequest $request): JsonResponse
    {
        $this->authorize('create', ReportFile::class);

        $file = $this->fileService->storeUpload(
            $request->integer('folder_id') ?: null,
            $request->file('file'),
            $request->input('name'),
            $request->user()->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully.',
            'data' => new ReportFileResource($file),
        ], 201);
    }

    public function updateFile(UpdateReportFileRequest $request, ReportFile $report_file): JsonResponse
    {
        $this->authorize('update', $report_file);

        $report_file->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'File updated successfully.',
            'data' => new ReportFileResource($report_file->fresh()),
        ]);
    }

    public function destroyFile(ReportFile $report_file): JsonResponse
    {
        $this->authorize('delete', $report_file);

        $report_file->delete();

        return response()->json([
            'success' => true,
            'message' => 'File moved to trash.',
        ]);
    }

    public function restoreFile(int $id): JsonResponse
    {
        $file = ReportFile::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $file);

        $file->restore();

        if ($file->folder_id && ReportFolder::onlyTrashed()->whereKey($file->folder_id)->exists()) {
            $file->update(['folder_id' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'File restored successfully.',
            'data' => new ReportFileResource($file->fresh()),
        ]);
    }

    public function forceDestroyFile(int $id): JsonResponse
    {
        $file = ReportFile::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $file);

        $this->fileService->deleteFile($file);
        $file->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'File permanently deleted.',
        ]);
    }

    public function downloadFile(ReportFile $report_file): StreamedResponse
    {
        $this->authorize('view', $report_file);

        return response()->streamDownload(function () use ($report_file) {
            echo \Illuminate\Support\Facades\Storage::disk('public')->get($report_file->path);
        }, $report_file->name, [
            'Content-Type' => $report_file->mime ?: 'application/octet-stream',
        ]);
    }

    public function saveMonitoringPdf(Request $request): JsonResponse
    {
        $this->authorize('create', ReportFile::class);
        $this->authorize('viewAny', PlantingMonitoring::class);

        $request->validate([
            'folder_id' => ['nullable', 'integer', 'exists:report_folders,id'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $query = PlantingMonitoring::query()->with('request.agency')
            ->orderByDesc('date_monitoring')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('seedling_type', 'like', "%{$search}%")
                    ->orWhereHas('request', function ($rq) use ($search) {
                        $rq->where('request_no', 'like', "%{$search}%")
                            ->orWhere('requester_name', 'like', "%{$search}%")
                            ->orWhere('location', 'like', "%{$search}%")
                            ->orWhereHas('agency', fn ($aq) => $aq->where('name', 'like', "%{$search}%"));
                    });
            });
        }

        if ($request->filled('agency_id')) {
            $agencyId = $request->integer('agency_id');
            $query->whereHas('request', fn ($rq) => $rq->where('agency_id', $agencyId));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_monitoring', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_monitoring', '<=', $request->date('date_to'));
        }

        $records = $query->get();
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

        $pdf = Pdf::setOptions(['temp_dir' => $tempDir], true)->loadView('reports.planting-monitoring', [
            'records' => $records,
            'totals' => $totals,
            'generatedAt' => now(),
            'menroSealDataUri' => $this->imageToDataUri(public_path('images/menro-seal.png')),
            'provinceSealDataUri' => $this->imageToDataUri(public_path('images/province-seal.png')),
        ])->setPaper('legal', 'portrait');

        $name = $request->input('name')
            ?: 'menro-planting-monitoring-report-'.now()->format('Y-m-d').'.pdf';

        $file = $this->fileService->storeGeneratedPdf(
            $request->integer('folder_id') ?: null,
            $pdf->output(),
            $name,
            $request->user()->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'Monitoring report saved to the file manager.',
            'data' => new ReportFileResource($file),
        ], 201);
    }

    private function breadcrumbsFor(ReportFolder $folder): array
    {
        $crumbs = [];
        $current = $folder;

        while ($current) {
            array_unshift($crumbs, $current);
            $current = $current->parent_id
                ? ReportFolder::query()->find($current->parent_id)
                : null;
        }

        return $crumbs;
    }

    private function softDeleteFolderTree(ReportFolder $folder): void
    {
        foreach ($folder->children()->get() as $child) {
            $this->softDeleteFolderTree($child);
        }

        $folder->files()->get()->each(fn (ReportFile $file) => $file->delete());
        $folder->delete();
    }

    private function forceDeleteFolderTree(ReportFolder $folder): void
    {
        $children = ReportFolder::withTrashed()->where('parent_id', $folder->id)->get();
        foreach ($children as $child) {
            $this->forceDeleteFolderTree($child);
        }

        $files = ReportFile::withTrashed()->where('folder_id', $folder->id)->get();
        foreach ($files as $file) {
            $this->fileService->deleteFile($file);
            $file->forceDelete();
        }

        $folder->forceDelete();
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
