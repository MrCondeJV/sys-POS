<div class="inline-flex items-center gap-1 justify-end">
    @can('update', $user)
    <x-icon-button
        variant="ghost"
        size="sm"
        href="{{ route('usuarios.edit', $user) }}"
        title="Editar usuario"
        aria-label="Editar usuario {{ $user->name }}"
    >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
    </x-icon-button>

    @if($user->id !== auth()->id())
        @php
            $esActivo = $user->estado === \App\Enums\EstadoGeneral::ACTIVO;
            // Las directivas Blade no se compilan dentro de atributos de componentes x-...: el payload se arma aquí
            $payloadEstado = [
                'title' => $esActivo ? '¿Inactivar cuenta de ' . $user->name . '?' : '¿Reactivar cuenta de ' . $user->name . '?',
                'message' => $esActivo ? 'El estado del usuario cambiará a INACTIVO.' : 'El estado del usuario cambiará a ACTIVO.',
                'consequence' => $esActivo
                    ? 'No podrá iniciar sesión y, si tiene una sesión abierta, el middleware SetCompanyContext cerrará e invalidará su sesión en su próxima petición HTTP.'
                    : 'El colaborador podrá volver a autenticarse con sus credenciales y permisos actuales.',
                'confirmText' => $esActivo ? 'Sí, inactivar usuario' : 'Sí, reactivar usuario',
                'variant' => $esActivo ? 'danger' : 'primary',
                'action' => route('usuarios.toggle-estado', $user),
                'method' => 'POST',
                'formId' => 'form-toggle-usuario-' . $user->id,
            ];
        @endphp
        <x-icon-button
            type="button"
            variant="ghost"
            size="sm"
            class="{{ $esActivo ? 'text-warning hover:text-warning-text hover:bg-warning-bg' : 'text-success hover:text-success-text hover:bg-success-bg' }}"
            :title="$esActivo ? 'Inactivar acceso' : 'Reactivar acceso'"
            :aria-label="$esActivo ? 'Inactivar acceso de ' . $user->name : 'Reactivar acceso de ' . $user->name"
            @click="$dispatch('open-modal-confirm-usuario', { ...{{ \Illuminate\Support\Js::from($payloadEstado) }}, triggerEl: $el })"
        >
            @if($esActivo)
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
            @else
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            @endif
        </x-icon-button>
    @endif
    @endcan

    @can('delete', $user)
    @if($user->id !== auth()->id())
        @php
            $payloadEliminar = [
                'title' => '¿Eliminar colaborador ' . $user->name . '?',
                'message' => 'Se aplicará eliminación lógica (soft delete) sobre el registro del usuario.',
                'consequence' => 'El usuario dejará de aparecer en los listados activos y no podrá autenticarse. Su correo electrónico permanece reservado en la base de datos y sus registros históricos (ventas, arqueos y movimientos asociados a su ID) se conservan sin cambios.',
                'confirmText' => 'Sí, eliminar usuario',
                'variant' => 'danger',
                'action' => route('usuarios.destroy', $user),
                'method' => 'DELETE',
                'formId' => 'form-delete-usuario-' . $user->id,
            ];
        @endphp
        <x-icon-button
            type="button"
            variant="danger"
            size="sm"
            title="Eliminar usuario"
            aria-label="Eliminar usuario {{ $user->name }}"
            @click="$dispatch('open-modal-confirm-usuario', { ...{{ \Illuminate\Support\Js::from($payloadEliminar) }}, triggerEl: $el })"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
        </x-icon-button>
    @endif
    @endcan
</div>
