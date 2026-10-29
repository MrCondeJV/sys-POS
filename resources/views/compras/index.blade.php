@extends('layouts.app')

@section('title', 'Historial de Compras')

@section('content')
<div class="space-y-6">
    <!-- Encabezado de la página -->
    <x-page-header
        title="Facturas de Compra de Mercancía"
        subtitle="Adquisición de inventario a proveedores, control de costos unitarios e ingreso directo a Kardex.">
        <x-slot:actions>
            <button type="button" onclick="window.print()" class="btn-secondary">
                <svg class="h-4 w-4 mr-2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Listado
            </button>
            <a href="{{ route('compras.create') }}" class="btn-primary">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Registrar Nueva Compra
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- KPIs del Módulo de Compras -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            label="Inversión Registrada"
            value="${{ number_format($montoTotalRegistrado, 2) }}"
            context="Total de compras activas"
            variant="success"
            icon="currency-dollar"
        />

        <x-kpi-card
            label="Facturas Registradas"
            value="{{ number_format($comprasRegistradas) }}"
            context="Con stock ingresado a sedes"
            badge="Activas"
            icon="credit-card"
        />

        <x-kpi-card
            label="Facturas Anuladas"
            value="{{ number_format($comprasAnuladas) }}"
            context="Mercancía revertida"
            badge="Revertidas"
            variant="danger"
            icon="alert"
        />

        <x-kpi-card
            label="Total Transacciones"
            value="{{ number_format($totalCompras) }}"
            context="Histórico acumulado"
            icon="check"
        />
    </div>

    <!-- Filtros de Búsqueda -->
    <x-filter-bar
        :action="route('compras.index')"
        :has-active-filters="!empty($term) || !empty($sucursalId) || !empty($proveedorId) || !empty($estado) || !empty($desde) || !empty($hasta)"
        :clear-url="route('compras.index')"
    >
        <div class="sm:col-span-12 lg:col-span-4 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" name="buscar" value="{{ $term }}"
                placeholder="Factura # o razón social..."
                class="input pl-9">
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <select name="sucursal_id" class="input">
                <option value="">Todas las Sedes</option>
                @foreach($sucursales as $s)
                    <option value="{{ $s->id }}" {{ $sucursalId == $s->id ? 'selected' : '' }}>
                        {{ $s->nombre }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <select name="proveedor_id" class="input">
                <option value="">Todos los Proveedores</option>
                @foreach($proveedores as $p)
                    <option value="{{ $p->id }}" {{ $proveedorId == $p->id ? 'selected' : '' }}>
                        {{ $p->razon_social }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <select name="estado" class="input">
                <option value="">Cualquier Estado</option>
                <option value="REGISTRADA" {{ $estado === 'REGISTRADA' ? 'selected' : '' }}>Registradas</option>
                <option value="ANULADA" {{ $estado === 'ANULADA' ? 'selected' : '' }}>Anuladas</option>
            </select>
        </div>
    </x-filter-bar>

    <!-- Lista de Compras: Desktop Table / Mobile Cards -->
    <div class="@container card p-0 overflow-hidden">
        <!-- Vista Desktop -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th class="hidden @4xl:table-cell">Fecha</th>
                        <th>Factura #</th>
                        <th>Proveedor</th>
                        <th class="hidden @5xl:table-cell">Sede Destino</th>
                        <th class="hidden @5xl:table-cell">Pago</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($compras as $c)
                    <tr>
                        <!-- Fecha -->
                        <td class="hidden @4xl:table-cell text-xs text-slate-600 font-mono">
                            {{ $c->fecha_emision->format('d/m/Y') }}
                        </td>

                        <!-- Factura # -->
                        <td class="font-mono font-semibold text-xs text-slate-900">
                            #{{ $c->numero_factura }}
                            <div class="@4xl:hidden font-normal text-[11px] text-slate-500">{{ $c->fecha_emision->format('d/m/Y') }}</div>
                        </td>

                        <!-- Proveedor -->
                        <td>
                            <div class="font-semibold text-slate-900 text-sm">{{ $c->proveedor?->razon_social ?? 'Proveedor Eliminado' }}</div>
                            <div class="text-xs text-slate-400 font-mono">
                                {{ $c->proveedor?->tipo_documento?->value }}: {{ $c->proveedor?->numero_documento }}
                            </div>
                        </td>

                        <!-- Sede Destino -->
                        <td class="hidden @5xl:table-cell text-xs text-slate-700">
                            {{ $c->sucursal?->nombre ?? 'Sede N/A' }}
                        </td>

                        <!-- Pago -->
                        <td class="hidden @5xl:table-cell">
                            <span class="badge {{ $c->tipo_pago?->value === 'CONTADO' ? 'badge-neutral' : 'badge-warning' }}">
                                {{ $c->tipo_pago?->value ?? 'CONTADO' }}
                            </span>
                        </td>

                        <!-- Total -->
                        <td class="text-right font-mono font-semibold text-sm text-slate-900 tabular-nums">
                            ${{ number_format($c->total, 2) }}
                        </td>

                        <!-- Estado -->
                        <td class="text-center">
                            @if($c->isRegistrada())
                                <span class="badge badge-success">Registrada</span>
                            @else
                                <span class="badge badge-danger">Anulada</span>
                            @endif
                        </td>

                        <!-- Acción -->
                        <td class="text-right">
                            <div class="flex items-center justify-end space-x-1.5">
                                <a href="{{ route('compras.show', $c) }}?print=1" target="_blank"
                                    class="p-1.5 text-slate-500 hover:text-brand-600 hover:bg-slate-100 rounded transition"
                                    title="Imprimir Comprobante Oficial">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                </a>
                                <a href="{{ route('compras.show', $c) }}"
                                    class="btn-secondary py-1 px-2.5 text-xs">
                                    Detalle
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12">
                            <div class="empty-state">
                                <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="empty-state-title">No se encontraron facturas de compra</p>
                                <p class="empty-state-desc">Registra facturas comerciales para reabastecer existencias de productos en tus sedes.</p>
                                <a href="{{ route('compras.create') }}" class="btn-primary mt-4">
                                    Registrar Factura
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vista Móvil / Tablet (Cards) -->
        <div class="block lg:hidden divide-y divide-slate-100">
            @forelse($compras as $c)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="font-mono font-semibold text-slate-900 text-sm">Factura #{{ $c->numero_factura }}</div>
                        <div class="text-xs text-slate-500 font-mono mt-0.5">
                            Fecha: {{ $c->fecha_emision->format('d/m/Y') }}
                        </div>
                    </div>
                    @if($c->isRegistrada())
                        <span class="badge badge-success">Registrada</span>
                    @else
                        <span class="badge badge-danger">Anulada</span>
                    @endif
                </div>

                <div class="text-xs text-slate-600 space-y-1">
                    <div><span class="text-slate-400">Proveedor:</span> <span class="font-semibold text-slate-800">{{ $c->proveedor?->razon_social ?? 'Eliminado' }}</span></div>
                    <div><span class="text-slate-400">Sede Destino:</span> {{ $c->sucursal?->nombre ?? 'N/A' }}</div>
                    <div><span class="text-slate-400">Tipo de Pago:</span> {{ $c->tipo_pago?->value ?? 'CONTADO' }}</div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <div>
                        <span class="text-xs text-slate-400">Total:</span>
                        <span class="font-mono font-semibold text-slate-900 text-sm ml-1 tabular-nums">${{ number_format($c->total, 2) }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('compras.show', $c) }}?print=1" target="_blank"
                            class="p-1 text-slate-500 hover:text-brand-600" title="Imprimir">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                        </a>
                        <a href="{{ route('compras.show', $c) }}" class="btn-secondary py-1 px-2 text-xs">
                            Detalle &rarr;
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-500 text-sm">
                No hay compras registradas en este período o filtro.
            </div>
            @endforelse
        </div>

        @if($compras->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $compras->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
