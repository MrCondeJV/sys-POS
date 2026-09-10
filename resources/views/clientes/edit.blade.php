@extends('layouts.app')

@section('title', 'Editar Cliente')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb & Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('clientes.index') }}" class="hover:text-indigo-600 transition">Clientes</a>
                <span>/</span>
                <span class="text-slate-800">Editar Cliente</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Editar: {{ $cliente->razon_social }}
                </h1>
                @if($cliente->isConsumidorFinal())
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        CONSUMIDOR FINAL POS
                    </span>
                @endif
            </div>
            <p class="text-sm text-slate-500">Actualiza los datos tributarios, de contacto o condiciones de cartera.</p>
        </div>
        <div>
            <a href="{{ route('clientes.index') }}"
                class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al listado
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800">
            <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span class="text-sm font-bold">Por favor corrige los siguientes errores:</span>
            </div>
            <ul class="list-disc list-inside mt-2 text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($cliente->isConsumidorFinal())
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 flex items-start space-x-3">
            <svg class="h-5 w-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-xs">
                <span class="font-bold block">Modificando Consumidor Final de POS</span>
                Este registro se usa automáticamente en la terminal de venta rápida cuando no se indica un cliente con documento.
                Se recomienda mantener su documento como 222222222222.
            </div>
        </div>
    @endif

    <form action="{{ route('clientes.update', $cliente) }}" method="POST"
          x-data="{
              tipoPersona: '{{ old('tipo_persona', $cliente->tipo_persona->value) }}',
              tipoDoc: '{{ old('tipo_documento', $cliente->tipo_documento->value) }}',
              cupoCredito: '{{ old('cupo_credito', $cliente->cupo_credito) }}',
              plazoDias: '{{ old('plazo_dias', $cliente->plazo_dias) }}',
              onTipoPersonaChange() {
                  if (this.tipoPersona === 'JURIDICA' && this.tipoDoc === 'CC') {
                      this.tipoDoc = 'NIT';
                  } else if (this.tipoPersona === 'NATURAL' && this.tipoDoc === 'NIT') {
                      this.tipoDoc = 'CC';
                  }
              }
          }"
          class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Tarjeta 1: Identificación y Tipo de Persona -->
        <div class="bg-white rounded-3xl border border-slate-200/70 shadow-sm p-6 space-y-5">
            <div class="flex items-center space-x-3 pb-4 border-b border-slate-100">
                <div class="h-8 w-8 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center font-bold text-sm">
                    1
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Identificación & Naturaleza Jurídica</h2>
                    <p class="text-xs text-slate-500">Define si es una persona natural o jurídica según el marco DIAN.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Tipo de Persona <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center justify-center p-3 border rounded-xl cursor-pointer text-sm font-medium transition"
                               :class="tipoPersona === 'NATURAL' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700 ring-2 ring-indigo-500/20' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            <input type="radio" name="tipo_persona" value="NATURAL" x-model="tipoPersona" @change="onTipoPersonaChange" class="sr-only">
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Persona Natural
                        </label>
                        <label class="flex items-center justify-center p-3 border rounded-xl cursor-pointer text-sm font-medium transition"
                               :class="tipoPersona === 'JURIDICA' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700 ring-2 ring-indigo-500/20' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            <input type="radio" name="tipo_persona" value="JURIDICA" x-model="tipoPersona" @change="onTipoPersonaChange" class="sr-only">
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Persona Jurídica
                        </label>
                    </div>
                    @error('tipo_persona')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="tipo_documento" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Tipo Documento <span class="text-rose-500">*</span>
                        </label>
                        <select id="tipo_documento" name="tipo_documento" x-model="tipoDoc"
                            class="block w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            @foreach(\App\Enums\TipoDocumentoIdentidad::cases() as $doc)
                                <option value="{{ $doc->value }}">{{ $doc->value }} - {{ $doc->label() }}</option>
                            @endforeach
                        </select>
                        @error('tipo_documento')
                            <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="numero_documento" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Número Documento <span class="text-rose-500">*</span>
                        </label>
                        @if($cliente->isConsumidorFinal())
                            <input type="text" id="numero_documento" value="{{ $cliente->numero_documento }}" disabled
                                class="block w-full py-2.5 px-3 border border-slate-200 bg-slate-100 rounded-xl text-sm text-slate-500 cursor-not-allowed">
                        @else
                            <input type="text" id="numero_documento" name="numero_documento" value="{{ old('numero_documento', $cliente->numero_documento) }}" required
                                placeholder="Ej: 1045678901 ó 901234567-8"
                                class="block w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                            @error('numero_documento')
                                <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                            @enderror
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label for="razon_social" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        <span x-text="tipoPersona === 'JURIDICA' ? 'Razón Social / Empresa *' : 'Nombre Completo *'">Nombre Completo *</span>
                    </label>
                    <input type="text" id="razon_social" name="razon_social" value="{{ old('razon_social', $cliente->razon_social) }}" required
                        placeholder="Ej: María Camila Restrepo / Ferretería Caribe S.A.S."
                        class="block w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('razon_social')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nombre_comercial" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Nombre Comercial <span class="text-slate-400 text-[10px] font-normal">(Opcional)</span>
                    </label>
                    <input type="text" id="nombre_comercial" name="nombre_comercial" value="{{ old('nombre_comercial', $cliente->nombre_comercial) }}"
                        placeholder="Ej: Almacén El Constructor"
                        class="block w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('nombre_comercial')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Tarjeta 2: Contacto y Ubicación -->
        <div class="bg-white rounded-3xl border border-slate-200/70 shadow-sm p-6 space-y-5">
            <div class="flex items-center space-x-3 pb-4 border-b border-slate-100">
                <div class="h-8 w-8 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center font-bold text-sm">
                    2
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Contacto & Domicilio</h2>
                    <p class="text-xs text-slate-500">Datos de localización física, comunicaciones y facturas electrónicas.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="telefono" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Teléfono / Móvil
                    </label>
                    <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $cliente->telefono) }}"
                        placeholder="Ej: 3001234567"
                        class="block w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('telefono')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Correo Electrónico
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email', $cliente->email) }}"
                        placeholder="cliente@ejemplo.com"
                        class="block w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('email')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-1">
                    <label for="departamento" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Departamento
                    </label>
                    <input type="text" id="departamento" name="departamento" value="{{ old('departamento', $cliente->departamento) }}"
                        placeholder="Ej: Sucre, Antioquia..."
                        class="block w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                </div>

                <div class="sm:col-span-1">
                    <label for="ciudad" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Ciudad / Municipio
                    </label>
                    <input type="text" id="ciudad" name="ciudad" value="{{ old('ciudad', $cliente->ciudad) }}"
                        placeholder="Ej: Coveñas, Sincelejo..."
                        class="block w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                </div>

                <div class="sm:col-span-1">
                    <label for="direccion" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Dirección Domiciliar
                    </label>
                    <input type="text" id="direccion" name="direccion" value="{{ old('direccion', $cliente->direccion) }}"
                        placeholder="Ej: Calle 15 # 20-30"
                        class="block w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                </div>
            </div>
        </div>

        <!-- Tarjeta 3: Condiciones Comerciales & Línea de Crédito -->
        <div class="bg-white rounded-3xl border border-slate-200/70 shadow-sm p-6 space-y-5">
            <div class="flex items-center space-x-3 pb-4 border-b border-slate-100">
                <div class="h-8 w-8 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center font-bold text-sm">
                    3
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Condiciones Comerciales & Crédito</h2>
                    <p class="text-xs text-slate-500">Parámetros para ventas a crédito, plazos de pago y estado operativo.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="cupo_credito" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Cupo de Crédito ($ COP)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-bold">$</div>
                        <input type="number" step="0.01" min="0" id="cupo_credito" name="cupo_credito" value="{{ old('cupo_credito', $cliente->cupo_credito) }}"
                            class="block w-full pl-8 pr-3 py-2.5 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Dejar en 0 si no maneja crédito.</p>
                    @error('cupo_credito')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="plazo_dias" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Plazo de Pago (Días)
                    </label>
                    <input type="number" min="0" max="365" id="plazo_dias" name="plazo_dias" value="{{ old('plazo_dias', $cliente->plazo_dias) }}"
                        placeholder="Ej: 15, 30, 60"
                        class="block w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <p class="text-[11px] text-slate-400 mt-1">Días autorizados para cancelar la factura.</p>
                    @error('plazo_dias')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="estado" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Estado del Cliente <span class="text-rose-500">*</span>
                    </label>
                    <select id="estado" name="estado"
                        class="block w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <option value="ACTIVO" @selected(old('estado', $cliente->estado->value) === 'ACTIVO')>Activo (Habilitado)</option>
                        <option value="INACTIVO" @selected(old('estado', $cliente->estado->value) === 'INACTIVO')>Inactivo (Bloqueado)</option>
                    </select>
                    @error('estado')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Botones de Guardado -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('clientes.index') }}"
                class="px-5 py-2.5 border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Cancelar
            </a>
            <button type="submit"
                class="inline-flex items-center px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Actualizar Cliente
            </button>
        </div>
    </form>

</div>
@endsection
