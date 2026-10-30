<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Cuenta – {{ $cliente->razon_social }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

        body { font-family: var(--font-sans); font-size: 11px; color: var(--color-text-primary); background: var(--color-surface); margin: 0; padding: 0; }

        @page {
            size: letter portrait;
            margin: 12mm 14mm;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .page-wrapper { box-shadow: none !important; border: none !important; margin: 0 !important; padding: 0 !important; max-width: 100% !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
        }

        @media screen {
            body { background: var(--color-surface-muted); padding: 24px 0; }
            .page-wrapper { max-width: 900px; margin: 0 auto; background: white; box-shadow: 0 4px 24px rgba(0,0,0,0.10); border-radius: 8px; padding: 28px 32px; }
        }

        h1 { font-size: 20px; font-weight: 700; color: var(--color-text-primary); margin: 0; }
        h2 { font-size: 12px; font-weight: 700; color: var(--color-text-primary); margin: 0 0 2px 0; }

        .doc-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid var(--color-text-primary); padding-bottom: 10px; margin-bottom: 14px; }
        .doc-header-right { text-align: right; font-size: 9px; color: var(--color-text-secondary); line-height: 1.6; }

        .client-bar { background: var(--color-surface-muted); border: 1px solid var(--color-border); border-radius: 5px; padding: 10px 14px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; }
        .client-name { font-size: 13px; font-weight: 700; color: var(--color-text-primary); }
        .client-sub { font-size: 9px; color: var(--color-text-secondary); margin-top: 2px; }
        .status-badge { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 99px; font-size: 9px; font-weight: 700; }
        .badge-mora { background: var(--color-danger-bg); color: var(--color-danger-text); border: 1px solid var(--color-danger-border); }
        .badge-ok   { background: var(--color-success-bg); color: var(--color-success-text); border: 1px solid var(--color-success-border); }

        .kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-bottom: 14px; }
        .print-kpi-box { border: 1px solid var(--color-border); border-radius: 5px; padding: 8px 10px; }
        .print-kpi-box.amber { border-color: var(--color-warning-border); background: var(--color-warning-bg); }
        .print-kpi-box.green { border-color: var(--color-success-border); background: var(--color-success-bg); }
        .print-kpi-box.danger  { border-color: var(--color-danger-border); background: var(--color-danger-bg); }
        .kpi-label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-text-secondary); }
        .print-kpi-box.amber .kpi-label { color: var(--color-warning-text); }
        .print-kpi-box.green .kpi-label { color: var(--color-success-text); }
        .print-kpi-box.danger  .kpi-label { color: var(--color-danger-text); }
        .kpi-value { font-size: 14px; font-weight: 700; color: var(--color-text-primary); margin-top: 3px; }
        .print-kpi-box.amber .kpi-value { color: var(--color-warning-text); }
        .print-kpi-box.green .kpi-value { color: var(--color-success-text); }
        .print-kpi-box.danger  .kpi-value { color: var(--color-danger-text); }
        .kpi-sub { font-size: 8px; color: var(--color-text-secondary); margin-top: 1px; }

        .progress-row { display: flex; justify-content: space-between; font-size: 9px; font-weight: 700; color: var(--color-text-secondary); margin-bottom: 3px; }
        .progress-track { height: 6px; background: var(--color-border); border-radius: 4px; overflow: hidden; margin-bottom: 14px; }
        .progress-fill { height: 100%; border-radius: 4px; }

        .section-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 1px solid var(--color-border); padding-bottom: 5px; margin-bottom: 6px; margin-top: 16px; }
        .section-sub { font-size: 8.5px; color: var(--color-text-muted); }
        .section-count { font-size: 9px; font-weight: 700; color: var(--color-text-secondary); }

        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        thead tr { background: var(--color-surface-muted); }
        th { padding: 5px 6px; text-align: left; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: var(--color-text-secondary); border-bottom: 1px solid var(--color-border-strong); white-space: nowrap; }
        td { padding: 5px 6px; border-bottom: 1px solid var(--color-border); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }

        .pill { display: inline-block; padding: 1px 6px; border-radius: 99px; font-size: 7.5px; font-weight: 700; white-space: nowrap; }
        .pill-mora    { background: var(--color-danger-bg); color: var(--color-danger-text); }
        .pill-ok      { background: var(--color-success-bg); color: var(--color-success-text); }
        .pill-pending { background: var(--color-surface-muted); color: var(--color-text-secondary); }
        .pill-applied { background: var(--color-success-bg); color: var(--color-success-text); }
        .pill-anulado { background: var(--color-danger-bg); color: var(--color-danger-text); }
        .pill-medio   { background: var(--color-info-bg); color: var(--color-info-text); }

        .doc-footer { margin-top: 18px; border-top: 1px solid var(--color-border); padding-top: 6px; font-size: 7.5px; color: var(--color-text-muted); display: flex; justify-content: space-between; }
        .empty-row td { text-align: center; padding: 12px; color: var(--color-text-muted); font-style: italic; }
    </style>
</head>
<body>

{{-- Barra de control (solo pantalla) --}}
<div class="no-print" style="max-width:900px;margin:0 auto 16px;padding:0 32px;display:flex;align-items:center;justify-content:space-between;">
    <a href="{{ route('cartera.estado-cuenta', $cliente) }}"
       style="display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:var(--color-text-secondary);text-decoration:none;padding:6px 14px;border:1px solid var(--color-border-strong);border-radius:6px;background:var(--color-surface);">
        ← Volver
    </a>
    <button onclick="window.print()"
            style="display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;color:var(--color-surface);padding:7px 18px;border:none;border-radius:6px;background:var(--color-text-primary);cursor:pointer;">
        Imprimir / Guardar PDF
    </button>
</div>

<div class="page-wrapper">

    {{-- Encabezado --}}
    <div class="doc-header">
        <div>
            <div style="font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-secondary);margin-bottom:2px;">Crédito &amp; Cartera</div>
            <h1>ESTADO DE CUENTA</h1>
        </div>
        <div class="doc-header-right">
            @if($cliente->empresa)
                <div style="font-weight:700;font-size:11px;color:var(--color-text-primary);">{{ $cliente->empresa->nombre_comercial }}</div>
                @if($cliente->empresa->nit)<div>NIT: {{ $cliente->empresa->nit }}</div>@endif
            @endif
            <div style="margin-top:4px;">Fecha de emisión: {{ now()->format('d/m/Y H:i') }}</div>
            <div>Generado por: {{ auth()->user()->name }}</div>
        </div>
    </div>

    {{-- Info cliente --}}
    <div class="client-bar">
        <div style="flex:1;">
            <div class="client-name">
                {{ $cliente->razon_social }}
                &nbsp;
                @if($totalDeuda > 0)
                    <span class="status-badge badge-mora">CON SALDO</span>
                @else
                    <span class="status-badge badge-ok">PAZ Y SALVO</span>
                @endif
            </div>
            <div class="client-sub">
                {{ $cliente->tipo_documento->value }}: {{ $cliente->numero_documento }}
                @if($cliente->nombre_comercial) &nbsp;•&nbsp; Comercial: {{ $cliente->nombre_comercial }} @endif
                @if($cliente->telefono) &nbsp;•&nbsp; Tel: {{ $cliente->telefono }} @endif
                @if($cliente->email) &nbsp;•&nbsp; {{ $cliente->email }} @endif
            </div>
            @if($cliente->direccion)<div class="client-sub">{{ $cliente->direccion }}</div>@endif
        </div>
    </div>

    {{-- KPIs --}}
    <div class="kpi-grid">
        <div class="print-kpi-box">
            <div class="kpi-label">Total Fiado</div>
            <div class="kpi-value">${{ number_format($totalFiado, 2) }}</div>
            <div class="kpi-sub">Ventas a crédito registradas</div>
        </div>
        <div class="print-kpi-box green">
            <div class="kpi-label">Total Abonado</div>
            <div class="kpi-value">${{ number_format($totalAbonado, 2) }}</div>
            <div class="kpi-sub">Pagos y abonos aplicados</div>
        </div>
        <div class="print-kpi-box amber">
            <div class="kpi-label">Lo que Debe</div>
            <div class="kpi-value">${{ number_format($totalDeuda, 2) }}</div>
            <div class="kpi-sub">{{ $cuentasPendientes->count() }} cuenta(s) abierta(s)</div>
        </div>
    </div>

    {{-- Tabla 1 --}}
    <div class="section-header">
        <div>
            <h2>Obligaciones Pendientes de Pago</h2>
            <div class="section-sub">Facturas y pagarés a crédito con saldo por cancelar.</div>
        </div>
        <div class="section-count">{{ $cuentasPendientes->count() }} registro(s)</div>
    </div>
    <table>
        <thead><tr>
            <th style="width:22%">Documento / Concepto</th>
            <th style="width:10%">Emisión</th>
            <th style="width:10%">Acordado</th>
            <th style="width:13%;text-align:right">Total</th>
            <th style="width:13%;text-align:right">Abonado</th>
            <th style="width:13%;text-align:right">Saldo Pendiente</th>
            <th style="width:19%;text-align:center">Antigüedad</th>
        </tr></thead>
        <tbody>
        @forelse($cuentasPendientes as $c)
            <tr>
                <td>
                    <div style="font-weight:700;font-family:var(--font-mono);">{{ $c->numero_documento }}</div>
                    <div style="color:var(--color-text-secondary);font-size:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;">{{ $c->concepto }}</div>
                </td>
                <td style="white-space:nowrap;">{{ $c->fecha_emision->format('d/m/Y') }}</td>
                <td style="white-space:nowrap;">{{ $c->fecha_vencimiento?->format('d/m/Y') ?? 'Sin acuerdo' }}</td>
                <td style="text-align:right;white-space:nowrap;">${{ number_format($c->monto_total, 2) }}</td>
                <td style="text-align:right;font-weight:700;color:var(--color-success-text);white-space:nowrap;">${{ number_format($c->monto_pagado, 2) }}</td>
                <td style="text-align:right;font-weight:700;white-space:nowrap;">${{ number_format($c->saldo_pendiente, 2) }}</td>
                <td style="text-align:center;">
                    @if($c->estaVencida())
                        <span class="pill pill-mora">Pasó fecha acordada</span>
                    @else
                        <span class="pill pill-pending">{{ $c->diasAntiguedad() }} días fiado</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="7">¡El cliente se encuentra totalmente a paz y salvo!</td></tr>
        @endforelse
        </tbody>
    </table>

    {{-- Tabla 2 --}}
    <div class="section-header">
        <div>
            <h2>Historial de Pagos &amp; Recaudos</h2>
            <div class="section-sub">Últimos abonos registrados en caja aplicados a la cuenta del cliente.</div>
        </div>
        <div class="section-count">{{ $historialPagos->count() }} pago(s)</div>
    </div>
    <table>
        <thead><tr>
            <th style="width:12%">N° Recibo</th>
            <th style="width:10%">Fecha</th>
            <th style="width:13%">Obligación</th>
            <th style="width:20%">Medio de Pago</th>
            <th style="width:15%;text-align:right">Monto Abonado</th>
            <th style="width:16%;text-align:right">Saldo Resultante</th>
            <th style="width:14%;text-align:center">Estado</th>
        </tr></thead>
        <tbody>
        @forelse($historialPagos as $p)
            <tr style="{{ $p->isAnulado() ? 'opacity:0.5;' : '' }}">
                <td style="font-weight:700;font-family:var(--font-mono);white-space:nowrap;">{{ $p->numero_recibo }}</td>
                <td style="white-space:nowrap;">{{ $p->fecha_pago->format('d/m/Y') }}</td>
                <td style="font-family:var(--font-mono);white-space:nowrap;">{{ $p->cuentaPorCobrar->numero_documento }}</td>
                <td><span class="pill pill-medio">{{ $p->metodo_pago->label() }}</span></td>
                <td style="text-align:right;font-weight:700;color:var(--color-success-text);white-space:nowrap;">${{ number_format($p->monto, 2) }}</td>
                <td style="text-align:right;font-weight:700;white-space:nowrap;">${{ number_format($p->saldo_posterior, 2) }}</td>
                <td style="text-align:center;">
                    <span class="pill {{ $p->isAnulado() ? 'pill-anulado' : 'pill-applied' }}">{{ $p->estado->label() }}</span>
                </td>
            </tr>
        @empty
            <tr class="empty-row"><td colspan="7">No se registran pagos en el historial de este cliente.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="doc-footer">
        <span>Estado de Cuenta generado por Sys-POS</span>
        <span>{{ now()->format('d/m/Y H:i') }} — {{ auth()->user()->name }}</span>
    </div>

</div>

<script>
    @if(request()->boolean('print'))
        window.addEventListener('load', function() { window.print(); });
    @endif
</script>

</body>
</html>
