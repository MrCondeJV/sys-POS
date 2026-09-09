<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Caja - Abono #{{ $abono->numero_recibo ?? $abono->id }}</title>
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
        <button onclick="window.print()" style="padding: 5px 12px; font-weight: bold;">Imprimir Recibo</button>
    </div>

    <div class="text-center">
        <div class="font-bold" style="font-size: 14px;">{{ $empresa->nombre_comercial ?? 'POS' }}</div>
        <div>NIT: {{ $empresa->nit ?? '' }}</div>
        <div class="font-bold" style="margin-top: 4px;">RECIBO DE CAJA / ABONO</div>
        <div>Nº {{ $abono->numero_recibo ?? ('ABN-' . str_pad($abono->id, 6, '0', STR_PAD_LEFT)) }}</div>
    </div>

    <div class="border-t border-b my-2" style="padding: 4px 0;">
        <div><strong>Fecha:</strong> {{ $abono->created_at->format('d/m/Y H:i') }}</div>
        <div><strong>Cliente:</strong> {{ $cliente->razon_social ?? 'Cliente' }}</div>
        <div><strong>Doc:</strong> {{ $cliente->numero_documento ?? 'N/A' }}</div>
        <div><strong>Factura Afectada:</strong> {{ $cuenta->concepto ?? '' }}</div>
    </div>

    <table>
        <tr>
            <td>Deuda Anterior:</td>
            <td class="text-right">${{ number_format(($abono->saldo_posterior ?? 0) + $abono->monto, 2) }}</td>
        </tr>
        <tr class="font-bold" style="font-size: 13px;">
            <td>MONTO ABONADO:</td>
            <td class="text-right">${{ number_format($abono->monto, 2) }}</td>
        </tr>
        <tr>
            <td>Medio de Pago:</td>
            <td class="text-right">{{ $abono->metodo_pago ?? 'EFECTIVO' }}</td>
        </tr>
        <tr class="border-t font-bold">
            <td>Saldo Restante:</td>
            <td class="text-right">${{ number_format($abono->saldo_posterior ?? 0, 2) }}</td>
        </tr>
    </table>

    <div class="border-t my-2" style="padding-top: 25px; text-align: center;">
        <div>_________________________________</div>
        <div>Firma Recibido Conforme</div>
    </div>
</body>
</html>
