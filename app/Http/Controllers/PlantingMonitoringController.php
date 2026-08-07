<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlantingMonitoring\StorePlantingMonitoringRequest;
use App\Http\Requests\PlantingMonitoring\UpdatePlantingMonitoringRequest;
use App\Http\Resources\PlantingMonitoringResource;
use App\Models\PlantingMonitoring;
use App\Services\MonitoringReportPdfService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlantingMonitoringController extends Controller
{
    public function __construct(private MonitoringReportPdfService $reportPdf) {}

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
            'agency_id' => $request->filled('agency_id') ? $request->integer('agency_id') : null,
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ]);

        return $pdf->download('menro-planting-monitoring-report-'.now()->format('Y-m-d').'.pdf');
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
