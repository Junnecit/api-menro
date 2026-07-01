<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tree\StoreTreeRequest;
use App\Http\Requests\Tree\UpdateTreeRequest;
use App\Http\Resources\TreeResource;
use App\Models\Tree;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TreeController extends Controller
{
    private const RELATIONS = ['agency', 'inspector', 'recordedBy', 'photos'];

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Tree::class);

        $trees = Tree::with(self::RELATIONS)
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

        $tree = Tree::create([
            ...$request->safe()->except('photos'),
            'date_recorded' => now()->toDateString(),
            'recorded_by_id' => $request->user()->id,
        ]);

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

        $tree->update($request->validated());

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
