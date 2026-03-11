<?php

namespace Tests\Unit;

use App\Helpers\AuditLogger;
use App\Models\AuditLog;
use App\Models\Copropietario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests unitarios para AuditLogger helper
 * 
 * Valida:
 * - Requisito 28: Auditoría de Operaciones Críticas
 */
class AuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear y autenticar un usuario para las pruebas
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /**
     * Test: AuditLogger captura todos los campos requeridos en logCreate
     * 
     * Valida Requisito 28.7: Campos requeridos en logs de auditoría
     */
    public function test_log_create_captures_all_required_fields(): void
    {
        $modelType = Copropietario::class;
        $modelId = 123;
        $newValues = [
            'nombre_completo' => 'Juan Pérez',
            'numero_departamento' => '101',
            'tipo' => 'propietario',
        ];

        AuditLogger::logCreate($modelType, $modelId, $newValues);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action' => 'create',
            'model_type' => $modelType,
            'model_id' => $modelId,
        ]);

        $log = AuditLog::latest()->first();
        
        // Verificar campos requeridos según Requisito 28.7
        $this->assertNotNull($log->user_id);
        $this->assertNotNull($log->action);
        $this->assertNotNull($log->model_type);
        $this->assertNotNull($log->model_id);
        $this->assertNotNull($log->ip_address);
        $this->assertNotNull($log->user_agent);
        $this->assertNotNull($log->created_at);
        
        // Verificar que new_values contiene los datos
        $this->assertEquals($newValues, $log->new_values);
        $this->assertNull($log->old_values);
    }

    /**
     * Test: AuditLogger captura todos los campos requeridos en logUpdate
     * 
     * Valida Requisito 28.7: Campos requeridos en logs de auditoría
     */
    public function test_log_update_captures_all_required_fields(): void
    {
        $modelType = Copropietario::class;
        $modelId = 456;
        $oldValues = [
            'nombre_completo' => 'Juan Pérez',
            'telefono' => '123456789',
        ];
        $newValues = [
            'nombre_completo' => 'Juan Pérez González',
            'telefono' => '987654321',
        ];

        AuditLogger::logUpdate($modelType, $modelId, $oldValues, $newValues);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action' => 'update',
            'model_type' => $modelType,
            'model_id' => $modelId,
        ]);

        $log = AuditLog::latest()->first();
        
        // Verificar que old_values y new_values están presentes
        $this->assertEquals($oldValues, $log->old_values);
        $this->assertEquals($newValues, $log->new_values);
    }

    /**
     * Test: AuditLogger captura todos los campos requeridos en logDelete
     * 
     * Valida Requisito 28.7: Campos requeridos en logs de auditoría
     */
    public function test_log_delete_captures_all_required_fields(): void
    {
        $modelType = Copropietario::class;
        $modelId = 789;
        $oldValues = [
            'nombre_completo' => 'Juan Pérez',
            'numero_departamento' => '101',
            'tipo' => 'propietario',
        ];

        AuditLogger::logDelete($modelType, $modelId, $oldValues);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action' => 'delete',
            'model_type' => $modelType,
            'model_id' => $modelId,
        ]);

        $log = AuditLog::latest()->first();
        
        // Verificar que old_values contiene los datos eliminados
        $this->assertEquals($oldValues, $log->old_values);
        $this->assertNull($log->new_values);
    }

    /**
     * Test: AuditLogger captura intentos no autorizados
     * 
     * Valida Requisito 28.5: Registro de intentos no autorizados
     */
    public function test_log_unauthorized_captures_attempt(): void
    {
        $action = 'delete';
        $modelType = Copropietario::class;
        $modelId = 999;

        AuditLogger::logUnauthorized($action, $modelType, $modelId);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action' => 'unauthorized',
            'model_type' => $modelType,
            'model_id' => $modelId,
        ]);

        $log = AuditLog::latest()->first();
        
        // Verificar que se registró la acción intentada
        $this->assertArrayHasKey('attempted_action', $log->new_values);
        $this->assertEquals($action, $log->new_values['attempted_action']);
    }

    /**
     * Test: AuditLogger formatea datos correctamente como JSON
     * 
     * Valida que los datos se almacenan en formato correcto
     */
    public function test_formats_data_correctly_as_json(): void
    {
        $modelType = Copropietario::class;
        $modelId = 100;
        $newValues = [
            'nombre_completo' => 'María López',
            'numero_departamento' => '202',
            'tipo' => 'arrendatario',
            'correo' => 'maria@example.com',
        ];

        AuditLogger::logCreate($modelType, $modelId, $newValues);

        $log = AuditLog::latest()->first();
        
        // Verificar que new_values es un array (Laravel lo deserializa automáticamente)
        $this->assertIsArray($log->new_values);
        $this->assertEquals($newValues, $log->new_values);
        
        // Verificar que cada campo está presente
        $this->assertEquals('María López', $log->new_values['nombre_completo']);
        $this->assertEquals('202', $log->new_values['numero_departamento']);
        $this->assertEquals('arrendatario', $log->new_values['tipo']);
        $this->assertEquals('maria@example.com', $log->new_values['correo']);
    }

    /**
     * Test: AuditLogger captura dirección IP correctamente
     * 
     * Valida Requisito 28.7: Captura de dirección IP
     */
    public function test_captures_ip_address_correctly(): void
    {
        // Simular una IP específica
        $this->app['request']->server->set('REMOTE_ADDR', '192.168.1.100');

        AuditLogger::logCreate(Copropietario::class, 1, ['test' => 'data']);

        $log = AuditLog::latest()->first();
        
        $this->assertNotNull($log->ip_address);
        $this->assertEquals('192.168.1.100', $log->ip_address);
    }

    /**
     * Test: AuditLogger captura user agent correctamente
     * 
     * Valida Requisito 28.7: Captura de user agent
     */
    public function test_captures_user_agent_correctly(): void
    {
        $userAgent = 'Mozilla/5.0 (Test Browser)';
        $this->app['request']->headers->set('User-Agent', $userAgent);

        AuditLogger::logCreate(Copropietario::class, 1, ['test' => 'data']);

        $log = AuditLog::latest()->first();
        
        $this->assertNotNull($log->user_agent);
        $this->assertEquals($userAgent, $log->user_agent);
    }

    /**
     * Test: AuditLogger maneja errores gracefully
     * 
     * Valida que el logger no falla si hay problemas al guardar
     */
    public function test_handles_errors_gracefully(): void
    {
        // Intentar loguear con un model_type muy largo que podría causar error
        $longModelType = str_repeat('A', 300);
        
        // No debería lanzar excepción
        try {
            AuditLogger::logCreate($longModelType, 1, ['test' => 'data']);
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('AuditLogger should handle errors gracefully');
        }
    }

    /**
     * Test: AuditLogger registra timestamp correctamente
     * 
     * Valida Requisito 28.7: Timestamp en logs
     */
    public function test_records_timestamp_correctly(): void
    {
        $beforeLog = now();
        
        AuditLogger::logCreate(Copropietario::class, 1, ['test' => 'data']);
        
        $afterLog = now();
        $log = AuditLog::latest()->first();
        
        $this->assertNotNull($log->created_at);
        $this->assertTrue($log->created_at->between($beforeLog, $afterLog));
    }

    /**
     * Test: AuditLogger puede loguear sin usuario autenticado
     * 
     * Valida que funciona incluso sin autenticación (ej: comandos artisan)
     */
    public function test_logs_without_authenticated_user(): void
    {
        // Cerrar sesión
        auth()->logout();

        AuditLogger::logCreate(Copropietario::class, 1, ['test' => 'data']);

        $log = AuditLog::latest()->first();
        
        $this->assertNull($log->user_id);
        $this->assertEquals('create', $log->action);
        $this->assertNotNull($log->ip_address);
    }

    /**
     * Test: AuditLogger diferencia entre tipos de acciones
     * 
     * Valida que cada método registra la acción correcta
     */
    public function test_differentiates_between_action_types(): void
    {
        AuditLogger::logCreate(Copropietario::class, 1, ['data' => 'create']);
        AuditLogger::logUpdate(Copropietario::class, 2, ['old' => 'data'], ['new' => 'data']);
        AuditLogger::logDelete(Copropietario::class, 3, ['data' => 'delete']);
        AuditLogger::logUnauthorized('view', Copropietario::class, 4);

        $logs = AuditLog::latest()->take(4)->get();
        
        $this->assertEquals('unauthorized', $logs[0]->action);
        $this->assertEquals('delete', $logs[1]->action);
        $this->assertEquals('update', $logs[2]->action);
        $this->assertEquals('create', $logs[3]->action);
    }
}
