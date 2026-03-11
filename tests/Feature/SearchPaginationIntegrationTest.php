<?php

namespace Tests\Feature;

use App\Models\Copropietario;
use App\Models\PersonaAutorizada;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de integración para búsqueda y paginación.
 * 
 * Valida los requisitos:
 * - Requisito 6: Búsqueda de Copropietarios
 * - Requisito 30: Paginación de Resultados
 */
class SearchPaginationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * Test: crear múltiples copropietarios → buscar → verificar resultados
     * 
     * Valida:
     * - Requisito 6.2: Búsqueda por nombre completo
     * - Requisito 6.3: Búsqueda por teléfono
     * - Requisito 6.4: Búsqueda por correo electrónico
     * - Requisito 6.5: Búsqueda por patente
     * - Requisito 6.8: Búsqueda por número de departamento
     * 
     * @test
     */
    public function test_busqueda_copropietarios_por_diferentes_criterios()
    {
        $this->actingAs($this->user);

        // Paso 1: Crear múltiples copropietarios con datos variados
        $copropietario1 = Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Juan Pérez García',
            'numero_departamento' => '101',
            'telefono' => '+56912345678',
            'correo' => 'juan.perez@example.com',
            'patente' => 'AA1111',
            'estacionamiento' => '10',
            'bodega' => '5',
        ]);

        $copropietario2 = Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'María González López',
            'numero_departamento' => '102',
            'telefono' => '+56987654321',
            'correo' => 'maria.gonzalez@example.com',
            'patente' => 'BB2222',
            'estacionamiento' => '20',
            'bodega' => '10',
        ]);

        $copropietario3 = Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Pedro Martínez Silva',
            'numero_departamento' => '103',
            'telefono' => '+56911111111',
            'correo' => 'pedro.martinez@example.com',
            'patente' => 'CC3333',
            'estacionamiento' => '30',
            'bodega' => '15',
        ]);

        // Paso 2: Búsqueda por nombre
        $response = $this->get(route('copropietarios.index', ['buscar' => 'Juan']));
        $response->assertStatus(200);
        $response->assertSee('Juan Pérez García');
        $response->assertDontSee('María González López');
        $response->assertDontSee('Pedro Martínez Silva');

        // Paso 3: Búsqueda por teléfono
        $response = $this->get(route('copropietarios.index', ['buscar' => '987654321']));
        $response->assertStatus(200);
        $response->assertSee('María González López');
        $response->assertDontSee('Juan Pérez García');

        // Paso 4: Búsqueda por correo
        $response = $this->get(route('copropietarios.index', ['buscar' => 'pedro.martinez']));
        $response->assertStatus(200);
        $response->assertSee('Pedro Martínez Silva');
        $response->assertDontSee('Juan Pérez García');

        // Paso 5: Búsqueda por patente
        $response = $this->get(route('copropietarios.index', ['buscar' => 'BB2222']));
        $response->assertStatus(200);
        $response->assertSee('María González López');
        $response->assertDontSee('Juan Pérez García');

        // Paso 6: Búsqueda por número de departamento (exacto)
        $response = $this->get(route('copropietarios.index', ['buscar' => '101']));
        $response->assertStatus(200);
        $response->assertSee('Juan Pérez García');
        $response->assertDontSee('María González López');
        $response->assertDontSee('Pedro Martínez Silva');

        // Paso 7: Búsqueda por estacionamiento
        $response = $this->get(route('copropietarios.index', ['buscar' => '20']));
        $response->assertStatus(200);
        $response->assertSee('María González López');

        // Paso 8: Búsqueda por bodega
        $response = $this->get(route('copropietarios.index', ['buscar' => '15']));
        $response->assertStatus(200);
        $response->assertSee('Pedro Martínez Silva');
    }

    /**
     * Test: búsqueda sin resultados
     * 
     * Valida:
     * - Requisito 6: Búsqueda de Copropietarios
     * 
     * @test
     */
    public function test_busqueda_sin_resultados()
    {
        $this->actingAs($this->user);

        // Crear algunos copropietarios
        Copropietario::factory()->propietario()->count(3)->create();

        // Buscar algo que no existe
        $response = $this->get(route('copropietarios.index', ['buscar' => 'NoExiste123']));
        $response->assertStatus(200);
        $response->assertSee('No se encontraron resultados', false);
    }

    /**
     * Test: crear más de 15 registros → verificar paginación
     * 
     * Valida:
     * - Requisito 30.1: Paginación de PersonaAutorizada con 15 registros por página
     * - Requisito 30.3: Uso de paginate() en lugar de get()
     * - Requisito 30.4: Controles de navegación entre páginas
     * 
     * @test
     */
    public function test_paginacion_personas_autorizadas()
    {
        $this->actingAs($this->user);

        // Paso 1: Crear un propietario
        $propietario = Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Propietario Principal',
            'numero_departamento' => '101',
        ]);

        // Paso 2: Crear más de 15 personas autorizadas (20 para probar paginación)
        PersonaAutorizada::factory()->count(20)->create([
            'copropietario_id' => $propietario->id,
            'departamento' => '101',
        ]);

        // Paso 3: Verificar primera página
        $response = $this->get(route('personas-autorizadas.index'));
        $response->assertStatus(200);
        
        // Debe mostrar 15 registros en la primera página
        $response->assertViewHas('personasAutorizadas', function ($paginator) {
            return $paginator->count() === 15 && $paginator->total() === 20;
        });

        // Paso 4: Verificar segunda página
        $response = $this->get(route('personas-autorizadas.index', ['page' => 2]));
        $response->assertStatus(200);
        
        // Debe mostrar 5 registros en la segunda página
        $response->assertViewHas('personasAutorizadas', function ($paginator) {
            return $paginator->count() === 5 && $paginator->total() === 20;
        });

        // Paso 5: Verificar que hay controles de navegación
        $response = $this->get(route('personas-autorizadas.index'));
        $response->assertSee('Siguiente', false); // Link a página siguiente
    }

    /**
     * Test: paginación de copropietarios por departamento
     * 
     * Valida:
     * - Requisito 5.3: Paginación de departamentos (3 por página)
     * - Requisito 5.4: Paginación de copropietarios dentro de departamento (10 por página)
     * - Requisito 30: Paginación de resultados
     * 
     * @test
     */
    public function test_paginacion_copropietarios_por_departamento()
    {
        $this->actingAs($this->user);

        // Paso 1: Crear múltiples departamentos con copropietarios
        // Departamento 101 - 5 copropietarios
        for ($i = 1; $i <= 5; $i++) {
            Copropietario::factory()->propietario()->create([
                'nombre_completo' => "Propietario Dept 101 - {$i}",
                'numero_departamento' => '101',
            ]);
        }

        // Departamento 102 - 5 copropietarios
        for ($i = 1; $i <= 5; $i++) {
            Copropietario::factory()->propietario()->create([
                'nombre_completo' => "Propietario Dept 102 - {$i}",
                'numero_departamento' => '102',
            ]);
        }

        // Departamento 103 - 5 copropietarios
        for ($i = 1; $i <= 5; $i++) {
            Copropietario::factory()->propietario()->create([
                'nombre_completo' => "Propietario Dept 103 - {$i}",
                'numero_departamento' => '103',
            ]);
        }

        // Departamento 104 - 5 copropietarios
        for ($i = 1; $i <= 5; $i++) {
            Copropietario::factory()->propietario()->create([
                'nombre_completo' => "Propietario Dept 104 - {$i}",
                'numero_departamento' => '104',
            ]);
        }

        // Paso 2: Verificar primera página de departamentos (debe mostrar 3 departamentos)
        $response = $this->get(route('copropietarios.index'));
        $response->assertStatus(200);
        
        $response->assertViewHas('departmentsPaginator', function ($paginator) {
            return $paginator->count() === 3 && $paginator->total() === 4;
        });

        // Debe ver departamentos 101, 102, 103
        $response->assertSee('101');
        $response->assertSee('102');
        $response->assertSee('103');
        $response->assertDontSee('104'); // Este está en la página 2

        // Paso 3: Verificar segunda página de departamentos
        $response = $this->get(route('copropietarios.index', ['dept_page' => 2]));
        $response->assertStatus(200);
        
        // Debe ver departamento 104
        $response->assertSee('104');
        $response->assertDontSee('101');
    }

    /**
     * Test: búsqueda mantiene parámetros en paginación
     * 
     * Valida:
     * - Requisito 6.10: Búsqueda mantiene paginación
     * - Requisito 30.5: Parámetros se mantienen en paginación
     * 
     * @test
     */
    public function test_busqueda_mantiene_parametros_en_paginacion()
    {
        $this->actingAs($this->user);

        // Crear múltiples copropietarios con nombre similar
        for ($i = 1; $i <= 10; $i++) {
            Copropietario::factory()->propietario()->create([
                'nombre_completo' => "Juan Pérez {$i}",
                'numero_departamento' => "10{$i}",
            ]);
        }

        // Buscar "Juan" - debería encontrar todos
        $response = $this->get(route('copropietarios.index', ['buscar' => 'Juan']));
        $response->assertStatus(200);
        
        // Verificar que el parámetro de búsqueda se mantiene en los links de paginación
        $response->assertSee('buscar=Juan', false);
    }

    /**
     * Test: ordenamiento de copropietarios por tipo
     * 
     * Valida:
     * - Requisito 5.2: Ordenamiento por tipo (Propietario primero, luego Arrendatario)
     * 
     * @test
     */
    public function test_ordenamiento_copropietarios_por_tipo()
    {
        $this->actingAs($this->user);

        // Crear propietario y arrendatarios en el mismo departamento
        $propietario = Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Propietario Principal',
            'numero_departamento' => '201',
        ]);

        $arrendatario1 = Copropietario::factory()->arrendatario()->create([
            'nombre_completo' => 'Arrendatario 1',
            'numero_departamento' => '201',
            'propietario_id' => $propietario->id,
        ]);

        $arrendatario2 = Copropietario::factory()->arrendatario()->create([
            'nombre_completo' => 'Arrendatario 2',
            'numero_departamento' => '201',
            'propietario_id' => $propietario->id,
        ]);

        // Obtener la lista
        $response = $this->get(route('copropietarios.index'));
        $response->assertStatus(200);

        // Verificar que el propietario aparece antes que los arrendatarios
        $content = $response->getContent();
        $posicionPropietario = strpos($content, 'Propietario Principal');
        $posicionArrendatario1 = strpos($content, 'Arrendatario 1');
        $posicionArrendatario2 = strpos($content, 'Arrendatario 2');

        $this->assertLessThan($posicionArrendatario1, $posicionPropietario);
        $this->assertLessThan($posicionArrendatario2, $posicionPropietario);
    }

    /**
     * Test: agrupación de copropietarios por departamento
     * 
     * Valida:
     * - Requisito 5.1: Agrupación por número de departamento
     * 
     * @test
     */
    public function test_agrupacion_copropietarios_por_departamento()
    {
        $this->actingAs($this->user);

        // Crear copropietarios en diferentes departamentos
        Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Propietario Dept 301',
            'numero_departamento' => '301',
        ]);

        Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Propietario Dept 302',
            'numero_departamento' => '302',
        ]);

        Copropietario::factory()->propietario()->create([
            'nombre_completo' => 'Otro Propietario Dept 301',
            'numero_departamento' => '301',
        ]);

        // Obtener la lista
        $response = $this->get(route('copropietarios.index'));
        $response->assertStatus(200);

        // Verificar que los copropietarios del mismo departamento aparecen juntos
        $response->assertSee('301');
        $response->assertSee('302');
        $response->assertSee('Propietario Dept 301');
        $response->assertSee('Otro Propietario Dept 301');
    }
}
