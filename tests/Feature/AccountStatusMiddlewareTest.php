<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountStatusMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $roleSlug = 'user', UserStatus $status = UserStatus::Active): User
    {
        $role = Role::firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => ucfirst($roleSlug), 'description' => 'Test Role']
        );

        return User::create([
            'role_id' => $role->id,
            'name' => fake()->unique()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'status' => $status,
        ]);
    }

    public function test_active_user_can_access_authenticated_routes(): void
    {
        $user = $this->createUser();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_user_with_relogin_required_receives_account_updated_and_tokens_are_revoked(): void
    {
        $user = $this->createUser();
        $token = $user->createToken('test-token');

        $user->forceFill([
            'relogin_required' => true,
            'relogin_reason' => 'role_updated',
        ])->save();

        $response = $this->withToken($token->plainTextToken)
            ->getJson('/api/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'code' => 'ACCOUNT_UPDATED',
                'message' => 'Your account is updated and you are advised to re-login.',
            ]);

        // Tokens should be deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);

        // User flag should be cleared
        $this->assertFalse($user->fresh()->relogin_required);
    }

    public function test_disabled_user_receives_account_disabled_and_tokens_are_revoked(): void
    {
        $user = $this->createUser();
        $token = $user->createToken('test-token');

        // Admin disables account
        $user->forceFill([
            'status' => UserStatus::Inactive,
        ])->save();

        $response = $this->withToken($token->plainTextToken)
            ->getJson('/api/auth/me');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'code' => 'ACCOUNT_DISABLED',
            ]);

        // Tokens should be deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
        ]);
    }

    public function test_admin_updating_user_role_sets_relogin_required(): void
    {
        $admin = $this->createUser('admin');
        $fieldUser = $this->createUser('user');
        $fieldUser->forceFill(['admin_id' => $admin->id])->save();

        $monitorRole = Role::firstOrCreate(
            ['slug' => 'monitor'],
            ['name' => 'Monitor', 'description' => 'Monitoring role']
        );

        Sanctum::actingAs($admin);

        $response = $this->putJson("/api/users/{$fieldUser->id}", [
            'role_id' => $monitorRole->id,
        ]);

        $response->assertOk();

        $updated = $fieldUser->fresh();
        $this->assertSame($monitorRole->id, $updated->role_id);
        $this->assertTrue($updated->relogin_required);
        $this->assertSame('role_updated', $updated->relogin_reason);
    }
}
