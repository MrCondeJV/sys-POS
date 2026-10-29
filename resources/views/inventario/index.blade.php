@extends('layouts.app')

@section('title', 'Control de Inventario y Kardex — POS Comercial')

@section('content')
<div class="space-y-6 max-w-[1680px] mx-auto overflow-x-hidden">
    <!-- Header de la Vista -->
    <x-page-header
        title="Control de Inventario"
        subtitle="Existencias físicas en tiempo real, valoración contable y Kardex inmutable por sucursal."
        :breadcrumbs="[
            ['label' => 'Inventario & Productos'],
            ['label' => 'Control de Stock']
        ]"
    >
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-2">
                <x-button variant="outline" size="sm" href="{{ route('inventario.traslado.create') }}">
                    <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Trasladar
                </x-button>

                <x-button variant="primary" size="sm" href="{{ route('inventario.ajuste.create') }}">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Nuevo Ajuste
                </x-button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- Banner KPI de Métricas Operacionales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            label="Valoración a Costo"
            :value="'$' . number_format($valorCosto, 0, ',', '.')"
            subtext="Inversión actual en existencias"
            icon="coins"
        />

        <x-kpi-card
            label="Valor Estimado PVP"
            :value="'$' . number_format($valorPvp, 0, ',', '.')"
            status="info"
            subtext="Potencial de venta total"
            icon="trending-up"
        />

        <x-kpi-card
            label="Artículos en Inventario"
            :value="number_format($totalItems)"
            subtext="Registros sucursal / producto"
            icon="cash"
        />

        <x-kpi-card
            label="Bajo Stock"
            :value="$totalBajoStock"
            :status="$totalBajoStock > 0 ? 'danger' : 'neutral'"
            subtext="Requieren reposición urgente"
            icon="filter"
        />
    </div>

    <!-- Barra de Filtros -->
    <x-filter-bar
        :action="route('inventario.index')"
        :has-active-filters="!empty($term) || !empty($sucursalId) || !empty($categoriaId) || $estadoStock !== 'todos'"
        :clear-url="route('inventario.index')"
    >
        <div class="sm:col-span-12 lg:col-span-4">
            <label class="block text-xs font-semibold text-text-secondary uppercase mb-1">Buscar Producto</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-text-muted">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </span>
                <input type="text" name="buscar" value="{{ $term }}" placeholder="Buscar por nombre o código..."
                    class="input pl-9">
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <label class="block text-xs font-semibold text-text-secondary uppercase mb-1">Sucursal</label>
            <select name="sucursal_id" onchange="this.form.submit()" class="input">
                <option value="">Todas las Sucursales</option>
                @foreach($sucursales as $s)
                <option value="{{ $s->id }}" {{ $sucursalId === $s->id ? 'selected' : '' }}>
                    {{ $s->nombre }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <label class="block text-xs font-semibold text-text-secondary uppercase mb-1">Categoría</label>
            <select name="categoria_id" onchange="this.form.submit()" class="input">
                <option value="">Todas las Categorías</option>
                @foreach($categorias as $cat)
                <option value="{{ $cat->id }}" {{ $categoriaId === $cat->id ? 'selected' : '' }}>
                    {{ $cat->nombre }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-6 lg:col-span-2">
            <label class="block text-xs font-semibold text-text-secondary uppercase mb-1">Condición Stock</label>
            <select name="estado_stock" onchange="this.form.submit()" class="input">
                <option value="todos" {{ $estadoStock === 'todos' ? 'selected' : '' }}>Todo el Stock</option>
                <option value="disponible" {{ $estadoStock === 'disponible' ? 'selected' : '' }}>Stock Normal</option>
                <option value="bajo_stock" {{ $estadoStock === 'bajo_stock' ? 'selected' : '' }}>Bajo Stock</option>
                <option value="agotado" {{ $estadoStock === 'agotado' ? 'selected' : '' }}>Agotados</option>
            </select>
        </div>
    </x-filter-bar>

    <!-- Tabla de Existencias -->
    <div class="space-y-4">
        <div class="hidden lg:block">
            <x-table>
                <x-slot:thead>
                    <tr>
                        <th class="px-5 py-3 text-left uppercase">Producto</th>
                        <th class="hidden @5xl:table-cell px-4 py-3 text-left uppercase">Sucursal</th>
                        <th class="hidden @6xl:table-cell px-4 py-3 text-left uppercase">Categoría / Marca</th>
                        <th class="px-4 py-3 text-right uppercase">Existencias</th>
                        <th class="px-4 py-3 text-center uppercase">Nivel de Stock</th>
                        <th class="hidden @4xl:table-cell px-4 py-3 text-right uppercase">Costo / PVP</th>
                        <th class="px-4 py-3 text-right uppercase">Valor Total</th>
                        <th class="px-5 py-3 text-right uppercase">Acciones</th>
                    </tr>
                </x-slot:thead>

                @forelse($inventarios as $inv)
                @php
                    $prod = $inv->producto;
                    $stock = (float) $inv->stock;
                    $stockMin = (float) $inv->stock_minimo;
                    $valorTotalItem = $stock * (float) ($prod?->precio_compra ?? 0);
                @endphp
                <tr class="hover:bg-slate-50/70 transition">
                    <!-- Producto -->
                    <td class="px-5 py-3">
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center">
                                @if($prod?->imagen_url)
                                <img src="{{ $prod->imagen_url }}" alt="{{ $prod->nombre }}" class="h-full w-full object-cover">
                                @else
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                @if($prod)
                                <a href="{{ route('inventario.kardex', $prod->id) }}" class="font-semibold text-slate-900 hover:text-brand-600 transition line-clamp-2 text-xs" title="{{ $prod->nombre }}">
                                    {{ $prod->nombre }}
                                </a>
                                @else
                                <span class="font-semibold text-slate-400 italic text-xs">Producto no disponible</span>
                                @endif
                                <div class="flex flex-wrap items-center gap-x-2 text-[11px] text-slate-400 mt-0.5 font-mono">
                                    @if($prod?->codigo)
                                    <span>SKU: {{ $prod->codigo }}</span>
                                    @endif
                                    @if($prod?->codigo_barras)
                                    <span>&bull; {{ $prod->codigo_barras }}</span>
                                    @endif
                                </div>
                                <div class="@5xl:hidden text-[11px] text-slate-500">{{ $inv->sucursal?->nombre ?? 'Sin sucursal' }}</div>
                            </div>
                        </div>
                    </td>

                    <!-- Sucursal -->
                    <td class="hidden @5xl:table-cell px-4 py-3 whitespace-nowrap text-xs text-slate-700">
                        {{ $inv->sucursal?->nombre ?? 'Sin sucursal' }}
                    </td>

                    <!-- Categoría / Marca -->
                    <td class="hidden @6xl:table-cell px-4 py-3 text-xs">
                        <div class="text-slate-900 font-medium">{{ $prod?->categoria?->nombre ?? '—' }}</div>
                        <div class="text-[11px] text-slate-400">{{ $prod?->marca?->nombre ?? '' }}</div>
                    </td>

                    <!-- Existencias -->
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <div class="font-bold text-slate-900 text-xs tabular-nums">
                            {{ number_format($stock, 2) }}
                            <span class="text-[10px] text-slate-500 font-normal">{{ $prod?->unidadMedida?->codigo ?? 'UND' }}</span>
                        </div>
                        <div class="text-[10px] text-slate-400 tabular-nums">Mín: {{ number_format($stockMin, 2) }}</div>
                    </td>

                    <!-- Nivel de Stock -->
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        @if($stock <= 0)
                            <x-badge status="danger" size="sm">Agotado</x-badge>
                        @elseif($stock <= $stockMin)
                            <x-badge status="warning" size="sm" :dot="true">Bajo Stock</x-badge>
                        @else
                            <x-badge status="success" size="sm">Óptimo</x-badge>
                        @endif
                    </td>

                    <!-- Costo / PVP -->
                    <td class="hidden @4xl:table-cell px-4 py-3 text-right whitespace-nowrap text-xs tabular-nums">
                        <div class="text-slate-600 font-medium">${{ number_format($prod?->precio_compra ?? 0, 0, ',', '.') }}</div>
                        <div class="text-slate-900 font-semibold">${{ number_format($prod?->precio_venta ?? 0, 0, ',', '.') }}</div>
                    </td>

                    <!-- Valor Total -->
                    <td class="px-4 py-3 text-right whitespace-nowrap text-xs font-bold text-slate-900 tabular-nums">
                        ${{ number_format($valorTotalItem, 0, ',', '.') }}
                    </td>

                    <!-- Acciones -->
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <div class="flex items-center justify-end space-x-1.5">
                            @if($prod)
                            <x-button variant="outline" size="sm" href="{{ route('inventario.kardex', $prod->id) }}">
                                Kardex
                            </x-button>
                            <x-button variant="secondary" size="sm" href="{{ route('inventario.ajuste.create', ['producto_id' => $prod->id, 'sucursal_id' => $inv->sucursal_id]) }}">
                                Ajustar
                            </x-button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center">
                        <x-empty-state
                            title="No se encontraron existencias"
                            description="No hay artículos registrados con los filtros aplicados."
                        />
                    </td>
                </tr>
                @endforelse

                @if($inventarios->hasPages())
                <x-slot:pagination>
                    {{ $inventarios->links() }}
                </x-slot:pagination>
                @endif
            </x-table>
        </div>

        <!-- Vista Móvil -->
        <div class="lg:hidden bg-white rounded-xl border border-slate-200/80 shadow-xs divide-y divide-slate-100 overflow-hidden">
            @forelse($inventarios as $inv)
            @php
                $prod = $inv->producto;
                $stock = (float) $inv->stock;
                $stockMin = (float) $inv->stock_minimo;
            @endphp
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold text-slate-900 text-sm truncate">{{ $prod?->nombre ?? 'Producto no disponible' }}</div>
                        <div class="text-xs text-slate-500 font-mono mt-0.5">
                            SKU: {{ $prod?->codigo ?? '—' }} &bull; {{ $inv->sucursal?->nombre ?? 'Sin sucursal' }}
                        </div>
                    </div>
                    @if($stock <= 0)
                        <x-badge status="danger" size="sm">Agotado</x-badge>
                    @elseif($stock <= $stockMin)
                        <x-badge status="warning" size="sm" :dot="true">Bajo Stock</x-badge>
                    @else
                        <x-badge status="success" size="sm">Óptimo</x-badge>
                    @endif
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[10px] uppercase font-semibold">Stock Actual</span>
                        <span class="font-bold text-slate-900 text-base tabular-nums">
                            {{ number_format($stock, 2) }} {{ $prod?->unidadMedida?->codigo ?? 'UND' }}
                        </span>
                    </div>

                    <div class="flex items-center space-x-2">
                        @if($prod)
                        <x-button variant="outline" size="sm" href="{{ route('inventario.kardex', $prod->id) }}">
                            Kardex
                        </x-button>
                        <x-button variant="secondary" size="sm" href="{{ route('inventario.ajuste.create', ['producto_id' => $prod->id, 'sucursal_id' => $inv->sucursal_id]) }}">
                            Ajustar
                        </x-button>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-xs text-slate-500">
                No hay existencias registradas.
            </div>
            @endforelse

            @if($inventarios->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $inventarios->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
