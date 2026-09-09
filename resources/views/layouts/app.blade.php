<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'POS Comercial') }} - @yield('title', 'Inicio')</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js es provisto automáticamente por Livewire -->
    <style>
        [x-cloak] { display: none !important; }

        @media print {
            @page {
                size: letter portrait;
                margin: 8mm 10mm;
            }
            body {
                background-color: #ffffff !important;
                background: #ffffff !important;
                color: #0f172a !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            aside, header, nav, .print\:hidden, .no-print, [role="dialog"] {
                display: none !important;
            }
            main {
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }
    </style>
    @yield('styles')
    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50 flex" x-data="{ mobileMenuOpen: false }">

    @auth
    <!-- Sidebar para Escritorio / Pantallas medianas y grandes -->
    <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-slate-900 text-slate-300 flex-shrink-0 border-r border-slate-800">
        <!-- Brand Header -->
        <div class="h-16 flex items-center px-6 bg-slate-950 border-b border-slate-800">
            <div class="h-9 w-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold shadow-md shadow-indigo-500/20 mr-3">
                POS
            </div>
            <div class="truncate">
                <span class="font-bold text-white tracking-wide block truncate text-sm">
                    {{ auth()->user()->empresa?->nombre_comercial ?? 'POS Comercial' }}
                </span>
                <span class="text-xs text-slate-400 block truncate">
                    NIT: {{ auth()->user()->empresa?->nit ?? 'Global' }}
                </span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <a href="{{ route('dashboard') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>

            <div class="pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">
                Gestión Empresarial
            </div>

            <a href="{{ route('empresa.perfil') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('empresa.perfil') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Perfil de Empresa
            </a>

            <a href="{{ route('sucursales.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('sucursales.index') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Sucursales
            </a>

            <div class="pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">
                Inventario & Productos
            </div>

            <a href="{{ route('productos.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('productos.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                Productos
            </a>

            <a href="{{ route('listas-precios.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('listas-precios.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                Listas de Precios
            </a>

            <a href="{{ route('inventario.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('inventario.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Inventario & Kardex
            </a>

            <a href="{{ route('catalogos.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('catalogos.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                Catálogos Auxiliares
            </a>

            <div class="pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">
                Compras & Proveedores
            </div>

            <a href="{{ route('compras.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('compras.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Compras / Facturas
            </a>

            <a href="{{ route('proveedores.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('proveedores.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Proveedores
            </a>

            <div class="pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">
                Ventas & Clientes
            </div>

            <a href="{{ route('ventas.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('ventas.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Ventas Realizadas
            </a>

            <a href="{{ route('devoluciones.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('devoluciones.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                </svg>
                Devoluciones
            </a>

            <a href="{{ route('documentos.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('documentos.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Documentos de Venta
            </a>

            <a href="{{ route('clientes.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('clientes.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Clientes
            </a>

            <a href="{{ route('cartera.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('cartera.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                Crédito & Cartera
            </a>

            <div class="pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">
                Operaciones & Caja
            </div>

            <a href="{{ route('cajas.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('cajas.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Cajas & Turnos
            </a>

            <a href="{{ route('pos.index') }}"
                class="flex items-center justify-between px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('pos.*') ? 'bg-emerald-600 text-white shadow-sm' : 'text-emerald-400 hover:text-white hover:bg-emerald-800/40' }}">
                <span class="flex items-center">
                    <svg class="h-5 w-5 mr-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Punto de Venta (POS)
                </span>
                <span class="text-[10px] bg-emerald-500/20 text-emerald-300 font-bold px-1.5 py-0.5 rounded">Rápido</span>
            </a>

            <div class="pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">
                Analítica & Negocio
            </div>

            <a href="{{ route('reportes.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('reportes.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Centro de Reportes
            </a>

            <a href="{{ route('auditoria.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('auditoria.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Auditoría & Logs
            </a>

            <a href="{{ route('impuestos.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('impuestos.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                </svg>
                Impuestos & Tarifas
            </a>

            <div class="pt-4 pb-1 px-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">DIAN & Fiscal</span>
            </div>

            <a href="{{ route('facturacion-electronica.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('facturacion-electronica.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Facturación Electrónica
            </a>

            <a href="{{ route('resoluciones.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('resoluciones.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Resoluciones DIAN
            </a>
        </nav>

        <!-- User footer -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/60 flex items-center justify-between">
            <div class="truncate mr-2">
                <div class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                <div class="text-xs text-indigo-400 truncate">{{ auth()->user()->roles->first()?->name ?? 'Usuario' }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="p-2 text-slate-400 hover:text-red-400 transition rounded-lg hover:bg-slate-800" title="Cerrar sesión">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Mobile Drawer Menu (Teléfonos y Tablets pequeñas) -->
    <div x-cloak x-show="mobileMenuOpen" class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm"
             @click="mobileMenuOpen = false"></div>

        <div class="fixed inset-0 flex">
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition ease-in-out duration-250 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-250 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="relative mr-16 flex w-full max-w-xs flex-1 flex-col bg-slate-900 pt-5 pb-4">
                <div class="flex items-center justify-between px-6 pb-4 border-b border-slate-800">
                    <div class="flex items-center space-x-3">
                        <div class="h-9 w-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white font-bold">POS</div>
                        <span class="font-bold text-white text-sm truncate">{{ auth()->user()->empresa?->nombre_comercial ?? 'POS Comercial' }}</span>
                    </div>
                    <button type="button" class="text-slate-400 hover:text-white" @click="mobileMenuOpen = false">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="mt-4 px-4 space-y-1 overflow-y-auto flex-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-slate-800">
                        Dashboard
                    </a>
                    <a href="{{ route('empresa.perfil') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-slate-800">
                        Perfil de Empresa
                    </a>
                    <a href="{{ route('sucursales.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-slate-800">
                        Sucursales
                    </a>
                    <a href="{{ route('productos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('productos.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Productos
                    </a>
                    <a href="{{ route('listas-precios.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('listas-precios.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Listas de Precios
                    </a>
                    <a href="{{ route('inventario.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('inventario.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Inventario & Kardex
                    </a>
                    <a href="{{ route('catalogos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('catalogos.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Catálogos Auxiliares
                    </a>
                    <a href="{{ route('compras.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('compras.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Compras / Facturas
                    </a>
                    <a href="{{ route('proveedores.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('proveedores.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Proveedores
                    </a>
                    <a href="{{ route('ventas.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('ventas.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Ventas Realizadas
                    </a>
                    <a href="{{ route('devoluciones.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('devoluciones.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Devoluciones
                    </a>
                    <a href="{{ route('documentos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('documentos.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Documentos de Venta
                    </a>
                    <a href="{{ route('clientes.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('clientes.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Clientes
                    </a>
                    <a href="{{ route('cartera.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('cartera.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Crédito & Cartera
                    </a>
                    <a href="{{ route('cajas.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('cajas.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Cajas & Turnos
                    </a>
                    <a href="{{ route('pos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('pos.*') ? 'bg-emerald-600 text-white' : 'text-emerald-400 hover:bg-slate-800' }}">
                        ⚡ Terminal POS (Ventas Rápidas)
                    </a>
                    <a href="{{ route('reportes.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('reportes.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        📊 Centro de Reportes
                    </a>
                    <a href="{{ route('auditoria.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('auditoria.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        🛡️ Auditoría & Logs
                    </a>
                    <a href="{{ route('impuestos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('impuestos.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        🏷️ Impuestos & Tarifas
                    </a>
                    <a href="{{ route('facturacion-electronica.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('facturacion-electronica.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        ⚡ Facturación Electrónica
                    </a>
                    <a href="{{ route('resoluciones.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('resoluciones.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        📋 Resoluciones DIAN
                    </a>
                </nav>

                <div class="p-4 border-t border-slate-800">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center py-2.5 px-4 rounded-xl bg-red-600/10 text-red-400 hover:bg-red-600 hover:text-white font-semibold text-sm transition">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endauth

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        @auth
        <!-- Header superior universal -->
        <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-10 flex-shrink-0">
            <!-- Left: Mobile Menu Button -->
            <div class="flex items-center space-x-3">
                <button type="button" @click="mobileMenuOpen = true" class="lg:hidden p-2 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="text-sm font-bold text-slate-900 hidden sm:block lg:hidden">
                    {{ auth()->user()->empresa?->nombre_comercial ?? 'POS' }}
                </div>
            </div>

            <!-- Right: Branch Selector & Profile -->
            <div class="flex items-center space-x-3 sm:space-x-4">
                <!-- Selector de Sucursal Activa -->
                @if(auth()->user()->empresa && auth()->user()->empresa->sucursales->count() > 0)
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" type="button"
                        class="inline-flex items-center px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-xl bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200 transition">
                        <svg class="h-4 w-4 mr-1.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                        </svg>
                        <span class="truncate max-w-[120px] sm:max-w-[200px]">
                            {{ \App\Support\Tenancy\BranchContext::getBranch()?->nombre ?? 'Seleccionar Sucursal' }}
                        </span>
                        <svg class="h-3.5 w-3.5 ml-1 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-cloak x-show="open" @click.away="open = false"
                        class="absolute right-0 mt-2 w-60 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 p-2 z-50 border border-slate-100">
                        <div class="px-3 py-1.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Cambiar Sucursal Activa
                        </div>
                        <div class="mt-1 space-y-1 max-h-60 overflow-y-auto">
                            @foreach(auth()->user()->empresa->sucursales as $sucursal)
                            <form action="{{ route('sucursales.seleccionar') }}" method="POST">
                                @csrf
                                <input type="hidden" name="sucursal_id" value="{{ $sucursal->id }}">
                                <button type="submit"
                                    class="w-full text-left px-3 py-2 text-xs rounded-xl flex items-center justify-between transition {{ \App\Support\Tenancy\BranchContext::getId() === $sucursal->id ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                    <span class="truncate">{{ $sucursal->nombre }}</span>
                                    @if($sucursal->es_principal)
                                    <span class="ml-2 text-[10px] bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded">Principal</span>
                                    @endif
                                </button>
                            </form>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <!-- Campana de Notificaciones -->
                <a href="{{ route('notificaciones.index') }}" class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition" title="Notificaciones">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @php
                        $alertasPendientes = \App\Models\NotificacionSistema::noLeidas()->count();
                    @endphp
                    @if($alertasPendientes > 0)
                        <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                        </span>
                    @endif
                </a>

                <!-- User pill -->
                <div class="flex items-center pl-2 border-l border-slate-200 space-x-2">
                    <div class="h-8 w-8 rounded-xl bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-xs">
                        {{ substr(auth()->user()->name, 0, 2) }}
                    </div>
                    <div class="hidden md:block text-left">
                        <div class="text-xs font-bold text-slate-800 leading-tight">{{ auth()->user()->name }}</div>
                        <div class="text-[10px] text-slate-500 leading-tight">{{ auth()->user()->cargo ?? auth()->user()->roles->first()?->name }}</div>
                    </div>
                </div>
            </div>
        </header>
        @endauth

        <!-- Contenedor con Scroll -->
        <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8">
            <!-- Alertas Flash -->
            <div class="w-full max-w-[1680px] mx-auto">
                @if(session('success'))
                <div x-data="{ show: true }" x-show="show" class="mb-6 bg-emerald-50 border border-emerald-200 p-4 rounded-2xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-3 text-emerald-800 text-sm font-medium">
                        <svg class="h-5 w-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" type="button" class="text-emerald-500 hover:text-emerald-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                @endif

                @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 p-4 rounded-2xl shadow-sm">
                    <div class="flex items-start space-x-3 text-red-800 text-sm">
                        <svg class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="space-y-1">
                            @foreach($errors->all() as $err)
                            <div>{{ $err }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
            </div>

            @yield('content')
        </main>
    </div>

    @livewireScripts
</body>
</html>
