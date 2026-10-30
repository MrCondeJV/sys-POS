{{-- Plantilla PDF tamaño carta compartida por cotizaciones y ventas. Solo tablas: dompdf no soporta flex/grid. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }} {{ $numero }}</title>
    <style>
        @page { margin: 14mm 14mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; line-height: 1.35; }
        table { width: 100%; border-collapse: collapse; }
        .muted { color: #64748b; }
        .label { font-size: 8px; font-weight: bold; color: #94a3b8; text-transform: uppercase; letter-spacing: .4px; }
        .empresa { font-size: 16px; font-weight: bold; color: #0f172a; text-transform: uppercase; }
        .badge { display: inline-block; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; padding: 3px 9px; border-radius: 4px; font-weight: bold; font-size: 10px; text-transform: uppercase; }
        .numero { font-size: 18px; font-weight: bold; color: #0f172a; font-family: DejaVu Sans Mono, monospace; margin-top: 4px; }
        .seccion { border-top: 1px solid #e2e8f0; margin-top: 12px; padding-top: 10px; }
        .items th { background: #f8fafc; color: #475569; font-size: 8px; text-transform: uppercase; padding: 6px; border-bottom: 2px solid #cbd5e1; text-align: left; }
        .items td { padding: 6px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        .r { text-align: right; }
        .c { text-align: center; }
        .mono { font-family: DejaVu Sans Mono, monospace; }
        .totales td { padding: 3px 6px; }
        .total-final td { font-size: 13px; font-weight: bold; color: #0f172a; border-top: 2px solid #0f172a; padding-top: 6px; }
        .nota { margin-top: 14px; padding: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="empresa">{{ $empresa->nombre_comercial ?? 'POS Comercial' }}</div>
                @if($empresa?->razon_social)
                    <div style="font-weight: bold;" class="muted">{{ $empresa->razon_social }}</div>
                @endif
                <div class="muted" style="margin-top: 3px;">
                    @if($empresa?->nit)<div>NIT: {{ $empresa->nit }}{{ $empresa->dv ? '-' . $empresa->dv : '' }}</div>@endif
                    @if($sucursal)<div>Sede: {{ $sucursal->nombre }}{{ $sucursal->direccion ? ' · ' . $sucursal->direccion : '' }}</div>@endif
                    @if($sucursal?->telefono ?? $empresa?->telefono)<div>Tel: {{ $sucursal?->telefono ?? $empresa?->telefono }}</div>@endif
                    @if($empresa?->email)<div>{{ $empresa->email }}</div>@endif
                </div>
            </td>
            <td style="width: 40%; vertical-align: top; text-align: right;">
                <span class="badge">{{ $titulo }}</span>
                <div class="numero">{{ $numero }}</div>
                <div class="muted" style="margin-top: 3px;">
                    @foreach($datosDocumento as $etiqueta => $valor)
                        <div><strong>{{ $etiqueta }}:</strong> {{ $valor }}</div>
                    @endforeach
                </div>
            </td>
        </tr>
    </table>

    <div class="seccion">
        <div class="label">Cliente</div>
        <div style="font-size: 12px; font-weight: bold; color: #0f172a; margin-top: 2px;">{{ $cliente->razon_social ?? 'Consumidor Final' }}</div>
        @if($cliente)
            <div class="muted">
                {{ $cliente->tipo_documento?->value }} {{ $cliente->numero_documento }}
                @if($cliente->telefono) · Tel: {{ $cliente->telefono }} @endif
                @if($cliente->email) · {{ $cliente->email }} @endif
            </div>
            @if($cliente->direccion)<div class="muted">{{ $cliente->direccion }}</div>@endif
        @endif
    </div>

    <table class="items" style="margin-top: 12px;">
        <thead>
            <tr>
                <th style="width: 12%;">Código</th>
                <th style="width: 40%;">Descripción</th>
                <th class="c" style="width: 9%;">Cant.</th>
                <th class="r" style="width: 13%;">Precio Unit.</th>
                <th class="r" style="width: 10%;">Desc.</th>
                <th class="c" style="width: 6%;">IVA</th>
                <th class="r" style="width: 10%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lineas as $linea)
                <tr>
                    <td class="mono muted">{{ $linea['codigo'] }}</td>
                    <td style="font-weight: bold;">{{ $linea['descripcion'] }}</td>
                    <td class="c mono">{{ rtrim(rtrim(number_format($linea['cantidad'], 2, ',', '.'), '0'), ',') }}</td>
                    <td class="r mono">${{ number_format($linea['precio_unitario'], 0, ',', '.') }}</td>
                    <td class="r mono">{{ $linea['descuento'] > 0 ? '$' . number_format($linea['descuento'], 0, ',', '.') : '—' }}</td>
                    <td class="c mono">{{ number_format($linea['impuesto_porcentaje'], 0) }}%</td>
                    <td class="r mono" style="font-weight: bold;">${{ number_format($linea['total'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="margin-top: 12px;">
        <tr>
            <td style="width: 58%; vertical-align: top; padding-right: 12px;">
                <div class="label">Observaciones</div>
                <div class="muted">{{ $observaciones ?: 'Sin observaciones.' }}</div>
                @if($nota)
                    <div class="nota muted">{{ $nota }}</div>
                @endif
            </td>
            <td style="width: 42%; vertical-align: top;">
                <table class="totales">
                    <tr><td class="muted">Subtotal</td><td class="r mono">${{ number_format($subtotal, 0, ',', '.') }}</td></tr>
                    @if($descuento > 0)
                        <tr><td class="muted">Descuentos</td><td class="r mono">-${{ number_format($descuento, 0, ',', '.') }}</td></tr>
                    @endif
                    <tr><td class="muted">IVA</td><td class="r mono">${{ number_format($impuesto, 0, ',', '.') }}</td></tr>
                    <tr class="total-final"><td>TOTAL</td><td class="r mono">${{ number_format($total, 0, ',', '.') }}</td></tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
