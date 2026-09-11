@extends('layouts.app')

@section('title', 'Historial de Devoluciones')

@section('content')
<div class="max-w-[1680px] mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
        <div class="flex items-center space-x-4">
            <div class="h-12 w-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-black shadow-md shadow-amber-500/20">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Devoluciones de Ventas</h1>
                <p class="text-xs text-slate-500 mt-0.5">Control de garantías, devoluciones totales y parciales con reingreso automático al kardex.</p>
            </div>
        </div>

        <a href="{{ route('ventas.index') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-sm font-semibold rounded-xl shadow-sm transition">
            <span>&larr; Ver Ventas para Devolver</span>
        </a>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Devuelto Hoy</span>
                <span class="text-2xl font-black text-slate-900 mt-1 block">${{ number_format($totalDevueltoHoy, 2) }}</span>
            </div>
            <div class="h-10 w-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                $
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Devoluciones Registradas</span>
                <span class="text-2xl font-black text-slate-900 mt-1 block">{{ $conteoTotal }}</span>
            </div>
            <div class="h-10 w-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                #
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white border border-slate-200 rounded-3xl p-4 shadow-sm">
        <form method="GET" action="{{ route('devoluciones.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por número de devolución o número de venta..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>
            <div>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                       class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>
            <div class="flex items-center space-x-2">
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                       class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm transition">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Tabla de Devoluciones -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Comprobante / Venta</th>
                        <th class="py-3.5 px-5">Fecha</th>
                        <th class="py-3.5 px-5">Cliente</th>
                        <th class="py-3.5 px-5">Tipo Devolución</th>
                        <th class="py-3.5 px-5">Reintegro</th>
                        <th class="py-3.5 px-5 text-right">Total Devuelto</th>
                        <th class="py-3.5 px-5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($devoluciones as $dev)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4">
                            <span class="font-black text-slate-900 text-sm block">{{ $dev->numero_devolucion }}</span>
                            <a href="{{ route('ventas.show', $dev->venta) }}" class="text-[11px] text-indigo-600 hover:underline font-mono">
                                Venta #{{ $dev->venta->numero_venta }}
                            </a>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">
                            {{ $dev->created_at->format('d/m/Y H:i') }}
                            <div class="text-[10px] text-slate-400">Por: {{ $dev->usuario->name }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-800">
                            {{ $dev->venta->cliente?->razon_social ?? 'Consumidor Final' }}
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black {{ $dev->tipo_devolucion === App\Enums\TipoDevolucion::TOTAL ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $dev->tipo_devolucion->label() }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                                {{ $dev->tipo_reintegro->label() }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-black text-slate-900 text-sm">
                            ${{ number_format($dev->total, 2) }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('devoluciones.show', $dev) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition text-[11px]">
                                    Ver Detalle
                                </a>
                                <a href="{{ route('devoluciones.comprobante', $dev) }}" target="_blank" class="p-1.5 text-indigo-600 hover:text-indigo-800 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            No se han registrado devoluciones en el período seleccionado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($devoluciones->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $devoluciones->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
