<?php

namespace Tests\Feature;

use App\Helpers\AuditLogger;
use App\Models\Copropietario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_actor_action_and_changed_values(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);
        AuditLogger::logUpdate(Copropietario::class, 10, ['nombre_completo' => 'Anterior'], ['nombre_completo' => 'Nuevo']);

        $this->get('/admin/audit-logs')
            ->assertOk()
            ->assertSee($admin->email)
            ->assertSee('Editó')
            ->assertSee('Anterior')
            ->assertSee('Nuevo');
    }

    public function test_regular_user_cannot_see_audit_logs(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin/audit-logs')->assertForbidden();
    }
}
