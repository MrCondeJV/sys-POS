@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Consolidado Multisucursal</h1>
            <p class="text-sm text-slate-500">Comparativo de existencias de inventario y ventas entre todas las sedes.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('traslados.index') }}" class="inline-flex items-center px-4 py-2 border border-slate-300 text-sm font-semibold rounded-xl text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
                Ver Traslados
            </a>
        </div>
    </div>

    {{-- Comparativo de Ventas --}}
    <div class="grid grid-cols-1 sm:grid-cols-{{ max(count($ventasPorSucursal), 1) }} gap-4">
        @foreach($ventasPorSucursal as $item)
            <div class="bg-white shadow-sm rounded-2xl border border-slate-200 p-5 border-l-4 border-indigo-500">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $item['sucursal']->nombre }}</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">${{ number_format($item['total'], 2) }}</p>
                <div class="mt-2 text-xs text-slate-500 flex justify-between">
                    <span>{{ $item['cantidad'] }} transacciones</span>
                    <span>Ticket prom: ${{ number_format($item['ticket_promedio'], 2) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Filtros de búsqueda --}}
    <div class="bg-white shadow-sm rounded-2xl border border-slate-200 p-4">
        <form method="GET" action="{{ route('reportes.multisucursal') }}" class="flex gap-3 items-center">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Buscar producto por nombre o código de barras..."
                    class="w-full pl-10 pr-4 py-2 text-sm border border-slate-300 rounded-xl">
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                Filtrar Existencias
            </button>
        </form>
    </div>

    {{-- Matriz de Inventario --}}
    <div class="bg-white shadow-sm rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Matriz de Existencias por Sucursal</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Categoría</th>
                        @foreach($sucursales as $s)
                            <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $s->nombre }}</th>
                        @endforeach
                        <th class="px-6 py-3 text-right text-xs font-bold text-indigo-700 uppercase tracking-wider">Stock Consolidado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($productos as $p)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4 font-semibold text-slate-900">
                                {{ $p->nombre }}
                                <span class="block text-xs text-slate-400">{{ $p->codigo_barras }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-500">{{ $p->categoria?->nombre ?? '-' }}</td>
                            @php $totalRow = 0; @endphp
                            @foreach($sucursales as $s)
                                @php
                                    $stockSede = (float) ($p->inventarios->where('sucursal_id', $s->id)->first()?->stock ?? 0);
                                    $totalRow += $stockSede;
                                @endphp
                                <td class="px-6 py-4 text-right font-mono {{ $stockSede <= 0 ? 'text-slate-300' : 'text-slate-800' }}">
                                    {{ number_format($stockSede, 2) }}
                                </td>
                            @endforeach
                            <td class="px-6 py-4 text-right font-mono font-bold text-indigo-700">
                                {{ number_format($totalRow, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($sucursales) + 3 }}" class="px-6 py-12 text-center text-slate-400">
                                No se encontraron productos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $productos->links() }}
        </div>
    </div>
</div>
@endsection
