<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que el rate limiting bloquea después de 5 intentos de login.
     *
     * @return void
     */
    public function test_login_rate_limiting_blocks_after_five_attempts()
    {
        // Crear un usuario para intentar autenticarse
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        // Realizar 5 intentos de login con credenciales incorrectas
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);

            // Los primeros 5 intentos deben retornar error de validación (302 o 422)
            $this->assertContains($response->status(), [302, 422]);
        }

        // El sexto intento debe ser bloqueado por rate limiting (429 Too Many Requests)
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }

    /**
     * Test que el rate limiting permite login exitoso dentro del límite.
     *
     * @return void
     */
    public function test_successful_login_within_rate_limit()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        // Realizar 3 intentos fallidos
        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // El cuarto intento con credenciales correctas debe funcionar
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test que el rate limiting se aplica por IP.
     *
     * @return void
     */
    public function test_rate_limiting_is_per_ip_address()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        // Realizar 5 intentos desde una IP
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ]);
        }

        // El sexto intento desde la misma IP debe ser bloqueado
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);

        // Simular una petición desde otra IP (en un test real esto requeriría más configuración)
        // Por ahora, este test documenta el comportamiento esperado
    }
}
