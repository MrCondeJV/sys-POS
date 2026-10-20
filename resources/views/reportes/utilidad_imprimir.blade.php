<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Utilidad</title>
    @vite(['resources/css/app.css'])
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: rgb(15, 23, 42); color: white; padding: 6px; text-align: left; font-size: 9px; }
        td { border-bottom: 1px solid rgb(226, 232, 240); padding: 6px; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 10px;">
        <button onclick="window.print()" class="btn-primary">Imprimir</button>
    </div>
    <h2>Reporte de Rentabilidad y Margen Bruto</h2>
    <p>Periodo: {{ $fechaDesde }} al {{ $fechaHasta }} | Ingresos: ${{ number_format($totalIngreso, 2) }} | Costo: ${{ number_format($totalCosto, 2) }} | <strong>Utilidad: ${{ number_format($totalUtilidad, 2) }} ({{ $porcentajeMargen }}%)</strong></p>
    <table>
        <thead>
            <tr><th>Producto</th><th>Unidades</th><th class="text-right">Ingreso</th><th class="text-right">Costo</th><th class="text-right">Utilidad</th></tr>
        </thead>
        <tbody>
            @foreach($items as $it)
            <tr>
                <td class="bold">{{ $it->producto_nombre }}</td>
                <td>{{ number_format($it->unidades_vendidas, 0) }}</td>
                <td class="text-right">${{ number_format($it->ingreso_neto, 2) }}</td>
                <td class="text-right">${{ number_format($it->costo_total, 2) }}</td>
                <td class="text-right bold text-success-text">${{ number_format($it->utilidad_bruta, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
