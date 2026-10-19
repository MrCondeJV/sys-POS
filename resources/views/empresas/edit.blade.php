@extends('layouts.app')

@section('title', 'Editar Empresa — ' . $empresa->nombre_comercial)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                <a href="{{ route('empresas.index') }}" class="hover:text-brand-600 transition">Empresas</a>
                <span>&rsaquo;</span>
                <span class="text-slate-700 font-semibold">Editar</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Editar Empresa: {{ $empresa->nombre_comercial }}</h1>
            <p class="text-sm text-slate-500">Actualiza los datos fiscales, monedas y estado de operación.</p>
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
    <form method="POST" action="{{ route('empresas.update', $empresa) }}" class="space-y-6"
        x-data="{
            monedaSeleccionada: '{{ old('moneda', $empresa->moneda) }}',
            simboloSeleccionado: '{{ old('simbolo_moneda', $empresa->simbolo_moneda) }}',
            mapaSimbolos: {{ json_encode($mapaSimbolos) }},
            onMonedaChange(e) {
                const cod = e.target.value;
                if (this.mapaSimbolos[cod]) {
                    this.simboloSeleccionado = this.mapaSimbolos[cod];
                }
            }
        }">
        @csrf
        @method('PUT')

        <div class="card p-6 sm:p-8 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="label-base">
                        Nombre Comercial *
                    </label>
                    <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial', $empresa->nombre_comercial) }}" required
                        class="input-base">
                </div>

                <div>
                    <label class="label-base">
                        Razón Social
                    </label>
                    <input type="text" name="razon_social" value="{{ old('razon_social', $empresa->razon_social) }}"
                        class="input-base">
                </div>

                <div>
                    <label class="label-base">
                        Tipo de Documento *
                    </label>
                    <select name="tipo_documento" required class="input-base">
                        @foreach($tiposDocumento as $td)
                        <option value="{{ $td->value }}" {{ old('tipo_documento', $empresa->tipo_documento?->value ?? $empresa->tipo_documento) === $td->value ? 'selected' : '' }}>
                            {{ $td->value }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label-base">
                        NIT / Número de Identificación *
                    </label>
                    <input type="text" name="nit" value="{{ old('nit', $empresa->nit) }}" required
                        class="input-base font-mono">
                </div>

                <div>
                    <label class="label-base">
                        Dígito de Verificación (DV)
                    </label>
                    <input type="text" name="dv" value="{{ old('dv', $empresa->dv) }}" maxlength="2"
                        class="input-base font-mono">
                </div>

                <div>
                    <label class="label-base">
                        Correo Electrónico
                    </label>
                    <input type="email" name="email" value="{{ old('email', $empresa->email) }}"
                        class="input-base">
                </div>

                <div>
                    <label class="label-base">
                        Teléfono / Celular
                    </label>
                    <input type="text" name="telefono" value="{{ old('telefono', $empresa->telefono) }}"
                        class="input-base">
                </div>

                <div class="sm:col-span-2">
                    <label class="label-base">
                        Dirección Principal
                    </label>
                    <input type="text" name="direccion" value="{{ old('direccion', $empresa->direccion) }}"
                        class="input-base">
                </div>

                <div>
                    <label class="label-base">
                        Ciudad
                    </label>
                    <input type="text" name="ciudad" value="{{ old('ciudad', $empresa->ciudad) }}"
                        class="input-base">
                </div>

                <div>
                    <label class="label-base">
                        Departamento
                    </label>
                    <input type="text" name="departamento" value="{{ old('departamento', $empresa->departamento) }}"
                        class="input-base">
                </div>

                <div>
                    <label for="moneda" class="label-base">
                        Moneda *
                    </label>
                    <select name="moneda" id="moneda" required
                        x-model="monedaSeleccionada"
                        @change="onMonedaChange($event)"
                        class="input-base bg-white">
                        @if(!isset($monedas[old('moneda', $empresa->moneda)]))
                            <option value="{{ old('moneda', $empresa->moneda) }}" selected>{{ old('moneda', $empresa->moneda) }} (Personalizada)</option>
                        @endif
                        @foreach($monedas as $m)
                            <option value="{{ $m['codigo'] }}" {{ old('moneda', $empresa->moneda) === $m['codigo'] ? 'selected' : '' }}>
                                {{ $m['codigo'] }} — {{ $m['nombre'] }} ({{ $m['pais'] }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Código ISO internacional de la divisa.</p>
                </div>

                <div>
                    <label for="simbolo_moneda" class="label-base">
                        Símbolo Moneda *
                    </label>
                    <select name="simbolo_moneda" id="simbolo_moneda" required
                        x-model="simboloSeleccionado"
                        class="input-base bg-white">
                        @if(!isset($simbolos[old('simbolo_moneda', $empresa->simbolo_moneda)]))
                            <option value="{{ old('simbolo_moneda', $empresa->simbolo_moneda) }}" selected>{{ old('simbolo_moneda', $empresa->simbolo_moneda) }} (Personalizado)</option>
                        @endif
                        @foreach($simbolos as $sym => $label)
                            <option value="{{ $sym }}" {{ old('simbolo_moneda', $empresa->simbolo_moneda) === $sym ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Símbolo visible en precios y recibos.</p>
                </div>

                <div class="sm:col-span-2">
                    <label class="label-base">
                        Estado de la Empresa *
                    </label>
                    <select name="estado" required class="input-base">
                        <option value="ACTIVO" {{ old('estado', $empresa->estado?->value ?? $empresa->estado) === 'ACTIVO' ? 'selected' : '' }}>
                            ACTIVO (Operando normalmente)
                        </option>
                        <option value="INACTIVO" {{ old('estado', $empresa->estado?->value ?? $empresa->estado) === 'INACTIVO' ? 'selected' : '' }}>
                            INACTIVO (Suspendido)
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('empresas.index') }}" class="btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn-primary">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
