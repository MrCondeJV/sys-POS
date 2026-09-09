<?php

namespace App\Services;

use App\Contracts\ProveedorFacturacionElectronicaInterface;
use App\Enums\EstadoDian;
use App\Enums\TipoDocumentoElectronico;
use App\Models\DocumentoElectronico;
use App\Models\DocumentoVenta;
use App\Models\ResolucionFacturacion;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FacturacionElectronicaService
{
    public function __construct(
        protected ProveedorFacturacionElectronicaInterface $proveedor
    ) {}

    /**
     * Emite y transmite una Factura Electrónica a partir de un DocumentoVenta emitido.
     */
    public function emitirFacturaElectronica(DocumentoVenta $documento, ?ResolucionFacturacion $resolucion = null): DocumentoElectronico
    {
        return DB::transaction(function () use ($documento, $resolucion) {
            $empresa = $documento->empresa;

            // Buscar resolución activa si no se especificó
            if (!$resolucion) {
                $resolucion = ResolucionFacturacion::where('empresa_id', $empresa->id)
                    ->where('es_predeterminada', true)
                    ->first()
                    ?? ResolucionFacturacion::where('empresa_id', $empresa->id)
                        ->latest('id')
                        ->first();
            }

            if (!$resolucion || !$resolucion->estaVigente()) {
                throw new Exception("No existe una resolución de facturación electrónica activa y vigente para la empresa.");
            }

            // Obtener siguiente consecutivo oficial
            $consecutivo = $resolucion->obtenerSiguienteConsecutivo();
            $consecutivoCompleto = $resolucion->prefijo . '-' . str_pad((string) $consecutivo, 6, '0', STR_PAD_LEFT);

            // Transmitir al proveedor / DIAN
            $respuesta = $this->proveedor->transmitirFactura($documento, $resolucion, $consecutivo);

            // Guardar XML si se generó
            $xmlPath = null;
            if (!empty($respuesta['xml_firmado'])) {
                $xmlPath = "facturacion_electronica/{$empresa->id}/{$consecutivoCompleto}.xml";
                Storage::disk('local')->put($xmlPath, $respuesta['xml_firmado']);
            }

            $estadoDian = $respuesta['success'] ? EstadoDian::ACEPTADO : EstadoDian::RECHAZADO;

            // Registrar Documento Electrónico
            $docElectronico = DocumentoElectronico::create([
                'empresa_id' => $empresa->id,
                'documento_venta_id' => $documento->id,
                'resolucion_id' => $resolucion->id,
                'tipo' => TipoDocumentoElectronico::FACTURA_ELECTRONICA,
                'cufe' => $respuesta['cufe'] ?? null,
                'prefijo' => $resolucion->prefijo,
                'numero' => $consecutivo,
                'consecutivo_completo' => $consecutivoCompleto,
                'estado_dian' => $estadoDian,
                'codigo_respuesta_dian' => $respuesta['codigo_respuesta'] ?? null,
                'mensaje_dian' => $respuesta['mensaje'] ?? null,
                'xml_path' => $xmlPath,
                'qr_data' => $respuesta['qr_data'] ?? null,
                'metadatos_envio' => [
                    'resolucion' => $resolucion->numero_resolucion,
                    'rango' => "{$resolucion->rango_desde} - {$resolucion->rango_hasta}",
                ],
                'metadatos_respuesta' => $respuesta['metadatos'] ?? null,
                'fecha_emision' => now(),
                'fecha_validacion' => $respuesta['success'] ? now() : null,
            ]);

            return $docElectronico;
        });
    }

    /**
     * Emite una Nota Crédito Electrónica para anular o ajustar una Factura Electrónica.
     */
    public function emitirNotaCredito(DocumentoElectronico $facturaElectronica, string $motivo): DocumentoElectronico
    {
        return DB::transaction(function () use ($facturaElectronica, $motivo) {
            $documentoVenta = $facturaElectronica->documentoVenta;
            $empresa = $facturaElectronica->empresa;

            $consecutivo = DocumentoElectronico::where('empresa_id', $empresa->id)
                ->where('tipo', TipoDocumentoElectronico::NOTA_CREDITO_ELECTRONICA)
                ->count() + 1;

            $consecutivoCompleto = 'NC-' . str_pad((string) $consecutivo, 6, '0', STR_PAD_LEFT);

            $respuesta = $this->proveedor->transmitirNotaCredito(
                documentoOriginal: $documentoVenta,
                cufeOriginal: $facturaElectronica->cufe ?? '',
                motivo: $motivo,
                consecutivo: $consecutivo
            );

            $xmlPath = null;
            if (!empty($respuesta['xml_firmado'])) {
                $xmlPath = "facturacion_electronica/{$empresa->id}/{$consecutivoCompleto}.xml";
                Storage::disk('local')->put($xmlPath, $respuesta['xml_firmado']);
            }

            return DocumentoElectronico::create([
                'empresa_id' => $empresa->id,
                'documento_venta_id' => $documentoVenta->id,
                'resolucion_id' => $facturaElectronica->resolucion_id,
                'tipo' => TipoDocumentoElectronico::NOTA_CREDITO_ELECTRONICA,
                'cufe' => $respuesta['cufe'] ?? null,
                'prefijo' => 'NC',
                'numero' => $consecutivo,
                'consecutivo_completo' => $consecutivoCompleto,
                'estado_dian' => EstadoDian::ACEPTADO,
                'codigo_respuesta_dian' => $respuesta['codigo_respuesta'] ?? null,
                'mensaje_dian' => $respuesta['mensaje'] ?? null,
                'xml_path' => $xmlPath,
                'qr_data' => $respuesta['qr_data'] ?? null,
                'metadatos_envio' => [
                    'factura_afectada' => $facturaElectronica->consecutivo_completo,
                    'motivo' => $motivo,
                ],
                'metadatos_respuesta' => $respuesta['metadatos'] ?? null,
                'fecha_emision' => now(),
                'fecha_validacion' => now(),
            ]);
        });
    }

    /**
     * Reintenta la transmisión de un documento que quedó pendiente o con error.
     */
    public function reintentarTransmision(DocumentoElectronico $documento): DocumentoElectronico
    {
        $respuesta = $this->proveedor->transmitirFactura(
            $documento->documentoVenta,
            $documento->resolucion,
            $documento->numero
        );

        $documento->update([
            'cufe' => $respuesta['cufe'] ?? $documento->cufe,
            'estado_dian' => $respuesta['success'] ? EstadoDian::ACEPTADO : EstadoDian::ERROR,
            'codigo_respuesta_dian' => $respuesta['codigo_respuesta'] ?? null,
            'mensaje_dian' => $respuesta['mensaje'] ?? null,
            'metadatos_respuesta' => $respuesta['metadatos'] ?? null,
            'fecha_validacion' => $respuesta['success'] ? now() : null,
        ]);

        return $documento;
    }
}
