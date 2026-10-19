@extends('layouts.app')

@section('title', 'Registrar Nuevo Usuario')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header -->
    <x-page-header
        title="Registrar Nuevo Colaborador"
        subtitle="Crea credenciales de acceso y asigna rol operativo y sede de trabajo."
        :breadcrumbs="[
            ['label' => 'Usuarios', 'url' => route('usuarios.index')],
            ['label' => 'Crear']
        ]">
        <x-slot:actions>
            <a href="{{ route('usuarios.index') }}" class="btn-secondary">
                Volver
            </a>
        </x-slot:actions>
    </x-page-header>

    @if ($errors->any())
    <div class="rounded-lg bg-red-50 p-4 border border-red-200 text-xs text-red-700">
        <div class="font-semibold mb-1">Por favor corrige los siguientes errores:</div>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('usuarios.store') }}" class="space-y-6">
        @csrf

        <div class="card p-6 sm:p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <!-- Si es Super Admin, puede elegir la empresa -->
                @if(auth()->user()->isSuperAdmin() && $empresas->isNotEmpty())
                <div class="sm:col-span-2">
                    <label class="label-base">
                        Empresa / Tenant <span class="text-red-500">*</span>
                    </label>
                    <select name="empresa_id" class="input-select text-xs">
                        @foreach($empresas as $emp)
                        <option value="{{ $emp->id }}" {{ old('empresa_id', $empresaId) == $emp->id ? 'selected' : '' }}>
                            {{ $emp->nombre_comercial }} ({{ $emp->nit }})
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div class="sm:col-span-2">
                    <label class="label-base">
                        Nombre Completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="input-base text-xs"
                        placeholder="Ej: Carlos Gómez Restrepo">
                </div>

                <div>
                    <label class="label-base">
                        Correo Electrónico (Login) <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="input-base text-xs"
                        placeholder="colaborador@comercio.com">
                </div>

                <div>
                    <label class="label-base">
                        Teléfono / Móvil
                    </label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                        class="input-base text-xs font-mono"
                        placeholder="Ej: 312 345 6789">
                </div>

                <div>
                    <label class="label-base">
                        Cargo Desempeñado
                    </label>
                    <input type="text" name="cargo" value="{{ old('cargo') }}"
                        class="input-base text-xs"
                        placeholder="Ej: Cajero Principal, Vendedor Mostrador">
                </div>

                <div>
                    <label class="label-base">
                        Sede / Sucursal de Operación
                    </label>
                    <select name="sucursal_id" class="input-select text-xs">
                        <option value="">Todas las Sedes / Sin sede fija</option>
                        @foreach($sucursales as $suc)
                        <option value="{{ $suc->id }}" {{ old('sucursal_id') == $suc->id ? 'selected' : '' }}>
                            {{ $suc->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label-base">
                        Rol del Sistema <span class="text-red-500">*</span>
                    </label>
                    <select name="rol" required class="input-select text-xs">
                        @foreach($roles as $r)
                        <option value="{{ $r->value }}" {{ old('rol', \App\Enums\RolSistema::CAJERO->value) === $r->value ? 'selected' : '' }}>
                            {{ str_replace('_', ' ', $r->value) }}
                        </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">Define los permisos en pantalla, cajas y ventas.</p>
                </div>

                <div>
                    <label class="label-base">
                        Estado Inicial <span class="text-red-500">*</span>
                    </label>
                    <select name="estado" required class="input-select text-xs">
                        <option value="ACTIVO" {{ old('estado', 'ACTIVO') === 'ACTIVO' ? 'selected' : '' }}>ACTIVO (Puede ingresar)</option>
                        <option value="INACTIVO" {{ old('estado') === 'INACTIVO' ? 'selected' : '' }}>INACTIVO (Acceso bloqueado)</option>
                    </select>
                </div>

                <div class="sm:col-span-2 pt-2 border-t border-slate-200">
                    <p class="text-xs font-semibold text-slate-800 uppercase tracking-wider mb-3">Seguridad de la Cuenta</p>
                </div>

                <div>
                    <label class="label-base">
                        Contraseña <span class="text-red-500">*</span> (mínimo 8 caracteres)
                    </label>
                    <input type="password" name="password" required
                        class="input-base text-xs"
                        placeholder="••••••••">
                </div>

                <div>
                    <label class="label-base">
                        Confirmar Contraseña <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" required
                        class="input-base text-xs"
                        placeholder="••••••••">
                </div>

            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('usuarios.index') }}" class="btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn-primary">
                Guardar y Crear Usuario
            </button>
        </div>
    </form>
</div>
@endsection
