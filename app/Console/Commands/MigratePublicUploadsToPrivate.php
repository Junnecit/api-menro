<?php

namespace App\Console\Commands;

use App\Support\PrivateStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigratePublicUploadsToPrivate extends Command
{
    protected $signature = 'storage:migrate-public-to-private
        {--dry-run : List files that would be moved without moving them}';

    protected $description = 'Copy sensitive upload directories from the public disk to the private (local) disk';

    /** @var list<string> */
    private const DIRS = [
        'agency-soil-docs',
        'planting-request-docs',
        'tree-photos',
        'report-files',
        'profile-photos',
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $public = Storage::disk('public');
        $private = Storage::disk(PrivateStorage::DISK);
        $moved = 0;
        $skipped = 0;

        foreach (self::DIRS as $dir) {
            if (! $public->exists($dir)) {
                $this->line("Skip missing public dir: {$dir}");
                continue;
            }

            foreach ($public->allFiles($dir) as $path) {
                if ($private->exists($path)) {
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("[dry-run] {$path}");
                    $moved++;
                    continue;
                }

                $private->put($path, $public->get($path));
                $moved++;
            }
        }

        $this->info(($dryRun ? 'Would move' : 'Moved')." {$moved} file(s); skipped {$skipped} already-private.");

        if (! $dryRun && $moved > 0) {
            $this->warn('Files remain on the public disk. After verifying downloads, delete public copies manually or with storage:link cleanup.');
        }

        return self::SUCCESS;
    }
}
