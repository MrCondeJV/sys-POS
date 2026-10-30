<?php

namespace App\Services;

use App\Models\Cotizacion;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;

/**
 * Genera los PDF tamaño carta de ventas y cotizaciones (para descargar, adjuntar por correo o compartir por WhatsApp).
 */
class DocumentoPdfService
{
    public function cotizacion(Cotizacion $cotizacion): DomPdf
    {
        $cotizacion->loadMissing(['empresa', 'sucursal', 'cliente', 'usuario', 'detalles.producto.unidadMedida', 'detalles.presentacion']);

        $datos = ['Fecha' => $cotizacion->fecha->format('d/m/Y')];
        if ($cotizacion->fecha_validez) {
            $datos['Válida hasta'] = $cotizacion->fecha_validez->format('d/m/Y');
        }
        if ($cotizacion->usuario) {
            $datos['Asesor'] = $cotizacion->usuario->name;
        }

        return $this->render([
            'titulo' => 'Cotización',
            'numero' => $cotizacion->numero_cotizacion,
            'empresa' => $cotizacion->empresa,
            'sucursal' => $cotizacion->sucursal,
            'cliente' => $cotizacion->cliente,
            'datosDocumento' => $datos,
            'lineas' => $cotizacion->detalles->map(fn ($d) => [
                'codigo' => $d->producto?->codigo ?? '',
                'descripcion' => $d->descripcion,
                'unidad' => $d->unidadTexto(),
                'cantidad' => (float) $d->cantidad,
                'precio_unitario' => (float) $d->precio_unitario,
                'descuento' => (float) $d->descuento,
                'impuesto_porcentaje' => (float) $d->impuesto_porcentaje,
                'total' => (float) $d->total,
            ])->all(),
            'subtotal' => (float) $cotizacion->subtotal,
            'descuento' => (float) $cotizacion->descuento,
            'impuesto' => (float) $cotizacion->impuesto,
            'total' => (float) $cotizacion->total,
            'observaciones' => $cotizacion->observaciones,
            'nota' => 'Esta cotización no constituye factura ni reserva de mercancía. Precios sujetos a disponibilidad de inventario'
                .($cotizacion->fecha_validez ? ' y válidos hasta el '.$cotizacion->fecha_validez->format('d/m/Y').'.' : '.'),
        ]);
    }

    public function venta(Venta $venta): DomPdf
    {
        $venta->loadMissing(['empresa', 'sucursal', 'cliente', 'usuario', 'detalles.producto.unidadMedida', 'detalles.presentacion']);

        $datos = [
            'Fecha' => $venta->fecha->format('d/m/Y H:i'),
            'Forma de pago' => $venta->tipo_pago->label(),
            'Medio de pago' => $venta->metodo_pago,
        ];
        if ($venta->isAnulada()) {
            $datos['Estado'] = 'ANULADA';
        }

        return $this->render([
            'titulo' => $venta->tipo_comprobante->label(),
            'numero' => $venta->numero_venta,
            'empresa' => $venta->empresa,
            'sucursal' => $venta->sucursal,
            'cliente' => $venta->cliente,
            'datosDocumento' => $datos,
            'lineas' => $venta->detalles->map(fn ($d) => [
                'codigo' => $d->producto?->codigo ?? '',
                'descripcion' => $d->producto?->nombre ?? 'Artículo',
                'unidad' => $d->unidadTexto(),
                'cantidad' => (float) $d->cantidad,
                'precio_unitario' => (float) $d->precio_unitario,
                'descuento' => (float) $d->descuento,
                'impuesto_porcentaje' => (float) $d->impuesto_porcentaje,
                'total' => (float) $d->total,
            ])->all(),
            'subtotal' => (float) $venta->subtotal,
            'descuento' => (float) $venta->descuento,
            'impuesto' => (float) $venta->impuesto,
            'total' => (float) $venta->total,
            'observaciones' => $venta->observaciones,
            'nota' => null,
        ]);
    }

    public function nombreArchivo(Cotizacion|Venta $documento): string
    {
        return ($documento instanceof Cotizacion ? $documento->numero_cotizacion : $documento->numero_venta).'.pdf';
    }

    protected function render(array $datos): DomPdf
    {
        return Pdf::loadView('pdf.documento', $datos)->setPaper('letter');
    }
}
