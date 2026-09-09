@extends('layouts.app')

@section('title', 'Reporte de Impuestos e IVA')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <a href="{{ route('reportes.index') }}" class="hover:text-indigo-600 transition">Reportes</a>
                <span>&rsaquo;</span>
                <span class="text-indigo-600">Impuestos</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Balance Fiscal de Impuestos (IVA)</h1>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('reportes.impuestos') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-sm">Filtrar</button>
                <a href="{{ route('reportes.impuestos') }}" class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl border border-slate-200 transition">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Balance Fiscal Card -->
    <div class="bg-gradient-to-r {{ $saldoIvaNeto >= 0 ? 'from-slate-900 to-indigo-950' : 'from-slate-900 to-emerald-950' }} p-6 sm:p-8 rounded-3xl text-white shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white/10 mb-2">
                {{ $saldoIvaNeto >= 0 ? 'IVA a Pagar Estimado' : 'Saldo a Favor Estimado' }}
            </div>
            <div class="text-3xl sm:text-4xl font-extrabold mt-1">
                ${{ number_format(abs($saldoIvaNeto), 2) }}
            </div>
            <p class="text-sm text-slate-300 mt-2">
                Resultado de restar el IVA Descontable (Compras) al IVA Generado (Ventas).
            </p>
        </div>
        <div class="grid grid-cols-2 gap-4 text-center">
            <div class="bg-white/10 p-4 rounded-2xl">
                <span class="text-xs uppercase text-slate-300 font-bold">IVA Generado</span>
                <div class="text-xl font-extrabold text-amber-300 mt-1">${{ number_format($totalIvaGenerado, 2) }}</div>
            </div>
            <div class="bg-white/10 p-4 rounded-2xl">
                <span class="text-xs uppercase text-slate-300 font-bold">IVA Descontable</span>
                <div class="text-xl font-extrabold text-emerald-300 mt-1">${{ number_format($totalIvaDescontable, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Comparativa Ventas vs Compras -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Tarjeta Ventas -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-extrabold text-slate-900 text-lg flex items-center gap-2">
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </span>
                Operaciones de Venta (Generado)
            </h3>
            <div class="space-y-3 pt-2">
                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                    <span class="text-sm text-slate-500">Base Gravable Neta (Subtotal)</span>
                    <span class="text-sm font-bold text-slate-900">${{ number_format($totalVentasNeto, 2) }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                    <span class="text-sm text-slate-500">Impuesto IVA Generado</span>
                    <span class="text-sm font-extrabold text-amber-600">${{ number_format($totalIvaGenerado, 2) }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm font-bold text-slate-900">Total Facturado</span>
                    <span class="text-base font-extrabold text-slate-900">${{ number_format($totalVentas, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Tarjeta Compras -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <h3 class="font-extrabold text-slate-900 text-lg flex items-center gap-2">
                <span class="p-2 rounded-xl bg-cyan-50 text-cyan-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </span>
                Operaciones de Compra (Descontable)
            </h3>
            <div class="space-y-3 pt-2">
                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                    <span class="text-sm text-slate-500">Base Gravable Compras</span>
                    <span class="text-sm font-bold text-slate-900">${{ number_format($totalComprasNeto, 2) }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                    <span class="text-sm text-slate-500">IVA Descontable Acreditado</span>
                    <span class="text-sm font-extrabold text-emerald-600">${{ number_format($totalIvaDescontable, 2) }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm font-bold text-slate-900">Total Compras</span>
                    <span class="text-base font-extrabold text-slate-900">${{ number_format($totalCompras, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
