@extends('layouts.app')

@section('title', 'Registrar Nueva Empresa')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <x-page-header
        title="Registrar Nueva Empresa (Tenant)"
        subtitle="Configura los datos fiscales, la sede inicial y las credenciales del administrador."
        :breadcrumbs="[
            ['label' => 'Empresas', 'url' => route('empresas.index')],
            ['label' => 'Registro Asistido']
        ]"
    >
        <x-slot:actions>
            <a href="{{ route('empresas.index') }}" class="btn btn-secondary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </x-slot:actions>
    </x-page-header>

    @if ($errors->any())
    <div class="rounded-lg bg-danger-bg p-4 border border-danger-border text-sm text-danger-text">
        <div class="font-semibold mb-1">Por favor corrige los siguientes errores:</div>
        <ul class="list-disc list-inside space-y-0.5 text-xs">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @php
        $monedas = $monedas ?? \App\Support\Monedas::todas();
        $simbolos = $simbolos ?? \App\Support\Monedas::simbolos();
        $mapaSimbolos = array_combine(array_keys($monedas), array_column($monedas, 'simbolo'));
    @endphp
    <form method="POST" action="{{ route('empresas.store') }}" class="space-y-6"
        x-data="{
            monedaSeleccionada: '{{ old('moneda', 'COP') }}',
            simboloSeleccionado: '{{ old('simbolo_moneda', '$') }}',
            mapaSimbolos: {{ json_encode($mapaSimbolos) }},
            onMonedaChange(e) {
                const cod = e.target.value;
                if (this.mapaSimbolos[cod]) {
                    this.simboloSeleccionado = this.mapaSimbolos[cod];
                }
            }
        }">
        @csrf

        <!-- Sección 1: Datos de la Empresa -->
        <div class="card p-6 sm:p-8 space-y-6">
            <div class="border-b border-border pb-4">
                <h2 class="text-base font-semibold text-text-primary flex items-center gap-2">
                    <span class="h-5 w-5 rounded bg-brand-800 text-white text-xs font-semibold inline-flex items-center justify-center">1</span>
                    Información Comercial y Fiscal de la Empresa
                </h2>
                <p class="text-xs text-text-secondary mt-0.5">Identificación legal y razón social del comercio.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="label-base">
                        Nombre Comercial <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial') }}" required
                        class="input @error('nombre_comercial') border-danger @enderror"
                        placeholder="Ej: Supermercado El Ahorro">
                    @error('nombre_comercial')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Razón Social
                    </label>
                    <input type="text" name="razon_social" value="{{ old('razon_social') }}"
                        class="input @error('razon_social') border-danger @enderror"
                        placeholder="Ej: Inversiones El Ahorro S.A.S.">
                    @error('razon_social')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Tipo de Documento <span class="text-danger">*</span>
                    </label>
                    <select name="tipo_documento" required class="input @error('tipo_documento') border-danger @enderror">
                        @foreach($tiposDocumento as $td)
                        <option value="{{ $td->value }}" {{ old('tipo_documento', 'NIT') === $td->value ? 'selected' : '' }}>
                            {{ $td->value }}
                        </option>
                        @endforeach
                    </select>
                    @error('tipo_documento')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        NIT / Número de Identificación <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nit" value="{{ old('nit') }}" required
                        class="input font-mono @error('nit') border-danger @enderror"
                        placeholder="Ej: 901234567">
                    @error('nit')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Dígito de Verificación (DV)
                    </label>
                    <input type="text" name="dv" value="{{ old('dv') }}" maxlength="2"
                        class="input font-mono @error('dv') border-danger @enderror"
                        placeholder="Ej: 3">
                    @error('dv')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Correo Electrónico
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="input @error('email') border-danger @enderror"
                        placeholder="contacto@empresa.com">
                    @error('email')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Teléfono / Celular
                    </label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                        class="input @error('telefono') border-danger @enderror"
                        placeholder="Ej: 300 123 4567">
                    @error('telefono')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="label-base">
                        Dirección Principal
                    </label>
                    <input type="text" name="direccion" value="{{ old('direccion') }}"
                        class="input @error('direccion') border-danger @enderror"
                        placeholder="Ej: Carrera 50 # 10-20">
                    @error('direccion')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Ciudad
                    </label>
                    <input type="text" name="ciudad" value="{{ old('ciudad') }}"
                        class="input @error('ciudad') border-danger @enderror"
                        placeholder="Ej: Medellín">
                    @error('ciudad')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Departamento
                    </label>
                    <input type="text" name="departamento" value="{{ old('departamento') }}"
                        class="input @error('departamento') border-danger @enderror"
                        placeholder="Ej: Antioquia">
                    @error('departamento')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="moneda" class="label-base">
                        Moneda <span class="text-danger">*</span>
                    </label>
                    <select name="moneda" id="moneda" required
                        x-model="monedaSeleccionada"
                        @change="onMonedaChange($event)"
                        class="input bg-surface @error('moneda') border-danger @enderror">
                        @foreach($monedas as $m)
                            <option value="{{ $m['codigo'] }}" {{ old('moneda', 'COP') === $m['codigo'] ? 'selected' : '' }}>
                                {{ $m['codigo'] }} — {{ $m['nombre'] }} ({{ $m['pais'] }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-text-secondary">Moneda principal para transacciones y contabilidad.</p>
                    @error('moneda')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="simbolo_moneda" class="label-base">
                        Símbolo Moneda <span class="text-danger">*</span>
                    </label>
                    <select name="simbolo_moneda" id="simbolo_moneda" required
                        x-model="simboloSeleccionado"
                        class="input bg-surface @error('simbolo_moneda') border-danger @enderror">
                        @foreach($simbolos as $sym => $label)
                            <option value="{{ $sym }}" {{ old('simbolo_moneda', '$') === $sym ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-text-secondary">Símbolo visible en precios y recibos.</p>
                    @error('simbolo_moneda')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Sección 2: Sede / Sucursal Principal Inicial -->
        <div class="card p-6 sm:p-8 space-y-6">
            <div class="border-b border-border pb-4">
                <h2 class="text-base font-semibold text-text-primary flex items-center gap-2">
                    <span class="h-5 w-5 rounded bg-brand-800 text-white text-xs font-semibold inline-flex items-center justify-center">2</span>
                    Sucursal Principal Inicial
                </h2>
                <p class="text-xs text-text-secondary mt-0.5">Toda empresa requiere al menos una sede física o punto de venta para operar inventario y cajas.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label-base">
                        Nombre de la Sucursal <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="sucursal_nombre" value="{{ old('sucursal_nombre', 'Sede Principal Centro') }}" required
                        class="input @error('sucursal_nombre') border-danger @enderror"
                        placeholder="Ej: Sede Principal Centro">
                    @error('sucursal_nombre')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Código de Sucursal
                    </label>
                    <input type="text" name="sucursal_codigo" value="{{ old('sucursal_codigo', 'SUC-001') }}"
                        class="input font-mono uppercase @error('sucursal_codigo') border-danger @enderror"
                        placeholder="Ej: SUC-001">
                    @error('sucursal_codigo')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Dirección de la Sede
                    </label>
                    <input type="text" name="sucursal_direccion" value="{{ old('sucursal_direccion') }}"
                        class="input @error('sucursal_direccion') border-danger @enderror"
                        placeholder="Dejar en blanco para usar la dirección fiscal">
                    @error('sucursal_direccion')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Ciudad de la Sede
                    </label>
                    <input type="text" name="sucursal_ciudad" value="{{ old('sucursal_ciudad') }}"
                        class="input @error('sucursal_ciudad') border-danger @enderror"
                        placeholder="Dejar en blanco para usar la ciudad fiscal">
                    @error('sucursal_ciudad')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Sección 3: Administrador Inicial -->
        <div class="card p-6 sm:p-8 space-y-6">
            <div class="border-b border-border pb-4">
                <h2 class="text-base font-semibold text-text-primary flex items-center gap-2">
                    <span class="h-5 w-5 rounded bg-brand-800 text-white text-xs font-semibold inline-flex items-center justify-center">3</span>
                    Usuario Administrador de Empresa (Credenciales)
                </h2>
                <p class="text-xs text-text-secondary mt-0.5">El usuario con rol ADMIN_EMPRESA que gestionará este negocio.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label-base">
                        Nombre Completo <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}" required
                        class="input @error('admin_name') border-danger @enderror"
                        placeholder="Ej: Andrés Restrepo">
                    @error('admin_name')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Cargo
                    </label>
                    <input type="text" name="admin_cargo" value="{{ old('admin_cargo', 'Gerente General') }}"
                        class="input @error('admin_cargo') border-danger @enderror"
                        placeholder="Ej: Gerente General / Administrador">
                    @error('admin_cargo')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Correo de Acceso (Email) <span class="text-danger">*</span>
                    </label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" required
                        class="input @error('admin_email') border-danger @enderror"
                        placeholder="admin@comercio.com">
                    @error('admin_email')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Teléfono del Administrador
                    </label>
                    <input type="text" name="admin_telefono" value="{{ old('admin_telefono') }}"
                        class="input @error('admin_telefono') border-danger @enderror"
                        placeholder="Ej: 310 987 6543">
                    @error('admin_telefono')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Contraseña <span class="text-danger">*</span> (mínimo 8 caracteres)
                    </label>
                    <input type="password" name="admin_password" required
                        class="input @error('admin_password') border-danger @enderror"
                        placeholder="••••••••">
                    @error('admin_password')
                        <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label-base">
                        Confirmar Contraseña <span class="text-danger">*</span>
                    </label>
                    <input type="password" name="admin_password_confirmation" required
                        class="input"
                        placeholder="••••••••">
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('empresas.index') }}" class="btn btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                Crear Empresa y Activar
            </button>
        </div>
    </form>
</div>
@endsection
