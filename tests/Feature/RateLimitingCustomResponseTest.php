<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitingCustomResponseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Limpiar rate limiter antes de cada test
        RateLimiter::clear('login');
    }

    /**
     * Test que verifica que la respuesta 429 incluye el header Retry-After
     *
     * @return void
     */
    public function test_rate_limit_response_includes_retry_after_header(): void
    {
        // Crear usuario para autenticación
        $user = User::factory()->create();

        // Hacer múltiples solicitudes para exceder el límite
        // El límite de login es 5 intentos por minuto
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', [
                'email' => 'wrong@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // La última solicitud debe retornar 429
        $this->assertEquals(429, $response->status());
        
        // Verificar que incluye el header Retry-After
        $this->assertTrue($response->headers->has('Retry-After'));
        
        // El valor debe ser un número (segundos)
        $retryAfter = $response->headers->get('Retry-After');
        $this->assertIsNumeric($retryAfter);
        $this->assertGreaterThan(0, $retryAfter);
    }

    /**
     * Test que verifica que la vista personalizada 429 se renderiza correctamente
     *
     * @return void
     */
    public function test_custom_429_view_is_rendered(): void
    {
        // Hacer múltiples solicitudes para exceder el límite
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', [
                'email' => 'wrong@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // Verificar que la respuesta contiene elementos de la vista personalizada
        $response->assertStatus(429);
        $response->assertSee('429');
        $response->assertSee('Demasiadas Solicitudes');
        $response->assertSee('Tiempo de espera:');
        $response->assertSee('segundos');
    }

    /**
     * Test que verifica el contenido de ayuda en la vista 429
     *
     * @return void
     */
    public function test_custom_429_view_includes_helpful_information(): void
    {
        // Hacer múltiples solicitudes para exceder el límite
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', [
                'email' => 'wrong@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // Verificar que incluye información útil para el usuario
        $response->assertStatus(429);
        $response->assertSee('¿Qué puedes hacer?');
        $response->assertSee('Espera');
        $response->assertSee('Reintentar');
        $response->assertSee('Volver');
    }

    /**
     * Test que verifica que después del tiempo de espera se puede volver a intentar
     *
     * @return void
     */
    public function test_can_retry_after_rate_limit_expires(): void
    {
        // Hacer múltiples solicitudes para exceder el límite
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'wrong@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // La siguiente solicitud debe retornar 429
        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);
        $this->assertEquals(429, $response->status());

        // Limpiar el rate limiter (simula que pasó el tiempo)
        RateLimiter::clear('login');

        // Ahora debe permitir nuevas solicitudes
        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);
        
        // No debe ser 429 (será 302 redirect o 422 validation error)
        $this->assertNotEquals(429, $response->status());
    }
}
