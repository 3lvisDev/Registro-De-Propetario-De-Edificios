<?php

namespace Database\Factories;

use App\Models\PersonaAutorizada;
use App\Models\Copropietario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo PersonaAutorizada.
 * 
 * Genera datos de prueba para personas autorizadas.
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PersonaAutorizada>
 */
class PersonaAutorizadaFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente a la factory.
     *
     * @var string
     */
    protected $model = PersonaAutorizada::class;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre_completo' => fake()->name(),
            'rut_pasaporte' => fake()->regexify('[0-9]{8}-[0-9K]'),
            'departamento' => fake()->numberBetween(101, 999),
            'patente' => fake()->optional()->regexify('[A-Z]{2}[0-9]{4}'),
            'copropietario_id' => Copropietario::factory(),
        ];
    }
}
