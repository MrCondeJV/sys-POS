<?php

namespace App\Actions\Cartera;

use App\Enums\EstadoCuentaCobrar;
use App\Models\CuentaPorCobrar;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RegistrarCuentaPorCobrarAction
{
    /**
     * Registra una nueva cuenta por cobrar garantizando integridad transaccional
     * y generación atómica de consecutivo por empresa.
     */
    public function execute(
        int $empresaId,
        ?int $sucursalId,
        int $clienteId,
        float $montoTotal,
        string $fechaEmision,
        string $fechaVencimiento,
        string $concepto,
        ?string $observaciones = null,
        ?int $userId = null
    ): CuentaPorCobrar {
        if ($montoTotal <= 0) {
            throw new InvalidArgumentException('El monto total de la cuenta por cobrar debe ser mayor a cero.');
        }

        return DB::transaction(function () use (
            $empresaId,
            $sucursalId,
            $clienteId,
            $montoTotal,
            $fechaEmision,
            $fechaVencimiento,
            $concepto,
            $observaciones,
            $userId
        ) {
            // Generación de consecutivo atómico CXC-00001
            $ultimoRegistro = CuentaPorCobrar::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            $siguienteNumero = 1;
            if ($ultimoRegistro && preg_match('/CXC-(\d+)/', $ultimoRegistro->numero_documento, $matches)) {
                $siguienteNumero = (int) $matches[1] + 1;
            }

            $numeroDocumento = sprintf('CXC-%05d', $siguienteNumero);

            return CuentaPorCobrar::create([
                'empresa_id' => $empresaId,
                'sucursal_id' => $sucursalId,
                'cliente_id' => $clienteId,
                'numero_documento' => $numeroDocumento,
                'concepto' => $concepto,
                'monto_total' => $montoTotal,
                'monto_pagado' => 0,
                'saldo_pendiente' => $montoTotal,
                'fecha_emision' => $fechaEmision,
                'fecha_vencimiento' => $fechaVencimiento,
                'estado' => EstadoCuentaCobrar::PENDIENTE,
                'observaciones' => $observaciones,
                'created_by' => $userId,
            ]);
        });
    }
}
