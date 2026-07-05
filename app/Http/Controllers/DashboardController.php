<?php

namespace App\Http\Controllers;

use App\Http\Resources\AgencyResource;
use App\Http\Resources\RequestResource;
use App\Models\Agency;
use App\Models\Request as PlantingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        // Request KPIs are scoped to the current account (Super Admin sees all
        // via the ownedBy scope). Fresh queries per stat keep the counts and
        // the recent list independent.
        return response()->json([
            'success' => true,
            'data' => [
                'total_requests' => PlantingRequest::ownedBy($user)->count(),
                'pending_requests' => PlantingRequest::ownedBy($user)->where('status', 'Pending')->count(),
                'completed_requests' => PlantingRequest::ownedBy($user)->where('status', 'Completed')->count(),
                'agencies_count' => Agency::count(),
                'recent_requests' => RequestResource::collection(
                    PlantingRequest::ownedBy($user)
                        ->with('agency')
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
