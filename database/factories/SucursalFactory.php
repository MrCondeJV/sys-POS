<?php

namespace Database\Factories;

use App\Enums\EstadoGeneral;
use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sucursal>
 */
class SucursalFactory extends Factory
{
    protected $model = Sucursal::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'nombre' => 'Sucursal '.fake()->streetName(),
            'codigo' => 'SUC-'.fake()->unique()->numerify('###'),
            'direccion' => fake()->streetAddress(),
            'telefono' => fake()->phoneNumber(),
            'ciudad' => fake()->city(),
            'departamento' => 'Antioquia',
            'es_principal' => false,
            'estado' => EstadoGeneral::ACTIVO,
        ];
    }

    public function principal(): static
    {
        return $this->state(fn (array $attributes) => [
            'es_principal' => true,
        ]);
    }

    public function inactiva(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => EstadoGeneral::INACTIVO,
        ]);
    }
}
