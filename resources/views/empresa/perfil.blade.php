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
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                <span class="h-2 w-2 rounded-full bg-emerald-500 mr-2"></span>
                Empresa Activa (ID: #{{ $empresa->id }})
            </span>
        </div>
    </div>

    <!-- Formulario Principal -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <form action="{{ route('empresa.perfil.update') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-8"
            x-data="{
                colorSeleccionado: '{{ old('color_primario', $empresa->configuraciones['color_primario'] ?? 'indigo') }}',
                logoPreview: null,
                eliminarLogo: false,
                fileChosen(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    this.eliminarLogo = false;
                    const reader = new FileReader();
                    reader.onload = (e) => { this.logoPreview = e.target.result; };
                    reader.readAsDataURL(file);
                }
            }">
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

            <!-- Sección 4: Identidad Visual y Personalización de la Plataforma -->
            <div class="border-t border-slate-100 pt-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">
                            Identidad Visual y Color de la Plataforma
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Personaliza el logotipo que aparece en el encabezado del sistema y el color temático de la interfaz.
                        </p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
                        Marca y Tema
                    </span>
                </div>

                <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <!-- Columna 1: Logotipo de la Empresa (5 cols) -->
                    <div class="lg:col-span-5 space-y-4">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Logotipo de la Empresa
                        </label>

                        <div class="flex items-start gap-4">
                            <!-- Preview Box -->
                            <div class="relative flex-shrink-0 w-24 h-24 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center overflow-hidden shadow-inner">
                                <!-- Preview de nuevo archivo seleccionado -->
                                <template x-if="logoPreview">
                                    <img :src="logoPreview" alt="Nuevo Logotipo" class="w-full h-full object-contain p-2 bg-white">
                                </template>

                                <!-- Logo actual guardado en storage (si no hay preview nuevo y no está marcado para eliminar) -->
                                <template x-if="!logoPreview && !eliminarLogo">
                                    @if($empresa->logo_path)
                                        <img src="{{ Storage::url($empresa->logo_path) }}" alt="{{ $empresa->nombre_comercial }}" class="w-full h-full object-contain p-2 bg-white">
                                    @else
                                        <div class="text-center p-2">
                                            <div class="h-10 w-10 mx-auto rounded-xl bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-sm">
                                                {{ mb_strtoupper(mb_substr($empresa->nombre_comercial, 0, 2)) }}
                                            </div>
                                            <span class="text-[10px] text-slate-400 mt-1 block font-medium">Sin logo</span>
                                        </div>
                                    @endif
                                </template>

                                <!-- Marcado para eliminar -->
                                <template x-if="eliminarLogo && !logoPreview">
                                    <div class="text-center p-2 text-rose-500">
                                        <svg class="h-6 w-6 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span class="text-[10px] block font-semibold mt-1">Se eliminará</span>
                                    </div>
                                </template>
                            </div>

                            <!-- Botones y Controles del Logo -->
                            <div class="flex-1 space-y-2">
                                <label class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-xl text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 cursor-pointer shadow-sm transition">
                                    <svg class="h-4 w-4 mr-2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Cargar Logotipo
                                    <input type="file" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml" class="sr-only" @change="fileChosen">
                                </label>

                                @if($empresa->logo_path)
                                    <div>
                                        <button type="button" @click="eliminarLogo = !eliminarLogo; logoPreview = null"
                                            class="inline-flex items-center text-xs font-semibold text-rose-600 hover:text-rose-800 transition">
                                            <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span x-text="eliminarLogo ? 'Cancelar eliminación' : 'Quitar logotipo actual'"></span>
                                        </button>
                                        <input type="hidden" name="eliminar_logo" :value="eliminarLogo ? '1' : '0'">
                                    </div>
                                @endif

                                <p class="text-[11px] text-slate-400 leading-relaxed">
                                    Recomendado: archivo PNG o SVG transparente, cuadrado o ligeramente apaisado, máx. 2MB.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Columna 2: Selector de Color de la Plataforma (7 cols) -->
                    <div class="lg:col-span-7 space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Color Temático de la Plataforma
                            </label>
                            <span class="text-xs text-slate-500">
                                Seleccionado: <strong class="capitalize text-slate-900" x-text="colorSeleccionado"></strong>
                            </span>
                        </div>

                        @php
                            $paletaColores = [
                                'indigo'  => ['nombre' => 'Índigo',     'hex' => '#6366f1', 'desc' => 'Clásico / Profesional'],
                                'blue'    => ['nombre' => 'Azul',       'hex' => '#2563eb', 'desc' => 'Corporativo'],
                                'emerald' => ['nombre' => 'Esmeralda',  'hex' => '#059669', 'desc' => 'Retail / Fresco'],
                                'violet'  => ['nombre' => 'Violeta',    'hex' => '#7c3aed', 'desc' => 'Moderno / Creativo'],
                                'rose'    => ['nombre' => 'Carmín',     'hex' => '#e11d48', 'desc' => 'Enérgico / Cálido'],
                                'orange'  => ['nombre' => 'Naranja',    'hex' => '#ea580c', 'desc' => 'Ferretería / Obra'],
                                'amber'   => ['nombre' => 'Ámbar',      'hex' => '#d97706', 'desc' => 'Industrial'],
                                'slate'   => ['nombre' => 'Pizarra',    'hex' => '#334155', 'desc' => 'Sobrio / Neutro'],
                            ];
                        @endphp

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach($paletaColores as $key => $col)
                                <label class="relative flex flex-col items-center p-3 rounded-2xl border cursor-pointer transition text-center select-none"
                                    :class="colorSeleccionado === '{{ $key }}' ? 'border-slate-900 ring-2 ring-slate-900/15 bg-slate-50/80 shadow-sm' : 'border-slate-200 hover:border-slate-300 bg-white'">
                                    <input type="radio" name="color_primario" value="{{ $key }}" class="sr-only"
                                        x-model="colorSeleccionado">
                                    
                                    <!-- Swatch Círculo -->
                                    <span class="w-8 h-8 rounded-full shadow-sm flex items-center justify-center transition transform"
                                        :class="colorSeleccionado === '{{ $key }}' ? 'scale-110 ring-2 ring-offset-2 ring-slate-900' : ''"
                                        style="background-color: {{ $col['hex'] }};">
                                        <svg x-show="colorSeleccionado === '{{ $key }}'" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </span>

                                    <span class="text-xs font-bold text-slate-800 mt-2">
                                        {{ $col['nombre'] }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $col['desc'] }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p class="text-[11px] text-slate-400">
                            * El color seleccionado se aplicará al menú lateral, encabezados y botones de acción principal en toda la aplicación.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Botón Guardar -->
            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit"
                    class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
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
