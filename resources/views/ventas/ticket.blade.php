<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket {{ $venta->numero_venta }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { font-family: "Courier New", Courier, monospace; font-size: 11px; line-height: 1.25; background: #f8fafc; margin: 0; padding: 0; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .ticket-wrapper { width: 100% !important; max-width: 80mm !important; margin: 0 !important; padding: 0 !important; border: none !important; box-shadow: none !important; }
        }
        @media screen {
            .ticket-wrapper { max-width: 80mm; margin: 24px auto; background: white; padding: 18px 16px; border: 1px solid #cbd5e1; border-radius: 6px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        }
    </style>
</head>
<body>

<div class="no-print" style="max-width:80mm;margin:16px auto 0;display:flex;justify-content:space-between;">
    <a href="{{ route('ventas.show', $venta) }}" style="font-size:11px;color:#475569;text-decoration:none;font-weight:bold;">&larr; Volver</a>
    <button onclick="window.print()" style="font-size:11px;font-weight:bold;background:#0f172a;color:white;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;">🖨 Imprimir</button>
</div>

<div class="ticket-wrapper">
    <!-- Header -->
    <div style="text-align: center; border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px;">
        <div style="font-size: 14px; font-weight: 900; text-transform: uppercase;">{{ $venta->empresa?->nombre_comercial ?? $venta->empresa?->razon_social ?? 'POS Comercial' }}</div>
        @if($venta->empresa?->nit)
            <div>NIT: {{ $venta->empresa->nit }}</div>
        @endif
        <div>Sucursal: {{ $venta->sucursal?->nombre ?? 'Principal' }}</div>
        @if($venta->sucursal?->direccion)
            <div>{{ $venta->sucursal->direccion }}</div>
        @endif
        @if($venta->sucursal?->telefono)
            <div>Tel: {{ $venta->sucursal->telefono }}</div>
        @endif
    </div>

    <!-- Info Venta -->
    <div style="border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 6px; font-size: 10px;">
        <div style="display: flex; justify-content: space-between;">
            <span><strong>COMPROBANTE:</strong></span>
            <span><strong>{{ $venta->numero_venta }}</strong></span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Fecha:</span>
            <span>{{ $venta->fecha->format('d/m/Y H:i') }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Cajero/Vendedor:</span>
            <span>{{ $venta->usuario->name }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span>Cliente:</span>
            <span>{{ $venta->cliente ? $venta->cliente->razon_social : 'Consumidor Final' }}</span>
        </div>
        @if($venta->cliente?->numero_documento)
            <div style="display: flex; justify-content: space-between;">
                <span>Doc/NIT:</span>
                <span>{{ $venta->cliente->numero_documento }}</span>
            </div>
        @endif
    </div>

    <!-- Items -->
    <table style="width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 8px;">
        <thead>
            <tr style="border-bottom: 1px solid #000;">
                <th style="text-align: left; padding: 2px 0;">CANT &bull; DESCRIPCIÓN</th>
                <th style="text-align: right; padding: 2px 0;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $item)
                <tr>
                    <td style="padding: 2px 0;">
                        <div>{{ $item->producto->nombre }}</div>
                        <div style="font-size: 9px; color: #555;">
                            {{ number_format($item->cantidad, 2) }} x ${{ number_format($item->precio_unitario, 2) }}
                        </div>
                    </td>
                    <td style="text-align: right; vertical-align: top; padding: 2px 0; font-weight: bold;">
                        ${{ number_format($item->total, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totales -->
    <div style="border-top: 1px dashed #000; padding-top: 6px; font-size: 11px;">
        <div style="display: flex; justify-content: space-between;">
            <span>Subtotal:</span>
            <span>${{ number_format($venta->subtotal, 2) }}</span>
        </div>
        @if($venta->descuento > 0)
            <div style="display: flex; justify-content: space-between; color: #555;">
                <span>Descuento:</span>
                <span>-${{ number_format($venta->descuento, 2) }}</span>
            </div>
        @endif
        @if($venta->impuesto > 0)
            <div style="display: flex; justify-content: space-between;">
                <span>Impuesto:</span>
                <span>+${{ number_format($venta->impuesto, 2) }}</span>
            </div>
        @endif
        <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 900; border-top: 1px solid #000; padding-top: 4px; margin-top: 4px;">
            <span>TOTAL:</span>
            <span>${{ number_format($venta->total, 2) }}</span>
        </div>
    </div>

    <!-- Pagos -->
    <div style="border-top: 1px dashed #000; margin-top: 8px; padding-top: 6px; font-size: 10px;">
        <div style="display: flex; justify-content: space-between;">
            <span>Forma de Pago:</span>
            <span><strong>{{ $venta->tipo_pago->label() }} ({{ $venta->metodo_pago }})</strong></span>
        </div>
        @if($venta->pago_con)
            <div style="display: flex; justify-content: space-between;">
                <span>Recibido:</span>
                <span>${{ number_format($venta->pago_con, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold;">
                <span>Cambio:</span>
                <span>${{ number_format($venta->cambio, 2) }}</span>
            </div>
        @endif
    </div>

    @if($venta->isAnulada())
        <div style="margin-top: 10px; border: 2px solid #000; padding: 4px; text-align: center; font-weight: 900; font-size: 12px;">
            *** VENTA ANULADA ***
        </div>
    @endif

    <!-- Footer -->
    <div style="text-align: center; margin-top: 14px; border-top: 1px dashed #000; padding-top: 8px; font-size: 9.5px;">
        <div>¡GRACIAS POR SU COMPRA!</div>
        <div style="color: #666; margin-top: 2px;">Documento sin validez fiscal formal</div>
    </div>
</div>

</body>
</html>
