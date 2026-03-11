<?php

namespace Tests\Unit;

use App\Models\Copropietario;
use App\Models\PersonaAutorizada;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests unitarios para el modelo PersonaAutorizada
 * 
 * Valida:
 * - Requisito 20: Protección contra Mass Assignment
 * - Requisito 29: Definición de Relaciones Eloquent
 */
class PersonaAutorizadaModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: PersonaAutorizada fillable permite solo campos esperados
     * 
     * Valida Requisito 20.2: Campos fillable definidos correctamente
     */
    public function test_fillable_allows_only_expected_fields(): void
    {
        $expectedFillable = [
            'nombre_completo',
            'rut_pasaporte',
            'departamento',
            'patente',
            'copropietario_id',
        ];

        $model = new PersonaAutorizada();
        $actualFillable = $model->getFillable();

        sort($expectedFillable);
        sort($actualFillable);

        $this->assertEquals($expectedFillable, $actualFillable);
    }

    /**
     * Test: Campos protegidos no pueden ser asignados masivamente
     * 
     * Valida Requisito 20.3: Protección contra asignación de campos sensibles
     */
    public function test_protected_fields_cannot_be_mass_assigned(): void
    {
        $data = [
            'id' => 999,
            'nombre_completo' => 'Test Persona',
            'rut_pasaporte' => '12345678-9',
            'departamento' => '101',
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(5),
        ];

        $personaAutorizada = new PersonaAutorizada($data);

        // Los campos protegidos no deben ser asignados
        $this->assertNull($personaAutorizada->id);
        $this->assertNull($personaAutorizada->created_at);
        $this->assertNull($personaAutorizada->updated_at);

        // Los campos fillable sí deben ser asignados
        $this->assertEquals('Test Persona', $personaAutorizada->nombre_completo);
        $this->assertEquals('12345678-9', $personaAutorizada->rut_pasaporte);
        $this->assertEquals('101', $personaAutorizada->departamento);
    }

    /**
     * Test: Relación copropietario retorna BelongsTo
     * 
     * Valida Requisito 29.4: Relación belongsTo con Copropietario
     */
    public function test_copropietario_relation_returns_belongs_to(): void
    {
        $personaAutorizada = new PersonaAutorizada();
        $relation = $personaAutorizada->copropietario();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertInstanceOf(Copropietario::class, $relation->getRelated());
    }

    /**
     * Test: Relación funciona correctamente con datos reales
     * 
     * Valida Requisito 29.6: Acceso a copropietario mediante relación Eloquent
     */
    public function test_relation_works_with_real_data(): void
    {
        // Crear copropietario
        $copropietario = Copropietario::create([
            'nombre_completo' => 'Juan Propietario',
            'numero_departamento' => '101',
            'tipo' => 'propietario',
        ]);

        // Crear persona autorizada asociada
        $personaAutorizada = PersonaAutorizada::create([
            'nombre_completo' => 'Ana Autorizada',
            'rut_pasaporte' => '12345678-9',
            'departamento' => '101',
            'copropietario_id' => $copropietario->id,
        ]);

        // Verificar relación
        $this->assertNotNull($personaAutorizada->copropietario);
        $this->assertEquals($copropietario->id, $personaAutorizada->copropietario->id);
        $this->assertEquals('Juan Propietario', $personaAutorizada->copropietario->nombre_completo);
    }

    /**
     * Test: Campo rut_pasaporte está correctamente nombrado
     * 
     * Valida Requisito 26.1, 26.2: Consistencia en nombres de campos
     */
    public function test_rut_pasaporte_field_is_correctly_named(): void
    {
        $model = new PersonaAutorizada();
        $fillable = $model->getFillable();

        // Verificar que el campo se llama 'rut_pasaporte' (no 'rut', 'pasaporte', etc.)
        $this->assertContains('rut_pasaporte', $fillable);
        $this->assertNotContains('rut', $fillable);
        $this->assertNotContains('pasaporte', $fillable);
    }
}
