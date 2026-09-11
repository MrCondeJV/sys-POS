<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'POS Comercial') }} - @yield('title', 'Inicio')</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    <!-- PWA Manifest & Service Worker -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
    <!-- Vite: CSS y JS compilados con Tailwind CSS v4 -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $empresaActualLayout = \App\Support\Tenancy\CompanyContext::getCompany() ?? (auth()->check() ? auth()->user()->empresa : null);
        $colorTema = $empresaActualLayout?->configuraciones['color_primario'] ?? 'indigo';

        $mapaColoresTema = [
            'indigo'  => ['hex' => '#6366f1', 'hover' => '#4f46e5', 'focus' => 'rgba(99, 102, 241, 0.25)'],
            'blue'    => ['hex' => '#2563eb', 'hover' => '#1d4ed8', 'focus' => 'rgba(37, 99, 235, 0.25)'],
            'emerald' => ['hex' => '#059669', 'hover' => '#047857', 'focus' => 'rgba(5, 150, 105, 0.25)'],
            'violet'  => ['hex' => '#7c3aed', 'hover' => '#6d28d9', 'focus' => 'rgba(124, 58, 237, 0.25)'],
            'rose'    => ['hex' => '#e11d48', 'hover' => '#be123c', 'focus' => 'rgba(225, 29, 72, 0.25)'],
            'orange'  => ['hex' => '#ea580c', 'hover' => '#c2410c', 'focus' => 'rgba(234, 88, 12, 0.25)'],
            'amber'   => ['hex' => '#d97706', 'hover' => '#b45309', 'focus' => 'rgba(217, 119, 6, 0.25)'],
            'slate'   => ['hex' => '#334155', 'hover' => '#1e293b', 'focus' => 'rgba(51, 65, 85, 0.25)'],
        ];

        $temaConfig = $mapaColoresTema[$colorTema] ?? $mapaColoresTema['indigo'];
    @endphp

    <!-- Alpine.js es provisto automáticamente por Livewire -->
    <style>
        :root {
            --theme-primary: {{ $temaConfig['hex'] }};
            --theme-primary-hover: {{ $temaConfig['hover'] }};
            --theme-primary-focus: {{ $temaConfig['focus'] }};
        }

        /* Utilidades temáticas dinámicas */
        .bg-theme-primary {
            background-color: var(--theme-primary) !important;
        }
        .text-theme-primary {
            color: var(--theme-primary) !important;
        }
        .border-theme-primary {
            border-color: var(--theme-primary) !important;
        }

        /* Enlaces activos en Sidebar de escritorio y móvil */
        aside nav a.bg-indigo-600,
        div[role="dialog"] nav a.bg-indigo-600 {
            background-color: var(--theme-primary) !important;
            color: #ffffff !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        /* Botones primarios con acento de la empresa */
        button.bg-indigo-600,
        a.bg-indigo-600:not(nav a) {
            background-color: var(--theme-primary) !important;
        }
        button.bg-indigo-600:hover,
        a.bg-indigo-600:hover:not(nav a) {
            background-color: var(--theme-primary-hover) !important;
        }

        [x-cloak] { display: none !important; }

        /* ==========================================================================
           Scrollbar del Sidebar — Fino y elegante
           ========================================================================== */
        aside nav::-webkit-scrollbar {
            width: 4px;
        }
        aside nav::-webkit-scrollbar-track {
            background: transparent;
        }
        aside nav::-webkit-scrollbar-thumb {
            background-color: #334155; /* slate-700 */
            border-radius: 9999px;
        }
        aside nav::-webkit-scrollbar-thumb:hover {
            background-color: #475569; /* slate-600 */
        }
        aside nav {
            scrollbar-width: thin;
            scrollbar-color: #334155 transparent;
        }

        /* ==========================================================================
           Cursor Pointer Global — Todos los elementos interactivos/clickeables
           ========================================================================== */
        button,
        [type="button"],
        [type="submit"],
        [type="reset"],
        [role="button"],
        label[for],
        label.cursor-pointer,
        summary,
        [x-on\:click],
        [onclick],
        [wire\:click],
        [@click],
        a[href] {
            cursor: pointer;
        }

        button:disabled,
        [type="button"]:disabled,
        [type="submit"]:disabled,
        [type="reset"]:disabled,
        button[disabled],
        .disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* ==========================================================================
           Estilos Modernos para Dropdowns / Select Boxes (Customizable Select)
           ========================================================================== */
        select,
        select::picker(select) {
            appearance: base-select;
        }

        select {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-color: #ffffff !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 0.75rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.15em 1.15em !important;
            padding-top: 0.55rem !important;
            padding-bottom: 0.55rem !important;
            padding-left: 0.875rem !important;
            padding-right: 2.5rem !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.75rem !important;
            color: #1e293b !important;
            font-size: 0.8125rem !important;
            line-height: 1.5rem !important;
            font-weight: 500 !important;
            min-height: 2.5rem !important;
            width: 100%;
            display: block;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
            cursor: pointer;
        }

        select:hover {
            border-color: #94a3b8 !important;
        }

        select:focus,
        select:focus-visible {
            outline: none !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18) !important;
            background-color: #ffffff !important;
        }

        /* Menú flotante (dropdown) en navegadores con soporte Customizable Select */
        select::picker(select) {
            border-radius: 0.875rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.08);
            padding: 0.375rem;
            background-color: #ffffff;
            color: #1e293b;
            font-family: inherit;
        }

        select option {
            padding: 0.55rem 0.85rem;
            font-size: 0.8125rem;
            color: #334155;
            background-color: #ffffff;
            border-radius: 0.5rem;
            margin: 2px 0;
            cursor: pointer;
        }

        select option:checked,
        select option:hover {
            background-color: #eef2ff !important;
            color: #4f46e5 !important;
            font-weight: 600;
        }

        /* ==========================================================================
           Inputs y Textareas Globales — Coherentes con los selects
           ========================================================================== */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="search"],
        input[type="tel"],
        input[type="url"],
        input[type="date"],
        input[type="datetime-local"],
        input[type="time"],
        textarea {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.75rem !important;
            color: #1e293b !important;
            font-size: 0.8125rem !important;
            line-height: 1.5rem !important;
            font-weight: 500 !important;
            padding-top: 0.55rem !important;
            padding-bottom: 0.55rem !important;
            padding-left: 0.875rem;
            padding-right: 0.875rem;
            min-height: 2.5rem !important;
            width: 100%;
            display: block;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        /* ==========================================================================
           Inputs con Iconos a la Izquierda o Derecha (Buscadores, filtros, etc.)
           Garantiza separación adecuada para que el texto nunca quede sobre el icono
           ========================================================================== */
        .relative:has(> [class*="left-0"]) input,
        .relative:has(> [class*="left-1"]) input,
        .relative:has(> [class*="left-2"]) input,
        .relative:has(> [class*="left-3"]) input,
        .relative:has(> [class*="left-4"]) input,
        .relative:has(> .pointer-events-none) input,
        input.pl-8, input[class*="pl-8"],
        input.pl-9, input[class*="pl-9"],
        input.pl-10, input[class*="pl-10"],
        input.pl-11, input[class*="pl-11"],
        input.pl-12, input[class*="pl-12"],
        input.pl-14, input[class*="pl-14"],
        input[data-icon-left] {
            padding-left: 2.75rem !important;
        }

        .relative:has(> [class*="right-0"]) input,
        input.pr-8, input[class*="pr-8"],
        input.pr-9, input[class*="pr-9"],
        input.pr-10, input[class*="pr-10"],
        input.pr-11, input[class*="pr-11"],
        input.pr-12, input[class*="pr-12"],
        input.pr-14, input[class*="pr-14"] {
            padding-right: 2.75rem !important;
        }

        input.pr-24, input[class*="pr-24"],
        input.pr-28, input[class*="pr-28"] {
            padding-right: 7rem !important;
        }

        input[type="text"]:hover,
        input[type="email"]:hover,
        input[type="password"]:hover,
        input[type="number"]:hover,
        input[type="search"]:hover,
        input[type="tel"]:hover,
        input[type="date"]:hover,
        input[type="datetime-local"]:hover,
        input[type="time"]:hover,
        textarea:hover {
            border-color: #94a3b8 !important;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="number"]:focus,
        input[type="search"]:focus,
        input[type="tel"]:focus,
        input[type="date"]:focus,
        input[type="datetime-local"]:focus,
        input[type="time"]:focus,
        textarea:focus {
            outline: none !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18) !important;
        }

        textarea {
            resize: vertical;
            min-height: 5rem !important;
        }

        /* Excepción: campos compactos para contextos de espacio reducido (ej: POS fila de pagos) */
        select.select-compact,
        input.input-compact {
            padding-top: 0.3rem !important;
            padding-bottom: 0.3rem !important;
            padding-left: 0.5rem !important;
            min-height: 1.875rem !important;
            font-size: 0.75rem !important;
            border-radius: 0.5rem !important;
        }

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
    <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-slate-900 text-slate-300 flex-shrink-0 border-r border-slate-800 h-screen sticky top-0 overflow-hidden">
        <!-- Brand Header -->
        <div class="h-16 flex items-center px-5 bg-slate-950 border-b border-slate-800">
            @if($empresaActualLayout?->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresaActualLayout->logo_path))
                <div class="h-10 w-10 flex-shrink-0 bg-white rounded-xl p-1 shadow-md mr-3 flex items-center justify-center overflow-hidden border border-slate-700">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($empresaActualLayout->logo_path) }}" alt="{{ $empresaActualLayout->nombre_comercial }}" class="h-full w-full object-contain">
                </div>
            @else
                <div class="h-10 w-10 flex-shrink-0 bg-theme-primary rounded-xl flex items-center justify-center text-white font-black text-sm tracking-wider shadow-md shadow-slate-950/40 mr-3">
                    {{ mb_strtoupper(mb_substr($empresaActualLayout?->nombre_comercial ?? 'POS', 0, 2)) }}
                </div>
            @endif
            <div class="truncate">
                <span class="font-bold text-white tracking-wide block truncate text-sm" title="{{ $empresaActualLayout?->nombre_comercial ?? 'POS Comercial' }}">
                    {{ $empresaActualLayout?->nombre_comercial ?? 'POS Comercial' }}
                </span>
                <span class="text-xs text-slate-400 block truncate font-mono">
                    NIT: {{ $empresaActualLayout?->nit ? $empresaActualLayout->nit . ($empresaActualLayout->dv ? '-' . $empresaActualLayout->dv : '') : 'Global' }}
                </span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 min-h-0 px-4 py-6 space-y-1.5 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">
            <a href="{{ route('dashboard') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>

            @if(auth()->user()->isSuperAdmin())
            <div class="pt-4 pb-1 text-xs font-semibold text-purple-400 uppercase tracking-wider px-3 flex items-center justify-between">
                <span>Plataforma SaaS</span>
                <span class="text-[10px] bg-purple-950 text-purple-300 px-1.5 py-0.5 rounded border border-purple-800">Super Admin</span>
            </div>

            <a href="{{ route('empresas.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('empresas.*') ? 'bg-purple-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Comercios / Empresas
            </a>
            @endif

            @canany(['empresa.gestionar', 'sucursales.gestionar', 'usuarios.ver', 'usuarios.gestionar'])
            <div class="pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">
                Gestión Empresarial
            </div>

            @can('empresa.gestionar')
            <a href="{{ route('empresa.perfil') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('empresa.perfil') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Perfil de Empresa
            </a>
            @endcan

            @canany(['usuarios.ver', 'usuarios.gestionar'])
            <a href="{{ route('usuarios.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('usuarios.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Usuarios y Roles
            </a>
            @endcanany

            @can('sucursales.gestionar')
            <a href="{{ route('sucursales.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('sucursales.index') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Sucursales
            </a>

            <a href="{{ route('traslados.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('traslados.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Traslados Sucursal
            </a>
            @endcan
            @endcanany

            @canany(['productos.ver', 'listas_precios.ver', 'inventario.ver'])
            <div class="pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">
                Inventario & Productos
            </div>

            @can('productos.ver')
            <a href="{{ route('productos.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('productos.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                Productos
            </a>
            @endcan

            @can('listas_precios.ver')
            <a href="{{ route('listas-precios.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('listas-precios.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                Listas de Precios
            </a>
            @endcan

            @can('inventario.ver')
            <a href="{{ route('inventario.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('inventario.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Inventario & Kardex
            </a>
            @endcan

            @can('productos.ver')
            <a href="{{ route('catalogos.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('catalogos.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                Catálogos Auxiliares
            </a>
            @endcan
            @endcanany

            @canany(['compras.ver', 'proveedores.ver'])
            <div class="pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">
                Compras & Proveedores
            </div>

            @can('compras.ver')
            <a href="{{ route('compras.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('compras.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Compras / Facturas
            </a>
            @endcan

            @can('proveedores.ver')
            <a href="{{ route('proveedores.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('proveedores.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Proveedores
            </a>
            @endcan
            @endcanany

            @canany(['ventas.ver', 'ventas.devolver', 'documentos.ver', 'clientes.ver', 'cartera.ver'])
            <div class="pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">
                Ventas & Clientes
            </div>

            @can('ventas.ver')
            <a href="{{ route('ventas.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('ventas.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Ventas Realizadas
            </a>
            @endcan

            @can('ventas.devolver')
            <a href="{{ route('devoluciones.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('devoluciones.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                </svg>
                Devoluciones
            </a>
            @endcan

            @can('documentos.ver')
            <a href="{{ route('documentos.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('documentos.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Documentos de Venta
            </a>
            @endcan

            @can('clientes.ver')
            <a href="{{ route('clientes.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('clientes.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Clientes
            </a>
            @endcan

            @can('cartera.ver')
            <a href="{{ route('cartera.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('cartera.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                Crédito & Cartera
            </a>
            @endcan
            @endcanany

            @canany(['caja.ver', 'ventas.crear'])
            <div class="pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">
                Operaciones & Caja
            </div>

            @can('caja.ver')
            <a href="{{ route('cajas.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('cajas.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Cajas & Turnos
            </a>
            @endcan

            @can('ventas.crear')
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
            @endcan
            @endcanany

            @canany(['reportes.ver', 'auditoria.ver', 'impuestos.ver'])
            <div class="pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">
                Analítica & Negocio
            </div>

            @can('reportes.ver')
            <a href="{{ route('reportes.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('reportes.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Centro de Reportes
            </a>
            @endcan

            @can('auditoria.ver')
            <a href="{{ route('auditoria.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('auditoria.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Auditoría & Logs
            </a>
            @endcan

            @can('impuestos.ver')
            <a href="{{ route('impuestos.index') }}"
                class="flex items-center px-3.5 py-2.5 text-sm font-medium rounded-xl transition {{ request()->routeIs('impuestos.*') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                </svg>
                Impuestos & Tarifas
            </a>
            @endcan
            @endcanany

            @canany(['documentos.ver', 'documentos.emitir'])
            <div class="pt-4 pb-1 px-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">DIAN & Fiscal</span>
            </div>

            @can('documentos.ver')
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
            @endcan
            @endcanany
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
                    <div class="flex items-center space-x-3 truncate mr-2">
                        @if($empresaActualLayout?->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresaActualLayout->logo_path))
                            <div class="h-9 w-9 flex-shrink-0 bg-white rounded-xl p-1 shadow-sm flex items-center justify-center overflow-hidden border border-slate-700">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($empresaActualLayout->logo_path) }}" alt="{{ $empresaActualLayout->nombre_comercial }}" class="h-full w-full object-contain">
                            </div>
                        @else
                            <div class="h-9 w-9 flex-shrink-0 bg-theme-primary rounded-xl flex items-center justify-center text-white font-bold text-xs">
                                {{ mb_strtoupper(mb_substr($empresaActualLayout?->nombre_comercial ?? 'POS', 0, 2)) }}
                            </div>
                        @endif
                        <span class="font-bold text-white text-sm truncate">{{ $empresaActualLayout?->nombre_comercial ?? 'POS Comercial' }}</span>
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

                    @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('empresas.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl text-purple-300 hover:bg-slate-800">
                        🏢 Comercios / Empresas
                    </a>
                    @endif

                    @can('empresa.gestionar')
                    <a href="{{ route('empresa.perfil') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-slate-800">
                        Perfil de Empresa
                    </a>
                    @endcan

                    @canany(['usuarios.ver', 'usuarios.gestionar'])
                    <a href="{{ route('usuarios.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-slate-800">
                        👥 Usuarios y Roles
                    </a>
                    @endcanany

                    @can('sucursales.gestionar')
                    <a href="{{ route('sucursales.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-slate-800">
                        Sucursales
                    </a>
                    <a href="{{ route('traslados.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-slate-800">
                        Traslados Sucursal
                    </a>
                    @endcan


                    @can('productos.ver')
                    <a href="{{ route('productos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('productos.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Productos
                    </a>
                    @endcan

                    @can('listas_precios.ver')
                    <a href="{{ route('listas-precios.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('listas-precios.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Listas de Precios
                    </a>
                    @endcan

                    @can('inventario.ver')
                    <a href="{{ route('inventario.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('inventario.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Inventario & Kardex
                    </a>
                    @endcan

                    @can('productos.ver')
                    <a href="{{ route('catalogos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('catalogos.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Catálogos Auxiliares
                    </a>
                    @endcan

                    @can('compras.ver')
                    <a href="{{ route('compras.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('compras.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Compras / Facturas
                    </a>
                    @endcan

                    @can('proveedores.ver')
                    <a href="{{ route('proveedores.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('proveedores.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Proveedores
                    </a>
                    @endcan

                    @can('ventas.ver')
                    <a href="{{ route('ventas.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('ventas.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Ventas Realizadas
                    </a>
                    @endcan

                    @can('ventas.devolver')
                    <a href="{{ route('devoluciones.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('devoluciones.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Devoluciones
                    </a>
                    @endcan

                    @can('documentos.ver')
                    <a href="{{ route('documentos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('documentos.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Documentos de Venta
                    </a>
                    @endcan

                    @can('clientes.ver')
                    <a href="{{ route('clientes.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('clientes.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Clientes
                    </a>
                    @endcan

                    @can('cartera.ver')
                    <a href="{{ route('cartera.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('cartera.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Crédito & Cartera
                    </a>
                    @endcan

                    @can('caja.ver')
                    <a href="{{ route('cajas.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('cajas.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        Cajas & Turnos
                    </a>
                    @endcan

                    @can('ventas.crear')
                    <a href="{{ route('pos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('pos.*') ? 'bg-emerald-600 text-white' : 'text-emerald-400 hover:bg-slate-800' }}">
                        ⚡ Terminal POS (Ventas Rápidas)
                    </a>
                    @endcan

                    @can('reportes.ver')
                    <a href="{{ route('reportes.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('reportes.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        📊 Centro de Reportes
                    </a>
                    @endcan

                    @can('auditoria.ver')
                    <a href="{{ route('auditoria.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('auditoria.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        🛡️ Auditoría & Logs
                    </a>
                    @endcan

                    @can('impuestos.ver')
                    <a href="{{ route('impuestos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('impuestos.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        🏷️ Impuestos & Tarifas
                    </a>
                    @endcan

                    @can('documentos.ver')
                    <a href="{{ route('facturacion-electronica.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('facturacion-electronica.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        ⚡ Facturación Electrónica
                    </a>
                    <a href="{{ route('resoluciones.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('resoluciones.*') ? 'bg-indigo-600 text-white' : 'text-white hover:bg-slate-800' }}">
                        📋 Resoluciones DIAN
                    </a>
                    @endcan
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
            <!-- Left: Mobile Menu Button & Brand Header Indicator -->
            <div class="flex items-center space-x-3">
                <button type="button" @click="mobileMenuOpen = true" class="lg:hidden p-2 text-slate-600 hover:text-slate-900 rounded-xl hover:bg-slate-100 transition">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="flex items-center space-x-3">
                    @if($empresaActualLayout?->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresaActualLayout->logo_path))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($empresaActualLayout->logo_path) }}" 
                             alt="{{ $empresaActualLayout->nombre_comercial }}" 
                             class="h-8 sm:h-9 w-auto max-w-[160px] object-contain rounded-lg p-0.5 bg-white border border-slate-200/80 shadow-xs">
                    @endif
                    <div class="leading-tight">
                        <span class="text-sm font-bold text-slate-900 block truncate max-w-[200px] sm:max-w-[320px]">
                            {{ $empresaActualLayout?->nombre_comercial ?? 'POS Comercial' }}
                        </span>
                        @if($empresaActualLayout?->nit)
                            <span class="text-[11px] text-slate-400 font-mono hidden sm:block">
                                NIT: {{ $empresaActualLayout->nit }}{{ $empresaActualLayout->dv ? '-' . $empresaActualLayout->dv : '' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right: Tenant Selector (SuperAdmin), Branch Selector & Profile -->
            <div class="flex items-center space-x-3 sm:space-x-4">
                <!-- Selector de Empresa Activa (Super Admin) -->
                @if(auth()->user()->isSuperAdmin())
                @php
                    $todasEmpresasActivas = \App\Models\Empresa::withoutGlobalScopes()->where('estado', \App\Enums\EstadoGeneral::ACTIVO->value)->get();
                @endphp
                @if($todasEmpresasActivas->count() > 0)
                <div class="relative" x-data="{ openEmpresa: false }">
                    <button @click="openEmpresa = !openEmpresa" type="button"
                        class="inline-flex items-center px-3 py-1.5 text-xs sm:text-sm font-semibold rounded-xl bg-purple-50 text-purple-800 hover:bg-purple-100 border border-purple-200 transition"
                        title="Alternar empresa activa">
                        <svg class="h-4 w-4 mr-1.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="truncate max-w-[120px] sm:max-w-[180px]">
                            {{ \App\Support\Tenancy\CompanyContext::getCompany()?->nombre_comercial ?? 'Cambiar Comercio' }}
                        </span>
                        <svg class="h-3.5 w-3.5 ml-1 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-cloak x-show="openEmpresa" @click.away="openEmpresa = false"
                        class="absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 p-2 z-50 border border-slate-100">
                        <div class="px-3 py-1.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                            Alternar Empresa Activa
                        </div>
                        <div class="mt-1 space-y-1 max-h-60 overflow-y-auto">
                            @foreach($todasEmpresasActivas as $empItem)
                            <form action="{{ route('empresas.seleccionar') }}" method="POST">
                                @csrf
                                <input type="hidden" name="empresa_id" value="{{ $empItem->id }}">
                                <button type="submit"
                                    class="w-full text-left px-3 py-2 text-xs rounded-xl flex items-center justify-between transition {{ \App\Support\Tenancy\CompanyContext::getId() === $empItem->id ? 'bg-purple-50 text-purple-700 font-bold' : 'text-slate-700 hover:bg-slate-50' }}">
                                    <span class="truncate">{{ $empItem->nombre_comercial }}</span>
                                    <span class="text-[10px] text-slate-400 ml-1">{{ $empItem->nit }}</span>
                                </button>
                            </form>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                @endif

                <!-- Selector de Sucursal Activa -->
                @php
                    $empresaActualHeader = \App\Support\Tenancy\CompanyContext::getCompany() ?? auth()->user()->empresa;
                @endphp
                @if($empresaActualHeader && $empresaActualHeader->sucursales->count() > 0)
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
                            @foreach($empresaActualHeader->sucursales as $sucursal)
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

    {{-- =====================================================================
         Auto-scroll del Sidebar al ítem activo
         Cada vez que se carga una página, el nav del sidebar hace scroll
         para mostrar el link activo (el que tiene bg-indigo-600, bg-emerald-600
         o bg-purple-600), evitando que el menú vuelva al inicio.
         ===================================================================== --}}
    <script>
        (function () {
            const nav = document.querySelector('aside nav');
            if (!nav) return;

            // Buscar el link activo por las clases de fondo que usa el layout
            const activeLink = nav.querySelector(
                'a.bg-indigo-600, a.bg-emerald-600, a.bg-purple-600'
            );

            if (activeLink) {
                // Centrar el ítem activo en el área visible del nav
                activeLink.scrollIntoView({ block: 'center', behavior: 'instant' });
            }
        })();

        /* =====================================================================
           Búsqueda Dinámica en Tiempo Real (Live Search As-You-Type)
           Filtra automáticamente a medida que el usuario escribe (debounce 300ms)
           sin recargar la página y preservando el foco y posición del cursor.
           ===================================================================== */
        (function () {
            let searchDebounceTimer = null;
            let searchAbortController = null;

            const searchInputSelector = 'input[name="buscar"], input[name="search"], input[name="q"]';

            function esFormularioBusqueda(input) {
                const form = input.closest('form');
                return form && form.method.toUpperCase() === 'GET';
            }

            async function ejecutarBusquedaDinamica(form, inputActual) {
                const mainContainer = document.querySelector('main');
                if (!mainContainer) return;

                // Abortar petición previa si el usuario continúa escribiendo
                if (searchAbortController) {
                    searchAbortController.abort();
                }
                searchAbortController = new AbortController();

                // Recopilar parámetros del formulario
                const formData = new FormData(form);
                const params = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value !== '' && value !== null) {
                        params.append(key, value);
                    }
                }

                const actionUrl = form.getAttribute('action') || window.location.pathname;
                const targetUrl = actionUrl + (params.toString() ? '?' + params.toString() : '');

                // Indicador visual de búsqueda en curso
                const iconContainer = inputActual.closest('.relative')?.querySelector('.pointer-events-none');
                if (iconContainer) {
                    iconContainer.classList.add('animate-pulse', 'text-indigo-600');
                }

                try {
                    const response = await fetch(targetUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal: searchAbortController.signal
                    });

                    if (!response.ok) throw new Error('Respuesta no satisfactoria');

                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newMain = doc.querySelector('main');

                    if (newMain) {
                        const inputName = inputActual.getAttribute('name');
                        const cursorPos = inputActual.selectionStart;
                        const scrollY = mainContainer.scrollTop;

                        // Reemplazo limpio del contenido de resultados
                        mainContainer.innerHTML = newMain.innerHTML;

                        // Sincronizar URL del navegador
                        window.history.replaceState({}, '', targetUrl);

                        // Restaurar foco y cursor en el input de búsqueda
                        const newInput = mainContainer.querySelector(`input[name="${inputName}"]`);
                        if (newInput) {
                            newInput.focus();
                            if (cursorPos !== null) {
                                try {
                                    newInput.setSelectionRange(cursorPos, cursorPos);
                                } catch (err) {}
                            }
                        }

                        // Preservar scroll del área de trabajo
                        mainContainer.scrollTop = scrollY;
                    }
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.warn('Búsqueda dinámica fallback:', error);
                    }
                } finally {
                    if (iconContainer) {
                        iconContainer.classList.remove('animate-pulse', 'text-indigo-600');
                    }
                }
            }

            // 1. Detección mientras escribe (evento input con debounce)
            document.addEventListener('input', function (e) {
                const target = e.target;
                if (!target.matches || !target.matches(searchInputSelector)) return;
                const form = target.closest('form');
                if (!form || !esFormularioBusqueda(target)) return;

                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => {
                    ejecutarBusquedaDinamica(form, target);
                }, 300);
            });

            // 2. Tecla Enter: buscar inmediatamente sin recargar
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    const target = e.target;
                    if (!target.matches || !target.matches(searchInputSelector)) return;
                    const form = target.closest('form');
                    if (!form || !esFormularioBusqueda(target)) return;

                    e.preventDefault();
                    clearTimeout(searchDebounceTimer);
                    ejecutarBusquedaDinamica(form, target);
                }
            });

            // 3. Cambios en filtros auxiliares del formulario (selects, checkboxes)
            document.addEventListener('change', function (e) {
                const target = e.target;
                if (!target.matches || !target.matches('form select, form input[type="checkbox"]')) return;
                const form = target.closest('form');
                if (!form || form.method.toUpperCase() !== 'GET') return;
                
                if (form.querySelector(searchInputSelector)) {
                    const searchInput = form.querySelector(searchInputSelector);
                    clearTimeout(searchDebounceTimer);
                    ejecutarBusquedaDinamica(form, searchInput || target);
                }
            });
        })();
    </script>
</body>
</html>
