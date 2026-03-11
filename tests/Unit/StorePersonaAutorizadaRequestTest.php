<?php

namespace Tests\Unit;

use App\Http\Requests\StorePersonaAutorizadaRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Tests unitarios para StorePersonaAutorizadaRequest
 * 
 * Valida:
 * - Requisito 14: Validación de Datos
 */
class StorePersonaAutorizadaRequestTest extends TestCase
{
    /**
     * Test: FormRequest rechaza nombre completo con menos de 3 caracteres
     * 
     * Valida Requisito 14.2: Validación de nombre completo mínimo 3 caracteres
     */
    public function test_rejects_nombre_completo_less_than_3_characters(): void
    {
        $request = new StorePersonaAutorizadaRequest();
        $validator = Validator::make(
            ['nombre_completo' => 'An'],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nombre_completo', $validator->errors()->toArray());
    }

    /**
     * Test: FormRequest acepta nombre completo válido
     * 
     * Valida Requisito 14.2: Validación de nombre completo
     */
    public function test_accepts_valid_nombre_completo(): void
    {
        $request = new StorePersonaAutorizadaRequest();
        $data = [
            'nombre_completo' => 'Ana García',
            'rut_pasaporte' => '12345678-9',
            'departamento' => '101',
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * Test: FormRequest rechaza datos sin rut_pasaporte
     * 
     * Valida que el campo rut_pasaporte es requerido
     */
    public function test_rejects_missing_rut_pasaporte(): void
    {
        $request = new StorePersonaAutorizadaRequest();
        $data = [
            'nombre_completo' => 'Ana García',
            'departamento' => '101',
            // rut_pasaporte faltante
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('rut_pasaporte', $validator->errors()->toArray());
    }

    /**
     * Test: FormRequest rechaza datos sin departamento
     * 
     * Valida que el campo departamento es requerido
     */
    public function test_rejects_missing_departamento(): void
    {
        $request = new StorePersonaAutorizadaRequest();
        $data = [
            'nombre_completo' => 'Ana García',
            'rut_pasaporte' => '12345678-9',
            // departamento faltante
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('departamento', $validator->errors()->toArray());
    }

    /**
     * Test: FormRequest acepta patente como campo opcional
     * 
     * Valida que patente puede ser null
     */
    public function test_accepts_patente_as_optional(): void
    {
        $request = new StorePersonaAutorizadaRequest();
        $data = [
            'nombre_completo' => 'Ana García',
            'rut_pasaporte' => '12345678-9',
            'departamento' => '101',
            'patente' => null,
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    /**
     * Test: Mensajes de error son descriptivos
     * 
     * Valida Requisito 14.6: Mensajes de error descriptivos
     */
    public function test_error_messages_are_descriptive(): void
    {
        $request = new StorePersonaAutorizadaRequest();
        $messages = $request->messages();

        // Verificar que existen mensajes personalizados
        $this->assertArrayHasKey('nombre_completo.required', $messages);
        $this->assertArrayHasKey('nombre_completo.min', $messages);
        $this->assertArrayHasKey('rut_pasaporte.required', $messages);
        $this->assertArrayHasKey('departamento.required', $messages);

        // Verificar que los mensajes son descriptivos (no vacíos)
        $this->assertNotEmpty($messages['nombre_completo.required']);
        $this->assertNotEmpty($messages['rut_pasaporte.required']);
        
        // Verificar que están en español
        $this->assertStringContainsString('obligatorio', $messages['nombre_completo.required']);
        $this->assertStringContainsString('caracteres', $messages['nombre_completo.min']);
    }

    /**
     * Test: FormRequest sanitiza entradas HTML
     * 
     * Valida Requisito 27.4: Sanitización de entradas para prevenir XSS
     */
    public function test_sanitizes_html_input(): void
    {
        $request = new StorePersonaAutorizadaRequest();
        
        // Simular datos con HTML
        $request->merge([
            'nombre_completo' => '<script>alert("XSS")</script>Ana García',
            'rut_pasaporte' => '<b>12345678-9</b>',
            'departamento' => '<i>101</i>',
        ]);
        
        // Llamar al método de preparación
        $request->prepareForValidation();
        
        // Verificar que el HTML fue removido
        $this->assertEquals('Ana García', $request->nombre_completo);
        $this->assertEquals('12345678-9', $request->rut_pasaporte);
        $this->assertEquals('101', $request->departamento);
    }

    /**
     * Test: Validación completa con todos los campos válidos
     * 
     * Valida que un conjunto completo de datos válidos pasa la validación
     */
    public function test_validates_complete_valid_data(): void
    {
        $request = new StorePersonaAutorizadaRequest();
        $data = [
            'nombre_completo' => 'Ana María García López',
            'rut_pasaporte' => '12345678-9',
            'departamento' => '101',
            'patente' => 'WXYZ99',
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
        $this->assertEmpty($validator->errors()->toArray());
    }

    /**
     * Test: Campo rut_pasaporte está correctamente nombrado en validación
     * 
     * Valida Requisito 26.2: Consistencia en nombres de campos
     */
    public function test_rut_pasaporte_field_is_correctly_named_in_validation(): void
    {
        $request = new StorePersonaAutorizadaRequest();
        $rules = $request->rules();

        // Verificar que la regla usa 'rut_pasaporte' (no 'rut', 'pasaporte', etc.)
        $this->assertArrayHasKey('rut_pasaporte', $rules);
        $this->assertArrayNotHasKey('rut', $rules);
        $this->assertArrayNotHasKey('pasaporte', $rules);
    }

    /**
     * Test: FormRequest rechaza datos inválidos con múltiples errores
     * 
     * Valida Requisito 14.6: Mensajes de error para múltiples campos
     */
    public function test_rejects_invalid_data_with_multiple_errors(): void
    {
        $request = new StorePersonaAutorizadaRequest();
        $data = [
            'nombre_completo' => 'An', // Muy corto
            // rut_pasaporte faltante
            // departamento faltante
        ];
        
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        
        $errors = $validator->errors()->toArray();
        $this->assertArrayHasKey('nombre_completo', $errors);
        $this->assertArrayHasKey('rut_pasaporte', $errors);
        $this->assertArrayHasKey('departamento', $errors);
    }
}
