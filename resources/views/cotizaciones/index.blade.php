@extends('layouts.app')

@section('title', 'Cotizaciones')

@section('content')
<div class="space-y-6">

    <x-page-header
        title="Cotizaciones"
        description="Propuestas comerciales para clientes. No afectan inventario ni caja hasta convertirse en venta."
        :breadcrumbs="[
            ['label' => 'Cotizaciones']
        ]">
        <x-slot:actions>
            @can('create', App\Models\Cotizacion::class)
                <x-button variant="primary" href="{{ route('cotizaciones.create') }}">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva Cotización
                </x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <x-flash-messages />

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-kpi-card
            title="Cotizaciones Pendientes"
            :value="number_format($totalPendientes)"
            subtitle="Esperando respuesta del cliente"
            icon="calendar"
        />
        <x-kpi-card
            title="Valor Cotizado Pendiente"
            :value="'$' . number_format($montoPendiente, 0, ',', '.')"
            subtitle="Suma de cotizaciones abiertas"
            icon="coins"
        />
        <x-kpi-card
            title="Convertidas este Mes"
            :value="number_format($convertidasMes)"
            :subtitle="'Ventas cerradas en ' . now()->translatedFormat('F')"
            icon="check"
        />
    </div>

    <x-filter-bar :action="route('cotizaciones.index')" :clear-url="route('cotizaciones.index')" :has-active-filters="!empty($term) || !empty($estado)">
        <div class="sm:col-span-8 lg:col-span-8 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" name="buscar" value="{{ $term ?? '' }}" placeholder="Buscar por número o cliente..." class="input pl-9">
        </div>
        <div class="sm:col-span-4 lg:col-span-2">
            <select name="estado" class="input">
                <option value="">Todos los estados</option>
                @foreach(App\Enums\EstadoCotizacion::cases() as $e)
                    <option value="{{ $e->value }}" @selected(($estado ?? '') === $e->value)>{{ $e->label() }}</option>
                @endforeach
            </select>
        </div>
    </x-filter-bar>

    <x-table>
        <x-slot:head>
            <tr>
                <th class="px-4 py-3 text-left">Cotización</th>
                <th class="px-4 py-3 text-left">Cliente</th>
                <th class="hidden @4xl:table-cell px-4 py-3 text-left">Validez</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3 text-center">Estado</th>
                <th class="px-4 py-3 text-right">Acciones</th>
            </tr>
        </x-slot:head>

        @forelse($cotizaciones as $cot)
            <tr class="hover:bg-slate-50/60 transition">
                <td class="px-4 py-3">
                    <a href="{{ route('cotizaciones.show', $cot) }}" class="font-semibold text-brand-600 hover:text-brand-800 text-sm font-mono">{{ $cot->numero_cotizacion }}</a>
                    <div class="text-xs text-slate-500">{{ $cot->fecha->format('d/m/Y') }} · {{ $cot->usuario?->name ?? '—' }}</div>
                </td>
                <td class="px-4 py-3">
                    <div class="text-sm font-semibold text-slate-900 line-clamp-1">{{ $cot->cliente?->razon_social ?? 'Consumidor Final' }}</div>
                    @if($cot->cliente)
                        <div class="text-xs text-slate-500 font-mono">{{ $cot->cliente->numero_documento }}</div>
                    @endif
                </td>
                <td class="hidden @4xl:table-cell px-4 py-3 whitespace-nowrap text-xs">
                    @if($cot->fecha_validez)
                        <span class="{{ $cot->estaVencida() ? 'text-danger-text font-semibold' : 'text-slate-600' }}">
                            {{ $cot->fecha_validez->format('d/m/Y') }}{{ $cot->estaVencida() ? ' · vencida' : '' }}
                        </span>
                    @else
                        <span class="text-slate-400">Sin fecha límite</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap font-semibold font-mono text-slate-900">${{ number_format($cot->total, 0, ',', '.') }}</td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <x-status-badge :status="$cot->estado->value" />
                    @if($cot->venta)
                        <a href="{{ route('ventas.show', $cot->venta) }}" class="block text-[11px] text-brand-600 hover:underline mt-0.5 font-mono">{{ $cot->venta->numero_venta }}</a>
                    @endif
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    <div class="flex items-center justify-end space-x-1.5">
                        <x-icon-button href="{{ route('cotizaciones.show', $cot) }}" variant="secondary" icon="eye" title="Ver cotización" />
                        <x-icon-button href="{{ route('cotizaciones.pdf', $cot) }}" variant="secondary" icon="document" title="Descargar PDF" />
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-8">
                    <x-empty-state
                        title="No hay cotizaciones"
                        description="Crea una cotización para enviarle precios a un cliente sin afectar el inventario."
                    />
                </td>
            </tr>
        @endforelse
    </x-table>

    @if($cotizaciones->hasPages())
        <div>{{ $cotizaciones->links() }}</div>
    @endif
</div>
@endsection
