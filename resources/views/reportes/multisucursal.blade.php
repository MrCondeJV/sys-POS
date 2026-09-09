@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Consolidado Multisucursal</h1>
            <p class="text-sm text-gray-500">Comparativo de existencias de inventario y ventas entre todas las sedes.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('traslados.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Ver Traslados
            </a>
        </div>
    </div>

    <!-- Comparativo de Ventas -->
    <div class="grid grid-cols-1 sm:grid-cols-{{ max(count($ventasPorSucursal), 1) }} gap-4">
        @foreach($ventasPorSucursal as $item)
            <div class="bg-white shadow rounded-lg p-5 border-l-4 border-indigo-500">
                <p class="text-xs font-medium text-gray-500 uppercase">{{ $item['sucursal']->nombre }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">${{ number_format($item['total'], 2) }}</p>
                <div class="mt-2 text-xs text-gray-600 flex justify-between">
                    <span>{{ $item['cantidad'] }} transacciones</span>
                    <span>Ticket prom: ${{ number_format($item['ticket_promedio'], 2) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filtros de búsqueda de inventario -->
    <div class="bg-white shadow rounded-lg p-4">
        <form method="GET" action="{{ route('reportes.multisucursal') }}" class="flex gap-4 items-center">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar producto por nombre o código de barras..." class="w-full text-sm rounded-md border-gray-300 shadow-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow">
                Filtrar Existencias
            </button>
        </form>
    </div>

    <!-- Matriz de Inventario por Sede -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Matriz de Existencias por Sucursal</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Producto</th>
                    <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase">Categoría</th>
                    @foreach($sucursales as $s)
                        <th class="px-6 py-3 text-right font-medium text-gray-500 uppercase">{{ $s->nombre }}</th>
                    @endforeach
                    <th class="px-6 py-3 text-right font-semibold text-indigo-700 uppercase">Stock Consolidado</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($productos as $p)
                    <tr>
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $p->nombre }}
                            <span class="block text-xs text-gray-400">{{ $p->codigo_barras }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-500">{{ $p->categoria?->nombre ?? '-' }}</td>
                        @php $totalRow = 0; @endphp
                        @foreach($sucursales as $s)
                            @php
                                $stockSede = (float) ($p->inventarios->where('sucursal_id', $s->id)->first()?->stock ?? 0);
                                $totalRow += $stockSede;
                            @endphp
                            <td class="px-6 py-4 text-right font-mono {{ $stockSede <= 0 ? 'text-gray-300' : 'text-gray-900' }}">
                                {{ number_format($stockSede, 2) }}
                            </td>
                        @endforeach
                        <td class="px-6 py-4 text-right font-mono font-bold text-indigo-700">
                            {{ number_format($totalRow, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($sucursales) + 3 }}" class="px-6 py-8 text-center text-gray-500">
                            No se encontraron productos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">
            {{ $productos->links() }}
        </div>
    </div>
</div>
@endsection
