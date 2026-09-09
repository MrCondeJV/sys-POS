<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket 80mm - {{ $documento->numero_completo ?? ($venta->numero_venta ?? 'COMPROBANTE') }}</title>
    <style>
        @page {
            margin: 0;
            size: 80mm auto;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif, monospace;
            font-size: 12px;
            line-height: 1.3;
            color: #111;
            background: #fff;
            width: 72mm;
            margin: 0 auto;
            padding: 10px 4px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .font-mono { font-family: monospace; }
        .border-t { border-top: 1px dashed #333; }
        .border-b { border-bottom: 1px dashed #333; }
        .border-double { border-top: 2px solid #000; border-bottom: 2px solid #000; }
        .py-1 { padding-top: 4px; padding-bottom: 4px; }
        .my-2 { margin-top: 6px; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 3px 0; font-size: 11px; }
        .no-print { display: block; margin-bottom: 15px; text-align: center; }
        @media print {
            .no-print { display: none !important; }
            body { width: 100%; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 6px 14px; font-weight: bold; background: #0f172a; color: #fff; border: none; border-radius: 6px; cursor: pointer;">
            🖨️ Imprimir Ticket 80mm
        </button>
    </div>

    <!-- Encabezado Empresa -->
    <div class="text-center">
        <div class="font-bold" style="font-size: 15px; letter-spacing: 0.5px;">{{ $empresa->nombre_comercial ?? 'POS COMERCIAL' }}</div>
        <div style="font-size: 11px;">{{ $empresa->razon_social ?? '' }}</div>
        <div class="font-bold" style="font-size: 11px;">NIT: {{ $empresa->nit ?? '900.000.000-1' }}</div>
        <div>Sucursal: {{ $sucursal->nombre ?? 'Sede Principal' }}</div>
        <div>{{ $sucursal->direccion ?? '' }}</div>
        <div>Tel: {{ $sucursal->telefono ?? '' }}</div>
    </div>

    <!-- Info del comprobante -->
    <div class="border-t border-b py-1 my-2" style="font-size: 11.5px;">
        <div class="font-bold text-center" style="font-size: 13px;">
            {{ $documento ? $documento->tipo->label() : 'COMPROBANTE DE VENTA' }}
        </div>
        <div class="font-bold text-center font-mono" style="font-size: 14px;">
            {{ $documento->numero_completo ?? $venta->numero_venta }}
        </div>
        @if($documento && $venta)
            <div class="text-center" style="font-size: 10.5px; color: #555;">(Venta: {{ $venta->numero_venta }})</div>
        @endif
        <div class="border-t my-2 pt-1">
            <div><strong>Fecha:</strong> {{ ($documento->fecha_emision ?? $venta->created_at)->format('d/m/Y H:i:s') }}</div>
            @if($venta && $venta->usuario)
                <div><strong>Cajero:</strong> {{ $venta->usuario->name }}</div>
            @endif
            <div><strong>Cliente:</strong> {{ $cliente->razon_social ?? 'Consumidor Final' }}</div>
            @if($cliente && $cliente->numero_documento)
                <div><strong>Doc/NIT:</strong> {{ $cliente->numero_documento }}</div>
            @endif
            @if($venta)
                <div><strong>Condición:</strong> {{ $venta->tipo_pago->value ?? 'CONTADO' }}</div>
            @endif
        </div>
    </div>

    <!-- Detalles del comprobante -->
    <table>
        <thead>
            <tr class="border-b">
                <th style="text-align: left; width: 45%;">Descripción</th>
                <th style="text-align: center; width: 15%;">Cant</th>
                <th style="text-align: right; width: 20%;">Precio</th>
                <th style="text-align: right; width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody class="font-mono">
            @if($venta && $venta->detalles)
                @foreach($venta->detalles as $det)
                    <tr>
                        <td colspan="4" class="font-bold" style="font-family: sans-serif; padding-top: 4px;">
                            {{ $det->producto->nombre ?? 'Artículo' }}
                        </td>
                    </tr>
                    <tr class="border-b">
                        <td style="font-size: 10px; color: #555;">{{ $det->producto->codigo ?? '' }}</td>
                        <td class="text-center">{{ number_format($det->cantidad, 2) }}</td>
                        <td class="text-right">${{ number_format($det->precio_unitario, 2) }}</td>
                        <td class="text-right font-bold">${{ number_format($det->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <!-- Totales -->
    <div class="my-2 pt-1">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td class="text-right font-mono">${{ number_format($documento->subtotal ?? $venta->subtotal, 2) }}</td>
            </tr>
            @if(($documento->descuento_total ?? $venta->descuento) > 0)
                <tr>
                    <td>Descuento:</td>
                    <td class="text-right font-mono">-${{ number_format($documento->descuento_total ?? $venta->descuento, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td>IVA / Impuestos:</td>
                <td class="text-right font-mono">${{ number_format($documento->impuesto_total ?? ($venta->impuestos ?? 0), 2) }}</td>
            </tr>
            <tr class="border-double font-bold" style="font-size: 14px;">
                <td style="padding: 4px 0;">TOTAL A PAGAR:</td>
                <td class="text-right font-mono" style="padding: 4px 0;">${{ number_format($documento->total ?? $venta->total, 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Pagos recibidos -->
    @if($venta && $venta->pagos && $venta->pagos->count() > 0)
        <div class="border-t py-1 my-2" style="font-size: 11px;">
            <div class="font-bold">Desglose de Pago:</div>
            @foreach($venta->pagos as $p)
                <div style="display: flex; justify-content: space-between;">
                    <span>{{ $p->metodo_pago }}:</span>
                    <span class="font-mono font-bold">${{ number_format($p->monto, 2) }}</span>
                </div>
            @endforeach
            @if($venta->pago_con > 0)
                <div style="display: flex; justify-content: space-between;">
                    <span>Recibido:</span>
                    <span class="font-mono">${{ number_format($venta->pago_con, 2) }}</span>
                </div>
            @endif
            @if($venta->cambio > 0)
                <div style="display: flex; justify-content: space-between; font-weight: bold;">
                    <span>Cambio:</span>
                    <span class="font-mono">${{ number_format($venta->cambio, 2) }}</span>
                </div>
            @endif
        </div>
    @endif

    <!-- Pie del Ticket -->
    <div class="text-center border-t pt-2 my-2" style="font-size: 10px;">
        <div class="font-bold">¡GRACIAS POR SU PREFERENCIA!</div>
        <div>Conserve este comprobante para cualquier reclamo o garantía.</div>
        <div style="margin-top: 4px; color: #666;">Documento emitido por software POS Comercial</div>
    </div>
</body>
</html>
