@extends('layouts.app')

@section('title', 'Editar Empresa — ' . $empresa->nombre_comercial)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('empresas.index') }}" class="hover:text-indigo-600 transition">Empresas</a>
                <span>/</span>
                <span>Editar</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Editar Empresa: {{ $empresa->nombre_comercial }}</h1>
            <p class="text-sm text-slate-500">Actualiza los datos fiscales, monedas y estado de operación.</p>
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

    <form method="POST" action="{{ route('empresas.update', $empresa) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-3xl border border-slate-200/70 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Nombre Comercial *
                    </label>
                    <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial', $empresa->nombre_comercial) }}" required
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Razón Social
                    </label>
                    <input type="text" name="razon_social" value="{{ old('razon_social', $empresa->razon_social) }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Tipo de Documento *
                    </label>
                    <select name="tipo_documento" required
                        class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($tiposDocumento as $td)
                        <option value="{{ $td->value }}" {{ old('tipo_documento', $empresa->tipo_documento?->value ?? $empresa->tipo_documento) === $td->value ? 'selected' : '' }}>
                            {{ $td->value }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        NIT / Número de Identificación *
                    </label>
                    <input type="text" name="nit" value="{{ old('nit', $empresa->nit) }}" required
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Dígito de Verificación (DV)
                    </label>
                    <input type="text" name="dv" value="{{ old('dv', $empresa->dv) }}" maxlength="2"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Correo Electrónico
                    </label>
                    <input type="email" name="email" value="{{ old('email', $empresa->email) }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Teléfono / Celular
                    </label>
                    <input type="text" name="telefono" value="{{ old('telefono', $empresa->telefono) }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Dirección Principal
                    </label>
                    <input type="text" name="direccion" value="{{ old('direccion', $empresa->direccion) }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Ciudad
                    </label>
                    <input type="text" name="ciudad" value="{{ old('ciudad', $empresa->ciudad) }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Departamento
                    </label>
                    <input type="text" name="departamento" value="{{ old('departamento', $empresa->departamento) }}"
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Moneda *
                    </label>
                    <input type="text" name="moneda" value="{{ old('moneda', $empresa->moneda) }}" required
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Símbolo Moneda *
                    </label>
                    <input type="text" name="simbolo_moneda" value="{{ old('simbolo_moneda', $empresa->simbolo_moneda) }}" required
                        class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                        Estado de la Empresa *
                    </label>
                    <select name="estado" required
                        class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
            <a href="{{ route('empresas.index') }}"
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
