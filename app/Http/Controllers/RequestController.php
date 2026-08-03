<?php

namespace App\Http\Controllers;

use App\Enums\PlantingHabitat;
use App\Http\Requests\PlantingRequest\AnalyzePlantingRequestDocumentRequest;
use App\Http\Requests\PlantingRequest\StorePlantingRequestRequest;
use App\Http\Requests\PlantingRequest\UpdatePlantingRequestRequest;
use App\Http\Resources\RequestResource;
use App\Models\Request as PlantingRequest;
use App\Services\PlantingRequestDocumentAnalyzer;
use App\Services\PlantingRequestDocumentService;
use App\Services\PlantingRequestTemplateService;
use App\Support\PrivateStorage;
use App\Support\TagoloanLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RequestController extends Controller
{
    public function __construct(
        private PlantingRequestDocumentService $documentService,
        private PlantingRequestTemplateService $templateService,
        private PlantingRequestDocumentAnalyzer $documentAnalyzer,
    ) {}

    public function documentTemplate(): Response
    {
        $this->authorize('viewAny', PlantingRequest::class);

        $binary = $this->templateService->buildDocxBinary();

        return response($binary, 200, [
            'Content-Type' => $this->templateService->mimeType(),
            'Content-Disposition' => 'attachment; filename="'.$this->templateService->filename().'"',
            'Content-Length' => (string) strlen($binary),
        ]);
    }

    public function analyzeDocument(AnalyzePlantingRequestDocumentRequest $request): JsonResponse
    {
        $this->authorize('create', PlantingRequest::class);

        $analysis = $this->documentAnalyzer->analyze($request->file('document'));

        return response()->json([
            'success' => true,
            'message' => $analysis['complete']
                ? 'All template components were found.'
                : 'Document analyzed — some template fields are missing.',
            'data' => $analysis,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PlantingRequest::class);

        return $this->paginatedResponse(
            $this->filteredQuery($request, PlantingRequest::query()),
            $request
        );
    }

    public function trash(Request $request): JsonResponse
    {
        $this->authorize('viewAny', PlantingRequest::class);

        return $this->paginatedResponse(
            $this->filteredQuery($request, PlantingRequest::onlyTrashed()),
            $request
        );
    }

    public function barangays(): JsonResponse
    {
        $this->authorize('viewAny', PlantingRequest::class);

        $barangays = collect(config('tagoloan.barangays', []))
            ->map(fn (string $name, string $code) => [
                'code' => $code,
                'name' => $name,
                'label' => TagoloanLocation::formatLocation($code),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => $barangays,
            'meta' => [
                'municipality_code' => config('tagoloan.municipality_code'),
                'location_suffix' => config('tagoloan.location_suffix'),
            ],
        ]);
    }

    public function store(StorePlantingRequestRequest $request): JsonResponse
    {
        $this->authorize('create', PlantingRequest::class);

        $data = TagoloanLocation::applyBarangay($request->safe()->except('document'));
        if (empty($data['request_no'])) {
            $data['request_no'] = $this->generateRequestNo();
        }

        $user = $request->user();

        // Stamp the creator so each account only sees the requests it owns.
        $data['user_id'] = $user->id;

        // Default agency to the submitter's agency so managed
        // field users only sync requests for their own agency.
        if (empty($data['agency_id'])) {
            $data['agency_id'] = $user->effectiveAgencyId();
        }

        // Admins can only submit — a request always starts as Pending. Only a
        // Super Admin may set a different status at creation time.
        $data['status'] = $user->isSuperAdmin()
            ? ($data['status'] ?? 'Pending')
            : 'Pending';

        $data['request_date'] = $data['request_date'] ?? now()->toDateString();
        $data['habitat'] = $data['habitat'] ?? PlantingHabitat::Terrestrial->value;

        // Document-only submissions do not collect barangay in the form; details
        // live in the uploaded file until a reviewer fills them in later.
        if (empty($data['location'])) {
            $data['location'] = 'See attached document';
        }

        $plantingRequest = PlantingRequest::create($data);
        $this->documentService->store($plantingRequest, $request->file('document'));

        return response()->json([
            'success' => true,
            'message' => 'Planting request created successfully.',
            'data' => new RequestResource($plantingRequest->fresh()->load(['agency', 'user'])),
        ], 201);
    }

    public function show(PlantingRequest $request): JsonResponse
    {
        $this->authorize('view', $request);

        return response()->json([
            'success' => true,
            'data' => new RequestResource($request->load(['agency', 'user'])),
        ]);
    }

    public function downloadDocument(PlantingRequest $request): StreamedResponse
    {
        $this->authorize('view', $request);

        return PrivateStorage::streamDownload(
            $request->document_path,
            $request->document_name ?: 'document',
            $request->document_mime,
        );
    }

    public function update(UpdatePlantingRequestRequest $formRequest, PlantingRequest $request): JsonResponse
    {
        $this->authorize('update', $request);

        $data = TagoloanLocation::applyBarangay($formRequest->safe()->except('document'));

        // Only a Super Admin may change a request's status (approve, reject,
        // etc.). Admins can edit their own request details but not its status.
        if (! $formRequest->user()->isSuperAdmin()) {
            unset($data['status']);
        }

        if (! empty($data)) {
            $request->update($data);
        }

        if ($formRequest->hasFile('document')) {
            $this->documentService->store($request, $formRequest->file('document'));
        }

        return response()->json([
            'success' => true,
            'message' => 'Planting request updated successfully.',
            'data' => new RequestResource($request->fresh()->load(['agency', 'user'])),
        ]);
    }

    public function destroy(PlantingRequest $request): JsonResponse
    {
        $this->authorize('delete', $request);

        $request->delete();

        return response()->json([
            'success' => true,
            'message' => 'Planting request moved to trash.',
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $plantingRequest = PlantingRequest::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $plantingRequest);

        $plantingRequest->restore();

        return response()->json([
            'success' => true,
            'message' => 'Planting request restored successfully.',
            'data' => new RequestResource($plantingRequest->fresh()->load(['agency', 'user'])),
        ]);
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $plantingRequest = PlantingRequest::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $plantingRequest);

        $this->documentService->deleteFile($plantingRequest);
        $plantingRequest->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Planting request permanently deleted.',
        ]);
    }

    /**
     * User IDs whose planting requests a mobile user may plant against.
     * Null means no ownership filter (Super Admin sees all).
     *
     * @return list<int>|null
     */
    private function plantingPoolUserIds($user): ?array
    {
        if ($user->isSuperAdmin()) {
            return null;
        }

        return $user->agencyPoolUserIds();
    }

    private function filteredQuery(Request $request, Builder $query): Builder
    {
        $user = $request->user();

        $query = $query->with(['agency', 'user'])
            ->orderByDesc('request_date')
            ->orderByDesc('id');

        // Mobile field users need Approved/In Progress requests from their
        // admin's agency pool (not only their own submissions).
        if ($request->boolean('for_planting')) {
            $poolIds = $this->plantingPoolUserIds($user);
            $agencyId = $user->effectiveAgencyId();

            if ($poolIds !== null) {
                if ($agencyId) {
                    // Own agency only: agency-tagged requests, or untagged ones
                    // submitted by someone in the admin pool.
                    $query->where(function ($q) use ($poolIds, $agencyId) {
                        $q->where('agency_id', $agencyId)
                            ->orWhere(function ($inner) use ($poolIds) {
                                $inner->whereNull('agency_id')->whereIn('user_id', $poolIds);
                            });
                    });
                } else {
                    $query->whereIn('user_id', $poolIds);
                }
            }

            if ($request->filled('status')) {
                $query->where('status', $request->string('status'));
            } else {
                $query->whereIn('status', ['Approved', 'In Progress']);
            }
        } else {
            $query->ownedBy($user);

            if ($request->filled('status')) {
                $query->where('status', $request->string('status'));
            }
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('request_no', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('requester_name', 'like', "%{$search}%")
                    ->orWhere('custom_address', 'like', "%{$search}%")
                    ->orWhere('document_name', 'like', "%{$search}%")
                    ->orWhereHas('agency', fn ($agencyQuery) => $agencyQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    private function paginatedResponse(Builder $query, Request $request): JsonResponse
    {
        if ($request->filled('limit')) {
            $items = $query->limit($request->integer('limit'))->get();

            return response()->json([
                'success' => true,
                'data' => RequestResource::collection($items),
            ]);
        }

        $items = $query->paginate($request->integer('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => RequestResource::collection($items),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    private function generateRequestNo(): string
    {
        $latestNumber = PlantingRequest::withTrashed()
            ->pluck('request_no')
            ->map(fn (string $no) => (int) preg_replace('/\D/', '', $no))
            ->max() ?? 0;

        return '#'.str_pad((string) ($latestNumber + 1), 5, '0', STR_PAD_LEFT);
    }
}
