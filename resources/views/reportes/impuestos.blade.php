@extends('layouts.app')

@section('title', 'Reporte de Impuestos e IVA')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Balance Fiscal de Impuestos (IVA)"
        subtitle="Liquidación estimada de IVA generado en ventas vs IVA descontable en compras de inventario."
        :breadcrumbs="[
            ['label' => 'Reportes', 'url' => route('reportes.index')],
            ['label' => 'Impuestos']
        ]"
    />

    <!-- Filtros -->
    <div class="card p-4">
        <form method="GET" action="{{ route('reportes.impuestos') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="label-base">Desde</label>
                <input type="date" name="fecha_desde" value="{{ $fechaDesde }}" class="input-base text-xs">
            </div>
            <div>
                <label class="label-base">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}" class="input-base text-xs">
            </div>
            <div>
                <label class="label-base">Sucursal</label>
                <select name="sucursal_id" class="input-select text-xs">
                    <option value="">Todas</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ $sucursalId == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary flex-1">Filtrar</button>
                <a href="{{ route('reportes.impuestos') }}" class="btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Balance Fiscal Card (Clean Corporate Dark) -->
    <div class="bg-slate-900 border border-slate-800 p-6 sm:p-8 rounded-xl text-white shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold bg-slate-800 text-slate-300 border border-slate-700 mb-2">
                {{ $saldoIvaNeto >= 0 ? 'IVA a Pagar Estimado' : 'Saldo a Favor Estimado' }}
            </div>
            <div class="text-3xl sm:text-4xl font-bold font-mono tracking-tight mt-1 tabular-nums">
                ${{ number_format(abs($saldoIvaNeto), 2) }}
            </div>
            <p class="text-xs text-slate-400 mt-2">
                Resultado de restar el IVA Descontable (Compras) al IVA Generado (Ventas).
            </p>
        </div>
        <div class="grid grid-cols-2 gap-4 text-center">
            <div class="bg-slate-800/80 border border-slate-700 p-4 rounded-lg">
                <span class="text-[10px] uppercase text-slate-400 font-semibold tracking-wider">IVA Generado</span>
                <div class="text-lg font-bold font-mono text-amber-400 mt-1 tabular-nums">${{ number_format($totalIvaGenerado, 2) }}</div>
            </div>
            <div class="bg-slate-800/80 border border-slate-700 p-4 rounded-lg">
                <span class="text-[10px] uppercase text-slate-400 font-semibold tracking-wider">IVA Descontable</span>
                <div class="text-lg font-bold font-mono text-emerald-400 mt-1 tabular-nums">${{ number_format($totalIvaDescontable, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Comparativa Ventas vs Compras -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Tarjeta Ventas -->
        <div class="card p-6 space-y-4">
            <h3 class="font-semibold text-slate-900 text-sm flex items-center gap-2">
                <span class="p-1.5 rounded bg-brand-50 text-brand-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </span>
                Operaciones de Venta (Generado)
            </h3>
            <div class="space-y-3 pt-2 text-xs">
                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                    <span class="text-slate-600">Base Gravable Neta (Subtotal)</span>
                    <span class="font-semibold text-slate-900 font-mono tabular-nums">${{ number_format($totalVentasNeto, 2) }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                    <span class="text-slate-600">Impuesto IVA Generado</span>
                    <span class="font-semibold text-amber-700 font-mono tabular-nums">${{ number_format($totalIvaGenerado, 2) }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="font-semibold text-slate-900">Total Facturado</span>
                    <span class="font-bold text-slate-900 font-mono text-sm tabular-nums">${{ number_format($totalVentas, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Tarjeta Compras -->
        <div class="card p-6 space-y-4">
            <h3 class="font-semibold text-slate-900 text-sm flex items-center gap-2">
                <span class="p-1.5 rounded bg-slate-100 text-slate-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </span>
                Operaciones de Compra (Descontable)
            </h3>
            <div class="space-y-3 pt-2 text-xs">
                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                    <span class="text-slate-600">Base Gravable Compras</span>
                    <span class="font-semibold text-slate-900 font-mono tabular-nums">${{ number_format($totalComprasNeto, 2) }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-slate-100">
                    <span class="text-slate-600">IVA Descontable Acreditado</span>
                    <span class="font-semibold text-emerald-700 font-mono tabular-nums">${{ number_format($totalIvaDescontable, 2) }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="font-semibold text-slate-900">Total Compras</span>
                    <span class="font-bold text-slate-900 font-mono text-sm tabular-nums">${{ number_format($totalCompras, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
