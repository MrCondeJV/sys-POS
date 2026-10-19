<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'POS Comercial') }} - @yield('title', 'Inicio')</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <!-- PWA Manifest & Service Worker -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0B1F36">
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
        $colorTema = $empresaActualLayout?->configuraciones['color_primario'] ?? 'blue';

        $mapaColoresTema = [
            'indigo'  => ['hex' => '#2563eb', 'hover' => '#1d4ed8', 'focus' => 'rgba(37, 99, 235, 0.25)'],
            'blue'    => ['hex' => '#2563eb', 'hover' => '#1d4ed8', 'focus' => 'rgba(37, 99, 235, 0.25)'],
            'emerald' => ['hex' => '#059669', 'hover' => '#047857', 'focus' => 'rgba(5, 150, 105, 0.25)'],
            'violet'  => ['hex' => '#2563eb', 'hover' => '#1d4ed8', 'focus' => 'rgba(37, 99, 235, 0.25)'],
            'rose'    => ['hex' => '#e11d48', 'hover' => '#be123c', 'focus' => 'rgba(225, 29, 72, 0.25)'],
            'orange'  => ['hex' => '#ea580c', 'hover' => '#c2410c', 'focus' => 'rgba(234, 88, 12, 0.25)'],
            'amber'   => ['hex' => '#d97706', 'hover' => '#b45309', 'focus' => 'rgba(217, 119, 6, 0.25)'],
            'slate'   => ['hex' => '#334155', 'hover' => '#1e293b', 'focus' => 'rgba(51, 65, 85, 0.25)'],
        ];

        $temaConfig = $mapaColoresTema[$colorTema] ?? $mapaColoresTema['blue'];
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
           Control Estricto de Scrollbar Único — Cero doble barra de desplazamiento
           ========================================================================== */
        html, body {
            height: 100%;
            overflow: hidden !important;
        }

        /* Scrollbar de Contenido Principal — Moderno y sutil */
        main::-webkit-scrollbar {
            width: 7px;
        }
        main::-webkit-scrollbar-track {
            background: transparent;
        }
        main::-webkit-scrollbar-thumb {
            background-color: #cbd5e1; /* slate-300 */
            border-radius: 9999px;
        }
        main::-webkit-scrollbar-thumb:hover {
            background-color: #94a3b8; /* slate-400 */
        }
        main {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }

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
            border-radius: 0.5rem !important;
            color: #0f172a !important;
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
            border-color: #1d4ed8 !important;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.18) !important;
            background-color: #ffffff !important;
        }

        /* Menú flotante (dropdown) en navegadores con soporte Customizable Select */
        select::picker(select) {
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.12), 0 8px 10px -6px rgba(15, 23, 42, 0.08);
            padding: 0.375rem;
            background-color: #ffffff;
            color: #0f172a;
            font-family: inherit;
        }

        select option {
            padding: 0.55rem 0.85rem;
            font-size: 0.8125rem;
            color: #334155;
            background-color: #ffffff;
            border-radius: 0.375rem;
            margin: 2px 0;
            cursor: pointer;
        }

        select option:checked,
        select option:hover {
            background-color: #eff6ff !important;
            color: #1d4ed8 !important;
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
            border-radius: 0.5rem !important;
            color: #0f172a !important;
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
            border-color: #1d4ed8 !important;
            box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.18) !important;
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

        /* ==========================================================================
           Sidebar Colapsable / Minimizable (Desktop)
           ========================================================================== */
        aside.sidebar-collapsed {
            width: 4.75rem !important;
        }
        aside.sidebar-collapsed .sidebar-text,
        aside.sidebar-collapsed .sidebar-brand-text,
        aside.sidebar-collapsed .sidebar-user-info,
        aside.sidebar-collapsed .sidebar-badge {
            display: none !important;
        }
        aside.sidebar-collapsed .sidebar-section-header {
            display: none !important;
        }
        aside.sidebar-collapsed .sidebar-section-divider {
            display: block !important;
        }
        aside.sidebar-collapsed .sidebar-brand-container {
            justify-content: center !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }
        aside.sidebar-collapsed .sidebar-logo {
            margin-right: 0 !important;
        }
        aside.sidebar-collapsed a.sidebar-link {
            justify-content: center !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }
        aside.sidebar-collapsed a.sidebar-link svg {
            margin-right: 0 !important;
        }
        aside.sidebar-collapsed .sidebar-footer {
            justify-content: center !important;
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }
        /* Tooltip flotante en hover cuando el sidebar está minimizado */
        aside.sidebar-collapsed a.sidebar-link {
            position: relative;
        }
        aside.sidebar-collapsed a.sidebar-link:hover::after {
            content: attr(data-title);
            position: absolute;
            left: calc(100% + 0.6rem);
            top: 50%;
            transform: translateY(-50%);
            background-color: #020617;
            color: #f8fafc;
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.5rem;
            white-space: nowrap;
            z-index: 100;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -4px rgba(0, 0, 0, 0.2);
            border: 1px solid #334155;
            pointer-events: none;
        }
    </style>
    @yield('styles')
    @livewireStyles
</head>
<body class="h-full font-sans antialiased text-[#0F172A] bg-[#F4F6F9] flex overflow-hidden" 
      x-data="{ 
          mobileMenuOpen: false,
          posMode: {{ request()->routeIs('pos.*') ? 'true' : 'false' }},
          sidebarCollapsed: {{ request()->routeIs('pos.*') ? 'true' : 'false' }},
          hoverOpen: false,
          pinned: false,
          _openT: null,
          _closeT: null,
          get expanded() {
              if (this.posMode) {
                  return this.pinned || this.hoverOpen;
              }
              return !this.sidebarCollapsed;
          },
          enter() {
              if (!this.posMode || window.innerWidth < 1024 || this.pinned) return;
              if (document.querySelector('[role=\'dialog\'][aria-modal=\'true\']:not([style*=\'display: none\'])')) return;
              clearTimeout(this._closeT);
              this._openT = setTimeout(() => this.hoverOpen = true, 120);
          },
          leave() {
              if (!this.posMode || this.pinned) return;
              clearTimeout(this._openT);
              this._closeT = setTimeout(() => this.hoverOpen = false, 250);
          },
          leaveNow() {
              clearTimeout(this._openT);
              clearTimeout(this._closeT);
              this.hoverOpen = false;
          },
          toggleSidebar() {
              if (window.innerWidth < 1024) {
                  this.mobileMenuOpen = !this.mobileMenuOpen;
              } else if (this.posMode) {
                  this.pinned = !this.pinned;
                  this.sidebarCollapsed = !this.pinned;
              } else {
                  this.sidebarCollapsed = !this.sidebarCollapsed;
              }
          }
      }">

    @auth
    <!-- Contenedor del Sidebar para Escritorio (Reserva espacio fijo en el flujo) -->
    <div class="hidden lg:block relative flex-shrink-0 h-full"
         :class="posMode && !pinned ? 'lg:w-20' : (sidebarCollapsed ? 'lg:w-20' : 'lg:w-64')">
        <!-- Sidebar para Escritorio con soporte Overlay en POS -->
        <aside @mouseenter="enter()" @mouseleave="leave()"
               @focusin="enter()" @keydown.escape.window="leaveNow()"
               :class="{
                   'sidebar-collapsed': !expanded,
                   'absolute inset-y-0 left-0 z-40': posMode && !pinned,
                   'shadow-[8px_0_24px_-12px_rgba(11,31,54,0.45)]': posMode && !pinned && hoverOpen
               }"
               :style="posMode && !pinned ? 'width:' + (expanded ? '16rem' : '5rem') : ''"
               class="w-full flex flex-col bg-[#0B1F36] text-slate-300 flex-shrink-0 border-r border-[#1E3A5F]/60 h-full overflow-hidden transition-[width] duration-200 ease-out">
        <!-- Brand Header -->
        <div class="h-16 flex items-center px-4 bg-[#081729] border-b border-[#1E3A5F]/60 sidebar-brand-container justify-between">
            <div class="flex items-center min-w-0">
                @if(auth()->user()->isSuperAdmin() && !\App\Support\Tenancy\CompanyContext::check())
                    <div class="h-9 w-9 flex-shrink-0 bg-[#1E3A5F] rounded-lg flex items-center justify-center text-white font-bold text-xs tracking-wider shadow-xs mr-3 sidebar-logo">
                        SA
                    </div>
                    <div class="truncate sidebar-brand-text">
                        <span class="font-bold text-white tracking-wide block truncate text-sm" title="Super Admin SaaS">
                            Super Admin
                        </span>
                        <span class="text-xs text-[#94A3B8] block truncate font-mono">
                            Plataforma Global
                        </span>
                    </div>
                @elseif($empresaActualLayout?->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresaActualLayout->logo_path))
                    <div class="h-9 w-9 flex-shrink-0 bg-white rounded-lg p-1 shadow-xs mr-3 sidebar-logo flex items-center justify-center overflow-hidden border border-[#1E3A5F]">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($empresaActualLayout->logo_path) }}" alt="{{ $empresaActualLayout->nombre_comercial }}" class="h-full w-full object-contain">
                    </div>
                    <div class="truncate sidebar-brand-text">
                        <span class="font-bold text-white tracking-wide block truncate text-sm" title="{{ $empresaActualLayout->nombre_comercial }}">
                            {{ $empresaActualLayout->nombre_comercial }}
                        </span>
                        <span class="text-xs text-[#94A3B8] block truncate font-mono">
                            NIT: {{ $empresaActualLayout->nit ? $empresaActualLayout->nit . ($empresaActualLayout->dv ? '-' . $empresaActualLayout->dv : '') : 'Global' }}
                        </span>
                    </div>
                @else
                    <div class="h-9 w-9 flex-shrink-0 bg-theme-primary rounded-lg flex items-center justify-center text-white font-bold text-xs tracking-wider shadow-xs mr-3 sidebar-logo">
                        {{ mb_strtoupper(mb_substr($empresaActualLayout?->nombre_comercial ?? 'POS', 0, 2)) }}
                    </div>
                    <div class="truncate sidebar-brand-text">
                        <span class="font-bold text-white tracking-wide block truncate text-sm" title="{{ $empresaActualLayout?->nombre_comercial ?? 'POS Comercial' }}">
                            {{ $empresaActualLayout?->nombre_comercial ?? 'POS Comercial' }}
                        </span>
                        <span class="text-xs text-[#94A3B8] block truncate font-mono">
                            NIT: {{ $empresaActualLayout->nit ? $empresaActualLayout->nit . ($empresaActualLayout->dv ? '-' . $empresaActualLayout->dv : '') : 'Global' }}
                        </span>
                    </div>
                @endif
            </div>
            <!-- Botón de colapso rápido dentro del sidebar -->
            <button type="button" @click="sidebarCollapsed = !sidebarCollapsed"
                    class="hidden lg:flex sidebar-brand-text p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-white/[0.05] transition cursor-pointer"
                    title="Minimizar menú lateral">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 min-h-0 px-3 py-4 space-y-1 overflow-y-auto scrollbar-thin scrollbar-thumb-[#1E3A5F] scrollbar-track-transparent"
             x-data="{
                 openSections: (function() {
                     try {
                         const saved = localStorage.getItem('sidebar_accordion');
                         if (saved) return JSON.parse(saved);
                     } catch(e) {}
                     return {
                         gestion: {{ request()->routeIs('empresa.*', 'usuarios.*', 'sucursales.*', 'traslados.*') ? 'true' : 'false' }},
                         inventario: {{ request()->routeIs('productos.*', 'listas-precios.*', 'inventario.*', 'catalogos.*') ? 'true' : 'false' }},
                         compras: {{ request()->routeIs('compras.*', 'proveedores.*') ? 'true' : 'false' }},
                         ventas: {{ request()->routeIs('ventas.*', 'devoluciones.*', 'documentos.*', 'clientes.*', 'cartera.*') ? 'true' : 'false' }},
                         caja: {{ request()->routeIs('cajas.*', 'pos.*') ? 'true' : 'false' }},
                         analitica: {{ request()->routeIs('reportes.*', 'auditoria.*', 'impuestos.*') ? 'true' : 'false' }},
                         dian: {{ request()->routeIs('facturacion-electronica.*', 'resoluciones.*') ? 'true' : 'false' }}
                     };
                 })(),
                 toggleSection(sec) {
                     this.openSections[sec] = !this.openSections[sec];
                     try {
                         localStorage.setItem('sidebar_accordion', JSON.stringify(this.openSections));
                     } catch(e) {}
                 }
             }">
            @if(auth()->user()->isSuperAdmin() && !\App\Support\Tenancy\CompanyContext::check())
            {{-- MODO SAAS SUPER ADMIN (NAVBAR LIMPIO) --}}
            <div class="sidebar-section-header pt-2 pb-1 text-[11px] font-bold text-[#64748B] uppercase tracking-wider px-3 flex items-center justify-between">
                <span>Plataforma SaaS</span>
                <span class="text-[10px] bg-[#1E3A5F] text-[#93C5FD] px-1.5 py-0.5 rounded font-mono font-medium sidebar-badge">Global</span>
            </div>
            <div class="sidebar-section-divider hidden my-2 border-t border-[#1E3A5F]/60 mx-2" title="Plataforma SaaS"></div>

            <a href="{{ route('empresas.index') }}"
                class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('empresas.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                data-title="Comercios / Empresas">
                <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('empresas.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="sidebar-text truncate">Comercios / Empresas</span>
            </a>

            <a href="{{ route('usuarios.index') }}"
                class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('usuarios.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                data-title="Usuarios de Plataforma">
                <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('usuarios.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span class="sidebar-text truncate">Usuarios de Plataforma</span>
            </a>

            <a href="{{ route('auditoria.index') }}"
                class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('auditoria.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                data-title="Auditoría Global">
                <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('auditoria.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span class="sidebar-text truncate">Auditoría Global</span>
            </a>
            @else
            {{-- MODO OPERATIVO DE NEGOCIO (USUARIO TENANT O SUPER ADMIN EN MODO SOPORTE) --}}
            @if(auth()->user()->isSuperAdmin() && \App\Support\Tenancy\CompanyContext::check())
            <div class="px-2 pb-3 mb-2 border-b border-[#1E3A5F]/60">
                <form action="{{ route('empresas.salir') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center px-3 py-2 text-xs font-semibold rounded-lg bg-[#1E3A5F] text-[#93C5FD] border border-[#3B82F6]/30 hover:bg-[#2563EB] hover:text-white transition shadow-xs group cursor-pointer"
                        title="Salir del negocio y volver al panel principal de Super Admin">
                        <svg class="h-3.5 w-3.5 mr-2 text-[#93C5FD] group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                        </svg>
                        <span class="truncate">Salir del Negocio</span>
                    </button>
                </form>
            </div>
            @endif

            <a href="{{ route('dashboard') }}"
                class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                data-title="Dashboard">
                <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="sidebar-text truncate">Dashboard</span>
            </a>

            @canany(['empresa.gestionar', 'sucursales.gestionar', 'usuarios.ver', 'usuarios.gestionar'])
            <button type="button" @click="toggleSection('gestion')"
                class="sidebar-section-header w-full pt-3 pb-1 text-[11px] font-semibold text-[#64748B] uppercase tracking-wider px-2 flex items-center justify-between hover:text-[#94A3B8] transition cursor-pointer">
                <span>Gestión Empresarial</span>
                <svg class="h-3.5 w-3.5 text-[#64748B] transition-transform duration-200"
                     :class="{ 'rotate-180': openSections.gestion, '': !openSections.gestion }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div class="sidebar-section-divider hidden my-2 border-t border-[#1E3A5F]/60 mx-2" title="Gestión Empresarial"></div>

            <div x-show="sidebarCollapsed || openSections.gestion" class="space-y-1">
                @can('empresa.gestionar')
                <a href="{{ route('empresa.perfil') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('empresa.perfil') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Perfil de Empresa">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('empresa.perfil') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="sidebar-text truncate">Perfil de Empresa</span>
                </a>
                @endcan

                @canany(['usuarios.ver', 'usuarios.gestionar'])
                <a href="{{ route('usuarios.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('usuarios.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Usuarios y Roles">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('usuarios.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="sidebar-text truncate">Usuarios y Roles</span>
                </a>
                @endcanany

                @can('sucursales.gestionar')
                <a href="{{ route('sucursales.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('sucursales.index') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Sucursales">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('sucursales.index') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="sidebar-text truncate">Sucursales</span>
                </a>

                <a href="{{ route('traslados.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('traslados.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Traslados Sucursal">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('traslados.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span class="sidebar-text truncate">Traslados Sucursal</span>
                </a>
                @endcan
            </div>
            @endcanany

            @canany(['productos.ver', 'listas_precios.ver', 'inventario.ver'])
            <button type="button" @click="toggleSection('inventario')"
                class="sidebar-section-header w-full pt-3 pb-1 text-[11px] font-semibold text-[#64748B] uppercase tracking-wider px-2 flex items-center justify-between hover:text-[#94A3B8] transition cursor-pointer">
                <span>Inventario & Catálogo</span>
                <svg class="h-3.5 w-3.5 text-[#64748B] transition-transform duration-200"
                     :class="{ 'rotate-180': openSections.inventario, '': !openSections.inventario }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div class="sidebar-section-divider hidden my-2 border-t border-[#1E3A5F]/60 mx-2" title="Inventario & Catálogo"></div>

            <div x-show="sidebarCollapsed || openSections.inventario" class="space-y-1">
                @can('productos.ver')
                <a href="{{ route('productos.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('productos.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Productos">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('productos.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span class="sidebar-text truncate">Productos</span>
                </a>
                @endcan

                @can('listas_precios.ver')
                <a href="{{ route('listas-precios.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('listas-precios.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Listas de Precios">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('listas-precios.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span class="sidebar-text truncate">Listas de Precios</span>
                </a>
                @endcan

                @can('inventario.ver')
                <a href="{{ route('inventario.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('inventario.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Inventario & Kardex">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('inventario.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span class="sidebar-text truncate">Inventario & Kardex</span>
                </a>
                @endcan

                @can('productos.ver')
                <a href="{{ route('catalogos.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('catalogos.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Catálogos Auxiliares">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('catalogos.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span class="sidebar-text truncate">Catálogos Auxiliares</span>
                </a>
                @endcan
            </div>
            @endcanany

            @canany(['compras.ver', 'proveedores.ver'])
            <button type="button" @click="toggleSection('compras')"
                class="sidebar-section-header w-full pt-3 pb-1 text-[11px] font-semibold text-[#64748B] uppercase tracking-wider px-2 flex items-center justify-between hover:text-[#94A3B8] transition cursor-pointer">
                <span>Compras & Proveedores</span>
                <svg class="h-3.5 w-3.5 text-[#64748B] transition-transform duration-200"
                     :class="{ 'rotate-180': openSections.compras, '': !openSections.compras }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div class="sidebar-section-divider hidden my-2 border-t border-[#1E3A5F]/60 mx-2" title="Compras & Proveedores"></div>

            <div x-show="sidebarCollapsed || openSections.compras" class="space-y-1">
                @can('compras.ver')
                <a href="{{ route('compras.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('compras.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Compras / Facturas">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('compras.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="sidebar-text truncate">Compras / Facturas</span>
                </a>
                @endcan

                @can('proveedores.ver')
                <a href="{{ route('proveedores.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('proveedores.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Proveedores">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('proveedores.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="sidebar-text truncate">Proveedores</span>
                </a>
                @endcan
            </div>
            @endcanany

            @canany(['ventas.ver', 'ventas.devolver', 'documentos.ver', 'clientes.ver', 'cartera.ver'])
            <button type="button" @click="toggleSection('ventas')"
                class="sidebar-section-header w-full pt-3 pb-1 text-[11px] font-semibold text-[#64748B] uppercase tracking-wider px-2 flex items-center justify-between hover:text-[#94A3B8] transition cursor-pointer">
                <span>Ventas & Clientes</span>
                <svg class="h-3.5 w-3.5 text-[#64748B] transition-transform duration-200"
                     :class="{ 'rotate-180': openSections.ventas, '': !openSections.ventas }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div class="sidebar-section-divider hidden my-2 border-t border-[#1E3A5F]/60 mx-2" title="Ventas & Clientes"></div>

            <div x-show="sidebarCollapsed || openSections.ventas" class="space-y-1">
                @can('ventas.ver')
                <a href="{{ route('ventas.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('ventas.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Ventas Realizadas">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('ventas.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span class="sidebar-text truncate">Ventas Realizadas</span>
                </a>
                @endcan

                @can('ventas.devolver')
                <a href="{{ route('devoluciones.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('devoluciones.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Devoluciones">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('devoluciones.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                    </svg>
                    <span class="sidebar-text truncate">Devoluciones</span>
                </a>
                @endcan

                @can('documentos.ver')
                <a href="{{ route('documentos.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('documentos.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Documentos de Venta">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('documentos.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="sidebar-text truncate">Documentos de Venta</span>
                </a>
                @endcan

                @can('clientes.ver')
                <a href="{{ route('clientes.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('clientes.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Clientes">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('clientes.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="sidebar-text truncate">Clientes</span>
                </a>
                @endcan

                @can('cartera.ver')
                <a href="{{ route('cartera.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('cartera.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Crédito & Cartera">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('cartera.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span class="sidebar-text truncate">Crédito & Cartera</span>
                </a>
                @endcan
            </div>
            @endcanany

            @canany(['caja.ver', 'ventas.crear'])
            <button type="button" @click="toggleSection('caja')"
                class="sidebar-section-header w-full pt-3 pb-1 text-[11px] font-semibold text-[#64748B] uppercase tracking-wider px-2 flex items-center justify-between hover:text-[#94A3B8] transition cursor-pointer">
                <span>Caja & Operaciones</span>
                <svg class="h-3.5 w-3.5 text-[#64748B] transition-transform duration-200"
                     :class="{ 'rotate-180': openSections.caja, '': !openSections.caja }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div class="sidebar-section-divider hidden my-2 border-t border-[#1E3A5F]/60 mx-2" title="Caja & Operaciones"></div>

            <div x-show="sidebarCollapsed || openSections.caja" class="space-y-1">
                @can('caja.ver')
                <a href="{{ route('cajas.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('cajas.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Cajas & Turnos">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('cajas.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="sidebar-text truncate">Cajas & Turnos</span>
                </a>
                @endcan

                @can('ventas.crear')
                <a href="{{ route('pos.index') }}"
                    class="sidebar-link flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('pos.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Terminal POS">
                    <span class="flex items-center min-w-0">
                        <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('pos.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span class="sidebar-text truncate">Terminal POS</span>
                    </span>
                    <span class="sidebar-badge text-[10px] bg-[#1E3A5F] text-[#93C5FD] font-medium px-1.5 py-0.5 rounded">Rápido</span>
                </a>
                @endcan
            </div>
            @endcanany

            @canany(['reportes.ver', 'auditoria.ver', 'impuestos.ver'])
            <button type="button" @click="toggleSection('analitica')"
                class="sidebar-section-header w-full pt-3 pb-1 text-[11px] font-semibold text-[#64748B] uppercase tracking-wider px-2 flex items-center justify-between hover:text-[#94A3B8] transition cursor-pointer">
                <span>Analítica & Negocio</span>
                <svg class="h-3.5 w-3.5 text-[#64748B] transition-transform duration-200"
                     :class="{ 'rotate-180': openSections.analitica, '': !openSections.analitica }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div class="sidebar-section-divider hidden my-2 border-t border-[#1E3A5F]/60 mx-2" title="Analítica & Negocio"></div>

            <div x-show="sidebarCollapsed || openSections.analitica" class="space-y-1">
                @can('reportes.ver')
                <a href="{{ route('reportes.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('reportes.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Centro de Reportes">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('reportes.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span class="sidebar-text truncate">Centro de Reportes</span>
                </a>
                @endcan

                @can('auditoria.ver')
                <a href="{{ route('auditoria.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('auditoria.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Auditoría & Logs">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('auditoria.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span class="sidebar-text truncate">Auditoría & Logs</span>
                </a>
                @endcan

                @can('impuestos.ver')
                <a href="{{ route('impuestos.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('impuestos.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Impuestos & Tarifas">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('impuestos.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                    </svg>
                    <span class="sidebar-text truncate">Impuestos & Tarifas</span>
                </a>
                @endcan
            </div>
            @endcanany

            @canany(['documentos.ver', 'documentos.emitir'])
            <button type="button" @click="toggleSection('dian')"
                class="sidebar-section-header w-full pt-3 pb-1 text-[11px] font-semibold text-[#64748B] uppercase tracking-wider px-2 flex items-center justify-between hover:text-[#94A3B8] transition cursor-pointer">
                <span>DIAN & Fiscal</span>
                <svg class="h-3.5 w-3.5 text-[#64748B] transition-transform duration-200"
                     :class="{ 'rotate-180': openSections.dian, '': !openSections.dian }"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div class="sidebar-section-divider hidden my-2 border-t border-[#1E3A5F]/60 mx-2" title="DIAN & Fiscal"></div>

            <div x-show="sidebarCollapsed || openSections.dian" class="space-y-1">
                @can('documentos.ver')
                <a href="{{ route('facturacion-electronica.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('facturacion-electronica.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Facturación Electrónica">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('facturacion-electronica.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span class="sidebar-text truncate">Facturación Electrónica</span>
                </a>

                <a href="{{ route('resoluciones.index') }}"
                    class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('resoluciones.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:text-white hover:bg-white/[0.05]' }}"
                    data-title="Resoluciones DIAN">
                    <svg class="h-4 w-4 mr-3 flex-shrink-0 {{ request()->routeIs('resoluciones.*') ? 'text-[#3B82F6]' : 'text-[#64748B]' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="sidebar-text truncate">Resoluciones DIAN</span>
                </a>
                @endcan
            </div>
            @endcanany
            @endif
        </nav>

        <!-- User footer -->
        <div class="p-3 border-t border-[#1E3A5F]/60 bg-[#081729] flex items-center justify-between sidebar-footer">
            <div class="truncate mr-2 sidebar-user-info">
                <div class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                <div class="text-[11px] text-[#94A3B8] truncate flex items-center gap-1.5 mt-0.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-[#10B981]"></span>
                    <span>{{ auth()->user()->roles->first()?->name ?? 'Usuario' }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="p-2 text-[#64748B] hover:text-red-400 transition rounded-lg hover:bg-white/[0.05] cursor-pointer" title="Cerrar sesión">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
        <!-- Indicador de Conexión Segura -->
        <div class="px-3 py-1.5 bg-[#061220] border-t border-[#1E3A5F]/40 flex items-center justify-between text-[11px] text-[#64748B] sidebar-footer">
            <div class="flex items-center gap-1.5 sidebar-user-info">
                <svg class="h-3 w-3 text-[#10B981] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span class="sidebar-text font-medium text-[#94A3B8]">Conexión segura</span>
            </div>
            <span class="sidebar-badge text-[10px] text-[#64748B] tabular-nums font-mono">v1.2</span>
        </div>
    </aside>
    </div>

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
                  class="relative mr-16 flex w-full max-w-xs flex-1 flex-col bg-[#0B1F36] pt-5 pb-4">
                <div class="flex items-center justify-between px-6 pb-4 border-b border-[#1E3A5F]/60">
                    <div class="flex items-center space-x-3 truncate mr-2">
                        @if(auth()->user()->isSuperAdmin() && !\App\Support\Tenancy\CompanyContext::check())
                            <div class="h-8 w-8 flex-shrink-0 bg-[#1E3A5F] rounded-lg flex items-center justify-center text-white font-bold text-xs">
                                SA
                            </div>
                            <span class="font-bold text-white text-sm truncate">Super Admin</span>
                        @elseif($empresaActualLayout?->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresaActualLayout->logo_path))
                            <div class="h-8 w-8 flex-shrink-0 bg-white rounded-lg p-1 shadow-xs flex items-center justify-center overflow-hidden border border-[#1E3A5F]">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($empresaActualLayout->logo_path) }}" alt="{{ $empresaActualLayout->nombre_comercial }}" class="h-full w-full object-contain">
                            </div>
                            <span class="font-bold text-white text-sm truncate">{{ $empresaActualLayout->nombre_comercial }}</span>
                        @else
                            <div class="h-8 w-8 flex-shrink-0 bg-theme-primary rounded-lg flex items-center justify-center text-white font-bold text-xs">
                                {{ mb_strtoupper(mb_substr($empresaActualLayout?->nombre_comercial ?? 'POS', 0, 2)) }}
                            </div>
                            <span class="font-bold text-white text-sm truncate">{{ $empresaActualLayout?->nombre_comercial ?? 'POS Comercial' }}</span>
                        @endif
                    </div>
                    <button type="button" class="text-slate-400 hover:text-white" @click="mobileMenuOpen = false">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <nav class="mt-4 px-4 space-y-1 overflow-y-auto flex-1">
                    @if(auth()->user()->isSuperAdmin() && !\App\Support\Tenancy\CompanyContext::check())
                    {{-- MENÚ MÓVIL SUPER ADMIN SAAS LIMPIO --}}
                    <div class="px-3 pt-2 pb-1 text-[11px] font-bold text-[#64748B] uppercase tracking-wider">
                        Plataforma SaaS
                    </div>
                    <a href="{{ route('empresas.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('empresas.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:bg-white/[0.05] hover:text-white' }}">
                        Comercios / Empresas
                    </a>
                    <a href="{{ route('usuarios.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('usuarios.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:bg-white/[0.05] hover:text-white' }}">
                        Usuarios de Plataforma
                    </a>
                    <a href="{{ route('auditoria.index') }}" class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('auditoria.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-[#2563EB] pl-[9px]' : 'text-[#94A3B8] hover:bg-white/[0.05] hover:text-white' }}">
                        Auditoría Global
                    </a>
                    @else
                    {{-- MENÚ MÓVIL OPERATIVO (TENANT O MODO SOPORTE) --}}
                    @if(auth()->user()->isSuperAdmin() && \App\Support\Tenancy\CompanyContext::check())
                    <div class="pb-2 mb-2 border-b border-[#1E3A5F]/60">
                        <form action="{{ route('empresas.salir') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center py-2 px-3 rounded-lg bg-[#1E3A5F] text-[#93C5FD] border border-[#3B82F6]/30 hover:bg-[#2563EB] hover:text-white font-semibold text-xs transition">
                                Salir al Panel Super Admin
                            </button>
                        </form>
                    </div>
                    @endif

                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl text-white hover:bg-slate-800">
                        Dashboard
                    </a>

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
                    <a href="{{ route('productos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('productos.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Productos
                    </a>
                    @endcan

                    @can('listas_precios.ver')
                    <a href="{{ route('listas-precios.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('listas-precios.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Listas de Precios
                    </a>
                    @endcan

                    @can('inventario.ver')
                    <a href="{{ route('inventario.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('inventario.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Inventario & Kardex
                    </a>
                    @endcan

                    @can('productos.ver')
                    <a href="{{ route('catalogos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('catalogos.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Catálogos Auxiliares
                    </a>
                    @endcan

                    @can('compras.ver')
                    <a href="{{ route('compras.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('compras.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Compras / Facturas
                    </a>
                    @endcan

                    @can('proveedores.ver')
                    <a href="{{ route('proveedores.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('proveedores.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Proveedores
                    </a>
                    @endcan

                    @can('ventas.ver')
                    <a href="{{ route('ventas.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('ventas.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Ventas Realizadas
                    </a>
                    @endcan

                    @can('ventas.devolver')
                    <a href="{{ route('devoluciones.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('devoluciones.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Devoluciones
                    </a>
                    @endcan

                    @can('documentos.ver')
                    <a href="{{ route('documentos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('documentos.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Documentos de Venta
                    </a>
                    @endcan

                    @can('clientes.ver')
                    <a href="{{ route('clientes.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('clientes.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Clientes
                    </a>
                    @endcan

                    @can('cartera.ver')
                    <a href="{{ route('cartera.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('cartera.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Crédito & Cartera
                    </a>
                    @endcan

                    @can('caja.ver')
                    <a href="{{ route('cajas.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('cajas.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Cajas & Turnos
                    </a>
                    @endcan

                    @can('ventas.crear')
                    <a href="{{ route('pos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('pos.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-emerald-400 hover:bg-slate-800' }}">
                        Terminal POS (Ventas Rápidas)
                    </a>
                    @endcan

                    @can('reportes.ver')
                    <a href="{{ route('reportes.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('reportes.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Centro de Reportes
                    </a>
                    @endcan

                    @can('auditoria.ver')
                    <a href="{{ route('auditoria.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('auditoria.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Auditoría & Logs
                    </a>
                    @endcan

                    @can('impuestos.ver')
                    <a href="{{ route('impuestos.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('impuestos.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Impuestos & Tarifas
                    </a>
                    @endcan

                    @can('documentos.ver')
                    <a href="{{ route('facturacion-electronica.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('facturacion-electronica.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Facturación Electrónica
                    </a>
                    <a href="{{ route('resoluciones.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-xl {{ request()->routeIs('resoluciones.*') ? 'bg-brand-600 text-white font-semibold shadow-xs' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                        Resoluciones DIAN
                    </a>
                    @endcan
                    @endif
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
    <div class="flex-1 flex flex-col min-w-0 min-h-0 h-full overflow-hidden">
        @auth
        @if(auth()->user()->isSuperAdmin() && \App\Support\Tenancy\CompanyContext::check())
        <!-- Banner Superior: Modo Soporte Super Admin Activo (36px, Fondo Ámbar Suave) -->
        <div class="h-9 min-h-[36px] bg-[#FEF3C7] border-b border-[#FDE68A] text-[#92400E] px-4 sm:px-6 flex items-center justify-between text-xs font-medium z-20 flex-shrink-0">
            <div class="flex items-center space-x-2 truncate">
                <span class="sr-only">MODO SOPORTE SUPER ADMIN</span>
                <span class="inline-block w-2 h-2 rounded-full bg-[#D97706] flex-shrink-0"></span>
                <span class="truncate">
                    Estás gestionando <strong class="font-semibold text-[#78350F]">{{ $empresaActualLayout?->nombre_comercial ?? 'Comercio' }}</strong> como Super Administrador
                </span>
            </div>
            <form action="{{ route('empresas.salir') }}" method="POST" class="flex-shrink-0 ml-4">
                @csrf
                <button type="submit" 
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#92400E] hover:text-[#78350F] hover:underline transition cursor-pointer"
                    title="Salir al Panel Super Admin">
                    <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Volver al panel Super Admin</span>
                    <span class="sr-only">Salir al Panel Super Admin</span>
                </button>
            </form>
        </div>
        @endif

        <!-- Header superior universal -->
        <header class="bg-white border-b border-[#E2E8F0] h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-10 flex-shrink-0">
            <!-- Left: Botón Hamburguesa Universal & Brand / Breadcrumb -->
            <div class="flex items-center space-x-3">
                <button type="button" 
                        @click="toggleSidebar()" 
                        class="p-2 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100 transition flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 cursor-pointer"
                        :title="posMode ? (pinned ? 'Desfijar menú lateral' : 'Fijar menú lateral') : (sidebarCollapsed ? 'Maximizar menú lateral' : 'Minimizar menú lateral')"
                        id="btn-toggle-sidebar"
                        aria-label="Alternar menú lateral">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div class="flex items-center space-x-3">
                    @if(auth()->user()->isSuperAdmin() && !\App\Support\Tenancy\CompanyContext::check())
                        <div class="flex items-center gap-2 text-sm font-semibold text-[#0F172A]">
                            <span class="text-[#64748B] font-normal">Super Admin</span>
                            <span class="text-[#CBD5E1]">/</span>
                            <span>Plataforma Multiempresa</span>
                        </div>
                    @else
                        @if($empresaActualLayout?->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresaActualLayout->logo_path))
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($empresaActualLayout->logo_path) }}" 
                                 alt="{{ $empresaActualLayout->nombre_comercial }}" 
                                 class="h-8 sm:h-9 w-auto max-w-[160px] object-contain rounded-lg p-0.5 bg-white border border-slate-200/80 shadow-xs">
                        @endif
                        <div class="leading-tight">
                            <span class="text-sm font-bold text-[#0F172A] block truncate max-w-[200px] sm:max-w-[320px]">
                                {{ $empresaActualLayout?->nombre_comercial ?? 'POS Comercial' }}
                            </span>
                            @if($empresaActualLayout?->nit)
                                <span class="text-[11px] text-[#64748B] font-normal tabular-nums hidden sm:block">
                                    NIT: {{ $empresaActualLayout->nit }}{{ $empresaActualLayout->dv ? '-' . $empresaActualLayout->dv : '' }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right: Tenant Selector (SuperAdmin en modo soporte), Branch Selector & Profile -->
            <div class="flex items-center space-x-3 sm:space-x-4">
                @php
                    $todasEmpresasActivas = (auth()->user()->isSuperAdmin() && \App\Support\Tenancy\CompanyContext::check())
                        ? \App\Models\Empresa::withoutGlobalScopes()->where('estado', \App\Enums\EstadoGeneral::ACTIVO->value)->get()
                        : collect();
                    $empresaActualHeader = \App\Support\Tenancy\CompanyContext::getCompany() ?? auth()->user()->empresa;
                    $tieneEmpresas = $todasEmpresasActivas->count() > 0;
                    $tieneSucursales = $empresaActualHeader && $empresaActualHeader->sucursales->count() > 0;
                @endphp

                @if($tieneEmpresas || $tieneSucursales)
                <!-- Selector Compuesto y Neutro: Empresa y Sede -->
                <div class="inline-flex items-center bg-white border border-[#CBD5E1] rounded-lg shadow-2xs divide-x divide-[#CBD5E1] text-xs font-medium text-[#0F172A] h-9">
                    @if($tieneEmpresas)
                    <div class="relative h-full" x-data="{ openEmpresa: false }">
                        <button @click="openEmpresa = !openEmpresa" type="button"
                            class="h-full px-3 inline-flex items-center gap-1.5 hover:bg-slate-50 transition rounded-l-lg cursor-pointer"
                            title="Cambiar Comercio">
                            <svg class="h-4 w-4 text-[#64748B] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span class="truncate max-w-[120px] sm:max-w-[160px] text-[#0F172A]">
                                {{ \App\Support\Tenancy\CompanyContext::getCompany()?->nombre_comercial ?? 'Cambiar Comercio' }}
                            </span>
                            <svg class="h-3.5 w-3.5 text-[#64748B] ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-cloak x-show="openEmpresa" @click.away="openEmpresa = false"
                            class="absolute right-0 sm:left-0 sm:right-auto mt-1 w-64 rounded-xl bg-white shadow-lg ring-1 ring-black/5 p-1.5 z-50 border border-[#E2E8F0]">
                            <div class="px-2.5 py-1.5 text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">
                                Cambiar Comercio
                            </div>
                            <div class="space-y-0.5 max-h-60 overflow-y-auto">
                                @foreach($todasEmpresasActivas as $empItem)
                                <form action="{{ route('empresas.seleccionar') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="empresa_id" value="{{ $empItem->id }}">
                                    <button type="submit"
                                        class="w-full text-left px-2.5 py-1.5 text-xs rounded-lg flex items-center justify-between transition cursor-pointer {{ \App\Support\Tenancy\CompanyContext::getId() === $empItem->id ? 'bg-[#EFF6FF] text-[#1D4ED8] font-semibold' : 'text-[#334155] hover:bg-slate-50' }}">
                                        <span class="truncate">{{ $empItem->nombre_comercial }}</span>
                                        <span class="text-[10px] text-[#64748B] ml-1 tabular-nums">{{ $empItem->nit }}</span>
                                    </button>
                                </form>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($tieneSucursales)
                    <div class="relative h-full" x-data="{ openSucursal: false }">
                        <button @click="openSucursal = !openSucursal" type="button"
                            class="h-full px-3 inline-flex items-center gap-1.5 hover:bg-slate-50 transition {{ $tieneEmpresas ? 'rounded-r-lg' : 'rounded-lg' }} cursor-pointer"
                            title="Cambiar Sede Activa">
                            <svg class="h-4 w-4 text-[#64748B] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="truncate max-w-[120px] sm:max-w-[160px] text-[#0F172A]">
                                {{ \App\Support\Tenancy\BranchContext::getBranch()?->nombre ?? 'Seleccionar Sede' }}
                            </span>
                            <svg class="h-3.5 w-3.5 text-[#64748B] ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-cloak x-show="openSucursal" @click.away="openSucursal = false"
                            class="absolute right-0 mt-1 w-60 rounded-xl bg-white shadow-lg ring-1 ring-black/5 p-1.5 z-50 border border-[#E2E8F0]">
                            <div class="px-2.5 py-1.5 text-[11px] font-semibold text-[#64748B] uppercase tracking-wider">
                                Cambiar Sede Activa
                            </div>
                            <div class="space-y-0.5 max-h-60 overflow-y-auto">
                                @foreach($empresaActualHeader->sucursales as $sucursal)
                                <form action="{{ route('sucursales.seleccionar') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="sucursal_id" value="{{ $sucursal->id }}">
                                    <button type="submit"
                                        class="w-full text-left px-2.5 py-1.5 text-xs rounded-lg flex items-center justify-between transition cursor-pointer {{ \App\Support\Tenancy\BranchContext::getId() === $sucursal->id ? 'bg-[#EFF6FF] text-[#1D4ED8] font-semibold' : 'text-[#334155] hover:bg-slate-50' }}">
                                        <span class="truncate">{{ $sucursal->nombre }}</span>
                                        @if($sucursal->es_principal)
                                        <span class="ml-2 text-[10px] bg-slate-100 text-[#64748B] px-1.5 py-0.5 rounded font-medium">Principal</span>
                                        @endif
                                    </button>
                                </form>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Campana de Notificaciones -->
                <a href="{{ route('notificaciones.index') }}" class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition" title="Notificaciones">
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

                <!-- User Profile & Dropdown -->
                <div class="relative" x-data="{ openUserMenu: false }">
                    <button @click="openUserMenu = !openUserMenu" type="button"
                        class="flex items-center pl-3 border-l border-[#E2E8F0] space-x-2.5 hover:opacity-80 transition cursor-pointer focus:outline-none">
                        <div class="h-8 w-8 rounded-lg bg-[#1E3A5F] text-white font-bold flex items-center justify-center text-xs">
                            {{ mb_substr(auth()->user()->name, 0, 2) }}
                        </div>
                        <div class="hidden md:block text-left">
                            <div class="text-xs font-semibold text-[#0F172A] leading-tight">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] text-[#64748B] leading-tight">{{ auth()->user()->cargo ?? auth()->user()->roles->first()?->name }}</div>
                        </div>
                        <svg class="h-3.5 w-3.5 text-[#64748B] hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-cloak x-show="openUserMenu" @click.away="openUserMenu = false"
                        class="absolute right-0 mt-2 w-56 rounded-xl bg-white shadow-lg ring-1 ring-black/5 p-1.5 z-50 border border-[#E2E8F0]">
                        <div class="px-3 py-2 border-b border-[#F1F5F9] mb-1">
                            <div class="text-xs font-semibold text-[#0F172A] truncate">{{ auth()->user()->name }}</div>
                            <div class="text-[11px] text-[#64748B] truncate">{{ auth()->user()->email }}</div>
                            <div class="mt-1 flex items-center gap-1.5 text-[10px] text-[#64748B]">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#10B981]"></span>
                                <span>{{ auth()->user()->roles->first()?->name ?? 'Usuario' }}</span>
                            </div>
                        </div>

                        @if(auth()->user()->isSuperAdmin() && \App\Support\Tenancy\CompanyContext::check())
                        <form action="{{ route('empresas.salir') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-3 py-2 text-xs rounded-lg text-[#92400E] hover:bg-[#FEF3C7] flex items-center gap-2 transition cursor-pointer font-medium">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                                </svg>
                                <span>Salir del Negocio</span>
                            </button>
                        </form>
                        <div class="my-1 border-t border-[#F1F5F9]"></div>
                        @endif

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-3 py-2 text-xs rounded-lg text-red-600 hover:bg-red-50 flex items-center gap-2 transition cursor-pointer font-medium">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                <span>Cerrar Sesión</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>
        @endauth

        <!-- Contenedor con Scroll -->
        <main class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden p-4 sm:p-6 lg:p-8">
            <!-- Alertas Flash con auto-cierre en 5 segundos y animación fade -->
            <div class="w-full max-w-7xl mx-auto">
                @if(session('success'))
                <div x-data="{ show: true }"
                     x-init="setTimeout(() => show = false, 5000)"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition-all ease-in-out duration-700"
                     x-transition:leave-start="opacity-100 max-h-40 transform translate-y-0"
                     x-transition:leave-end="opacity-0 max-h-0 -translate-y-2 py-0 my-0 overflow-hidden"
                     class="mb-6 bg-emerald-50 border border-emerald-200 p-4 rounded-lg flex items-center justify-between shadow-xs transition-all">
                    <div class="flex items-center space-x-3 text-emerald-800 text-sm font-medium">
                        <svg class="h-5 w-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" type="button" class="text-emerald-500 hover:text-emerald-700 p-1 rounded-lg hover:bg-emerald-100/60 transition" title="Cerrar">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                @endif

                @if(session('error'))
                <div x-data="{ show: true }"
                     x-init="setTimeout(() => show = false, 5000)"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition-all ease-in-out duration-700"
                     x-transition:leave-start="opacity-100 max-h-40 transform translate-y-0"
                     x-transition:leave-end="opacity-0 max-h-0 -translate-y-2 py-0 my-0 overflow-hidden"
                     class="mb-6 bg-red-50 border border-red-200 p-4 rounded-lg flex items-center justify-between shadow-xs transition-all">
                    <div class="flex items-center space-x-3 text-red-800 text-sm font-medium">
                        <svg class="h-5 w-5 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" type="button" class="text-red-500 hover:text-red-700 p-1 rounded-lg hover:bg-red-100/60 transition" title="Cerrar">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                @endif

                @if(session('warning'))
                <div x-data="{ show: true }"
                     x-init="setTimeout(() => show = false, 5000)"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition-all ease-in-out duration-700"
                     x-transition:leave-start="opacity-100 max-h-40 transform translate-y-0"
                     x-transition:leave-end="opacity-0 max-h-0 -translate-y-2 py-0 my-0 overflow-hidden"
                     class="mb-6 bg-amber-50 border border-amber-200 p-4 rounded-lg flex items-center justify-between shadow-xs transition-all">
                    <div class="flex items-center space-x-3 text-amber-800 text-sm font-medium">
                        <svg class="h-5 w-5 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ session('warning') }}</span>
                    </div>
                    <button @click="show = false" type="button" class="text-amber-500 hover:text-amber-700 p-1 rounded-lg hover:bg-amber-100/60 transition" title="Cerrar">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                @endif

                @if(session('status') || session('info'))
                <div x-data="{ show: true }"
                     x-init="setTimeout(() => show = false, 5000)"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition-all ease-in-out duration-700"
                     x-transition:leave-start="opacity-100 max-h-40 transform translate-y-0"
                     x-transition:leave-end="opacity-0 max-h-0 -translate-y-2 py-0 my-0 overflow-hidden"
                     class="mb-6 bg-blue-50 border border-blue-200 p-4 rounded-lg flex items-center justify-between shadow-xs transition-all">
                    <div class="flex items-center space-x-3 text-blue-800 text-sm font-medium">
                        <svg class="h-5 w-5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('status') ?? session('info') }}</span>
                    </div>
                    <button @click="show = false" type="button" class="text-blue-500 hover:text-blue-700 p-1 rounded-lg hover:bg-blue-100/60 transition" title="Cerrar">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                @endif

                @if(isset($errors) && $errors->any())
                <div x-data="{ show: true }"
                     x-init="setTimeout(() => show = false, 7000)"
                     x-show="show"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition-all ease-in-out duration-700"
                     x-transition:leave-start="opacity-100 max-h-60 transform translate-y-0"
                     x-transition:leave-end="opacity-0 max-h-0 -translate-y-2 py-0 my-0 overflow-hidden"
                     class="mb-6 bg-red-50 border border-red-200 p-4 rounded-lg shadow-xs flex items-start justify-between transition-all">
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
                    <button @click="show = false" type="button" class="text-red-500 hover:text-red-700 p-1 rounded-lg hover:bg-red-100/60 transition ml-4" title="Cerrar">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
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

            // Buscar el link activo por las clases de fondo o borde que usa el layout
            const activeLink = nav.querySelector(
                'a.bg-brand-600, a.bg-indigo-600, a.bg-emerald-600, a.bg-purple-600, a[class*="border-[#2563EB]"], a[class*="border-brand"]'
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
                    iconContainer.classList.add('animate-pulse', 'text-brand-600');
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
                        iconContainer.classList.remove('animate-pulse', 'text-brand-600');
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
