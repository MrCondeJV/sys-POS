<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Arqueo de Caja - {{ $caja->nombre ?? 'Caja' }}</title>
    <style>
        @page { margin: 0; size: 80mm auto; }
        body {
            font-family: monospace;
            font-size: 11px;
            color: #000;
            background: #fff;
            width: 72mm;
            margin: 0 auto;
            padding: 8px 4px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .border-t { border-top: 1px dashed #000; }
        .border-b { border-bottom: 1px dashed #000; }
        .my-2 { margin-top: 5px; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 3px 0; }
        .no-print { margin-bottom: 10px; text-align: center; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 5px 12px; font-weight: bold;">Imprimir Arqueo</button>
    </div>

    <div class="text-center">
        <div class="font-bold" style="font-size: 14px;">{{ $empresa->nombre_comercial ?? 'POS' }}</div>
        <div>{{ $sucursal->nombre ?? '' }}</div>
        <div class="font-bold">COMPROBANTE DE CIERRE DE CAJA</div>
    </div>

    <div class="border-t border-b my-2" style="padding: 4px 0;">
        <div><strong>Caja:</strong> {{ $caja->nombre }} ({{ $caja->codigo }})</div>
        <div><strong>Apertura:</strong> {{ $sesion->fecha_apertura ? $sesion->fecha_apertura->format('d/m/Y H:i') : '' }}</div>
        <div><strong>Cierre:</strong> {{ $sesion->fecha_cierre ? $sesion->fecha_cierre->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</div>
        <div><strong>Cajero:</strong> {{ $sesion->cajero->name ?? 'N/A' }}</div>
    </div>

    <table>
        <tr>
            <td>Fondo Inicial:</td>
            <td class="text-right">${{ number_format($sesion->monto_apertura, 2) }}</td>
        </tr>
        <tr>
            <td>Ventas Efectivo:</td>
            <td class="text-right">${{ number_format($sesion->total_ventas_efectivo ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Otros Ingresos:</td>
            <td class="text-right">${{ number_format($sesion->total_ingresos ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Egresos / Gastos:</td>
            <td class="text-right">-${{ number_format($sesion->total_egresos ?? 0, 2) }}</td>
        </tr>
        <tr class="border-t font-bold">
            <td>Saldo Esperado:</td>
            <td class="text-right">${{ number_format($sesion->saldo_esperado ?? 0, 2) }}</td>
        </tr>
        <tr class="font-bold">
            <td>Efectivo Real:</td>
            <td class="text-right">${{ number_format($sesion->monto_final_real ?? 0, 2) }}</td>
        </tr>
        <tr class="border-t font-bold">
            <td>Diferencia:</td>
            <td class="text-right">${{ number_format($sesion->diferencia ?? 0, 2) }}</td>
        </tr>
    </table>

    <div class="border-t my-2" style="padding-top: 30px; text-align: center;">
        <div>_________________________________</div>
        <div>Firma del Cajero Responsable</div>
    </div>
</body>
</html>
