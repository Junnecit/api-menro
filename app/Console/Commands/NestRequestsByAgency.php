<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Models\Request as PlantingRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NestRequestsByAgency extends Command
{
    protected $signature = 'requests:nest-by-agency
        {--dry-run : Preview changes without writing}';

    protected $description = 'For each agency with 2+ planting requests, keep one existing row as the anchor (prefer In Progress — keeps its document/seed) and nest the rest as sub-requests. Removes empty shell parents first.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Dry run — no changes will be saved.');
        }

        $shellsRemoved = $this->dissolveEmptyShells($dryRun);
        $linked = $this->nestAgencyGroups($dryRun);

        $this->info(sprintf(
            '%s %d empty shell(s), nested %d sub-request(s) under existing anchors.',
            $dryRun ? 'Would remove' : 'Removed',
            $shellsRemoved,
            $linked
        ));

        return self::SUCCESS;
    }

    /**
     * Unlink children from empty shell parents (no document, no trees), then delete the shells.
     */
    private function dissolveEmptyShells(bool $dryRun): int
    {
        $shells = PlantingRequest::query()
            ->roots()
            ->whereHas('children')
            ->whereNull('document_path')
            ->whereDoesntHave('trees')
            ->with('children')
            ->get();

        if ($shells->isEmpty()) {
            return 0;
        }

        foreach ($shells as $shell) {
            $this->line(sprintf(
                'Shell %s (#%d) — unlink %d child(ren) then delete',
                $shell->request_no,
                $shell->id,
                $shell->children->count()
            ));

            if ($dryRun) {
                continue;
            }

            DB::transaction(function () use ($shell) {
                PlantingRequest::query()
                    ->where('parent_id', $shell->id)
                    ->update(['parent_id' => null]);

                $shell->forceDelete();
            });
        }

        return $shells->count();
    }

    private function nestAgencyGroups(bool $dryRun): int
    {
        $agencyIds = PlantingRequest::query()
            ->roots()
            ->whereNotNull('agency_id')
            ->whereDoesntHave('children')
            ->select('agency_id')
            ->groupBy('agency_id')
            ->havingRaw('COUNT(*) >= 2')
            ->pluck('agency_id');

        if ($agencyIds->isEmpty()) {
            $this->info('No agencies need nesting (none with 2+ unparented root requests).');

            return 0;
        }

        $linkedChildren = 0;

        foreach ($agencyIds as $agencyId) {
            $agency = Agency::query()->find($agencyId);
            if (! $agency) {
                continue;
            }

            $group = PlantingRequest::query()
                ->roots()
                ->where('agency_id', $agencyId)
                ->whereDoesntHave('children')
                ->orderBy('request_date')
                ->orderBy('id')
                ->get();

            if ($group->count() < 2) {
                continue;
            }

            $anchor = $this->pickAnchor($group);
            $subs = $group->where('id', '!=', $anchor->id)->values();

            $this->line(sprintf(
                'Agency #%d %s — anchor %s [%s] (doc=%s, seed=%s), %d sub-request(s)',
                $agency->id,
                $agency->name,
                $anchor->request_no,
                $anchor->status,
                $anchor->document_path ? 'yes' : 'no',
                $anchor->seedling_draft['raw'] ?? $anchor->seedling_draft['species'][0] ?? 'none',
                $subs->count()
            ));

            foreach ($subs as $sub) {
                $this->line(sprintf('  sub %s [%s]', $sub->request_no, $sub->status));
            }

            if ($dryRun) {
                $linkedChildren += $subs->count();

                continue;
            }

            DB::transaction(function () use ($anchor, $subs, &$linkedChildren) {
                foreach ($subs as $sub) {
                    $sub->update(['parent_id' => $anchor->id]);
                }
                $linkedChildren += $subs->count();
            });
        }

        return $linkedChildren;
    }

    /**
     * Prefer In Progress so the list anchor keeps that row's document and seedling draft.
     *
     * @param  Collection<int, PlantingRequest>  $group
     */
    private function pickAnchor(Collection $group): PlantingRequest
    {
        $inProgress = $group->first(fn (PlantingRequest $r) => $r->status === 'In Progress');
        if ($inProgress) {
            return $inProgress;
        }

        $withDoc = $group->first(fn (PlantingRequest $r) => filled($r->document_path));
        if ($withDoc) {
            return $withDoc;
        }

        return $group->sortByDesc('id')->first();
    }
}
