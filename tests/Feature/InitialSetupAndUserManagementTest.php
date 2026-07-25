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
        $this->actingAs($user)->get('/admin/users/'.$user->id.'/edit')->assertForbidden();
        $this->actingAs($user)->put('/admin/users/'.$user->id, [
            'email' => 'changed@example.com',
        ])->assertForbidden();
        $this->actingAs($user)->delete('/admin/users/'.$user->id)->assertForbidden();
    }

    public function test_admin_can_edit_user_and_promote_it_to_administrator(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($admin)->put('/admin/users/'.$user->id, [
            'email' => 'promoted@example.com',
            'password' => 'New-secure-password-123',
            'password_confirmation' => 'New-secure-password-123',
            'is_admin' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $user->refresh();
        $this->assertSame('promoted@example.com', $user->email);
        $this->assertTrue($user->isAdmin());
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('New-secure-password-123', $user->password));
    }

    public function test_admin_can_delete_another_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($admin)->delete('/admin/users/'.$user->id)
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_itself_or_remove_the_last_admin_role(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->delete('/admin/users/'.$admin->id)
            ->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        $this->actingAs($admin)->put('/admin/users/'.$admin->id, [
            'email' => $admin->email,
        ])->assertSessionHasErrors('is_admin');
        $this->assertTrue($admin->fresh()->isAdmin());
    }
}
