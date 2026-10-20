@extends('layouts.app')

@section('title', 'Catálogo de Productos')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6" x-data="{
    deleteModalOpen: false,
    deleteFormAction: '',
    deleteProductName: '',
    confirmDelete(action, name) {
        this.deleteFormAction = action;
        this.deleteProductName = name;
        this.deleteModalOpen = true;
    }
}">
    <!-- Header Principal -->
    <x-page-header
        title="Catálogo de Productos"
        subtitle="Artículos, códigos, precios comerciales e inventario de {{ auth()->user()->empresa?->nombre_comercial }}."
        :breadcrumbs="[
            ['label' => 'Inventario & Productos'],
            ['label' => 'Catálogo General']
        ]"
    >
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200/80 shadow-xs text-xs">
                    <span class="text-slate-500 font-medium">Total:</span>
                    <span class="font-bold text-slate-900 tabular-nums">{{ $totalProductos ?? $productos->total() }}</span>
                    @if(($totalBajoStock ?? 0) > 0)
                        <span class="text-slate-300">|</span>
                        <x-badge status="danger" size="sm" :dot="true">
                            {{ $totalBajoStock }} Bajo Stock
                        </x-badge>
                    @endif
                </div>

                <x-button variant="outline" size="sm" href="{{ route('catalogos.index') }}">
                    <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    Categorías y Marcas
                </x-button>

                <x-button variant="primary" size="sm" href="{{ route('productos.create') }}">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Producto
                </x-button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- Barra de Búsqueda y Filtros Rápidos -->
    <x-filter-bar
        :action="route('productos.index')"
        :has-active-filters="!empty($term) || !empty($categoriaId) || !empty($bajoStock)"
        :clear-url="route('productos.index')"
    >
        <div class="sm:col-span-12 lg:col-span-5">
            <label class="block text-xs font-semibold text-text-secondary uppercase mb-1">Buscar Producto</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-text-muted">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" name="buscar" value="{{ $term ?? '' }}"
                    class="input pl-9"
                    placeholder="Buscar por nombre, SKU o código de barras...">
            </div>
        </div>

        <div class="sm:col-span-6 lg:col-span-3">
            <label class="block text-xs font-semibold text-text-secondary uppercase mb-1">Categoría</label>
            <select name="categoria_id" onchange="this.form.submit()" class="input">
                <option value="">Todas las Categorías ({{ $categorias->count() }})</option>
                @foreach($categorias as $cat)
                <option value="{{ $cat->id }}" {{ ($categoriaId ?? '') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->nombre }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-6 lg:col-span-2 flex items-center h-10">
            <label class="inline-flex items-center space-x-2 cursor-pointer text-xs font-semibold text-text-primary select-none">
                <input type="checkbox" name="bajo_stock" value="1" {{ ($bajoStock ?? false) ? 'checked' : '' }} onchange="this.form.submit()"
                    class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-border-strong rounded">
                <span>Solo Bajo Stock</span>
            </label>
        </div>
    </x-filter-bar>

    <!-- Lista de Productos -->
    <div class="space-y-4">
        <!-- Vista Desktop -->
        <div class="hidden lg:block">
            <x-table>
                <x-slot:thead>
                    <tr>
                        <th class="px-5 py-3 text-left uppercase">Producto</th>
                        <th class="px-4 py-3 text-left uppercase">Códigos</th>
                        <th class="px-4 py-3 text-left uppercase">Categoría</th>
                        <th class="px-4 py-3 text-left uppercase">Marca</th>
                        <th class="px-4 py-3 text-right uppercase">Precio Venta</th>
                        <th class="px-4 py-3 text-right uppercase">P. Mayorista</th>
                        <th class="px-4 py-3 text-center uppercase">Stock</th>
                        <th class="px-4 py-3 text-center uppercase">Estado</th>
                        <th class="px-5 py-3 text-right uppercase">Acciones</th>
                    </tr>
                </x-slot:thead>

                @forelse($productos as $p)
                <tr class="hover:bg-slate-50/70 transition">
                    <!-- 1. Producto -->
                    <td class="px-5 py-3">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="h-10 w-10 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                @if($p->imagen_path)
                                    <img src="{{ $p->imagen_url }}" alt="{{ $p->nombre }}" class="h-full w-full object-cover">
                                @else
                                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-semibold text-slate-900 text-xs leading-snug truncate">{{ $p->nombre }}</div>
                                @if($p->descripcion)
                                <div class="text-[11px] text-slate-400 truncate max-w-xs mt-0.5">{{ $p->descripcion }}</div>
                                @else
                                <div class="text-[11px] text-slate-400 mt-0.5">Unidad: {{ $p->unidadMedida?->nombre ?? 'Unidad' }}</div>
                                @endif
                            </div>
                        </div>
                    </td>

                    <!-- 2. Códigos -->
                    <td class="px-4 py-3 font-mono text-xs">
                        <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 font-semibold text-slate-700 border border-slate-200 text-[11px]">
                            {{ $p->codigo ?? '—' }}
                        </span>
                        @if($p->codigo_barras)
                        <div class="text-slate-500 mt-0.5 flex items-center gap-1 text-[11px] tabular-nums">
                            {{ $p->codigo_barras }}
                        </div>
                        @endif
                    </td>

                    <!-- 3. Categoría -->
                    <td class="px-4 py-3 text-xs">
                        @if($p->categoria)
                        <x-badge status="neutral" size="sm">{{ $p->categoria->nombre }}</x-badge>
                        @else
                        <span class="text-slate-400">—</span>
                        @endif
                    </td>

                    <!-- 4. Marca -->
                    <td class="px-4 py-3 text-xs">
                        @if($p->marca)
                        <span class="text-slate-700 font-medium">{{ $p->marca->nombre }}</span>
                        @else
                        <span class="text-slate-400">—</span>
                        @endif
                    </td>

                    <!-- 5. Precios -->
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <div class="font-bold text-slate-900 text-xs tabular-nums">
                            ${{ number_format($p->precio_venta, 0, ',', '.') }}
                        </div>
                        <div class="text-[10px] text-slate-400 tabular-nums">
                            IVA {{ (float)$p->iva }}% (${{ number_format($p->calcularPrecioConIva(), 0, ',', '.') }})
                        </div>
                    </td>

                    <!-- 6. Precio Mayorista -->
                    <td class="px-4 py-3 text-right whitespace-nowrap text-xs tabular-nums">
                        @if($p->precio_mayorista)
                        <span class="font-semibold text-slate-800">
                            ${{ number_format($p->precio_mayorista, 0, ',', '.') }}
                        </span>
                        @else
                        <span class="text-slate-300">—</span>
                        @endif
                    </td>

                    <!-- 7. Stock -->
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        @if($p->tieneBajoStock())
                            <x-badge status="danger" size="sm" :dot="true">
                                {{ number_format($p->stock, 0) }} {{ $p->unidadMedida?->codigo ?? 'UND' }}
                            </x-badge>
                        @else
                            <x-badge status="success" size="sm">
                                {{ number_format($p->stock, 0) }} {{ $p->unidadMedida?->codigo ?? 'UND' }}
                            </x-badge>
                        @endif
                        <div class="text-[10px] text-slate-400 mt-0.5 tabular-nums">Mín: {{ number_format($p->stock_minimo, 0) }}</div>
                    </td>

                    <!-- 8. Estado -->
                    <td class="px-4 py-3 text-center whitespace-nowrap">
                        @if($p->estado === \App\Enums\EstadoGeneral::ACTIVO)
                            <x-badge status="success" size="sm">Activo</x-badge>
                        @else
                            <x-badge status="neutral" size="sm">Inactivo</x-badge>
                        @endif
                    </td>

                    <!-- 9. Acciones -->
                    <td class="px-5 py-3 text-right whitespace-nowrap">
                        <div class="inline-flex items-center justify-end space-x-1.5">
                            <x-button variant="secondary" size="sm" href="{{ route('productos.edit', $p) }}">
                                Editar
                            </x-button>
                            <x-icon-button
                                type="button"
                                variant="danger"
                                icon="trash"
                                label="Eliminar Producto"
                                @click="confirmDelete('{{ route('productos.destroy', $p) }}', '{{ addslashes($p->nombre) }}')"
                            />
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-5 py-12 text-center">
                        <x-empty-state
                            title="No se encontraron productos"
                            description="No hay artículos en el catálogo con los filtros seleccionados."
                        />
                    </td>
                </tr>
                @endforelse

                @if($productos->hasPages())
                <x-slot:pagination>
                    {{ $productos->links() }}
                </x-slot:pagination>
                @endif
            </x-table>
        </div>

        <!-- Vista Móvil y Tablet -->
        <div class="lg:hidden bg-white rounded-xl border border-slate-200/80 shadow-xs divide-y divide-slate-100 overflow-hidden">
            @forelse($productos as $p)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start space-x-3 min-w-0">
                        <div class="h-10 w-10 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                            @if($p->imagen_path)
                                <img src="{{ $p->imagen_url }}" alt="{{ $p->nombre }}" class="h-full w-full object-cover">
                            @else
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-slate-900 text-sm leading-snug truncate">{{ $p->nombre }}</div>
                            <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                SKU: {{ $p->codigo ?? '—' }} | EAN: {{ $p->codigo_barras ?? '—' }}
                            </div>
                        </div>
                    </div>
                    @if($p->tieneBajoStock())
                        <x-badge status="danger" size="sm" :dot="true">
                            {{ number_format($p->stock, 0) }} {{ $p->unidadMedida?->codigo ?? 'UND' }}
                        </x-badge>
                    @else
                        <x-badge status="success" size="sm">
                            {{ number_format($p->stock, 0) }} {{ $p->unidadMedida?->codigo ?? 'UND' }}
                        </x-badge>
                    @endif
                </div>

                <div class="flex flex-wrap gap-1.5 text-xs">
                    <x-badge status="neutral" size="sm">{{ $p->categoria?->nombre ?? 'Sin Categoría' }}</x-badge>
                    @if($p->marca)
                        <span class="text-xs text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md font-medium">{{ $p->marca->nombre }}</span>
                    @endif
                </div>

                <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase font-semibold block">Precio Venta</span>
                        <span class="font-bold text-slate-900 text-base tabular-nums">
                            ${{ number_format($p->precio_venta, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <x-button variant="secondary" size="sm" href="{{ route('productos.edit', $p) }}">
                            Editar
                        </x-button>
                        <x-button variant="danger" size="sm" type="button" @click="confirmDelete('{{ route('productos.destroy', $p) }}', '{{ addslashes($p->nombre) }}')">
                            Eliminar
                        </x-button>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-xs text-slate-500">
                No hay productos en el catálogo.
            </div>
            @endforelse

            @if($productos->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $productos->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Formulario Oculto y Modal de Eliminación -->
    <form id="deleteProductForm" :action="deleteFormAction" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <x-modal-confirm
        show="deleteModalOpen"
        title="Eliminar Producto"
        message="¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer."
        confirm-text="Sí, eliminar"
        cancel-text="Cancelar"
        confirm-variant="danger"
        @confirm="document.getElementById('deleteProductForm').submit()"
        @cancel="deleteModalOpen = false"
    />
</div>
@endsection
