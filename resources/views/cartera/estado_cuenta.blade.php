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
            background-color: #ffffff !important;
            color: #0f172a !important;
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
            border-top: 1px solid #cbd5e1 !important;
            font-size: 7.5pt !important;
            color: #64748b !important;
        }

        /* Contenedores limpios sin bordes exagerados ni sombras */
        .rounded-3xl, .rounded-2xl, .rounded-xl {
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

        .kpi-card-item {
            padding: 6px 8px !important;
            border-radius: 4px !important;
            border: 1px solid #e2e8f0 !important;
            background-color: #f8fafc !important;
        }

        .kpi-card-item .text-xl {
            font-size: 13pt !important;
            font-weight: 900 !important;
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
            background-color: #f1f5f9 !important;
            color: #334155 !important;
            border-bottom: 1.5px solid #cbd5e1 !important;
            text-transform: uppercase !important;
            white-space: normal !important;
        }

        td {
            padding: 4px 6px !important;
            font-size: 8pt !important;
            border-bottom: 1px solid #f1f5f9 !important;
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
<div class="max-w-[1680px] mx-auto space-y-6">

    {{-- Membrete exclusivo para impresión (Oculto en pantalla) --}}
    <div class="print-only-header hidden border-b-2 border-slate-900 pb-3 mb-2">
        <div class="flex justify-between items-start">
            <div>
                <span class="text-[9px] font-bold tracking-wider text-slate-500 uppercase">Crédito & Cartera</span>
                <h1 class="text-xl font-black text-slate-900 tracking-tight leading-none">ESTADO DE CUENTA</h1>
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
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 no-print">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('cartera.index') }}" class="hover:text-indigo-600 transition">Crédito & Cartera</a>
                <span>/</span>
                <span class="text-slate-800">Estado de Cuenta</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Estado de Cuenta: {{ $cliente->razon_social }}
                </h1>
                @if($deudaVencida > 0)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                        CLIENTE EN MORA
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        AL DÍA
                    </span>
                @endif
            </div>
            <p class="text-sm text-slate-500">Resumen integral de facturas a crédito, pagos aplicados y cupo comercial.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('cartera.index') }}"
                class="inline-flex items-center px-3.5 py-2 rounded-xl border border-slate-300 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a Cartera
            </a>

            <button type="button" onclick="window.print()"
                class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition shadow-sm cursor-pointer">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Estado de Cuenta
            </button>
        </div>
    </div>

    <!-- Banner de Resumen de Cartera y Cupo -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start pb-6 border-b border-slate-100 gap-4 print:pb-2 print:border-slate-200">
            <div class="flex items-center space-x-4">
                <div class="h-14 w-14 rounded-2xl flex items-center justify-center font-black text-lg client-avatar-box {{ $cliente->tipo_persona->value === 'JURIDICA' ? 'bg-blue-100 text-blue-700' : 'bg-indigo-100 text-indigo-700' }}">
                    {{ strtoupper(substr($cliente->razon_social, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-black text-slate-900 print:text-base">{{ $cliente->razon_social }}</h2>
                        @if($deudaVencida > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                MORA
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                AL DÍA
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 font-semibold mt-0.5 print:text-[9.5px]">
                        {{ $cliente->tipo_documento->value }}: {{ $cliente->numero_documento }}
                        @if($cliente->nombre_comercial) • Comercial: {{ $cliente->nombre_comercial }} @endif
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5 print:text-[9px] print:text-slate-500">
                        {{ $cliente->direccion ?? 'Sin dirección' }} • Tel: {{ $cliente->telefono ?? 'Sin teléfono' }} • {{ $cliente->email ?? '' }}
                    </p>
                </div>
            </div>

            <div class="text-right print:hidden">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Fecha de Emisión:</span>
                <span class="text-sm font-bold text-slate-800">{{ now()->translatedFormat('d \d\e F \d\e Y') }}</span>
            </div>
        </div>

        <!-- 4 Tarjetas de Métricas del Cliente -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 kpi-container">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 kpi-card-item">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block print:text-[8pt]">Cupo de Crédito</span>
                <div class="text-xl font-black text-slate-900 mt-1">${{ number_format($cupoTotal, 2) }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5 print:text-[7.5pt]">Plazo: {{ $cliente->plazo_dias }} días</div>
            </div>

            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-100 kpi-card-item">
                <span class="text-xs font-bold text-amber-700 uppercase tracking-wider block print:text-[8pt]">Deuda Total Pendiente</span>
                <div class="text-xl font-black text-amber-900 mt-1">${{ number_format($totalDeuda, 2) }}</div>
                <div class="text-[11px] text-amber-700 mt-0.5 print:text-[7.5pt]">{{ $cuentasPendientes->count() }} facturas activas</div>
            </div>

            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-100 kpi-card-item">
                <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider block print:text-[8pt]">Cupo Disponible</span>
                <div class="text-xl font-black text-emerald-600 mt-1">${{ number_format($cupoDisponible, 2) }}</div>
                <div class="text-[11px] text-emerald-700 mt-0.5 print:text-[7.5pt]">{{ 100 - $porcentajeUtilizado }}% libre</div>
            </div>

            <div class="p-4 rounded-2xl {{ $deudaVencida > 0 ? 'bg-rose-50 border border-rose-200' : 'bg-slate-50 border border-slate-100' }} kpi-card-item">
                <span class="text-xs font-bold {{ $deudaVencida > 0 ? 'text-rose-700' : 'text-slate-400' }} uppercase tracking-wider block print:text-[8pt]">
                    Saldo Vencido (Mora)
                </span>
                <div class="text-xl font-black mt-1 {{ $deudaVencida > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                    ${{ number_format($deudaVencida, 2) }}
                </div>
                <div class="text-[11px] {{ $deudaVencida > 0 ? 'text-rose-600 font-semibold' : 'text-slate-400' }} mt-0.5 print:text-[7.5pt]">
                    {{ $deudaVencida > 0 ? 'Exige recaudo prioritario' : 'Sin morosidad' }}
                </div>
            </div>
        </div>

        <!-- Barra de uso de cupo -->
        <div class="print:mt-1">
            <div class="flex justify-between text-xs font-bold text-slate-600 mb-1.5 print:text-[8pt] print:mb-0.5">
                <span>Cupo Utilizado</span>
                <span>{{ $porcentajeUtilizado }}%</span>
            </div>
            <div class="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden print:h-1.5">
                <div class="h-full rounded-full {{ $porcentajeUtilizado > 90 ? 'bg-rose-500' : ($porcentajeUtilizado > 70 ? 'bg-amber-500' : 'bg-indigo-600') }}"
                     style="width: {{ $porcentajeUtilizado }}%"></div>
            </div>
        </div>
    </div>

    <!-- Sección 1: Cuentas Pendientes por Cobrar -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between print:p-2.5 print:bg-slate-50">
            <div>
                <h2 class="text-base font-bold text-slate-900 print:text-xs">Obligaciones Pendientes de Pago</h2>
                <p class="text-xs text-slate-500 mt-0.5 print:hidden">Facturas y pagarés a crédito con saldo por cancelar.</p>
            </div>
            <span class="text-xs font-bold text-slate-500 print:text-[8pt]">{{ $cuentasPendientes->count() }} registros</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs table-pendientes">
                <thead class="bg-slate-50/80 font-bold text-slate-700">
                    <tr>
                        <th class="px-6 py-3.5 text-left uppercase">Documento / Concepto</th>
                        <th class="px-6 py-3.5 text-left uppercase">Emisión</th>
                        <th class="px-6 py-3.5 text-left uppercase">Vencimiento</th>
                        <th class="px-6 py-3.5 text-right uppercase">Total</th>
                        <th class="px-6 py-3.5 text-right uppercase">Abonado</th>
                        <th class="px-6 py-3.5 text-right uppercase">Saldo Pendiente</th>
                        <th class="px-6 py-3.5 text-center uppercase">Condición</th>
                        <th class="px-6 py-3.5 text-right uppercase no-print">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($cuentasPendientes as $c)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-3.5">
                                <div class="font-bold text-slate-900 font-mono">{{ $c->numero_documento }}</div>
                                <div class="text-slate-500 truncate max-w-xs print:max-w-none print:text-[7pt]">{{ $c->concepto }}</div>
                            </td>
                            <td class="px-6 py-3.5 text-slate-700 whitespace-nowrap">{{ $c->fecha_emision->format('d/m/Y') }}</td>
                            <td class="px-6 py-3.5 text-slate-700 whitespace-nowrap">{{ $c->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td class="px-6 py-3.5 text-right font-medium text-slate-800 whitespace-nowrap">${{ number_format($c->monto_total, 2) }}</td>
                            <td class="px-6 py-3.5 text-right font-bold text-emerald-600 whitespace-nowrap">${{ number_format($c->monto_pagado, 2) }}</td>
                            <td class="px-6 py-3.5 text-right font-black text-slate-900 whitespace-nowrap">${{ number_format($c->saldo_pendiente, 2) }}</td>
                            <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                @if($c->estaVencida())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 print:text-[7pt] print:px-1.5">
                                        {{ abs($c->diasDiferenciaVencimiento()) }} días mora
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 print:text-[7pt] print:px-1.5">
                                        {{ $c->diasDiferenciaVencimiento() }} días restantes
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-right whitespace-nowrap no-print">
                                <a href="{{ route('cartera.show', $c) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                    Ver Ficha &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-400">
                                ¡Excelente! Este cliente se encuentra totalmente a paz y salvo sin deudas pendientes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sección 2: Historial Reciente de Abonos -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between print:p-2.5 print:bg-slate-50">
            <div>
                <h2 class="text-base font-bold text-slate-900 print:text-xs">Historial de Pagos & Recaudos</h2>
                <p class="text-xs text-slate-500 mt-0.5 print:hidden">Últimos abonos registrados en caja aplicados a la cuenta del cliente.</p>
            </div>
            <span class="text-xs font-bold text-slate-500 print:text-[8pt]">{{ $historialPagos->count() }} pagos</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs table-historial">
                <thead class="bg-slate-50/80 font-bold text-slate-700">
                    <tr>
                        <th class="px-6 py-3.5 text-left uppercase">N° Recibo</th>
                        <th class="px-6 py-3.5 text-left uppercase">Fecha</th>
                        <th class="px-6 py-3.5 text-left uppercase">Obligación / Cuenta</th>
                        <th class="px-6 py-3.5 text-left uppercase">Medio de Pago</th>
                        <th class="px-6 py-3.5 text-right uppercase">Monto Abonado</th>
                        <th class="px-6 py-3.5 text-right uppercase">Saldo Resultante</th>
                        <th class="px-6 py-3.5 text-center uppercase">Estado</th>
                        <th class="px-6 py-3.5 text-right uppercase no-print">Recibo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($historialPagos as $p)
                        <tr class="hover:bg-slate-50/70 transition {{ $p->isAnulado() ? 'opacity-50' : '' }}">
                            <td class="px-6 py-3.5 font-bold text-slate-900 whitespace-nowrap font-mono">{{ $p->numero_recibo }}</td>
                            <td class="px-6 py-3.5 text-slate-700 whitespace-nowrap">{{ $p->fecha_pago->format('d/m/Y') }}</td>
                            <td class="px-6 py-3.5 text-slate-700 whitespace-nowrap font-mono">{{ $p->cuentaPorCobrar->numero_documento }}</td>
                            <td class="px-6 py-3.5 text-slate-700 whitespace-nowrap">
                                <span class="px-2 py-0.5 bg-slate-100 rounded text-[11px] font-medium print:text-[7pt] print:bg-transparent print:p-0">{{ $p->metodo_pago->label() }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-right font-black text-emerald-600 whitespace-nowrap">${{ number_format($p->monto, 2) }}</td>
                            <td class="px-6 py-3.5 text-right font-bold text-slate-800 whitespace-nowrap">${{ number_format($p->saldo_posterior, 2) }}</td>
                            <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $p->isAnulado() ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }} print:text-[7pt] print:px-1.5">
                                    {{ $p->estado->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right whitespace-nowrap no-print">
                                <a href="{{ route('cartera.recibo', $p) }}" target="_blank" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                    Imprimir &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-slate-400">
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
