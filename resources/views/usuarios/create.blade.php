@extends('layouts.app')

@section('title', 'Registrar Nuevo Usuario')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('usuarios.index') }}" class="hover:text-indigo-600 transition">Usuarios</a>
                <span>/</span>
                <span>Crear</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Registrar Nuevo Colaborador</h1>
            <p class="text-sm text-slate-500">Crea credenciales de acceso y asigna rol operativo y sede de trabajo.</p>
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

    <form method="POST" action="{{ route('usuarios.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-3xl border border-slate-200/70 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                <!-- Si es Super Admin, puede elegir la empresa -->
                @if(auth()->user()->isSuperAdmin() && $empresas->isNotEmpty())
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Empresa / Tenant *
                    </label>
                    <select name="empresa_id"
                        class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($empresas as $emp)
                        <option value="{{ $emp->id }}" {{ old('empresa_id', $empresaId) == $emp->id ? 'selected' : '' }}>
                            {{ $emp->nombre_comercial }} ({{ $emp->nit }})
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Nombre Completo *
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: Carlos Gómez Restrepo">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Correo Electrónico (Login) *
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="colaborador@comercio.com">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Teléfono / Móvil
                    </label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: 312 345 6789">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Cargo Desempeñado
                    </label>
                    <input type="text" name="cargo" value="{{ old('cargo') }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: Cajero Principal, Vendedor Mostrador">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Sede / Sucursal de Operación
                    </label>
                    <select name="sucursal_id"
                        class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Todas las Sedes / Sin sede fija</option>
                        @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}" {{ old('sucursal_id') == $suc->id ? 'selected' : '' }}>
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
                        <option value="{{ $r->value }}" {{ old('rol', \App\Enums\RolSistema::CAJERO->value) === $r->value ? 'selected' : '' }}>
                            {{ str_replace('_', ' ', $r->value) }}
                        </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Define los permisos en pantalla, cajas y ventas.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Estado Inicial *
                    </label>
                    <select name="estado" required
                        class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="ACTIVO" {{ old('estado', 'ACTIVO') === 'ACTIVO' ? 'selected' : '' }}>ACTIVO (Puede ingresar)</option>
                        <option value="INACTIVO" {{ old('estado') === 'INACTIVO' ? 'selected' : '' }}>INACTIVO (Acceso bloqueado)</option>
                    </select>
                </div>

                <div class="sm:col-span-2 pt-2 border-t border-slate-100">
                    <p class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Seguridad de la Cuenta</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Contraseña * (mínimo 8 caracteres)
                    </label>
                    <input type="password" name="password" required
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="••••••••">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Confirmar Contraseña *
                    </label>
                    <input type="password" name="password_confirmation" required
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
                Guardar y Crear Usuario
            </button>
        </div>
    </form>
</div>
@endsection
