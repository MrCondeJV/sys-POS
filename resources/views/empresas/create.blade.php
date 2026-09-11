@extends('layouts.app')

@section('title', 'Registrar Nueva Empresa')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('empresas.index') }}" class="hover:text-indigo-600 transition">Empresas</a>
                <span>/</span>
                <span>Registro Asistido</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Registrar Nueva Empresa (Tenant)</h1>
            <p class="text-sm text-slate-500">Configura los datos fiscales, la sede inicial y las credenciales del administrador.</p>
        </div>
        <a href="{{ route('empresas.index') }}"
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
        <div class="bg-white rounded-3xl border border-slate-200/70 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="h-6 w-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-black inline-flex items-center justify-center">1</span>
                    Información Comercial y Fiscal de la Empresa
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Identificación legal y razón social del comercio.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Nombre Comercial *
                    </label>
                    <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial') }}" required
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: Supermercado El Ahorro">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Razón Social
                    </label>
                    <input type="text" name="razon_social" value="{{ old('razon_social') }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: Inversiones El Ahorro S.A.S.">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Tipo de Documento *
                    </label>
                    <select name="tipo_documento" required
                        class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($tiposDocumento as $td)
                        <option value="{{ $td->value }}" {{ old('tipo_documento', 'NIT') === $td->value ? 'selected' : '' }}>
                            {{ $td->value }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        NIT / Número de Identificación *
                    </label>
                    <input type="text" name="nit" value="{{ old('nit') }}" required
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: 901234567">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Dígito de Verificación (DV)
                    </label>
                    <input type="text" name="dv" value="{{ old('dv') }}" maxlength="2"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: 3">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Correo Electrónico
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="contacto@empresa.com">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Teléfono / Celular
                    </label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: 300 123 4567">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Dirección Principal
                    </label>
                    <input type="text" name="direccion" value="{{ old('direccion') }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: Carrera 50 # 10-20">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Ciudad
                    </label>
                    <input type="text" name="ciudad" value="{{ old('ciudad') }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: Medellín">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Departamento
                    </label>
                    <input type="text" name="departamento" value="{{ old('departamento') }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: Antioquia">
                </div>

                <div>
                    <label for="moneda" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Moneda *
                    </label>
                    <select name="moneda" id="moneda" required
                        x-model="monedaSeleccionada"
                        @change="onMonedaChange($event)"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        @foreach($monedas as $m)
                            <option value="{{ $m['codigo'] }}" {{ old('moneda', 'COP') === $m['codigo'] ? 'selected' : '' }}>
                                {{ $m['codigo'] }} — {{ $m['nombre'] }} ({{ $m['pais'] }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-slate-400">Moneda principal para transacciones y contabilidad.</p>
                </div>

                <div>
                    <label for="simbolo_moneda" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Símbolo Moneda *
                    </label>
                    <select name="simbolo_moneda" id="simbolo_moneda" required
                        x-model="simboloSeleccionado"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        @foreach($simbolos as $sym => $label)
                            <option value="{{ $sym }}" {{ old('simbolo_moneda', '$') === $sym ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[11px] text-slate-400">Símbolo visible en precios y recibos.</p>
                </div>
            </div>
        </div>

        <!-- Sección 2: Sede / Sucursal Principal Inicial -->
        <div class="bg-white rounded-3xl border border-slate-200/70 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="h-6 w-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-black inline-flex items-center justify-center">2</span>
                    Sucursal Principal Inicial
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Toda empresa requiere al menos una sede física o punto de venta para operar inventario y cajas.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Nombre de la Sucursal *
                    </label>
                    <input type="text" name="sucursal_nombre" value="{{ old('sucursal_nombre', 'Sede Principal Centro') }}" required
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: Sede Principal Centro">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Código de Sucursal
                    </label>
                    <input type="text" name="sucursal_codigo" value="{{ old('sucursal_codigo', 'SUC-001') }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: SUC-001">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Dirección de la Sede
                    </label>
                    <input type="text" name="sucursal_direccion" value="{{ old('sucursal_direccion') }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Dejar en blanco para usar la dirección fiscal">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Ciudad de la Sede
                    </label>
                    <input type="text" name="sucursal_ciudad" value="{{ old('sucursal_ciudad') }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Dejar en blanco para usar la ciudad fiscal">
                </div>
            </div>
        </div>

        <!-- Sección 3: Administrador Inicial -->
        <div class="bg-white rounded-3xl border border-slate-200/70 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="h-6 w-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-black inline-flex items-center justify-center">3</span>
                    Usuario Administrador de Empresa (Credenciales)
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">El usuario con rol ADMIN_EMPRESA que gestionará este negocio.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Nombre Completo *
                    </label>
                    <input type="text" name="admin_name" value="{{ old('admin_name') }}" required
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: Andrés Restrepo">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Cargo
                    </label>
                    <input type="text" name="admin_cargo" value="{{ old('admin_cargo', 'Gerente General') }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: Gerente General / Administrador">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Correo de Acceso (Email) *
                    </label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" required
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="admin@comercio.com">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Teléfono del Administrador
                    </label>
                    <input type="text" name="admin_telefono" value="{{ old('admin_telefono') }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ej: 310 987 6543">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Contraseña * (mínimo 8 caracteres)
                    </label>
                    <input type="password" name="admin_password" required
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="••••••••">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Confirmar Contraseña *
                    </label>
                    <input type="password" name="admin_password_confirmation" required
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="••••••••">
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('empresas.index') }}"
                class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Cancelar
            </a>
            <button type="submit"
                class="inline-flex items-center px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                Crear Empresa y Activar
            </button>
        </div>
    </form>
</div>
@endsection
