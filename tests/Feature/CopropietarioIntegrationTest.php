<?php

namespace Tests\Feature;

use App\Models\Copropietario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de integración para flujo completo de Copropietario.
 * 
 * Valida los requisitos:
 * - Requisito 3: Registro de Copropietarios
 * - Requisito 7: Actualización de Copropietarios
 * - Requisito 8: Eliminación de Copropietarios
 * - Requisito 13: Relación entre Propietarios y Arrendatarios
 */
class CopropietarioIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test: crear propietario → crear arrendatario → verificar relación
     * 
     * Valida:
     * - Requisito 3: Registro de Copropietarios
     * - Requisito 13.1: Primer propietario como principal
     * - Requisito 13.2: Asociación automática de arrendatarios
     * 
     * @test
     */
    public function test_crear_propietario_y_arrendatario_verifica_relacion()
    {
        $this->actingAs($this->user);

        // Paso 1: Crear propietario
        $propietarioData = [
            'numero_departamento' => '101',
            'estacionamiento' => '10',
            'bodega' => '5',
            'copropietarios' => [
                [
                    'nombre_completo' => 'Juan Pérez Propietario',
                    'telefono' => '+56912345678',
                    'correo' => 'juan@example.com',
                    'patente' => 'AB1234',
                    'tipo' => 'propietario',
                ],
            ],
        ];

        $response = $this->post(route('copropietarios.store'), $propietarioData);
        $response->assertRedirect(route('copropietarios.index'));
        $response->assertSessionHas('success');

        // Verificar que el propietario fue creado
        $this->assertDatabaseHas('copropietarios', [
            'nombre_completo' => 'Juan Pérez Propietario',
            'numero_departamento' => '101',
            'tipo' => 'propietario',
            'propietario_id' => null, // Es el propietario principal
        ]);

        $propietario = Copropietario::where('nombre_completo', 'Juan Pérez Propietario')->first();
        $this->assertNotNull($propietario);
        $this->assertEquals('propietario', $propietario->tipo);
        $this->assertNull($propietario->propietario_id);

        // Paso 2: Crear arrendatario para el mismo departamento
        $arrendatarioData = [
            'numero_departamento' => '101',
            'estacionamiento' => '10',
            'bodega' => '5',
            'copropietarios' => [
                [
                    'nombre_completo' => 'María González Arrendataria',
                    'telefono' => '+56987654321',
                    'correo' => 'maria@example.com',
                    'patente' => 'CD5678',
                    'tipo' => 'arrendatario',
                ],
            ],
        ];

        $response = $this->post(route('copropietarios.store'), $arrendatarioData);
        $response->assertRedirect(route('copropietarios.index'));

        // Paso 3: Verificar relación
        $arrendatario = Copropietario::where('nombre_completo', 'María González Arrendataria')->first();
        $this->assertNotNull($arrendatario);
        $this->assertEquals('arrendatario', $arrendatario->tipo);
        $this->assertEquals($propietario->id, $arrendatario->propietario_id);

        // Verificar relación Eloquent
        $propietario->refresh();
        $this->assertCount(1, $propietario->arrendatarios);
        $this->assertEquals($arrendatario->id, $propietario->arrendatarios->first()->id);

        // Verificar relación inversa
        $this->assertNotNull($arrendatario->propietarioPrincipal);
        $this->assertEquals($propietario->id, $arrendatario->propietarioPrincipal->id);
    }

    /**
     * Test: crear copropietario → actualizar → verificar cambios
     * 
     * Valida:
     * - Requisito 3: Registro de Copropietarios
     * - Requisito 7: Actualización de Copropietarios
     * - Requisito 21: Validación en actualización
     * 
     * @test
     */
    public function test_crear_copropietario_actualizar_y_verificar_cambios()
    {
        $this->actingAs($this->user);

        // Paso 1: Crear copropietario
        $copropietario = Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Pedro Martínez Original',
            'numero_departamento' => '202',
            'telefono' => '+56911111111',
            'correo' => 'pedro.original@example.com',
            'patente' => 'EF1111',
        ]);

        $this->assertDatabaseHas('copropietarios', [
            'id' => $copropietario->id,
            'nombre_completo' => 'Pedro Martínez Original',
            'correo' => 'pedro.original@example.com',
        ]);

        // Paso 2: Actualizar copropietario
        $updateData = [
            'nombre_completo' => 'Pedro Martínez Actualizado',
            'numero_departamento' => '202',
            'tipo' => 'Propietario',
            'telefono' => '+56922222222',
            'correo' => 'pedro.actualizado@example.com',
            'patente' => 'EF2222',
            'estacionamiento' => '20',
            'bodega' => '10',
        ];

        $response = $this->put(route('copropietarios.update', $copropietario->id), $updateData);
        $response->assertRedirect(route('copropietarios.index'));
        $response->assertSessionHas('success');

        // Paso 3: Verificar cambios
        $copropietario->refresh();
        $this->assertEquals('Pedro Martínez Actualizado', $copropietario->nombre_completo);
        $this->assertEquals('+56922222222', $copropietario->telefono);
        $this->assertEquals('pedro.actualizado@example.com', $copropietario->correo);
        $this->assertEquals('EF2222', $copropietario->patente);
        $this->assertEquals('20', $copropietario->estacionamiento);
        $this->assertEquals('10', $copropietario->bodega);

        $this->assertDatabaseHas('copropietarios', [
            'id' => $copropietario->id,
            'nombre_completo' => 'Pedro Martínez Actualizado',
            'correo' => 'pedro.actualizado@example.com',
            'patente' => 'EF2222',
        ]);
    }

    /**
     * Test: crear copropietario → eliminar → verificar eliminación
     * 
     * Valida:
     * - Requisito 3: Registro de Copropietarios
     * - Requisito 8: Eliminación de Copropietarios
     * - Requisito 13.3: Eliminación en cascada de arrendatarios
     * 
     * @test
     */
    public function test_crear_copropietario_eliminar_y_verificar_eliminacion()
    {
        $this->actingAs($this->user);

        // Paso 1: Crear propietario con arrendatarios
        $propietario = Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Ana López Propietaria',
            'numero_departamento' => '303',
        ]);

        $arrendatario1 = Copropietario::factory()->arrendatario()->create([
            'nombre_completo' => 'Carlos Ruiz Arrendatario 1',
            'numero_departamento' => '303',
            'propietario_id' => $propietario->id,
        ]);

        $arrendatario2 = Copropietario::factory()->arrendatario()->create([
            'nombre_completo' => 'Laura Díaz Arrendataria 2',
            'numero_departamento' => '303',
            'propietario_id' => $propietario->id,
        ]);

        // Verificar que todos fueron creados
        $this->assertDatabaseHas('copropietarios', ['id' => $propietario->id]);
        $this->assertDatabaseHas('copropietarios', ['id' => $arrendatario1->id]);
        $this->assertDatabaseHas('copropietarios', ['id' => $arrendatario2->id]);
        $this->assertCount(2, $propietario->arrendatarios);

        // Paso 2: Intentar eliminar propietario (debería advertir sobre arrendatarios)
        $response = $this->delete(route('copropietarios.destroy', $propietario->id));
        
        // El sistema debe advertir que hay arrendatarios asociados
        $response->assertRedirect(route('copropietarios.index'));
        $response->assertSessionHas('error');

        // Verificar que el propietario NO fue eliminado
        $this->assertDatabaseHas('copropietarios', ['id' => $propietario->id]);

        // Paso 3: Eliminar arrendatarios primero
        $this->delete(route('copropietarios.destroy', $arrendatario1->id));
        $this->delete(route('copropietarios.destroy', $arrendatario2->id));

        // Verificar que los arrendatarios fueron eliminados
        $this->assertDatabaseMissing('copropietarios', ['id' => $arrendatario1->id]);
        $this->assertDatabaseMissing('copropietarios', ['id' => $arrendatario2->id]);

        // Paso 4: Ahora eliminar el propietario
        $response = $this->delete(route('copropietarios.destroy', $propietario->id));
        $response->assertRedirect(route('copropietarios.index'));
        $response->assertSessionHas('success');

        // Verificar que el propietario fue eliminado
        $this->assertDatabaseMissing('copropietarios', ['id' => $propietario->id]);
    }

    /**
     * Test: validación de datos al crear copropietario
     * 
     * Valida:
     * - Requisito 3.2: Nombre mínimo 5 caracteres
     * - Requisito 3.7: Validación de email
     * - Requisito 14: Validación de datos
     * 
     * @test
     */
    public function test_validacion_al_crear_copropietario()
    {
        $this->actingAs($this->user);

        // Nombre muy corto (menos de 5 caracteres)
        $response = $this->post(route('copropietarios.store'), [
            'numero_departamento' => '101',
            'copropietarios' => [
                [
                    'nombre_completo' => 'Ana', // Solo 3 caracteres
                    'tipo' => 'propietario',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('copropietarios.0.nombre_completo');

        // Email inválido
        $response = $this->post(route('copropietarios.store'), [
            'numero_departamento' => '101',
            'copropietarios' => [
                [
                    'nombre_completo' => 'Juan Pérez',
                    'correo' => 'email-invalido', // Email sin formato válido
                    'tipo' => 'propietario',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('copropietarios.0.correo');

        // Tipo inválido
        $response = $this->post(route('copropietarios.store'), [
            'numero_departamento' => '101',
            'copropietarios' => [
                [
                    'nombre_completo' => 'Juan Pérez',
                    'tipo' => 'inquilino', // Tipo no válido
                ],
            ],
        ]);

        $response->assertSessionHasErrors('copropietarios.0.tipo');
    }

    /**
     * Test: validación de datos al actualizar copropietario
     * 
     * Valida:
     * - Requisito 21: Validación en actualización
     * 
     * @test
     */
    public function test_validacion_al_actualizar_copropietario()
    {
        $this->actingAs($this->user);

        $copropietario = Copropietario::factory()->propietario()->create();

        // Nombre muy corto
        $response = $this->put(route('copropietarios.update', $copropietario->id), [
            'nombre_completo' => 'Ana', // Solo 3 caracteres
            'numero_departamento' => '101',
            'tipo' => 'Propietario',
        ]);

        $response->assertSessionHasErrors('nombre_completo');

        // Email inválido
        $response = $this->put(route('copropietarios.update', $copropietario->id), [
            'nombre_completo' => 'Juan Pérez',
            'numero_departamento' => '101',
            'tipo' => 'Propietario',
            'correo' => 'email-invalido',
        ]);

        $response->assertSessionHasErrors('correo');
    }
}
