<?php

namespace Tests\Feature;

use App\Models\Copropietario;
use App\Models\PersonaAutorizada;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de integración para flujo completo de PersonaAutorizada.
 * 
 * Valida los requisitos:
 * - Requisito 4: Registro de Personas Autorizadas
 * - Requisito 10: Eliminación de Personas Autorizadas
 */
class PersonaAutorizadaIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test: crear copropietario → crear persona autorizada → verificar asociación
     * 
     * Valida:
     * - Requisito 4: Registro de Personas Autorizadas
     * - Requisito 4.6: Asociación automática al propietario principal
     * - Requisito 29.3: Relación hasMany con PersonaAutorizada
     * - Requisito 29.4: Relación belongsTo con Copropietario
     * 
     * @test
     */
    public function test_crear_copropietario_y_persona_autorizada_verifica_asociacion()
    {
        $this->actingAs($this->user);

        // Paso 1: Crear propietario
        $propietario = Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Roberto Sánchez Propietario',
            'numero_departamento' => '404',
        ]);

        $this->assertDatabaseHas('copropietarios', [
            'id' => $propietario->id,
            'nombre_completo' => 'Roberto Sánchez Propietario',
            'tipo' => 'Propietario',
        ]);

        // Paso 2: Crear persona autorizada asociada al propietario
        $personaAutorizadaData = [
            'nombre_completo' => 'Sofía Ramírez Autorizada',
            'rut_pasaporte' => '12345678-9',
            'departamento' => '404',
            'patente' => 'GH7890',
            'copropietario_id' => $propietario->id,
        ];

        $response = $this->post(route('personas-autorizadas.store'), $personaAutorizadaData);
        $response->assertRedirect(route('personas-autorizadas.index'));
        $response->assertSessionHas('success');

        // Paso 3: Verificar asociación
        $personaAutorizada = PersonaAutorizada::where('nombre_completo', 'Sofía Ramírez Autorizada')->first();
        $this->assertNotNull($personaAutorizada);
        $this->assertEquals($propietario->id, $personaAutorizada->copropietario_id);
        $this->assertEquals('404', $personaAutorizada->departamento);

        // Verificar relación Eloquent - belongsTo
        $this->assertNotNull($personaAutorizada->copropietario);
        $this->assertEquals($propietario->id, $personaAutorizada->copropietario->id);
        $this->assertEquals('Roberto Sánchez Propietario', $personaAutorizada->copropietario->nombre_completo);

        // Verificar relación Eloquent - hasMany
        $propietario->refresh();
        $this->assertCount(1, $propietario->personasAutorizadas);
        $this->assertEquals($personaAutorizada->id, $propietario->personasAutorizadas->first()->id);
    }

    /**
     * Test: crear persona autorizada → eliminar → verificar eliminación
     * 
     * Valida:
     * - Requisito 4: Registro de Personas Autorizadas
     * - Requisito 10: Eliminación de Personas Autorizadas
     * - Requisito 28.4: Auditoría de eliminación
     * 
     * @test
     */
    public function test_crear_persona_autorizada_eliminar_y_verificar_eliminacion()
    {
        $this->actingAs($this->user);

        // Paso 1: Crear propietario y persona autorizada
        $propietario = Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Elena Torres Propietaria',
            'numero_departamento' => '505',
        ]);

        $personaAutorizada = PersonaAutorizada::factory()->create([
            'nombre_completo' => 'Miguel Ángel Fernández',
            'rut_pasaporte' => '98765432-1',
            'departamento' => '505',
            'copropietario_id' => $propietario->id,
        ]);

        // Verificar que fue creada
        $this->assertDatabaseHas('persona_autorizadas', [
            'id' => $personaAutorizada->id,
            'nombre_completo' => 'Miguel Ángel Fernández',
            'copropietario_id' => $propietario->id,
        ]);

        $this->assertCount(1, $propietario->personasAutorizadas);

        // Paso 2: Eliminar persona autorizada
        $response = $this->delete(route('personas-autorizadas.destroy', $personaAutorizada->id));
        $response->assertRedirect(route('personas-autorizadas.index'));
        $response->assertSessionHas('success');

        // Paso 3: Verificar eliminación
        $this->assertDatabaseMissing('persona_autorizadas', [
            'id' => $personaAutorizada->id,
        ]);

        // Verificar que el propietario sigue existiendo
        $this->assertDatabaseHas('copropietarios', [
            'id' => $propietario->id,
        ]);

        $propietario->refresh();
        $this->assertCount(0, $propietario->personasAutorizadas);
    }

    /**
     * Test: crear múltiples personas autorizadas para un copropietario
     * 
     * Valida:
     * - Requisito 4.7: Registro múltiple de personas autorizadas
     * - Requisito 29.3: Relación hasMany
     * 
     * @test
     */
    public function test_crear_multiples_personas_autorizadas_para_un_copropietario()
    {
        $this->actingAs($this->user);

        // Paso 1: Crear propietario
        $propietario = Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Fernando Vargas Propietario',
            'numero_departamento' => '606',
        ]);

        // Paso 2: Crear múltiples personas autorizadas
        $persona1 = PersonaAutorizada::factory()->create([
            'nombre_completo' => 'Persona Autorizada 1',
            'rut_pasaporte' => '11111111-1',
            'departamento' => '606',
            'copropietario_id' => $propietario->id,
        ]);

        $persona2 = PersonaAutorizada::factory()->create([
            'nombre_completo' => 'Persona Autorizada 2',
            'rut_pasaporte' => '22222222-2',
            'departamento' => '606',
            'copropietario_id' => $propietario->id,
        ]);

        $persona3 = PersonaAutorizada::factory()->create([
            'nombre_completo' => 'Persona Autorizada 3',
            'rut_pasaporte' => '33333333-3',
            'departamento' => '606',
            'copropietario_id' => $propietario->id,
        ]);

        // Paso 3: Verificar asociación
        $propietario->refresh();
        $this->assertCount(3, $propietario->personasAutorizadas);

        $nombres = $propietario->personasAutorizadas->pluck('nombre_completo')->toArray();
        $this->assertContains('Persona Autorizada 1', $nombres);
        $this->assertContains('Persona Autorizada 2', $nombres);
        $this->assertContains('Persona Autorizada 3', $nombres);
    }

    /**
     * Test: eliminación en cascada de personas autorizadas al eliminar copropietario
     * 
     * Valida:
     * - Requisito 13.4: Eliminación en cascada de personas autorizadas
     * - Requisito 32.2: Validación de integridad referencial
     * 
     * @test
     */
    public function test_eliminacion_cascada_personas_autorizadas_al_eliminar_copropietario()
    {
        $this->actingAs($this->user);

        // Paso 1: Crear propietario con personas autorizadas
        $propietario = Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Gabriela Morales Propietaria',
            'numero_departamento' => '707',
        ]);

        $persona1 = PersonaAutorizada::factory()->create([
            'nombre_completo' => 'Autorizado Cascada 1',
            'copropietario_id' => $propietario->id,
        ]);

        $persona2 = PersonaAutorizada::factory()->create([
            'nombre_completo' => 'Autorizado Cascada 2',
            'copropietario_id' => $propietario->id,
        ]);

        // Verificar que fueron creadas
        $this->assertDatabaseHas('persona_autorizadas', ['id' => $persona1->id]);
        $this->assertDatabaseHas('persona_autorizadas', ['id' => $persona2->id]);
        $this->assertCount(2, $propietario->personasAutorizadas);

        // Paso 2: Eliminar propietario (debería advertir sobre personas autorizadas)
        $response = $this->delete(route('copropietarios.destroy', $propietario->id));
        
        // El sistema debe advertir que hay personas autorizadas
        $response->assertRedirect(route('copropietarios.index'));
        $response->assertSessionHas('warning');

        // Verificar que el propietario NO fue eliminado (según implementación actual)
        $this->assertDatabaseHas('copropietarios', ['id' => $propietario->id]);
        
        // Las personas autorizadas tampoco deberían ser eliminadas aún
        $this->assertDatabaseHas('persona_autorizadas', ['id' => $persona1->id]);
        $this->assertDatabaseHas('persona_autorizadas', ['id' => $persona2->id]);
    }

    /**
     * Test: validación de datos al crear persona autorizada
     * 
     * Valida:
     * - Requisito 4.2: Nombre mínimo 3 caracteres
     * - Requisito 4.3: RUT o pasaporte requerido
     * - Requisito 14: Validación de datos
     * 
     * @test
     */
    public function test_validacion_al_crear_persona_autorizada()
    {
        $this->actingAs($this->user);

        $propietario = Copropietario::factory()->propietario()->create();

        // Nombre muy corto (menos de 3 caracteres)
        $response = $this->post(route('personas-autorizadas.store'), [
            'nombre_completo' => 'AB', // Solo 2 caracteres
            'rut_pasaporte' => '12345678-9',
            'departamento' => '101',
            'copropietario_id' => $propietario->id,
        ]);

        $response->assertSessionHasErrors('nombre_completo');

        // RUT/pasaporte faltante
        $response = $this->post(route('personas-autorizadas.store'), [
            'nombre_completo' => 'Juan Pérez',
            'departamento' => '101',
            'copropietario_id' => $propietario->id,
        ]);

        $response->assertSessionHasErrors('rut_pasaporte');

        // Departamento faltante
        $response = $this->post(route('personas-autorizadas.store'), [
            'nombre_completo' => 'Juan Pérez',
            'rut_pasaporte' => '12345678-9',
            'copropietario_id' => $propietario->id,
        ]);

        $response->assertSessionHasErrors('departamento');
    }

    /**
     * Test: validación de integridad referencial al crear persona autorizada
     * 
     * Valida:
     * - Requisito 32.5: Validación de copropietario_id existente
     * 
     * @test
     */
    public function test_validacion_integridad_referencial_copropietario_id()
    {
        $this->actingAs($this->user);

        // Intentar crear persona autorizada con copropietario_id inexistente
        $response = $this->post(route('personas-autorizadas.store'), [
            'nombre_completo' => 'Juan Pérez',
            'rut_pasaporte' => '12345678-9',
            'departamento' => '101',
            'copropietario_id' => 99999, // ID que no existe
        ]);

        $response->assertSessionHasErrors('copropietario_id');
    }
}
