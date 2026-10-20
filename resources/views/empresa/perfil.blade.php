@extends('layouts.app')

@section('title', 'Perfil de la Empresa')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <x-page-header
        title="Perfil de la Empresa"
        subtitle="Configuración de datos comerciales y fiscales para {{ $empresa->nombre_comercial }}."
    >
        <x-slot:actions>
            <x-badge variant="success" :dot="true">
                Empresa Activa (ID: #{{ $empresa->id }})
            </x-badge>
        </x-slot:actions>
    </x-page-header>

    <!-- Formulario Principal -->
    <div class="card overflow-hidden">
        @php
            $monedas = $monedas ?? \App\Support\Monedas::todas();
            $simbolos = $simbolos ?? \App\Support\Monedas::simbolos();
            $mapaSimbolos = array_combine(array_keys($monedas), array_column($monedas, 'simbolo'));
        @endphp
        <form action="{{ route('empresa.perfil.update') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-8"
            x-data="{
                monedaSeleccionada: '{{ old('moneda', $empresa->moneda ?? 'COP') }}',
                simboloSeleccionado: '{{ old('simbolo_moneda', $empresa->simbolo_moneda ?? '$') }}',
                mapaSimbolos: {{ json_encode($mapaSimbolos) }},
                onMonedaChange(e) {
                    const cod = e.target.value;
                    if (this.mapaSimbolos[cod]) {
                        this.simboloSeleccionado = this.mapaSimbolos[cod];
                    }
                },
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
                <h2 class="text-xs font-semibold text-text-primary uppercase tracking-wider border-b border-border pb-2.5">
                    Identificación Tributaria
                </h2>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="label-base">
                            Tipo de Documento
                        </label>
                        <input type="text" readonly disabled value="{{ $empresa->tipo_documento?->value ?? 'NIT' }}"
                            class="input bg-surface-muted text-text-muted cursor-not-allowed">
                    </div>

                    <div>
                        <label class="label-base">
                            Número de NIT
                        </label>
                        <input type="text" readonly disabled value="{{ $empresa->nit }}"
                            class="input bg-surface-muted text-text-muted font-mono cursor-not-allowed">
                    </div>

                    <div>
                        <label class="label-base">
                            Dígito de Verificación (DV)
                        </label>
                        <input type="text" readonly disabled value="{{ $empresa->dv ?? 'N/A' }}"
                            class="input bg-surface-muted text-text-muted font-mono cursor-not-allowed">
                    </div>
                </div>
                <p class="mt-2 text-xs text-text-secondary">
                    * El NIT y documento fiscal son inmutables para garantizar la trazabilidad contable y tributaria.
                </p>
            </div>

            <!-- Sección 2: Información Comercial -->
            <div>
                <h2 class="text-xs font-semibold text-text-primary uppercase tracking-wider border-b border-border pb-2.5">
                    Datos Comerciales
                </h2>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nombre_comercial" class="label-base">
                            Nombre Comercial <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nombre_comercial" id="nombre_comercial" required
                            value="{{ old('nombre_comercial', $empresa->nombre_comercial) }}"
                            class="input @error('nombre_comercial') border-danger @enderror">
                        @error('nombre_comercial')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="razon_social" class="label-base">
                            Razón Social Completa
                        </label>
                        <input type="text" name="razon_social" id="razon_social"
                            value="{{ old('razon_social', $empresa->razon_social) }}"
                            class="input @error('razon_social') border-danger @enderror">
                        @error('razon_social')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="label-base">
                            Correo Electrónico de Contacto
                        </label>
                        <input type="email" name="email" id="email"
                            value="{{ old('email', $empresa->email) }}"
                            class="input @error('email') border-danger @enderror">
                        @error('email')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="telefono" class="label-base">
                            Teléfono / Celular
                        </label>
                        <input type="text" name="telefono" id="telefono"
                            value="{{ old('telefono', $empresa->telefono) }}"
                            class="input @error('telefono') border-danger @enderror">
                        @error('telefono')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Sección 3: Ubicación y Moneda -->
            <div>
                <h2 class="text-xs font-semibold text-text-primary uppercase tracking-wider border-b border-border pb-2.5">
                    Ubicación y Moneda Operativa
                </h2>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="sm:col-span-2">
                        <label for="direccion" class="label-base">
                            Dirección Principal
                        </label>
                        <input type="text" name="direccion" id="direccion"
                            value="{{ old('direccion', $empresa->direccion) }}"
                            class="input @error('direccion') border-danger @enderror">
                        @error('direccion')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="ciudad" class="label-base">
                            Ciudad / Municipio
                        </label>
                        <input type="text" name="ciudad" id="ciudad"
                            value="{{ old('ciudad', $empresa->ciudad) }}"
                            class="input @error('ciudad') border-danger @enderror">
                        @error('ciudad')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="departamento" class="label-base">
                            Departamento
                        </label>
                        <input type="text" name="departamento" id="departamento"
                            value="{{ old('departamento', $empresa->departamento) }}"
                            class="input @error('departamento') border-danger @enderror">
                        @error('departamento')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="moneda" class="label-base">
                            Código Moneda <span class="text-danger">*</span>
                        </label>
                        <select name="moneda" id="moneda" required
                            x-model="monedaSeleccionada"
                            @change="onMonedaChange($event)"
                            class="input bg-surface @error('moneda') border-danger @enderror">
                            @if(!isset($monedas[old('moneda', $empresa->moneda)]))
                                <option value="{{ old('moneda', $empresa->moneda) }}" selected>{{ old('moneda', $empresa->moneda) }} (Personalizada)</option>
                            @endif
                            @foreach($monedas as $m)
                                <option value="{{ $m['codigo'] }}" {{ old('moneda', $empresa->moneda) === $m['codigo'] ? 'selected' : '' }}>
                                    {{ $m['codigo'] }} — {{ $m['nombre'] }} ({{ $m['pais'] }})
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-text-secondary">Código ISO internacional de la moneda.</p>
                        @error('moneda')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="simbolo_moneda" class="label-base">
                            Símbolo <span class="text-danger">*</span>
                        </label>
                        <select name="simbolo_moneda" id="simbolo_moneda" required
                            x-model="simboloSeleccionado"
                            class="input bg-surface @error('simbolo_moneda') border-danger @enderror">
                            @if(!isset($simbolos[old('simbolo_moneda', $empresa->simbolo_moneda)]))
                                <option value="{{ old('simbolo_moneda', $empresa->simbolo_moneda) }}" selected>{{ old('simbolo_moneda', $empresa->simbolo_moneda) }} (Personalizado)</option>
                            @endif
                            @foreach($simbolos as $sym => $label)
                                <option value="{{ $sym }}" {{ old('simbolo_moneda', $empresa->simbolo_moneda) === $sym ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-text-secondary">Símbolo visible en tickets, ventas y reportes.</p>
                        @error('simbolo_moneda')
                            <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Sección 4: Identidad Visual e Imagen Institucional (Opción A) -->
            <div class="border-t border-border pt-6">
                <div class="flex items-center justify-between border-b border-border pb-2.5">
                    <div>
                        <h2 class="text-xs font-semibold text-text-primary uppercase tracking-wider">
                            Identidad Visual de la Empresa
                        </h2>
                        <p class="text-xs text-text-secondary mt-0.5">
                            Personaliza el logotipo corporativo que aparece en el encabezado, menú lateral y comprobantes.
                        </p>
                    </div>
                    <x-badge variant="neutral">Identidad Corporativa</x-badge>
                </div>

                <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <!-- Columna 1: Logotipo de la Empresa (6 cols) -->
                    <div class="lg:col-span-6 space-y-4">
                        <label class="label-base">
                            Logotipo de la Empresa
                        </label>

                        <div class="flex items-start gap-4">
                            <!-- Preview Box -->
                            <div class="relative shrink-0 w-24 h-24 rounded-lg border-2 border-dashed border-border-strong bg-surface-muted flex items-center justify-center overflow-hidden">
                                <!-- Preview de nuevo archivo seleccionado -->
                                <template x-if="logoPreview">
                                    <img :src="logoPreview" alt="Nuevo Logotipo" class="w-full h-full object-contain p-2 bg-surface">
                                </template>

                                <!-- Logo actual guardado en storage -->
                                <template x-if="!logoPreview && !eliminarLogo">
                                    @if($empresa->logo_path)
                                        <img src="{{ Storage::url($empresa->logo_path) }}" alt="{{ $empresa->nombre_comercial }}" class="w-full h-full object-contain p-2 bg-surface">
                                    @else
                                        <div class="text-center p-2">
                                            <div class="h-10 w-10 mx-auto rounded-lg bg-brand-50 text-brand-700 border border-brand-100 flex items-center justify-center font-semibold text-sm">
                                                {{ mb_strtoupper(mb_substr($empresa->nombre_comercial, 0, 2)) }}
                                            </div>
                                            <span class="text-[10px] text-text-muted mt-1 block font-medium">Sin logo</span>
                                        </div>
                                    @endif
                                </template>

                                <!-- Marcado para eliminar -->
                                <template x-if="eliminarLogo && !logoPreview">
                                    <div class="text-center p-2 text-danger">
                                        <svg class="h-6 w-6 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        <span class="text-[10px] block font-semibold mt-1">Se eliminará</span>
                                    </div>
                                </template>
                            </div>

                            <!-- Botones y Controles del Logo -->
                            <div class="flex-1 space-y-2">
                                <label class="btn btn-secondary cursor-pointer">
                                    <svg class="h-4 w-4 text-text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Cargar Logotipo
                                    <input type="file" name="logo" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml" class="sr-only" @change="fileChosen">
                                </label>

                                @if($empresa->logo_path)
                                    <div>
                                        <button type="button" @click="eliminarLogo = !eliminarLogo; logoPreview = null"
                                            class="inline-flex items-center text-xs font-semibold text-danger hover:text-danger-text transition">
                                            <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span x-text="eliminarLogo ? 'Cancelar eliminación' : 'Quitar logotipo actual'"></span>
                                        </button>
                                        <input type="hidden" name="eliminar_logo" :value="eliminarLogo ? '1' : '0'">
                                    </div>
                                @endif

                                <p class="text-xs text-text-secondary leading-relaxed">
                                    Recomendado: PNG o SVG transparente, cuadrado o ligeramente apaisado, máx. 2MB.
                                </p>
                                @error('logo')
                                    <p class="mt-1 text-xs text-danger">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Columna 2: Tarjeta Informativa Paleta Institucional (6 cols) -->
                    <div class="lg:col-span-6">
                        <div class="rounded-lg border border-border bg-surface-muted p-4 space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="h-9 w-9 rounded-md bg-brand-900 text-white flex items-center justify-center shrink-0">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-xs font-semibold text-text-primary uppercase tracking-wider">
                                        Paleta Institucional Unificada
                                    </h3>
                                    <p class="text-xs text-text-secondary mt-1 leading-relaxed">
                                        La plataforma utiliza un estándar visual financiero unificado para garantizar contraste WCAG AA, legibilidad en terminales POS y claridad en estados contables.
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2 pt-1">
                                <div class="flex items-center gap-2 rounded-md border border-border bg-surface px-2.5 py-2">
                                    <span class="h-4 w-4 rounded-sm bg-brand-900 shrink-0"></span>
                                    <span class="text-[11px] font-medium text-text-primary truncate">Azul Marino</span>
                                </div>
                                <div class="flex items-center gap-2 rounded-md border border-border bg-surface px-2.5 py-2">
                                    <span class="h-4 w-4 rounded-sm bg-brand-600 shrink-0"></span>
                                    <span class="text-[11px] font-medium text-text-primary truncate">Azul Primario</span>
                                </div>
                                <div class="flex items-center gap-2 rounded-md border border-border bg-surface px-2.5 py-2">
                                    <span class="h-4 w-4 rounded-sm bg-surface-muted border border-border-strong shrink-0"></span>
                                    <span class="text-[11px] font-medium text-text-primary truncate">Gris Neutro</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón Guardar -->
            <div class="pt-4 border-t border-border flex justify-end">
                <button type="submit" class="btn btn-primary w-full sm:w-auto">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Guardar Cambios de la Empresa
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
