<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Valoración de Inventario</title>
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
    <h2>Valoración de Inventario</h2>
    <p>Costo Total en Bodega: <strong>${{ number_format($totalCostoValorizado, 2) }}</strong> | Valor Venta Proyectado: <strong>${{ number_format($totalValorVenta, 2) }}</strong></p>
    <table>
        <thead>
            <tr><th>Producto</th><th>Sucursal</th><th class="text-right">Stock</th><th class="text-right">Costo Unit.</th><th class="text-right">Costo Total</th></tr>
        </thead>
        <tbody>
            @foreach($inventarios as $i)
            <tr>
                <td class="bold">{{ $i->producto->nombre }}</td>
                <td>{{ $i->sucursal?->nombre }}</td>
                <td class="text-right">{{ number_format($i->stock, 2) }}</td>
                <td class="text-right">${{ number_format($i->producto->precio_costo, 2) }}</td>
                <td class="text-right bold">${{ number_format($i->stock * $i->producto->precio_costo, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
