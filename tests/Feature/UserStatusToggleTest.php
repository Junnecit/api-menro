<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserStatusToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_admin_can_toggle_managed_user_status_to_inactive_and_active(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $planterRole = Role::where('slug', 'user')->first();

        $admin = User::create([
            'role_id' => $adminRole->id,
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ]);

        $managedUser = User::create([
            'role_id' => $planterRole->id,
            'admin_id' => $admin->id,
            'name' => 'Field Planter',
            'email' => 'planter@test.com',
            'password' => Hash::make('password'),
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ]);

        $token = $admin->createToken('test')->plainTextToken;

        // 1. Disable user
        $response = $this->withToken($token)->putJson("/api/users/{$managedUser->id}", [
            'status' => 'inactive',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'inactive');

        $this->assertEquals(UserStatus::Inactive, $managedUser->fresh()->status);
        $this->assertTrue($managedUser->fresh()->relogin_required);
        $this->assertEquals('account_disabled', $managedUser->fresh()->relogin_reason);

        // 2. Activate user
        $activateResponse = $this->withToken($token)->putJson("/api/users/{$managedUser->id}", [
            'status' => 'active',
        ]);

        $activateResponse->assertOk()
            ->assertJsonPath('data.status', 'active');

        $this->assertEquals(UserStatus::Active, $managedUser->fresh()->status);
    }
}
