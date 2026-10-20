@if(auth()->check() && auth()->user()->isSuperAdmin() && \App\Support\Tenancy\CompanyContext::check())
<!-- Banner Superior: Modo Soporte Super Admin Activo (36px, Fondo Ámbar Suave) -->
<div class="h-9 min-h-[36px] bg-warning-bg border-b border-warning-border text-warning-text px-4 sm:px-6 flex items-center justify-between text-xs font-medium z-20 flex-shrink-0">
    <div class="flex items-center gap-2 truncate">
        <span class="sr-only">MODO SOPORTE SUPER ADMIN</span>
        <span class="inline-block w-2 h-2 rounded-full bg-warning flex-shrink-0" aria-hidden="true"></span>
        <span class="truncate">
            Estás gestionando <strong class="font-semibold text-warning-text">{{ $empresaActualLayout?->nombre_comercial ?? 'Comercio' }}</strong> como Super Administrador
        </span>
    </div>
    <form action="{{ route('empresas.salir') }}" method="POST" class="flex-shrink-0 ml-4">
        @csrf
        <button type="submit"
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-warning-text hover:underline transition cursor-pointer"
            title="Salir al Panel Super Admin">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Volver al panel Super Admin</span>
            <span class="sr-only">Salir al Panel Super Admin</span>
        </button>
    </form>
</div>
@endif
