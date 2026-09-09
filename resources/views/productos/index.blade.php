@extends('layouts.app')

@section('title', 'Catálogo de Productos')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Catálogo de Productos</h1>
            <p class="text-sm text-slate-500 mt-1">
                Artículos, precios comerciales e inventario de {{ auth()->user()->empresa?->nombre_comercial }}.
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('catalogos.index') }}"
                class="inline-flex items-center px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-white transition">
                <svg class="h-4 w-4 mr-2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                Categorías y Marcas
            </a>
            <a href="{{ route('productos.create') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo Producto
            </a>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros Rápidos -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('productos.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <!-- Input Búsqueda -->
            <div class="sm:col-span-6 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="buscar" value="{{ $term ?? '' }}"
                    class="block w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                    placeholder="Buscar por nombre, código o código de barras (Enter)...">
            </div>

            <!-- Filtro Categoría -->
            <div class="sm:col-span-3">
                <select name="categoria_id" onchange="this.form.submit()"
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <option value="">Todas las Categorías</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ ($categoriaId ?? '') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Checkbox Bajo Stock -->
            <div class="sm:col-span-3 flex items-center justify-between sm:justify-end space-x-3">
                <label class="flex items-center space-x-2 cursor-pointer text-xs font-semibold text-slate-700">
                    <input type="checkbox" name="bajo_stock" value="1" {{ ($bajoStock ?? false) ? 'checked' : '' }} onchange="this.form.submit()"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded">
                    <span>Solo Bajo Stock</span>
                </label>

                @if(!empty($term) || !empty($categoriaId) || !empty($bajoStock))
                <a href="{{ route('productos.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                    Limpiar
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Lista de Productos (Dual: Table en Desktop / Cards en Mobile y Tablet) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Vista Desktop -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Producto</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Códigos</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Clasificación</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Precio Venta</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Precio Mayorista</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Stock</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($productos as $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $p->nombre }}</div>
                            <div class="text-xs text-slate-400 truncate max-w-xs">{{ $p->descripcion ?? 'Sin descripción' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-slate-600">
                            <div>SKU: {{ $p->codigo ?? '—' }}</div>
                            <div class="text-slate-400">EAN: {{ $p->codigo_barras ?? '—' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-600">
                            <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 font-medium">
                                {{ $p->categoria?->nombre ?? 'Sin categoría' }}
                            </span>
                            @if($p->marca)
                            <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 font-medium ml-1">
                                {{ $p->marca->nombre }}
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="font-bold text-slate-900 text-sm">
                                ${{ number_format($p->precio_venta, 2, ',', '.') }}
                            </div>
                            <div class="text-[11px] text-slate-400">
                                IVA: {{ (float)$p->iva }}% (${{ number_format($p->calcularPrecioConIva(), 2, ',', '.') }})
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-xs text-slate-600">
                            {{ $p->precio_mayorista ? '$'.number_format($p->precio_mayorista, 2, ',', '.') : '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($p->tieneBajoStock())
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500 mr-1.5"></span>
                                {{ number_format($p->stock, 0) }} {{ $p->unidadMedida?->codigo ?? 'UND' }}
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                {{ number_format($p->stock, 0) }} {{ $p->unidadMedida?->codigo ?? 'UND' }}
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('productos.edit', $p) }}"
                                class="inline-block text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg transition">
                                Editar
                            </a>
                            <form action="{{ route('productos.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-2.5 py-1.5 rounded-lg transition">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500">
                            No se encontraron productos registrados con los filtros seleccionados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vista Móvil y Tablet (Grid de Cards táctiles) -->
        <div class="lg:hidden divide-y divide-slate-100">
            @forelse($productos as $p)
            <div class="p-5 space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-bold text-slate-900 text-base leading-snug">{{ $p->nombre }}</div>
                        <div class="text-xs text-slate-500 font-mono mt-0.5">
                            SKU: {{ $p->codigo ?? '—' }} | EAN: {{ $p->codigo_barras ?? '—' }}
                        </div>
                    </div>
                    @if($p->tieneBajoStock())
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 flex-shrink-0">
                        {{ number_format($p->stock, 0) }} {{ $p->unidadMedida?->codigo ?? 'UND' }}
                    </span>
                    @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 flex-shrink-0">
                        {{ number_format($p->stock, 0) }} {{ $p->unidadMedida?->codigo ?? 'UND' }}
                    </span>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2 text-xs">
                    <span class="bg-slate-100 px-2.5 py-1 rounded-lg text-slate-700 font-medium">
                        {{ $p->categoria?->nombre ?? 'Sin Categoría' }}
                    </span>
                    @if($p->marca)
                    <span class="bg-slate-100 px-2.5 py-1 rounded-lg text-slate-700 font-medium">
                        {{ $p->marca->nombre }}
                    </span>
                    @endif
                </div>

                <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                    <div>
                        <span class="text-xs text-slate-400 block">Precio de Venta</span>
                        <span class="font-bold text-slate-900 text-lg">
                            ${{ number_format($p->precio_venta, 2, ',', '.') }}
                        </span>
                        <span class="text-[11px] text-slate-400 block">
                            (IVA: ${{ number_format($p->calcularPrecioConIva(), 2, ',', '.') }})
                        </span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <a href="{{ route('productos.edit', $p) }}"
                            class="text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3.5 py-2 rounded-xl transition">
                            Editar
                        </a>
                        <form action="{{ route('productos.destroy', $p) }}" method="POST" onsubmit="return confirm('¿Eliminar producto?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-bold text-red-700 bg-red-50 hover:bg-red-100 px-3.5 py-2 rounded-xl transition">
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-sm text-slate-500">
                No hay productos en el catálogo.
            </div>
            @endforelse
        </div>

        @if($productos->hasPages())
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            {{ $productos->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
