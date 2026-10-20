@extends('layouts.app')

@section('title', 'Reporte de Utilidad y Rentabilidad')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Margen Bruto y Rentabilidad"
        subtitle="Cálculo de ganancia neta, costo histórico de reposición y margen porcentual por artículo."
        :breadcrumbs="[
            ['label' => 'Reportes', 'url' => route('reportes.index')],
            ['label' => 'Utilidad y Rentabilidad']
        ]">
        <x-slot:actions>
            <a href="{{ route('reportes.utilidad.imprimir', request()->all()) }}" target="_blank" class="btn-secondary">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir / PDF
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- Filtros -->
    <div class="card p-4">
        <form method="GET" action="{{ route('reportes.utilidad') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
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
                <label class="label-base">Categoría</label>
                <select name="categoria_id" class="input text-xs">
                    <option value="">Todas</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ $categoriaId == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1">Filtrar</button>
                <a href="{{ route('reportes.utilidad') }}" class="btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            label="Ingresos Netos Ventas"
            value="${{ number_format($totalIngreso, 2) }}"
            context="{{ number_format($totalUnidades, 0) }} artículos vendidos"
            icon="currency-dollar"
        />

        <x-kpi-card
            label="Costo Mercancía Vendida"
            value="${{ number_format($totalCosto, 2) }}"
            context="Costo histórico registrado"
            variant="danger"
            icon="alert"
        />

        <x-kpi-card
            label="Utilidad Bruta"
            value="${{ number_format($totalUtilidad, 2) }}"
            context="Ganancia neta sobre costo"
            variant="success"
            icon="check"
        />

        <x-kpi-card
            label="% Margen de Rentabilidad"
            value="{{ number_format($porcentajeMargen, 1) }}%"
            context="Sobre el total de ventas"
            icon="credit-card"
        />
    </div>

    <!-- Tabla Rentabilidad por Producto -->
    <div class="card p-0 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50/50">
            <h3 class="font-semibold text-slate-900 text-sm">Rentabilidad por Artículo</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Unidades</th>
                        <th class="text-right">Ingreso Neto</th>
                        <th class="text-right">Costo Total</th>
                        <th class="text-right">Utilidad Bruta</th>
                        <th class="text-center">% Margen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rentabilidadProductos as $r)
                    @php
                        $pctProd = $r->ingreso_neto > 0 ? round(($r->utilidad_bruta / $r->ingreso_neto) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td>
                            <div class="font-semibold text-slate-900 text-xs">{{ $r->producto_nombre }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">SKU: {{ $r->producto_codigo }}</div>
                        </td>
                        <td class="text-center font-mono text-xs text-slate-800 tabular-nums">{{ number_format($r->unidades_vendidas, 0) }}</td>
                        <td class="text-right font-mono text-xs text-slate-700 tabular-nums">${{ number_format($r->ingreso_neto, 2) }}</td>
                        <td class="text-right font-mono text-xs text-danger-text tabular-nums">${{ number_format($r->costo_total, 2) }}</td>
                        <td class="text-right font-mono font-semibold text-xs text-success-text tabular-nums">${{ number_format($r->utilidad_bruta, 2) }}</td>
                        <td class="text-center">
                            <span class="badge {{ $pctProd >= 20 ? 'badge-success' : ($pctProd > 0 ? 'badge-warning' : 'badge-danger') }} font-mono">
                                {{ $pctProd }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12">
                            <div class="empty-state">
                                <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                                <p class="empty-state-title">No hay ventas registradas</p>
                                <p class="empty-state-desc">No se encontraron movimientos comerciales en el periodo seleccionado.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $rentabilidadProductos->links() }}
        </div>
    </div>
</div>
@endsection
