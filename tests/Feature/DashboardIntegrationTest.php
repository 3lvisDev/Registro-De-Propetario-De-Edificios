<?php

namespace Tests\Feature;

use App\Models\Copropietario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de integración para Dashboard.
 * 
 * Valida los requisitos:
 * - Requisito 2: Visualización del Dashboard
 */
class DashboardIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test: crear copropietarios de diferentes tipos → verificar estadísticas
     * 
     * Valida:
     * - Requisito 2.1: Total de copropietarios registrados
     * - Requisito 2.2: Total de propietarios
     * - Requisito 2.3: Total de arrendatarios
     * - Requisito 2.4: Total de departamentos únicos
     * 
     * @test
     */
    public function test_dashboard_muestra_estadisticas_correctas()
    {
        $this->actingAs($this->user);

        // Paso 1: Verificar dashboard vacío
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas('total', 0);
        $response->assertViewHas('propietarios', 0);
        $response->assertViewHas('arrendatarios', 0);
        $response->assertViewHas('departamentos', 0);

        // Paso 2: Crear propietarios en diferentes departamentos
        $propietario1 = Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Propietario Dept 101',
            'numero_departamento' => '101',
        ]);

        $propietario2 = Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Propietario Dept 102',
            'numero_departamento' => '102',
        ]);

        $propietario3 = Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Propietario Dept 103',
            'numero_departamento' => '103',
        ]);

        // Verificar estadísticas después de crear propietarios
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas('total', 3);
        $response->assertViewHas('propietarios', 3);
        $response->assertViewHas('arrendatarios', 0);
        $response->assertViewHas('departamentos', 3);

        // Paso 3: Crear arrendatarios
        $arrendatario1 = Copropietario::factory()->arrendatario()->create([
            'nombre_completo' => 'Arrendatario Dept 101',
            'numero_departamento' => '101',
            'propietario_id' => $propietario1->id,
        ]);

        $arrendatario2 = Copropietario::factory()->arrendatario()->create([
            'nombre_completo' => 'Arrendatario Dept 102',
            'numero_departamento' => '102',
            'propietario_id' => $propietario2->id,
        ]);

        // Verificar estadísticas después de crear arrendatarios
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas('total', 5); // 3 propietarios + 2 arrendatarios
        $response->assertViewHas('propietarios', 3);
        $response->assertViewHas('arrendatarios', 2);
        $response->assertViewHas('departamentos', 3); // Sigue siendo 3 departamentos únicos

        // Paso 4: Crear más copropietarios en el mismo departamento
        $propietario4 = Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Otro Propietario Dept 101',
            'numero_departamento' => '101',
        ]);

        // Verificar que el total de departamentos no aumenta
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas('total', 6);
        $response->assertViewHas('propietarios', 4);
        $response->assertViewHas('arrendatarios', 2);
        $response->assertViewHas('departamentos', 3); // Sigue siendo 3 departamentos únicos
    }

    /**
     * Test: dashboard con múltiples arrendatarios por propietario
     * 
     * Valida:
     * - Requisito 2: Visualización del Dashboard
     * - Conteo correcto cuando hay múltiples arrendatarios
     * 
     * @test
     */
    public function test_dashboard_con_multiples_arrendatarios_por_propietario()
    {
        $this->actingAs($this->user);

        // Crear propietario
        $propietario = Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Propietario Principal',
            'numero_departamento' => '201',
        ]);

        // Crear múltiples arrendatarios para el mismo propietario
        for ($i = 1; $i <= 5; $i++) {
            Copropietario::factory()->arrendatario()->create([
                'nombre_completo' => "Arrendatario {$i}",
                'numero_departamento' => '201',
                'propietario_id' => $propietario->id,
            ]);
        }

        // Verificar estadísticas
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas('total', 6); // 1 propietario + 5 arrendatarios
        $response->assertViewHas('propietarios', 1);
        $response->assertViewHas('arrendatarios', 5);
        $response->assertViewHas('departamentos', 1);
    }

    /**
     * Test: dashboard después de eliminar copropietarios
     * 
     * Valida:
     * - Requisito 2: Visualización del Dashboard
     * - Actualización correcta de estadísticas después de eliminaciones
     * 
     * @test
     */
    public function test_dashboard_actualiza_estadisticas_despues_de_eliminaciones()
    {
        $this->actingAs($this->user);

        // Crear copropietarios
        $propietario1 = Copropietario::factory()->propietario()->create([
            'numero_departamento' => '301',
        ]);

        $propietario2 = Copropietario::factory()->propietario()->create([
            'numero_departamento' => '302',
        ]);

        $arrendatario = Copropietario::factory()->arrendatario()->create([
            'numero_departamento' => '301',
            'propietario_id' => $propietario1->id,
        ]);

        // Verificar estadísticas iniciales
        $response = $this->get(route('dashboard'));
        $response->assertViewHas('total', 3);
        $response->assertViewHas('propietarios', 2);
        $response->assertViewHas('arrendatarios', 1);
        $response->assertViewHas('departamentos', 2);

        // Eliminar arrendatario
        $arrendatario->delete();

        // Verificar estadísticas después de eliminar arrendatario
        $response = $this->get(route('dashboard'));
        $response->assertViewHas('total', 2);
        $response->assertViewHas('propietarios', 2);
        $response->assertViewHas('arrendatarios', 0);
        $response->assertViewHas('departamentos', 2);

        // Eliminar un propietario
        $propietario2->delete();

        // Verificar estadísticas después de eliminar propietario
        $response = $this->get(route('dashboard'));
        $response->assertViewHas('total', 1);
        $response->assertViewHas('propietarios', 1);
        $response->assertViewHas('arrendatarios', 0);
        $response->assertViewHas('departamentos', 1);
    }

    /**
     * Test: dashboard con departamentos duplicados
     * 
     * Valida:
     * - Requisito 2.4: Total de departamentos únicos
     * - Conteo correcto de departamentos distintos
     * 
     * @test
     */
    public function test_dashboard_cuenta_departamentos_unicos_correctamente()
    {
        $this->actingAs($this->user);

        // Crear múltiples copropietarios en el mismo departamento
        Copropietario::factory()->propietario()->create(['numero_departamento' => '401']);
        Copropietario::factory()->propietario()->create(['numero_departamento' => '401']);
        Copropietario::factory()->propietario()->create(['numero_departamento' => '401']);

        // Crear copropietarios en otro departamento
        Copropietario::factory()->propietario()->create(['numero_departamento' => '402']);
        Copropietario::factory()->propietario()->create(['numero_departamento' => '402']);

        // Crear copropietarios en un tercer departamento
        Copropietario::factory()->propietario()->create(['numero_departamento' => '403']);

        // Verificar que solo cuenta 3 departamentos únicos
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas('total', 6);
        $response->assertViewHas('propietarios', 6);
        $response->assertViewHas('departamentos', 3); // Solo 3 departamentos únicos
    }

    /**
     * Test: dashboard requiere autenticación
     * 
     * Valida:
     * - Requisito 17.1: Protección de rutas
     * 
     * @test
     */
    public function test_dashboard_requiere_autenticacion()
    {
        // Intentar acceder sin autenticación
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test: dashboard muestra vista correcta
     * 
     * Valida:
     * - Requisito 2: Visualización del Dashboard
     * - Requisito 15: Interfaz de usuario con AdminLTE
     * 
     * @test
     */
    public function test_dashboard_muestra_vista_correcta()
    {
        $this->actingAs($this->user);

        // Crear algunos datos
        Copropietario::factory()->propietario()->count(3)->create();
        Copropietario::factory()->arrendatario()->count(2)->create([
            'propietario_id' => Copropietario::factory()->propietario(),
        ]);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        
        // Verificar que se pasan las variables correctas a la vista
        $response->assertViewHasAll(['total', 'propietarios', 'arrendatarios', 'departamentos']);
    }

    /**
     * Test: dashboard con datos de gran volumen
     * 
     * Valida:
     * - Requisito 2: Visualización del Dashboard
     * - Rendimiento con múltiples registros
     * 
     * @test
     */
    public function test_dashboard_con_gran_volumen_de_datos()
    {
        $this->actingAs($this->user);

        // Crear 50 propietarios en diferentes departamentos
        for ($i = 1; $i <= 50; $i++) {
            Copropietario::factory()->propietario()->create([
                'numero_departamento' => (string)(100 + $i),
            ]);
        }

        // Crear 30 arrendatarios
        $propietarios = Copropietario::where('tipo', 'Propietario')->limit(30)->get();
        foreach ($propietarios as $propietario) {
            Copropietario::factory()->arrendatario()->create([
                'numero_departamento' => $propietario->numero_departamento,
                'propietario_id' => $propietario->id,
            ]);
        }

        // Verificar estadísticas
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas('total', 80); // 50 propietarios + 30 arrendatarios
        $response->assertViewHas('propietarios', 50);
        $response->assertViewHas('arrendatarios', 30);
        $response->assertViewHas('departamentos', 50);
    }
}
