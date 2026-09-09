@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <!-- Banner de Bienvenida -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="relative z-10 max-w-2xl">
            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 mb-3">
                <span class="h-2 w-2 rounded-full bg-emerald-400 mr-2"></span>
                Contexto Multiempresa Activo
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                {{ auth()->user()->empresa?->nombre_comercial ?? 'POS Comercial Global' }}
            </h1>
            <p class="mt-2 text-slate-300 text-sm sm:text-base leading-relaxed">
                Bienvenido, <span class="font-bold text-white">{{ auth()->user()->name }}</span>. Tienes acceso asignado como <span class="text-indigo-400 font-semibold">{{ auth()->user()->roles->first()?->name ?? 'Usuario' }}</span> en la sucursal <span class="text-emerald-400 font-semibold">{{ \App\Support\Tenancy\BranchContext::getBranch()?->nombre ?? 'Principal' }}</span>.
            </p>
        </div>

        <div class="absolute right-0 bottom-0 opacity-10 hidden md:block">
            <svg class="h-64 w-64 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
        </div>
    </div>

    <!-- Indicadores Clave del Tenant (KPIs / Métricas Rápidas) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <!-- Tarjeta 1: Empresa -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-slate-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Identificación</span>
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                    </svg>
                </span>
            </div>
            <div class="text-lg font-bold text-slate-900 mt-3 truncate">
                NIT: {{ auth()->user()->empresa?->nit ?? 'N/A' }}
            </div>
            <div class="text-xs text-slate-500 mt-1 truncate">
                {{ auth()->user()->empresa?->razon_social ?? 'Empresa Registrada' }}
            </div>
        </div>

        <!-- Tarjeta 2: Sucursal Activa -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-slate-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Sucursal Operativa</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    </svg>
                </span>
            </div>
            <div class="text-lg font-bold text-slate-900 mt-3 truncate">
                {{ \App\Support\Tenancy\BranchContext::getBranch()?->nombre ?? 'Sin Asignar' }}
            </div>
            <div class="text-xs text-emerald-600 font-semibold mt-1 flex items-center">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1"></span>
                Punto de Venta Conectado
            </div>
        </div>

        <!-- Tarjeta 3: Moneda Operativa -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-slate-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Moneda Base</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600 font-bold">
                    {{ auth()->user()->empresa?->simbolo_moneda ?? '$' }}
                </span>
            </div>
            <div class="text-lg font-bold text-slate-900 mt-3">
                {{ auth()->user()->empresa?->moneda ?? 'COP' }}
            </div>
            <div class="text-xs text-slate-500 mt-1">
                Transacciones Comerciales
            </div>
        </div>

        <!-- Tarjeta 4: Seguridad -->
        <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-slate-300 transition">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Seguridad</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </span>
            </div>
            <div class="text-lg font-bold text-slate-900 mt-3">
                Aislamiento Total
            </div>
            <div class="text-xs text-slate-500 mt-1">
                Protección Global Scope OK
            </div>
        </div>
    </div>

    <!-- Módulos y Accesos Rápidos Táctiles (Optimizado para Touch / Móvil / Tablet / PC) -->
    <div>
        <h2 class="text-lg font-bold text-slate-900 mb-4 tracking-tight">Accesos Rápidos</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <!-- Acceso: Configuración de Empresa -->
            <a href="{{ route('empresa.perfil') }}"
                class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md hover:border-indigo-300 transition group flex flex-col justify-between min-h-[140px]">
                <div class="flex items-center justify-between">
                    <div class="h-12 w-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <span class="text-slate-400 group-hover:text-indigo-600 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base group-hover:text-indigo-600 transition mt-4">Perfil de la Empresa</h3>
                    <p class="text-xs text-slate-500 mt-1">Configurar datos comerciales, logo, moneda e información tributaria.</p>
                </div>
            </a>

            <!-- Acceso: Gestión de Sucursales -->
            <a href="{{ route('sucursales.index') }}"
                class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:shadow-md hover:border-indigo-300 transition group flex flex-col justify-between min-h-[140px]">
                <div class="flex items-center justify-between">
                    <div class="h-12 w-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <span class="text-slate-400 group-hover:text-emerald-600 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base group-hover:text-emerald-600 transition mt-4">Gestión de Sucursales</h3>
                    <p class="text-xs text-slate-500 mt-1">Crear sedes, puntos de venta y administrar la sede principal.</p>
                </div>
            </a>

            <!-- Acceso: Catálogo y Productos (Próxima Fase) -->
            <div class="bg-slate-50 p-6 rounded-3xl border border-slate-200 opacity-80 flex flex-col justify-between min-h-[140px]">
                <div class="flex items-center justify-between">
                    <div class="h-12 w-12 rounded-2xl bg-slate-200 text-slate-500 flex items-center justify-center">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold bg-slate-200 text-slate-600 px-2 py-0.5 rounded-md">Fase 4</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-700 text-base mt-4">Catálogos de Productos</h3>
                    <p class="text-xs text-slate-400 mt-1">Categorías, marcas, unidades de medida y productos.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
