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
            // 'admin' slug (so it retains the full admin interface + powers),
            // but is presented as "Stakeholder" throughout the app.
            ['name' => 'Stakeholder', 'slug' => 'admin'],
            ['name' => 'User', 'slug' => 'user'],
            ['name' => 'Other', 'slug' => 'other'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
