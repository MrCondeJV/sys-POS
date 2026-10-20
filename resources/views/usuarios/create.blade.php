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

    <form method="POST" action="{{ route('usuarios.store') }}" class="space-y-6">
        @csrf

        <div class="card p-6 sm:p-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <!-- Si es Super Admin, puede elegir la empresa -->
                @if(auth()->user()->isSuperAdmin() && $empresas->isNotEmpty())
                <div class="sm:col-span-2">
                    <label class="label-base">
                        Empresa / Tenant <span class="text-danger">*</span>
                    </label>
                    <select name="empresa_id" class="input text-xs @error('empresa_id') border-danger @enderror">
                        @foreach($empresas as $emp)
                        <option value="{{ $emp->id }}" {{ old('empresa_id', $empresaId) == $emp->id ? 'selected' : '' }}>
                            {{ $emp->nombre_comercial }} ({{ $emp->nit }})
                        </option>
                        @endforeach
                    </select>
                    @error('empresa_id')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <div class="sm:col-span-2">
                    <label class="label-base">
                        Nombre Completo <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="input text-xs @error('name') border-danger @enderror"
                        placeholder="Ej: Carlos Gómez Restrepo">
                    @error('name')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Correo Electrónico (Login) <span class="text-danger">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="input text-xs @error('email') border-danger @enderror"
                        placeholder="colaborador@comercio.com">
                    @error('email')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Teléfono / Móvil
                    </label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                        class="input text-xs font-mono @error('telefono') border-danger @enderror"
                        placeholder="Ej: 312 345 6789">
                    @error('telefono')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Cargo Desempeñado
                    </label>
                    <input type="text" name="cargo" value="{{ old('cargo') }}"
                        class="input text-xs @error('cargo') border-danger @enderror"
                        placeholder="Ej: Cajero Principal, Vendedor Mostrador">
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
                        <option value="{{ $suc->id }}" {{ old('sucursal_id') == $suc->id ? 'selected' : '' }}>
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
                        <option value="{{ $r->value }}" {{ old('rol', \App\Enums\RolSistema::CAJERO->value) === $r->value ? 'selected' : '' }}>
                            {{ str_replace('_', ' ', $r->value) }}
                        </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-text-muted mt-1">Define los permisos en pantalla, cajas y ventas.</p>
                    @error('rol')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Estado Inicial <span class="text-danger">*</span>
                    </label>
                    <select name="estado" required class="input text-xs @error('estado') border-danger @enderror">
                        <option value="ACTIVO" {{ old('estado', 'ACTIVO') === 'ACTIVO' ? 'selected' : '' }}>ACTIVO (Puede ingresar)</option>
                        <option value="INACTIVO" {{ old('estado') === 'INACTIVO' ? 'selected' : '' }}>INACTIVO (Acceso bloqueado)</option>
                    </select>
                    @error('estado')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2 pt-2 border-t border-border">
                    <p class="text-xs font-semibold text-text-primary uppercase tracking-wider mb-3">Seguridad de la Cuenta</p>
                </div>

                <div>
                    <label class="label-base">
                        Contraseña <span class="text-danger">*</span> (mínimo 8 caracteres)
                    </label>
                    <input type="password" name="password" required
                        class="input text-xs @error('password') border-danger @enderror"
                        placeholder="••••••••">
                    @error('password')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Confirmar Contraseña <span class="text-danger">*</span>
                    </label>
                    <input type="password" name="password_confirmation" required
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
                Guardar y Crear Usuario
            </button>
        </div>
    </form>
</div>
@endsection
