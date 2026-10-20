@php
    $rolRealSpatie = auth()->user()->roles->first()?->name
        ? str_replace('_', ' ', auth()->user()->roles->first()->name)
        : 'Usuario';
@endphp

<!-- Contenedor del Sidebar para Escritorio (Reserva espacio fijo de 80px en POS o 256px/80px en vistas regulares) -->
<div class="hidden lg:block relative flex-shrink-0 h-full"
     :class="posMode && !pinned ? 'lg:w-20' : (sidebarCollapsed ? 'lg:w-20' : 'lg:w-64')">
    <!-- Sidebar para Escritorio con soporte Overlay en POS -->
    <aside @mouseenter="enter()" @mouseleave="leave()"
           @focusin="enter()" @focusout="if (!$el.contains($event.relatedTarget)) leave()"
           @click="if ($event.target.closest('a')) leaveNow()"
           @click.outside="leaveNow()"
           @keydown.escape.window="leaveNow()"
           :class="{
               'sidebar-collapsed': !expanded,
               'pos-sidebar': posMode,
               'overflow-hidden': posMode || expanded,
               'overflow-visible': !posMode && !expanded,
               'absolute inset-y-0 left-0 z-40': posMode && !pinned,
               'shadow-xl': posMode && !pinned && hoverOpen
           }"
           :style="posMode && !pinned ? 'width:' + (expanded ? '16rem' : '5rem') : ''"
           class="w-full flex flex-col bg-brand-900 text-slate-300 flex-shrink-0 border-r border-brand-700/60 h-full transition-[width] duration-200 ease-out">
        <!-- Brand Header -->
        <div class="h-16 flex items-center px-4 bg-brand-800 border-b border-brand-700/60 sidebar-brand-container justify-between flex-shrink-0">
            <div class="flex items-center min-w-0">
                @if(auth()->user()->isSuperAdmin() && !\App\Support\Tenancy\CompanyContext::check())
                    <div class="h-9 w-9 flex-shrink-0 bg-brand-700 rounded-lg flex items-center justify-center text-white font-bold text-xs tracking-wider shadow-xs mr-3 sidebar-logo">
                        SA
                    </div>
                    <div class="truncate whitespace-nowrap sidebar-brand-text">
                        <span class="font-semibold text-white tracking-wide block truncate whitespace-nowrap text-sm" title="Super Admin SaaS">
                            Super Admin
                        </span>
                        <span class="text-xs text-slate-400 block truncate whitespace-nowrap font-mono">
                            Plataforma Global
                        </span>
                    </div>
                @elseif($empresaActualLayout?->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresaActualLayout->logo_path))
                    <div class="h-9 w-9 flex-shrink-0 bg-surface rounded-lg p-1 shadow-xs mr-3 sidebar-logo flex items-center justify-center overflow-hidden border border-brand-700">
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($empresaActualLayout->logo_path) }}" alt="{{ $empresaActualLayout->nombre_comercial }}" class="h-full w-full object-contain">
                    </div>
                    <div class="truncate whitespace-nowrap sidebar-brand-text">
                        <span class="font-semibold text-white tracking-wide block truncate whitespace-nowrap text-sm" title="{{ $empresaActualLayout->nombre_comercial }}">
                            {{ $empresaActualLayout->nombre_comercial }}
                        </span>
                        <span class="text-xs text-slate-400 block truncate whitespace-nowrap font-mono tabular-nums">
                            NIT: {{ $empresaActualLayout->nit ? $empresaActualLayout->nit . ($empresaActualLayout->dv ? '-' . $empresaActualLayout->dv : '') : 'Global' }}
                        </span>
                    </div>
                @else
                    <div class="h-9 w-9 flex-shrink-0 bg-brand-700 rounded-lg flex items-center justify-center text-white font-bold text-xs tracking-wider shadow-xs mr-3 sidebar-logo">
                        {{ mb_strtoupper(mb_substr($empresaActualLayout?->nombre_comercial ?? 'POS', 0, 2)) }}
                    </div>
                    <div class="truncate whitespace-nowrap sidebar-brand-text">
                        <span class="font-semibold text-white tracking-wide block truncate whitespace-nowrap text-sm" title="{{ $empresaActualLayout?->nombre_comercial ?? 'POS Comercial' }}">
                            {{ $empresaActualLayout?->nombre_comercial ?? 'POS Comercial' }}
                        </span>
                        <span class="text-xs text-slate-400 block truncate whitespace-nowrap font-mono tabular-nums">
                            NIT: {{ $empresaActualLayout?->nit ? $empresaActualLayout->nit . ($empresaActualLayout->dv ? '-' . $empresaActualLayout->dv : '') : 'Global' }}
                        </span>
                    </div>
                @endif
            </div>
            <!-- Botón de colapso rápido dentro del sidebar -->
            <button type="button" @click="toggleSidebar()"
                    class="hidden lg:flex sidebar-brand-text p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-white/[0.05] transition cursor-pointer"
                    :title="posMode ? (pinned ? 'Desfijar menú lateral' : 'Fijar menú lateral') : 'Minimizar menú lateral'"
                    aria-label="Minimizar menú lateral">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
            </button>
        </div>

        <!-- Menú único compartido -->
        @include('layouts.partials.sidebar-nav')

        <!-- Pie del Sidebar: Usuario, Rol Real (Spatie) y Conexión Segura -->
        <div class="p-3 border-t border-brand-700/60 bg-brand-800/80 flex items-center justify-between sidebar-footer flex-shrink-0">
            <div class="truncate whitespace-nowrap mr-2 sidebar-user-info">
                <div class="text-xs font-semibold text-white truncate whitespace-nowrap">{{ auth()->user()->name }}</div>
                <div class="text-[11px] text-slate-400 truncate whitespace-nowrap flex items-center gap-1.5 mt-0.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-success flex-shrink-0" aria-hidden="true"></span>
                    <span class="truncate whitespace-nowrap">{{ $rolRealSpatie }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="p-2 text-slate-400 hover:text-white transition rounded-lg hover:bg-white/[0.05] cursor-pointer" title="Cerrar sesión" aria-label="Cerrar sesión">
                    <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
        <div class="px-3 py-1.5 bg-brand-900 border-t border-brand-800 flex items-center justify-between text-[11px] text-slate-400 sidebar-footer flex-shrink-0">
            <div class="flex items-center gap-1.5 whitespace-nowrap sidebar-user-info">
                <svg class="w-3.5 h-3.5 text-success flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span class="sidebar-text whitespace-nowrap font-medium text-slate-300">Conexión segura</span>
            </div>
            <span class="sidebar-badge whitespace-nowrap text-[10px] text-slate-400 tabular-nums font-mono">TLS</span>
        </div>
    </aside>
</div>

<!-- Drawer Móvil / Tablet (< 1024px) — Reutiliza exactamente el mismo sidebar-nav -->
<div x-cloak x-show="mobileMenuOpen" data-mobile-drawer="true" class="relative z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Menú de navegación">
    <div x-show="mobileMenuOpen"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-brand-900/75 backdrop-blur-xs"
         @click="mobileMenuOpen = false"></div>

    <div class="fixed inset-0 flex">
        <div x-show="mobileMenuOpen"
             x-data="{ expanded: true }"
             x-transition:enter="transition ease-in-out duration-250 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-250 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="relative mr-16 flex w-full max-w-xs flex-1 flex-col bg-brand-900 text-slate-300">
            <!-- Cabecera Móvil -->
            <div class="h-16 flex items-center justify-between px-4 bg-brand-800 border-b border-brand-700/60 flex-shrink-0">
                <div class="flex items-center gap-3 truncate mr-2">
                    @if(auth()->user()->isSuperAdmin() && !\App\Support\Tenancy\CompanyContext::check())
                        <div class="h-8 w-8 flex-shrink-0 bg-brand-700 rounded-lg flex items-center justify-center text-white font-bold text-xs">
                            SA
                        </div>
                        <span class="font-semibold text-white text-sm truncate">Super Admin</span>
                    @elseif($empresaActualLayout?->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($empresaActualLayout->logo_path))
                        <div class="h-8 w-8 flex-shrink-0 bg-surface rounded-lg p-1 shadow-xs flex items-center justify-center overflow-hidden border border-brand-700">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($empresaActualLayout->logo_path) }}" alt="{{ $empresaActualLayout->nombre_comercial }}" class="h-full w-full object-contain">
                        </div>
                        <span class="font-semibold text-white text-sm truncate">{{ $empresaActualLayout->nombre_comercial }}</span>
                    @else
                        <div class="h-8 w-8 flex-shrink-0 bg-brand-700 rounded-lg flex items-center justify-center text-white font-bold text-xs">
                            {{ mb_strtoupper(mb_substr($empresaActualLayout?->nombre_comercial ?? 'POS', 0, 2)) }}
                        </div>
                        <span class="font-semibold text-white text-sm truncate">{{ $empresaActualLayout?->nombre_comercial ?? 'POS Comercial' }}</span>
                    @endif
                </div>
                <button type="button" class="p-1.5 text-slate-400 hover:text-white rounded-lg hover:bg-white/[0.05]" @click="mobileMenuOpen = false" aria-label="Cerrar menú">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Menú único compartido -->
            @include('layouts.partials.sidebar-nav')

            <!-- Pie Móvil -->
            <div class="p-3 border-t border-brand-700/60 bg-brand-800/80 flex items-center justify-between flex-shrink-0">
                <div class="truncate mr-2">
                    <div class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</div>
                    <div class="text-[11px] text-slate-400 truncate flex items-center gap-1.5 mt-0.5">
                        <span class="h-1.5 w-1.5 rounded-full bg-success flex-shrink-0" aria-hidden="true"></span>
                        <span class="truncate">{{ $rolRealSpatie }}</span>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-white transition rounded-lg hover:bg-white/[0.05] cursor-pointer" title="Cerrar sesión" aria-label="Cerrar sesión">
                        <svg class="w-[18px] h-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
