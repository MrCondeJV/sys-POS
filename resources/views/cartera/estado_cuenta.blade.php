@extends('layouts.app')

@section('title', 'Estado de Cuenta: ' . $cliente->razon_social)

@section('styles')
<style>
    @media print {
        @page {
            size: letter portrait;
            margin: 10mm 12mm;
        }

        body {
            background-color: var(--color-surface) !important;
            color: var(--color-text-primary) !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Ocultar elementos no imprimibles */
        aside, header, nav, .no-print, .print\:hidden, [role="dialog"] {
            display: none !important;
        }

        main {
            padding: 0 !important;
            margin: 0 !important;
            overflow: visible !important;
            max-width: 100% !important;
            width: 100% !important;
        }

        .max-w-\[1680px\], .max-w-6xl {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .space-y-6 > * + * {
            margin-top: 10px !important;
        }

        /* Mostrar encabezado y footer exclusivo de impresión */
        .print-only-header {
            display: block !important;
        }

        .print-footer {
            display: flex !important;
            justify-content: space-between !important;
            margin-top: 12px !important;
            padding-top: 6px !important;
            border-top: 1px solid var(--color-border-strong) !important;
            font-size: 7.5pt !important;
            color: var(--color-text-secondary) !important;
        }

        /* Contenedores limpios sin bordes exagerados ni sombras */
        .rounded-xl, .rounded-lg {
            border-radius: 4px !important;
        }

        .shadow-sm, .shadow {
            box-shadow: none !important;
        }

        .p-6, .sm\:p-8 {
            padding: 10px 12px !important;
        }

        /* Avatar decorativo oculto en papel */
        .client-avatar-box {
            display: none !important;
        }

        /* 4 KPIs siempre en una sola fila compacta */
        .kpi-container {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 8px !important;
        }

        .print-kpi-box {
            padding: 6px 8px !important;
            border-radius: 4px !important;
            border: 1px solid var(--color-border) !important;
            background-color: var(--color-surface-muted) !important;
        }

        .print-kpi-box .text-xl {
            font-size: 13pt !important;
            font-weight: 700 !important;
            margin-top: 2px !important;
        }

        /* Tablas: sin scroll horizontal, ancho 100% fijo */
        .overflow-x-auto {
            overflow: visible !important;
            width: 100% !important;
        }

        table {
            width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
        }

        th {
            padding: 5px 6px !important;
            font-size: 7.5pt !important;
            background-color: var(--color-surface-muted) !important;
            color: var(--color-text-secondary) !important;
            border-bottom: 1.5px solid var(--color-border-strong) !important;
            text-transform: uppercase !important;
            white-space: normal !important;
        }

        td {
            padding: 4px 6px !important;
            font-size: 8pt !important;
            border-bottom: 1px solid var(--color-border) !important;
            line-height: 1.25 !important;
            vertical-align: middle !important;
        }

        tr {
            page-break-inside: avoid !important;
        }

        thead {
            display: table-header-group !important;
        }

        /* Distribución exacta de columnas (100% total) */
        .table-pendientes th:nth-child(1), .table-pendientes td:nth-child(1) { width: 30% !important; }
        .table-pendientes th:nth-child(2), .table-pendientes td:nth-child(2) { width: 11% !important; }
        .table-pendientes th:nth-child(3), .table-pendientes td:nth-child(3) { width: 11% !important; }
        .table-pendientes th:nth-child(4), .table-pendientes td:nth-child(4) { width: 12% !important; text-align: right !important; }
        .table-pendientes th:nth-child(5), .table-pendientes td:nth-child(5) { width: 12% !important; text-align: right !important; }
        .table-pendientes th:nth-child(6), .table-pendientes td:nth-child(6) { width: 12% !important; text-align: right !important; }
        .table-pendientes th:nth-child(7), .table-pendientes td:nth-child(7) { width: 12% !important; text-align: center !important; }

        .table-historial th:nth-child(1), .table-historial td:nth-child(1) { width: 14% !important; }
        .table-historial th:nth-child(2), .table-historial td:nth-child(2) { width: 11% !important; }
        .table-historial th:nth-child(3), .table-historial td:nth-child(3) { width: 15% !important; }
        .table-historial th:nth-child(4), .table-historial td:nth-child(4) { width: 18% !important; }
        .table-historial th:nth-child(5), .table-historial td:nth-child(5) { width: 14% !important; text-align: right !important; }
        .table-historial th:nth-child(6), .table-historial td:nth-child(6) { width: 14% !important; text-align: right !important; }
        .table-historial th:nth-child(7), .table-historial td:nth-child(7) { width: 14% !important; text-align: center !important; }
    }
</style>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Membrete exclusivo para impresión (Oculto en pantalla) --}}
    <div class="print-only-header hidden border-b-2 border-slate-900 pb-3 mb-2">
        <div class="flex justify-between items-start">
            <div>
                <span class="text-[9px] font-bold tracking-wider text-slate-500 uppercase">Crédito & Cartera</span>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight leading-none">ESTADO DE CUENTA</h1>
                <p class="text-xs font-semibold text-slate-600 mt-1">Consolidado general de obligaciones y recaudos</p>
            </div>
            <div class="text-right text-[10px] text-slate-600 leading-tight">
                <div class="font-bold text-slate-900 text-sm">{{ $cliente->empresa->nombre ?? config('app.name') }}</div>
                @if($cliente->empresa?->nit)
                    <div>NIT: {{ $cliente->empresa->nit }}</div>
                @endif
                <div class="text-slate-500 mt-1">Fecha de Emisión: {{ now()->format('d/m/Y H:i') }}</div>
                <div class="text-slate-400">Generado por: {{ auth()->user()->name }}</div>
            </div>
        </div>
    </div>

    {{-- Encabezado & Acciones en Pantalla --}}
    <x-page-header
        title="Estado de Cuenta: {{ $cliente->razon_social }}"
        description="Resumen integral de facturas a crédito, pagos aplicados y cupo comercial."
        :breadcrumbs="[
            ['label' => 'Crédito & Cartera', 'url' => route('cartera.index')],
            ['label' => 'Estado de Cuenta']
        ]">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('cartera.index') }}">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a Cartera
            </x-button>

            <x-button variant="primary" type="button" onclick="window.print()">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Estado de Cuenta
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Banner de Resumen de Cartera y Cupo -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-xs p-5 sm:p-6 space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start pb-5 border-b border-slate-100 gap-4 print:pb-2 print:border-slate-200">
            <div class="flex items-center space-x-4">
                <div class="h-12 w-12 rounded-lg flex items-center justify-center font-bold text-base client-avatar-box {{ $cliente->tipo_persona->value === 'JURIDICA' ? 'bg-brand-50 text-brand-700 border border-brand-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                    {{ strtoupper(substr($cliente->razon_social, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-900 print:text-base">{{ $cliente->razon_social }}</h2>
                        @if($deudaVencida > 0)
                            <x-badge color="danger">MORA</x-badge>
                        @else
                            <x-badge color="success">AL DÍA</x-badge>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 font-mono mt-0.5 print:text-[9.5px]">
                        {{ $cliente->tipo_documento->value }}: {{ $cliente->numero_documento }}
                        @if($cliente->nombre_comercial) • Comercial: {{ $cliente->nombre_comercial }} @endif
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5 print:text-[9px] print:text-slate-500">
                        {{ $cliente->direccion ?? 'Sin dirección' }} • Tel: {{ $cliente->telefono ?? 'Sin teléfono' }} • {{ $cliente->email ?? '' }}
                    </p>
                </div>
            </div>

            <div class="text-right print:hidden">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Fecha de Emisión:</span>
                <span class="text-sm font-semibold text-slate-800">{{ now()->translatedFormat('d \d\e F \d\e Y') }}</span>
            </div>
        </div>

        <!-- 4 Tarjetas de Métricas del Cliente -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 kpi-container">
            <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/80 print-kpi-box">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block print:text-[8pt]">Cupo de Crédito</span>
                <div class="text-xl font-bold font-mono tabular-nums text-slate-900 mt-1">${{ number_format($cupoTotal, 2) }}</div>
                <div class="text-xs text-slate-500 mt-0.5 print:text-[7.5pt]">Plazo: {{ $cliente->plazo_dias }} días</div>
            </div>

            <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/80 print-kpi-box">
                <span class="text-xs font-semibold text-warning-text uppercase tracking-wider block print:text-[8pt]">Deuda Total Pendiente</span>
                <div class="text-xl font-bold font-mono tabular-nums text-warning-text mt-1">${{ number_format($totalDeuda, 2) }}</div>
                <div class="text-xs text-warning-text mt-0.5 print:text-[7.5pt]">{{ $cuentasPendientes->count() }} facturas activas</div>
            </div>

            <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/80 print-kpi-box">
                <span class="text-xs font-semibold text-success-text uppercase tracking-wider block print:text-[8pt]">Cupo Disponible</span>
                <div class="text-xl font-bold font-mono tabular-nums text-success-text mt-1">${{ number_format($cupoDisponible, 2) }}</div>
                <div class="text-xs text-success-text mt-0.5 print:text-[7.5pt]">{{ 100 - $porcentajeUtilizado }}% libre</div>
            </div>

            <div class="p-4 rounded-lg {{ $deudaVencida > 0 ? 'bg-danger-bg border border-danger-border' : 'bg-slate-50 border border-slate-200/80' }} print-kpi-box">
                <span class="text-xs font-semibold {{ $deudaVencida > 0 ? 'text-danger-text' : 'text-slate-500' }} uppercase tracking-wider block print:text-[8pt]">
                    Saldo Vencido (Mora)
                </span>
                <div class="text-xl font-bold font-mono tabular-nums mt-1 {{ $deudaVencida > 0 ? 'text-danger-text' : 'text-slate-400' }}">
                    ${{ number_format($deudaVencida, 2) }}
                </div>
                <div class="text-xs {{ $deudaVencida > 0 ? 'text-danger-text font-medium' : 'text-slate-400' }} mt-0.5 print:text-[7.5pt]">
                    {{ $deudaVencida > 0 ? 'Exige recaudo prioritario' : 'Sin morosidad' }}
                </div>
            </div>
        </div>

        <!-- Barra de uso de cupo -->
        <div class="print:mt-1">
            <div class="flex justify-between text-xs font-semibold text-slate-600 mb-1.5 print:text-[8pt] print:mb-0.5">
                <span>Cupo Utilizado</span>
                <span>{{ $porcentajeUtilizado }}%</span>
            </div>
            <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden print:h-1.5">
                <div class="h-full rounded-full {{ $porcentajeUtilizado > 90 ? 'bg-danger-text' : ($porcentajeUtilizado > 70 ? 'bg-warning-text' : 'bg-brand-600') }}"
                     style="width: {{ $porcentajeUtilizado }}%"></div>
            </div>
        </div>
    </div>

    <!-- Sección 1: Cuentas Pendientes por Cobrar -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between print:p-2.5 print:bg-slate-50 bg-slate-50/50">
            <div>
                <h2 class="text-sm font-bold text-slate-900 print:text-xs">Obligaciones Pendientes de Pago</h2>
                <p class="text-xs text-slate-500 mt-0.5 print:hidden">Facturas y pagarés a crédito con saldo por cancelar.</p>
            </div>
            <span class="text-xs font-semibold text-slate-500 print:text-[8pt]">{{ $cuentasPendientes->count() }} registros</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs table-pendientes">
                <thead class="bg-slate-50 font-semibold text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left uppercase">Documento / Concepto</th>
                        <th class="px-4 py-3 text-left uppercase">Emisión</th>
                        <th class="px-4 py-3 text-left uppercase">Vencimiento</th>
                        <th class="px-4 py-3 text-right uppercase">Total</th>
                        <th class="px-4 py-3 text-right uppercase">Abonado</th>
                        <th class="px-4 py-3 text-right uppercase">Saldo Pendiente</th>
                        <th class="px-4 py-3 text-center uppercase">Condición</th>
                        <th class="px-4 py-3 text-right uppercase no-print">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($cuentasPendientes as $c)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900 font-mono">{{ $c->numero_documento }}</div>
                                <div class="text-slate-500 truncate max-w-xs print:max-w-none print:text-[7pt]">{{ $c->concepto }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-700 whitespace-nowrap">{{ $c->fecha_emision->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-slate-700 whitespace-nowrap">{{ $c->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right font-mono text-slate-800 whitespace-nowrap">${{ number_format($c->monto_total, 2) }}</td>
                            <td class="px-4 py-3 text-right font-mono font-semibold text-success-text whitespace-nowrap">${{ number_format($c->monto_pagado, 2) }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 whitespace-nowrap">${{ number_format($c->saldo_pendiente, 2) }}</td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($c->estaVencida())
                                    <x-badge color="danger">
                                        {{ abs($c->diasDiferenciaVencimiento()) }} días mora
                                    </x-badge>
                                @else
                                    <x-badge color="neutral">
                                        {{ $c->diasDiferenciaVencimiento() }} días restantes
                                    </x-badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap no-print">
                                <a href="{{ route('cartera.show', $c) }}" class="text-xs font-semibold text-brand-600 hover:text-brand-800">
                                    Ver Ficha &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                ¡Excelente! Este cliente se encuentra totalmente a paz y salvo sin deudas pendientes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sección 2: Historial Reciente de Abonos -->
    <div class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between print:p-2.5 print:bg-slate-50 bg-slate-50/50">
            <div>
                <h2 class="text-sm font-bold text-slate-900 print:text-xs">Historial de Pagos & Recaudos</h2>
                <p class="text-xs text-slate-500 mt-0.5 print:hidden">Últimos abonos registrados en caja aplicados a la cuenta del cliente.</p>
            </div>
            <span class="text-xs font-semibold text-slate-500 print:text-[8pt]">{{ $historialPagos->count() }} pagos</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs table-historial">
                <thead class="bg-slate-50 font-semibold text-slate-600">
                    <tr>
                        <th class="px-4 py-3 text-left uppercase">N° Recibo</th>
                        <th class="px-4 py-3 text-left uppercase">Fecha</th>
                        <th class="px-4 py-3 text-left uppercase">Obligación / Cuenta</th>
                        <th class="px-4 py-3 text-left uppercase">Medio de Pago</th>
                        <th class="px-4 py-3 text-right uppercase">Monto Abonado</th>
                        <th class="px-4 py-3 text-right uppercase">Saldo Resultante</th>
                        <th class="px-4 py-3 text-center uppercase">Estado</th>
                        <th class="px-4 py-3 text-right uppercase no-print">Recibo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($historialPagos as $p)
                        <tr class="hover:bg-slate-50/60 transition {{ $p->isAnulado() ? 'opacity-50' : '' }}">
                            <td class="px-4 py-3 font-semibold text-slate-900 whitespace-nowrap font-mono">{{ $p->numero_recibo }}</td>
                            <td class="px-4 py-3 text-slate-700 whitespace-nowrap">{{ $p->fecha_pago->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-slate-700 whitespace-nowrap font-mono">{{ $p->cuentaPorCobrar->numero_documento }}</td>
                            <td class="px-4 py-3 text-slate-700 whitespace-nowrap">
                                <x-badge color="neutral">{{ $p->metodo_pago->label() }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-semibold text-success-text whitespace-nowrap">${{ number_format($p->monto, 2) }}</td>
                            <td class="px-4 py-3 text-right font-mono font-semibold text-slate-800 whitespace-nowrap">${{ number_format($p->saldo_posterior, 2) }}</td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                @if($p->isAnulado())
                                    <x-badge color="danger">Anulado</x-badge>
                                @else
                                    <x-badge color="success">Aplicado</x-badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap no-print">
                                <a href="{{ route('cartera.recibo', $p) }}" target="_blank" class="text-xs font-semibold text-brand-600 hover:text-brand-800">
                                    Imprimir &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400">
                                No se registran pagos en el historial de este cliente.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pie de página exclusivo para impresión --}}
    <div class="print-footer hidden">
        <span>Sys-POS &bull; Sistema POS & Cartera Comercial</span>
        <span>Documento generado el {{ now()->format('d/m/Y H:i:s') }} por {{ auth()->user()->name }}</span>
    </div>

</div>
@endsection
