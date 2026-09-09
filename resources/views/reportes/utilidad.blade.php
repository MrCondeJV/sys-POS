@extends('layouts.app')

@section('title', 'Reporte de Utilidad y Rentabilidad')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <a href="{{ route('reportes.index') }}" class="hover:text-indigo-600 transition">Reportes</a>
                <span>&rsaquo;</span>
                <span class="text-indigo-600">Utilidad y Rentabilidad</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Margen Bruto y Rentabilidad</h1>
        </div>
        <div>
            <a href="{{ route('reportes.utilidad.imprimir', request()->all()) }}" target="_blank" class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold transition shadow-sm">
                Imprimir / PDF
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('reportes.utilidad') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="{{ $fechaDesde }}" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Sucursal</label>
                <select name="sucursal_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
                    <option value="">Todas</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ $sucursalId == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Categoría</label>
                <select name="categoria_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
                    <option value="">Todas</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ $categoriaId == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-sm">Filtrar</button>
                <a href="{{ route('reportes.utilidad') }}" class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl border border-slate-200 transition">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ingresos Netos Ventas</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">${{ number_format($totalIngreso, 2) }}</div>
            <span class="text-xs text-slate-500">{{ number_format($totalUnidades, 0) }} artículos vendidos</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Costo Mercancía Vendida</span>
            <div class="text-2xl font-extrabold text-rose-600 mt-1">${{ number_format($totalCosto, 2) }}</div>
            <span class="text-xs text-slate-500">Costo histórico registrado</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Utilidad Bruta</span>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1">${{ number_format($totalUtilidad, 2) }}</div>
            <span class="text-xs text-emerald-600 font-semibold">Ganancia neta sobre costo</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">% Margen de Rentabilidad</span>
            <div class="text-2xl font-extrabold text-indigo-600 mt-1">{{ number_format($porcentajeMargen, 1) }}%</div>
            <span class="text-xs text-slate-500">Sobre el total de ventas</span>
        </div>
    </div>

    <!-- Tabla Rentabilidad por Producto -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="font-extrabold text-slate-900 text-base">Rentabilidad por Artículo</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Producto</th>
                        <th class="py-3.5 px-4 text-center">Unidades</th>
                        <th class="py-3.5 px-4 text-right">Ingreso Neto</th>
                        <th class="py-3.5 px-4 text-right">Costo Total</th>
                        <th class="py-3.5 px-4 text-right">Utilidad Bruta</th>
                        <th class="py-3.5 px-4 text-center">% Margen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rentabilidadProductos as $r)
                    @php
                        $pctProd = $r->ingreso_neto > 0 ? round(($r->utilidad_bruta / $r->ingreso_neto) * 100, 1) : 0;
                    @endphp
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $r->producto_nombre }}</div>
                            <div class="text-xs text-slate-400">SKU: {{ $r->producto_codigo }}</div>
                        </td>
                        <td class="py-3 px-4 text-center font-bold">{{ number_format($r->unidades_vendidas, 0) }}</td>
                        <td class="py-3 px-4 text-right font-medium">${{ number_format($r->ingreso_neto, 2) }}</td>
                        <td class="py-3 px-4 text-right text-rose-600">${{ number_format($r->costo_total, 2) }}</td>
                        <td class="py-3 px-4 text-right font-extrabold text-emerald-600">${{ number_format($r->utilidad_bruta, 2) }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-extrabold {{ $pctProd >= 20 ? 'bg-emerald-50 text-emerald-700' : ($pctProd > 0 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                {{ $pctProd }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">No hay ventas registradas en el periodo.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $rentabilidadProductos->links() }}
        </div>
    </div>
</div>
@endsection
