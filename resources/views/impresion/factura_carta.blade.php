<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Carta - {{ $documento->numero_completo ?? ($venta->numero_venta ?? 'FACTURA') }}</title>
    <style>
        @page {
            margin: 15mm 15mm;
            size: letter portrait;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
            font-size: 12px;
            color: rgb(30, 41, 59);
            line-height: 1.4;
            background: rgb(255, 255, 255);
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header { display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 2px solid rgb(203, 213, 225); }
        .company-title { font-size: 22px; font-weight: 800; color: rgb(15, 23, 42); text-transform: uppercase; }
        .invoice-badge { background: rgb(239, 246, 255); color: rgb(29, 78, 216); padding: 4px 12px; border-radius: 6px; font-weight: 700; font-size: 12px; display: inline-block; border: 1px solid rgb(191, 219, 254); }
        .invoice-num { font-size: 24px; font-weight: 800; color: rgb(15, 23, 42); font-family: monospace; margin-top: 4px; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding: 15px 0; border-bottom: 1px solid rgb(226, 232, 240); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: rgb(248, 250, 252); color: rgb(71, 85, 105); font-weight: 700; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; padding: 8px 10px; border-bottom: 2px solid rgb(203, 213, 225); }
        td { padding: 8px 10px; border-bottom: 1px solid rgb(241, 245, 249); font-size: 11.5px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: monospace; }
        .totals-section { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 15px; }
        .totals-table { width: 300px; }
        .totals-table td { padding: 4px 8px; }
        .total-row { font-size: 16px; font-weight: 800; color: rgb(15, 23, 42); border-top: 2px solid rgb(15, 23, 42); }
        .no-print { margin-bottom: 20px; text-align: right; }
        .btn-print { background: rgb(29, 78, 216); color: rgb(255, 255, 255); padding: 8px 16px; border-radius: 6px; font-weight: 700; font-size: 13px; border: none; cursor: pointer; }
        @media print {
            .no-print { display: none !important; }
            body { max-width: 100%; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Imprimir / Guardar en PDF</button>
    </div>

    <!-- Header -->
    <div class="header">
        <div>
            <div class="company-title">{{ $empresa->nombre_comercial ?? 'POS COMERCIAL' }}</div>
            <div style="font-weight: 600; color: rgb(71, 85, 105);">{{ $empresa->razon_social ?? '' }}</div>
            <div style="font-size: 11px; color: rgb(100, 116, 139); margin-top: 4px;">
                <div><strong>NIT:</strong> {{ $empresa->nit ?? '900.000.000-1' }}</div>
                <div><strong>Sede:</strong> {{ $sucursal->nombre ?? 'Sede Principal' }} | {{ $sucursal->direccion ?? '' }}</div>
                <div><strong>Teléfono:</strong> {{ $sucursal->telefono ?? $empresa->telefono ?? 'N/A' }}</div>
            </div>
        </div>

        <div style="text-align: right;">
            <span class="invoice-badge">{{ $documento->tipo->label() ?? 'FACTURA DE VENTA' }}</span>
            <div class="invoice-num">{{ $documento->numero_completo ?? $venta->numero_venta }}</div>
            <div style="font-size: 11px; color: rgb(100, 116, 139); margin-top: 4px;">
                <div><strong>Fecha Emisión:</strong> {{ ($documento->fecha_emision ?? $venta->created_at)->format('d/m/Y H:i') }}</div>
                @if($documento && $documento->usuario)
                    <div><strong>Emitido por:</strong> {{ $documento->usuario->name }}</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Info Cliente & Condiciones -->
    <div class="grid-2">
        <div>
            <div style="font-size: 10px; font-weight: 800; color: rgb(148, 163, 184); text-transform: uppercase;">Adquiriente / Cliente</div>
            <div style="font-size: 14px; font-weight: 800; color: rgb(15, 23, 42); margin-top: 2px;">{{ $cliente->razon_social ?? 'Consumidor Final' }}</div>
            <div style="font-size: 11px; color: rgb(71, 85, 105); margin-top: 2px;">
                <div><strong>NIT / CC:</strong> {{ $cliente->numero_documento ?? '222222222222' }}</div>
                @if($cliente && $cliente->direccion)
                    <div><strong>Dirección:</strong> {{ $cliente->direccion }}</div>
                @endif
                @if($cliente && $cliente->telefono)
                    <div><strong>Teléfono:</strong> {{ $cliente->telefono }}</div>
                @endif
            </div>
        </div>

        <div style="text-align: right;">
            <div style="font-size: 10px; font-weight: 800; color: rgb(148, 163, 184); text-transform: uppercase;">Detalles de la Operación</div>
            <div style="font-size: 11px; color: rgb(71, 85, 105); margin-top: 4px;">
                @if($venta)
                    <div><strong>Forma de Pago:</strong> {{ $venta->tipo_pago->value ?? 'CONTADO' }}</div>
                    <div><strong>Medio de Pago:</strong> {{ $venta->metodo_pago ?? 'EFECTIVO' }}</div>
                    <div><strong>Comprobante POS:</strong> {{ $venta->numero_venta }}</div>
                @endif
                <div><strong>Estado:</strong> {{ $documento->estado->label() ?? 'EMITIDO' }}</div>
            </div>
        </div>
    </div>

    <!-- Tabla de Artículos -->
    <table>
        <thead>
            <tr>
                <th style="text-align: left; width: 12%;">Código</th>
                <th style="text-align: left; width: 45%;">Descripción del Producto / Servicio</th>
                <th class="text-center" style="width: 10%;">Cant.</th>
                <th class="text-right" style="width: 15%;">Precio Unit.</th>
                <th class="text-center" style="width: 8%;">IVA</th>
                <th class="text-right" style="width: 15%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @if($venta && $venta->detalles)
                @foreach($venta->detalles as $det)
                    <tr>
                        <td class="font-mono" style="color: rgb(100, 116, 139);">{{ $det->producto->codigo ?? '' }}</td>
                        <td style="font-weight: 600; color: rgb(30, 41, 59);">{{ $det->producto->nombre ?? 'Artículo' }}</td>
                        <td class="text-center font-mono">{{ number_format($det->cantidad, 2) }}</td>
                        <td class="text-right font-mono">${{ number_format($det->precio_unitario, 2) }}</td>
                        <td class="text-center font-mono">{{ number_format($det->impuesto_porcentaje ?? 0, 0) }}%</td>
                        <td class="text-right font-mono" style="font-weight: 700;">${{ number_format($det->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <!-- Totales y Resumen -->
    <div class="totals-section">
        <div style="max-width: 420px; font-size: 11px; color: rgb(100, 116, 139);">
            <div style="font-weight: 700; color: rgb(51, 65, 85); margin-bottom: 2px;">Observaciones:</div>
            <div>{{ $documento->observaciones ?? ($venta->observaciones ?? 'Sin observaciones adicionales.') }}</div>

            <div style="margin-top: 15px; padding: 10px; background: rgb(248, 250, 252); border-radius: 6px; border: 1px solid rgb(226, 232, 240);">
                <div style="font-weight: 700; color: rgb(30, 41, 59);">Normatividad Comercial:</div>
                <div>Esta factura o comprobante de venta se expide de acuerdo con las disposiciones vigentes del Código de Comercio colombiano y el régimen tributario nacional.</div>
            </div>
        </div>

        <div>
            <table class="totals-table">
                <tr>
                    <td style="color: rgb(100, 116, 139);">Subtotal Gravable:</td>
                    <td class="text-right font-mono">${{ number_format($documento->subtotal ?? $venta->subtotal, 2) }}</td>
                </tr>
                @if(($documento->descuento_total ?? $venta->descuento) > 0)
                    <tr>
                        <td style="color: rgb(5, 150, 105);">Descuentos:</td>
                        <td class="text-right font-mono" style="color: rgb(5, 150, 105);">-${{ number_format($documento->descuento_total ?? $venta->descuento, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td style="color: rgb(100, 116, 139);">IVA / Impuestos:</td>
                    <td class="text-right font-mono">${{ number_format($documento->impuesto_total ?? ($venta->impuestos ?? 0), 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL A PAGAR:</td>
                    <td class="text-right font-mono" style="color: rgb(15, 23, 42);">${{ number_format($documento->total ?? $venta->total, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
