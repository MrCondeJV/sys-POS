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
        <!-- Vista Desktop (Optimizada al 100% sin scroll horizontal) -->
        <div class="hidden lg:block">
            <table class="w-full divide-y divide-slate-200 table-auto">
                <thead class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Producto</th>
                        <th class="px-4 py-3.5 text-left">Códigos</th>
                        <th class="px-4 py-3.5 text-right">Precios (COP)</th>
                        <th class="px-4 py-3.5 text-center">Stock</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white text-sm">
                    @forelse($productos as $p)
                    <tr class="hover:bg-slate-50/70 transition">
                        <!-- 1. Producto (Foto, Nombre, Categoría y Marca) -->
                        <td class="px-5 py-3.5">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 rounded-xl bg-slate-100 border border-slate-200/80 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    @if($p->imagen_path)
                                        <img src="{{ $p->imagen_url }}" alt="{{ $p->nombre }}" class="h-full w-full object-cover">
                                    @else
                                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-slate-900 text-sm truncate leading-snug">{{ $p->nombre }}</div>
                                    <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                        @if($p->categoria)
                                        <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 text-[10px] font-semibold text-slate-600">
                                            {{ $p->categoria->nombre }}
                                        </span>
                                        @endif
                                        @if($p->marca)
                                        <span class="text-[11px] text-slate-400 truncate max-w-[120px]">· {{ $p->marca->nombre }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- 2. Códigos (SKU y Código de barras) -->
                        <td class="px-4 py-3.5 font-mono text-xs text-slate-600">
                            <div class="font-semibold text-slate-800">{{ $p->codigo ?? '—' }}</div>
                            @if($p->codigo_barras)
                            <div class="text-[10px] text-slate-400 tracking-tight">{{ $p->codigo_barras }}</div>
                            @endif
                        </td>

                        <!-- 3. Precios (PVP, IVA, Mayorista) -->
                        <td class="px-4 py-3.5 text-right whitespace-nowrap">
                            <div class="font-bold text-slate-900 text-sm">
                                ${{ number_format($p->precio_venta, 0, ',', '.') }}
                            </div>
                            <div class="text-[10px] text-slate-400 leading-tight">
                                IVA {{ (float)$p->iva }}% (${{ number_format($p->calcularPrecioConIva(), 0, ',', '.') }})
                            </div>
                            @if($p->precio_mayorista)
                            <div class="text-[10px] font-semibold text-indigo-600 leading-tight mt-0.5">
                                May: ${{ number_format($p->precio_mayorista, 0, ',', '.') }}
                            </div>
                            @endif
                        </td>

                        <!-- 4. Stock -->
                        <td class="px-4 py-3.5 text-center whitespace-nowrap">
                            @if($p->tieneBajoStock())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                <span class="h-1.5 w-1.5 rounded-full bg-red-500 mr-1.5"></span>
                                {{ number_format($p->stock, 0) }} {{ $p->unidadMedida?->codigo ?? 'UND' }}
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                {{ number_format($p->stock, 0) }} {{ $p->unidadMedida?->codigo ?? 'UND' }}
                            </span>
                            @endif
                        </td>

                        <!-- 5. Acciones Rápidas -->
                        <td class="px-5 py-3.5 text-right whitespace-nowrap">
                            <div class="inline-flex items-center space-x-1">
                                <a href="{{ route('productos.edit', $p) }}"
                                    class="p-1.5 text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50 rounded-lg transition" title="Editar Producto">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form action="{{ route('productos.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Eliminar Producto">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">
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
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start space-x-3 min-w-0">
                        <div class="h-12 w-12 rounded-xl bg-slate-100 border border-slate-200/80 overflow-hidden flex-shrink-0 flex items-center justify-center">
                            @if($p->imagen_path)
                                <img src="{{ $p->imagen_url }}" alt="{{ $p->nombre }}" class="h-full w-full object-cover">
                            @else
                                <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-slate-900 text-base leading-snug truncate">{{ $p->nombre }}</div>
                            <div class="text-xs text-slate-500 font-mono mt-0.5">
                                SKU: {{ $p->codigo ?? '—' }} | EAN: {{ $p->codigo_barras ?? '—' }}
                            </div>
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
