@extends('layouts.app')

@section('title', 'Reporte de Métodos de Pago')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Desglose de Recaudos por Medio de Pago"
        subtitle="Distribución porcentual y montos capturados en caja por canal de pago."
        :breadcrumbs="[
            ['label' => 'Reportes', 'url' => route('reportes.index')],
            ['label' => 'Métodos de Pago']
        ]"
    />

    <!-- Filtros -->
    <div class="card p-4">
        <form method="GET" action="{{ route('reportes.metodos-pago') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
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
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1">Filtrar</button>
                <a href="{{ route('reportes.metodos-pago') }}" class="btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Total General (Corporate Dark Card) -->
    <div class="bg-slate-900 border border-slate-800 p-6 rounded-lg text-white shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Recaudado en el Periodo</span>
            <div class="text-3xl font-bold font-mono tracking-tight mt-1 tabular-nums">${{ number_format($totalGeneral, 2) }}</div>
        </div>
        <div class="p-3 bg-slate-800 rounded-lg text-slate-300 border border-slate-700">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
        </div>
    </div>

    <!-- Desglose por Medio -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($desglose as $item)
        @php
            $pct = $totalGeneral > 0 ? round(($item->monto_total / $totalGeneral) * 100, 1) : 0;
        @endphp
        <div class="card p-5 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="badge badge-brand">
                        {{ $item->tipo_pago }} - {{ $item->metodo_pago }}
                    </span>
                    <span class="text-xs font-semibold text-slate-500 font-mono">{{ $pct }}%</span>
                </div>
                <div class="text-2xl font-bold font-mono text-slate-900 tabular-nums">${{ number_format($item->monto_total, 2) }}</div>
                <p class="text-xs text-slate-500 mt-1 tabular-nums font-mono">{{ $item->cantidad_transacciones }} transacciones</p>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-4 overflow-hidden">
                <div class="bg-brand-600 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
            </div>
        </div>
        @empty
        <div class="col-span-3 card p-12 text-center">
            <div class="empty-state">
                <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <p class="empty-state-title">No hay recaudos registrados</p>
                <p class="empty-state-desc">No se encontraron cobros en el periodo o sucursal seleccionada.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
