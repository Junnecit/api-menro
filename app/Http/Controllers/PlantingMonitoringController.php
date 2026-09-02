<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlantingMonitoring\StorePlantingMonitoringRequest;
use App\Http\Requests\PlantingMonitoring\UpdatePlantingMonitoringRequest;
use App\Http\Resources\PlantingMonitoringResource;
use App\Models\PlantingMonitoring;
use App\Models\Tree;
use App\Services\MonitoringReportPdfService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\ReportFolder;
use App\Services\ReportFileService;

class PlantingMonitoringController extends Controller
{
    public function __construct(
        private MonitoringReportPdfService $reportPdf,
        private ReportFileService $fileService,
    ) {}

    public function seedlingTypes(): JsonResponse
    {
        $this->authorize('viewAny', PlantingMonitoring::class);

        $dbTypes = PlantingMonitoring::query()
            ->select('seedling_type')
            ->distinct()
            ->whereNotNull('seedling_type')
            ->where('seedling_type', '!=', '')
            ->pluck('seedling_type')
            ->all();

        $treeSpecies = Tree::query()
            ->select('species', 'common_name')
            ->distinct()
            ->get()
            ->flatMap(function ($t) {
                return array_filter([$t->common_name, $t->species]);
            })
            ->all();

        $curatedCatalog = [
            'Narra', 'Mahogany', 'Molave', 'Agoho', 'Talisay', 'Acacia',
            'Gmelina', 'Jackfruit', 'Bakauan', 'Mangrove', 'Tindalo', 'Ipil',
            'Bamboo', 'Falcata', 'Kamagong', 'Banaba', 'Teak', 'Bagras',
            'Yemane', 'Rubber', 'Cacao', 'Coffee', 'Guyabano', 'Avocado',
            'Mango', 'Balayong', 'Dao', 'Toog', 'Bitaog', 'Katmon', 'Lauan',
            'Almon', 'Apitong', 'Dungon', 'Kalumpit', 'Kupang', 'Malapapaya', 'Tuai',
        ];

        $speciesMap = [];
        foreach (array_merge($dbTypes, $treeSpecies, $curatedCatalog) as $item) {
            $trimmed = trim((string) $item);
            if ($trimmed !== '') {
                $key = strtolower($trimmed);
                if (!isset($speciesMap[$key])) {
                    $speciesMap[$key] = $trimmed;
                }
            }
        }

        $sortedSpecies = array_values($speciesMap);
        natcasesort($sortedSpecies);

        return response()->json([
            'success' => true,
            'data' => array_values($sortedSpecies),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PlantingMonitoring::class);

        return $this->paginatedResponse(
            $this->filteredQuery($request, PlantingMonitoring::query()),
            $request
        );
    }

    public function trash(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PlantingMonitoring::class);

        return $this->paginatedResponse(
            $this->filteredQuery($request, PlantingMonitoring::onlyTrashed()),
            $request
        );
    }

    public function store(StorePlantingMonitoringRequest $request): JsonResponse
    {
        $this->authorize('create', PlantingMonitoring::class);

        $monitoring = PlantingMonitoring::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Monitoring record created successfully.',
            'data' => new PlantingMonitoringResource($monitoring->load('request.agency')),
        ], 201);
    }

    public function show(PlantingMonitoring $planting_monitoring): JsonResponse
    {
        $this->authorize('view', $planting_monitoring);

        return response()->json([
            'success' => true,
            'data' => new PlantingMonitoringResource($planting_monitoring->load('request.agency')),
        ]);
    }

    public function update(UpdatePlantingMonitoringRequest $formRequest, PlantingMonitoring $planting_monitoring): JsonResponse
    {
        $this->authorize('update', $planting_monitoring);

        $planting_monitoring->update($formRequest->validated());

        return response()->json([
            'success' => true,
            'message' => 'Monitoring record updated successfully.',
            'data' => new PlantingMonitoringResource($planting_monitoring->fresh()->load('request.agency')),
        ]);
    }

    public function destroy(PlantingMonitoring $planting_monitoring): JsonResponse
    {
        $this->authorize('delete', $planting_monitoring);

        $planting_monitoring->delete();

        return response()->json([
            'success' => true,
            'message' => 'Monitoring record moved to trash.',
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $monitoring = PlantingMonitoring::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $monitoring);

        $monitoring->restore();

        return response()->json([
            'success' => true,
            'message' => 'Monitoring record restored successfully.',
            'data' => new PlantingMonitoringResource($monitoring->fresh()->load('request.agency')),
        ]);
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $monitoring = PlantingMonitoring::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $monitoring);

        $monitoring->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Monitoring record permanently deleted.',
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $this->authorize('viewAny', PlantingMonitoring::class);

        $query = $this->filteredQuery($request, PlantingMonitoring::query());

        $pdf = $this->reportPdf->make($query, [
            'search' => $request->input('search'),
            'seedling_type' => $request->input('seedling_type'),
            'agency_id' => $request->filled('agency_id') ? $request->integer('agency_id') : null,
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ]);

        $filename = 'menro-planting-monitoring-report-'.now()->format('Y-m-d-His').'.pdf';
        $pdfBinary = $pdf->output();

        // Automatically store the generated report in the database (report_files table)
        $agencyId = $request->user()->effectiveAgencyId();
        $folder = null;
        if ($agencyId) {
            $folder = ReportFolder::query()->where('agency_id', $agencyId)->where('name', 'Area Data')->first()
                ?? ReportFolder::query()->where('agency_id', $agencyId)->first();
        }

        $reportFile = $this->fileService->storeGeneratedPdf(
            $folder?->id,
            $pdfBinary,
            $filename,
            $request->user()->id,
            'monitoring-pdf',
            'export:'.now()->timestamp
        );

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'X-Report-File-Id' => (string) $reportFile->id,
        ]);
    }

    private function filteredQuery(Request $request, Builder $query): Builder
    {
        $query = $query->with('request.agency')
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

        if ($request->filled('seedling_type')) {
            $query->where('seedling_type', 'like', '%' . $request->string('seedling_type') . '%');
        }

        if ($request->filled('agency_id')) {
            $agencyId = $request->integer('agency_id');
            $query->whereHas('request', fn ($rq) => $rq->where('agency_id', $agencyId));
        }

        if ($request->filled('request_id')) {
            $query->where('request_id', $request->integer('request_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_monitoring', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_monitoring', '<=', $request->date('date_to'));
        }

        if ($request->has('ids')) {
            $ids = $request->input('ids');
            if (is_string($ids)) {
                $ids = array_filter(array_map('trim', explode(',', $ids)));
            }
            if (is_array($ids) && count($ids) > 0) {
                $query->whereIn('id', $ids);
            }
        }

        return $query;
    }

    private function paginatedResponse(Builder $query, Request $request): JsonResponse
    {
        $totals = $this->computeTotals($query);

        if ($request->filled('limit')) {
            $items = $query->limit($request->integer('limit'))->get();

            return response()->json([
                'success' => true,
                'data' => PlantingMonitoringResource::collection($items),
                'meta' => ['totals' => $totals],
            ]);
        }

        $items = $query->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => PlantingMonitoringResource::collection($items),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'totals' => $totals,
            ],
        ]);
    }

    private function computeTotals(Builder $query): array
    {
        return $this->reportPdf->computeTotals($query);
    }
}
