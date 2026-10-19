@extends('layouts.app')

@section('title', 'Consolidado Multisucursal')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Consolidado Multisucursal"
        subtitle="Comparativo de existencias de inventario y ventas entre todas las sedes."
        :breadcrumbs="[
            ['label' => 'Reportes', 'url' => route('reportes.index')],
            ['label' => 'Multisucursal']
        ]">
        <x-slot:actions>
            <a href="{{ route('traslados.index') }}" class="btn-secondary">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Ver Traslados
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Comparativo de Ventas --}}
    <div class="grid grid-cols-1 sm:grid-cols-{{ max(count($ventasPorSucursal), 1) }} gap-4">
        @foreach($ventasPorSucursal as $item)
            <div class="card p-5 border-l-4 border-brand-600">
                <p class="kpi-label">{{ $item['sucursal']->nombre }}</p>
                <p class="text-2xl font-bold text-slate-900 mt-1 font-mono tabular-nums">${{ number_format($item['total'], 2) }}</p>
                <div class="mt-2 text-xs text-slate-500 flex justify-between font-mono">
                    <span>{{ $item['cantidad'] }} transacciones</span>
                    <span>Ticket prom: ${{ number_format($item['ticket_promedio'], 2) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filtros de búsqueda --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('reportes.multisucursal') }}" class="flex gap-3 items-center">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Buscar producto por nombre o código de barras..."
                    class="input-base pl-9 text-xs">
            </div>
            <button type="submit" class="btn-primary">
                Filtrar Existencias
            </button>
        </form>
    </div>

    {{-- Matriz de Inventario --}}
    <div class="card p-0 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 bg-slate-50/50">
            <h3 class="text-sm font-semibold text-slate-900">Matriz de Existencias por Sucursal</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table-corporate">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        @foreach($sucursales as $s)
                            <th class="text-right">{{ $s->nombre }}</th>
                        @endforeach
                        <th class="text-right font-bold text-brand-700">Stock Consolidado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $p)
                        <tr>
                            <td>
                                <div class="font-semibold text-slate-900 text-xs">{{ $p->nombre }}</div>
                                <span class="text-[11px] text-slate-400 font-mono">{{ $p->codigo_barras }}</span>
                            </td>
                            <td class="text-xs text-slate-500">{{ $p->categoria?->nombre ?? '-' }}</td>
                            @php $totalRow = 0; @endphp
                            @foreach($sucursales as $s)
                                @php
                                    $stockSede = (float) ($p->inventarios->where('sucursal_id', $s->id)->first()?->stock ?? 0);
                                    $totalRow += $stockSede;
                                @endphp
                                <td class="text-right font-mono text-xs tabular-nums {{ $stockSede <= 0 ? 'text-slate-300' : 'text-slate-800 font-medium' }}">
                                    {{ number_format($stockSede, 2) }}
                                </td>
                            @endforeach
                            <td class="text-right font-mono font-semibold text-xs text-brand-700 tabular-nums">
                                {{ number_format($totalRow, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($sucursales) + 3 }}" class="text-center py-12">
                                <div class="empty-state">
                                    <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <p class="empty-state-title">No se encontraron productos</p>
                                    <p class="empty-state-desc">No hay productos registrados en el catálogo para esta consulta.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">
            {{ $productos->links() }}
        </div>
    </div>
</div>
@endsection
