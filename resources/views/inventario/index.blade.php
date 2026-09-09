@extends('layouts.app')

@section('title', 'Control de Inventario y Kardex — POS Comercial')

@section('content')
<div class="space-y-6 max-w-[1680px] mx-auto overflow-x-hidden">
    <!-- Header de la Vista -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-2xl">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Control de Inventario</h1>
                    <p class="text-sm text-slate-500 font-medium">Existencias físicas en tiempo real, valoración contable y Kardex inmutable por sucursal.</p>
                </div>
            </div>
        </div>

        <!-- Botones de Acción Rápida -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('inventario.traslado.create') }}"
                class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 hover:border-slate-300 shadow-sm transition">
                <svg class="h-4 w-4 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Trasladar entre Sucursales
            </a>

            <a href="{{ route('inventario.ajuste.create') }}"
                class="inline-flex items-center px-4 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 shadow-sm shadow-indigo-200 transition">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Nuevo Ajuste Manual
            </a>
        </div>
    </div>

    <!-- Banner KPI de Métricas Operacionales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Valor a Costo -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Valoración a Costo</p>
                <h3 class="text-xl font-black text-slate-900 mt-1">${{ number_format($valorCosto, 0, ',', '.') }}</h3>
                <span class="text-[11px] text-slate-500 font-medium">Inversión actual en existencias</span>
            </div>
            <div class="h-11 w-11 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Valor a PVP -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Valor Estimado PVP</p>
                <h3 class="text-xl font-black text-indigo-600 mt-1">${{ number_format($valorPvp, 0, ',', '.') }}</h3>
                <span class="text-[11px] text-emerald-600 font-medium">Potencial de venta total</span>
            </div>
            <div class="h-11 w-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
        </div>

        <!-- Total de Registros -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Artículos en Inventario</p>
                <h3 class="text-xl font-black text-slate-900 mt-1">{{ number_format($totalItems) }}</h3>
                <span class="text-[11px] text-slate-500 font-medium">Registros sucursal/producto</span>
            </div>
            <div class="h-11 w-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
        </div>

        <!-- Alerta Bajo Stock -->
        <div class="bg-white p-5 rounded-2xl border {{ $totalBajoStock > 0 ? 'border-amber-300 bg-amber-50/20' : 'border-slate-200/80' }} shadow-sm flex items-center justify-between">
            <div>
                <div class="flex items-center space-x-1.5">
                    <p class="text-xs font-bold text-amber-700 uppercase tracking-wider">Bajo Stock</p>
                    @if($totalBajoStock > 0)
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    </span>
                    @endif
                </div>
                <h3 class="text-xl font-black text-amber-600 mt-1">{{ $totalBajoStock }}</h3>
                <span class="text-[11px] text-amber-600 font-medium">Requieren reposición</span>
            </div>
            <div class="h-11 w-11 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

        <!-- Alerta Agotados -->
        <div class="bg-white p-5 rounded-2xl border {{ $totalAgotados > 0 ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200/80' }} shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-rose-700 uppercase tracking-wider">Agotados</p>
                <h3 class="text-xl font-black text-rose-600 mt-1">{{ $totalAgotados }}</h3>
                <span class="text-[11px] text-rose-500 font-medium">Existencias en 0</span>
            </div>
            <div class="h-11 w-11 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('inventario.index') }}" class="flex flex-col lg:flex-row gap-3">
            <!-- Input Buscador -->
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="buscar" value="{{ $term }}" placeholder="Buscar por producto, código SKU o código de barras (lector óptico)..."
                    class="block w-full pl-10 pr-4 py-2 text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition placeholder-slate-400">
            </div>

            <!-- Filtro Sucursal -->
            <div class="w-full lg:w-56">
                <select name="sucursal_id" onchange="this.form.submit()"
                    class="block w-full py-2 px-3 text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition font-medium text-slate-700">
                    <option value="">Todas las Sucursales</option>
                    @foreach($sucursales as $suc)
                    <option value="{{ $suc->id }}" {{ $sucursalId === $suc->id ? 'selected' : '' }}>
                        {{ $suc->nombre }} {{ $suc->es_principal ? '(Principal)' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro Categoría -->
            <div class="w-full lg:w-48">
                <select name="categoria_id" onchange="this.form.submit()"
                    class="block w-full py-2 px-3 text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition font-medium text-slate-700">
                    <option value="">Todas las Categorías</option>
                    @foreach($categorias as $cat)
                    <option value="{{ $cat->id }}" {{ $categoriaId === $cat->id ? 'selected' : '' }}>
                        {{ $cat->nombre }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro Condición Stock -->
            <div class="w-full lg:w-44">
                <select name="estado_stock" onchange="this.form.submit()"
                    class="block w-full py-2 px-3 text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition font-medium text-slate-700">
                    <option value="todos" {{ $estadoStock === 'todos' ? 'selected' : '' }}>Todo el Stock</option>
                    <option value="disponible" {{ $estadoStock === 'disponible' ? 'selected' : '' }}>Stock Normal</option>
                    <option value="bajo_stock" {{ $estadoStock === 'bajo_stock' ? 'selected' : '' }}>Bajo Stock</option>
                    <option value="agotado" {{ $estadoStock === 'agotado' ? 'selected' : '' }}>Agotados</option>
                </select>
            </div>

            <!-- Botones -->
            <div class="flex items-center space-x-2">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-sm font-semibold hover:bg-slate-900 transition">
                    Filtrar
                </button>
                @if($term || $sucursalId || $categoriaId || $estadoStock !== 'todos')
                <a href="{{ route('inventario.index') }}" class="px-3 py-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition" title="Limpiar filtros">
                    Limpiar
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Grid / Tabla de Existencias -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        @if($inventarios->count() > 0)
        <!-- Tabla en Desktop -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 divide-y divide-slate-100">
                <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th scope="col" class="py-3 px-4">Producto</th>
                        <th scope="col" class="py-3 px-3">Sucursal</th>
                        <th scope="col" class="py-3 px-3">Categoría / Marca</th>
                        <th scope="col" class="py-3 px-3 text-right">Existencias</th>
                        <th scope="col" class="py-3 px-3 text-center">Nivel de Stock</th>
                        <th scope="col" class="py-3 px-3 text-right">Costo / PVP</th>
                        <th scope="col" class="py-3 px-3 text-right">Valor Total</th>
                        <th scope="col" class="py-3 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($inventarios as $inv)
                    @php
                        $prod = $inv->producto;
                        $stock = (float) $inv->stock;
                        $stockMin = (float) $inv->stock_minimo;
                        $porcentajeStock = $stockMin > 0 ? min(100, round(($stock / ($stockMin * 2)) * 100)) : 100;
                        $valorTotalItem = $stock * (float) ($prod->precio_compra ?? 0);
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition">
                        <!-- Producto con Imagen y Códigos -->
                        <td class="py-3 px-4">
                            <div class="flex items-center space-x-3">
                                <div class="h-11 w-11 flex-shrink-0 rounded-xl bg-slate-100 border border-slate-200/80 overflow-hidden flex items-center justify-center">
                                    @if($prod->imagen_url)
                                    <img src="{{ $prod->imagen_url }}" alt="{{ $prod->nombre }}" class="h-full w-full object-cover">
                                    @else
                                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <a href="{{ route('inventario.kardex', $prod->id) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition truncate block max-w-xs" title="{{ $prod->nombre }}">
                                        {{ $prod->nombre }}
                                    </a>
                                    <div class="flex items-center space-x-2 text-[11px] text-slate-400 mt-0.5">
                                        @if($prod->codigo)
                                        <span>SKU: <span class="font-mono text-slate-600">{{ $prod->codigo }}</span></span>
                                        @endif
                                        @if($prod->codigo_barras)
                                        <span>• EAN: <span class="font-mono text-slate-600">{{ $prod->codigo_barras }}</span></span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Sucursal -->
                        <td class="py-3 px-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                                {{ $inv->sucursal->nombre }}
                            </span>
                        </td>

                        <!-- Categoría / Marca -->
                        <td class="py-3 px-3 text-xs whitespace-nowrap">
                            <div class="text-slate-700 font-semibold">{{ $prod->categoria?->nombre ?? 'Sin categoría' }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $prod->marca?->nombre ?? 'Sin marca' }}</div>
                        </td>

                        <!-- Existencias -->
                        <td class="py-3 px-3 text-right whitespace-nowrap">
                            <span class="text-base font-black {{ $inv->estaAgotado() ? 'text-rose-600' : ($inv->tieneBajoStock() ? 'text-amber-600' : 'text-slate-900') }}">
                                {{ number_format($stock, 2) }}
                            </span>
                            <span class="text-xs text-slate-400 ml-1">{{ $prod->unidadMedida?->codigo ?? 'UND' }}</span>
                            <div class="text-[11px] text-slate-400">Mín: {{ number_format($stockMin, 2) }}</div>
                        </td>

                        <!-- Nivel de Stock (Barra visual) -->
                        <td class="py-3 px-3 text-center whitespace-nowrap">
                            <div class="inline-block w-24">
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full {{ $inv->estaAgotado() ? 'bg-rose-500 w-full' : ($inv->tieneBajoStock() ? 'bg-amber-400' : 'bg-emerald-500') }}"
                                         style="width: {{ $porcentajeStock }}%"></div>
                                </div>
                                <span class="text-[10px] font-bold mt-0.5 inline-block {{ $inv->estaAgotado() ? 'text-rose-600' : ($inv->tieneBajoStock() ? 'text-amber-600' : 'text-emerald-600') }}">
                                    {{ $inv->estaAgotado() ? 'AGOTADO' : ($inv->tieneBajoStock() ? 'BAJO STOCK' : 'ÓPTIMO') }}
                                </span>
                            </div>
                        </td>

                        <!-- Costo / PVP -->
                        <td class="py-3 px-3 text-right text-xs whitespace-nowrap">
                            <div class="text-slate-900 font-bold">${{ number_format($prod->precio_venta, 0, ',', '.') }}</div>
                            <div class="text-slate-400 text-[11px]">Costo: ${{ number_format($prod->precio_compra, 0, ',', '.') }}</div>
                        </td>

                        <!-- Valor Total en Sucursal -->
                        <td class="py-3 px-3 text-right font-bold text-slate-900 whitespace-nowrap">
                            ${{ number_format($valorTotalItem, 0, ',', '.') }}
                        </td>

                        <!-- Acciones -->
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-1.5">
                                <a href="{{ route('inventario.kardex', $prod->id) }}"
                                    class="inline-flex items-center p-2 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-xl transition"
                                    title="Consultar Kardex Histórico">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </a>

                                <a href="{{ route('inventario.ajuste.create', ['producto_id' => $prod->id]) }}"
                                    class="inline-flex items-center p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition"
                                    title="Ajuste de Stock Rápido">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Vista Cards Táctiles en Móvil y Tablet -->
        <div class="block lg:hidden divide-y divide-slate-100 p-3 space-y-3">
            @foreach($inventarios as $inv)
            @php
                $prod = $inv->producto;
                $stock = (float) $inv->stock;
                $stockMin = (float) $inv->stock_minimo;
            @endphp
            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="h-12 w-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                            @if($prod->imagen_url)
                            <img src="{{ $prod->imagen_url }}" alt="{{ $prod->nombre }}" class="h-full w-full object-cover">
                            @else
                            <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-bold text-slate-900 text-sm truncate">{{ $prod->nombre }}</h4>
                            <div class="flex items-center space-x-2 text-xs text-slate-400 mt-0.5">
                                <span class="bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded text-[10px] font-semibold">{{ $inv->sucursal->nombre }}</span>
                                <span>{{ $prod->categoria?->nombre }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-right flex-shrink-0">
                        <span class="text-lg font-black {{ $inv->estaAgotado() ? 'text-rose-600' : ($inv->tieneBajoStock() ? 'text-amber-600' : 'text-slate-900') }}">
                            {{ number_format($stock, 2) }}
                        </span>
                        <div class="text-[10px] text-slate-400">Mín: {{ number_format($stockMin, 2) }}</div>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-50 text-xs">
                    <div>
                        <span class="text-slate-400">PVP:</span>
                        <span class="font-bold text-slate-800">${{ number_format($prod->precio_venta, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex items-center space-x-2">
                        <a href="{{ route('inventario.kardex', $prod->id) }}" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 font-semibold rounded-xl text-xs hover:bg-indigo-100 transition">
                            Kardex
                        </a>
                        <a href="{{ route('inventario.ajuste.create', ['producto_id' => $prod->id]) }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 font-semibold rounded-xl text-xs hover:bg-slate-200 transition">
                            Ajustar
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Paginación -->
        @if($inventarios->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $inventarios->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-12 px-4">
            <div class="h-14 w-14 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-900">No se encontraron artículos en inventario</h3>
            <p class="text-sm text-slate-500 mt-1 max-w-sm mx-auto">
                {{ $term ? 'No hay coincidencias para tu búsqueda. Intenta con otro término o limpia los filtros.' : 'No existen existencias registradas con los filtros seleccionados.' }}
            </p>
            <div class="mt-4">
                <a href="{{ route('inventario.ajuste.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-xs font-semibold rounded-xl hover:bg-indigo-700 transition">
                    Realizar Primer Ajuste de Inventario
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
