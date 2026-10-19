@extends('layouts.app')

@section('title', 'Dashboard Operativo')

@section('content')
@php
    $hora = (int) now()->format('H');
    if ($hora < 12) {
        $saludo = 'Buenos días';
    } elseif ($hora < 19) {
        $saludo = 'Buenas tardes';
    } else {
        $saludo = 'Buenas noches';
    }

    $fechaEspanol = ucfirst(\Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY'));
    $empresaNombre = auth()->user()->empresa?->nombre_comercial ?? \App\Support\Tenancy\CompanyContext::getCompany()?->nombre_comercial ?? 'POS Comercial';
    $sedeNombre = \App\Support\Tenancy\BranchContext::getBranch()?->nombre ?? 'Sede Principal';

    $ventas7Dias = $ventasUltimos7Dias ?? [];
    $totalSemana = array_sum(array_column($ventas7Dias, 'total'));
    $maxVentaDia = !empty($ventas7Dias) ? (max(array_column($ventas7Dias, 'total')) ?: 1) : 1;
@endphp

<div class="w-full max-w-[1360px] mx-auto space-y-6">
    <!-- Encabezado de la Página (Hero / Welcome) -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-[#E2E8F0]">
        <div>
            <h1 class="text-2xl font-semibold text-[#0F172A] tracking-tight">
                {{ $saludo }}, {{ auth()->user()->name }}
            </h1>
            <p class="text-sm text-[#64748B] mt-1">
                Resumen de <strong class="font-semibold text-[#334155]">{{ $empresaNombre }}</strong> · {{ $sedeNombre }}, {{ $fechaEspanol }}
            </p>
            <div class="flex items-center gap-2 text-xs text-[#64748B] mt-2">
                <span class="inline-block w-2 h-2 rounded-full bg-[#10B981]"></span>
                <span>Actualizado hace un momento</span>
                <button type="button" onclick="window.location.reload()" 
                        class="p-1 text-[#64748B] hover:text-[#0F172A] hover:bg-slate-200/60 rounded transition cursor-pointer"
                        title="Recargar datos del dashboard">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Botones de Acción Directa -->
        <div class="flex items-center gap-2.5 flex-shrink-0">
            @can('productos.crear')
            <a href="{{ route('productos.create') }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg border border-[#CBD5E1] bg-white text-[#334155] hover:bg-slate-50 transition shadow-2xs">
                <svg class="h-4 w-4 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nuevo producto</span>
            </a>
            @elsecan('productos.ver')
            <a href="{{ route('productos.index') }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg border border-[#CBD5E1] bg-white text-[#334155] hover:bg-slate-50 transition shadow-2xs">
                <svg class="h-4 w-4 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span>Ver catálogo</span>
            </a>
            @endcan

            @can('ventas.crear')
            <a href="{{ route('pos.index') }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-lg bg-[#1D4ED8] hover:bg-[#1E40AF] text-white transition shadow-2xs">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nueva venta</span>
            </a>
            @endcan
        </div>
    </div>

    <!-- Fila 1: Métricas Financieras (4 Tarjetas Uniformes) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Ventas de Hoy -->
        <div class="card-banking bg-white border border-[#E2E8F0] rounded-xl p-5 hover:border-[#CBD5E1] transition flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Ventas de Hoy</span>
                    <svg class="h-5 w-5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-2xl sm:text-3xl font-semibold text-[#0F172A] tabular-nums mt-3">
                    ${{ number_format($ventasHoy ?? 0, 2) }}
                </div>
                <div class="text-xs text-[#64748B] mt-1">
                    @if(($ventasHoy ?? 0) == 0)
                        <span>Aún no hay ventas hoy</span>
                        @can('ventas.crear')
                        <div>
                            <a href="{{ route('pos.index') }}" class="font-semibold text-[#1D4ED8] hover:underline mt-1 inline-block">
                                Registrar primera venta &rarr;
                            </a>
                        </div>
                        @endcan
                    @else
                        <span class="tabular-nums">{{ $cantidadVentasHoy ?? 0 }} transacciones registradas</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('reportes.ventas') }}" class="text-xs font-medium text-[#1D4ED8] hover:underline flex items-center gap-1 mt-4 pt-3 border-t border-[#F1F5F9]">
                <span>Ver detalle</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- 2. Ventas del Mes -->
        <div class="card-banking bg-white border border-[#E2E8F0] rounded-xl p-5 hover:border-[#CBD5E1] transition flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Ventas del Mes</span>
                    <svg class="h-5 w-5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="text-2xl sm:text-3xl font-semibold text-[#0F172A] tabular-nums mt-3">
                    ${{ number_format($ventasMes ?? 0, 2) }}
                </div>
                <div class="text-xs text-[#64748B] mt-1 tabular-nums">
                    {{ $cantidadVentasMes ?? 0 }} transacciones acumuladas
                </div>
            </div>
            <a href="{{ route('reportes.ventas') }}" class="text-xs font-medium text-[#1D4ED8] hover:underline flex items-center gap-1 mt-4 pt-3 border-t border-[#F1F5F9]">
                <span>Ver detalle</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- 3. Utilidad del Mes -->
        <div class="card-banking bg-white border border-[#E2E8F0] rounded-xl p-5 hover:border-[#CBD5E1] transition flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Utilidad del Mes</span>
                    <svg class="h-5 w-5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <div class="text-2xl sm:text-3xl font-semibold text-[#0F172A] tabular-nums mt-3">
                    ${{ number_format($utilidadMes ?? 0, 2) }}
                </div>
                <div class="text-xs text-[#64748B] mt-1 tabular-nums">
                    Ticket promedio: ${{ number_format($ticketPromedio ?? 0, 2) }}
                </div>
            </div>
            <a href="{{ route('reportes.utilidad') }}" class="text-xs font-medium text-[#1D4ED8] hover:underline flex items-center gap-1 mt-4 pt-3 border-t border-[#F1F5F9]">
                <span>Ver detalle</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- 4. Cartera Pendiente -->
        <div class="card-banking bg-white border border-[#E2E8F0] rounded-xl p-5 hover:border-[#CBD5E1] transition flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Cartera Pendiente</span>
                    <svg class="h-5 w-5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="text-2xl sm:text-3xl font-semibold text-[#0F172A] tabular-nums mt-3">
                    ${{ number_format($carteraPendiente ?? 0, 2) }}
                </div>
                <div class="text-xs mt-1">
                    @if(($carteraPendiente ?? 0) > 0)
                        <span class="inline-flex items-center gap-1.5 font-medium text-[#92400E]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#D97706]"></span>
                            Saldo pendiente de cobro
                        </span>
                    @else
                        <span class="text-[#64748B]">Al día, sin saldo pendiente</span>
                    @endif
                </div>
            </div>
            <a href="{{ route('reportes.cartera') }}" class="text-xs font-medium text-[#1D4ED8] hover:underline flex items-center gap-1 mt-4 pt-3 border-t border-[#F1F5F9]">
                <span>Ver detalle</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Fila 2: Métricas Operativas (4 Tarjetas Uniformes) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 5. Catálogo General -->
        <div class="card-banking bg-white border border-[#E2E8F0] rounded-xl p-5 hover:border-[#CBD5E1] transition flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Catálogo General</span>
                    <svg class="h-5 w-5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div class="text-2xl sm:text-3xl font-semibold text-[#0F172A] tabular-nums mt-3">
                    {{ $totalProductos ?? 0 }}
                </div>
                <div class="text-xs text-[#64748B] mt-1">
                    Artículos activos en catálogo
                </div>
            </div>
            <a href="{{ route('productos.index') }}" class="text-xs font-medium text-[#1D4ED8] hover:underline flex items-center gap-1 mt-4 pt-3 border-t border-[#F1F5F9]">
                <span>Ver detalle</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- 6. Alertas de Stock -->
        <div class="card-banking bg-white border border-[#E2E8F0] rounded-xl p-5 hover:border-[#CBD5E1] transition flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Alertas de Stock</span>
                    <svg class="h-5 w-5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                @if(($totalBajoStock ?? 0) > 0)
                <div class="text-2xl sm:text-3xl font-semibold text-[#DC2626] tabular-nums mt-3 flex items-center gap-2">
                    <span>{{ $totalBajoStock }}</span>
                    <span class="text-[11px] font-medium bg-[#FEF2F2] text-[#991B1B] border border-[#FECACA] px-2 py-0.5 rounded-full">
                        Requiere reposición
                    </span>
                </div>
                <div class="text-xs text-[#DC2626] font-medium mt-1">
                    Artículos bajo stock mínimo
                </div>
                @else
                <div class="text-2xl sm:text-3xl font-semibold text-[#0F172A] tabular-nums mt-3">
                    0
                </div>
                <div class="text-xs text-[#64748B] flex items-center gap-1.5 mt-1">
                    <svg class="h-3.5 w-3.5 text-[#10B981] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Stock en niveles óptimos</span>
                </div>
                @endif
            </div>
            <a href="{{ route('productos.index', ['bajo_stock' => 1]) }}" class="text-xs font-medium text-[#1D4ED8] hover:underline flex items-center gap-1 mt-4 pt-3 border-t border-[#F1F5F9]">
                <span>Ver detalle</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- 7. Sedes Comerciales -->
        @can('sucursales.gestionar')
        <div class="card-banking bg-white border border-[#E2E8F0] rounded-xl p-5 hover:border-[#CBD5E1] transition flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Sedes Comerciales</span>
                    <svg class="h-5 w-5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div class="text-2xl sm:text-3xl font-semibold text-[#0F172A] tabular-nums mt-3">
                    {{ $totalSucursales ?? 1 }}
                </div>
                <div class="text-xs text-[#64748B] mt-1 truncate">
                    Activa: <strong class="font-medium text-[#334155]">{{ $sedeNombre }}</strong>
                </div>
            </div>
            <a href="{{ route('sucursales.index') }}" class="text-xs font-medium text-[#1D4ED8] hover:underline flex items-center gap-1 mt-4 pt-3 border-t border-[#F1F5F9]">
                <span>Ver detalle</span>
                <span>&rarr;</span>
            </a>
        </div>
        @else
        <div class="card-banking bg-white border border-[#E2E8F0] rounded-xl p-5 hover:border-[#CBD5E1] transition flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Sede Asignada</span>
                    <svg class="h-5 w-5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                </div>
                <div class="text-lg font-semibold text-[#0F172A] truncate mt-3">
                    {{ $sedeNombre }}
                </div>
                <div class="text-xs text-[#64748B] mt-1">
                    Punto de atención operativo
                </div>
            </div>
            <span class="text-xs font-medium text-[#64748B] flex items-center gap-1 mt-4 pt-3 border-t border-[#F1F5F9]">
                Asignación activa
            </span>
        </div>
        @endcan

        <!-- 8. Clasificación -->
        <div class="card-banking bg-white border border-[#E2E8F0] rounded-xl p-5 hover:border-[#CBD5E1] transition flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">Clasificación</span>
                    <svg class="h-5 w-5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                </div>
                <div class="text-2xl sm:text-3xl font-semibold text-[#0F172A] tabular-nums mt-3">
                    {{ $totalCategorias ?? 0 }}
                </div>
                <div class="text-xs text-[#64748B] mt-1">
                    Categorías comerciales
                </div>
            </div>
            <a href="{{ route('catalogos.index') }}" class="text-xs font-medium text-[#1D4ED8] hover:underline flex items-center gap-1 mt-4 pt-3 border-t border-[#F1F5F9]">
                <span>Ver detalle</span>
                <span>&rarr;</span>
            </a>
        </div>
    </div>

    <!-- Gráfico de Tendencia — Ventas últimos 7 días -->
    <div class="card-banking bg-white border border-[#E2E8F0] rounded-xl p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4 pb-3 border-b border-[#F1F5F9]">
            <div>
                <h2 class="text-sm font-semibold text-[#0F172A]">Tendencia de Ventas (Últimos 7 Días)</h2>
                <p class="text-xs text-[#64748B] mt-0.5">Comportamiento diario de ingresos en la sucursal actual</p>
            </div>
            <div class="text-left sm:text-right">
                <span class="text-[11px] text-[#64748B] uppercase tracking-wider font-semibold block">Total 7 días</span>
                <span class="text-base font-semibold text-[#0F172A] tabular-nums">${{ number_format($totalSemana, 2) }}</span>
            </div>
        </div>

        @if($totalSemana > 0)
        <!-- Barras de Gráfico Semanal -->
        <div class="grid grid-cols-7 gap-2 sm:gap-4 items-end h-40 pt-4">
            @foreach($ventas7Dias as $item)
                @php
                    $pct = $maxVentaDia > 0 ? max(6, round(($item['total'] / $maxVentaDia) * 100)) : 6;
                @endphp
                <div class="flex flex-col items-center h-full justify-end group relative" title="{{ $item['dia'] }} ({{ $item['fecha'] }}): ${{ number_format($item['total'], 2) }}">
                    <!-- Tooltip flotante al pasar el mouse -->
                    <div class="absolute -top-7 opacity-0 group-hover:opacity-100 transition-opacity bg-[#0F172A] text-white text-[10px] font-semibold py-0.5 px-2 rounded pointer-events-none whitespace-nowrap z-10 tabular-nums shadow-sm">
                        ${{ number_format($item['total'], 0, ',', '.') }}
                    </div>
                    <!-- Barra de volumen -->
                    <div class="w-full max-w-[42px] rounded-t-md transition-all duration-200 {{ $item['total'] > 0 ? 'bg-[#1D4ED8] group-hover:bg-[#1E40AF]' : 'bg-slate-100' }}"
                         style="height: {{ $pct }}%;">
                    </div>
                    <!-- Etiqueta del día -->
                    <div class="text-[11px] font-medium text-[#64748B] mt-2 truncate w-full text-center">
                        {{ $item['dia'] }}
                    </div>
                </div>
            @endforeach
        </div>
        @else
        <!-- Estado Vacío -->
        <div class="h-32 flex flex-col items-center justify-center text-center p-4">
            <svg class="h-8 w-8 text-[#94A3B8] mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <p class="text-xs text-[#64748B]">Sin movimientos registrados en los últimos 7 días</p>
        </div>
        @endif
    </div>

    <!-- Sección Principal: Tabla Operativa (8 cols) y Accesos Directos (4 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Columna Izquierda: Tabla Operativa de Inventario (8 cols) -->
        <div class="lg:col-span-8 space-y-6">
            <div class="card-banking bg-white border border-[#E2E8F0] rounded-xl overflow-hidden">
                <div class="p-5 border-b border-[#E2E8F0] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-semibold text-[#0F172A] text-base">Estado de Artículos en Catálogo</h2>
                            @if(($totalBajoStock ?? 0) > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#FEF2F2] text-[#991B1B] border border-[#FECACA]">
                                {{ $totalBajoStock }} bajo stock
                            </span>
                            @endif
                        </div>
                        <p class="text-xs text-[#64748B] mt-0.5">
                            Existencias actuales, precios de venta y control preventivo.
                        </p>
                    </div>

                    <a href="{{ route('productos.index') }}" class="text-xs font-semibold text-[#1D4ED8] hover:underline transition flex items-center gap-1 flex-shrink-0">
                        <span>Ver catálogo completo</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <!-- Tabla de Artículos -->
                @php
                    $prodCriticos = $productosCriticos ?? collect();
                    $ultimosProd = $ultimosProductos ?? collect();
                    $articulosAMostrar = $prodCriticos->isNotEmpty() ? $prodCriticos : $ultimosProd;
                @endphp

                @if($articulosAMostrar->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs table-auto">
                        <thead class="bg-[#F8FAFC] text-[11px] font-semibold text-[#64748B] uppercase tracking-wider border-b border-[#E2E8F0]">
                            <tr>
                                <th class="px-4 py-3">Producto</th>
                                <th class="px-3 py-3">Código / SKU</th>
                                <th class="px-4 py-3 text-right">Precio Venta</th>
                                <th class="px-4 py-3 text-center">Estado Stock</th>
                                <th class="px-4 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F1F5F9]">
                            @foreach($articulosAMostrar as $art)
                            <tr class="hover:bg-[#F8FAFC] transition">
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-9 w-9 rounded-lg bg-slate-100 border border-[#E2E8F0] overflow-hidden flex-shrink-0 flex items-center justify-center">
                                            @if($art->imagen_path)
                                                <img src="{{ $art->imagen_url }}" alt="{{ $art->nombre }}" class="h-full w-full object-cover">
                                            @else
                                                <svg class="h-4 w-4 text-[#94A3B8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-[#0F172A] text-xs truncate max-w-[180px] sm:max-w-[240px]">
                                                {{ $art->nombre }}
                                            </div>
                                            <div class="text-[11px] text-[#64748B] mt-0.5">
                                                {{ $art->categoria?->nombre ?? 'Sin categoría' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 text-[#64748B] tabular-nums">
                                    {{ $art->codigo ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-semibold text-[#0F172A] tabular-nums">
                                    ${{ number_format($art->precio_venta, 2) }}
                                </td>
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    @if($art->stock <= 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#FEF2F2] text-[#991B1B] border border-[#FECACA]">
                                            Agotado (0 {{ $art->unidadMedida?->codigo ?? 'UND' }})
                                        </span>
                                    @elseif($art->tieneBajoStock())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#FFFBEB] text-[#92400E] border border-[#FDE68A]">
                                            Bajo stock ({{ number_format($art->stock, 0) }} {{ $art->unidadMedida?->codigo ?? 'UND' }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-[#ECFDF5] text-[#065F46] border border-[#A7F3D0]">
                                            En stock ({{ number_format($art->stock, 0) }} {{ $art->unidadMedida?->codigo ?? 'UND' }})
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <a href="{{ route('productos.edit', $art) }}" class="text-xs font-medium text-[#1D4ED8] hover:underline">
                                        Editar
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-8 text-center text-[#64748B]">
                    <p class="text-xs">No hay productos registrados en el inventario aún.</p>
                    @can('productos.crear')
                    <a href="{{ route('productos.create') }}" class="inline-flex items-center mt-3 px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-[#1D4ED8] hover:bg-[#1E40AF] transition shadow-2xs">
                        Registrar primer producto
                    </a>
                    @endcan
                </div>
                @endif
            </div>
        </div>

        <!-- Columna Derecha: Accesos Directos del Sistema (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            <div class="card-banking bg-white border border-[#E2E8F0] rounded-xl p-5 space-y-4">
                <div class="border-b border-[#F1F5F9] pb-3">
                    <h3 class="font-semibold text-[#0F172A] text-sm">Accesos Directos</h3>
                    <p class="text-xs text-[#64748B] mt-0.5">Operaciones frecuentes y consulta rápida</p>
                </div>

                <div class="space-y-2">
                    @can('ventas.crear')
                    <a href="{{ route('pos.index') }}"
                        class="flex items-center justify-between p-2.5 rounded-lg border border-[#F1F5F9] hover:bg-[#F4F6F9] hover:border-[#E2E8F0] transition group">
                        <div class="flex items-center space-x-3">
                            <div class="h-9 w-9 rounded-lg bg-[#F1F5F9] text-[#64748B] flex items-center justify-center flex-shrink-0 group-hover:text-[#1D4ED8] transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-[#0F172A] group-hover:text-[#1D4ED8] transition">Terminal POS</div>
                                <div class="text-[11px] text-[#64748B]">Venta rápida en mostrador</div>
                            </div>
                        </div>
                        <span class="text-xs text-[#64748B] group-hover:translate-x-0.5 transition">&rarr;</span>
                    </a>
                    @endcan

                    @can('productos.ver')
                    <a href="{{ route('productos.index') }}"
                        class="flex items-center justify-between p-2.5 rounded-lg border border-[#F1F5F9] hover:bg-[#F4F6F9] hover:border-[#E2E8F0] transition group">
                        <div class="flex items-center space-x-3">
                            <div class="h-9 w-9 rounded-lg bg-[#F1F5F9] text-[#64748B] flex items-center justify-center flex-shrink-0 group-hover:text-[#1D4ED8] transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-[#0F172A] group-hover:text-[#1D4ED8] transition">Catálogo de Productos</div>
                                <div class="text-[11px] text-[#64748B]">Listado, precios e inventario</div>
                            </div>
                        </div>
                        <span class="text-xs text-[#64748B] tabular-nums font-medium">
                            {{ $totalProductos ?? 0 }}
                        </span>
                    </a>
                    @endcan

                    @can('inventario.ver')
                    <a href="{{ route('inventario.index') }}"
                        class="flex items-center justify-between p-2.5 rounded-lg border border-[#F1F5F9] hover:bg-[#F4F6F9] hover:border-[#E2E8F0] transition group">
                        <div class="flex items-center space-x-3">
                            <div class="h-9 w-9 rounded-lg bg-[#F1F5F9] text-[#64748B] flex items-center justify-center flex-shrink-0 group-hover:text-[#1D4ED8] transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-[#0F172A] group-hover:text-[#1D4ED8] transition">Inventario & Kardex</div>
                                <div class="text-[11px] text-[#64748B]">Movimientos y ajustes</div>
                            </div>
                        </div>
                        <span class="text-xs text-[#64748B] group-hover:translate-x-0.5 transition">&rarr;</span>
                    </a>
                    @endcan

                    @can('catalogos.index')
                    <a href="{{ route('catalogos.index') }}"
                        class="flex items-center justify-between p-2.5 rounded-lg border border-[#F1F5F9] hover:bg-[#F4F6F9] hover:border-[#E2E8F0] transition group">
                        <div class="flex items-center space-x-3">
                            <div class="h-9 w-9 rounded-lg bg-[#F1F5F9] text-[#64748B] flex items-center justify-center flex-shrink-0 group-hover:text-[#1D4ED8] transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-[#0F172A] group-hover:text-[#1D4ED8] transition">Catálogos Auxiliares</div>
                                <div class="text-[11px] text-[#64748B]">Categorías y marcas</div>
                            </div>
                        </div>
                        <span class="text-xs text-[#64748B] tabular-nums font-medium">
                            {{ $totalCategorias ?? 0 }}
                        </span>
                    </a>
                    @endcan

                    @can('sucursales.gestionar')
                    <a href="{{ route('sucursales.index') }}"
                        class="flex items-center justify-between p-2.5 rounded-lg border border-[#F1F5F9] hover:bg-[#F4F6F9] hover:border-[#E2E8F0] transition group">
                        <div class="flex items-center space-x-3">
                            <div class="h-9 w-9 rounded-lg bg-[#F1F5F9] text-[#64748B] flex items-center justify-center flex-shrink-0 group-hover:text-[#1D4ED8] transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-[#0F172A] group-hover:text-[#1D4ED8] transition">Sedes y Sucursales</div>
                                <div class="text-[11px] text-[#64748B]">Puntos de venta autorizados</div>
                            </div>
                        </div>
                        <span class="text-xs text-[#64748B] tabular-nums font-medium">
                            {{ $totalSucursales ?? 1 }}
                        </span>
                    </a>
                    @endcan

                    @can('empresa.gestionar')
                    <a href="{{ route('empresa.perfil') }}"
                        class="flex items-center justify-between p-2.5 rounded-lg border border-[#F1F5F9] hover:bg-[#F4F6F9] hover:border-[#E2E8F0] transition group">
                        <div class="flex items-center space-x-3">
                            <div class="h-9 w-9 rounded-lg bg-[#F1F5F9] text-[#64748B] flex items-center justify-center flex-shrink-0 group-hover:text-[#1D4ED8] transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-semibold text-[#0F172A] group-hover:text-[#1D4ED8] transition">Perfil de la Empresa</div>
                                <div class="text-[11px] text-[#64748B]">Datos fiscales y configuración</div>
                            </div>
                        </div>
                        <span class="text-xs text-[#64748B] group-hover:translate-x-0.5 transition">&rarr;</span>
                    </a>
                    @endcan
                </div>
            </div>

            <!-- Ficha de Seguridad del Tenant -->
            <div class="card-banking bg-white border border-[#E2E8F0] rounded-xl p-5 space-y-3">
                <span class="text-[11px] font-semibold text-[#64748B] uppercase tracking-wider block">Contexto del Negocio</span>
                <div class="space-y-2 text-xs text-[#64748B]">
                    <div class="flex justify-between py-1 border-b border-[#F1F5F9]">
                        <span>Razón Social</span>
                        <span class="font-medium text-[#0F172A] truncate max-w-[160px]">{{ auth()->user()->empresa?->razon_social ?? $empresaNombre }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-[#F1F5F9]">
                        <span>NIT</span>
                        <span class="font-medium text-[#0F172A] tabular-nums">{{ auth()->user()->empresa?->nit ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-[#F1F5F9]">
                        <span>Moneda</span>
                        <span class="font-medium text-[#0F172A] tabular-nums">{{ auth()->user()->empresa?->moneda ?? 'COP' }} ({{ auth()->user()->empresa?->simbolo_moneda ?? '$' }})</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span>Aislamiento de Datos</span>
                        <span class="font-medium text-[#059669] flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#10B981]"></span>
                            <span>Seguro</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
