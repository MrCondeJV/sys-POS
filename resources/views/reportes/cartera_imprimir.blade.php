<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Cartera</title>
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
    <h2>Reporte de Cartera Pendiente</h2>
    <p>Saldo Total Adeudado: <strong>${{ number_format($saldoTotalPendiente, 2) }}</strong></p>
    <table>
        <thead>
            <tr><th>Documento</th><th>Cliente</th><th>Vencimiento</th><th class="text-right">Monto Total</th><th class="text-right">Saldo Pendiente</th></tr>
        </thead>
        <tbody>
            @foreach($cuentas as $c)
            <tr>
                <td class="bold">{{ $c->numero_documento }}</td>
                <td>{{ $c->cliente?->razon_social }}</td>
                <td>{{ $c->fecha_vencimiento->format('d/m/Y') }}</td>
                <td class="text-right">${{ number_format($c->monto_total, 2) }}</td>
                <td class="text-right bold" style="color: #b91c1c;">${{ number_format($c->saldo_pendiente, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
