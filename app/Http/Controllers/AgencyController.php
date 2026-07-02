<?php

namespace App\Http\Controllers;

use App\Http\Resources\AgencyResource;
use App\Models\Agency;
use Illuminate\Http\JsonResponse;

class AgencyController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => AgencyResource::collection(
                Agency::withCount('requests')->orderBy('name')->get()
            ),
        ]);
    }
}
