<?php

namespace App\Console\Commands;

use App\Enums\EstadoSesionCaja;
use App\Models\CajaSesion;
use App\Models\CuentaPorCobrar;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerificarIntegridadSistema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sistema:verificar-integridad {--empresa= : Filtrar verificación por ID de empresa}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta auditoría de solo lectura sobre invariantes contables, de inventario, caja, cartera y tenencia.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $empresaId = $this->option('empresa') ? (int) $this->option('empresa') : null;
        $anomalias = [];

        $this->info('Iniciando auditoría de integridad de solo lectura (sys-POS)...');

        // 1. Verificar ventas cuyos detalles no cuadran con el total de cabecera
        $ventasQuery = Venta::withoutGlobalScopes()->with('detalles');
        if ($empresaId) {
            $ventasQuery->where('empresa_id', $empresaId);
        }

        foreach ($ventasQuery->cursor() as $venta) {
            if ($venta->detalles->isNotEmpty()) {
                $sumaDetalles = round((float) $venta->detalles->sum('total'), 2);
                $totalCabecera = round((float) $venta->total, 2);

                if (abs($sumaDetalles - $totalCabecera) > 0.05) {
                    $anomalias[] = sprintf(
                        '[VENTAS] Venta #%d (%s): total cabecera (%.2f) != suma detalles (%.2f)',
                        $venta->id,
                        $venta->numero_venta,
                        $totalCabecera,
                        $sumaDetalles
                    );
                }
            }
        }

        // 2. Verificar productos con stock negativo
        $productosNegativos = Producto::withoutGlobalScopes()
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->where('stock', '<', 0)
            ->get(['id', 'codigo', 'nombre', 'stock']);

        foreach ($productosNegativos as $prod) {
            $anomalias[] = sprintf(
                '[INVENTARIO] Producto #%d (%s - %s) tiene stock negativo: %.4f',
                $prod->id,
                $prod->codigo,
                $prod->nombre,
                (float) $prod->stock
            );
        }

        // 3. Verificar invariante de cuentas por cobrar: saldo_pendiente == monto_total - monto_pagado
        $cuentasQuery = CuentaPorCobrar::withoutGlobalScopes()
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId));

        foreach ($cuentasQuery->cursor() as $cuenta) {
            $esperado = round((float) $cuenta->monto_total - (float) $cuenta->monto_pagado, 2);
            $actual = round((float) $cuenta->saldo_pendiente, 2);

            if (abs($esperado - $actual) > 0.05 || $actual < -0.01) {
                $anomalias[] = sprintf(
                    '[CARTERA] Cuenta #%d (%s): saldo_pendiente (%.2f) != monto_total - monto_pagado (%.2f)',
                    $cuenta->id,
                    $cuenta->numero_documento,
                    $actual,
                    $esperado
                );
            }
        }

        // 4. Verificar unicidad de sesiones ABIERTAS por caja y cuadre matemático de diferencia en cerradas
        $cajasConMultiplesSesiones = DB::table('cajas_sesiones')
            ->select('caja_id', DB::raw('COUNT(*) as abiertas'))
            ->whereNull('deleted_at')
            ->where('estado', EstadoSesionCaja::ABIERTA->value)
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->groupBy('caja_id')
            ->having('abiertas', '>', 1)
            ->get();

        foreach ($cajasConMultiplesSesiones as $fila) {
            $anomalias[] = sprintf(
                '[CAJAS] La caja #%d tiene %d sesiones ABIERTAS simultáneamente.',
                $fila->caja_id,
                $fila->abiertas
            );
        }

        $sesionesCerradas = CajaSesion::withoutGlobalScopes()
            ->where('estado', EstadoSesionCaja::CERRADA->value)
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))
            ->get();

        foreach ($sesionesCerradas as $sesion) {
            $difCalculada = round((float) $sesion->monto_cierre_contado - (float) $sesion->monto_cierre_esperado, 2);
            $difRegistrada = round((float) $sesion->diferencia, 2);

            if (abs($difCalculada - $difRegistrada) > 0.05) {
                $anomalias[] = sprintf(
                    '[CAJAS] Sesión #%d: diferencia registrada (%.2f) != contado - esperado (%.2f)',
                    $sesion->id,
                    $difRegistrada,
                    $difCalculada
                );
            }
        }

        // 5. Verificar registros huérfanos sin empresa_id en tablas multi-tenant
        $tablasTenant = ['sucursales', 'productos', 'clientes', 'proveedores', 'cajas', 'ventas', 'compras', 'cuentas_por_cobrar'];
        foreach ($tablasTenant as $tabla) {
            $huerfanos = DB::table($tabla)->whereNull('empresa_id')->count();
            if ($huerfanos > 0) {
                $anomalias[] = sprintf(
                    '[TENENCIA] Tabla "%s" contiene %d registro(s) sin empresa_id.',
                    $tabla,
                    $huerfanos
                );
            }
        }

        if (empty($anomalias)) {
            $this->info('OK: Todos los invariantes de integridad financiera, stock, caja, cartera y tenencia se cumplen.');

            return self::SUCCESS;
        }

        $this->error(sprintf('Se detectaron %d anomalía(s) de integridad:', count($anomalias)));
        foreach ($anomalias as $anomalia) {
            $this->line(' - '.$anomalia);
        }

        return self::FAILURE;
    }
}
