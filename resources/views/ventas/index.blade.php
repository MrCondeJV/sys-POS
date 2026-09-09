@extends('layouts.app')

@section('title', 'Ventas Realizadas')

@section('content')
<div class="max-w-[1680px] mx-auto space-y-6">

    <!-- Encabezado de la Sección -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Ventas & Clientes</span>
                <span>/</span>
                <span class="text-slate-800">Ventas Realizadas</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Historial de Ventas</h1>
            <p class="text-sm text-slate-500">Consulta y auditoría de transacciones comerciales, facturación y tickets emitidos.</p>
        </div>
    </div>

    <!-- KPIs del Módulo -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider block">Ventas de Hoy</span>
                <div class="text-2xl font-black text-emerald-600 mt-1">${{ number_format($totalVentasHoy, 2) }}</div>
                <div class="text-[11px] text-emerald-700 mt-0.5">{{ $conteoVentasHoy }} transacciones hoy</div>
            </div>
            <div class="h-12 w-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider block">Ventas del Mes</span>
                <div class="text-2xl font-black text-indigo-600 mt-1">${{ number_format($totalVentasMes, 2) }}</div>
                <div class="text-[11px] text-indigo-700 mt-0.5">Acumulado mes en curso</div>
            </div>
            <div class="h-12 w-12 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Ticket Promedio Hoy</span>
                <div class="text-2xl font-black text-slate-800 mt-1">${{ number_format($ticketPromedio, 2) }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">Por transacción realizada</div>
            </div>
            <div class="h-12 w-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Registros Filtrados</span>
                <div class="text-2xl font-black text-slate-800 mt-1">{{ $ventas->total() }}</div>
                <div class="text-[11px] text-slate-500 mt-0.5">En la vista actual</div>
            </div>
            <div class="h-12 w-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5">
        <form action="{{ route('ventas.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <!-- Buscar término -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Buscar Venta / Cliente</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="N° Venta o Razón Social..."
                        class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                </div>
            </div>

            <!-- Estado -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Estado</label>
                <select name="estado" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    <option value="">Todos los estados</option>
                    <option value="COMPLETADA" {{ request('estado') === 'COMPLETADA' ? 'selected' : '' }}>Completadas</option>
                    <option value="ANULADA" {{ request('estado') === 'ANULADA' ? 'selected' : '' }}>Anuladas</option>
                </select>
            </div>

            <!-- Tipo Pago -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tipo de Pago</label>
                <select name="tipo_pago" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    <option value="">Todos</option>
                    <option value="CONTADO" {{ request('tipo_pago') === 'CONTADO' ? 'selected' : '' }}>Contado</option>
                    <option value="CREDITO" {{ request('tipo_pago') === 'CREDITO' ? 'selected' : '' }}>Crédito</option>
                </select>
            </div>

            <!-- Fecha Desde -->
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
            </div>

            <!-- Botones -->
            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-sm">
                    Filtrar
                </button>
                @if(request()->hasAny(['buscar', 'estado', 'tipo_pago', 'fecha_desde', 'fecha_hasta']))
                    <a href="{{ route('ventas.index') }}" class="p-2 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50" title="Limpiar">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Ventas -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-xs">
                <thead class="bg-slate-50/80 font-bold text-slate-700">
                    <tr>
                        <th class="px-6 py-3.5 text-left uppercase">N° Venta / Comprobante</th>
                        <th class="px-6 py-3.5 text-left uppercase">Fecha & Hora</th>
                        <th class="px-6 py-3.5 text-left uppercase">Cliente</th>
                        <th class="px-6 py-3.5 text-left uppercase">Vendedor</th>
                        <th class="px-6 py-3.5 text-center uppercase">Condición / Medio</th>
                        <th class="px-6 py-3.5 text-right uppercase">Total Venta</th>
                        <th class="px-6 py-3.5 text-center uppercase">Estado</th>
                        <th class="px-6 py-3.5 text-right uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($ventas as $v)
                        <tr class="hover:bg-slate-50/70 transition {{ $v->isAnulada() ? 'opacity-60 bg-rose-50/20' : '' }}">
                            <td class="px-6 py-3.5">
                                <div class="font-bold text-slate-900 font-mono">{{ $v->numero_venta }}</div>
                                <div class="text-[10px] text-slate-500 font-semibold">{{ $v->tipo_comprobante->label() }}</div>
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap text-slate-700">
                                {{ $v->fecha->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-3.5">
                                @if($v->cliente)
                                    <div class="font-bold text-slate-800">{{ $v->cliente->razon_social }}</div>
                                    <div class="text-[10px] text-slate-500">{{ $v->cliente->numero_documento }}</div>
                                @else
                                    <span class="text-slate-500 italic">Consumidor Final</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-slate-700 whitespace-nowrap">
                                {{ $v->usuario->name }}
                            </td>
                            <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $v->tipo_pago->badgeClasses() }}">
                                    {{ $v->tipo_pago->label() }}
                                </span>
                                <div class="text-[10px] text-slate-500 mt-0.5">{{ $v->metodo_pago }}</div>
                            </td>
                            <td class="px-6 py-3.5 text-right font-black text-slate-900 text-sm whitespace-nowrap">
                                ${{ number_format($v->total, 2) }}
                            </td>
                            <td class="px-6 py-3.5 text-center whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $v->estado->badgeClasses() }}">
                                    {{ $v->estado->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('ventas.ticket', $v) }}" target="_blank"
                                        class="px-2.5 py-1 rounded-lg border border-slate-200 text-[11px] font-bold text-slate-600 hover:bg-slate-50 shadow-sm">
                                        Ticket
                                    </a>
                                    <a href="{{ route('ventas.show', $v) }}"
                                        class="px-2.5 py-1 rounded-lg bg-indigo-50 border border-indigo-100 text-[11px] font-bold text-indigo-700 hover:bg-indigo-100">
                                        Detalle &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                No se encontraron ventas con los criterios seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ventas->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $ventas->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
