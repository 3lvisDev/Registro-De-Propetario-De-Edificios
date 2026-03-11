<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateCopropietarioRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Tests unitarios para UpdateCopropietarioRequest
 * 
 * Valida:
 * - Requisito 14: Validación de Datos
 * - Requisito 21: Validación en Actualización de Copropietarios
 */
class UpdateCopropietarioRequestTest extends TestCase
{
    /**
     * Test: FormRequest rechaza nombre completo con menos de 5 caracteres
     * 
     * Valida Requisito 21.1: Validación de nombre completo mínimo 5 caracteres
     */
    public function test_rejects_nombre_completo_less_than_5_characters(): void
    {
        $request = new UpdateCopropietarioRequest();
        $validator = Validator::make(
            ['nombre_completo' => 'Juan'],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nombre_completo', $validator->errors()->toArray());
    }

    /**
     * Test: FormRequest acepta nombre completo válido
     * 
     * Valida Requisito 21.1: Validación de nombre completo
     */
    public function test_accepts_valid_nombre_completo(): void
    {
        $request = new UpdateCopropietarioRequest();
        $data = [
            'nombre_completo' => 'Juan Pérez González',
            'numero_departamento' => '101',
            'tipo' => 'propietario',
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * Test: FormRequest rechaza correo electrónico con formato inválido
     * 
     * Valida Requisito 21.2: Validación de formato de correo electrónico
     */
    public function test_rejects_invalid_email_format(): void
    {
        $request = new UpdateCopropietarioRequest();
        $data = [
            'nombre_completo' => 'Juan Pérez',
            'numero_departamento' => '101',
            'tipo' => 'propietario',
            'correo' => 'correo-invalido',
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('correo', $validator->errors()->toArray());
    }

    /**
     * Test: FormRequest acepta correo electrónico válido
     * 
     * Valida Requisito 21.2: Validación de formato de correo electrónico
     */
    public function test_accepts_valid_email(): void
    {
        $request = new UpdateCopropietarioRequest();
        $data = [
            'nombre_completo' => 'Juan Pérez',
            'numero_departamento' => '101',
            'tipo' => 'propietario',
            'correo' => 'juan.perez@example.com',
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * Test: FormRequest rechaza tipo inválido
     * 
     * Valida Requisito 21.3: Validación de tipo (Propietario o Arrendatario)
     */
    public function test_rejects_invalid_tipo(): void
    {
        $request = new UpdateCopropietarioRequest();
        $data = [
            'nombre_completo' => 'Juan Pérez',
            'numero_departamento' => '101',
            'tipo' => 'inquilino', // Tipo inválido
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tipo', $validator->errors()->toArray());
    }

    /**
     * Test: FormRequest acepta tipos válidos
     * 
     * Valida Requisito 21.3: Validación de tipo
     */
    public function test_accepts_valid_tipos(): void
    {
        $request = new UpdateCopropietarioRequest();
        
        // Test propietario
        $data1 = [
            'nombre_completo' => 'Juan Pérez',
            'numero_departamento' => '101',
            'tipo' => 'propietario',
        ];
        $validator1 = Validator::make($data1, $request->rules());
        $this->assertFalse($validator1->fails());

        // Test arrendatario
        $data2 = [
            'nombre_completo' => 'Pedro López',
            'numero_departamento' => '102',
            'tipo' => 'arrendatario',
        ];
        $validator2 = Validator::make($data2, $request->rules());
        $this->assertFalse($validator2->fails());
    }

    /**
     * Test: FormRequest rechaza datos sin número de departamento
     * 
     * Valida Requisito 21.4: Validación de número de departamento requerido
     */
    public function test_rejects_missing_numero_departamento(): void
    {
        $request = new UpdateCopropietarioRequest();
        $data = [
            'nombre_completo' => 'Juan Pérez',
            'tipo' => 'propietario',
            // numero_departamento faltante
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('numero_departamento', $validator->errors()->toArray());
    }

    /**
     * Test: Mensajes de error son descriptivos
     * 
     * Valida Requisito 21.5: Mensajes de error descriptivos
     */
    public function test_error_messages_are_descriptive(): void
    {
        $request = new UpdateCopropietarioRequest();
        $messages = $request->messages();

        // Verificar que existen mensajes personalizados
        $this->assertArrayHasKey('nombre_completo.required', $messages);
        $this->assertArrayHasKey('nombre_completo.min', $messages);
        $this->assertArrayHasKey('correo.email', $messages);
        $this->assertArrayHasKey('tipo.in', $messages);
        $this->assertArrayHasKey('numero_departamento.required', $messages);

        // Verificar que los mensajes son descriptivos (no vacíos)
        $this->assertNotEmpty($messages['nombre_completo.required']);
        $this->assertNotEmpty($messages['correo.email']);
        
        // Verificar que están en español
        $this->assertStringContainsString('obligatorio', $messages['nombre_completo.required']);
        $this->assertStringContainsString('caracteres', $messages['nombre_completo.min']);
    }

    /**
     * Test: FormRequest acepta campos opcionales como null
     * 
     * Valida que campos opcionales pueden ser omitidos
     */
    public function test_accepts_optional_fields_as_null(): void
    {
        $request = new UpdateCopropietarioRequest();
        $data = [
            'nombre_completo' => 'Juan Pérez',
            'numero_departamento' => '101',
            'tipo' => 'propietario',
            'telefono' => null,
            'correo' => null,
            'patente' => null,
            'estacionamiento' => null,
            'bodega' => null,
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * Test: FormRequest sanitiza entradas HTML
     * 
     * Valida Requisito 27.4: Sanitización de entradas para prevenir XSS
     */
    public function test_sanitizes_html_input(): void
    {
        $request = new UpdateCopropietarioRequest();
        
        // Simular datos con HTML
        $request->merge([
            'nombre_completo' => '<script>alert("XSS")</script>Juan Pérez',
            'telefono' => '<b>123456789</b>',
        ]);
        
        // Llamar al método de preparación
        $request->prepareForValidation();
        
        // Verificar que el HTML fue removido
        $this->assertEquals('Juan Pérez', $request->nombre_completo);
        $this->assertEquals('123456789', $request->telefono);
    }

    /**
     * Test: Validación completa con todos los campos válidos
     * 
     * Valida que un conjunto completo de datos válidos pasa la validación
     */
    public function test_validates_complete_valid_data(): void
    {
        $request = new UpdateCopropietarioRequest();
        $data = [
            'nombre_completo' => 'Juan Pérez González',
            'numero_departamento' => '101',
            'tipo' => 'propietario',
            'telefono' => '+56912345678',
            'correo' => 'juan.perez@example.com',
            'patente' => 'ABCD12',
            'estacionamiento' => '15',
            'bodega' => '8',
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
        $this->assertEmpty($validator->errors()->toArray());
    }
}