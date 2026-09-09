<?php

namespace Database\Factories;

use App\Enums\EstadoGeneral;
use App\Enums\TipoDocumentoIdentidad;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Empresa>
 */
class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    public function definition(): array
    {
        return [
            'nombre_comercial' => fake()->company(),
            'razon_social' => fake()->company().' S.A.S.',
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'nit' => fake()->unique()->numerify('90########'),
            'dv' => (string) fake()->numberBetween(0, 9),
            'email' => fake()->unique()->companyEmail(),
            'telefono' => fake()->phoneNumber(),
            'direccion' => fake()->streetAddress(),
            'ciudad' => fake()->city(),
            'departamento' => 'Antioquia',
            'codigo_postal' => '050001',
            'moneda' => 'COP',
            'simbolo_moneda' => '$',
            'estado' => EstadoGeneral::ACTIVO,
            'configuraciones' => [
                'decimales' => 2,
                'regimen' => 'Comun',
            ],
        ];
    }

    public function inactiva(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => EstadoGeneral::INACTIVO,
        ]);
    }
}
