<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante de Devolución - {{ $devolucion->numero_devolucion }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: monospace, sans-serif; font-size: 11px; }
        body { width: 80mm; margin: 0 auto; padding: 10px; background: #fff; color: #000; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .line { border-top: 1px dashed #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; margin: 2px 0; }
        @media print {
            body { width: 100%; margin: 0; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 10px; text-align: center;">
        <button onclick="window.print()" style="padding: 6px 12px; background: #000; color: #fff; border: none; border-radius: 4px; cursor: pointer;">
            Imprimir Comprobante
        </button>
    </div>

    <div class="text-center">
        <div class="bold" style="font-size: 13px;">{{ $devolucion->sucursal->empresa->nombre_comercial }}</div>
        <div>NIT: {{ $devolucion->sucursal->empresa->nit }}</div>
        <div>{{ $devolucion->sucursal->nombre }}</div>
        <div>{{ $devolucion->sucursal->direccion }}</div>
    </div>

    <div class="line"></div>
    <div class="text-center bold" style="font-size: 12px;">COMPROBANTE DE DEVOLUCIÓN</div>
    <div class="text-center bold">{{ $devolucion->numero_devolucion }}</div>
    <div class="line"></div>

    <div class="row"><span>Fecha:</span><span>{{ $devolucion->created_at->format('d/m/Y H:i') }}</span></div>
    <div class="row"><span>Venta Original:</span><span>#{{ $devolucion->venta->numero_venta }}</span></div>
    <div class="row"><span>Cliente:</span><span>{{ Str::limit($devolucion->venta->cliente?->razon_social ?? 'Consumidor Final', 18) }}</span></div>
    <div class="row"><span>Reintegro:</span><span>{{ $devolucion->tipo_reintegro->label() }}</span></div>
    <div class="row"><span>Cajero:</span><span>{{ $devolucion->usuario->name }}</span></div>

    <div class="line"></div>
    <div class="row bold">
        <span style="width: 50%;">Desc.</span>
        <span style="width: 20%; text-align: center;">Cant.</span>
        <span style="width: 30%; text-align: right;">Total</span>
    </div>
    <div class="line"></div>

    @foreach($devolucion->detalles as $det)
    <div class="row">
        <span style="width: 50%;">{{ Str::limit($det->producto->nombre, 18) }}</span>
        <span style="width: 20%; text-align: center;">{{ number_format($det->cantidad, 1) }}</span>
        <span style="width: 30%; text-align: right;">${{ number_format($det->total, 0) }}</span>
    </div>
    @endforeach

    <div class="line"></div>
    <div class="row bold" style="font-size: 12px;">
        <span>TOTAL DEVUELTO:</span>
        <span>${{ number_format($devolucion->total, 2) }}</span>
    </div>
    <div class="line"></div>

    @if($devolucion->motivo)
    <div style="font-size: 10px; margin-top: 4px;">
        <strong>Motivo:</strong> {{ $devolucion->motivo }}
    </div>
    <div class="line"></div>
    @endif

    <div class="text-center" style="margin-top: 8px; font-size: 10px;">
        Documento de control interno de inventario y caja.
    </div>
</body>
</html>
