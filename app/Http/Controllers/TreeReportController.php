<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Http\Requests\TreeReport\StoreTreeReportRequest;
use App\Http\Requests\TreeReport\UpdateTreeReportRequest;
use App\Http\Resources\TreeReportResource;
use App\Models\Agency;
use App\Models\Tree;
use App\Models\TreeReport;
use App\Services\TreeReportNotifier;
use App\Services\TreeReportPdfService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TreeReportController extends Controller
{
    private const RELATIONS = ['tree.photos', 'plantingRequest', 'agency', 'reportedBy', 'resolvedBy'];

    public function __construct(
        private TreeReportNotifier $notifier,
        private TreeReportPdfService $pdfService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TreeReport::class);

        $query = $this->buildFilterQuery($request);

        if ($request->filled('limit')) {
            $reports = $query->limit($request->integer('limit'))->get();

            return response()->json([
                'success' => true,
                'data' => TreeReportResource::collection($reports),
                'meta' => [
                    'total' => $reports->count(),
                ],
            ]);
        }

        $perPage = $request->integer('per_page', 15);
        $paginated = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => TreeReportResource::collection($paginated),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TreeReport::class);

        $user = $request->user();
        $query = TreeReport::ownedBy($user);

        if ($request->filled('agency_id')) {
            $query->where('agency_id', $request->integer('agency_id'));
        }

        $total = (clone $query)->count();
        $submitted = (clone $query)->where('status', 'submitted')->count();
        $underReview = (clone $query)->where('status', 'under_review')->count();
        $resolved = (clone $query)->where('status', 'resolved')->count();
        $critical = (clone $query)->where('severity', 'critical')->count();
        $high = (clone $query)->where('severity', 'high')->count();

        $byType = (clone $query)
            ->select('report_type', DB::raw('COUNT(*) as count'))
            ->groupBy('report_type')
            ->pluck('count', 'report_type')
            ->all();

        $bySeverity = (clone $query)
            ->select('severity', DB::raw('COUNT(*) as count'))
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->all();

        return response()->json([
            'success' => true,
            'data' => [
                'total_reports' => $total,
                'submitted' => $submitted,
                'under_review' => $underReview,
                'resolved' => $resolved,
                'critical' => $critical,
                'high' => $high,
                'by_type' => $byType,
                'by_severity' => $bySeverity,
            ],
        ]);
    }

    public function store(StoreTreeReportRequest $request): JsonResponse
    {
        $this->authorize('create', TreeReport::class);

        $clientUuid = $request->input('client_uuid');

        // Offline sync idempotency deduplication
        if ($clientUuid) {
            $existing = TreeReport::where('client_uuid', $clientUuid)->with(self::RELATIONS)->first();
            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Report created successfully.',
                    'data' => new TreeReportResource($existing),
                ], 200);
            }
        }

        $payload = $request->validated();
        $tree = null;

        if ($request->filled('tree_id')) {
            $tree = Tree::query()->find($request->integer('tree_id'));
            if ($tree) {
                $payload['agency_id'] = $payload['agency_id'] ?? $tree->agency_id;
                $payload['request_id'] = $payload['request_id'] ?? $tree->request_id;
                $payload['barangay'] = $payload['barangay'] ?? $tree->barangay;
                $payload['municipality'] = $payload['municipality'] ?? $tree->municipality;
                $payload['province'] = $payload['province'] ?? $tree->province;
                $payload['latitude'] = $payload['latitude'] ?? $tree->latitude;
                $payload['longitude'] = $payload['longitude'] ?? $tree->longitude;
                $payload['landmark'] = $payload['landmark'] ?? $tree->landmark;
            }
        }

        if (empty($payload['agency_id'])) {
            $payload['agency_id'] = $request->user()->effectiveAgencyId();
        }

        if (empty($payload['municipality'])) {
            $payload['municipality'] = 'Tagoloan';
        }

        if (empty($payload['province'])) {
            $payload['province'] = 'Misamis Oriental';
        }

        try {
            $report = TreeReport::create([
                ...$payload,
                'reported_by_id' => $request->user()->id,
                'status' => ReportStatus::Submitted,
            ]);
        } catch (QueryException $e) {
            if ($clientUuid && $e->getCode() === '23000') {
                $existing = TreeReport::where('client_uuid', $clientUuid)->with(self::RELATIONS)->first();
                if ($existing) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Report created successfully.',
                        'data' => new TreeReportResource($existing),
                    ], 200);
                }
            }
            throw $e;
        }

        $reportCode = sprintf('RPT-%s-%05d', now()->format('Y'), $report->id);
        $report->update(['report_code' => $reportCode]);

        $fresh = $report->fresh()->load(self::RELATIONS);

        // Notify admins about the new report
        $this->notifier->notifyAdminsOfNewReport($fresh, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Tree report submitted successfully.',
            'data' => new TreeReportResource($fresh),
        ], 201);
    }

    public function show(TreeReport $treeReport): JsonResponse
    {
        $this->authorize('view', $treeReport);

        return response()->json([
            'success' => true,
            'data' => new TreeReportResource($treeReport->load(self::RELATIONS)),
        ]);
    }

    public function update(UpdateTreeReportRequest $request, TreeReport $treeReport): JsonResponse
    {
        $this->authorize('update', $treeReport);

        $payload = $request->validated();

        // Check if status is transitioning to resolved
        if ($request->input('status') === 'resolved' && $treeReport->status?->value !== 'resolved') {
            $payload['resolved_by_id'] = $request->user()->id;
            $payload['resolved_at'] = now();
        }

        $treeReport->update($payload);

        $fresh = $treeReport->fresh()->load(self::RELATIONS);

        // Notify reporter of status update
        $this->notifier->notifyReporterOfStatusUpdate($fresh, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Tree report updated successfully.',
            'data' => new TreeReportResource($fresh),
        ]);
    }

    public function destroy(TreeReport $treeReport): JsonResponse
    {
        $this->authorize('delete', $treeReport);

        $treeReport->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tree report moved to trash.',
        ]);
    }

    public function trash(Request $request): JsonResponse
    {
        $this->authorize('viewAny', TreeReport::class);

        $trashed = TreeReport::onlyTrashed()
            ->with(self::RELATIONS)
            ->ownedBy($request->user())
            ->orderByDesc('deleted_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => TreeReportResource::collection($trashed),
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $report = TreeReport::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $report);

        $report->restore();

        return response()->json([
            'success' => true,
            'message' => 'Tree report restored successfully.',
            'data' => new TreeReportResource($report->fresh()->load(self::RELATIONS)),
        ]);
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $report = TreeReport::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $report);

        $report->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Tree report permanently deleted.',
        ]);
    }

    /**
     * Bulk & Filtered PDF Export
     */
    public function exportPdf(Request $request): Response
    {
        $this->authorize('viewAny', TreeReport::class);

        $query = $this->buildFilterQuery($request);

        $pdf = $this->pdfService->make($query, [
            'status' => $request->query('status'),
            'severity' => $request->query('severity'),
            'report_type' => $request->query('report_type'),
            'barangay' => $request->query('barangay'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'search' => $request->query('search'),
        ]);

        $ids = null;
        if ($request->has('ids')) {
            $rawIds = $request->input('ids');
            if (is_string($rawIds)) {
                $rawIds = array_filter(array_map('trim', explode(',', $rawIds)));
            }
            if (is_array($rawIds) && count($rawIds) > 0) {
                $ids = array_values(array_map('intval', $rawIds));
            }
        }

        $title = ($ids && count($ids) > 0)
            ? 'Tree Incident Reports Summary (' . count($ids) . ' Selected ' . (count($ids) === 1 ? 'Record' : 'Records') . ')'
            : 'Tree Incident & Damage Reports Summary';

        try {
            $filePath = 'generated-reports/' . $filename;
            \App\Support\PrivateStorage::put($filePath, $pdfBinary);

            \App\Models\GeneratedReport::create([
                'user_id' => $request->user()?->id,
                'agency_id' => $request->user()?->effectiveAgencyId(),
                'report_type' => 'tree_reports',
                'title' => $title,
                'filename' => $filename,
                'file_path' => $filePath,
                'file_size' => strlen($pdfBinary),
                'record_count' => $query->count(),
                'filters' => array_filter([
                    'ids' => $ids,
                    'status' => $request->query('status'),
                    'severity' => $request->query('severity'),
                    'report_type' => $request->query('report_type'),
                    'barangay' => $request->query('barangay'),
                    'date_from' => $request->query('date_from'),
                    'date_to' => $request->query('date_to'),
                    'search' => $request->query('search'),
                ], fn ($val) => $val !== null && $val !== '' && $val !== []),
                'generated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to log generated report: ' . $e->getMessage());
        }

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function buildFilterQuery(Request $request): Builder
    {
        $query = TreeReport::with(self::RELATIONS)
            ->ownedBy($request->user())
            ->status($request->query('status'))
            ->severity($request->query('severity'))
            ->reportType($request->query('report_type'))
            ->agency($request->integer('agency_id') ?: null)
            ->barangay($request->query('barangay'));

        if ($request->filled('tree_id')) {
            $query->where('tree_id', $request->integer('tree_id'));
        }

        if ($request->filled('request_id')) {
            $query->where('request_id', $request->integer('request_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('report_code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('landmark', 'like', "%{$search}%")
                    ->orWhereHas('tree', fn ($tq) => $tq->where('tree_code', 'like', "%{$search}%")->orWhere('species', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        // Support bulk selection array (ids[]=1&ids[]=2 or comma-separated ids)
        if ($request->has('ids')) {
            $ids = $request->input('ids');
            if (is_string($ids)) {
                $ids = array_filter(array_map('trim', explode(',', $ids)));
            }
            if (is_array($ids) && count($ids) > 0) {
                $query->whereIn('id', $ids);
            }
        }

        return $query->orderByDesc('created_at')->orderByDesc('id');
    }
}
