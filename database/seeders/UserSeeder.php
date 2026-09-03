<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->first();

        // Drop legacy loose demo accounts — partner admins/planters come from TagoloanPartnerSeeder.
        User::withTrashed()
            ->whereIn('email', ['admin@example.com', 'user@example.com'])
            ->get()
            ->each(function (User $user) {
                $user->tokens()->delete();
                $user->forceDelete();
            });

        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'role_id' => $superAdminRole->id,
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ]
        );
    }
}
