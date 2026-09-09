@extends('layouts.app')

@section('title', 'Perfil de la Empresa')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Perfil de la Empresa</h1>
            <p class="text-sm text-slate-500 mt-1">
                Configuración de datos comerciales y fiscales para {{ $empresa->nombre_comercial }}.
            </p>
        </div>
        <div class="flex items-center">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                <span class="h-2 w-2 rounded-full bg-emerald-500 mr-2"></span>
                Empresa Activa (ID: #{{ $empresa->id }})
            </span>
        </div>
    </div>

    <!-- Formulario Principal -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <form action="{{ route('empresa.perfil.update') }}" method="POST" class="p-6 sm:p-8 space-y-8">
            @csrf
            @method('PUT')

            <!-- Sección 1: Identificación Fiscal -->
            <div>
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">
                    Identificación Tributaria
                </h2>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tipo de Documento
                        </label>
                        <input type="text" readonly disabled value="{{ $empresa->tipo_documento?->value ?? 'NIT' }}"
                            class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-500 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Número de NIT
                        </label>
                        <input type="text" readonly disabled value="{{ $empresa->nit }}"
                            class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-500 font-mono cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Dígito de Verificación (DV)
                        </label>
                        <input type="text" readonly disabled value="{{ $empresa->dv ?? 'N/A' }}"
                            class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-500 font-mono cursor-not-allowed">
                    </div>
                </div>
                <p class="mt-2 text-xs text-slate-400">
                    * El NIT y documento fiscal son inmutables para garantizar la trazabilidad contable y tributaria.
                </p>
            </div>

            <!-- Sección 2: Información Comercial -->
            <div>
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">
                    Datos Comerciales
                </h2>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="nombre_comercial" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nombre Comercial <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombre_comercial" id="nombre_comercial" required
                            value="{{ old('nombre_comercial', $empresa->nombre_comercial) }}"
                            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="razon_social" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Razón Social Completa
                        </label>
                        <input type="text" name="razon_social" id="razon_social"
                            value="{{ old('razon_social', $empresa->razon_social) }}"
                            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Correo Electrónico de Contacto
                        </label>
                        <input type="email" name="email" id="email"
                            value="{{ old('email', $empresa->email) }}"
                            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="telefono" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Teléfono / Celular
                        </label>
                        <input type="text" name="telefono" id="telefono"
                            value="{{ old('telefono', $empresa->telefono) }}"
                            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Sección 3: Ubicación y Moneda -->
            <div>
                <h2 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">
                    Ubicación y Moneda Operativa
                </h2>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div class="sm:col-span-2">
                        <label for="direccion" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Dirección Principal
                        </label>
                        <input type="text" name="direccion" id="direccion"
                            value="{{ old('direccion', $empresa->direccion) }}"
                            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="ciudad" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Ciudad / Municipio
                        </label>
                        <input type="text" name="ciudad" id="ciudad"
                            value="{{ old('ciudad', $empresa->ciudad) }}"
                            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="departamento" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Departamento
                        </label>
                        <input type="text" name="departamento" id="departamento"
                            value="{{ old('departamento', $empresa->departamento) }}"
                            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="moneda" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Código Moneda <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="moneda" id="moneda" required
                            value="{{ old('moneda', $empresa->moneda) }}"
                            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="simbolo_moneda" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Símbolo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="simbolo_moneda" id="simbolo_moneda" required
                            value="{{ old('simbolo_moneda', $empresa->simbolo_moneda) }}"
                            class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Botón Guardar -->
            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit"
                    class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Guardar Cambios de la Empresa
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
