<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Role;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TreeRoleScopingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    private function makeUser(string $roleSlug, ?int $adminId = null, ?int $agencyId = null): User
    {
        $role = Role::where('slug', $roleSlug)->firstOrFail();

        return User::create([
            'role_id' => $role->id,
            'admin_id' => $adminId,
            'agency_id' => $agencyId,
            'name' => fake()->unique()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    private function makeTree(User $recordedBy, ?int $agencyId = null): Tree
    {
        return Tree::create([
            'species' => 'Narra',
            'common_name' => 'Narra',
            'status' => 'alive',
            'date_planted' => now()->toDateString(),
            'date_recorded' => now()->toDateString(),
            'latitude' => 8.5381,
            'longitude' => 124.7731,
            'recorded_by_id' => $recordedBy->id,
            'agency_id' => $agencyId,
        ]);
    }

    public function test_planter_can_view_own_trees_but_cannot_update_or_delete(): void
    {
        $admin = $this->makeUser('admin');
        $planter = $this->makeUser('user', adminId: $admin->id);
        $tree = $this->makeTree($planter);

        $token = $planter->createToken('test')->plainTextToken;

        // Planter can view their own tree
        $viewRes = $this->withToken($token)->getJson("/api/trees/{$tree->id}");
        $viewRes->assertOk();

        // Planter CANNOT update the tree
        $updateRes = $this->withToken($token)->putJson("/api/trees/{$tree->id}", [
            'status' => 'need_replacement',
            'species' => 'Narra',
        ]);
        $updateRes->assertForbidden();

        // Planter CANNOT delete the tree
        $deleteRes = $this->withToken($token)->deleteJson("/api/trees/{$tree->id}");
        $deleteRes->assertForbidden();
    }

    public function test_monitor_can_view_and_update_trees_planted_under_assigned_admin_but_cannot_create(): void
    {
        $admin = $this->makeUser('admin');
        $planter = $this->makeUser('user', adminId: $admin->id);
        $monitor = $this->makeUser('monitor', adminId: $admin->id);

        $treeByPlanter = $this->makeTree($planter);
        $treeByAdmin = $this->makeTree($admin);

        $token = $monitor->createToken('test')->plainTextToken;

        // Monitor can view trees planted by their assigned admin and their planter
        $this->withToken($token)->getJson("/api/trees/{$treeByPlanter->id}")->assertOk();
        $this->withToken($token)->getJson("/api/trees/{$treeByAdmin->id}")->assertOk();

        // Monitor CAN update trees planted under assigned admin
        $updateRes = $this->withToken($token)->putJson("/api/trees/{$treeByPlanter->id}", [
            'status' => 'need_replacement',
            'species' => 'Narra',
        ]);
        $updateRes->assertOk();

        // Monitor CANNOT create new trees
        $createRes = $this->withToken($token)->postJson('/api/trees', [
            'species' => 'Mahogany',
            'latitude' => 8.5381,
            'longitude' => 124.7731,
        ]);
        $createRes->assertForbidden();
    }

    public function test_plain_admin_cannot_promote_to_admin_only_super_admin_can(): void
    {
        $admin = $this->makeUser('admin');
        $superAdmin = $this->makeUser('super-admin');
        $targetUser = $this->makeUser('user', adminId: $admin->id);
        $adminRole = Role::where('slug', 'admin')->first();

        // Plain admin attempts to promote user to Admin -> Forbidden
        $adminToken = $admin->createToken('test')->plainTextToken;
        $resForbidden = $this->withToken($adminToken)->putJson("/api/users/{$targetUser->id}", [
            'role_id' => $adminRole->id,
        ]);
        $resForbidden->assertForbidden();

        // Super Admin promotes user to Admin -> Allowed
        auth()->forgetGuards();
        $superAdminToken = $superAdmin->createToken('test')->plainTextToken;
        $resAllowed = $this->withToken($superAdminToken)->putJson("/api/users/{$targetUser->id}", [
            'role_id' => $adminRole->id,
        ]);
        $resAllowed->assertOk();
        $this->assertSame('admin', $targetUser->fresh()->role->slug);
    }

    public function test_plain_admin_cannot_create_admin(): void
    {
        $admin = $this->makeUser('admin');
        $adminRole = Role::where('slug', 'admin')->first();
        $token = $admin->createToken('test')->plainTextToken;

        $res = $this->withToken($token)->postJson('/api/users', [
            'name' => 'Direct Admin',
            'email' => 'directadmin@example.com',
            'password' => 'Password123!',
            'role_id' => $adminRole->id,
            'status' => 'active',
        ]);

        $res->assertForbidden()
            ->assertJsonPath('message', 'Admin accounts must be a Super Admin promotion only.');
    }
}
