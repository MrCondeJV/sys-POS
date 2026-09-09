<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket 58mm - {{ $documento->numero_completo ?? ($venta->numero_venta ?? 'COMPROBANTE') }}</title>
    <style>
        @page {
            margin: 0;
            size: 58mm auto;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            line-height: 1.2;
            color: #000;
            background: #fff;
            width: 48mm;
            margin: 0 auto;
            padding: 5px 2px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .border-t { border-top: 1px dashed #000; }
        .border-b { border-bottom: 1px dashed #000; }
        .py-1 { padding-top: 3px; padding-bottom: 3px; }
        .my-1 { margin-top: 3px; margin-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 2px 0; font-size: 9.5px; }
        .no-print { display: block; margin-bottom: 10px; text-align: center; }
        @media print {
            .no-print { display: none !important; }
            body { width: 100%; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 5px 10px; font-size: 11px; cursor: pointer;">Imprimir 58mm</button>
    </div>

    <div class="text-center">
        <div class="font-bold" style="font-size: 12px;">{{ $empresa->nombre_comercial ?? 'POS COMERCIAL' }}</div>
        <div>{{ $empresa->razon_social ?? '' }}</div>
        <div>NIT: {{ $empresa->nit ?? '900.000.000-1' }}</div>
        <div>{{ $sucursal->nombre ?? 'Principal' }}</div>
        <div>{{ $sucursal->direccion ?? '' }}</div>
        <div>Tel: {{ $sucursal->telefono ?? '' }}</div>
    </div>

    <div class="border-t border-b py-1 my-1">
        <div><strong>{{ $documento->tipo->label() ?? 'TICKET POS' }}:</strong> {{ $documento->numero_completo ?? $venta->numero_venta }}</div>
        @if($documento && $venta)
            <div>Venta: {{ $venta->numero_venta }}</div>
        @endif
        <div>Fecha: {{ ($documento->fecha_emision ?? $venta->created_at)->format('d/m/Y H:i') }}</div>
        @if($venta && $venta->usuario)
            <div>Cajero: {{ $venta->usuario->name }}</div>
        @endif
        <div>Cliente: {{ $cliente->razon_social ?? 'Consumidor Final' }}</div>
        @if($cliente && $cliente->numero_documento)
            <div>NIT/CC: {{ $cliente->numero_documento }}</div>
        @endif
    </div>

    <table>
        <thead>
            <tr class="border-b">
                <th style="text-align: left;">Cant/Desc</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @if($venta && $venta->detalles)
                @foreach($venta->detalles as $det)
                    <tr>
                        <td colspan="2" class="font-bold">{{ $det->producto->nombre ?? 'Artículo' }}</td>
                    </tr>
                    <tr>
                        <td>{{ number_format($det->cantidad, 2) }} x ${{ number_format($det->precio_unitario, 2) }}</td>
                        <td class="text-right font-bold">${{ number_format($det->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="border-t my-1 pt-1">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">${{ number_format($documento->subtotal ?? $venta->subtotal, 2) }}</td>
            </tr>
            @if(($documento->descuento_total ?? $venta->descuento) > 0)
                <tr>
                    <td>Descuento:</td>
                    <td class="text-right">-${{ number_format($documento->descuento_total ?? $venta->descuento, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td>IVA/Impuestos:</td>
                <td class="text-right">${{ number_format($documento->impuesto_total ?? ($venta->impuestos ?? 0), 2) }}</td>
            </tr>
            <tr class="font-bold" style="font-size: 11px;">
                <td>TOTAL:</td>
                <td class="text-right">${{ number_format($documento->total ?? $venta->total, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($venta && $venta->pagos && $venta->pagos->count() > 0)
        <div class="border-t py-1">
            <div class="font-bold">Forma de Pago:</div>
            @foreach($venta->pagos as $p)
                <div>{{ $p->metodo_pago }}: ${{ number_format($p->monto, 2) }}</div>
            @endforeach
            @if($venta->cambio > 0)
                <div>Cambio: ${{ number_format($venta->cambio, 2) }}</div>
            @endif
        </div>
    @endif

    <div class="text-center border-t pt-1 my-1">
        <div>¡Gracias por su compra!</div>
        <div style="font-size: 8px;">Sistema POS Comercial</div>
    </div>
</body>
</html>
