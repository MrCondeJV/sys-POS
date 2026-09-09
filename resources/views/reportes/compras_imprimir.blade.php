<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Compras</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #0f172a; color: white; padding: 6px; text-align: left; font-size: 9px; }
        td { border-bottom: 1px solid #e2e8f0; padding: 6px; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 10px;">
        <button onclick="window.print()" style="padding: 6px 12px; background: #4f46e5; color: white; border: none; border-radius: 4px;">Imprimir</button>
    </div>
    <h2>Reporte de Compras</h2>
    <p>Periodo: {{ $fechaDesde }} al {{ $fechaHasta }} | Total Comprado: <strong>${{ number_format($totalComprado, 2) }}</strong></p>
    <table>
        <thead>
            <tr><th>Factura</th><th>Fecha</th><th>Proveedor</th><th>Sucursal</th><th class="text-right">Total</th></tr>
        </thead>
        <tbody>
            @foreach($compras as $c)
            <tr>
                <td class="bold">{{ $c->numero_factura }}</td>
                <td>{{ $c->fecha_emision->format('d/m/Y') }}</td>
                <td>{{ $c->proveedor?->razon_social }}</td>
                <td>{{ $c->sucursal?->nombre }}</td>
                <td class="text-right bold">${{ number_format($c->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
