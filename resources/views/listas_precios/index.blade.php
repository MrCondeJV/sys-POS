@extends('layouts.app')

@section('title', 'Listas de Precios')

@section('content')
<div class="max-w-[1680px] mx-auto space-y-6">

    <!-- Header Section -->
    <x-page-header
        title="Listas de Precios"
        subtitle="Administra tarifas personalizadas por cliente, mayoristas, distribuidores y promociones especiales."
        :breadcrumbs="[
            ['label' => 'Catálogo', 'url' => route('productos.index')],
            ['label' => 'Listas de Precios']
        ]"
    >
        <x-slot:actions>
            @can('create', App\Models\ListaPrecio::class)
            <x-button variant="primary" size="sm" href="{{ route('listas-precios.create') }}">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Lista de Precios
            </x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    <!-- Filtros y Búsqueda -->
    <x-filter-bar
        :action="route('listas-precios.index')"
        :has-active-filters="request()->hasAny(['search', 'estado'])"
        :clear-url="route('listas-precios.index')"
    >
        <div class="sm:col-span-2">
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Buscar Lista</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o código..."
                    class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg text-xs placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Estado</label>
            <select name="estado" class="w-full py-2 px-3 text-xs border border-slate-300 rounded-lg text-slate-700 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                <option value="">Todos los Estados</option>
                <option value="ACTIVO" {{ request('estado') === 'ACTIVO' ? 'selected' : '' }}>Activas</option>
                <option value="INACTIVO" {{ request('estado') === 'INACTIVO' ? 'selected' : '' }}>Inactivas</option>
            </select>
        </div>
    </x-filter-bar>

    <!-- Tabla de Listas de Precios -->
    <x-table>
        <x-slot:thead>
            <tr>
                <th class="px-5 py-3 text-left uppercase">Lista / Código</th>
                <th class="px-4 py-3 text-left uppercase">Tipo de Ajuste</th>
                <th class="px-4 py-3 text-center uppercase">Ajuste Base</th>
                <th class="px-4 py-3 text-center uppercase">Precios Específicos</th>
                <th class="px-4 py-3 text-center uppercase">Clientes</th>
                <th class="px-4 py-3 text-center uppercase">Estado</th>
                <th class="px-5 py-3 text-right uppercase">Acciones</th>
            </tr>
        </x-slot:thead>

        @forelse($listas as $lista)
        <tr class="hover:bg-slate-50/70 transition">
            <td class="px-5 py-3">
                <div class="flex items-center space-x-2">
                    <span class="font-semibold text-slate-900 text-xs">{{ $lista->nombre }}</span>
                    @if($lista->es_predeterminada)
                        <x-badge status="success" size="sm">Predeterminada</x-badge>
                    @endif
                </div>
                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                    {{ $lista->codigo ? 'CÓD: ' . $lista->codigo : 'Sin código' }}
                    @if($lista->descripcion)
                        &bull; <span class="text-slate-500 font-sans">{{ Str::limit($lista->descripcion, 40) }}</span>
                    @endif
                </div>
            </td>
            <td class="px-4 py-3 text-xs text-slate-700">
                <x-badge status="neutral" size="sm">{{ $lista->tipo_ajuste->label() }}</x-badge>
            </td>
            <td class="px-4 py-3 text-center text-xs tabular-nums">
                @if($lista->tipo_ajuste === App\Enums\TipoAjusteListaPrecio::PORCENTAJE_DESCUENTO)
                    <span class="text-emerald-600 font-bold">&minus;{{ $lista->porcentaje_defecto }}%</span>
                @elseif($lista->tipo_ajuste === App\Enums\TipoAjusteListaPrecio::PORCENTAJE_AUMENTO)
                    <span class="text-brand-600 font-bold">+{{ $lista->porcentaje_defecto }}%</span>
                @else
                    <span class="text-slate-400">Individual</span>
                @endif
            </td>
            <td class="px-4 py-3 text-center text-xs text-slate-700 tabular-nums">
                {{ $lista->precios_count ?? ($lista->precios?->count() ?? 0) }} ítems
            </td>
            <td class="px-4 py-3 text-center text-xs text-slate-700 tabular-nums">
                {{ $lista->clientes_count ?? ($lista->clientes?->count() ?? 0) }} clientes
            </td>
            <td class="px-4 py-3 text-center whitespace-nowrap">
                <x-status-badge :status="$lista->estado" size="sm" />
            </td>
            <td class="px-5 py-3 text-right whitespace-nowrap">
                <div class="flex items-center justify-end space-x-1.5">
                    <x-button variant="outline" size="sm" href="{{ route('listas-precios.show', $lista) }}">
                        Precios
                    </x-button>
                    @can('update', $lista)
                    <x-button variant="secondary" size="sm" href="{{ route('listas-precios.edit', $lista) }}">
                        Editar
                    </x-button>
                    @endcan
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" class="px-5 py-12 text-center">
                <x-empty-state
                    title="No se encontraron listas de precios"
                    description="Crea una lista para definir precios preferenciales por cliente o volumen."
                />
            </td>
        </tr>
        @endforelse

        @if($listas->hasPages())
        <x-slot:pagination>
            {{ $listas->links() }}
        </x-slot:pagination>
        @endif
    </x-table>
</div>
@endsection
