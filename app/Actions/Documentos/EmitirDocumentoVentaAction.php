<?php

namespace App\Actions\Documentos;

use App\Actions\Auditoria\RegistrarAuditoriaAction;
use App\Enums\EstadoDocumentoVenta;
use App\Enums\TipoDocumentoVenta;
use App\Models\DocumentoVenta;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class EmitirDocumentoVentaAction
{
    /**
     * Emite un documento comercial formal a partir de una Venta.
     */
    public function execute(
        Venta $venta,
        TipoDocumentoVenta $tipo = TipoDocumentoVenta::TICKET,
        ?string $prefijo = null,
        ?string $observaciones = null,
        ?array $metadatos = null
    ): DocumentoVenta {
        return DB::transaction(function () use ($venta, $tipo, $prefijo, $observaciones, $metadatos) {
            $empresaId = $venta->empresa_id;
            $prefijoEfectivo = $prefijo ?? $tipo->prefijoPorDefecto();

            // Obtener el siguiente consecutivo de manera segura con bloqueo
            $ultimoNumero = DocumentoVenta::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->where('tipo', $tipo->value)
                ->lockForUpdate()
                ->max('numero');

            $siguienteNumero = ($ultimoNumero ?? 0) + 1;
            $numeroCompleto = sprintf('%s-%06d', $prefijoEfectivo, $siguienteNumero);

            $documento = DocumentoVenta::create([
                'empresa_id' => $empresaId,
                'sucursal_id' => $venta->sucursal_id,
                'venta_id' => $venta->id,
                'cliente_id' => $venta->cliente_id,
                'user_id' => $venta->user_id,
                'tipo' => $tipo,
                'estado' => EstadoDocumentoVenta::EMITIDO,
                'prefijo' => $prefijoEfectivo,
                'numero' => $siguienteNumero,
                'numero_completo' => $numeroCompleto,
                'fecha_emision' => $venta->created_at ?? now(),
                'subtotal' => $venta->subtotal,
                'impuesto_total' => $venta->impuestos ?? 0,
                'descuento_total' => $venta->descuento ?? 0,
                'total' => $venta->total,
                'observaciones' => $observaciones ?? $venta->observaciones,
                'metadatos' => $metadatos,
            ]);

            // Registrar auditoría de emisión
            RegistrarAuditoriaAction::execute(
                accion: 'EMITIR_DOCUMENTO_VENTA',
                modulo: 'DOCUMENTOS',
                model: $documento,
                datosAnteriores: null,
                datosNuevos: [
                    'numero_completo' => $numeroCompleto,
                    'tipo' => $tipo->value,
                    'total' => $venta->total,
                    'venta_id' => $venta->id,
                ],
                descripcion: "Emisión de {$tipo->label()} {$numeroCompleto} para la venta {$venta->numero_venta}",
                usuario: $venta->usuario ?? auth()->user(),
                empresaId: $empresaId
            );

            return $documento;
        });
    }
}
