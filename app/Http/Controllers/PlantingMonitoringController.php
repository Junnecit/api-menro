<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlantingMonitoring\StorePlantingMonitoringRequest;
use App\Http\Requests\PlantingMonitoring\UpdatePlantingMonitoringRequest;
use App\Http\Resources\PlantingMonitoringResource;
use App\Models\PlantingMonitoring;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlantingMonitoringController extends Controller
{
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
        $totals = $this->computeTotals($query);
        $records = $query->get();

        $tempDir = storage_path('app/pdf-temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $pdf = Pdf::setOptions([
            // sys_get_temp_dir() can resolve to an unwritable system path
            // (e.g. when TMP/TEMP env vars aren't set for the serving
            // process), which silently drops any embedded image with no
            // visible error. Point dompdf at a directory we know Laravel
            // can write to. mergeWithDefaults=true keeps the rest of the
            // package's default options (fonts, chroot, etc.) intact.
            'temp_dir' => $tempDir,
        ], true)->loadView('reports.planting-monitoring', [
            'records' => $records,
            'totals' => $totals,
            'generatedAt' => now(),
            'menroSealDataUri' => $this->imageToDataUri(public_path('images/menro-seal.png')),
            'provinceSealDataUri' => $this->imageToDataUri(public_path('images/province-seal.png')),
        ])->setPaper('legal', 'portrait');

        return $pdf->download('menro-planting-monitoring-report-'.now()->format('Y-m-d').'.pdf');
    }

    private function imageToDataUri(string $path): ?string
    {
        // dompdf's URL resolver misparses Windows drive-letter paths like
        // "C:/Users/..." (parse_url() reads "C" as the URI scheme), which
        // breaks its local-file and chroot handling. A base64 data URI
        // skips that resolution path entirely, so it works cross-platform.
        if (! file_exists($path)) {
            return null;
        }

        $type = pathinfo($path, PATHINFO_EXTENSION) ?: 'png';

        return 'data:image/'.$type.';base64,'.base64_encode(file_get_contents($path));
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
        // Use the base query builder (not the Eloquent builder) for the aggregate:
        // selectRaw() alongside the existing with('request.agency') eager-load would
        // otherwise try to hydrate a PlantingMonitoring model missing its primary key.
        // reorder() drops the list ordering, which is meaningless on a single-row aggregate.
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
}
