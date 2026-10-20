@extends('layouts.app')

@section('title', 'Valoración de Inventario')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Valoración de Existencias e Inventario"
        subtitle="Auditoría de existencias físicas, costo valorizado en bodega y margen proyectado de venta."
        :breadcrumbs="[
            ['label' => 'Reportes', 'url' => route('reportes.index')],
            ['label' => 'Inventario']
        ]">
        <x-slot:actions>
            <a href="{{ route('reportes.inventario.imprimir', request()->all()) }}" target="_blank" class="btn-secondary">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir / PDF
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- Filtros -->
    <div class="card p-4">
        <form method="GET" action="{{ route('reportes.inventario') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="label-base">Sucursal</label>
                <select name="sucursal_id" class="input text-xs">
                    <option value="">Todas las Sedes</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ $sucursalId == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-base">Categoría</label>
                <select name="categoria_id" class="input text-xs">
                    <option value="">Todas</option>
                    @foreach($categorias as $c)
                        <option value="{{ $c->id }}" {{ $categoriaId == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-base">Estado de Stock</label>
                <select name="filtro_stock" class="input text-xs">
                    <option value="todos" {{ $filtroStock === 'todos' ? 'selected' : '' }}>Todos</option>
                    <option value="bajo_stock" {{ $filtroStock === 'bajo_stock' ? 'selected' : '' }}>Bajo Stock Mínimo</option>
                    <option value="agotados" {{ $filtroStock === 'agotados' ? 'selected' : '' }}>Agotados (0 o menos)</option>
                    <option value="disponibles" {{ $filtroStock === 'disponibles' ? 'selected' : '' }}>Con Disponibilidad</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1">Filtrar</button>
                <a href="{{ route('reportes.inventario') }}" class="btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- KPIs Valoración -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            label="Unidades en Stock"
            value="{{ number_format($totalUnidades, 0) }}"
            context="En almacén físico"
            icon="check"
        />

        <x-kpi-card
            label="Costo Valorizado Total"
            value="${{ number_format($totalCostoValorizado, 2) }}"
            context="Inversión actual en bodega"
            variant="warning"
            icon="currency-dollar"
        />

        <x-kpi-card
            label="Valor Proyectado Venta"
            value="${{ number_format($totalValorVenta, 2) }}"
            context="A precio catálogo actual"
            icon="credit-card"
        />

        <x-kpi-card
            label="Utilidad Potencial"
            value="${{ number_format($utilidadPotencial, 2) }}"
            context="Margen proyectado de venta"
            variant="success"
            icon="currency-dollar"
        />
    </div>

    <!-- Tabla -->
    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Sucursal</th>
                        <th class="text-center">Stock Actual</th>
                        <th class="text-right">Costo Unit.</th>
                        <th class="text-right">Costo Total</th>
                        <th class="text-right">Precio Venta</th>
                        <th class="text-right">Valor Venta Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventarios as $inv)
                    @php
                        $costoTotalItem = (float) $inv->stock * (float) $inv->producto->precio_costo;
                        $ventaTotalItem = (float) $inv->stock * (float) $inv->producto->precio_venta;
                        $esBajo = (float) $inv->stock <= (float) $inv->producto->stock_minimo;
                    @endphp
                    <tr>
                        <td>
                            <div class="font-semibold text-slate-900 text-xs">{{ $inv->producto->nombre }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">SKU: {{ $inv->producto->codigo }} | {{ $inv->producto->categoria?->nombre }}</div>
                        </td>
                        <td class="text-xs text-slate-600">{{ $inv->sucursal?->nombre }}</td>
                        <td class="text-center">
                            <span class="badge {{ $esBajo ? 'badge-danger' : 'badge-success' }} font-mono tabular-nums">
                                {{ number_format($inv->stock, 2) }}
                            </span>
                        </td>
                        <td class="text-right font-mono text-xs text-slate-700 tabular-nums">${{ number_format($inv->producto->precio_costo, 2) }}</td>
                        <td class="text-right font-mono text-xs text-warning-text tabular-nums">${{ number_format($costoTotalItem, 2) }}</td>
                        <td class="text-right font-mono text-xs text-slate-700 tabular-nums">${{ number_format($inv->producto->precio_venta, 2) }}</td>
                        <td class="text-right font-mono font-semibold text-xs text-slate-900 tabular-nums">${{ number_format($ventaTotalItem, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <div class="empty-state">
                                <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <p class="empty-state-title">No hay registros de inventario</p>
                                <p class="empty-state-desc">No se encontraron productos o existencias para los criterios seleccionados.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $inventarios->links() }}
        </div>
    </div>
</div>
@endsection
