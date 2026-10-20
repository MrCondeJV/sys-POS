@extends('layouts.app')

@section('title', 'Reporte de Ventas')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <x-page-header
        title="Reporte Detallado de Ventas"
        subtitle="Listado analítico de transacciones comerciales, liquidación de impuestos y ticket promedio."
        :breadcrumbs="[
            ['label' => 'Reportes', 'url' => route('reportes.index')],
            ['label' => 'Ventas']
        ]">
        <x-slot:actions>
            <a href="{{ route('reportes.ventas.imprimir', request()->all()) }}" target="_blank" class="btn-secondary">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir / PDF
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- Filtros -->
    <div class="card p-4">
        <form method="GET" action="{{ route('reportes.ventas') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div>
                <label class="label-base">Desde</label>
                <input type="date" name="fecha_desde" value="{{ $fechaDesde }}" class="input text-xs">
            </div>
            <div>
                <label class="label-base">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}" class="input text-xs">
            </div>
            <div>
                <label class="label-base">Sucursal</label>
                <select name="sucursal_id" class="input text-xs">
                    <option value="">Todas las Sedes</option>
                    @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}" {{ $sucursalId == $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-base">Cajero / Vendedor</label>
                <select name="usuario_id" class="input text-xs">
                    <option value="">Todos los Usuarios</option>
                    @foreach($usuarios as $usr)
                        <option value="{{ $usr->id }}" {{ $usuarioId == $usr->id ? 'selected' : '' }}>{{ $usr->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-base">Tipo de Pago</label>
                <select name="tipo_pago" class="input text-xs">
                    <option value="">Todos</option>
                    <option value="CONTADO" {{ $tipoPago === 'CONTADO' ? 'selected' : '' }}>Contado</option>
                    <option value="CREDITO" {{ $tipoPago === 'CREDITO' ? 'selected' : '' }}>Crédito</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1">
                    Filtrar
                </button>
                <a href="{{ route('reportes.ventas') }}" class="btn-secondary">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- KPIs Resumen -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            label="Total Ventas Netas"
            value="${{ number_format($totalNeto, 2) }}"
            context="{{ $cantidadVentas }} ventas completadas"
            variant="success"
            icon="currency-dollar"
        />

        <x-kpi-card
            label="Ticket Promedio"
            value="${{ number_format($ticketPromedio, 2) }}"
            context="Por comprobante emitido"
            icon="credit-card"
        />

        <x-kpi-card
            label="IVA Generado"
            value="${{ number_format($totalImpuestos, 2) }}"
            context="Impuestos recaudados"
            icon="check"
        />

        <x-kpi-card
            label="Descuentos Aplicados"
            value="${{ number_format($totalDescuentos, 2) }}"
            context="En el periodo seleccionado"
            variant="danger"
            icon="alert"
        />
    </div>

    <!-- Tabla Detallada -->
    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Comprobante</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Sucursal</th>
                        <th>Pago</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-right">IVA</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $v)
                    <tr>
                        <td>
                            <a href="{{ route('ventas.show', $v) }}" class="font-mono font-semibold text-brand-600 hover:text-brand-800 text-xs">
                                {{ $v->numero_venta }}
                            </a>
                        </td>
                        <td class="text-xs text-slate-500 font-mono">{{ $v->fecha->format('d/m/Y H:i') }}</td>
                        <td class="text-xs font-medium text-slate-800">{{ $v->cliente?->razon_social ?? 'Consumidor Final' }}</td>
                        <td class="text-xs text-slate-600">{{ $v->sucursal?->nombre }}</td>
                        <td>
                            <span class="badge {{ $v->tipo_pago->value === 'CONTADO' ? 'badge-neutral' : 'badge-warning' }}">
                                {{ $v->tipo_pago->value }} ({{ $v->metodo_pago }})
                            </span>
                        </td>
                        <td class="text-right font-mono text-xs text-slate-700 tabular-nums">${{ number_format($v->subtotal, 2) }}</td>
                        <td class="text-right font-mono text-xs text-slate-500 tabular-nums">${{ number_format($v->impuesto, 2) }}</td>
                        <td class="text-right font-mono font-semibold text-xs text-slate-900 tabular-nums">${{ number_format($v->total, 2) }}</td>
                        <td class="text-center">
                            <span class="badge {{ $v->estado->value === 'COMPLETADA' ? 'badge-success' : 'badge-danger' }}">
                                {{ $v->estado->value }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-12">
                            <div class="empty-state">
                                <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <p class="empty-state-title">No se encontraron ventas</p>
                                <p class="empty-state-desc">Ajusta los filtros de fecha o sede para consultar otros periodos comerciales.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $ventas->links() }}
        </div>
    </div>
</div>
@endsection
