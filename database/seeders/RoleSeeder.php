<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin'],
            // Self-registered web accounts land on this role. It keeps the
            // 'admin' slug (so it retains the full admin interface + powers).
            // Partner orgs are Agencies; this role may link to one via agency_id.
            ['name' => 'Admin', 'slug' => 'admin'],
            // Field planter: create trees, then read-only after upload.
            ['name' => 'Planter', 'slug' => 'user'],
            // Field monitor: agency-pool sync + edit; no tree create.
            ['name' => 'Monitor', 'slug' => 'monitor'],
            ['name' => 'Other', 'slug' => 'other'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
