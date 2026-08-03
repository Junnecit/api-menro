<?php

namespace App\Console\Commands;

use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class WipeAllData extends Command
{
    protected $signature = 'db:wipe-all
        {--force : Run without confirmation}';

    protected $description = 'Hard-delete all operational and user data (keep roles), then re-seed demo users';

    /** @var list<string> */
    private const OPS_TABLES = [
        'tree_photos',
        'trees',
        'planting_monitorings',
        'report_files',
        'report_folders',
        'requests',
        'agencies',
        'test_items',
    ];

    /** @var list<string> */
    private const AUTH_TABLES = [
        'personal_access_tokens',
        'password_reset_tokens',
        'sessions',
        'users',
    ];

    /** @var list<string> */
    private const STORAGE_DIRS = [
        'agency-soil-docs',
        'planting-request-docs',
        'tree-photos',
        'report-files',
        'profile-photos',
    ];

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will DELETE all agencies, requests, trees, reports, and users. Continue?')) {
            $this->warn('Aborted.');

            return self::FAILURE;
        }

        $before = $this->counts();

        DB::transaction(function () {
            if (Schema::hasColumn('users', 'agency_id')) {
                DB::table('users')->update(['agency_id' => null]);
            }
            if (Schema::hasColumn('users', 'admin_id')) {
                DB::table('users')->update(['admin_id' => null]);
            }

            foreach ([...self::OPS_TABLES, ...self::AUTH_TABLES] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                }
            }
        });

        $disk = Storage::disk(\App\Support\PrivateStorage::DISK);
        foreach (self::STORAGE_DIRS as $dir) {
            if ($disk->exists($dir)) {
                $disk->deleteDirectory($dir);
            }
        }

        // Also clear any leftover public copies from before the private-disk migration.
        $public = Storage::disk('public');
        foreach (self::STORAGE_DIRS as $dir) {
            if ($public->exists($dir)) {
                $public->deleteDirectory($dir);
            }
        }

        $this->callSilent('db:seed', ['--class' => RoleSeeder::class, '--force' => true]);
        $this->callSilent('db:seed', ['--class' => UserSeeder::class, '--force' => true]);

        $after = $this->counts();

        $this->info('Wipe complete. Row counts:');
        $this->table(
            ['table', 'before', 'after'],
            collect([...self::OPS_TABLES, ...self::AUTH_TABLES, 'roles'])
                ->filter(fn (string $table) => Schema::hasTable($table))
                ->map(fn (string $table) => [$table, $before[$table] ?? 0, $after[$table] ?? 0])
                ->all()
        );

        $this->newLine();
        $this->info('Demo logins (password: password):');
        $this->line('  - superadmin@example.com');
        $this->line('  - admin@example.com');
        $this->line('  - user@example.com');

        return self::SUCCESS;
    }

    /** @return array<string, int> */
    private function counts(): array
    {
        $tables = [...self::OPS_TABLES, ...self::AUTH_TABLES, 'roles'];
        $counts = [];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $counts[$table] = DB::table($table)->count();
            }
        }

        return $counts;
    }
}
