<?php

namespace App\Http\Controllers;

use App\Http\Resources\AgencyResource;
use App\Http\Resources\RequestResource;
use App\Models\Agency;
use App\Models\Request as PlantingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        // Request KPIs are scoped to the current account (Super Admin sees all
        // via the ownedBy scope). Fresh queries per stat keep the counts and
        // the recent list independent. Operational = leaf/single plantings
        // (excludes shell parents that only group children).
        return response()->json([
            'success' => true,
            'data' => [
                'total_requests' => PlantingRequest::ownedBy($user)->operational()->count(),
                'pending_requests' => PlantingRequest::ownedBy($user)->operational()->where('status', 'Pending')->count(),
                'completed_requests' => PlantingRequest::ownedBy($user)->operational()->where('status', 'Completed')->count(),
                'agencies_count' => Agency::count(),
                'recent_requests' => RequestResource::collection(
                    PlantingRequest::ownedBy($user)
                        ->roots()
                        ->with([
                            'agency',
                            'user',
                            'children' => fn ($children) => $children
                                ->with(['agency', 'user'])
                                ->orderByDesc('request_date')
                                ->orderByDesc('id'),
                        ])
                        ->orderByDesc('request_date')
                        ->orderByDesc('id')
                        ->limit(5)
                        ->get()
                ),
                'agencies' => AgencyResource::collection(
                    Agency::withCount(['requests' => fn ($q) => $q->operational()])
                        ->orderBy('name')
                        ->limit(4)
                        ->get()
                ),
                'agency_comparison' => $this->agencyComparison(),
            ],
        ]);
    }

    /**
     * Top agencies by request volume with pending/completed breakdowns for
     * the Overview comparison panel.
     *
     * @return list<array{id:int,name:string,initials:?string,color:?string,total_requests:int,pending_requests:int,completed_requests:int,completion_rate:int}>
     */
    private function agencyComparison(int $limit = 10): array
    {
        // Count operational (leaf) requests only — exclude shell parents.
        $rows = Agency::query()
            ->leftJoin('requests', function ($join) {
                $join->on('agencies.id', '=', 'requests.agency_id')
                    ->whereNull('requests.deleted_at')
                    ->whereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('requests as child_requests')
                            ->whereColumn('child_requests.parent_id', 'requests.id')
                            ->whereNull('child_requests.deleted_at');
                    });
            })
            ->whereNull('agencies.deleted_at')
            ->groupBy('agencies.id', 'agencies.name', 'agencies.initials', 'agencies.color')
            ->orderByDesc(DB::raw('COUNT(requests.id)'))
            ->orderBy('agencies.name')
            ->limit($limit)
            ->get([
                'agencies.id',
                'agencies.name',
                'agencies.initials',
                'agencies.color',
                DB::raw('COUNT(requests.id) as total_requests'),
                DB::raw("SUM(CASE WHEN requests.status = 'Pending' THEN 1 ELSE 0 END) as pending_requests"),
                DB::raw("SUM(CASE WHEN requests.status = 'Completed' THEN 1 ELSE 0 END) as completed_requests"),
            ]);

        return $rows->map(function ($row) {
            $total = (int) $row->total_requests;
            $completed = (int) $row->completed_requests;

            return [
                'id' => (int) $row->id,
                'name' => $row->name,
                'initials' => $row->initials,
                'color' => $row->color,
                'total_requests' => $total,
                'pending_requests' => (int) $row->pending_requests,
                'completed_requests' => $completed,
                'completion_rate' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            ];
        })->all();
    }
}
