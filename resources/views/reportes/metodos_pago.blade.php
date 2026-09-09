@extends('layouts.app')

@section('title', 'Reporte de Métodos de Pago')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <a href="{{ route('reportes.index') }}" class="hover:text-indigo-600 transition">Reportes</a>
                <span>&rsaquo;</span>
                <span class="text-indigo-600">Métodos de Pago</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Desglose de Recaudos por Medio de Pago</h1>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('reportes.metodos-pago') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
                <a href="{{ route('reportes.metodos-pago') }}" class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl border border-slate-200 transition">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Total General -->
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 p-6 rounded-3xl text-white shadow-xl flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-indigo-300 uppercase tracking-wider">Total Recaudado en Periodo</span>
            <div class="text-3xl font-extrabold mt-1">${{ number_format($totalGeneral, 2) }}</div>
        </div>
        <div class="p-3 bg-white/10 rounded-2xl">
            <svg class="h-8 w-8 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
        </div>
    </div>

    <!-- Desglose por Medio -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($desglose as $item)
        @php
            $pct = $totalGeneral > 0 ? round(($item->monto_total / $totalGeneral) * 100, 1) : 0;
        @endphp
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700">
                        {{ $item->tipo_pago }} - {{ $item->metodo_pago }}
                    </span>
                    <span class="text-xs font-extrabold text-slate-400">{{ $pct }}%</span>
                </div>
                <div class="text-2xl font-extrabold text-slate-900">${{ number_format($item->monto_total, 2) }}</div>
                <p class="text-xs text-slate-500 mt-1">{{ $item->cantidad_transacciones }} transacciones</p>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2 mt-4 overflow-hidden">
                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $pct }}%"></div>
            </div>
        </div>
        @empty
        <div class="col-span-3 bg-white p-8 rounded-3xl border border-slate-200 text-center text-slate-400">
            No hay recaudos registrados en el periodo seleccionado.
        </div>
        @endforelse
    </div>
</div>
@endsection
