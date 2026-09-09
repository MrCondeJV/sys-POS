@extends('layouts.app')

@section('title', 'Reporte de Compras')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <a href="{{ route('reportes.index') }}" class="hover:text-indigo-600 transition">Reportes</a>
                <span>&rsaquo;</span>
                <span class="text-indigo-600">Compras</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Reporte de Compras a Proveedores</h1>
        </div>
        <div>
            <a href="{{ route('reportes.compras.imprimir', request()->all()) }}" target="_blank" class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold transition shadow-sm">
                Imprimir / PDF
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('reportes.compras') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
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
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Proveedor</label>
                <select name="proveedor_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
                    <option value="">Todos</option>
                    @foreach($proveedores as $p)
                        <option value="{{ $p->id }}" {{ $proveedorId == $p->id ? 'selected' : '' }}>{{ $p->razon_social }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-sm">Filtrar</button>
                <a href="{{ route('reportes.compras') }}" class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl border border-slate-200 transition">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Comprado</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">${{ number_format($totalComprado, 2) }}</div>
            <span class="text-xs text-emerald-600 font-semibold">{{ $totalComprasCount }} facturas registradas</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">IVA Descontable</span>
            <div class="text-2xl font-extrabold text-indigo-600 mt-1">${{ number_format($totalImpuestos, 2) }}</div>
            <span class="text-xs text-slate-500">Impuestos en compras</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Subtotal Sin IVA</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">${{ number_format($totalComprado - $totalImpuestos, 2) }}</div>
            <span class="text-xs text-slate-500">Base gravable global</span>
        </div>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Factura N°</th>
                        <th class="py-3.5 px-4">Fecha</th>
                        <th class="py-3.5 px-4">Proveedor</th>
                        <th class="py-3.5 px-4">Sucursal</th>
                        <th class="py-3.5 px-4 text-right">Subtotal</th>
                        <th class="py-3.5 px-4 text-right">IVA</th>
                        <th class="py-3.5 px-4 text-right">Total</th>
                        <th class="py-3.5 px-4 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($compras as $c)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-bold text-slate-900">{{ $c->numero_factura }}</td>
                        <td class="py-3 px-4 text-xs text-slate-500">{{ $c->fecha_emision->format('d/m/Y') }}</td>
                        <td class="py-3 px-4">{{ $c->proveedor?->razon_social }}</td>
                        <td class="py-3 px-4 text-xs">{{ $c->sucursal?->nombre }}</td>
                        <td class="py-3 px-4 text-right">${{ number_format($c->subtotal, 2) }}</td>
                        <td class="py-3 px-4 text-right text-xs">${{ number_format($c->impuestos, 2) }}</td>
                        <td class="py-3 px-4 text-right font-extrabold text-slate-900">${{ number_format($c->total, 2) }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $c->estado->value === 'REGISTRADA' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $c->estado->value }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400">No se encontraron compras.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $compras->links() }}
        </div>
    </div>
</div>
@endsection
