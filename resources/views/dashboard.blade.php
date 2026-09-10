@extends('layouts.app')

@section('title', 'Dashboard Principal')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-8">
    <!-- Banner de Bienvenida y Estado General -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div class="max-w-2xl">
                <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 mb-3">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 mr-2"></span>
                    Contexto Multiempresa Activo
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    {{ auth()->user()->empresa?->nombre_comercial ?? 'POS Comercial Global' }}
                </h1>
                <p class="mt-2 text-slate-300 text-sm sm:text-base leading-relaxed">
                    Bienvenido, <span class="font-bold text-white">{{ auth()->user()->name }}</span>. Has iniciado sesión con rol <span class="text-indigo-300 font-semibold">{{ auth()->user()->roles->first()?->name ?? 'Usuario' }}</span> en la sucursal <span class="text-emerald-400 font-semibold">{{ \App\Support\Tenancy\BranchContext::getBranch()?->nombre ?? 'Principal' }}</span>.
                </p>
            </div>

            <!-- Botones de Acción Directa en el Banner -->
            <div class="flex flex-wrap items-center gap-3 flex-shrink-0">
                @can('productos.crear')
                <a href="{{ route('productos.create') }}"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-bold text-slate-900 bg-white hover:bg-slate-100 shadow-md transition">
                    <svg class="h-4 w-4 mr-2 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Producto
                </a>
                @endcan
                @can('productos.ver')
                <a href="{{ route('productos.index') }}"
                    class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-white/10 hover:bg-white/20 border border-white/20 transition">
                    <svg class="h-4 w-4 mr-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    Ver Catálogo
                </a>
                @endcan
            </div>
        </div>

        <div class="absolute right-0 bottom-0 opacity-10 hidden lg:block pointer-events-none">
            <svg class="h-72 w-72 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
        </div>
    </div>

    <!-- Indicadores Financieros y Comerciales (Fase 15) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- KPI: Ventas Hoy -->
        <a href="{{ route('reportes.ventas') }}" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-indigo-400 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ventas de Hoy</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3">
                ${{ number_format($ventasHoy ?? 0, 2) }}
            </div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
                <span>{{ $cantidadVentasHoy ?? 0 }} ventas realizadas</span>
                <span class="text-indigo-600 font-semibold group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>

        <!-- KPI: Ventas del Mes -->
        <a href="{{ route('reportes.ventas') }}" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-indigo-400 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ventas del Mes</span>
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-indigo-600 mt-3">
                ${{ number_format($ventasMes ?? 0, 2) }}
            </div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
                <span>{{ $cantidadVentasMes ?? 0 }} transacciones</span>
                <span class="text-indigo-600 font-semibold group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>

        <!-- KPI: Ticket Promedio y Utilidad -->
        <a href="{{ route('reportes.utilidad') }}" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-emerald-400 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Utilidad del Mes</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-3">
                ${{ number_format($utilidadMes ?? 0, 2) }}
            </div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
                <span>Ticket Prom: ${{ number_format($ticketPromedio ?? 0, 2) }}</span>
                <span class="text-emerald-600 font-semibold group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>

        <!-- KPI: Cartera Pendiente -->
        <a href="{{ route('reportes.cartera') }}" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-amber-400 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cartera Pendiente</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600 group-hover:bg-amber-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-amber-600 mt-3">
                ${{ number_format($carteraPendiente ?? 0, 2) }}
            </div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
                <span>Saldo por cobrar a clientes</span>
                <span class="text-amber-600 font-semibold group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>
    </div>

    <!-- Indicadores Clave del Negocio (KPIs de Alto Impacto) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Tarjeta 1: Catálogo de Productos -->
        <a href="{{ route('productos.index') }}" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-indigo-300 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Catálogo General</span>
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3">
                {{ $totalProductos ?? 0 }}
            </div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
                <span>Artículos en catálogo</span>
                <span class="text-indigo-600 font-semibold group-hover:translate-x-1 transition">Ver todos &rarr;</span>
            </div>
        </a>

        <!-- Tarjeta 2: Alertas de Stock Bajo -->
        <a href="{{ route('productos.index', ['bajo_stock' => 1]) }}" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-red-300 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Alertas de Stock</span>
                <span class="p-2 rounded-xl {{ ($totalBajoStock ?? 0) > 0 ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-600' }} transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold {{ ($totalBajoStock ?? 0) > 0 ? 'text-red-600' : 'text-slate-900' }} mt-3 flex items-center gap-2">
                {{ $totalBajoStock ?? 0 }}
                @if(($totalBajoStock ?? 0) > 0)
                <span class="h-2.5 w-2.5 rounded-full bg-red-500 animate-pulse"></span>
                @endif
            </div>
            <div class="text-xs {{ ($totalBajoStock ?? 0) > 0 ? 'text-red-600 font-medium' : 'text-emerald-600 font-medium' }} mt-1 flex items-center justify-between">
                <span>{{ ($totalBajoStock ?? 0) > 0 ? 'Requieren reposición inmediata' : 'Stock en niveles óptimos' }}</span>
                <span class="group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>

        @can('sucursales.gestionar')
        <!-- Tarjeta 3: Sucursales y Sedes -->
        <a href="{{ route('sucursales.index') }}" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-emerald-300 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Sedes Comerciales</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3">
                {{ $totalSucursales ?? 1 }}
            </div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
                <span class="truncate">Activa: {{ \App\Support\Tenancy\BranchContext::getBranch()?->nombre ?? 'Principal' }}</span>
                <span class="text-emerald-600 font-semibold group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>
        @endcan

        <!-- Tarjeta 4: Clasificación / Categorías -->
        <a href="{{ route('catalogos.index') }}" class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-indigo-300 hover:shadow-md transition group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Clasificación</span>
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-3">
                {{ $totalCategorias ?? 0 }}
            </div>
            <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
                <span>Categorías comerciales</span>
                <span class="text-indigo-600 font-semibold group-hover:translate-x-1 transition">&rarr;</span>
            </div>
        </a>
    </div>

    <!-- Sección Principal Dividida (Aprovechamiento Integral de Ancho) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Columna Izquierda: Tabla Operativa de Inventario (8 cols) -->
        <div class="lg:col-span-8 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-bold text-slate-900 text-lg">Estado de Artículos en Catálogo</h2>
                            @if(($totalBajoStock ?? 0) > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                {{ $totalBajoStock }} con bajo stock
                            </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Visualización rápida de existencias, precios y alertas preventivas.
                        </p>
                    </div>

                    <a href="{{ route('productos.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1">
                        Ver catálogo completo &rarr;
                    </a>
                </div>

                <!-- Tabla de Productos Destacados -->
                @php
                    $prodCriticos = $productosCriticos ?? collect();
                    $ultimosProd = $ultimosProductos ?? collect();
                    $articulosAMostrar = $prodCriticos->isNotEmpty() ? $prodCriticos : $ultimosProd;
                @endphp

                @if($articulosAMostrar->isNotEmpty())
                <div class="overflow-x-hidden">
                    <table class="w-full divide-y divide-slate-100 table-auto text-left text-sm">
                        <thead class="bg-slate-50/70 text-xs font-bold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="px-5 py-3.5">Producto</th>
                                <th class="px-4 py-3.5">SKU</th>
                                <th class="px-4 py-3.5 text-right">Precio Venta</th>
                                <th class="px-4 py-3.5 text-center">Stock</th>
                                <th class="px-5 py-3.5 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($articulosAMostrar as $art)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-10 w-10 rounded-xl bg-slate-100 border border-slate-200/80 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                            @if($art->imagen_path)
                                                <img src="{{ $art->imagen_url }}" alt="{{ $art->nombre }}" class="h-full w-full object-cover">
                                            @else
                                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-900 text-sm truncate leading-snug">{{ $art->nombre }}</div>
                                            <div class="text-xs text-slate-400 mt-0.5">{{ $art->categoria?->nombre ?? 'Sin categoría' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 font-mono text-xs text-slate-600">
                                    <span class="bg-slate-100 px-2 py-0.5 rounded font-semibold">{{ $art->codigo ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-right font-bold text-slate-900 text-sm">
                                    ${{ number_format($art->precio_venta, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    @if($art->tieneBajoStock())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500 mr-1.5 animate-pulse"></span>
                                        {{ number_format($art->stock, 0) }} {{ $art->unidadMedida?->codigo ?? 'UND' }}
                                    </span>
                                    @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        {{ number_format($art->stock, 0) }} {{ $art->unidadMedida?->codigo ?? 'UND' }}
                                    </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('productos.edit', $art) }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg transition">
                                        Editar
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-10 text-center text-slate-500">
                    <p class="text-sm">No hay productos registrados en el inventario aún.</p>
                    <a href="{{ route('productos.create') }}" class="inline-flex items-center mt-3 px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                        Registrar primer producto
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Columna Derecha: Accesos Rápidos del Sistema & Ficha Tenant (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Bloque de Módulos Activos -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                <h3 class="font-bold text-slate-900 text-base">Accesos Directos</h3>

                <div class="space-y-2.5">
                    @can('productos.ver')
                    <a href="{{ route('productos.index') }}"
                        class="flex items-center justify-between p-3 rounded-2xl hover:bg-slate-50 border border-slate-100 hover:border-slate-200 transition group">
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition">Catálogo de Productos</div>
                                <div class="text-xs text-slate-400">Listado, precios e imágenes</div>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">
                            {{ $totalProductos ?? 0 }}
                        </span>
                    </a>
                    @endcan

                    @can('inventario.ver')
                    <a href="{{ route('inventario.index') }}"
                        class="flex items-center justify-between p-3 rounded-2xl hover:bg-slate-50 border border-slate-100 hover:border-slate-200 transition group">
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center font-bold">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-800 group-hover:text-teal-600 transition">Inventario & Kardex</div>
                                <div class="text-xs text-slate-400">Existencias, ajustes y traslados</div>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-teal-600 bg-teal-50 px-2 py-0.5 rounded-md">
                            Kardex
                        </span>
                    </a>
                    @endcan

                    @can('productos.ver')
                    <a href="{{ route('catalogos.index') }}"
                        class="flex items-center justify-between p-3 rounded-2xl hover:bg-slate-50 border border-slate-100 hover:border-slate-200 transition group">
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition">Catálogos Auxiliares</div>
                                <div class="text-xs text-slate-400">Categorías, marcas y unidades</div>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">
                            {{ $totalCategorias ?? 0 }}
                        </span>
                    </a>
                    @endcan

                    @can('sucursales.gestionar')
                    <a href="{{ route('sucursales.index') }}"
                        class="flex items-center justify-between p-3 rounded-2xl hover:bg-slate-50 border border-slate-100 hover:border-slate-200 transition group">
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-800 group-hover:text-emerald-600 transition">Sedes y Sucursales</div>
                                <div class="text-xs text-slate-400">Puntos de venta habilitados</div>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">
                            {{ $totalSucursales ?? 1 }}
                        </span>
                    </a>
                    @endcan

                    @can('empresa.gestionar')
                    <a href="{{ route('empresa.perfil') }}"
                        class="flex items-center justify-between p-3 rounded-2xl hover:bg-slate-50 border border-slate-100 hover:border-slate-200 transition group">
                        <div class="flex items-center space-x-3">
                            <div class="h-10 w-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-bold">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-800 group-hover:text-slate-900 transition">Perfil de la Empresa</div>
                                <div class="text-xs text-slate-400">Datos fiscales y moneda</div>
                            </div>
                        </div>
                        <span class="text-slate-400 group-hover:text-slate-600 transition">&rarr;</span>
                    </a>
                    @endcan
                </div>
            </div>

            <!-- Ficha Tributaria y de Seguridad -->
            <div class="bg-gradient-to-br from-slate-50 to-slate-100 p-6 rounded-3xl border border-slate-200 shadow-sm space-y-3">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Identificación del Tenant</span>
                <div class="space-y-1.5 text-xs text-slate-600">
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">Razón Social:</span>
                        <span class="font-bold text-slate-800">{{ auth()->user()->empresa?->razon_social ?? 'Empresa Registrada' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">NIT:</span>
                        <span class="font-mono font-bold text-slate-800">{{ auth()->user()->empresa?->nit ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">Moneda Base:</span>
                        <span class="font-bold text-slate-800">{{ auth()->user()->empresa?->moneda ?? 'COP' }} ({{ auth()->user()->empresa?->simbolo_moneda ?? '$' }})</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">Seguridad:</span>
                        <span class="font-semibold text-emerald-600 flex items-center gap-1">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aislamiento OK
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
