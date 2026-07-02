<?php

namespace App\Http\Controllers;

use App\Http\Resources\AgencyResource;
use App\Http\Resources\RequestResource;
use App\Models\Agency;
use App\Models\Request as PlantingRequest;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_requests' => PlantingRequest::count(),
                'pending_requests' => PlantingRequest::where('status', 'Pending')->count(),
                'completed_requests' => PlantingRequest::where('status', 'Completed')->count(),
                'agencies_count' => Agency::count(),
                'recent_requests' => RequestResource::collection(
                    PlantingRequest::with('agency')
                        ->orderByDesc('request_date')
                        ->orderByDesc('id')
                        ->limit(5)
                        ->get()
                ),
                'agencies' => AgencyResource::collection(
                    Agency::withCount('requests')->orderBy('name')->limit(4)->get()
                ),
            ],
        ]);
    }
}
