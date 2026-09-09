<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas - {{ config('app.name') }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; margin: 20px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 15px; }
        .title { font-size: 18px; font-weight: bold; text-transform: uppercase; color: #0f172a; }
        .meta { font-size: 10px; color: #64748b; margin-top: 4px; }
        .summary-box { display: flex; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        .summary-item { text-align: center; }
        .summary-label { font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: bold; }
        .summary-val { font-size: 14px; font-weight: bold; color: #0f172a; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; margin-top: 10px; }
        th { background: #0f172a; color: #ffffff; text-transform: uppercase; font-size: 9px; padding: 6px 8px; text-align: left; }
        td { border-bottom: 1px solid #e2e8f0; padding: 6px 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #4f46e5; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            Imprimir Reporte
        </button>
    </div>

    <div class="header">
        <div class="title">Reporte Detallado de Ventas</div>
        <div class="meta">
            Empresa: {{ auth()->user()->empresa?->nombre_comercial ?? 'POS' }} | Rango: {{ $fechaDesde }} al {{ $fechaHasta }} | Generado: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-label">Total Transacciones</div>
            <div class="summary-val">{{ $cantidadVentas }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total IVA Generado</div>
            <div class="summary-val">${{ number_format($totalImpuestos, 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Neto Facturado</div>
            <div class="summary-val" style="color: #059669;">${{ number_format($totalNeto, 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Comprobante</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Sucursal</th>
                <th>Medio</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventas as $v)
            <tr>
                <td class="bold">{{ $v->numero_venta }}</td>
                <td>{{ $v->fecha->format('d/m/Y H:i') }}</td>
                <td>{{ $v->cliente?->razon_social ?? 'Consumidor Final' }}</td>
                <td>{{ $v->sucursal?->nombre }}</td>
                <td>{{ $v->tipo_pago->value }} ({{ $v->metodo_pago }})</td>
                <td class="text-right bold">${{ number_format($v->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
