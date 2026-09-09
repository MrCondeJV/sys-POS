<?php

namespace App\Services;

use App\Enums\FormatoImpresion;
use App\Models\AbonoCartera;
use App\Models\CajaSesion;
use App\Models\DocumentoVenta;
use App\Models\Venta;
use Illuminate\View\View;

class ImpresionService
{
    /**
     * Renderiza un comprobante de venta o documento comercial en el formato solicitado.
     */
    public function renderVenta(Venta $venta, FormatoImpresion $formato = FormatoImpresion::TICKET_80MM): View
    {
        $venta->loadMissing(['empresa', 'sucursal', 'cliente', 'usuario', 'detalles.producto', 'pagos', 'documentoVenta']);

        return match ($formato) {
            FormatoImpresion::TICKET_58MM => view('impresion.ticket_58mm', [
                'venta' => $venta,
                'documento' => $venta->documentoVenta,
                'empresa' => $venta->empresa,
                'sucursal' => $venta->sucursal,
                'cliente' => $venta->cliente,
            ]),
            FormatoImpresion::CARTA_PDF => view('impresion.factura_carta', [
                'venta' => $venta,
                'documento' => $venta->documentoVenta,
                'empresa' => $venta->empresa,
                'sucursal' => $venta->sucursal,
                'cliente' => $venta->cliente,
            ]),
            default => view('impresion.ticket_80mm', [
                'venta' => $venta,
                'documento' => $venta->documentoVenta,
                'empresa' => $venta->empresa,
                'sucursal' => $venta->sucursal,
                'cliente' => $venta->cliente,
            ]),
        };
    }

    /**
     * Renderiza un documento comercial formal (Factura, Ticket, Doc Equivalente).
     */
    public function renderDocumento(DocumentoVenta $documento, FormatoImpresion $formato = FormatoImpresion::TICKET_80MM): View
    {
        $documento->loadMissing([
            'empresa',
            'sucursal',
            'cliente',
            'usuario',
            'venta.detalles.producto',
            'venta.pagos',
        ]);

        $venta = $documento->venta;

        return match ($formato) {
            FormatoImpresion::TICKET_58MM => view('impresion.ticket_58mm', [
                'documento' => $documento,
                'venta' => $venta,
                'empresa' => $documento->empresa,
                'sucursal' => $documento->sucursal,
                'cliente' => $documento->cliente,
            ]),
            FormatoImpresion::CARTA_PDF => view('impresion.factura_carta', [
                'documento' => $documento,
                'venta' => $venta,
                'empresa' => $documento->empresa,
                'sucursal' => $documento->sucursal,
                'cliente' => $documento->cliente,
            ]),
            default => view('impresion.ticket_80mm', [
                'documento' => $documento,
                'venta' => $venta,
                'empresa' => $documento->empresa,
                'sucursal' => $documento->sucursal,
                'cliente' => $documento->cliente,
            ]),
        };
    }

    /**
     * Renderiza el comprobante de cierre o arqueo de caja.
     */
    public function renderCajaCierre(CajaSesion $sesion, FormatoImpresion $formato = FormatoImpresion::TICKET_80MM): View
    {
        $sesion->loadMissing(['caja.sucursal.empresa', 'cajero', 'cajeroCierre', 'movimientos']);

        return view('impresion.caja_cierre', [
            'sesion' => $sesion,
            'caja' => $sesion->caja,
            'sucursal' => $sesion->caja->sucursal,
            'empresa' => $sesion->caja->sucursal->empresa,
            'formato' => $formato,
        ]);
    }

    /**
     * Renderiza un comprobante de abono a cartera / recibo de caja.
     */
    public function renderAbono(\App\Models\PagoCliente $pago, FormatoImpresion $formato = FormatoImpresion::TICKET_80MM): View
    {
        $pago->loadMissing(['cliente', 'cuentaPorCobrar.empresa', 'cuentaPorCobrar.sucursal', 'usuario']);

        return view('impresion.recibo_pago', [
            'abono' => $pago,
            'pago' => $pago,
            'cuenta' => $pago->cuentaPorCobrar,
            'cliente' => $pago->cliente,
            'empresa' => $pago->cuentaPorCobrar?->empresa,
            'sucursal' => $pago->cuentaPorCobrar?->sucursal,
            'formato' => $formato,
        ]);
    }
}
