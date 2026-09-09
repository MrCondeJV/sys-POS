@extends('layouts.app')

@section('title', 'Estado de Cuenta: ' . $cliente->razon_social)

@section('styles')
<style>
    @media print {
        @page {
            size: letter portrait;
            margin: 10mm 12mm;
        }

        /* Show print-only header when printing */
        .print-only-header {
            display: block !important;
        }

        /* Make the content wrapper full-width and remove its max-width constraint */
        .max-w-6xl {
            max-width: 100% !important;
            width: 100% !important;
        }

        /* Remove decorative styles that don't print well */
        .rounded-3xl, .rounded-2xl, .rounded-xl, .rounded-full {
            border-radius: 4px !important;
        }

        .shadow-sm, .shadow {
            box-shadow: none !important;
        }

        /* Remove space-y gaps that cause blank pages */
        .space-y-6 > * + * {
            margin-top: 12px !important;
        }

        /* Allow tables to expand fully — no horizontal scroll */
        .overflow-x-auto {
            overflow: visible !important;
        }

        /* Fix progress bar for print */
        .h-2\.5 {
            height: 8px !important;
            border-radius: 4px !important;
        }

        /* Make cards print-friendly */
        .bg-white {
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
        }

        .bg-slate-50\/80,
        .bg-slate-50\/70,
        .bg-amber-50\/70,
        .bg-emerald-50\/70,
        .bg-rose-50 {
            background-color: #f8fafc !important;
        }

        /* Metric cards in grid */
        .grid-cols-4, .lg\\:grid-cols-4 {
            grid-template-columns: repeat(4, 1fr) !important;
        }

        /* Table rows clickable styles should be flat in print */
        tr.hover\\:bg-slate-50\\/70:hover {
            background-color: transparent !important;
        }

        /* Footer text on each page */
        body::after {
            content: "Estado de Cuenta generado por Sys-POS — " attr(data-empresa);
            display: block;
            text-align: center;
            font-size: 8pt;
            color: #94a3b8;
            margin-top: 12px;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    }
</style>
@endsection

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    {{-- Encabezado solo visible al imprimir --}}
    <div class="print-only-header" style="display:none">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px; border-bottom:2px solid #1e293b; padding-bottom:8px;">
            <div>
                <div style="font-size:18pt; font-weight:900; color:#1e293b;">ESTADO DE CUENTA</div>
                <div style="font-size:10pt; color:#475569; margin-top:2px;">Crédito &amp; Cartera</div>
            </div>
            <div style="text-align:right; font-size:8pt; color:#64748b;">
                <div>Fecha de emisión: {{ now()->format('d/m/Y H:i') }}</div>
                <div>Generado por: {{ auth()->user()->name }}</div>
            </div>
        </div>
        <div style="display:flex; gap:24px; margin-bottom:12px; font-size:9pt; color:#334155;">
            <div>
                <span style="font-weight:700;">Cliente:</span>
                {{ $cliente->razon_social }}
                @if($deudaVencida > 0)
                    <span style="color:#be123c; font-weight:700; margin-left:6px;">⚠ EN MORA</span>
                @else
                    <span style="color:#047857; font-weight:700; margin-left:6px;">✓ AL DÍA</span>
                @endif
            </div>
            <div>
                <span style="font-weight:700;">{{ $cliente->tipo_documento->value }}:</span>
                {{ $cliente->numero_documento }}
            </div>
        </div>
    </div>

    {{-- Encabezado & Acciones --}}
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

            <button onclick="window.print()" type="button"
                class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition shadow-sm">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Estado de Cuenta
            </button>
        </div>
    </div>

    <!-- Banner de Resumen de Cartera y Cupo -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start pb-6 border-b border-slate-100 gap-4">
            <div class="flex items-center space-x-4">
                <div class="h-14 w-14 rounded-2xl flex items-center justify-center font-black text-lg {{ $cliente->tipo_persona->value === 'JURIDICA' ? 'bg-blue-100 text-blue-700' : 'bg-indigo-100 text-indigo-700' }}">
                    {{ strtoupper(substr($cliente->razon_social, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-900">{{ $cliente->razon_social }}</h2>
                    <p class="text-xs text-slate-500 font-semibold mt-0.5">
                        {{ $cliente->tipo_documento->value }}: {{ $cliente->numero_documento }}
                        @if($cliente->nombre_comercial) • Comercial: {{ $cliente->nombre_comercial }} @endif
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ $cliente->direccion ?? 'Sin dirección' }} • Tel: {{ $cliente->telefono ?? 'Sin teléfono' }} • {{ $cliente->email ?? '' }}
                    </p>
                </div>
            </div>

            <div class="text-right">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Fecha de Emisión:</span>
                <span class="text-sm font-bold text-slate-800">{{ now()->translatedFormat('d \d\e F \d\e Y') }}</span>
            </div>
        </div>

        <!-- 4 Tarjetas de Métricas del Cliente -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Cupo de Crédito</span>
                <div class="text-xl font-black text-slate-900 mt-1">${{ number_format($cupoTotal, 2) }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Plazo: {{ $cliente->plazo_dias }} días</div>
            </div>

            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-100">
                <span class="text-xs font-bold text-amber-700 uppercase tracking-wider block">Deuda Total Pendiente</span>
                <div class="text-xl font-black text-amber-900 mt-1">${{ number_format($totalDeuda, 2) }}</div>
                <div class="text-[11px] text-amber-700 mt-0.5">{{ $cuentasPendientes->count() }} facturas activas</div>
            </div>

            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-100">
                <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider block">Cupo Disponible</span>
                <div class="text-xl font-black text-emerald-600 mt-1">${{ number_format($cupoDisponible, 2) }}</div>
                <div class="text-[11px] text-emerald-700 mt-0.5">{{ 100 - $porcentajeUtilizado }}% libre</div>
            </div>

            <div class="p-4 rounded-2xl {{ $deudaVencida > 0 ? 'bg-rose-50 border border-rose-200' : 'bg-slate-50 border border-slate-100' }}">
                <span class="text-xs font-bold {{ $deudaVencida > 0 ? 'text-rose-700' : 'text-slate-400' }} uppercase tracking-wider block">
                    Saldo Vencido (Mora)
                </span>
                <div class="text-xl font-black mt-1 {{ $deudaVencida > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                    ${{ number_format($deudaVencida, 2) }}
                </div>
                <div class="text-[11px] {{ $deudaVencida > 0 ? 'text-rose-600 font-semibold' : 'text-slate-400' }} mt-0.5">
                    {{ $deudaVencida > 0 ? 'Exige recaudo prioritario' : 'Sin morosidad' }}
                </div>
            </div>
        </div>

        <!-- Barra de uso de cupo -->
        <div>
            <div class="flex justify-between text-xs font-bold text-slate-600 mb-1.5">
                <span>Cupo Utilizado</span>
                <span>{{ $porcentajeUtilizado }}%</span>
            </div>
            <div class="h-2.5 w-full bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full {{ $porcentajeUtilizado > 90 ? 'bg-rose-500' : ($porcentajeUtilizado > 70 ? 'bg-amber-500' : 'bg-indigo-600') }}"
                     style="width: {{ $porcentajeUtilizado }}%"></div>
            </div>
        </div>
    </div>

    <!-- Sección 1: Cuentas Pendientes por Cobrar -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Obligaciones Pendientes de Pago</h2>
                <p class="text-xs text-slate-500 mt-0.5">Facturas y pagarés a crédito con saldo por cancelar.</p>
            </div>
            <span class="text-xs font-bold text-slate-500">{{ $cuentasPendientes->count() }} registros</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
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
                                <div class="font-bold text-slate-900">{{ $c->numero_documento }}</div>
                                <div class="text-slate-500 truncate max-w-xs">{{ $c->concepto }}</div>
                            </td>
                            <td class="px-6 py-3.5 text-slate-700 whitespace-nowrap">{{ $c->fecha_emision->format('d/m/Y') }}</td>
                            <td class="px-6 py-3.5 text-slate-700 whitespace-nowrap">{{ $c->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td class="px-6 py-3.5 text-right font-medium text-slate-800 whitespace-nowrap">${{ number_format($c->monto_total, 2) }}</td>
                            <td class="px-6 py-3.5 text-right font-bold text-emerald-600 whitespace-nowrap">${{ number_format($c->monto_pagado, 2) }}</td>
                            <td class="px-6 py-3.5 text-right font-black text-slate-900 whitespace-nowrap">${{ number_format($c->saldo_pendiente, 2) }}</td>
                            <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                @if($c->estaVencida())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                        {{ abs($c->diasDiferenciaVencimiento()) }} días mora
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
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
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-900">Historial de Pagos & Recaudos</h2>
                <p class="text-xs text-slate-500 mt-0.5">Últimos abonos registrados en caja aplicados a la cuenta del cliente.</p>
            </div>
            <span class="text-xs font-bold text-slate-500">{{ $historialPagos->count() }} pagos</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
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
                            <td class="px-6 py-3.5 font-bold text-slate-900 whitespace-nowrap">{{ $p->numero_recibo }}</td>
                            <td class="px-6 py-3.5 text-slate-700 whitespace-nowrap">{{ $p->fecha_pago->format('d/m/Y') }}</td>
                            <td class="px-6 py-3.5 text-slate-700 whitespace-nowrap">{{ $p->cuentaPorCobrar->numero_documento }}</td>
                            <td class="px-6 py-3.5 text-slate-700 whitespace-nowrap">
                                <span class="px-2 py-0.5 bg-slate-100 rounded text-[11px] font-medium">{{ $p->metodo_pago->label() }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-right font-black text-emerald-600 whitespace-nowrap">${{ number_format($p->monto, 2) }}</td>
                            <td class="px-6 py-3.5 text-right font-bold text-slate-800 whitespace-nowrap">${{ number_format($p->saldo_posterior, 2) }}</td>
                            <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $p->isAnulado() ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">
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

</div>
@endsection
