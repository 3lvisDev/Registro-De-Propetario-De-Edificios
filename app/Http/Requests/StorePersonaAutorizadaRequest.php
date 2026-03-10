<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonaAutorizadaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre_completo' => 'required|string|min:3|max:100',
            'rut_pasaporte' => 'required|string|max:20',
            'departamento' => 'required|string|max:10',
            'patente' => 'nullable|string|max:20',
            // Validación de integridad referencial - Requisito 32.5
            // Si se proporciona copropietario_id, debe existir en la tabla copropietarios
            'copropietario_id' => 'nullable|exists:copropietarios,id',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre_completo.required' => 'El nombre completo es obligatorio.',
            'nombre_completo.min' => 'El nombre completo debe tener al menos 3 caracteres.',
            'nombre_completo.max' => 'El nombre completo no puede exceder 100 caracteres.',
            'rut_pasaporte.required' => 'El RUT o pasaporte es obligatorio.',
            'rut_pasaporte.max' => 'El RUT o pasaporte no puede exceder 20 caracteres.',
            'departamento.required' => 'El número de departamento es obligatorio.',
            'departamento.max' => 'El número de departamento no puede exceder 10 caracteres.',
            'patente.max' => 'La patente no puede exceder 20 caracteres.',
            'copropietario_id.exists' => 'El copropietario seleccionado no existe en el sistema.',
        ];
    }

    /**
     * Preparar datos para validación - Sanitización de entradas
     * 
     * Requisito 27.4: Sanitizar entradas antes de almacenar
     * Remueve tags HTML y scripts para prevenir XSS
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre_completo' => strip_tags($this->nombre_completo ?? ''),
            'rut_pasaporte' => strip_tags($this->rut_pasaporte ?? ''),
            'departamento' => strip_tags($this->departamento ?? ''),
            'patente' => strip_tags($this->patente ?? ''),
        ]);
    }
}
