<?php

namespace Database\Factories;

use App\Enums\EstadoCuentaCobrar;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CuentaPorCobrar>
 */
class CuentaPorCobrarFactory extends Factory
{
    protected $model = CuentaPorCobrar::class;

    public function definition(): array
    {
        $monto = fake()->randomFloat(2, 50000, 2000000);
        $fechaEmision = fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d');
        $fechaVencimiento = fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d');

        return [
            'empresa_id' => Empresa::factory(),
            'sucursal_id' => Sucursal::factory(),
            'cliente_id' => Cliente::factory(),
            'numero_documento' => 'CXC-'.fake()->unique()->numerify('#####'),
            'concepto' => fake()->randomElement([
                'Crédito Venta de Materiales',
                'Factura Comercial a Plazo',
                'Saldo Inicial de Cartera',
                'Suministros Ferreteros a Crédito',
            ]),
            'monto_total' => $monto,
            'monto_pagado' => 0,
            'saldo_pendiente' => $monto,
            'fecha_emision' => $fechaEmision,
            'fecha_vencimiento' => $fechaVencimiento,
            'estado' => EstadoCuentaCobrar::PENDIENTE,
            'observaciones' => fake()->optional()->sentence(),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Estado para cuenta con abono parcial.
     */
    public function parcial(float $porcentajeAbono = 0.5): static
    {
        return $this->state(function (array $attributes) use ($porcentajeAbono) {
            $total = (float) $attributes['monto_total'];
            $pagado = round($total * $porcentajeAbono, 2);
            $saldo = round($total - $pagado, 2);

            return [
                'monto_pagado' => $pagado,
                'saldo_pendiente' => $saldo,
                'estado' => EstadoCuentaCobrar::PARCIAL,
            ];
        });
    }

    /**
     * Estado para cuenta totalmente cancelada.
     */
    public function pagada(): static
    {
        return $this->state(function (array $attributes) {
            $total = (float) $attributes['monto_total'];

            return [
                'monto_pagado' => $total,
                'saldo_pendiente' => 0,
                'estado' => EstadoCuentaCobrar::PAGADA,
            ];
        });
    }

    /**
     * Estado para cuenta vencida (en mora).
     */
    public function vencida(int $diasMora = 15): static
    {
        return $this->state(fn (array $attributes) => [
            'fecha_emision' => now()->subDays($diasMora + 30)->toDateString(),
            'fecha_vencimiento' => now()->subDays($diasMora)->toDateString(),
            'estado' => EstadoCuentaCobrar::PENDIENTE,
        ]);
    }
}
