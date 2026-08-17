<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'teacher@example.com',
            'password' => 'password',
            'role' => 'teacher',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'teacher@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonPath('user.email', 'teacher@example.com')
            ->assertJsonPath('user.role', 'teacher')
            ->assertJsonStructure(['token']);
    }

    public function test_login_response_includes_role_and_permissions(): void
    {
        User::factory()->create([
            'email' => 'teacher@example.com',
            'password' => 'password',
            'role' => 'teacher',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'teacher@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.role', 'teacher');

        $this->assertContains('view_students', $response->json('user.permissions'));
        $this->assertContains('manage_exams', $response->json('user.permissions'));
    }

    public function test_invalid_password_returns_401(): void
    {
        User::factory()->create([
            'email' => 'teacher@example.com',
            'password' => 'password',
            'role' => 'teacher',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'teacher@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_login_never_exposes_the_password(): void
    {
        User::factory()->create([
            'email' => 'teacher@example.com',
            'password' => 'password',
            'role' => 'teacher',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'teacher@example.com',
            'password' => 'password',
        ]);

        $response->assertOk();

        $data = $response->json();

        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('password', $data['user']);
    }

    public function test_me_returns_role_and_permissions(): void
    {
        $admin = $this->createUser('admin');
        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/me');

        $response->assertOk()
            ->assertJsonPath('role', 'admin');

        $this->assertContains('manage_users', $response->json('permissions'));
    }
}
