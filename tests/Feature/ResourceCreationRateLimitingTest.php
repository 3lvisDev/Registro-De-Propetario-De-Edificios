<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Copropietario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceCreationRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que el rate limiting bloquea creación de copropietarios después de 10 intentos.
     *
     * @return void
     */
    public function test_copropietario_creation_rate_limiting_blocks_after_ten_attempts()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Realizar 10 intentos de creación de copropietarios
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post('/copropietarios', [
                'copropietarios' => [
                    [
                        'nombre_completo' => "Test Copropietario {$i}",
                        'numero_departamento' => "10{$i}",
                        'tipo' => 'Propietario',
                        'telefono' => '123456789',
                        'correo' => "test{$i}@example.com",
                    ]
                ]
            ]);

            // Los primeros 10 intentos deben proceder (302 redirect o 201 created)
            $this->assertContains($response->status(), [200, 201, 302]);
        }

        // El intento 11 debe ser bloqueado por rate limiting (429 Too Many Requests)
        $response = $this->post('/copropietarios', [
            'copropietarios' => [
                [
                    'nombre_completo' => 'Test Copropietario 11',
                    'numero_departamento' => '111',
                    'tipo' => 'Propietario',
                ]
            ]
        ]);

        $response->assertStatus(429);
    }

    /**
     * Test que el rate limiting bloquea creación de personas autorizadas después de 10 intentos.
     *
     * @return void
     */
    public function test_persona_autorizada_creation_rate_limiting_blocks_after_ten_attempts()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Crear un copropietario para asociar las personas autorizadas
        $copropietario = Copropietario::factory()->create([
            'numero_departamento' => '101',
            'tipo' => 'Propietario',
        ]);

        // Realizar 10 intentos de creación de personas autorizadas
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post('/personas-autorizadas', [
                'nombre_completo' => "Persona Autorizada {$i}",
                'rut_pasaporte' => "12345678-{$i}",
                'numero_departamento' => '101',
                'copropietario_id' => $copropietario->id,
            ]);

            // Los primeros 10 intentos deben proceder (302 redirect o 201 created)
            $this->assertContains($response->status(), [200, 201, 302]);
        }

        // El intento 11 debe ser bloqueado por rate limiting (429 Too Many Requests)
        $response = $this->post('/personas-autorizadas', [
            'nombre_completo' => 'Persona Autorizada 11',
            'rut_pasaporte' => '12345678-X',
            'numero_departamento' => '101',
            'copropietario_id' => $copropietario->id,
        ]);

        $response->assertStatus(429);
    }

    /**
     * Test que el rate limiting se aplica por usuario autenticado.
     *
     * @return void
     */
    public function test_rate_limiting_is_per_authenticated_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Usuario 1 realiza 10 intentos
        $this->actingAs($user1);
        for ($i = 0; $i < 10; $i++) {
            $this->post('/copropietarios', [
                'copropietarios' => [
                    [
                        'nombre_completo' => "User1 Copropietario {$i}",
                        'numero_departamento' => "20{$i}",
                        'tipo' => 'Propietario',
                    ]
                ]
            ]);
        }

        // Usuario 1 debe ser bloqueado en el intento 11
        $response = $this->post('/copropietarios', [
            'copropietarios' => [
                [
                    'nombre_completo' => 'User1 Copropietario 11',
                    'numero_departamento' => '211',
                    'tipo' => 'Propietario',
                ]
            ]
        ]);
        $response->assertStatus(429);

        // Usuario 2 debe poder crear sin problemas (diferente usuario)
        $this->actingAs($user2);
        $response = $this->post('/copropietarios', [
            'copropietarios' => [
                [
                    'nombre_completo' => 'User2 Copropietario 1',
                    'numero_departamento' => '301',
                    'tipo' => 'Propietario',
                ]
            ]
        ]);
        $this->assertContains($response->status(), [200, 201, 302]);
    }

    /**
     * Test que otras operaciones CRUD no están afectadas por el rate limiting de creación.
     *
     * @return void
     */
    public function test_other_crud_operations_not_affected_by_creation_rate_limiting()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Crear un copropietario
        $copropietario = Copropietario::factory()->create([
            'numero_departamento' => '101',
            'tipo' => 'Propietario',
        ]);

        // Realizar 10 creaciones para alcanzar el límite
        for ($i = 0; $i < 10; $i++) {
            $this->post('/copropietarios', [
                'copropietarios' => [
                    [
                        'nombre_completo' => "Test Copropietario {$i}",
                        'numero_departamento' => "40{$i}",
                        'tipo' => 'Propietario',
                    ]
                ]
            ]);
        }

        // Verificar que el límite de creación está activo
        $response = $this->post('/copropietarios', [
            'copropietarios' => [
                [
                    'nombre_completo' => 'Test Copropietario 11',
                    'numero_departamento' => '411',
                    'tipo' => 'Propietario',
                ]
            ]
        ]);
        $response->assertStatus(429);

        // Pero otras operaciones (GET, PUT, DELETE) deben funcionar normalmente
        $response = $this->get('/copropietarios');
        $response->assertStatus(200);

        $response = $this->get("/copropietarios/{$copropietario->id}/edit");
        $response->assertStatus(200);

        $response = $this->put("/copropietarios/{$copropietario->id}", [
            'nombre_completo' => 'Nombre Actualizado',
            'numero_departamento' => '101',
            'tipo' => 'Propietario',
        ]);
        $this->assertContains($response->status(), [200, 302]);
    }
}
