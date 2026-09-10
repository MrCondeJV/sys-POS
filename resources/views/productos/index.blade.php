@extends('layouts.app')

@section('title', 'Catálogo de Productos')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <!-- Header Principal con KPIs y Acciones -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <span>Inventario & Productos</span>
                <span>/</span>
                <span class="text-slate-800">Catálogo General</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Catálogo de Productos</h1>
            <p class="text-sm text-slate-500 mt-1">
                Artículos, códigos, precios comerciales e inventario de <span class="font-semibold text-slate-700">{{ auth()->user()->empresa?->nombre_comercial }}</span>.
            </p>
        </div>

        <!-- Indicadores Rápidos y Botones de Acción -->
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-sm text-xs">
                <span class="text-slate-500 font-medium">Total:</span>
                <span class="font-bold text-slate-900">{{ $totalProductos ?? $productos->total() }}</span>
                <span class="text-slate-300">|</span>
                <span class="inline-flex items-center text-red-600 font-bold gap-1">
                    <span class="h-2 w-2 rounded-full bg-red-500 {{ ($totalBajoStock ?? 0) > 0 ? 'animate-pulse' : '' }}"></span>
                    {{ $totalBajoStock ?? 0 }} Bajo Stock
                </span>
            </div>

            <a href="{{ route('catalogos.index') }}"
                class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 shadow-sm transition">
                <svg class="h-4 w-4 mr-2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                Categorías y Marcas
            </a>

            <a href="{{ route('productos.create') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo Producto
            </a>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros Rápidos -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('productos.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-center">
            <!-- Input Búsqueda -->
            <div class="md:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="buscar" value="{{ $term ?? '' }}"
                    class="block w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                    placeholder="Buscar por nombre, código SKU o código de barras (Enter)...">
            </div>

            <!-- Filtro Categoría -->
            <div class="md:col-span-4">
                <select name="categoria_id" onchange="this.form.submit()"
                    class="block w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <option value="">Todas las Categorías ({{ $categorias->count() }})</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ ($categoriaId ?? '') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro Bajo Stock & Limpiar -->
            <div class="md:col-span-3 flex items-center justify-between md:justify-end gap-3">
                <label class="inline-flex items-center space-x-2 cursor-pointer text-xs font-bold text-slate-700 select-none bg-slate-50 hover:bg-slate-100 px-3.5 py-2.5 rounded-xl border border-slate-200 transition">
                    <input type="checkbox" name="bajo_stock" value="1" {{ ($bajoStock ?? false) ? 'checked' : '' }} onchange="this.form.submit()"
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded">
                    <span class="flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full {{ ($totalBajoStock ?? 0) > 0 ? 'bg-red-500 animate-pulse' : 'bg-slate-300' }}"></span>
                        Solo Bajo Stock
                    </span>
                </label>

                @if(!empty($term) || !empty($categoriaId) || !empty($bajoStock))
                <a href="{{ route('productos.index') }}"
                    class="text-xs font-bold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-2.5 rounded-xl transition flex items-center gap-1">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    Limpiar
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Lista de Productos (Dual: Tabla Completa en Desktop / Cards Táctiles en Móvil y Tablet) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Vista Desktop (Amplia, equilibrada y sin scroll horizontal) -->
        <div class="hidden lg:block">
            <table class="w-full divide-y divide-slate-200 table-auto">
                <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 text-left">Producto</th>
                        <th class="px-4 py-3.5 text-left">Códigos</th>
                        <th class="px-4 py-3.5 text-left">Categoría</th>
                        <th class="px-4 py-3.5 text-left">Marca</th>
                        <th class="px-4 py-3.5 text-right">Precio Venta (COP)</th>
                        <th class="px-4 py-3.5 text-right">P. Mayorista</th>
                        <th class="px-4 py-3.5 text-center">Stock</th>
                        <th class="px-4 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white text-sm">
                    @forelse($productos as $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <!-- 1. Producto (Foto, Nombre, Descripción corta) -->
                        <td class="px-5 py-4">
                            <div class="flex items-center space-x-3.5">
                                <div class="h-12 w-12 rounded-2xl bg-slate-100 border border-slate-200/80 overflow-hidden flex-shrink-0 flex items-center justify-center shadow-xs">
                                    @if($p->imagen_path)
                                        <img src="{{ $p->imagen_url }}" alt="{{ $p->nombre }}" class="h-full w-full object-cover">
                                    @else
                                        <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-slate-900 text-sm leading-snug">{{ $p->nombre }}</div>
                                    @if($p->descripcion)
                                    <div class="text-xs text-slate-400 truncate max-w-xs mt-0.5">{{ $p->descripcion }}</div>
                                    @else
                                    <div class="text-xs text-slate-400 mt-0.5">Unidad: {{ $p->unidadMedida?->nombre ?? 'Unidad' }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- 2. Códigos (SKU y Código de barras) -->
                        <td class="px-4 py-4 font-mono text-xs">
                            <div class="inline-block px-2 py-0.5 rounded-md bg-slate-100 font-bold text-slate-800 border border-slate-200/60">
                                {{ $p->codigo ?? '—' }}
                            </div>
                            @if($p->codigo_barras)
                            <div class="text-slate-500 mt-1 flex items-center gap-1 text-[11px] tracking-tight">
                                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                {{ $p->codigo_barras }}
                            </div>
                            @endif
                        </td>

                        <!-- 3. Categoría -->
                        <td class="px-4 py-4 text-xs">
                            @if($p->categoria)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-semibold border border-indigo-100/70">
                                {{ $p->categoria->nombre }}
                            </span>
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        <!-- 4. Marca -->
                        <td class="px-4 py-4 text-xs">
                            @if($p->marca)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-medium">
                                {{ $p->marca->nombre }}
                            </span>
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        <!-- 5. Precios (PVP e IVA) -->
                        <td class="px-4 py-4 text-right whitespace-nowrap">
                            <div class="font-extrabold text-slate-900 text-sm sm:text-base">
                                ${{ number_format($p->precio_venta, 0, ',', '.') }}
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5">
                                IVA {{ (float)$p->iva }}% (${{ number_format($p->calcularPrecioConIva(), 0, ',', '.') }})
                            </div>
                        </td>

                        <!-- 6. Precio Mayorista -->
                        <td class="px-4 py-4 text-right whitespace-nowrap text-xs">
                            @if($p->precio_mayorista)
                            <span class="inline-block font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2.5 py-1 rounded-lg">
                                ${{ number_format($p->precio_mayorista, 0, ',', '.') }}
                            </span>
                            @else
                            <span class="text-slate-300">—</span>
                            @endif
                        </td>

                        <!-- 7. Stock -->
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @if($p->tieneBajoStock())
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                <span class="h-2 w-2 rounded-full bg-red-500 mr-1.5 animate-pulse"></span>
                                {{ number_format($p->stock, 0) }} {{ $p->unidadMedida?->codigo ?? 'UND' }}
                            </span>
                            <div class="text-[11px] text-red-500 font-medium mt-0.5">Mín: {{ number_format($p->stock_minimo, 0) }}</div>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 mr-1.5"></span>
                                {{ number_format($p->stock, 0) }} {{ $p->unidadMedida?->codigo ?? 'UND' }}
                            </span>
                            <div class="text-[11px] text-slate-400 mt-0.5">Mín: {{ number_format($p->stock_minimo, 0) }}</div>
                            @endif
                        </td>

                        <!-- 8. Estado -->
                        <td class="px-4 py-4 text-center whitespace-nowrap">
                            @if($p->estado === \App\Enums\EstadoGeneral::ACTIVO)
                            <span class="inline-flex items-center text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200/60 px-2.5 py-1 rounded-full">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Activo
                            </span>
                            @else
                            <span class="inline-flex items-center text-xs font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                                Inactivo
                            </span>
                            @endif
                        </td>

                        <!-- 9. Acciones -->
                        <td class="px-5 py-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end space-x-2">
                                <a href="{{ route('productos.edit', $p) }}"
                                    class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition gap-1 shadow-xs" title="Editar Producto">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <span>Editar</span>
                                </a>
                                <form action="{{ route('productos.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition" title="Eliminar Producto">
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
                        <td colspan="9" class="px-6 py-12 text-center text-sm text-slate-500">
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
