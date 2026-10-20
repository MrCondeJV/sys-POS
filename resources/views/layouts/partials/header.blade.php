@php
    $todasEmpresasActivas = (auth()->user()->isSuperAdmin() && \App\Support\Tenancy\CompanyContext::check())
        ? \App\Models\Empresa::withoutGlobalScopes()->where('estado', \App\Enums\EstadoGeneral::ACTIVO->value)->get()
        : collect();
    $empresaActualHeader = \App\Support\Tenancy\CompanyContext::getCompany() ?? auth()->user()->empresa;
    $tieneEmpresas = $todasEmpresasActivas->count() > 0;
    $tieneSucursales = $empresaActualHeader && $empresaActualHeader->sucursales->count() > 0;
    $alertasPendientes = \App\Models\NotificacionSistema::noLeidas()->count();
    $rolRealHeader = auth()->user()->roles->first()?->name
        ? str_replace('_', ' ', auth()->user()->roles->first()->name)
        : 'Usuario';
@endphp

<!-- Header superior universal -->
<header class="bg-surface border-b border-border h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8 z-10 flex-shrink-0">
    <!-- Left: Botón Hamburguesa Universal & Empresa + NIT -->
    <div class="flex items-center gap-3 min-w-0">
        <button type="button"
                @click="toggleSidebar()"
                class="p-2 text-text-secondary hover:text-text-primary rounded-lg hover:bg-surface-muted transition flex items-center justify-center cursor-pointer flex-shrink-0"
                :title="posMode ? (pinned ? 'Desfijar menú lateral' : 'Fijar menú lateral') : (sidebarCollapsed ? 'Maximizar menú lateral' : 'Minimizar menú lateral')"
                id="btn-toggle-sidebar"
                aria-label="Alternar menú lateral">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <div class="flex items-center gap-3 min-w-0">
            @if(auth()->user()->isSuperAdmin() && !\App\Support\Tenancy\CompanyContext::check())
                <div class="flex items-center gap-2 text-sm font-semibold text-text-primary truncate">
                    <span class="text-text-secondary font-normal">Super Admin</span>
                    <span class="text-border-strong">/</span>
                    <span class="truncate">Plataforma Multiempresa</span>
                </div>
            @else
                <div class="leading-tight min-w-0">
                    <span class="text-sm font-semibold text-text-primary block truncate max-w-[200px] sm:max-w-[320px]">
                        {{ $empresaActualLayout?->nombre_comercial ?? 'POS Comercial' }}
                    </span>
                    @if($empresaActualLayout?->nit)
                        <span class="text-[11px] text-text-muted font-normal tabular-nums hidden sm:block">
                            NIT: {{ $empresaActualLayout->nit }}{{ $empresaActualLayout->dv ? '-' . $empresaActualLayout->dv : '' }}
                        </span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Right: Selector Compuesto Empresa/Sede, Notificaciones y Menú de Usuario -->
    <div class="flex items-center gap-3 sm:gap-4 flex-shrink-0">
        @if($tieneEmpresas || $tieneSucursales)
        <!-- Selector Compuesto y Neutro: Empresa y Sede -->
        <div class="inline-flex items-center bg-surface border border-border-strong rounded-lg divide-x divide-border-strong text-xs font-medium text-text-primary h-9">
            @if($tieneEmpresas)
            <div class="relative h-full" x-data="{ openEmpresa: false }">
                <button @click="openEmpresa = !openEmpresa" type="button"
                        class="h-full px-3 inline-flex items-center gap-1.5 hover:bg-surface-muted transition rounded-l-lg cursor-pointer"
                        title="Cambiar Comercio">
                    <svg class="w-4 h-4 text-text-muted flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="truncate max-w-[120px] sm:max-w-[160px] text-text-primary">
                        {{ \App\Support\Tenancy\CompanyContext::getCompany()?->nombre_comercial ?? 'Cambiar Comercio' }}
                    </span>
                    <svg class="w-3.5 h-3.5 text-text-muted ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-cloak x-show="openEmpresa" @click.away="openEmpresa = false"
                     class="absolute right-0 sm:left-0 sm:right-auto mt-1 w-64 rounded-xl bg-surface shadow-lg p-1.5 z-50 border border-border">
                    <div class="px-2.5 py-1.5 text-[11px] font-semibold text-text-muted uppercase tracking-wider">
                        Cambiar Comercio
                    </div>
                    <div class="space-y-0.5 max-h-60 overflow-y-auto">
                        @foreach($todasEmpresasActivas as $empItem)
                        <form action="{{ route('empresas.seleccionar') }}" method="POST">
                            @csrf
                            <input type="hidden" name="empresa_id" value="{{ $empItem->id }}">
                            <button type="submit"
                                    class="w-full text-left px-2.5 py-1.5 text-xs rounded-lg flex items-center justify-between transition cursor-pointer {{ \App\Support\Tenancy\CompanyContext::getId() === $empItem->id ? 'bg-brand-50 text-brand-600 font-semibold' : 'text-text-secondary hover:bg-surface-muted hover:text-text-primary' }}">
                                <span class="truncate">{{ $empItem->nombre_comercial }}</span>
                                <span class="text-[10px] text-text-muted ml-1 tabular-nums">{{ $empItem->nit }}</span>
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
                        class="h-full px-3 inline-flex items-center gap-1.5 hover:bg-surface-muted transition {{ $tieneEmpresas ? 'rounded-r-lg' : 'rounded-lg' }} cursor-pointer"
                        title="Cambiar Sede Activa">
                    <svg class="w-4 h-4 text-text-muted flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="truncate max-w-[120px] sm:max-w-[160px] text-text-primary">
                        {{ \App\Support\Tenancy\BranchContext::getBranch()?->nombre ?? 'Seleccionar Sede' }}
                    </span>
                    <svg class="w-3.5 h-3.5 text-text-muted ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-cloak x-show="openSucursal" @click.away="openSucursal = false"
                     class="absolute right-0 mt-1 w-60 rounded-xl bg-surface shadow-lg p-1.5 z-50 border border-border">
                    <div class="px-2.5 py-1.5 text-[11px] font-semibold text-text-muted uppercase tracking-wider">
                        Cambiar Sede Activa
                    </div>
                    <div class="space-y-0.5 max-h-60 overflow-y-auto">
                        @foreach($empresaActualHeader->sucursales as $sucursal)
                        <form action="{{ route('sucursales.seleccionar') }}" method="POST">
                            @csrf
                            <input type="hidden" name="sucursal_id" value="{{ $sucursal->id }}">
                            <button type="submit"
                                    class="w-full text-left px-2.5 py-1.5 text-xs rounded-lg flex items-center justify-between transition cursor-pointer {{ \App\Support\Tenancy\BranchContext::getId() === $sucursal->id ? 'bg-brand-50 text-brand-600 font-semibold' : 'text-text-secondary hover:bg-surface-muted hover:text-text-primary' }}">
                                <span class="truncate">{{ $sucursal->nombre }}</span>
                                @if($sucursal->es_principal)
                                <span class="ml-2 text-[10px] bg-surface-muted text-text-muted border border-border px-1.5 py-0.5 rounded font-medium">Principal</span>
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
        <a href="{{ route('notificaciones.index') }}"
           class="relative p-2 text-text-secondary hover:text-text-primary hover:bg-surface-muted rounded-lg transition"
           title="Notificaciones"
           aria-label="Notificaciones">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            @if($alertasPendientes > 0)
                <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-danger opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-danger"></span>
                </span>
            @endif
        </a>

        <!-- Menú de Usuario con Avatar sobre brand-700 -->
        <div class="relative" x-data="{ openUserMenu: false }">
            <button @click="openUserMenu = !openUserMenu" type="button"
                    class="flex items-center pl-3 border-l border-border gap-2.5 hover:opacity-85 transition cursor-pointer"
                    aria-label="Menú de usuario">
                <div class="h-8 w-8 rounded-lg bg-brand-700 text-white font-semibold flex items-center justify-center text-xs uppercase">
                    {{ mb_substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="hidden md:block text-left">
                    <div class="text-xs font-semibold text-text-primary leading-tight">{{ auth()->user()->name }}</div>
                    <div class="text-[10px] text-text-muted leading-tight">{{ $rolRealHeader }}</div>
                </div>
                <svg class="w-3.5 h-3.5 text-text-muted hidden sm:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-cloak x-show="openUserMenu" @click.away="openUserMenu = false"
                 class="absolute right-0 mt-2 w-56 rounded-xl bg-surface shadow-lg p-1.5 z-50 border border-border">
                <div class="px-3 py-2 border-b border-border mb-1">
                    <div class="text-xs font-semibold text-text-primary truncate">{{ auth()->user()->name }}</div>
                    <div class="text-[11px] text-text-muted truncate">{{ auth()->user()->email }}</div>
                    <div class="mt-1 flex items-center gap-1.5 text-[10px] text-text-muted">
                        <span class="h-1.5 w-1.5 rounded-full bg-success" aria-hidden="true"></span>
                        <span>{{ $rolRealHeader }}</span>
                    </div>
                </div>

                @if(auth()->user()->isSuperAdmin() && \App\Support\Tenancy\CompanyContext::check())
                <form action="{{ route('empresas.salir') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full text-left px-3 py-2 text-xs rounded-lg text-warning-text hover:bg-warning-bg flex items-center gap-2 transition cursor-pointer font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                        </svg>
                        <span>Salir del Negocio</span>
                    </button>
                </form>
                <div class="my-1 border-t border-border"></div>
                @endif

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full text-left px-3 py-2 text-xs rounded-lg text-danger hover:bg-danger-bg flex items-center gap-2 transition cursor-pointer font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span>Cerrar Sesión</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
