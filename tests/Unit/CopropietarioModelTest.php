<?php

namespace Tests\Unit;

use App\Models\Copropietario;
use App\Models\PersonaAutorizada;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests unitarios para el modelo Copropietario
 * 
 * Valida:
 * - Requisito 20: Protección contra Mass Assignment
 * - Requisito 29: Definición de Relaciones Eloquent
 */
class CopropietarioModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Copropietario fillable permite solo campos esperados
     * 
     * Valida Requisito 20.1: Campos fillable definidos correctamente
     */
    public function test_fillable_allows_only_expected_fields(): void
    {
        $expectedFillable = [
            'nombre_completo',
            'numero_departamento',
            'tipo',
            'telefono',
            'correo',
            'patente',
            'estacionamiento',
            'bodega',
            'propietario_id',
        ];

        $model = new Copropietario();
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
            'nombre_completo' => 'Test User',
            'numero_departamento' => '101',
            'tipo' => 'propietario',
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(5),
        ];

        $copropietario = new Copropietario($data);

        // Los campos protegidos no deben ser asignados
        $this->assertNull($copropietario->id);
        $this->assertNull($copropietario->created_at);
        $this->assertNull($copropietario->updated_at);

        // Los campos fillable sí deben ser asignados
        $this->assertEquals('Test User', $copropietario->nombre_completo);
        $this->assertEquals('101', $copropietario->numero_departamento);
        $this->assertEquals('propietario', $copropietario->tipo);
    }

    /**
     * Test: Relación arrendatarios retorna HasMany
     * 
     * Valida Requisito 29.1: Relación hasMany con arrendatarios
     */
    public function test_arrendatarios_relation_returns_has_many(): void
    {
        $copropietario = new Copropietario();
        $relation = $copropietario->arrendatarios();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertEquals('propietario_id', $relation->getForeignKeyName());
    }

    /**
     * Test: Relación propietarioPrincipal retorna BelongsTo
     * 
     * Valida Requisito 29.2: Relación belongsTo con propietario principal
     */
    public function test_propietario_principal_relation_returns_belongs_to(): void
    {
        $copropietario = new Copropietario();
        $relation = $copropietario->propietarioPrincipal();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals('propietario_id', $relation->getForeignKeyName());
    }

    /**
     * Test: Relación personasAutorizadas retorna HasMany
     * 
     * Valida Requisito 29.3: Relación hasMany con personas autorizadas
     */
    public function test_personas_autorizadas_relation_returns_has_many(): void
    {
        $copropietario = new Copropietario();
        $relation = $copropietario->personasAutorizadas();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertInstanceOf(PersonaAutorizada::class, $relation->getRelated());
    }

    /**
     * Test: Relaciones funcionan correctamente con datos reales
     * 
     * Valida Requisito 29.5, 29.6: Acceso a relaciones mediante Eloquent
     */
    public function test_relations_work_with_real_data(): void
    {
        // Crear propietario principal
        $propietario = Copropietario::create([
            'nombre_completo' => 'Juan Propietario',
            'numero_departamento' => '101',
            'tipo' => 'propietario',
        ]);

        // Crear arrendatario asociado
        $arrendatario = Copropietario::create([
            'nombre_completo' => 'Pedro Arrendatario',
            'numero_departamento' => '101',
            'tipo' => 'arrendatario',
            'propietario_id' => $propietario->id,
        ]);

        // Crear persona autorizada
        $personaAutorizada = PersonaAutorizada::create([
            'nombre_completo' => 'Ana Autorizada',
            'rut_pasaporte' => '12345678-9',
            'departamento' => '101',
            'copropietario_id' => $propietario->id,
        ]);

        // Verificar relaciones
        $this->assertCount(1, $propietario->arrendatarios);
        $this->assertEquals($arrendatario->id, $propietario->arrendatarios->first()->id);
        
        $this->assertEquals($propietario->id, $arrendatario->propietarioPrincipal->id);
        
        $this->assertCount(1, $propietario->personasAutorizadas);
        $this->assertEquals($personaAutorizada->id, $propietario->personasAutorizadas->first()->id);
    }
}
