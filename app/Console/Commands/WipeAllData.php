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

    protected $description = 'Hard-delete all operational and non-superadmin user data (keep roles & superadmin)';

    /** @var list<string> */
    private const OPS_TABLES = [
        'tree_reports',
        'tree_photos',
        'trees',
        'planting_monitorings',
        'requests',
        'agencies',
        'app_notifications',
        'user_push_tokens',
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
        'planting-request-docs',
        'tree-photos',
        'profile-photos',
    ];

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This will DELETE all agencies, requests, trees, reports, and non-superadmin users. Continue?')) {
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

            foreach (self::OPS_TABLES as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                }
            }

            // Find Super Admin user IDs to retain
            $superAdminRole = DB::table('roles')->where('slug', 'super-admin')->first();
            $superAdminIds = $superAdminRole
                ? DB::table('users')->where('role_id', $superAdminRole->id)->pluck('id')->all()
                : [];

            if (Schema::hasTable('personal_access_tokens')) {
                DB::table('personal_access_tokens')
                    ->when(! empty($superAdminIds), fn ($q) => $q->whereNotIn('tokenable_id', $superAdminIds))
                    ->delete();
            }

            if (Schema::hasTable('password_reset_tokens')) {
                DB::table('password_reset_tokens')->delete();
            }

            if (Schema::hasTable('sessions')) {
                DB::table('sessions')
                    ->when(! empty($superAdminIds), fn ($q) => $q->whereNotIn('user_id', $superAdminIds))
                    ->delete();
            }

            if (Schema::hasTable('users')) {
                DB::table('users')
                    ->when(! empty($superAdminIds), fn ($q) => $q->whereNotIn('id', $superAdminIds))
                    ->delete();
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
        $this->info('Super Admin account retained:');
        $this->line('  - Email: superadmin@example.com');

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
