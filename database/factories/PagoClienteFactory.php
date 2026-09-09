<?php

namespace Database\Factories;

use App\Enums\EstadoPagoCartera;
use App\Enums\MetodoPagoCartera;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\Empresa;
use App\Models\PagoCliente;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PagoCliente>
 */
class PagoClienteFactory extends Factory
{
    protected $model = PagoCliente::class;

    public function definition(): array
    {
        $monto = fake()->randomFloat(2, 20000, 300000);

        return [
            'empresa_id' => Empresa::factory(),
            'sucursal_id' => Sucursal::factory(),
            'cuenta_por_cobrar_id' => CuentaPorCobrar::factory(),
            'cliente_id' => Cliente::factory(),
            'numero_recibo' => 'RC-'.fake()->unique()->numerify('#####'),
            'monto' => $monto,
            'metodo_pago' => MetodoPagoCartera::EFECTIVO,
            'referencia_pago' => fake()->optional()->bothify('TR-######'),
            'fecha_pago' => now()->toDateString(),
            'saldo_anterior' => $monto * 2,
            'saldo_posterior' => $monto,
            'notas' => fake()->optional()->sentence(),
            'user_id' => User::factory(),
            'estado' => EstadoPagoCartera::APLICADO,
        ];
    }
}
