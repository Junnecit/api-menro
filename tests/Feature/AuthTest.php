<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'initials' => 'TU',
            'agency_name' => 'Test Agency',
            'type' => 'NGO',
            'contact' => 'Test Contact',
            'agency_email' => 'agency@example.com',
            'phone' => '+63 917 000 0000',
            'region_code' => '100000000',
            'province_code' => '100430000',
            'municipality_code' => '100431400',
            'barangay_code' => '100431401',
            'region_name' => 'Region X',
            'province_name' => 'Misamis Oriental',
            'municipality_name' => 'Tagoloan',
            'barangay_name' => 'Poblacion',
            'custom_address' => 'Near the plaza',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.needs_verification', true)
            ->assertJsonPath('data.pending', true)
            ->assertJsonPath('data.token', null)
            ->assertJsonStructure(['success', 'data' => ['user', 'token', 'email']]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'phone' => '+63 917 000 0000',
            'status' => 'pending',
            'email_verified_at' => null,
        ]);
        $this->assertDatabaseHas('agencies', [
            'name' => 'Test Agency',
            'initials' => 'TU',
            'status' => 'Active',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user?->agency_id);
        $this->assertSame('admin', $user->role?->slug);
        $this->assertNull($user->email_verified_at);
        $this->assertStringContainsString('Near the plaza', $user->address);
        $this->assertStringContainsString('Poblacion', $user->address);
        $this->assertStringContainsString('Tagoloan', $user->address);
        $this->assertStringContainsString('Misamis Oriental', $user->address);
        $this->assertStringContainsString('Region X', $user->address);
    }

    public function test_user_can_login(): void
    {
        $role = Role::where('slug', 'user')->first();
        User::create([
            'role_id' => $role->id,
            'name' => 'Test User',
            'email' => 'login@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);

        $response = $this->withHeader('X-Client-Platform', 'mobile')
            ->postJson('/api/auth/login', [
                'email' => 'login@example.com',
                'password' => 'password',
            ]);

        $response->assertOk()
            ->assertJsonStructure(['success', 'data' => ['user', 'token']]);
    }

    public function test_authenticated_user_can_get_profile(): void
    {
        $user = $this->createUser();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_field_user_can_register_with_phone_and_date_of_birth(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $admin = User::create([
            'role_id' => $adminRole->id,
            'name' => 'Managing Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Field Worker',
            'email' => 'field@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
            'admin_id' => $admin->id,
            'phone' => '+639171234567',
            'date_of_birth' => '2000-06-15',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.needs_verification', true)
            ->assertJsonPath('data.email', 'field@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'field@example.com',
            'admin_id' => $admin->id,
            'phone' => '+639171234567',
        ]);

        $fieldUser = User::where('email', 'field@example.com')->first();
        $this->assertNotNull($fieldUser);
        $this->assertSame('2000-06-15', $fieldUser->date_of_birth?->format('Y-m-d'));
    }

    public function test_admin_verifying_registration_otp_activates_account_and_returns_token(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $admin = User::create([
            'role_id' => $adminRole->id,
            'name' => 'Pending Admin',
            'email' => 'pending_admin@example.com',
            'password' => Hash::make('password'),
            'status' => 'pending',
            'email_verified_at' => null,
        ]);

        $otpService = app(\App\Services\RegistrationOtpService::class);
        $code = $otpService->issue($admin->email);

        $response = $this->postJson('/api/auth/verify-registration-otp', [
            'email' => 'pending_admin@example.com',
            'code' => $code,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.pending', false)
            ->assertJsonPath('data.needs_verification', false);

        $this->assertNotNull($response->json('data.token'));

        $this->assertDatabaseHas('users', [
            'email' => 'pending_admin@example.com',
            'status' => 'active',
        ]);
        $this->assertNotNull($admin->fresh()->email_verified_at);
    }

    private function createUser(string $roleSlug = 'user'): User
    {
        $role = Role::where('slug', $roleSlug)->first();

        return User::create([
            'role_id' => $role->id,
            'name' => 'Test User',
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }
}
