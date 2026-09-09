@extends('layouts.app')

@section('title', 'Valoración de Inventario')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <a href="{{ route('reportes.index') }}" class="hover:text-indigo-600 transition">Reportes</a>
                <span>&rsaquo;</span>
                <span class="text-indigo-600">Inventario</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Valoración de Existencias e Inventario</h1>
        </div>
        <div>
            <a href="{{ route('reportes.inventario.imprimir', request()->all()) }}" target="_blank" class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold transition shadow-sm">
                Imprimir / PDF
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('reportes.inventario') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Sucursal</label>
                <select name="sucursal_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
                    <option value="">Todas las Sedes</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ $sucursalId == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Categoría</label>
                <select name="categoria_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
                    <option value="">Todas</option>
                    @foreach($categorias as $c)
                        <option value="{{ $c->id }}" {{ $categoriaId == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Estado de Stock</label>
                <select name="filtro_stock" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
                    <option value="todos" {{ $filtroStock === 'todos' ? 'selected' : '' }}>Todos</option>
                    <option value="bajo_stock" {{ $filtroStock === 'bajo_stock' ? 'selected' : '' }}>Bajo Stock Mínimo</option>
                    <option value="agotados" {{ $filtroStock === 'agotados' ? 'selected' : '' }}>Agotados (0 o menos)</option>
                    <option value="disponibles" {{ $filtroStock === 'disponibles' ? 'selected' : '' }}>Con Disponibilidad</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-sm">Filtrar</button>
                <a href="{{ route('reportes.inventario') }}" class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl border border-slate-200 transition">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- KPIs Valoración -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Unidades en Stock</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalUnidades, 0) }}</div>
            <span class="text-xs text-slate-500">En almacén físico</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Costo Valorizado Total</span>
            <div class="text-2xl font-extrabold text-amber-600 mt-1">${{ number_format($totalCostoValorizado, 2) }}</div>
            <span class="text-xs text-slate-500">Inversión actual en bodega</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Valor Proyectado Venta</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">${{ number_format($totalValorVenta, 2) }}</div>
            <span class="text-xs text-slate-500">A precio catálogo actual</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Utilidad Potencial</span>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1">${{ number_format($utilidadPotencial, 2) }}</div>
            <span class="text-xs text-emerald-600 font-semibold">Margen proyectado de venta</span>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Producto</th>
                        <th class="py-3.5 px-4">Sucursal</th>
                        <th class="py-3.5 px-4 text-center">Stock Actual</th>
                        <th class="py-3.5 px-4 text-right">Costo Unit.</th>
                        <th class="py-3.5 px-4 text-right">Costo Total</th>
                        <th class="py-3.5 px-4 text-right">Precio Venta</th>
                        <th class="py-3.5 px-4 text-right">Valor Venta Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($inventarios as $inv)
                    @php
                        $costoTotalItem = (float) $inv->stock * (float) $inv->producto->precio_costo;
                        $ventaTotalItem = (float) $inv->stock * (float) $inv->producto->precio_venta;
                        $esBajo = (float) $inv->stock <= (float) $inv->producto->stock_minimo;
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $inv->producto->nombre }}</div>
                            <div class="text-xs text-slate-400">SKU: {{ $inv->producto->codigo }} | {{ $inv->producto->categoria?->nombre }}</div>
                        </td>
                        <td class="py-3 px-4 text-xs">{{ $inv->sucursal?->nombre }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-extrabold {{ $esBajo ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ number_format($inv->stock, 2) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">${{ number_format($inv->producto->precio_costo, 2) }}</td>
                        <td class="py-3 px-4 text-right font-semibold text-amber-700">${{ number_format($costoTotalItem, 2) }}</td>
                        <td class="py-3 px-4 text-right">${{ number_format($inv->producto->precio_venta, 2) }}</td>
                        <td class="py-3 px-4 text-right font-extrabold text-slate-900">${{ number_format($ventaTotalItem, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">No hay registros de inventario.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $inventarios->links() }}
        </div>
    </div>
</div>
@endsection
