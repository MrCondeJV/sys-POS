@extends('layouts.app')

@section('title', 'Ventas Realizadas')

@section('content')
<div class="max-w-[1680px] mx-auto space-y-6">

    <!-- Encabezado de la Sección -->
    <x-page-header
        title="Historial de Ventas"
        subtitle="Consulta y auditoría de transacciones comerciales, facturación y tickets emitidos."
    >
        <x-slot:actions>
            @can('ventas.crear')
                <x-button variant="primary" href="{{ route('pos.index') }}">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Terminal POS
                </x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <!-- KPIs del Módulo -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            label="Ventas de Hoy"
            :value="'$' . number_format($totalVentasHoy, 2)"
            status="success"
            :subtext="$conteoVentasHoy . ' transacciones hoy'"
            icon="cash"
        />

        <x-kpi-card
            label="Ventas del Mes"
            :value="'$' . number_format($totalVentasMes, 2)"
            status="info"
            subtext="Acumulado mes en curso"
            icon="trending-up"
        />

        <x-kpi-card
            label="Ticket Promedio Hoy"
            :value="'$' . number_format($ticketPromedio, 2)"
            subtext="Por transacción realizada"
            icon="coins"
        />

        <x-kpi-card
            label="Registros Filtrados"
            :value="$ventas->total()"
            status="neutral"
            subtext="En la vista actual"
            icon="filter"
        />
    </div>

    <!-- Barra de Filtros -->
    <x-filter-bar
        :action="route('ventas.index')"
        :has-active-filters="request()->hasAny(['buscar', 'estado', 'tipo_pago', 'fecha_desde', 'fecha_hasta'])"
        :clear-url="route('ventas.index')"
    >
        <div class="sm:col-span-12 lg:col-span-4">
            <label class="block text-xs font-semibold text-text-secondary uppercase mb-1">Buscar Venta / Cliente</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-text-muted">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </span>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="N° Venta o Razón Social..."
                    class="input pl-9">
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <label class="block text-xs font-semibold text-text-secondary uppercase mb-1">Estado</label>
            <select name="estado" class="input">
                <option value="">Todos los estados</option>
                <option value="COMPLETADA" {{ request('estado') === 'COMPLETADA' ? 'selected' : '' }}>Completadas</option>
                <option value="ANULADA" {{ request('estado') === 'ANULADA' ? 'selected' : '' }}>Anuladas</option>
            </select>
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <label class="block text-xs font-semibold text-text-secondary uppercase mb-1">Tipo de Pago</label>
            <select name="tipo_pago" class="input">
                <option value="">Todos</option>
                <option value="CONTADO" {{ request('tipo_pago') === 'CONTADO' ? 'selected' : '' }}>Contado</option>
                <option value="CREDITO" {{ request('tipo_pago') === 'CREDITO' ? 'selected' : '' }}>Crédito</option>
            </select>
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <label class="block text-xs font-semibold text-text-secondary uppercase mb-1">Desde</label>
            <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                class="input">
        </div>
    </x-filter-bar>

    <!-- Tabla de Ventas -->
    <x-table>
        <x-slot:thead>
            <tr>
                <th class="px-5 py-3 text-left uppercase">N° Venta / Comprobante</th>
                <th class="px-5 py-3 text-left uppercase">Fecha & Hora</th>
                <th class="px-5 py-3 text-left uppercase">Cliente</th>
                <th class="px-5 py-3 text-left uppercase">Vendedor</th>
                <th class="px-5 py-3 text-center uppercase">Condición / Medio</th>
                <th class="px-5 py-3 text-right uppercase">Total Venta</th>
                <th class="px-5 py-3 text-center uppercase">Estado</th>
                <th class="px-5 py-3 text-right uppercase">Acciones</th>
            </tr>
        </x-slot:thead>

        @forelse($ventas as $v)
            <tr class="hover:bg-surface-muted/60 transition {{ $v->isAnulada() ? 'opacity-60 bg-danger-bg/20' : '' }}">
                <td class="px-5 py-3">
                    <div class="font-bold text-text-primary font-mono text-xs">{{ $v->numero_venta }}</div>
                    <div class="text-[11px] text-text-muted font-medium">{{ $v->tipo_comprobante->label() }}</div>
                </td>
                <td class="px-5 py-3 whitespace-nowrap text-text-secondary text-xs tabular-nums">
                    {{ $v->fecha->format('d/m/Y H:i') }}
                </td>
                <td class="px-5 py-3">
                    @if($v->cliente)
                        <div class="font-semibold text-text-primary text-xs">{{ $v->cliente->razon_social }}</div>
                        <div class="text-[11px] text-text-muted font-mono">{{ $v->cliente->numero_documento }}</div>
                    @else
                        <span class="text-text-muted italic text-xs">Consumidor Final</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-text-secondary text-xs whitespace-nowrap">
                    {{ $v->usuario->name }}
                </td>
                <td class="px-5 py-3 text-center whitespace-nowrap">
                    <x-badge :status="$v->tipo_pago->value === 'CONTADO' ? 'success' : 'warning'" size="sm">
                        {{ $v->tipo_pago->label() }}
                    </x-badge>
                    <div class="text-[11px] text-text-muted mt-0.5">{{ $v->metodo_pago }}</div>
                </td>
                <td class="px-5 py-3 text-right font-bold text-text-primary text-sm tabular-nums whitespace-nowrap">
                    ${{ number_format($v->total, 2) }}
                </td>
                <td class="px-5 py-3 text-center whitespace-nowrap">
                    <x-status-badge :status="$v->estado" size="sm" />
                </td>
                <td class="px-5 py-3 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end space-x-2">
                        <x-button variant="outline" size="sm" href="{{ route('ventas.ticket', $v) }}" target="_blank">
                            Ticket
                        </x-button>
                        <x-button variant="secondary" size="sm" href="{{ route('ventas.show', $v) }}">
                            Detalle &rarr;
                        </x-button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-5 py-12 text-center">
                    <x-empty-state
                        title="No se encontraron ventas"
                        description="No hay transacciones registradas con los criterios seleccionados."
                    />
                </td>
            </tr>
        @endforelse

        @if($ventas->hasPages())
            <x-slot:pagination>
                {{ $ventas->links() }}
            </x-slot:pagination>
        @endif
    </x-table>

</div>
@endsection
