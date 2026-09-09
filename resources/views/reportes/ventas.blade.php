@extends('layouts.app')

@section('title', 'Reporte de Ventas')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <a href="{{ route('reportes.index') }}" class="hover:text-indigo-600 transition">Reportes</a>
                <span>&rsaquo;</span>
                <span class="text-indigo-600">Ventas</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Reporte Detallado de Ventas</h1>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('reportes.ventas.imprimir', request()->all()) }}" target="_blank" class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold transition shadow-sm">
                <svg class="h-4 w-4 mr-2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir / PDF
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('reportes.ventas') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="{{ $fechaDesde }}" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Sucursal</label>
                <select name="sucursal_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todas las Sedes</option>
                    @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}" {{ $sucursalId == $suc->id ? 'selected' : '' }}>{{ $suc->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Cajero / Vendedor</label>
                <select name="usuario_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos los Usuarios</option>
                    @foreach($usuarios as $usr)
                        <option value="{{ $usr->id }}" {{ $usuarioId == $usr->id ? 'selected' : '' }}>{{ $usr->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Tipo de Pago</label>
                <select name="tipo_pago" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos</option>
                    <option value="CONTADO" {{ $tipoPago === 'CONTADO' ? 'selected' : '' }}>Contado</option>
                    <option value="CREDITO" {{ $tipoPago === 'CREDITO' ? 'selected' : '' }}>Crédito</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-sm">
                    Filtrar
                </button>
                <a href="{{ route('reportes.ventas') }}" class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl border border-slate-200 transition">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- KPIs Resumen -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Ventas Netas</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">${{ number_format($totalNeto, 2) }}</div>
            <span class="text-xs text-emerald-600 font-semibold">{{ $cantidadVentas }} ventas completadas</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ticket Promedio</span>
            <div class="text-2xl font-extrabold text-indigo-600 mt-1">${{ number_format($ticketPromedio, 2) }}</div>
            <span class="text-xs text-slate-500">Por comprobante emitido</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">IVA Generado</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">${{ number_format($totalImpuestos, 2) }}</div>
            <span class="text-xs text-slate-500">Impuestos recaudados</span>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Descuentos Aplicados</span>
            <div class="text-2xl font-extrabold text-rose-600 mt-1">${{ number_format($totalDescuentos, 2) }}</div>
            <span class="text-xs text-slate-500">En el periodo seleccionado</span>
        </div>
    </div>

    <!-- Tabla Detallada -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Comprobante</th>
                        <th class="py-3.5 px-4">Fecha</th>
                        <th class="py-3.5 px-4">Cliente</th>
                        <th class="py-3.5 px-4">Sucursal</th>
                        <th class="py-3.5 px-4">Pago</th>
                        <th class="py-3.5 px-4 text-right">Subtotal</th>
                        <th class="py-3.5 px-4 text-right">IVA</th>
                        <th class="py-3.5 px-4 text-right">Total</th>
                        <th class="py-3.5 px-4 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($ventas as $v)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            <a href="{{ route('ventas.show', $v) }}" class="text-indigo-600 hover:underline">
                                {{ $v->numero_venta }}
                            </a>
                        </td>
                        <td class="py-3 px-4 text-xs text-slate-500">{{ $v->fecha->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4">{{ $v->cliente?->razon_social ?? 'Consumidor Final' }}</td>
                        <td class="py-3 px-4 text-xs">{{ $v->sucursal?->nombre }}</td>
                        <td class="py-3 px-4 text-xs">
                            <span class="px-2 py-0.5 rounded-md font-semibold {{ $v->tipo_pago->value === 'CONTADO' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                {{ $v->tipo_pago->value }} ({{ $v->metodo_pago }})
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right font-medium">${{ number_format($v->subtotal, 2) }}</td>
                        <td class="py-3 px-4 text-right text-xs text-slate-500">${{ number_format($v->impuesto, 2) }}</td>
                        <td class="py-3 px-4 text-right font-extrabold text-slate-900">${{ number_format($v->total, 2) }}</td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $v->estado->value === 'COMPLETADA' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $v->estado->value }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-400">No se encontraron ventas para los filtros seleccionados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $ventas->links() }}
        </div>
    </div>
</div>
@endsection
