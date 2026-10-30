@extends('layouts.app')

@section('title', 'Reporte de Cartera y Envejecimiento')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Reporte de Cartera y Antigüedad de Saldos"
        subtitle="Ventas fiadas por cobrar, abonos recibidos y antigüedad de la deuda desde la venta."
        :breadcrumbs="[
            ['label' => 'Reportes', 'url' => route('reportes.index')],
            ['label' => 'Cartera']
        ]">
        <x-slot:actions>
            <a href="{{ route('reportes.cartera.imprimir') }}" target="_blank" class="btn-secondary">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir / PDF
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- Filtros -->
    <div class="card p-4">
        <form method="GET" action="{{ route('reportes.cartera') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label class="label-base">Cliente</label>
                <select name="cliente_id" class="input text-xs">
                    <option value="">Todos los Clientes</option>
                    @foreach($clientes as $cl)
                        <option value="{{ $cl->id }}" {{ $clienteId == $cl->id ? 'selected' : '' }}>{{ $cl->razon_social }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-base">Sucursal</label>
                <select name="sucursal_id" class="input text-xs">
                    <option value="">Todas</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ $sucursalId == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1">Filtrar</button>
                <a href="{{ route('reportes.cartera') }}" class="btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- KPIs Antigüedad -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <x-kpi-card
            label="Saldo Pendiente"
            value="${{ number_format($saldoTotalPendiente, 2) }}"
            context="Deuda global activa"
            icon="currency-dollar"
        />

        <x-kpi-card
            label="Hasta 30 Días"
            value="${{ number_format($hasta30, 2) }}"
            context="Fiado reciente"
            variant="success"
            icon="check"
        />

        <x-kpi-card
            label="31 - 60 Días"
            value="${{ number_format($de31a60, 2) }}"
            context="Desde la venta"
            variant="warning"
            icon="calendar"
        />

        <x-kpi-card
            label="61 - 90 Días"
            value="${{ number_format($de61a90, 2) }}"
            context="Desde la venta"
            variant="warning"
            icon="calendar"
        />

        <x-kpi-card
            label="Más de 90 Días"
            value="${{ number_format($mas90, 2) }}"
            context="Deuda más antigua"
            variant="danger"
            icon="alert"
        />
    </div>

    <!-- Tabla -->
    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Cliente</th>
                        <th>Emisión</th>
                        <th>Antigüedad / Acuerdo</th>
                        <th class="text-right">Monto Original</th>
                        <th class="text-right">Abonado</th>
                        <th class="text-right">Saldo Pendiente</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cuentasPaginadas as $cp)
                    <tr>
                        <td class="font-mono font-semibold text-slate-900 text-xs">{{ $cp->numero_documento }}</td>
                        <td class="text-xs font-medium text-slate-800">{{ $cp->cliente?->razon_social }}</td>
                        <td class="text-xs text-slate-500 font-mono">{{ $cp->fecha_emision->format('d/m/Y') }}</td>
                        <td class="text-xs font-mono {{ $cp->estaVencida() ? 'text-danger-text font-semibold' : 'text-slate-600' }}">
                            {{ $cp->diasAntiguedad() }} d
                            @if($cp->fecha_vencimiento)
                                <span class="text-[10px] {{ $cp->estaVencida() ? 'text-danger-text' : 'text-slate-400' }}">(acordado {{ $cp->fecha_vencimiento->format('d/m/Y') }})</span>
                            @endif
                        </td>
                        <td class="text-right font-mono text-xs text-slate-700 tabular-nums">${{ number_format($cp->monto_total, 2) }}</td>
                        <td class="text-right font-mono text-xs text-success-text tabular-nums">${{ number_format($cp->monto_pagado, 2) }}</td>
                        <td class="text-right font-mono font-semibold text-xs text-slate-900 tabular-nums">${{ number_format($cp->saldo_pendiente, 2) }}</td>
                        <td class="text-center">
                            <span class="badge {{ $cp->estado->value === 'PAGADA' ? 'badge-success' : ($cp->estaVencida() ? 'badge-danger' : 'badge-warning') }}">
                                {{ $cp->estado->value }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12">
                            <div class="empty-state">
                                <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="empty-state-title">No hay cuentas por cobrar</p>
                                <p class="empty-state-desc">No se encontraron créditos registrados con saldos pendientes para los filtros aplicados.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $cuentasPaginadas->links() }}
        </div>
    </div>
</div>
@endsection
