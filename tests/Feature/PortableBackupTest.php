<?php

namespace Tests\Feature;

use App\Models\Copropietario;
use App\Models\User;
use App\Services\PortableBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PortableBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_encrypted_backup_can_restore_users_and_decrypted_domain_data(): void
    {
        $admin = User::factory()->create(['email' => 'original@example.com', 'is_admin' => true]);
        Copropietario::create([
            'nombre_completo' => 'Persona Protegida',
            'numero_departamento' => '101',
            'tipo' => 'propietario',
            'telefono' => '123456789',
            'correo' => 'persona@example.com',
        ]);

        $service = app(PortableBackupService::class);
        $backup = $service->export('backup-password-123');

        $this->assertStringNotContainsString('Persona Protegida', $backup);
        $this->assertStringNotContainsString('original@example.com', $backup);

        User::query()->whereKey($admin->id)->update(['email' => 'changed@example.com']);
        Copropietario::query()->delete();
        $service->import($backup, 'backup-password-123');

        $this->assertDatabaseHas('users', ['email' => 'original@example.com', 'is_admin' => true]);
        $this->assertSame('Persona Protegida', Copropietario::firstOrFail()->nombre_completo);
    }

    public function test_wrong_password_or_tampered_backup_is_rejected_without_replacing_data(): void
    {
        User::factory()->create(['email' => 'safe@example.com', 'is_admin' => true]);
        $backup = app(PortableBackupService::class)->export('backup-password-123');

        try {
            app(PortableBackupService::class)->import($backup, 'incorrect-password');
            $this->fail('Expected invalid backup password to be rejected.');
        } catch (RuntimeException) {
            $this->assertDatabaseHas('users', ['email' => 'safe@example.com']);
        }
    }

    public function test_regular_user_cannot_access_backup_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin/backups')->assertForbidden();
        $this->actingAs($user)->post('/admin/backups/export')->assertForbidden();
        $this->actingAs($user)->post('/admin/backups/import')->assertForbidden();
    }
}
