<nav class="flex-1 min-h-0 px-3 py-4 space-y-1 overflow-y-auto"
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
        {{-- MODO SAAS SUPER ADMIN (SIN EMPRESA EN CONTEXTO) --}}
        <div class="sidebar-section-header whitespace-nowrap pt-2 pb-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-3 flex items-center justify-between">
            <span>Plataforma SaaS</span>
            <span class="text-[10px] bg-brand-700 text-brand-300 px-1.5 py-0.5 rounded font-mono font-medium sidebar-badge whitespace-nowrap">Global</span>
        </div>
        <div class="sidebar-section-divider hidden my-2 border-t border-brand-700/60 mx-2" title="Plataforma SaaS"></div>

        <a href="{{ route('empresas.index') }}"
           class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('empresas.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
           data-title="Comercios / Empresas">
            <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('empresas.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span class="sidebar-text truncate whitespace-nowrap">Comercios / Empresas</span>
        </a>

        <a href="{{ route('usuarios.index') }}"
           class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('usuarios.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
           data-title="Usuarios de Plataforma">
            <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('usuarios.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span class="sidebar-text truncate whitespace-nowrap">Usuarios de Plataforma</span>
        </a>

        <a href="{{ route('auditoria.index') }}"
           class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('auditoria.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
           data-title="Auditoría Global">
            <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('auditoria.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span class="sidebar-text truncate whitespace-nowrap">Auditoría Global</span>
        </a>
    @else
        {{-- MODO OPERATIVO DE NEGOCIO (USUARIO TENANT O SUPER ADMIN GESTIONANDO EMPRESA) --}}
        <a href="{{ route('dashboard') }}"
           class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
           data-title="Dashboard">
            <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="sidebar-text truncate whitespace-nowrap">Dashboard</span>
        </a>

        @canany(['empresa.gestionar', 'sucursales.gestionar', 'usuarios.ver', 'usuarios.gestionar'])
        <button type="button" @click="toggleSection('gestion')"
                class="sidebar-section-header whitespace-nowrap w-full pt-3 pb-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-2 flex items-center justify-between hover:text-slate-200 transition cursor-pointer">
            <span>Gestión Empresarial</span>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': openSections.gestion }"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="sidebar-section-divider hidden my-2 border-t border-brand-700/60 mx-2" title="Gestión Empresarial"></div>

        <div x-show="!expanded || openSections.gestion" class="space-y-1">
            @can('empresa.gestionar')
            <a href="{{ route('empresa.perfil') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('empresa.perfil') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Perfil de Empresa">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('empresa.perfil') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Perfil de Empresa</span>
            </a>

            <a href="{{ route('suscripcion.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('suscripcion.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Suscripción y Pagos">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('suscripcion.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Suscripción y Pagos</span>
            </a>
            @endcan

            @canany(['usuarios.ver', 'usuarios.gestionar'])
            <a href="{{ route('usuarios.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('usuarios.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Usuarios y Roles">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('usuarios.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Usuarios y Roles</span>
            </a>
            @endcanany

            @can('sucursales.gestionar')
            <a href="{{ route('sucursales.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('sucursales.index') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Sucursales">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('sucursales.index') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Sucursales</span>
            </a>

            <a href="{{ route('traslados.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('traslados.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Traslados Sucursal">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('traslados.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Traslados Sucursal</span>
            </a>
            @endcan
        </div>
        @endcanany

        @canany(['productos.ver', 'listas_precios.ver', 'inventario.ver'])
        <button type="button" @click="toggleSection('inventario')"
                class="sidebar-section-header whitespace-nowrap w-full pt-3 pb-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-2 flex items-center justify-between hover:text-slate-200 transition cursor-pointer">
            <span>Inventario & Catálogo</span>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': openSections.inventario }"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="sidebar-section-divider hidden my-2 border-t border-brand-700/60 mx-2" title="Inventario & Catálogo"></div>

        <div x-show="!expanded || openSections.inventario" class="space-y-1">
            @can('productos.ver')
            <a href="{{ route('productos.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('productos.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Productos">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('productos.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Productos</span>
            </a>
            @endcan

            @can('listas_precios.ver')
            <a href="{{ route('listas-precios.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('listas-precios.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Listas de Precios">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('listas-precios.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Listas de Precios</span>
            </a>
            @endcan

            @can('inventario.ver')
            <a href="{{ route('inventario.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('inventario.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Inventario & Kardex">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('inventario.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Inventario & Kardex</span>
            </a>
            @endcan

            @can('productos.ver')
            <a href="{{ route('catalogos.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('catalogos.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Catálogos Auxiliares">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('catalogos.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Catálogos Auxiliares</span>
            </a>
            @endcan
        </div>
        @endcanany

        @canany(['compras.ver', 'proveedores.ver'])
        <button type="button" @click="toggleSection('compras')"
                class="sidebar-section-header whitespace-nowrap w-full pt-3 pb-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-2 flex items-center justify-between hover:text-slate-200 transition cursor-pointer">
            <span>Compras & Proveedores</span>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': openSections.compras }"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="sidebar-section-divider hidden my-2 border-t border-brand-700/60 mx-2" title="Compras & Proveedores"></div>

        <div x-show="!expanded || openSections.compras" class="space-y-1">
            @can('compras.ver')
            <a href="{{ route('compras.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('compras.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Compras / Facturas">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('compras.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Compras / Facturas</span>
            </a>
            @endcan

            @can('proveedores.ver')
            <a href="{{ route('proveedores.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('proveedores.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Proveedores">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('proveedores.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Proveedores</span>
            </a>
            @endcan
        </div>
        @endcanany

        @canany(['ventas.ver', 'ventas.devolver', 'documentos.ver', 'clientes.ver', 'cartera.ver'])
        <button type="button" @click="toggleSection('ventas')"
                class="sidebar-section-header whitespace-nowrap w-full pt-3 pb-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-2 flex items-center justify-between hover:text-slate-200 transition cursor-pointer">
            <span>Ventas & Clientes</span>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': openSections.ventas }"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="sidebar-section-divider hidden my-2 border-t border-brand-700/60 mx-2" title="Ventas & Clientes"></div>

        <div x-show="!expanded || openSections.ventas" class="space-y-1">
            @can('ventas.ver')
            <a href="{{ route('ventas.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('ventas.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Ventas Realizadas">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('ventas.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Ventas Realizadas</span>
            </a>
            @endcan

            @can('ventas.devolver')
            <a href="{{ route('devoluciones.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('devoluciones.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Devoluciones">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('devoluciones.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Devoluciones</span>
            </a>
            @endcan

            @can('documentos.ver')
            <a href="{{ route('documentos.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('documentos.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Documentos de Venta">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('documentos.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Documentos de Venta</span>
            </a>
            @endcan

            @can('clientes.ver')
            <a href="{{ route('clientes.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('clientes.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Clientes">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('clientes.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Clientes</span>
            </a>
            @endcan

            @can('cartera.ver')
            <a href="{{ route('cartera.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('cartera.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Crédito & Cartera">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('cartera.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Crédito & Cartera</span>
            </a>
            @endcan
        </div>
        @endcanany

        @canany(['caja.ver', 'ventas.crear'])
        <button type="button" @click="toggleSection('caja')"
                class="sidebar-section-header whitespace-nowrap w-full pt-3 pb-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-2 flex items-center justify-between hover:text-slate-200 transition cursor-pointer">
            <span>Caja & Operaciones</span>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': openSections.caja }"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="sidebar-section-divider hidden my-2 border-t border-brand-700/60 mx-2" title="Caja & Operaciones"></div>

        <div x-show="!expanded || openSections.caja" class="space-y-1">
            @can('caja.ver')
            <a href="{{ route('cajas.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('cajas.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Cajas & Turnos">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('cajas.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Cajas & Turnos</span>
            </a>
            @endcan

            @can('ventas.crear')
            <a href="{{ route('pos.index') }}"
               class="sidebar-link flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('pos.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Terminal POS">
                <span class="flex items-center min-w-0">
                    <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('pos.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span class="sidebar-text truncate whitespace-nowrap">Terminal POS</span>
                </span>
                <span class="sidebar-badge whitespace-nowrap text-[10px] bg-brand-700 text-brand-300 font-medium px-1.5 py-0.5 rounded">Rápido</span>
            </a>
            @endcan
        </div>
        @endcanany

        @canany(['reportes.ver', 'auditoria.ver', 'impuestos.ver'])
        <button type="button" @click="toggleSection('analitica')"
                class="sidebar-section-header whitespace-nowrap w-full pt-3 pb-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-2 flex items-center justify-between hover:text-slate-200 transition cursor-pointer">
            <span>Analítica & Negocio</span>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': openSections.analitica }"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="sidebar-section-divider hidden my-2 border-t border-brand-700/60 mx-2" title="Analítica & Negocio"></div>

        <div x-show="!expanded || openSections.analitica" class="space-y-1">
            @can('reportes.ver')
            <a href="{{ route('reportes.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('reportes.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Centro de Reportes">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('reportes.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Centro de Reportes</span>
            </a>
            @endcan

            @can('auditoria.ver')
            <a href="{{ route('auditoria.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('auditoria.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Auditoría & Logs">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('auditoria.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Auditoría & Logs</span>
            </a>
            @endcan

            @can('impuestos.ver')
            <a href="{{ route('impuestos.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('impuestos.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Impuestos & Tarifas">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('impuestos.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Impuestos & Tarifas</span>
            </a>
            @endcan
        </div>
        @endcanany

        @canany(['documentos.ver', 'documentos.emitir'])
        <button type="button" @click="toggleSection('dian')"
                class="sidebar-section-header whitespace-nowrap w-full pt-3 pb-1 text-[11px] font-semibold text-slate-400 uppercase tracking-wider px-2 flex items-center justify-between hover:text-slate-200 transition cursor-pointer">
            <span>DIAN & Fiscal</span>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200"
                 :class="{ 'rotate-180': openSections.dian }"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="sidebar-section-divider hidden my-2 border-t border-brand-700/60 mx-2" title="DIAN & Fiscal"></div>

        <div x-show="!expanded || openSections.dian" class="space-y-1">
            @can('documentos.ver')
            <a href="{{ route('facturacion-electronica.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('facturacion-electronica.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Facturación Electrónica">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('facturacion-electronica.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Facturación Electrónica</span>
            </a>

            <a href="{{ route('resoluciones.index') }}"
               class="sidebar-link flex items-center px-3 py-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('resoluciones.*') ? 'bg-white/[0.08] text-white border-l-[3px] border-brand-500 pl-[9px]' : 'text-slate-300 hover:text-white hover:bg-white/[0.05]' }}"
               data-title="Resoluciones DIAN">
                <svg class="w-[18px] h-[18px] mr-3 flex-shrink-0 {{ request()->routeIs('resoluciones.*') ? 'text-brand-400' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="sidebar-text truncate whitespace-nowrap">Resoluciones DIAN</span>
            </a>
            @endcan
        </div>
        @endcanany
    @endif
</nav>
