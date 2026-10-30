<?php

namespace App\Actions\Inventario;

use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Enums\EstadoGeneral;
use App\Enums\TipoMovimientoInventario;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\ProductoPresentacion;
use App\Models\UnidadMedida;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConvertirProductoAMedidaAction
{
    public const REFERENCIA = 'CONVERSION-UNIDAD';

    public function execute(
        Producto $producto,
        float $factor,
        ?int $userId = null,
        ?float $precioVentaM2 = null
    ): ProductoPresentacion {
        if ($factor <= 0) {
            throw ValidationException::withMessages([
                'm2_por_caja' => 'El factor de conversión debe ser mayor a cero.',
            ]);
        }

        return DB::transaction(function () use ($producto, $factor, $userId, $precioVentaM2) {
            $producto = Producto::withoutGlobalScopes()
                ->where('empresa_id', $producto->empresa_id)
                ->lockForUpdate()
                ->findOrFail($producto->id);

            $unidadOrigen = UnidadMedida::withoutGlobalScopes()->find($producto->unidad_medida_id);
            $unidadDestino = UnidadMedida::withoutGlobalScopes()
                ->where('empresa_id', $producto->empresa_id)
                ->whereRaw('UPPER(codigo) = ?', ['M2'])
                ->first();

            if (! $unidadDestino) {
                throw ValidationException::withMessages([
                    'm2_por_caja' => 'No existe la unidad M2 para esta empresa. Actualiza los catálogos antes de convertir.',
                ]);
            }

            if ((int) $producto->unidad_medida_id === (int) $unidadDestino->id) {
                throw ValidationException::withMessages([
                    'm2_por_caja' => 'Este producto ya está medido en M2.',
                ]);
            }

            if (DB::table('lista_precio_detalles')->where('producto_id', $producto->id)->exists()) {
                throw ValidationException::withMessages([
                    'm2_por_caja' => 'El producto tiene precios especiales en listas de precios. Elimínalos antes de convertir.',
                ]);
            }

            if (DB::table('traslado_detalles')
                ->join('traslados_sucursal', 'traslados_sucursal.id', '=', 'traslado_detalles.traslado_sucursal_id')
                ->where('traslado_detalles.producto_id', $producto->id)
                ->where('traslados_sucursal.estado', 'EN_TRANSITO')
                ->exists()) {
                throw ValidationException::withMessages([
                    'm2_por_caja' => 'El producto tiene traslados en tránsito. Recíbelos antes de convertir.',
                ]);
            }

            $nombreCaja = $unidadOrigen?->nombre
                ? ucfirst(mb_strtolower($unidadOrigen->nombre))
                : 'Caja';
            $costoBase = round((float) $producto->precio_compra / $factor, 2);
            $precioVentaCaja = (float) $producto->precio_venta;

            $presentacion = ProductoPresentacion::withoutGlobalScopes()
                ->where('empresa_id', $producto->empresa_id)
                ->where('producto_id', $producto->id)
                ->where('unidad_medida_id', $unidadOrigen?->id)
                ->where('nombre', $nombreCaja)
                ->first();

            if ($presentacion) {
                if ($presentacion->trashed()) {
                    $presentacion->restore();
                }
                $presentacion->update([
                    'factor_conversion' => $factor,
                    'precio_venta' => $precioVentaCaja,
                    'es_predeterminada' => true,
                ]);
            } else {
                $presentacion = ProductoPresentacion::withoutGlobalScopes()->create([
                    'empresa_id' => $producto->empresa_id,
                    'producto_id' => $producto->id,
                    'unidad_medida_id' => $unidadOrigen?->id,
                    'nombre' => $nombreCaja,
                    'factor_conversion' => $factor,
                    'precio_venta' => $precioVentaCaja,
                    'es_predeterminada' => true,
                    'estado' => EstadoGeneral::ACTIVO,
                ]);
            }

            ProductoPresentacion::withoutGlobalScopes()
                ->where('producto_id', $producto->id)
                ->where('id', '<>', $presentacion->id)
                ->update(['es_predeterminada' => false]);

            $inventarios = Inventario::withoutGlobalScopes()
                ->where('producto_id', $producto->id)
                ->lockForUpdate()
                ->get();

            foreach ($inventarios as $inventario) {
                $anterior = (float) $inventario->stock;
                $nuevo = round($anterior * $factor, 4);
                $inventario->update([
                    'stock' => $nuevo,
                    'stock_minimo' => round((float) $inventario->stock_minimo * $factor, 4),
                ]);

                MovimientoInventario::withoutGlobalScopes()->create([
                    'empresa_id' => $producto->empresa_id,
                    'sucursal_id' => $inventario->sucursal_id,
                    'producto_id' => $producto->id,
                    'user_id' => $userId,
                    'tipo' => $nuevo >= $anterior
                        ? TipoMovimientoInventario::AJUSTE_POSITIVO
                        : TipoMovimientoInventario::AJUSTE_NEGATIVO,
                    'cantidad' => abs($nuevo - $anterior),
                    'costo_unitario' => $costoBase,
                    'stock_anterior' => $anterior,
                    'stock_posterior' => $nuevo,
                    'referencia' => self::REFERENCIA,
                    'notas' => sprintf(
                        'Conversión de unidad: %s %s = %s M2 (1 %s = %s M2). No es entrada ni salida de mercancía.',
                        $anterior,
                        $nombreCaja,
                        $nuevo,
                        $nombreCaja,
                        $factor
                    ),
                ]);
            }

            $stockBase = $inventarios->isEmpty()
                ? round((float) $producto->stock * $factor, 4)
                : round((float) $inventarios->sum('stock'), 4);

            $producto->update([
                'unidad_medida_id' => $unidadDestino->id,
                'precio_compra' => $costoBase,
                'precio_venta' => $precioVentaM2 ?? 0,
                'precio_mayorista' => $producto->precio_mayorista !== null
                    ? round((float) $producto->precio_mayorista / $factor, 2)
                    : null,
                'precio_distribuidor' => $producto->precio_distribuidor !== null
                    ? round((float) $producto->precio_distribuidor / $factor, 2)
                    : null,
                'stock' => $stockBase,
                'stock_minimo' => round((float) $producto->stock_minimo * $factor, 4),
            ]);

            DB::table('producto_lotes')->where('producto_id', $producto->id)->update([
                'stock_inicial' => DB::raw('stock_inicial * '.$factor),
                'stock_actual' => DB::raw('stock_actual * '.$factor),
            ]);

            DB::table('venta_detalles')
                ->where('producto_id', $producto->id)
                ->whereNull('presentacion_id')
                ->update([
                    'presentacion_id' => $presentacion->id,
                    'factor_conversion' => DB::raw('factor_conversion * '.$factor),
                ]);

            DB::table('cotizacion_detalles')
                ->where('producto_id', $producto->id)
                ->whereNull('presentacion_id')
                ->update([
                    'presentacion_id' => $presentacion->id,
                    'factor_conversion' => DB::raw('factor_conversion * '.$factor),
                ]);

            RegistrarAuditoriaAction::execute(
                accion: 'CONVERSION_UNIDAD',
                modulo: 'PRODUCTOS',
                model: $producto,
                datosAnteriores: ['unidad' => $unidadOrigen?->codigo, 'precio_venta' => $precioVentaCaja],
                datosNuevos: [
                    'unidad' => $unidadDestino->codigo,
                    'factor' => $factor,
                    'presentacion' => $nombreCaja,
                    'costo_base' => $costoBase,
                ],
                descripcion: "Producto {$producto->codigo} convertido a {$unidadDestino->codigo} (1 {$nombreCaja} = {$factor} {$unidadDestino->codigo})",
                empresaId: $producto->empresa_id
            );

            return $presentacion;
        });
    }
}
