<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RateLimitingLoggingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que el rate limiting registra en logs cuando se excede el límite.
     * Requisito 25.6: Registrar en logs los intentos que excedan los límites de tasa
     */
    public function test_rate_limit_exceeded_is_logged(): void
    {
        // Crear un usuario autenticado
        $user = User::factory()->create();
        $this->actingAs($user);

        // Configurar el mock de Log para capturar los mensajes
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) use ($user) {
                // Verificar que el mensaje es correcto
                if ($message !== 'Rate limit exceeded') {
                    return false;
                }

                // Verificar que el contexto contiene todos los campos requeridos
                return isset($context['ip'])
                    && isset($context['user_id'])
                    && isset($context['route'])
                    && isset($context['timestamp'])
                    && $context['user_id'] === $user->id;
            });

        // Hacer 11 peticiones para exceder el límite de 10 por minuto
        for ($i = 0; $i < 11; $i++) {
            $response = $this->post(route('copropietarios.store'), [
                'copropietarios' => [
                    [
                        'nombre_completo' => 'Test Copropietario ' . $i,
                        'numero_departamento' => 101,
                        'tipo' => 'Propietario',
                    ]
                ]
            ]);

            // La última petición debe ser rechazada con 429
            if ($i === 10) {
                $response->assertStatus(429);
            }
        }
    }

    /**
     * Test que el logging incluye la dirección IP del cliente.
     * Requisito 25.6: Registrar IP
     */
    public function test_rate_limit_log_includes_ip_address(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return isset($context['ip']) && !empty($context['ip']);
            });

        // Exceder el límite
        for ($i = 0; $i < 11; $i++) {
            $this->post(route('copropietarios.store'), [
                'copropietarios' => [
                    [
                        'nombre_completo' => 'Test ' . $i,
                        'numero_departamento' => 101,
                        'tipo' => 'Propietario',
                    ]
                ]
            ]);
        }
    }

    /**
     * Test que el logging incluye el usuario autenticado.
     * Requisito 25.6: Registrar usuario
     */
    public function test_rate_limit_log_includes_user_info(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);
        $this->actingAs($user);

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) use ($user) {
                return isset($context['user_id'])
                    && $context['user_id'] === $user->id
                    && isset($context['user_email'])
                    && $context['user_email'] === 'test@example.com';
            });

        // Exceder el límite
        for ($i = 0; $i < 11; $i++) {
            $this->post(route('copropietarios.store'), [
                'copropietarios' => [
                    [
                        'nombre_completo' => 'Test ' . $i,
                        'numero_departamento' => 101,
                        'tipo' => 'Propietario',
                    ]
                ]
            ]);
        }
    }

    /**
     * Test que el logging incluye la ruta/endpoint accedido.
     * Requisito 25.6: Registrar ruta
     */
    public function test_rate_limit_log_includes_route_info(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return isset($context['route'])
                    && str_contains($context['route'], 'copropietarios')
                    && isset($context['method'])
                    && $context['method'] === 'POST'
                    && isset($context['url']);
            });

        // Exceder el límite
        for ($i = 0; $i < 11; $i++) {
            $this->post(route('copropietarios.store'), [
                'copropietarios' => [
                    [
                        'nombre_completo' => 'Test ' . $i,
                        'numero_departamento' => 101,
                        'tipo' => 'Propietario',
                    ]
                ]
            ]);
        }
    }

    /**
     * Test que el logging incluye el timestamp del evento.
     * Requisito 25.6: Registrar timestamp
     */
    public function test_rate_limit_log_includes_timestamp(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return isset($context['timestamp']) && !empty($context['timestamp']);
            });

        // Exceder el límite
        for ($i = 0; $i < 11; $i++) {
            $this->post(route('copropietarios.store'), [
                'copropietarios' => [
                    [
                        'nombre_completo' => 'Test ' . $i,
                        'numero_departamento' => 101,
                        'tipo' => 'Propietario',
                    ]
                ]
            ]);
        }
    }

    /**
     * Test que el rate limiting en login también registra en logs.
     * Requisito 25.1, 25.6: Rate limiting en autenticación con logging
     */
    public function test_login_rate_limit_exceeded_is_logged(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Rate limit exceeded'
                    && isset($context['ip'])
                    && isset($context['route'])
                    && str_contains($context['route'], 'login');
            });

        // Hacer 6 intentos de login para exceder el límite de 5 por minuto
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post(route('login'), [
                'email' => 'test@example.com',
                'password' => 'password123'
            ]);

            // La última petición debe ser rechazada con 429
            if ($i === 5) {
                $response->assertStatus(429);
            }
        }
    }

    /**
     * Test que el rate limiting en personas autorizadas también registra en logs.
     * Requisito 25.3, 25.6: Rate limiting en creación de personas autorizadas con logging
     */
    public function test_persona_autorizada_rate_limit_exceeded_is_logged(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Rate limit exceeded'
                    && isset($context['route'])
                    && str_contains($context['route'], 'personas-autorizadas');
            });

        // Hacer 11 peticiones para exceder el límite de 10 por minuto
        for ($i = 0; $i < 11; $i++) {
            $response = $this->post(route('personas-autorizadas.store'), [
                'personas_autorizadas' => [
                    [
                        'nombre_completo' => 'Test Persona ' . $i,
                        'rut_pasaporte' => '12345678-' . $i,
                        'numero_departamento' => 101,
                    ]
                ]
            ]);

            if ($i === 10) {
                $response->assertStatus(429);
            }
        }
    }
}
