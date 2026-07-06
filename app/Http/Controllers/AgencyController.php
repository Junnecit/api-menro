<?php

namespace App\Http\Controllers;

use App\Http\Requests\Agency\StoreAgencyRequest;
use App\Http\Requests\Agency\UpdateAgencyRequest;
use App\Http\Resources\AgencyResource;
use App\Models\Agency;
use App\Support\PsgcLocation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgencyController extends Controller
{
    /**
     * Lightweight id/name list for pickers (e.g. linking an admin account to
     * the stakeholder they represent). No pagination, no policy gate — same
     * pattern as UserController::options().
     */
    public function options(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Agency::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Agency::class);

        return $this->collectionResponse(
            $this->filteredQuery($request, Agency::query())
        );
    }

    public function trash(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Agency::class);

        return $this->collectionResponse(
            $this->filteredQuery($request, Agency::onlyTrashed())
        );
    }

    public function store(StoreAgencyRequest $request): JsonResponse
    {
        $this->authorize('create', Agency::class);

        $agency = Agency::create($this->prepareData($request->validated()));

        return response()->json([
            'success' => true,
            'message' => 'Stakeholder created successfully.',
            'data' => new AgencyResource($agency->loadCount('requests')),
        ], 201);
    }

    public function show(Agency $agency): JsonResponse
    {
        $this->authorize('view', $agency);

        return response()->json([
            'success' => true,
            'data' => new AgencyResource($agency->loadCount('requests')),
        ]);
    }

    public function update(UpdateAgencyRequest $request, Agency $agency): JsonResponse
    {
        $this->authorize('update', $agency);

        $agency->update($this->prepareData($request->validated()));

        return response()->json([
            'success' => true,
            'message' => 'Stakeholder updated successfully.',
            'data' => new AgencyResource($agency->fresh()->loadCount('requests')),
        ]);
    }

    public function destroy(Agency $agency): JsonResponse
    {
        $this->authorize('delete', $agency);

        $agency->delete();

        return response()->json([
            'success' => true,
            'message' => 'Stakeholder moved to trash.',
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $agency = Agency::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $agency);

        $agency->restore();

        return response()->json([
            'success' => true,
            'message' => 'Stakeholder restored successfully.',
            'data' => new AgencyResource($agency->fresh()->loadCount('requests')),
        ]);
    }

    public function forceDestroy(int $id): JsonResponse
    {
        $agency = Agency::onlyTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $agency);

        if ($agency->requests()->exists() || $agency->trees()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot permanently delete a stakeholder with linked planting requests or trees.',
            ], 422);
        }

        $agency->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Stakeholder permanently deleted.',
        ]);
    }

    private function filteredQuery(Request $request, Builder $query): Builder
    {
        $query = $query->withCount('requests')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('custom_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return $query;
    }

    private function collectionResponse(Builder $query): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => AgencyResource::collection($query->get()),
        ]);
    }

    private function prepareData(array $data): array
    {
        return PsgcLocation::applyAddress($data);
    }
}
