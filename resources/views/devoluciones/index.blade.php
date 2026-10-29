@extends('layouts.app')

@section('title', 'Historial de Devoluciones')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <x-page-header
        title="Devoluciones de Ventas"
        subtitle="Control de garantías, devoluciones totales y parciales con reingreso automático al kardex.">
        <x-slot:actions>
            <a href="{{ route('ventas.index') }}" class="btn-secondary">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Ver Ventas para Devolver
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-kpi-card
            label="Devuelto Hoy"
            value="${{ number_format($totalDevueltoHoy, 2) }}"
            context="Monto reembolsado en el día"
            variant="warning"
            icon="currency-dollar"
        />

        <x-kpi-card
            label="Total Devoluciones Registradas"
            value="{{ number_format($conteoTotal) }}"
            context="Operaciones históricas de devolución"
            icon="check"
        />
    </div>

    <!-- Filtros -->
    <x-filter-bar
        :action="route('devoluciones.index')"
        :has-active-filters="request()->hasAny(['search', 'fecha_desde', 'fecha_hasta'])"
        :clear-url="route('devoluciones.index')"
    >
        <div class="sm:col-span-12 lg:col-span-6 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por número de devolución o venta..."
                   class="input pl-9">
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                   class="input" title="Fecha Desde">
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                   class="input" title="Fecha Hasta">
        </div>
    </x-filter-bar>

    <!-- Tabla de Devoluciones -->
    <div class="@container card p-0 overflow-hidden">
        <div class="hidden lg:block overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Comprobante / Venta</th>
                        <th class="hidden @4xl:table-cell">Fecha</th>
                        <th>Cliente</th>
                        <th class="hidden @5xl:table-cell">Tipo Devolución</th>
                        <th class="hidden @5xl:table-cell">Reintegro</th>
                        <th class="text-right">Total Devuelto</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devoluciones as $dev)
                    <tr>
                        <td>
                            <span class="font-mono font-semibold text-slate-900 text-xs block">{{ $dev->numero_devolucion }}</span>
                            <a href="{{ route('ventas.show', $dev->venta) }}" class="text-[11px] text-brand-600 hover:text-brand-800 font-mono">
                                Venta #{{ $dev->venta->numero_venta }}
                            </a>
                            <div class="@4xl:hidden text-[11px] text-slate-500 font-mono">{{ $dev->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="hidden @4xl:table-cell text-xs text-slate-600">
                            <span class="font-mono">{{ $dev->created_at->format('d/m/Y H:i') }}</span>
                            <div class="text-[10px] text-slate-400">Por: {{ $dev->usuario->name }}</div>
                        </td>
                        <td class="text-xs font-semibold text-slate-800">
                            {{ $dev->venta->cliente?->razon_social ?? 'Consumidor Final' }}
                        </td>
                        <td class="hidden @5xl:table-cell">
                            <span class="badge {{ $dev->tipo_devolucion === App\Enums\TipoDevolucion::TOTAL ? 'badge-danger' : 'badge-warning' }}">
                                {{ $dev->tipo_devolucion->label() }}
                            </span>
                        </td>
                        <td class="hidden @5xl:table-cell">
                            <span class="badge badge-neutral">
                                {{ $dev->tipo_reintegro->label() }}
                            </span>
                        </td>
                        <td class="text-right font-mono font-semibold text-slate-900 text-xs tabular-nums">
                            ${{ number_format($dev->total, 2) }}
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end space-x-1.5">
                                <a href="{{ route('devoluciones.show', $dev) }}" class="btn-secondary py-1 px-2.5 text-xs">
                                    Detalle
                                </a>
                                <a href="{{ route('devoluciones.comprobante', $dev) }}" target="_blank"
                                   class="p-1.5 text-slate-500 hover:text-brand-600 hover:bg-slate-100 rounded transition"
                                   title="Imprimir Comprobante">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <div class="empty-state">
                                <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                                </svg>
                                <p class="empty-state-title">No se han registrado devoluciones</p>
                                <p class="empty-state-desc">Las operaciones de garantía o cancelación de ítems aparecerán en este historial.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vista Móvil y Tablet -->
        <div class="lg:hidden divide-y divide-slate-100">
            @forelse($devoluciones as $dev)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-mono font-semibold text-slate-900 text-sm">{{ $dev->numero_devolucion }}</div>
                        <a href="{{ route('ventas.show', $dev->venta) }}" class="text-[11px] text-brand-600 hover:text-brand-800 font-mono">
                            Venta #{{ $dev->venta->numero_venta }}
                        </a>
                    </div>
                    <span class="badge {{ $dev->tipo_devolucion === App\Enums\TipoDevolucion::TOTAL ? 'badge-danger' : 'badge-warning' }}">
                        {{ $dev->tipo_devolucion->label() }}
                    </span>
                </div>

                <div class="text-xs text-slate-600 space-y-1">
                    <div><span class="text-slate-400">Cliente:</span> <span class="font-semibold text-slate-800">{{ $dev->venta->cliente?->razon_social ?? 'Consumidor Final' }}</span></div>
                    <div><span class="text-slate-400">Fecha:</span> <span class="font-mono">{{ $dev->created_at->format('d/m/Y H:i') }}</span> &bull; {{ $dev->usuario->name }}</div>
                    <div><span class="text-slate-400">Reintegro:</span> {{ $dev->tipo_reintegro->label() }}</div>
                </div>

                <div class="flex items-center justify-between gap-3 pt-2 border-t border-slate-100">
                    <div>
                        <span class="text-xs text-slate-400">Total devuelto:</span>
                        <span class="font-mono font-semibold text-slate-900 text-sm ml-1 tabular-nums">${{ number_format($dev->total, 2) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('devoluciones.comprobante', $dev) }}" target="_blank"
                           class="p-1.5 text-slate-500 hover:text-brand-600 hover:bg-slate-100 rounded transition"
                           title="Imprimir Comprobante">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                        </a>
                        <a href="{{ route('devoluciones.show', $dev) }}" class="btn-secondary py-1 px-2.5 text-xs">
                            Detalle &rarr;
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-xs text-slate-500">
                No se han registrado devoluciones.
            </div>
            @endforelse
        </div>

        @if($devoluciones->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $devoluciones->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
