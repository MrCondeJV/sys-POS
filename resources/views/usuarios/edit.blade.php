@extends('layouts.app')

@section('title', 'Editar Usuario — ' . $usuario->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('usuarios.index') }}" class="hover:text-indigo-600 transition">Usuarios</a>
                <span>/</span>
                <span>Editar</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Editar Usuario: {{ $usuario->name }}</h1>
            <p class="text-sm text-slate-500">Modifica datos personales, asignación de rol, sede o restablece la contraseña.</p>
        </div>
        <a href="{{ route('usuarios.index') }}"
            class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
            Volver
        </a>
    </div>

    @if ($errors->any())
    <div class="rounded-2xl bg-red-50 p-4 border border-red-200 text-sm text-red-700">
        <div class="font-bold mb-1">Por favor corrige los siguientes errores:</div>
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-3xl border border-slate-200/70 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Nombre Completo *
                    </label>
                    <input type="text" name="name" value="{{ old('name', $usuario->name) }}" required
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Correo Electrónico (Login) *
                    </label>
                    <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Teléfono / Móvil
                    </label>
                    <input type="text" name="telefono" value="{{ old('telefono', $usuario->telefono) }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Cargo Desempeñado
                    </label>
                    <input type="text" name="cargo" value="{{ old('cargo', $usuario->cargo) }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Sede / Sucursal de Operación
                    </label>
                    <select name="sucursal_id"
                        class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todas las Sedes / Sin sede fija</option>
                        @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}" {{ old('sucursal_id', $usuario->sucursal_id) == $suc->id ? 'selected' : '' }}>
                            {{ $suc->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Rol del Sistema *
                    </label>
                    <select name="rol" required
                        class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($roles as $r)
                        <option value="{{ $r->value }}" {{ old('rol', $rolActual) === $r->value ? 'selected' : '' }}>
                            {{ str_replace('_', ' ', $r->value) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Estado de la Cuenta *
                    </label>
                    <select name="estado" required
                        class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="ACTIVO" {{ old('estado', $usuario->estado?->value ?? $usuario->estado) === 'ACTIVO' ? 'selected' : '' }}>
                            ACTIVO (Acceso permitido)
                        </option>
                        <option value="INACTIVO" {{ old('estado', $usuario->estado?->value ?? $usuario->estado) === 'INACTIVO' ? 'selected' : '' }}>
                            INACTIVO (Acceso bloqueado)
                        </option>
                    </select>
                </div>

                <div class="sm:col-span-2 pt-3 border-t border-slate-100">
                    <p class="text-xs font-bold text-slate-700 uppercase tracking-wider">Restablecer Contraseña (Opcional)</p>
                    <p class="text-[11px] text-slate-400">Deja estos campos vacíos si no deseas cambiar la contraseña actual.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Nueva Contraseña (mínimo 8 caracteres)
                    </label>
                    <input type="password" name="password"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="••••••••">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Confirmar Nueva Contraseña
                    </label>
                    <input type="password" name="password_confirmation"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="••••••••">
                </div>

            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('usuarios.index') }}"
                class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Cancelar
            </a>
            <button type="submit"
                class="inline-flex items-center px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
