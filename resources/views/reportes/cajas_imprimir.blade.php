<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Cajas</title>
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
    <h2>Reporte de Turnos de Caja</h2>
    <p>Periodo: {{ $fechaDesde }} al {{ $fechaHasta }}</p>
    <table>
        <thead>
            <tr><th>Caja</th><th>Cajero</th><th>Apertura</th><th>Cierre</th><th class="text-right">Esperado</th><th class="text-right">Contado</th><th class="text-right">Diferencia</th></tr>
        </thead>
        <tbody>
            @foreach($sesiones as $s)
            <tr>
                <td class="bold">{{ $s->caja?->nombre }}</td>
                <td>{{ $s->cajero?->name }}</td>
                <td>{{ $s->fecha_apertura->format('d/m/Y H:i') }}</td>
                <td>{{ $s->fecha_cierre ? $s->fecha_cierre->format('d/m/Y H:i') : 'ABIERTA' }}</td>
                <td class="text-right">${{ number_format($s->monto_cierre_esperado ?? 0, 2) }}</td>
                <td class="text-right bold">${{ number_format($s->monto_cierre_contado ?? 0, 2) }}</td>
                <td class="text-right bold">${{ number_format($s->diferencia ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
