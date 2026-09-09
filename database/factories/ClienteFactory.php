<?php

namespace Database\Factories;

use App\Enums\EstadoGeneral;
use App\Enums\TipoDocumentoIdentidad;
use App\Enums\TipoPersona;
use App\Models\Cliente;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'empresa_id' => Empresa::factory(),
            'tipo_persona' => TipoPersona::NATURAL,
            'tipo_documento' => TipoDocumentoIdentidad::CC,
            'numero_documento' => fake()->unique()->numerify('10########'),
            'razon_social' => fake()->name(),
            'nombre_comercial' => null,
            'telefono' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'direccion' => fake()->streetAddress(),
            'ciudad' => fake()->city(),
            'departamento' => 'Sucre',
            'cupo_credito' => 0,
            'plazo_dias' => 0,
            'estado' => EstadoGeneral::ACTIVO,
            'es_predeterminado' => false,
        ];
    }

    /**
     * Estado para cliente tipo persona jurídica.
     */
    public function juridica(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_persona' => TipoPersona::JURIDICA,
            'tipo_documento' => TipoDocumentoIdentidad::NIT,
            'numero_documento' => fake()->unique()->numerify('90########'),
            'razon_social' => fake()->company().' S.A.S.',
            'nombre_comercial' => fake()->company(),
        ]);
    }

    /**
     * Estado para cliente con crédito comercial.
     */
    public function conCredito(float $cupo = 1000000, int $plazo = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'cupo_credito' => $cupo,
            'plazo_dias' => $plazo,
        ]);
    }

    /**
     * Estado para el Consumidor Final predeterminado.
     */
    public function consumidorFinal(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_persona' => TipoPersona::NATURAL,
            'tipo_documento' => TipoDocumentoIdentidad::CC,
            'numero_documento' => '222222222222',
            'razon_social' => 'CONSUMIDOR FINAL',
            'nombre_comercial' => null,
            'telefono' => null,
            'email' => null,
            'direccion' => 'Mostrador / Local',
            'ciudad' => null,
            'departamento' => null,
            'cupo_credito' => 0,
            'plazo_dias' => 0,
            'estado' => EstadoGeneral::ACTIVO,
            'es_predeterminado' => true,
        ]);
    }
}
