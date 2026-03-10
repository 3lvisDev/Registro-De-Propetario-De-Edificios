<?php

namespace Database\Factories;

use App\Models\Copropietario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Copropietario.
 * 
 * Genera datos de prueba para copropietarios (propietarios y arrendatarios).
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Copropietario>
 */
class CopropietarioFactory extends Factory
{
    /**
     * El nombre del modelo correspondiente a la factory.
     *
     * @var string
     */
    protected $model = Copropietario::class;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre_completo' => fake()->name(),
            'numero_departamento' => fake()->numberBetween(101, 999),
            'tipo' => fake()->randomElement(['Propietario', 'Arrendatario']),
            'telefono' => fake()->optional()->phoneNumber(),
            'correo' => fake()->optional()->safeEmail(),
            'patente' => fake()->optional()->regexify('[A-Z]{2}[0-9]{4}'),
            'estacionamiento' => fake()->optional()->numberBetween(1, 100),
            'bodega' => fake()->optional()->numberBetween(1, 100),
            'propietario_id' => null,
        ];
    }

    /**
     * Estado para crear un propietario (sin propietario_id).
     *
     * @return static
     */
    public function propietario(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'Propietario',
            'propietario_id' => null,
        ]);
    }

    /**
     * Estado para crear un arrendatario (con propietario_id).
     *
     * @return static
     */
    public function arrendatario(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'Arrendatario',
        ]);
    }
}
