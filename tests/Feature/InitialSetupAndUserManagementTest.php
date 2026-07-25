<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InitialSetupAndUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_registration_creates_the_only_initial_administrator(): void
    {
        $response = $this->post('/install', [
            'email' => 'admin@example.com',
            'password' => 'Secure-password-123',
            'password_confirmation' => 'Secure-password-123',
        ]);

        $response->assertRedirect('/dashboard');
        $admin = User::firstOrFail();
        $this->assertTrue($admin->isAdmin());
        $this->assertAuthenticatedAs($admin);

        $this->post('/logout');
        $this->get('/install')->assertNotFound();
        $this->post('/install', [
            'email' => 'attacker@example.com',
            'password' => 'Secure-password-123',
            'password_confirmation' => 'Secure-password-123',
        ])->assertForbidden();
    }

    public function test_admin_can_create_another_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'email' => 'user@example.com',
            'password' => 'Secure-password-123',
            'password_confirmation' => 'Secure-password-123',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'user@example.com',
            'is_admin' => false,
        ]);
    }

    public function test_regular_user_cannot_manage_users(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin/users')->assertForbidden();
        $this->actingAs($user)->post('/admin/users', [
            'email' => 'other@example.com',
            'password' => 'Secure-password-123',
            'password_confirmation' => 'Secure-password-123',
        ])->assertForbidden();
    }
}
