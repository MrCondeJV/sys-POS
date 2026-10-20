@extends('layouts.app')

@section('title', 'Editar Usuario — ' . $usuario->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <x-page-header
        title="Editar Usuario: {{ $usuario->name }}"
        subtitle="Modifica datos personales, asignación de rol, sede o restablece la contraseña."
        :breadcrumbs="[
            ['label' => 'Usuarios', 'url' => route('usuarios.index')],
            ['label' => 'Editar']
        ]">
        <x-slot:actions>
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                Volver
            </a>
        </x-slot:actions>
    </x-page-header>

    @if ($errors->any())
    <div class="rounded-lg bg-danger-bg p-4 border border-danger-border text-xs text-danger-text">
        <div class="font-semibold mb-1">Por favor corrige los siguientes errores:</div>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="card p-6 sm:p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="sm:col-span-2">
                    <label class="label-base">
                        Nombre Completo <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $usuario->name) }}" required
                        class="input text-xs @error('name') border-danger @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Correo Electrónico (Login) <span class="text-danger">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required
                        class="input text-xs @error('email') border-danger @enderror">
                    @error('email')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Teléfono / Móvil
                    </label>
                    <input type="text" name="telefono" value="{{ old('telefono', $usuario->telefono) }}"
                        class="input text-xs font-mono @error('telefono') border-danger @enderror">
                    @error('telefono')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Cargo Desempeñado
                    </label>
                    <input type="text" name="cargo" value="{{ old('cargo', $usuario->cargo) }}"
                        class="input text-xs @error('cargo') border-danger @enderror">
                    @error('cargo')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Sede / Sucursal de Operación
                    </label>
                    <select name="sucursal_id" class="input text-xs @error('sucursal_id') border-danger @enderror">
                        <option value="">Todas las Sedes / Sin sede fija</option>
                        @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}" {{ old('sucursal_id', $usuario->sucursal_id) == $suc->id ? 'selected' : '' }}>
                            {{ $suc->nombre }}
                        </option>
                        @endforeach
                    </select>
                    @error('sucursal_id')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Rol del Sistema <span class="text-danger">*</span>
                    </label>
                    <select name="rol" required class="input text-xs @error('rol') border-danger @enderror">
                        @foreach($roles as $r)
                        <option value="{{ $r->value }}" {{ old('rol', $rolActual) === $r->value ? 'selected' : '' }}>
                            {{ str_replace('_', ' ', $r->value) }}
                        </option>
                        @endforeach
                    </select>
                    @error('rol')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Estado de la Cuenta <span class="text-danger">*</span>
                    </label>
                    <select name="estado" required class="input text-xs @error('estado') border-danger @enderror">
                        <option value="ACTIVO" {{ old('estado', $usuario->estado?->value ?? $usuario->estado) === 'ACTIVO' ? 'selected' : '' }}>
                            ACTIVO (Acceso permitido)
                        </option>
                        <option value="INACTIVO" {{ old('estado', $usuario->estado?->value ?? $usuario->estado) === 'INACTIVO' ? 'selected' : '' }}>
                            INACTIVO (Acceso bloqueado)
                        </option>
                    </select>
                    @error('estado')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2 pt-3 border-t border-border">
                    <p class="text-xs font-semibold text-text-primary uppercase tracking-wider">Restablecer Contraseña (Opcional)</p>
                    <p class="text-[11px] text-text-muted">Deja estos campos vacíos si no deseas cambiar la contraseña actual.</p>
                </div>

                <div>
                    <label class="label-base">
                        Nueva Contraseña (mínimo 8 caracteres)
                    </label>
                    <input type="password" name="password"
                        class="input text-xs @error('password') border-danger @enderror"
                        placeholder="••••••••">
                    @error('password')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Confirmar Nueva Contraseña
                    </label>
                    <input type="password" name="password_confirmation"
                        class="input text-xs"
                        placeholder="••••••••">
                </div>

            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
