<?php

namespace App\Http\Controllers;

use App\Http\Resources\RequestResource;
use App\Models\Request as PlantingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PlantingRequest::with('agency')
            ->orderByDesc('request_date')
            ->orderByDesc('id');

        if ($request->filled('limit')) {
            $query->limit($request->integer('limit'));
        }

        return response()->json([
            'success' => true,
            'data' => RequestResource::collection($query->get()),
        ]);
    }
}
