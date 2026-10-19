@extends('layouts.app')

@section('title', 'Traslados entre Sucursales')

@section('content')
<div class="space-y-6 max-w-[1680px] mx-auto">
    <!-- Header -->
    <x-page-header
        title="Traslados entre Sucursales"
        subtitle="Gestión, despacho y recepción formal de mercancía entre sedes de la empresa."
        :breadcrumbs="[
            ['label' => 'Inventario', 'url' => route('inventario.index')],
            ['label' => 'Traslados']
        ]"
    >
        <x-slot:actions>
            <div class="flex items-center space-x-2">
                <x-button variant="outline" size="sm" href="{{ route('reportes.multisucursal') }}">
                    Ver Consolidado
                </x-button>
                <x-button variant="primary" size="sm" href="{{ route('traslados.create') }}">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Traslado
                </x-button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- Filtros -->
    <x-filter-bar
        :action="route('traslados.index')"
        :has-active-filters="request()->hasAny(['origen_id', 'destino_id', 'estado'])"
        :clear-url="route('traslados.index')"
    >
        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Sucursal Origen</label>
            <select name="origen_id" class="w-full py-2 px-3 text-xs border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                <option value="">Todas las sedes</option>
                @foreach($sucursales as $s)
                    <option value="{{ $s->id }}" {{ request('origen_id') == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Sucursal Destino</label>
            <select name="destino_id" class="w-full py-2 px-3 text-xs border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                <option value="">Todas las sedes</option>
                @foreach($sucursales as $s)
                    <option value="{{ $s->id }}" {{ request('destino_id') == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Estado</label>
            <select name="estado" class="w-full py-2 px-3 text-xs border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                <option value="">Todos los estados</option>
                @foreach($estados as $e)
                    <option value="{{ $e->value }}" {{ request('estado') == $e->value ? 'selected' : '' }}>{{ $e->label() }}</option>
                @endforeach
            </select>
        </div>
    </x-filter-bar>

    <!-- Tabla -->
    <x-table>
        <x-slot:thead>
            <tr>
                <th class="px-5 py-3 text-left uppercase">Consecutivo</th>
                <th class="px-4 py-3 text-left uppercase">Ruta</th>
                <th class="px-4 py-3 text-left uppercase">Motivo</th>
                <th class="px-4 py-3 text-center uppercase">Estado</th>
                <th class="px-4 py-3 text-left uppercase">Fecha Envío</th>
                <th class="px-5 py-3 text-right uppercase">Acción</th>
            </tr>
        </x-slot:thead>

        @forelse($traslados as $t)
            <tr class="hover:bg-slate-50/70 transition">
                <td class="px-5 py-3 font-mono font-bold text-slate-900 text-xs">{{ $t->consecutivo }}</td>
                <td class="px-4 py-3 text-xs text-slate-700">
                    <span class="font-medium text-slate-900">{{ $t->sucursalOrigen?->nombre ?? 'Sin sucursal' }}</span>
                    <span class="text-slate-400 mx-1">&rarr;</span>
                    <span class="font-medium text-slate-900">{{ $t->sucursalDestino?->nombre ?? 'Sin sucursal' }}</span>
                </td>
                <td class="px-4 py-3 text-xs text-slate-600 max-w-xs truncate">{{ Str::limit($t->motivo, 40) }}</td>
                <td class="px-4 py-3 text-center whitespace-nowrap">
                    <x-status-badge :status="$t->estado" size="sm" />
                </td>
                <td class="px-4 py-3 text-xs text-slate-500 whitespace-nowrap tabular-nums">
                    {{ $t->fecha_envio?->format('d/m/Y H:i') ?? '—' }}
                </td>
                <td class="px-5 py-3 text-right whitespace-nowrap">
                    <x-button variant="secondary" size="sm" href="{{ route('traslados.show', $t) }}">
                        Detalle &rarr;
                    </x-button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-5 py-12 text-center">
                    <x-empty-state
                        title="No se encontraron traslados"
                        description="No hay registros de traslados entre sucursales con los filtros seleccionados."
                    />
                </td>
            </tr>
        @endforelse

        @if($traslados->hasPages())
        <x-slot:pagination>
            {{ $traslados->links() }}
        </x-slot:pagination>
        @endif
    </x-table>
</div>
@endsection
