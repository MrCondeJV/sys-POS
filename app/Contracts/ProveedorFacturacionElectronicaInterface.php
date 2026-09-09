<?php

namespace App\Contracts;

use App\Models\DocumentoVenta;
use App\Models\ResolucionFacturacion;

interface ProveedorFacturacionElectronicaInterface
{
    /**
     * Transmite una factura electrónica hacia la DIAN.
     *
     * @return array{
     *     success: bool,
     *     cufe: string,
     *     codigo_respuesta: string,
     *     mensaje: string,
     *     xml_firmado: string,
     *     qr_data: string,
     *     metadatos: array
     * }
     */
    public function transmitirFactura(DocumentoVenta $documento, ResolucionFacturacion $resolucion, int $consecutivo): array;

    /**
     * Transmite una nota crédito electrónica hacia la DIAN.
     */
    public function transmitirNotaCredito(DocumentoVenta $documentoOriginal, string $cufeOriginal, string $motivo, int $consecutivo): array;

    /**
     * Consulta el estado de un CUFE en la DIAN.
     */
    public function consultarEstado(string $cufe): array;
}
