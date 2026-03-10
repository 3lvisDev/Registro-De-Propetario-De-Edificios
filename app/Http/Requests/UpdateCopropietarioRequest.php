<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCopropietarioRequest extends FormRequest
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
            'nombre_completo' => 'required|string|min:5|max:100',
            'tipo' => 'required|in:propietario,arrendatario',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email',
            'patente' => 'nullable|string|max:20',
            'numero_departamento' => 'required|string|max:10',
            'estacionamiento' => 'nullable|string|max:50',
            'bodega' => 'nullable|string|max:50',
            // Validación de integridad referencial - Requisito 32.4
            // Si se proporciona propietario_id, debe existir en la tabla copropietarios
            'propietario_id' => 'nullable|exists:copropietarios,id',
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
            'nombre_completo.min' => 'El nombre completo debe tener al menos 5 caracteres.',
            'nombre_completo.max' => 'El nombre completo no puede exceder 100 caracteres.',
            'tipo.required' => 'El tipo de copropietario es obligatorio.',
            'tipo.in' => 'El tipo debe ser propietario o arrendatario.',
            'correo.email' => 'El correo electrónico debe tener un formato válido.',
            'numero_departamento.required' => 'El número de departamento es obligatorio.',
            'numero_departamento.max' => 'El número de departamento no puede exceder 10 caracteres.',
            'telefono.max' => 'El teléfono no puede exceder 20 caracteres.',
            'patente.max' => 'La patente no puede exceder 20 caracteres.',
            'estacionamiento.max' => 'El número de estacionamiento no puede exceder 50 caracteres.',
            'bodega.max' => 'El número de bodega no puede exceder 50 caracteres.',
            'propietario_id.exists' => 'El propietario seleccionado no existe en el sistema.',
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
            'telefono' => strip_tags($this->telefono ?? ''),
            'correo' => strip_tags($this->correo ?? ''),
            'patente' => strip_tags($this->patente ?? ''),
            'numero_departamento' => strip_tags($this->numero_departamento ?? ''),
            'estacionamiento' => strip_tags($this->estacionamiento ?? ''),
            'bodega' => strip_tags($this->bodega ?? ''),
        ]);
    }
}
