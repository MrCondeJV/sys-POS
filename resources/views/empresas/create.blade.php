@extends('layouts.app')

@section('title', 'Registrar Nueva Empresa')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                <a href="{{ route('empresas.index') }}" class="hover:text-brand-600 transition">Empresas</a>
                <span>&rsaquo;</span>
                <span class="text-slate-700 font-semibold">Registro Asistido</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Registrar Nueva Empresa (Tenant)</h1>
            <p class="text-sm text-slate-500">Configura los datos fiscales, la sede inicial y las credenciales del administrador.</p>
        </div>
        <div>
            <a href="{{ route('empresas.index') }}" class="btn-secondary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </div>
    </div>

    @if ($errors->any())
    <div class="rounded-lg bg-rose-50 p-4 border border-rose-200 text-sm text-rose-800">
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
            <div class="border-b border-slate-200 pb-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="h-5 w-5 rounded bg-slate-900 text-white text-xs font-semibold inline-flex items-center justify-center">1</span>
                    Información Comercial y Fiscal de la Empresa
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Identificación legal y razón social del comercio.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="label-base">
                        Nombre Comercial *
                    </label>
                    <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial') }}" required
                        class="input-base"
                        placeholder="Ej: Supermercado El Ahorro">
                </div>

                <div>
                    <label class="label-base">
                        Razón Social
                    </label>
                    <input type="text" name="razon_social" value="{{ old('razon_social') }}"
                        class="input-base"
                        placeholder="Ej: Inversiones El Ahorro S.A.S.">
                </div>

                <div>
                    <label class="label-base">
                        Tipo de Documento *
                    </label>
                    <select name="tipo_documento" required class="input-base">
                        @foreach($tiposDocumento as $td)
                        <option value="{{ $td->value }}" {{ old('tipo_documento', 'NIT') === $td->value ? 'selected' : '' }}>
                            {{ $td->value }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label-base">
                        NIT / Número de Identificación *
                    </label>
                    <input type="text" name="nit" value="{{ old('nit') }}" required
                        class="input-base font-mono"
                        placeholder="Ej: 901234567">
                </div>

                <div>
                    <label class="label-base">
                        Dígito de Verificación (DV)
                    </label>
                    <input type="text" name="dv" value="{{ old('dv') }}" maxlength="2"
                        class="input-base font-mono"
                        placeholder="Ej: 3">
                </div>

                <div>
                    <label class="label-base">
                        Correo Electrónico
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="input-base"
                        placeholder="contacto@empresa.com">
                </div>

                <div>
                    <label class="label-base">
                        Teléfono / Celular
                    </label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                        class="input-base"
                        placeholder="Ej: 300 123 4567">
                </div>

                <div class="sm:col-span-2">
                    <label class="label-base">
                        Dirección Principal
                    </label>
                    <input type="text" name="direccion" value="{{ old('direccion') }}"
                        class="input-base"
                        placeholder="Ej: Carrera 50 # 10-20">
                </div>

                <div>
                    <label class="label-base">
                        Ciudad
                    </label>
                    <input type="text" name="ciudad" value="{{ old('ciudad') }}"
                        class="input-base"
                        placeholder="Ej: Medellín">
                </div>

                <div>
                    <label class="label-base">
                        Departamento
                    </label>
                    <input type="text" name="departamento" value="{{ old('departamento') }}"
                        class="input-base"
                        placeholder="Ej: Antioquia">
                </div>

                <div>
                    <label for="moneda" class="label-base">
                        Moneda *
                    </label>
                    <select name="moneda" id="moneda" required
                        x-model="monedaSeleccionada"
                        @change="onMonedaChange($event)"
                        class="input-base bg-white">
                        @foreach($monedas as $m)
                            <option value="{{ $m['codigo'] }}" {{ old('moneda', 'COP') === $m['codigo'] ? 'selected' : '' }}>
                                {{ $m['codigo'] }} — {{ $m['nombre'] }} ({{ $m['pais'] }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Moneda principal para transacciones y contabilidad.</p>
                </div>

                <div>
                    <label for="simbolo_moneda" class="label-base">
                        Símbolo Moneda *
                    </label>
                    <select name="simbolo_moneda" id="simbolo_moneda" required
                        x-model="simboloSeleccionado"
                        class="input-base bg-white">
                        @foreach($simbolos as $sym => $label)
                            <option value="{{ $sym }}" {{ old('simbolo_moneda', '$') === $sym ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Símbolo visible en precios y recibos.</p>
                </div>
            </div>
        </div>

        <!-- Sección 2: Sede / Sucursal Principal Inicial -->
        <div class="card p-6 sm:p-8 space-y-6">
            <div class="border-b border-slate-200 pb-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="h-5 w-5 rounded bg-slate-900 text-white text-xs font-semibold inline-flex items-center justify-center">2</span>
                    Sucursal Principal Inicial
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Toda empresa requiere al menos una sede física o punto de venta para operar inventario y cajas.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label-base">
                        Nombre de la Sucursal *
                    </label>
                    <input type="text" name="sucursal_nombre" value="{{ old('sucursal_nombre', 'Sede Principal Centro') }}" required
                        class="input-base"
                        placeholder="Ej: Sede Principal Centro">
                </div>

                <div>
                    <label class="label-base">
                        Código de Sucursal
                    </label>
                    <input type="text" name="sucursal_codigo" value="{{ old('sucursal_codigo', 'SUC-001') }}"
                        class="input-base font-mono uppercase"
                        placeholder="Ej: SUC-001">
                </div>

                <div>
                    <label class="label-base">
                        Dirección de la Sede
                    </label>
                    <input type="text" name="sucursal_direccion" value="{{ old('sucursal_direccion') }}"
                        class="input-base"
                        placeholder="Dejar en blanco para usar la dirección fiscal">
                </div>

                <div>
                    <label class="label-base">
                        Ciudad de la Sede
                    </label>
                    <input type="text" name="sucursal_ciudad" value="{{ old('sucursal_ciudad') }}"
                        class="input-base"
                        placeholder="Dejar en blanco para usar la ciudad fiscal">
                </div>
            </div>
        </div>

        <!-- Sección 3: Administrador Inicial -->
        <div class="card p-6 sm:p-8 space-y-6">
            <div class="border-b border-slate-200 pb-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="h-5 w-5 rounded bg-slate-900 text-white text-xs font-semibold inline-flex items-center justify-center">3</span>
                    Usuario Administrador de Empresa (Credenciales)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">El usuario con rol ADMIN_EMPRESA que gestionará este negocio.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label-base">
                        Nombre Completo *
                    </label>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}" required
                        class="input-base"
                        placeholder="Ej: Andrés Restrepo">
                </div>

                <div>
                    <label class="label-base">
                        Cargo
                    </label>
                    <input type="text" name="admin_cargo" value="{{ old('admin_cargo', 'Gerente General') }}"
                        class="input-base"
                        placeholder="Ej: Gerente General / Administrador">
                </div>

                <div>
                    <label class="label-base">
                        Correo de Acceso (Email) *
                    </label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" required
                        class="input-base"
                        placeholder="admin@comercio.com">
                </div>

                <div>
                    <label class="label-base">
                        Teléfono del Administrador
                    </label>
                    <input type="text" name="admin_telefono" value="{{ old('admin_telefono') }}"
                        class="input-base"
                        placeholder="Ej: 310 987 6543">
                </div>

                <div>
                    <label class="label-base">
                        Contraseña * (mínimo 8 caracteres)
                    </label>
                    <input type="password" name="admin_password" required
                        class="input-base"
                        placeholder="••••••••">
                </div>

                <div>
                    <label class="label-base">
                        Confirmar Contraseña *
                    </label>
                    <input type="password" name="admin_password_confirmation" required
                        class="input-base"
                        placeholder="••••••••">
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('empresas.index') }}" class="btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn-primary">
                Crear Empresa y Activar
            </button>
        </div>
    </form>
</div>
@endsection
