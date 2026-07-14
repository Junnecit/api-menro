<?php

namespace App\Console\Commands;

use App\Models\Agency;
use App\Models\PlantingMonitoring;
use App\Models\Request as PlantingRequest;
use App\Models\User;
use App\Support\TagoloanLocation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * One-time importer for the legacy MENRO workbook (2024-Tree-Planting-Data.xlsx).
 *
 * Reads the per-sheet CSVs produced by database/imports/export-workbook.ps1 and
 * loads them into agencies, requests and planting_monitorings. Safe to re-run:
 * existing imported requests are matched and updated instead of duplicated.
 */
class ImportLegacyPlanting extends Command
{
    protected $signature = 'import:legacy-planting
        {dir? : Folder containing the exported CSVs (defaults to storage/app/imports/legacy-planting)}
        {--dry-run : Parse and report only, write nothing}
        {--fresh : Force-delete previously imported rows before importing}';

    protected $description = 'One-time import of the legacy tree-planting workbook (agencies, requests, planting monitorings)';

    /** Cell values treated as empty. */
    private const NULL_MARKERS = ['', '-', 'no data', 'n/a', 'na', 'none'];

    private array $issues = [];

    private array $sheetStats = [];

    public function handle(): int
    {
        $dir = $this->argument('dir') ?: storage_path('app/imports/legacy-planting');
        $files = glob($dir.DIRECTORY_SEPARATOR.'*.csv') ?: [];

        if ($files === []) {
            $this->error("No CSV files found in {$dir}. Run database/imports/export-workbook.ps1 first.");

            return self::FAILURE;
        }

        $superAdmin = User::whereHas('role', fn ($q) => $q->where('slug', 'super-admin'))->firstOrFail();

        $plantings = [];
        foreach ($files as $file) {
            $plantings = array_merge($plantings, $this->parseSheet($file));
        }

        $barangays = config('tagoloan.barangays', []);
        foreach ($plantings as &$p) {
            [$p['barangay_code'], $p['location'], $p['custom_address']] = $this->resolveArea($p['area'], $p['sheet'], $barangays);
        }
        unset($p);

        [$agencyMap, $newAgencies] = $this->resolveAgencies($plantings);

        $this->report($plantings, $newAgencies);

        if ($this->option('dry-run')) {
            $this->info('Dry run — nothing written.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($plantings, $newAgencies, $agencyMap, $superAdmin) {
            $this->write($plantings, $newAgencies, $agencyMap, $superAdmin);
        });

        return self::SUCCESS;
    }

    // ---------------------------------------------------------------- parsing

    /** @return array<int, array> plantings parsed from one sheet CSV */
    private function parseSheet(string $file): array
    {
        $sheet = basename($file, '.csv');
        $stats = ['rows' => 0, 'plantings' => 0, 'continuations' => 0, 'skipped' => 0];

        $handle = fopen($file, 'r');
        $columns = null;   // logical field => csv index
        $plantings = [];
        $current = null;   // index into $plantings of the open planting

        while (($row = fgetcsv($handle)) !== false) {
            $stats['rows']++;
            $excelRow = $row[0] ?? '?';
            $cells = array_slice($row, 1);

            if ($columns === null) {
                if ($this->rowMatches($cells, '/DATE\s*PLANTED/i')) {
                    $next = fgetcsv($handle);
                    $stats['rows']++;
                    $columns = $this->mapHeaders($cells, $next === false ? [] : array_slice($next, 1));
                }

                continue; // everything above the header block is legend/decoration
            }

            $v = fn (string $field) => $this->cell($cells, $columns, $field);

            $datePlantedRaw = $v('date_planted');
            $monitoring = [
                'date' => $this->parseDate($v('date_monitoring')),
                'seedlings_planted' => $this->parseCount($v('seedlings_planted')),
                'replanted_count' => $this->parseCount($v('replanted')),
                'survived_count' => $this->parseCount($v('survived')),
                'died_count' => $this->parseCount($v('died')),
            ];
            $hasMonitoring = $v('date_monitoring') !== null
                || array_filter($monitoring, fn ($x) => $x !== null && ! $x instanceof Carbon) !== [];
            $hasAnything = $datePlantedRaw !== null || $hasMonitoring
                || $v('agency') !== null || $v('area') !== null || $v('seedling_type') !== null;

            if (! $hasAnything) {
                continue; // blank row
            }

            if ($datePlantedRaw !== null) {
                $datePlanted = $this->parseDate($datePlantedRaw);
                if ($datePlanted === null) {
                    $stats['skipped']++;
                    $this->issues[] = "[{$sheet}] row {$excelRow}: unparseable DATE PLANTED '{$datePlantedRaw}' — row skipped";

                    continue;
                }

                $plantings[] = [
                    'sheet' => $sheet,
                    'row' => $excelRow,
                    'date_planted' => $datePlanted,
                    'agency' => $v('agency'),
                    'area' => $v('area'),
                    'seedling_type' => $v('seedling_type'),
                    'monitorings' => $hasMonitoring ? [$monitoring] : [],
                ];
                $current = array_key_last($plantings);
                $stats['plantings']++;

                continue;
            }

            if ($hasMonitoring && $current !== null) {
                $plantings[$current]['monitorings'][] = $monitoring;
                // Continuation rows sometimes repeat/refine area or seedling type.
                $plantings[$current]['area'] ??= $v('area');
                $plantings[$current]['seedling_type'] ??= $v('seedling_type');
                $stats['continuations']++;

                continue;
            }

            $stats['skipped']++;
            $this->issues[] = "[{$sheet}] row {$excelRow}: unrecognized row — skipped";
        }
        fclose($handle);

        if ($columns === null) {
            $this->issues[] = "[{$sheet}] no 'DATE PLANTED' header found — sheet ignored";
        }

        foreach ($plantings as &$p) {
            $p = $this->collapseMonitorings($p, $stats);
        }
        unset($p);

        $this->sheetStats[$sheet] = $stats;

        return $plantings;
    }

    /** Map logical fields onto CSV column indexes using the two header rows. */
    private function mapHeaders(array $rowA, array $rowB): array
    {
        $patterns = [
            'date_planted' => '/DATE\s*PLANTED/i',
            'agency' => '/NAME\s*OF\s*PERSON/i',
            'area' => '/AREA\s*PLANTED/i',
            'seedling_type' => '/TYPE\s*OF\s*SEEDLING/i',
            'date_monitoring' => '/DATE\s*MO/i', // source sheets spell it "MOINITORING"
            'seedlings_planted' => '/NO\.?\s*OF\s*SEEDLING\s*PLANTED/i',
            'replanted' => '/RE-?\s*PLANT/i',
            'survived' => '/PLANT\s*SURVIVE\b/i',
            'died' => '/DIED/i',
        ];

        $map = [];
        foreach ([$rowA, $rowB] as $header) {
            foreach ($header as $i => $text) {
                foreach ($patterns as $field => $pattern) {
                    if (! isset($map[$field]) && $text !== '' && preg_match($pattern, $text)) {
                        $map[$field] = $i;
                    }
                }
            }
        }

        return $map;
    }

    /**
     * Keep the latest monitoring check-up (dated beats undated, ties keep file
     * order), coalescing counts backwards so e.g. a seedlings-planted figure
     * recorded only on the planting row is not lost.
     */
    private function collapseMonitorings(array $planting, array &$stats): array
    {
        $entries = $planting['monitorings'];
        unset($planting['monitorings']);

        usort($entries, function ($a, $b) {
            if ($a['date'] === null || $b['date'] === null) {
                return 0; // stable: undated entries keep file order
            }

            return $a['date'] <=> $b['date'];
        });

        $planting['discarded_monitorings'] = max(count($entries) - 1, 0);
        $stats['discarded'] = ($stats['discarded'] ?? 0) + $planting['discarded_monitorings'];

        $final = ['date' => null, 'seedlings_planted' => null, 'replanted_count' => null, 'survived_count' => null, 'died_count' => null];
        foreach ($entries as $entry) { // later (kept) entries overwrite earlier values
            foreach ($entry as $k => $val) {
                if ($val !== null) {
                    $final[$k] = $val;
                }
            }
        }

        $planting['monitoring'] = $final;

        return $planting;
    }

    private function rowMatches(array $cells, string $pattern): bool
    {
        foreach ($cells as $cell) {
            if ($cell !== '' && preg_match($pattern, $cell)) {
                return true;
            }
        }

        return false;
    }

    private function cell(array $cells, array $columns, string $field): ?string
    {
        if (! isset($columns[$field])) {
            return null;
        }

        $value = trim(preg_replace('/\s+/', ' ', (string) ($cells[$columns[$field]] ?? '')));

        return in_array(mb_strtolower($value), self::NULL_MARKERS, true) ? null : $value;
    }

    private function parseDate(?string $value): ?Carbon
    {
        if ($value === null) {
            return null;
        }

        foreach (['Y-m-d', 'F j, Y', 'j-M-y', 'j-M-Y', 'n/j/Y', 'n/j/y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->startOfDay();
            } catch (\Throwable) {
                // try next format
            }
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseCount(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $clean = str_replace([',', ' ', '%'], '', $value);
        if (! is_numeric($clean)) {
            return null;
        }

        return max((int) (float) $clean, 0);
    }

    // -------------------------------------------------------------- resolving

    /** @return array{0: ?string, 1: string, 2: ?string} [barangay_code, location, custom_address] */
    private function resolveArea(?string $area, string $sheet, array $barangays): array
    {
        $code = $this->matchBarangay($area, $barangays) ?? $this->matchBarangay($sheet, $barangays);

        if ($code !== null) {
            return [$code, TagoloanLocation::formatLocation($code), $area];
        }

        if ($area !== null) {
            $this->issues[] = "no barangay match for area '{$area}' — kept as custom address";

            return [null, $area, $area];
        }

        return [null, config('tagoloan.location_suffix'), null];
    }

    private function matchBarangay(?string $text, array $barangays): ?string
    {
        if ($text === null) {
            return null;
        }

        $norm = mb_strtolower($text);
        $norm = preg_replace('/\bsta\.?\s+/', 'santa ', $norm);
        $norm = preg_replace('/\bsto\.?\s+/', 'santo ', $norm);
        $norm = trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9]+/', ' ', $norm)));

        $names = array_map('mb_strtolower', $barangays);
        uasort($names, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a)); // longest first

        foreach ($names as $barangayCode => $name) {
            if (str_contains($norm, $name)) {
                return (string) $barangayCode;
            }
        }

        return null;
    }

    /** @return array{0: array<string, ?Agency>, 1: array<string, array>} [lowername => Agency|null, lowername => new attrs] */
    private function resolveAgencies(array $plantings): array
    {
        $existing = Agency::withTrashed()->get()->keyBy(fn (Agency $a) => mb_strtolower(trim($a->name)));

        $map = [];
        $new = [];
        foreach ($plantings as $p) {
            if ($p['agency'] === null) {
                continue;
            }
            $key = mb_strtolower($p['agency']);
            if (isset($map[$key]) || isset($new[$key])) {
                continue;
            }

            if ($existing->has($key)) {
                $map[$key] = $existing[$key];

                continue;
            }

            $name = $p['agency'];
            $isAcronym = ! str_contains($name, ' ') || mb_strlen($name) <= 6;
            $words = preg_split('/[\s\/]+/', $name, -1, PREG_SPLIT_NO_EMPTY);
            $initials = match (true) {
                mb_strlen($name) <= 5 => mb_strtoupper($name),
                count($words) > 1 => mb_strtoupper(mb_substr(implode('', array_map(fn ($w) => mb_substr($w, 0, 1), $words)), 0, 5)),
                default => mb_strtoupper(mb_substr($words[0], 0, 5)),
            };

            $new[$key] = [
                'initials' => $initials,
                'name' => $isAcronym ? mb_strtoupper($name) : mb_convert_case(mb_strtolower($name), MB_CASE_TITLE),
                'type' => 'Government Agency',
                'status' => 'Active',
            ];
        }

        return [$map, $new];
    }

    // -------------------------------------------------------------- reporting

    private function report(array $plantings, array $newAgencies): void
    {
        $this->table(
            ['Sheet', 'Rows read', 'Plantings', 'Continuations merged', 'Monitorings discarded', 'Skipped'],
            collect($this->sheetStats)->map(fn ($s, $sheet) => [
                $sheet, $s['rows'], $s['plantings'], $s['continuations'], $s['discarded'] ?? 0, $s['skipped'],
            ])->values()->all(),
        );

        if ($newAgencies !== []) {
            $this->info('Agencies to create:');
            $this->table(['Name', 'Initials'], collect($newAgencies)->map(fn ($a) => [$a['name'], $a['initials']])->values()->all());
        }

        if ($this->issues !== []) {
            $this->warn('Issues ('.count($this->issues).'):');
            foreach (array_slice($this->issues, 0, 40) as $issue) {
                $this->line('  - '.$issue);
            }
            if (count($this->issues) > 40) {
                $this->line('  … and '.(count($this->issues) - 40).' more');
            }
        }

        $this->info(sprintf(
            'Totals: %d plantings -> %d requests + monitorings, %d new agencies.',
            count($plantings), count($plantings), count($newAgencies),
        ));
    }

    // ---------------------------------------------------------------- writing

    private function write(array $plantings, array $newAgencies, array $agencyMap, User $superAdmin): void
    {
        if ($this->option('fresh')) {
            $old = PlantingRequest::withTrashed()
                ->where('user_id', $superAdmin->id)->where('status', 'Completed')->get();
            foreach ($old as $request) {
                $request->forceDelete(); // cascades to planting_monitorings
            }
            $this->warn("--fresh: force-deleted {$old->count()} previously imported requests.");
        }

        foreach ($newAgencies as $key => $attrs) {
            $agencyMap[$key] = Agency::create($attrs);
        }

        // Same numbering scheme as RequestController::generateRequestNo().
        $nextNo = (PlantingRequest::withTrashed()
            ->pluck('request_no')
            ->map(fn (string $no) => (int) preg_replace('/\D/', '', $no))
            ->max() ?? 0) + 1;

        // Ordinal-aware idempotency: the nth in-file occurrence of a natural key
        // consumes the nth existing imported row, so re-runs update in place.
        $existing = PlantingRequest::where('user_id', $superAdmin->id)
            ->where('status', 'Completed')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (PlantingRequest $r) => $this->naturalKey($r->request_date, $r->agency_id, $r->custom_address ?? $r->location));
        $consumed = [];

        $created = $updated = 0;
        foreach ($plantings as $p) {
            $agency = $p['agency'] !== null ? ($agencyMap[mb_strtolower($p['agency'])] ?? null) : null;
            $key = $this->naturalKey($p['date_planted'], $agency?->id, $p['custom_address'] ?? $p['location']);

            $ordinal = $consumed[$key] = ($consumed[$key] ?? 0) + 1;
            $request = $existing->get($key)?->values()->get($ordinal - 1);

            $attrs = [
                'user_id' => $superAdmin->id,
                'agency_id' => $agency?->id,
                'requester_name' => $agency?->name,
                'barangay_code' => $p['barangay_code'],
                'location' => $p['location'],
                'custom_address' => $p['custom_address'],
                'status' => 'Completed',
                'request_date' => $p['date_planted'],
            ];

            if ($request !== null) {
                $request->update($attrs);
                $updated++;
            } else {
                $request = PlantingRequest::create($attrs + [
                    'request_no' => '#'.str_pad((string) $nextNo++, 5, '0', STR_PAD_LEFT),
                ]);
                $created++;
            }

            $m = $p['monitoring'];
            PlantingMonitoring::updateOrCreate(['request_id' => $request->id], [
                'seedling_type' => $p['seedling_type'] ?? 'Unknown',
                'date_monitoring' => $m['date'],
                'seedlings_planted' => $m['seedlings_planted'] ?? 0,
                'replanted_count' => $m['replanted_count'] ?? 0,
                'survived_count' => $m['survived_count'] ?? 0,
                'died_count' => $m['died_count'] ?? 0,
            ]);
        }

        $this->info(sprintf(
            'Done: %d agencies created, %d requests created, %d requests updated, %d monitorings upserted.',
            count($newAgencies), $created, $updated, $created + $updated,
        ));
    }

    private function naturalKey(mixed $date, ?int $agencyId, ?string $address): string
    {
        $day = $date instanceof Carbon ? $date->toDateString() : (string) $date;

        return $day.'|'.($agencyId ?? '-').'|'.mb_strtolower(trim((string) $address));
    }
}
