<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Z - Cierre de Caja {{ $sesion->caja->codigo }} #{{ $sesion->id }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        @page { size: letter portrait; margin: 12mm 14mm; }
    </style>
</head>
<body class="bg-app print:bg-surface text-text-primary text-[11px] py-6 print:py-0 antialiased">

<!-- Barra de Control en Pantalla -->
<div class="print:hidden max-w-[680px] mx-auto mb-4 px-4 flex items-center justify-between">
    <a href="{{ route('cajas.index') }}" class="btn btn-secondary text-xs h-8 px-3">
        &larr; Volver al Módulo de Cajas
    </a>
    <button type="button" onclick="window.print()" class="btn btn-primary text-xs h-8 px-4">
        Imprimir Reporte Z
    </button>
</div>

<!-- Contenedor Principal del Reporte -->
<div class="max-w-[680px] mx-auto bg-surface rounded-lg border border-border shadow-xs p-7 px-8 print:max-w-none print:shadow-none print:border-0 print:p-0">

    <!-- Membrete -->
    <div class="border-b-2 border-brand-900 pb-3 mb-3.5">
        <div class="flex justify-between items-start gap-4">
            <div>
                <span class="text-[9px] font-bold tracking-wider text-text-muted uppercase block">
                    Auditoría de Caja &bull; Sys-POS
                </span>
                <h1 class="text-xl font-bold text-text-primary mt-0.5 leading-none">
                    COMPROBANTE DE CIERRE (REPORTE Z)
                </h1>
                <div class="text-[11px] font-semibold text-text-secondary mt-1 tabular-nums">
                    Turno N° #{{ str_pad($sesion->id, 6, '0', STR_PAD_LEFT) }} &bull; Caja: {{ $sesion->caja->nombre }} ({{ $sesion->caja->codigo }})
                </div>
            </div>

            <div class="text-right text-[10px] text-text-secondary">
                <div class="text-xs font-bold text-text-primary">{{ $sesion->empresa->nombre_comercial ?? $sesion->empresa->nombre }}</div>
                @if($sesion->empresa->nit)
                    <div class="tabular-nums">NIT: {{ $sesion->empresa->nit }}</div>
                @endif
                <div>Sucursal: {{ $sesion->sucursal?->nombre ?? $sesion->caja->sucursal?->nombre ?? 'Sin sucursal' }}</div>
            </div>
        </div>
    </div>

    <!-- Tiempos y Operadores -->
    <div class="grid grid-cols-2 gap-3 bg-surface-muted border border-border rounded-md p-3 px-3.5 mb-4">
        <div>
            <div class="text-[9px] font-bold uppercase text-text-muted">Datos de Apertura</div>
            <div class="text-[11px] font-bold text-text-primary mt-0.5">Cajero: {{ $sesion->cajero->name }}</div>
            <div class="text-[10px] text-text-secondary tabular-nums">Fecha: {{ $sesion->fecha_apertura->format('d/m/Y H:i:s') }}</div>
        </div>

        <div>
            <div class="text-[9px] font-bold uppercase text-text-muted">Datos de Cierre</div>
            <div class="text-[11px] font-bold text-text-primary mt-0.5">Auditor / Cierre: {{ $sesion->cajeroCierre?->name ?? 'Mismo cajero' }}</div>
            <div class="text-[10px] text-text-secondary tabular-nums">Fecha: {{ $sesion->fecha_cierre ? $sesion->fecha_cierre->format('d/m/Y H:i:s') : 'En curso' }}</div>
        </div>
    </div>

    <!-- Balance del Turno -->
    <div class="mb-4">
        <div class="text-[10px] font-bold uppercase tracking-wider text-text-primary border-b border-border pb-1 mb-2">
            Resumen Consolidado del Efectivo
        </div>

        <table class="w-full border-collapse text-[11px] tabular-nums">
            <tbody>
                <tr class="border-b border-border">
                    <td class="py-1.5 text-text-secondary">(+) Fondo Inicial de Caja (Base):</td>
                    <td class="py-1.5 text-right font-semibold text-text-primary">${{ number_format($sesion->monto_apertura, 2) }}</td>
                </tr>
                <tr class="border-b border-border">
                    <td class="py-1.5 text-text-secondary">(+) Total Ingresos / Entradas Registradas:</td>
                    <td class="py-1.5 text-right font-semibold text-success">+${{ number_format($sesion->total_ingresos, 2) }}</td>
                </tr>
                <tr class="border-b border-border">
                    <td class="py-1.5 text-text-secondary">(&minus;) Total Egresos / Retiros Realizados:</td>
                    <td class="py-1.5 text-right font-semibold text-danger">-${{ number_format($sesion->total_egresos, 2) }}</td>
                </tr>
                <tr class="border-t-2 border-border-strong bg-surface-muted">
                    <td class="py-2 px-1.5 font-bold text-text-primary">(=) Saldo Teórico Esperado en Caja:</td>
                    <td class="py-2 px-1.5 text-right text-xs font-bold text-text-primary">
                        ${{ number_format($sesion->monto_cierre_esperado ?? $sesion->calcularSaldoEsperadoEfectivo(), 2) }}
                    </td>
                </tr>
                <tr class="border-t border-border bg-surface">
                    <td class="py-2 px-1.5 font-bold text-text-primary">Efectivo Físico Contado (Declarado):</td>
                    <td class="py-2 px-1.5 text-right text-xs font-bold text-text-primary">
                        ${{ number_format($sesion->monto_cierre_contado ?? 0, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Resultado del Arqueo / Diferencia -->
    @php
        $difClass = $sesion->diferencia == 0
            ? 'bg-success-bg border-success-border text-success-text'
            : ($sesion->diferencia > 0
                ? 'bg-warning-bg border-warning-border text-warning-text'
                : 'bg-danger-bg border-danger-border text-danger-text');
    @endphp
    <div class="rounded-md p-3 px-3.5 mb-4 border {{ $difClass }}">
        <div class="flex justify-between items-center">
            <div>
                <div class="text-[9px] font-bold uppercase tracking-wider">
                    Diferencia de Arqueo
                </div>
                <div class="text-sm font-bold mt-0.5 tabular-nums">
                    @if($sesion->diferencia == 0)
                        Cuadre Exacto ($0.00)
                    @elseif($sesion->diferencia > 0)
                        +${{ number_format($sesion->diferencia, 2) }} (Sobrante en Efectivo)
                    @else
                        -${{ number_format(abs($sesion->diferencia), 2) }} (Faltante en Efectivo)
                    @endif
                </div>
            </div>
            <div class="text-[10px] font-bold">
                Estado: {{ $sesion->estado->label() }}
            </div>
        </div>

        @if($sesion->observaciones_cierre)
            <div class="mt-2 text-[10px] border-t border-current/20 pt-1.5">
                <strong>Observaciones:</strong> {{ $sesion->observaciones_cierre }}
            </div>
        @endif
    </div>

    <!-- Firmas de Auditoría -->
    <div class="grid grid-cols-2 gap-10 mt-12 pt-5">
        <div class="border-t border-border-strong text-center pt-1.5">
            <div class="font-bold text-[10px] text-text-primary">{{ $sesion->cajero->name }}</div>
            <div class="text-[9px] text-text-muted">Firma del Cajero Responsable</div>
        </div>

        <div class="border-t border-border-strong text-center pt-1.5">
            <div class="font-bold text-[10px] text-text-primary">{{ $sesion->cajeroCierre?->name ?? 'Supervisor / Administrador' }}</div>
            <div class="text-[9px] text-text-muted">Firma del Auditor / Supervisor</div>
        </div>
    </div>

    <!-- Pie del Documento -->
    <div class="mt-8 border-t border-border pt-1.5 text-[8.5px] text-text-muted flex justify-between tabular-nums">
        <span>Documento generado por Sys-POS &bull; Gestión de Cajas y Arqueos</span>
        <span>Impreso el: {{ now()->format('d/m/Y H:i:s') }}</span>
    </div>

</div>

</body>
</html>
