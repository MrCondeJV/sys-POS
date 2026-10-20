@extends('layouts.app')

@section('title', 'Reporte de Compras')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Reporte de Compras a Proveedores"
        subtitle="Registro consolidado de compras de inventario, costos de adquisición y crédito fiscal."
        :breadcrumbs="[
            ['label' => 'Reportes', 'url' => route('reportes.index')],
            ['label' => 'Compras']
        ]">
        <x-slot:actions>
            <a href="{{ route('reportes.compras.imprimir', request()->all()) }}" target="_blank" class="btn-secondary">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir / PDF
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- Filtros -->
    <div class="card p-4">
        <form method="GET" action="{{ route('reportes.compras') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
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
                    <option value="">Todas</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ $sucursalId == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-base">Proveedor</label>
                <select name="proveedor_id" class="input text-xs">
                    <option value="">Todos</option>
                    @foreach($proveedores as $p)
                        <option value="{{ $p->id }}" {{ $proveedorId == $p->id ? 'selected' : '' }}>{{ $p->razon_social }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1">Filtrar</button>
                <a href="{{ route('reportes.compras') }}" class="btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-kpi-card
            label="Total Comprado"
            value="${{ number_format($totalComprado, 2) }}"
            context="{{ $totalComprasCount }} facturas registradas"
            icon="currency-dollar"
        />

        <x-kpi-card
            label="IVA Descontable"
            value="${{ number_format($totalImpuestos, 2) }}"
            context="Impuestos en compras"
            variant="success"
            icon="check"
        />

        <x-kpi-card
            label="Subtotal Sin IVA"
            value="${{ number_format($totalComprado - $totalImpuestos, 2) }}"
            context="Base gravable global"
            icon="credit-card"
        />
    </div>

    <!-- Tabla -->
    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Factura N°</th>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Sucursal</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-right">IVA</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($compras as $c)
                    <tr>
                        <td class="font-mono font-semibold text-slate-900 text-xs">#{{ $c->numero_factura }}</td>
                        <td class="text-xs text-slate-500 font-mono">{{ $c->fecha_emision->format('d/m/Y') }}</td>
                        <td class="text-xs font-medium text-slate-800">{{ $c->proveedor?->razon_social }}</td>
                        <td class="text-xs text-slate-600">{{ $c->sucursal?->nombre }}</td>
                        <td class="text-right font-mono text-xs text-slate-700 tabular-nums">${{ number_format($c->subtotal, 2) }}</td>
                        <td class="text-right font-mono text-xs text-slate-500 tabular-nums">${{ number_format($c->impuestos, 2) }}</td>
                        <td class="text-right font-mono font-semibold text-xs text-slate-900 tabular-nums">${{ number_format($c->total, 2) }}</td>
                        <td class="text-center">
                            <span class="badge {{ $c->estado->value === 'REGISTRADA' ? 'badge-success' : 'badge-danger' }}">
                                {{ $c->estado->value }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12">
                            <div class="empty-state">
                                <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="empty-state-title">No se encontraron compras</p>
                                <p class="empty-state-desc">No hay compras registradas para el rango de fechas o proveedor seleccionado.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $compras->links() }}
        </div>
    </div>
</div>
@endsection
