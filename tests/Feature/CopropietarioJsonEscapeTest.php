<?php

namespace Tests\Feature;

use App\Models\Copropietario;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test para verificar que las respuestas JSON escapan caracteres HTML
 * para prevenir ataques XSS (Requisito 27.3)
 */
class CopropietarioJsonEscapeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Verificar que getDetails escapa caracteres HTML en respuesta JSON
     * 
     * @return void
     */
    public function test_get_details_escapes_html_characters_in_json_response()
    {
        // Crear un usuario autenticado
        $user = User::factory()->create();
        
        // Crear un copropietario con caracteres HTML potencialmente peligrosos
        $copropietario = Copropietario::create([
            'nombre_completo' => 'Juan <script>alert("XSS")</script> Pérez',
            'numero_departamento' => '101',
            'tipo' => 'Propietario',
            'telefono' => '555-1234 & Co.',
            'correo' => 'test@example.com',
            'patente' => "ABC'123",
        ]);

        // Hacer petición autenticada al endpoint getDetails
        $response = $this->actingAs($user)->getJson("/copropietarios/{$copropietario->id}/details");

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Obtener el contenido JSON crudo
        $jsonContent = $response->getContent();

        // Verificar que los caracteres HTML están escapados usando las flags JSON_HEX_*
        // JSON_HEX_TAG: < se convierte en \u003C, > se convierte en \u003E
        $this->assertStringContainsString('\u003C', $jsonContent, 'El carácter < debe estar escapado como \u003C');
        $this->assertStringContainsString('\u003E', $jsonContent, 'El carácter > debe estar escapado como \u003E');
        
        // JSON_HEX_AMP: & se convierte en \u0026
        $this->assertStringContainsString('\u0026', $jsonContent, 'El carácter & debe estar escapado como \u0026');
        
        // JSON_HEX_APOS: ' se convierte en \u0027
        $this->assertStringContainsString('\u0027', $jsonContent, 'El carácter \' debe estar escapado como \u0027');

        // Verificar que el script tag no aparece sin escapar
        $this->assertStringNotContainsString('<script>', $jsonContent, 'El tag <script> no debe aparecer sin escapar');
        $this->assertStringNotContainsString('</script>', $jsonContent, 'El tag </script> no debe aparecer sin escapar');
    }

    /**
     * Test: Verificar que datos normales sin caracteres especiales funcionan correctamente
     * 
     * @return void
     */
    public function test_get_details_works_with_normal_data()
    {
        // Crear un usuario autenticado
        $user = User::factory()->create();
        
        // Crear un copropietario con datos normales
        $copropietario = Copropietario::create([
            'nombre_completo' => 'María González',
            'numero_departamento' => '202',
            'tipo' => 'Arrendatario',
            'telefono' => '555-5678',
            'correo' => 'maria@example.com',
        ]);

        // Hacer petición autenticada al endpoint getDetails
        $response = $this->actingAs($user)->getJson("/copropietarios/{$copropietario->id}/details");

        // Verificar que la respuesta es exitosa
        $response->assertStatus(200);

        // Verificar que los datos están presentes en la respuesta
        $response->assertJsonStructure([
            'id',
            'nombre_completo',
            'numero_departamento',
            'tipo',
            'telefono',
            'correo',
            'created_at',
            'updated_at',
        ]);
    }
}
