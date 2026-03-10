<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class DetectCommandInjectionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear un usuario para las pruebas autenticadas
        $this->user = User::factory()->create();
    }

    /**
     * Test que el middleware detecta caracteres de control de shell
     */
    public function test_detecta_caracteres_de_control_shell(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Intento sospechoso de inyección de comandos detectado'
                    && isset($context['field'])
                    && isset($context['value'])
                    && str_contains($context['value'], ';');
            });

        $response = $this->actingAs($this->user)
            ->post('/copropietarios', [
                'numero_departamento' => '101',
                'copropietarios' => [
                    [
                        'nombre_completo' => 'Test; rm -rf /',
                        'tipo' => 'propietario',
                    ]
                ]
            ]);

        // El middleware no debe bloquear la petición, solo registrar
        $this->assertTrue(true);
    }

    /**
     * Test que el middleware detecta sustitución de comandos
     */
    public function test_detecta_sustitucion_comandos(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Intento sospechoso de inyección de comandos detectado'
                    && str_contains($context['value'], '$(');
            });

        $response = $this->actingAs($this->user)
            ->post('/copropietarios', [
                'numero_departamento' => '102',
                'copropietarios' => [
                    [
                        'nombre_completo' => 'Test $(whoami)',
                        'tipo' => 'propietario',
                    ]
                ]
            ]);

        $this->assertTrue(true);
    }

    /**
     * Test que el middleware detecta backticks
     */
    public function test_detecta_backticks(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Intento sospechoso de inyección de comandos detectado'
                    && str_contains($context['value'], '`');
            });

        $response = $this->actingAs($this->user)
            ->post('/copropietarios', [
                'numero_departamento' => '103',
                'copropietarios' => [
                    [
                        'nombre_completo' => 'Test `ls -la`',
                        'tipo' => 'propietario',
                    ]
                ]
            ]);

        $this->assertTrue(true);
    }

    /**
     * Test que el middleware detecta pipes y operadores lógicos
     */
    public function test_detecta_pipes_y_operadores(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Intento sospechoso de inyección de comandos detectado'
                    && (str_contains($context['value'], '|') || str_contains($context['value'], '&&'));
            });

        $response = $this->actingAs($this->user)
            ->post('/copropietarios', [
                'numero_departamento' => '104',
                'copropietarios' => [
                    [
                        'nombre_completo' => 'Test | cat /etc/passwd',
                        'tipo' => 'propietario',
                    ]
                ]
            ]);

        $this->assertTrue(true);
    }

    /**
     * Test que el middleware detecta comandos shell comunes
     */
    public function test_detecta_comandos_shell_comunes(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Intento sospechoso de inyección de comandos detectado'
                    && preg_match('/\b(cat|ls|pwd|whoami|wget|curl)\b/i', $context['value']);
            });

        $response = $this->actingAs($this->user)
            ->post('/copropietarios', [
                'numero_departamento' => '105',
                'copropietarios' => [
                    [
                        'nombre_completo' => 'Test wget malicious.com',
                        'tipo' => 'propietario',
                    ]
                ]
            ]);

        $this->assertTrue(true);
    }

    /**
     * Test que el middleware NO detecta entradas normales
     */
    public function test_no_detecta_entradas_normales(): void
    {
        Log::shouldReceive('warning')->never();

        $response = $this->actingAs($this->user)
            ->post('/copropietarios', [
                'numero_departamento' => '106',
                'copropietarios' => [
                    [
                        'nombre_completo' => 'Juan Pérez García',
                        'tipo' => 'propietario',
                        'telefono' => '+56912345678',
                        'correo' => 'juan@example.com',
                    ]
                ]
            ]);

        $this->assertTrue(true);
    }

    /**
     * Test que el middleware detecta patrones en campos anidados
     */
    public function test_detecta_patrones_en_arrays_anidados(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Intento sospechoso de inyección de comandos detectado'
                    && str_contains($context['field'], 'autorizados');
            });

        $response = $this->actingAs($this->user)
            ->post('/copropietarios', [
                'numero_departamento' => '107',
                'copropietarios' => [
                    [
                        'nombre_completo' => 'Juan Pérez',
                        'tipo' => 'propietario',
                    ]
                ],
                'autorizados' => [
                    [
                        'nombre_completo' => 'Test; malicious',
                        'rut_pasaporte' => '12345678-9',
                    ]
                ]
            ]);

        $this->assertTrue(true);
    }

    /**
     * Test que el middleware registra información completa del contexto
     */
    public function test_registra_informacion_completa_contexto(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Intento sospechoso de inyección de comandos detectado'
                    && isset($context['timestamp'])
                    && isset($context['ip_address'])
                    && isset($context['user_agent'])
                    && isset($context['user_id'])
                    && isset($context['url'])
                    && isset($context['method'])
                    && isset($context['field'])
                    && isset($context['value'])
                    && isset($context['matched_patterns'])
                    && is_array($context['matched_patterns']);
            });

        $response = $this->actingAs($this->user)
            ->post('/copropietarios', [
                'numero_departamento' => '108',
                'copropietarios' => [
                    [
                        'nombre_completo' => 'Test; injection',
                        'tipo' => 'propietario',
                    ]
                ]
            ]);

        $this->assertTrue(true);
    }
}
