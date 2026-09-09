<?php

namespace App\Services;

use App\Contracts\ProveedorFacturacionElectronicaInterface;
use App\Models\DocumentoVenta;
use App\Models\ResolucionFacturacion;
use Illuminate\Support\Str;

class MockProveedorFacturacionService implements ProveedorFacturacionElectronicaInterface
{
    /**
     * Simula la transmisión hacia la DIAN y cálculo de CUFE conforme al estándar UBL 2.1 colombiano.
     */
    public function transmitirFactura(DocumentoVenta $documento, ResolucionFacturacion $resolucion, int $consecutivo): array
    {
        $empresa = $documento->empresa;
        $cliente = $documento->cliente;

        $numFac = $resolucion->prefijo . $consecutivo;
        $fecFac = $documento->fecha_emision->format('Y-m-d');
        $horFac = $documento->fecha_emision->format('H:i:s-05:00');
        $valFac = number_format($documento->subtotal, 2, '.', '');
        $codImp1 = '01'; // IVA
        $valImp1 = number_format($documento->impuesto_total, 2, '.', '');
        $valTot = number_format($documento->total, 2, '.', '');
        $nitOfe = preg_replace('/[^0-9]/', '', $empresa->nit ?? '900000000');
        $numAdq = preg_replace('/[^0-9]/', '', $cliente?->numero_documento ?? '222222222222');
        $claveTecnica = $resolucion->clave_tecnica ?? 'fc8eac422eba16e22ffd8c6f94b3f40a6e3811e8';
        $tipoAmbiente = '2'; // 1=Producción, 2=Habilitación/Pruebas

        // Algoritmo CUFE: SHA-384
        $cadenaCufe = "{$numFac}{$fecFac}{$horFac}{$valFac}{$codImp1}{$valImp1}040.00030.00{$valTot}{$nitOfe}{$numAdq}{$claveTecnica}{$tipoAmbiente}";
        $cufe = hash('sha384', $cadenaCufe);

        // QR Data requerido por DIAN
        $qrData = "NumFac={$numFac}\nFecFac={$fecFac}\nHorFac={$horFac}\nNitFac={$nitOfe}\nDocAdq={$numAdq}\nValFac={$valFac}\nValIva={$valImp1}\nValOtroIm=0.00\nValTotal={$valTot}\nCUFE={$cufe}";

        // XML Simulado UBL 2.1
        $xmlSimulado = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">
    <cbc:UBLVersionID>UBL 2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>10</cbc:CustomizationID>
    <cbc:ProfileID>DIAN 2.1: Factura Electrónica de Venta</cbc:ProfileID>
    <cbc:ID>{$numFac}</cbc:ID>
    <cbc:UUID schemeName="CUFE-SHA384">{$cufe}</cbc:UUID>
    <cbc:IssueDate>{$fecFac}</cbc:IssueDate>
    <cbc:IssueTime>{$horFac}</cbc:IssueTime>
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyTaxScheme>
                <cbc:RegistrationName>{$empresa->nombre_comercial}</cbc:RegistrationName>
                <cbc:CompanyID schemeAgencyID="195">{$nitOfe}</cbc:CompanyID>
            </cac:PartyTaxScheme>
        </cac:Party>
    </cac:AccountingSupplierParty>
    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="COP">{$valFac}</cbc:LineExtensionAmount>
        <cbc:TaxExclusiveAmount currencyID="COP">{$valImp1}</cbc:TaxExclusiveAmount>
        <cbc:PayableAmount currencyID="COP">{$valTot}</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
</Invoice>
XML;

        return [
            'success' => true,
            'cufe' => $cufe,
            'codigo_respuesta' => 'DIAN-00',
            'mensaje' => 'La Factura Electrónica ' . $numFac . ' ha sido Aprobada con éxito por la DIAN.',
            'xml_firmado' => $xmlSimulado,
            'qr_data' => $qrData,
            'metadatos' => [
                'ambiente' => 'Habilitacion',
                'track_id' => Str::uuid()->toString(),
                'version' => 'UBL 2.1',
                'fecha_recepcion' => now()->toIso8601String(),
            ],
        ];
    }

    public function transmitirNotaCredito(DocumentoVenta $documentoOriginal, string $cufeOriginal, string $motivo, int $consecutivo): array
    {
        $numNC = 'NC-' . str_pad((string) $consecutivo, 6, '0', STR_PAD_LEFT);
        $cude = hash('sha384', $numNC . $cufeOriginal . $motivo . now()->toIso8601String());

        $xmlSimulado = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<CreditNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2">
    <cbc:ID>{$numNC}</cbc:ID>
    <cbc:UUID schemeName="CUDE-SHA384">{$cude}</cbc:UUID>
    <cac:DiscrepancyResponse>
        <cbc:ReferenceID>{$documentoOriginal->numero_completo}</cbc:ReferenceID>
        <cbc:Description>{$motivo}</cbc:Description>
    </cac:DiscrepancyResponse>
</CreditNote>
XML;

        return [
            'success' => true,
            'cufe' => $cude,
            'codigo_respuesta' => 'DIAN-00',
            'mensaje' => 'Nota Crédito Electrónica ' . $numNC . ' validada con éxito.',
            'xml_firmado' => $xmlSimulado,
            'qr_data' => "NumNC={$numNC}\nCUDE={$cude}\nFacturaRef={$documentoOriginal->numero_completo}",
            'metadatos' => [
                'motivo' => $motivo,
                'cufe_referencia' => $cufeOriginal,
            ],
        ];
    }

    public function consultarEstado(string $cufe): array
    {
        return [
            'cufe' => $cufe,
            'estado' => 'ACEPTADO',
            'mensaje' => 'Documento validado previamente y registrado en el sistema DIAN.',
            'fecha_consulta' => now()->toIso8601String(),
        ];
    }
}
