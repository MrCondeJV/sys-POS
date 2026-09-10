<?php

namespace Database\Seeders;

use App\Actions\Cartera\RegistrarAbonoCarteraAction;
use App\Actions\Cartera\RegistrarCuentaPorCobrarAction;
use App\Enums\MetodoPagoCartera;
use App\Models\Cliente;
use App\Models\Empresa;
use Illuminate\Database\Seeder;

class CarteraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $registrarCuentaAction = app(RegistrarCuentaPorCobrarAction::class);
        $registrarAbonoAction = app(RegistrarAbonoCarteraAction::class);

        foreach (Empresa::withoutGlobalScopes()->get() as $empresa) {
            $sucursal = $empresa->sucursalPrincipal ?? $empresa->sucursales()->first();
            $admin = $empresa->users()->first();

            // Clientes con crédito en la empresa
            $clienteCreditoA = Cliente::withoutGlobalScopes()
                ->where('empresa_id', $empresa->id)
                ->where('numero_documento', '901234567') // Ferretería Caribe
                ->first();

            $clienteCreditoB = Cliente::withoutGlobalScopes()
                ->where('empresa_id', $empresa->id)
                ->where('numero_documento', '1045678901') // Juan Carlos Pérez
                ->first();

            if ($clienteCreditoA) {
                // 1. Cuenta Vigente
                $cuenta1 = $registrarCuentaAction->execute(
                    empresaId: $empresa->id,
                    sucursalId: $sucursal?->id,
                    clienteId: $clienteCreditoA->id,
                    montoTotal: 1250000.00,
                    fechaEmision: now()->subDays(5)->toDateString(),
                    fechaVencimiento: now()->addDays(25)->toDateString(),
                    concepto: 'Factura a Crédito de Materiales de Construcción #0012',
                    observaciones: 'Pago acordado a 30 días.',
                    userId: $admin?->id
                );

                // 2. Cuenta con Abono Parcial
                $cuenta2 = $registrarCuentaAction->execute(
                    empresaId: $empresa->id,
                    sucursalId: $sucursal?->id,
                    clienteId: $clienteCreditoA->id,
                    montoTotal: 850000.00,
                    fechaEmision: now()->subDays(20)->toDateString(),
                    fechaVencimiento: now()->addDays(10)->toDateString(),
                    concepto: 'Factura a Crédito Tuberías y Grifería #0008',
                    observaciones: null,
                    userId: $admin?->id
                );

                $registrarAbonoAction->execute(
                    cuenta: $cuenta2,
                    monto: 350000.00,
                    metodoPago: MetodoPagoCartera::TRANSFERENCIA,
                    fechaPago: now()->subDays(2)->toDateString(),
                    referenciaPago: 'Bancolombia TR-98231',
                    notas: 'Abono 1 por transferencia',
                    userId: $admin?->id
                );
            }

            if ($clienteCreditoB) {
                // 3. Cuenta Vencida (en mora)
                $cuenta3 = $registrarCuentaAction->execute(
                    empresaId: $empresa->id,
                    sucursalId: $sucursal?->id,
                    clienteId: $clienteCreditoB->id,
                    montoTotal: 420000.00,
                    fechaEmision: now()->subDays(40)->toDateString(),
                    fechaVencimiento: now()->subDays(10)->toDateString(), // Vencida hace 10 días
                    concepto: 'Crédito Herramientas Manuales y Taladro #0004',
                    observaciones: 'Cliente notificado por WhatsApp.',
                    userId: $admin?->id
                );

                // 4. Cuenta Totalmente Pagada
                $cuenta4 = $registrarCuentaAction->execute(
                    empresaId: $empresa->id,
                    sucursalId: $sucursal?->id,
                    clienteId: $clienteCreditoB->id,
                    montoTotal: 250000.00,
                    fechaEmision: now()->subDays(30)->toDateString(),
                    fechaVencimiento: now()->subDays(5)->toDateString(),
                    concepto: 'Compra de Pinturas y Accesorios #0001',
                    observaciones: null,
                    userId: $admin?->id
                );

                $registrarAbonoAction->execute(
                    cuenta: $cuenta4,
                    monto: 250000.00,
                    metodoPago: MetodoPagoCartera::EFECTIVO,
                    fechaPago: now()->subDays(6)->toDateString(),
                    referenciaPago: null,
                    notas: 'Pago total en caja principal',
                    userId: $admin?->id
                );
            }
        }
    }
}
