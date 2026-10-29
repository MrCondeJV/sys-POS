<div class="inline-flex items-center gap-1.5 justify-end">
    <!-- Entrar al Negocio (Contexto Super Admin) -->
    <form method="POST" action="{{ route('empresas.seleccionar') }}" class="inline">
        @csrf
        <input type="hidden" name="empresa_id" value="{{ $emp->id }}">
        <button
            type="submit"
            class="{{ $esActivaEnSesion ? 'btn btn-secondary text-xs h-8 px-2.5' : 'btn btn-primary text-xs h-8 px-2.5' }}"
            title="Entrar a administrar este negocio"
        >
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            <span>{{ $esActivaEnSesion ? 'Gestionando' : 'Entrar' }}</span>
        </button>
    </form>

    <!-- Editar -->
    <x-icon-button
        href="{{ route('empresas.edit', $emp) }}"
        variant="ghost"
        size="sm"
        title="Editar datos de la empresa"
        aria-label="Editar empresa {{ $emp->nombre_comercial }}"
    >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
    </x-icon-button>

    <!-- Activar / Suspender con confirmación accesible reutilizable -->
    <x-icon-button
        type="button"
        variant="ghost"
        size="sm"
        :title="$empEstadoActivo ? 'Suspender empresa' : 'Activar empresa'"
        :aria-label="($empEstadoActivo ? 'Suspender ' : 'Activar ') . $emp->nombre_comercial"
        @click="$dispatch('open-modal-confirm-empresa', {
            triggerEl: $el,
            title: @js(($empEstadoActivo ? '¿Suspender ' : '¿Activar ') . $emp->nombre_comercial . '?'),
            message: @js($empEstadoActivo
                ? 'El estado del registro de la empresa cambiará a INACTIVO en el directorio de tenants.'
                : 'El estado del registro de la empresa cambiará a ACTIVO en el directorio de tenants.'),
            consequence: @js($empEstadoActivo
                ? 'Al suspender la empresa, sus usuarios no podrán iniciar sesión ni operar en el sistema. Las sesiones activas serán cerradas en su siguiente petición.'
                : 'Esta acción reactiva el acceso de la empresa y permite a sus usuarios volver a iniciar sesión y operar.'),
            confirmText: @js($empEstadoActivo ? 'Suspender empresa' : 'Activar empresa'),
            variant: @js($empEstadoActivo ? 'danger' : 'primary'),
            action: @js(route('empresas.toggle-estado', $emp)),
            method: 'POST',
            formId: @js('form-toggle-empresa-' . $emp->id)
        })"
    >
        @if($empEstadoActivo)
        <svg class="h-4 w-4 text-text-secondary hover:text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
        </svg>
        @else
        <svg class="h-4 w-4 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        @endif
    </x-icon-button>
</div>
