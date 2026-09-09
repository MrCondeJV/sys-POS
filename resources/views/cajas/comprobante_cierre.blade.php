<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Z - Cierre de Caja {{ $sesion->caja->codigo }} #{{ $sesion->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        body { font-family: "Segoe UI", Arial, sans-serif; font-size: 11px; color: #1e293b; background: #fff; margin: 0; padding: 0; }
        @page { size: letter portrait; margin: 12mm 14mm; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .page-wrapper { box-shadow: none !important; border: none !important; margin: 0 !important; padding: 0 !important; max-width: 100% !important; }
        }
        @media screen {
            body { background: #f1f5f9; padding: 24px 0; }
            .page-wrapper { max-width: 680px; margin: 0 auto; background: white; box-shadow: 0 4px 24px rgba(0,0,0,0.08); border-radius: 8px; padding: 28px 32px; }
        }
    </style>
</head>
<body>

<!-- Barra de Control en Pantalla -->
<div class="no-print" style="max-width:680px;margin:0 auto 16px;padding:0 16px;display:flex;align-items:center;justify-content:space-between;">
    <a href="{{ route('cajas.index') }}"
       style="display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:#475569;text-decoration:none;padding:6px 14px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;">
        &larr; Volver al Módulo de Cajas
    </a>
    <button onclick="window.print()"
            style="display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;color:#fff;padding:7px 18px;border:none;border-radius:6px;background:#0f172a;cursor:pointer;">
        🖨 Imprimir Reporte Z
    </button>
</div>

<!-- Contenedor Principal del Reporte -->
<div class="page-wrapper border border-slate-200">

    <!-- Membrete -->
    <div style="border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 14px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <span style="font-size: 9px; font-weight: 700; letter-spacing: 0.05em; color: #64748b; text-transform: uppercase;">
                    Auditoría de Caja &bull; Sys-POS
                </span>
                <h1 style="font-size: 20px; font-weight: 900; color: #0f172a; margin: 2px 0 0 0; line-height: 1;">
                    COMPROBANTE DE CIERRE (REPORTE Z)
                </h1>
                <div style="font-size: 11px; font-weight: 700; color: #475569; margin-top: 4px;">
                    Turno N° #{{ str_pad($sesion->id, 6, '0', STR_PAD_LEFT) }} &bull; Caja: {{ $sesion->caja->nombre }} ({{ $sesion->caja->codigo }})
                </div>
            </div>

            <div style="text-align: right; font-size: 9px; color: #475569;">
                <div style="font-size: 12px; font-weight: 900; color: #0f172a;">{{ $sesion->empresa->nombre }}</div>
                @if($sesion->empresa->nit)
                    <div>NIT: {{ $sesion->empresa->nit }}</div>
                @endif
                <div>Sucursal: {{ $sesion->sucursal->nombre }}</div>
            </div>
        </div>
    </div>

    <!-- Tiempos y Operadores -->
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px;">
        <div>
            <div style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; color: #64748b;">Datos de Apertura</div>
            <div style="font-size: 11px; font-weight: 800; color: #0f172a; margin-top: 2px;">Cajero: {{ $sesion->cajero->name }}</div>
            <div style="font-size: 9.5px; color: #475569;">Fecha: {{ $sesion->fecha_apertura->format('d/m/Y H:i:s') }}</div>
        </div>

        <div>
            <div style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; color: #64748b;">Datos de Cierre</div>
            <div style="font-size: 11px; font-weight: 800; color: #0f172a; margin-top: 2px;">Auditor / Cierre: {{ $sesion->cajeroCierre?->name ?? 'Mismo cajero' }}</div>
            <div style="font-size: 9.5px; color: #475569;">Fecha: {{ $sesion->fecha_cierre ? $sesion->fecha_cierre->format('d/m/Y H:i:s') : 'En curso' }}</div>
        </div>
    </div>

    <!-- Balance del Turno -->
    <div style="margin-bottom: 18px;">
        <div style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 8px;">
            Resumen Consolidado del Efectivo
        </div>

        <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
            <tbody>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 6px 0; color: #475569;">(+) Fondo Inicial de Caja (Base):</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #0f172a;">${{ number_format($sesion->monto_apertura, 2) }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 6px 0; color: #475569;">(+) Total Ingresos / Entradas Registradas:</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #059669;">+${{ number_format($sesion->total_ingresos, 2) }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #f1f5f9;">
                    <td style="padding: 6px 0; color: #475569;">(&minus;) Total Egresos / Retiros Realizados:</td>
                    <td style="padding: 6px 0; text-align: right; font-weight: 700; color: #dc2626;">-${{ number_format($sesion->total_egresos, 2) }}</td>
                </tr>
                <tr style="border-top: 1.5px solid #cbd5e1; background: #f8fafc;">
                    <td style="padding: 8px 6px; font-weight: 800; color: #0f172a;">(=) Saldo Teórico Esperado en Caja:</td>
                    <td style="padding: 8px 6px; text-align: right; font-size: 12px; font-weight: 900; color: #0f172a;">
                        ${{ number_format($sesion->monto_cierre_esperado ?? $sesion->calcularSaldoEsperadoEfectivo(), 2) }}
                    </td>
                </tr>
                <tr style="border-top: 1px solid #e2e8f0; background: #f1f5f9;">
                    <td style="padding: 8px 6px; font-weight: 800; color: #0f172a;">Efectivo Físico Contado (Declarado):</td>
                    <td style="padding: 8px 6px; text-align: right; font-size: 12px; font-weight: 900; color: #0f172a;">
                        ${{ number_format($sesion->monto_cierre_contado ?? 0, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Resultado del Arqueo / Diferencia -->
    <div style="border-radius: 6px; padding: 10px 14px; margin-bottom: 18px; border: 1.5px solid; 
        {{ $sesion->diferencia == 0 ? 'background:#f0fdf4; border-color:#86efac; color:#166534;' : ($sesion->diferencia > 0 ? 'background:#fffbeb; border-color:#fde68a; color:#92400e;' : 'background:#fef2f2; border-color:#fecaca; color:#991b1b;') }}">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-size: 8.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">
                    Diferencia de Arqueo
                </div>
                <div style="font-size: 14px; font-weight: 900; margin-top: 2px;">
                    @if($sesion->diferencia == 0)
                        ✓ Cuadre Exacto ($0.00)
                    @elseif($sesion->diferencia > 0)
                        +${{ number_format($sesion->diferencia, 2) }} (Sobrante en Efectivo)
                    @else
                        -${{ number_format(abs($sesion->diferencia), 2) }} (Faltante en Efectivo)
                    @endif
                </div>
            </div>
            <div style="font-size: 10px; font-weight: 700;">
                Estado: {{ $sesion->estado->label() }}
            </div>
        </div>

        @if($sesion->observaciones_cierre)
            <div style="margin-top: 6px; font-size: 9px; border-top: 1px dashed rgba(0,0,0,0.15); padding-top: 4px;">
                <strong>Observaciones:</strong> {{ $sesion->observaciones_cierre }}
            </div>
        @endif
    </div>

    <!-- Firmas de Auditoría -->
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 40px; margin-top: 50px; padding-top: 20px;">
        <div style="border-top: 1px solid #94a3b8; text-align: center; padding-top: 6px;">
            <div style="font-weight: 800; font-size: 10px; color: #0f172a;">{{ $sesion->cajero->name }}</div>
            <div style="font-size: 8.5px; color: #64748b;">Firma del Cajero Responsable</div>
        </div>

        <div style="border-top: 1px solid #94a3b8; text-align: center; padding-top: 6px;">
            <div style="font-weight: 800; font-size: 10px; color: #0f172a;">{{ $sesion->cajeroCierre?->name ?? 'Supervisor / Administrador' }}</div>
            <div style="font-size: 8.5px; color: #64748b;">Firma del Auditor / Supervisor</div>
        </div>
    </div>

    <!-- Pie del Documento -->
    <div style="margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 6px; font-size: 7.5px; color: #94a3b8; display: flex; justify-content: space-between;">
        <span>Documento generado por Sys-POS &bull; Gestión de Cajas y Arqueos</span>
        <span>Impreso el: {{ now()->format('d/m/Y H:i:s') }}</span>
    </div>

</div>

</body>
</html>
