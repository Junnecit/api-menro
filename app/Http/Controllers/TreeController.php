<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tree\StoreTreeRequest;
use App\Http\Requests\Tree\UpdateTreeRequest;
use App\Http\Resources\TreeResource;
use App\Models\Tree;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TreeController extends Controller
{
    private const RELATIONS = ['agency', 'inspector', 'recordedBy', 'updatedBy', 'photos'];

    // Photos are only needed when a single tree is opened, not for the map/list view.
    private const LIST_RELATIONS = ['agency', 'inspector', 'recordedBy', 'updatedBy'];

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Tree::class);

        // Photos are omitted from the list by default (the web map lazy-fetches
        // them per popup). The mobile app renders thumbnails in its Home/Tree
        // List, so it opts in with ?with_photos=1 to get them eagerly.
        $relations = self::LIST_RELATIONS;
        if ($request->boolean('with_photos')) {
            $relations[] = 'photos';
        }

        $trees = Tree::with($relations)
            ->ownedBy($request->user())
            ->status($request->query('status'))
            ->agency($request->integer('agency_id') ?: null)
            ->barangay($request->query('barangay'))
            ->get();

        return response()->json([
            'success' => true,
            'data' => TreeResource::collection($trees),
        ]);
    }

    public function store(StoreTreeRequest $request): JsonResponse
    {
        $this->authorize('create', Tree::class);

        $clientUuid = $request->input('client_uuid');

        if ($clientUuid) {
            $existing = Tree::where('client_uuid', $clientUuid)->with(self::RELATIONS)->first();

            if ($existing) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tree created successfully.',
                    'data' => new TreeResource($existing),
                ], 200);
            }
        }

        try {
            $tree = Tree::create([
                ...$request->safe()->except('photos'),
                'date_recorded' => now()->toDateString(),
                'recorded_by_id' => $request->user()->id,
            ]);
        } catch (QueryException $e) {
            // Another request with the same client_uuid won the race and
            // inserted first; return that row instead of a 500.
            if ($clientUuid && $e->getCode() === '23000') {
                $existing = Tree::where('client_uuid', $clientUuid)->with(self::RELATIONS)->first();
                if ($existing) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Tree created successfully.',
                        'data' => new TreeResource($existing),
                    ], 200);
                }
            }

            throw $e;
        }

        $tree->update(['tree_code' => sprintf('TGL-%s-%05d', now()->format('Y'), $tree->id)]);

        foreach ($request->file('photos', []) as $photo) {
            $tree->photos()->create([
                'path' => $photo->store("tree-photos/{$tree->id}", 'public'),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tree created successfully.',
            'data' => new TreeResource($tree->load(self::RELATIONS)),
        ], 201);
    }

    public function show(Tree $tree): JsonResponse
    {
        $this->authorize('view', $tree);

        return response()->json([
            'success' => true,
            'data' => new TreeResource($tree->load(self::RELATIONS)),
        ]);
    }

    public function update(UpdateTreeRequest $request, Tree $tree): JsonResponse
    {
        $this->authorize('update', $tree);

        $tree->update([
            ...$request->validated(),
            'updated_by_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tree updated successfully.',
            'data' => new TreeResource($tree->fresh()->load(self::RELATIONS)),
        ]);
    }

    public function destroy(Tree $tree): JsonResponse
    {
        $this->authorize('delete', $tree);

        $tree->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tree deleted successfully.',
        ]);
    }
}
