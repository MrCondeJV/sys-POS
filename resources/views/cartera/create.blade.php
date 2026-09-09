@extends('layouts.app')

@section('title', 'Nueva Cuenta por Cobrar')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb & Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('cartera.index') }}" class="hover:text-indigo-600 transition">Crédito & Cartera</a>
                <span>/</span>
                <span class="text-slate-800">Nueva Cuenta por Cobrar</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Aperturar Cuenta por Cobrar</h1>
            <p class="text-sm text-slate-500">Registra una obligación a crédito comercial, saldo inicial o pagaré de cliente.</p>
        </div>
        <div>
            <a href="{{ route('cartera.index') }}"
                class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a Cartera
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

    <form action="{{ route('cartera.store') }}" method="POST"
          x-data="{
              clientes: {{ json_encode($clientes->map(fn($c) => [
                  'id' => $c->id,
                  'nombre' => $c->razon_social,
                  'documento' => $c->tipo_documento->value . ' ' . $c->numero_documento,
                  'cupo' => (float) $c->cupo_credito,
                  'disponible' => $c->cupoDisponible(),
                  'plazo' => (int) $c->plazo_dias,
              ])) }},
              clienteId: '{{ old('cliente_id', '') }}',
              fechaEmision: '{{ old('fecha_emision', now()->toDateString()) }}',
              fechaVencimiento: '{{ old('fecha_vencimiento', '') }}',
              monto: {{ old('monto_total', 0) }},
              get clienteSeleccionado() {
                  return this.clientes.find(c => c.id == this.clienteId) || null;
              },
              onClienteChange() {
                  if (this.clienteSeleccionado && this.clienteSeleccionado.plazo > 0) {
                      let emision = new Date(this.fechaEmision + 'T00:00:00');
                      emision.setDate(emision.getDate() + this.clienteSeleccionado.plazo);
                      this.fechaVencimiento = emision.toISOString().split('T')[0];
                  }
              }
          }"
          class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
            <!-- Selección de Cliente -->
            <div>
                <label for="cliente_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Cliente Deudor <span class="text-rose-500">*</span>
                </label>
                <select id="cliente_id" name="cliente_id" x-model="clienteId" @change="onClienteChange" required
                    class="block w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <option value="">-- Selecciona el cliente --</option>
                    <template x-for="c in clientes" :key="c.id">
                        <option :value="c.id" x-text="c.nombre + ' (' + c.documento + ')'" :selected="c.id == clienteId"></option>
                    </template>
                </select>
                @error('cliente_id')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Ficha reactiva del cliente seleccionado -->
            <template x-if="clienteSeleccionado">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Línea de Crédito Autorizada:</span>
                        <span class="font-bold text-slate-900" x-text="'$' + clienteSeleccionado.cupo.toLocaleString('es-CO', {minimumFractionDigits: 2})"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Cupo Disponible:</span>
                        <span class="font-black text-emerald-600" x-text="'$' + clienteSeleccionado.disponible.toLocaleString('es-CO', {minimumFractionDigits: 2})"></span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Plazo Predeterminado:</span>
                        <span class="font-bold text-indigo-600" x-text="clienteSeleccionado.plazo + ' días'"></span>
                    </div>
                    <div x-show="monto > clienteSeleccionado.disponible && clienteSeleccionado.cupo > 0" class="pt-2 text-xs font-semibold text-amber-700 flex items-center">
                        <svg class="h-4 w-4 mr-1 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Atención: El monto ingresado excede el cupo disponible del cliente.
                    </div>
                </div>
            </template>

            <!-- Concepto -->
            <div>
                <label for="concepto" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Concepto de la Cuenta / Factura <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="concepto" name="concepto" value="{{ old('concepto') }}" required
                    placeholder="Ej: Saldo Inicial / Crédito de Materiales Factura #1234"
                    class="block w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                @error('concepto')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Monto Total -->
            <div>
                <label for="monto_total" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Monto Total de la Obligación ($ COP) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold">$</div>
                    <input type="number" step="0.01" min="0.01" id="monto_total" name="monto_total" x-model.number="monto" required
                        placeholder="0.00"
                        class="block w-full pl-9 pr-3 py-2.5 border border-slate-300 rounded-xl text-base font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                </div>
                @error('monto_total')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fechas de Emisión y Vencimiento -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="fecha_emision" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Fecha de Emisión <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="fecha_emision" name="fecha_emision" x-model="fechaEmision" @change="onClienteChange" required
                        class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('fecha_emision')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="fecha_vencimiento" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Fecha Límite de Vencimiento <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" x-model="fechaVencimiento" required
                        class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('fecha_vencimiento')
                        <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Observaciones -->
            <div>
                <label for="observaciones" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Observaciones / Términos de Pago <span class="text-slate-400 text-[10px] font-normal">(Opcional)</span>
                </label>
                <textarea id="observaciones" name="observaciones" rows="3"
                    placeholder="Detalles sobre acuerdos verbales, garantía de pago, etc."
                    class="block w-full py-2 px-3 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">{{ old('observaciones') }}</textarea>
                @error('observaciones')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('cartera.index') }}"
                class="px-5 py-2.5 border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Cancelar
            </a>
            <button type="submit"
                class="inline-flex items-center px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Crear Cuenta por Cobrar
            </button>
        </div>
    </form>

</div>
@endsection
