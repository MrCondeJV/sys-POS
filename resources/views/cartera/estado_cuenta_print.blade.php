<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Cuenta – {{ $cliente->razon_social }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }

        body { font-family: "Segoe UI", Arial, sans-serif; font-size: 11px; color: #1e293b; background: #fff; margin: 0; padding: 0; }

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
            body { background: #f1f5f9; padding: 24px 0; }
            .page-wrapper { max-width: 900px; margin: 0 auto; background: white; box-shadow: 0 4px 24px rgba(0,0,0,0.10); border-radius: 8px; padding: 28px 32px; }
        }

        h1 { font-size: 20px; font-weight: 900; color: #0f172a; margin: 0; }
        h2 { font-size: 12px; font-weight: 700; color: #1e293b; margin: 0 0 2px 0; }

        .doc-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #1e293b; padding-bottom: 10px; margin-bottom: 14px; }
        .doc-header-right { text-align: right; font-size: 9px; color: #475569; line-height: 1.6; }

        .client-bar { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 5px; padding: 10px 14px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; }
        .client-name { font-size: 13px; font-weight: 900; color: #0f172a; }
        .client-sub { font-size: 9px; color: #64748b; margin-top: 2px; }
        .status-badge { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 99px; font-size: 9px; font-weight: 700; }
        .badge-mora { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .badge-ok   { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }

        .kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 14px; }
        .kpi-card { border: 1px solid #e2e8f0; border-radius: 5px; padding: 8px 10px; }
        .kpi-card.amber { border-color: #fcd34d; background: #fffbeb; }
        .kpi-card.green { border-color: #6ee7b7; background: #f0fdf4; }
        .kpi-card.rose  { border-color: #fca5a5; background: #fff1f2; }
        .kpi-label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
        .kpi-card.amber .kpi-label { color: #92400e; }
        .kpi-card.green .kpi-label { color: #065f46; }
        .kpi-card.rose  .kpi-label { color: #9f1239; }
        .kpi-value { font-size: 14px; font-weight: 900; color: #0f172a; margin-top: 3px; }
        .kpi-card.amber .kpi-value { color: #92400e; }
        .kpi-card.green .kpi-value { color: #065f46; }
        .kpi-card.rose  .kpi-value { color: #9f1239; }
        .kpi-sub { font-size: 8px; color: #64748b; margin-top: 1px; }

        .progress-row { display: flex; justify-content: space-between; font-size: 9px; font-weight: 700; color: #475569; margin-bottom: 3px; }
        .progress-track { height: 6px; background: #e2e8f0; border-radius: 4px; overflow: hidden; margin-bottom: 14px; }
        .progress-fill { height: 100%; border-radius: 4px; }

        .section-header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin-bottom: 6px; margin-top: 16px; }
        .section-sub { font-size: 8.5px; color: #94a3b8; }
        .section-count { font-size: 9px; font-weight: 700; color: #64748b; }

        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        thead tr { background: #f1f5f9; }
        th { padding: 5px 6px; text-align: left; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #475569; border-bottom: 1px solid #cbd5e1; white-space: nowrap; }
        td { padding: 5px 6px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }

        .pill { display: inline-block; padding: 1px 6px; border-radius: 99px; font-size: 7.5px; font-weight: 700; white-space: nowrap; }
        .pill-mora    { background: #fee2e2; color: #991b1b; }
        .pill-ok      { background: #dcfce7; color: #166534; }
        .pill-pending { background: #f1f5f9; color: #475569; }
        .pill-applied { background: #dcfce7; color: #166534; }
        .pill-anulado { background: #fee2e2; color: #991b1b; }
        .pill-medio   { background: #e0e7ff; color: #3730a3; }

        .doc-footer { margin-top: 18px; border-top: 1px solid #e2e8f0; padding-top: 6px; font-size: 7.5px; color: #94a3b8; display: flex; justify-content: space-between; }
        .empty-row td { text-align: center; padding: 12px; color: #94a3b8; font-style: italic; }
    </style>
</head>
<body>

{{-- Barra de control (solo pantalla) --}}
<div class="no-print" style="max-width:900px;margin:0 auto 16px;padding:0 32px;display:flex;align-items:center;justify-content:space-between;">
    <a href="{{ route('cartera.estado-cuenta', $cliente) }}"
       style="display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:#475569;text-decoration:none;padding:6px 14px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;">
        ← Volver
    </a>
    <button onclick="window.print()"
            style="display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;color:#fff;padding:7px 18px;border:none;border-radius:6px;background:#1e293b;cursor:pointer;">
        🖨 Imprimir / Guardar PDF
    </button>
</div>

<div class="page-wrapper">

    {{-- Encabezado --}}
    <div class="doc-header">
        <div>
            <div style="font-size:8px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#64748b;margin-bottom:2px;">Crédito &amp; Cartera</div>
            <h1>ESTADO DE CUENTA</h1>
        </div>
        <div class="doc-header-right">
            @if($cliente->empresa)
                <div style="font-weight:700;font-size:11px;color:#0f172a;">{{ $cliente->empresa->nombre }}</div>
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
                @if($deudaVencida > 0)
                    <span class="status-badge badge-mora">⚠ EN MORA</span>
                @else
                    <span class="status-badge badge-ok">✓ AL DÍA</span>
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
        <div style="text-align:right;white-space:nowrap;">
            <div class="kpi-label">Plazo crédito</div>
            <div style="font-size:13px;font-weight:900;color:#0f172a;">{{ $cliente->plazo_dias }} días</div>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label">Cupo de Crédito</div>
            <div class="kpi-value">${{ number_format($cupoTotal, 2) }}</div>
            <div class="kpi-sub">Plazo: {{ $cliente->plazo_dias }} días</div>
        </div>
        <div class="kpi-card amber">
            <div class="kpi-label">Deuda Total Pendiente</div>
            <div class="kpi-value">${{ number_format($totalDeuda, 2) }}</div>
            <div class="kpi-sub">{{ $cuentasPendientes->count() }} factura(s) activas</div>
        </div>
        <div class="kpi-card green">
            <div class="kpi-label">Cupo Disponible</div>
            <div class="kpi-value">${{ number_format($cupoDisponible, 2) }}</div>
            <div class="kpi-sub">{{ 100 - $porcentajeUtilizado }}% libre</div>
        </div>
        <div class="kpi-card {{ $deudaVencida > 0 ? 'rose' : '' }}">
            <div class="kpi-label">Saldo Vencido (Mora)</div>
            <div class="kpi-value">${{ number_format($deudaVencida, 2) }}</div>
            <div class="kpi-sub">{{ $deudaVencida > 0 ? 'Exige recaudo prioritario' : 'Sin morosidad' }}</div>
        </div>
    </div>

    {{-- Barra cupo --}}
    <div class="progress-row"><span>Cupo Utilizado</span><span>{{ $porcentajeUtilizado }}%</span></div>
    <div class="progress-track">
        <div class="progress-fill" style="width:{{ $porcentajeUtilizado }}%;background:{{ $porcentajeUtilizado > 90 ? '#ef4444' : ($porcentajeUtilizado > 70 ? '#f59e0b' : '#6366f1') }};"></div>
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
            <th style="width:10%">Vencimiento</th>
            <th style="width:13%;text-align:right">Total</th>
            <th style="width:13%;text-align:right">Abonado</th>
            <th style="width:13%;text-align:right">Saldo Pendiente</th>
            <th style="width:19%;text-align:center">Condición</th>
        </tr></thead>
        <tbody>
        @forelse($cuentasPendientes as $c)
            <tr>
                <td>
                    <div style="font-weight:900;font-family:Consolas,monospace;">{{ $c->numero_documento }}</div>
                    <div style="color:#64748b;font-size:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;">{{ $c->concepto }}</div>
                </td>
                <td style="white-space:nowrap;">{{ $c->fecha_emision->format('d/m/Y') }}</td>
                <td style="white-space:nowrap;">{{ $c->fecha_vencimiento->format('d/m/Y') }}</td>
                <td style="text-align:right;white-space:nowrap;">${{ number_format($c->monto_total, 2) }}</td>
                <td style="text-align:right;font-weight:700;color:#059669;white-space:nowrap;">${{ number_format($c->monto_pagado, 2) }}</td>
                <td style="text-align:right;font-weight:900;white-space:nowrap;">${{ number_format($c->saldo_pendiente, 2) }}</td>
                <td style="text-align:center;">
                    @if($c->estaVencida())
                        <span class="pill pill-mora">{{ abs($c->diasDiferenciaVencimiento()) }} días mora</span>
                    @else
                        <span class="pill pill-pending">{{ $c->diasDiferenciaVencimiento() }} días restantes</span>
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
                <td style="font-weight:900;font-family:Consolas,monospace;white-space:nowrap;">{{ $p->numero_recibo }}</td>
                <td style="white-space:nowrap;">{{ $p->fecha_pago->format('d/m/Y') }}</td>
                <td style="font-family:Consolas,monospace;white-space:nowrap;">{{ $p->cuentaPorCobrar->numero_documento }}</td>
                <td><span class="pill pill-medio">{{ $p->metodo_pago->label() }}</span></td>
                <td style="text-align:right;font-weight:900;color:#059669;white-space:nowrap;">${{ number_format($p->monto, 2) }}</td>
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
