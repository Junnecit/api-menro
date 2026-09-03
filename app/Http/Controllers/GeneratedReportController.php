<?php

namespace App\Http\Controllers;

use App\Http\Resources\GeneratedReportResource;
use App\Models\GeneratedReport;
use App\Support\PrivateStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GeneratedReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = GeneratedReport::with(['user', 'agency'])
            ->visibleTo($request->user())
            ->orderByDesc('generated_at')
            ->orderByDesc('id');

        if ($request->filled('report_type')) {
            $query->where('report_type', $request->string('report_type'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('filename', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('agency', fn ($aq) => $aq->where('name', 'like', "%{$search}%"));
            });
        }

        $reports = $query->paginate($request->integer('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => GeneratedReportResource::collection($reports),
            'meta' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    public function download(Request $request, int $id): StreamedResponse|JsonResponse
    {
        $report = GeneratedReport::visibleTo($request->user())->findOrFail($id);

        if (! $report->file_path || ! PrivateStorage::exists($report->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Report file not found or has been expired.',
            ], 404);
        }

        return PrivateStorage::download($report->file_path, $report->filename);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $report = GeneratedReport::visibleTo($request->user())->findOrFail($id);

        if ($report->file_path && PrivateStorage::exists($report->file_path)) {
            PrivateStorage::delete($report->file_path);
        }

        $report->delete();

        return response()->json([
            'success' => true,
            'message' => 'Report history entry deleted.',
        ]);
    }
}
