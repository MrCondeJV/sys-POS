@extends('layouts.app')

@section('title', 'Nueva Cuenta por Cobrar')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb & Encabezado -->
    <x-page-header
        title="Aperturar Cuenta por Cobrar"
        description="Registra una obligación a crédito comercial, saldo inicial o pagaré de cliente."
        :breadcrumbs="[
            ['label' => 'Crédito & Cartera', 'url' => route('cartera.index')],
            ['label' => 'Nueva Cuenta']
        ]">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('cartera.index') }}">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a Cartera
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($errors->any())
        <div class="p-4 rounded-lg bg-danger-bg border border-danger-border text-danger-text">
            <div class="flex items-center space-x-2">
                <svg class="h-5 w-5 text-danger-text flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                  'deuda' => $c->saldoTotalPendiente(),
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

        <div class="bg-white rounded-lg border border-slate-200 shadow-xs p-6 space-y-4">
            <!-- Selección de Cliente -->
            <div>
                <label for="cliente_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Cliente Deudor <span class="text-danger-text">*</span>
                </label>
                <select id="cliente_id" name="cliente_id" x-model="clienteId" @change="onClienteChange" required
                    class="input">
                    <option value="">-- Selecciona el cliente --</option>
                    <template x-for="c in clientes" :key="c.id">
                        <option :value="c.id" x-text="c.nombre + ' (' + c.documento + ')'" :selected="c.id == clienteId"></option>
                    </template>
                </select>
                @error('cliente_id')
                    <p class="text-xs text-danger-text mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Ficha reactiva del cliente seleccionado -->
            <template x-if="clienteSeleccionado">
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Deuda Actual del Cliente:</span>
                        <span class="font-bold font-mono text-slate-900" x-text="'$' + clienteSeleccionado.deuda.toLocaleString('es-CO', {minimumFractionDigits: 2})"></span>
                    </div>
                    <div x-show="clienteSeleccionado.plazo > 0" class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Plazo Acordado Habitual:</span>
                        <span class="font-semibold text-brand-600" x-text="clienteSeleccionado.plazo + ' días'"></span>
                    </div>
                </div>
            </template>

            <!-- Concepto -->
            <div>
                <label for="concepto" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Concepto de la Cuenta / Factura <span class="text-danger-text">*</span>
                </label>
                <input type="text" id="concepto" name="concepto" value="{{ old('concepto') }}" required
                    placeholder="Ej: Saldo Inicial / Crédito de Materiales Factura N° 1234"
                    class="block w-full py-2 px-3 border border-slate-300 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                @error('concepto')
                    <p class="text-xs text-danger-text mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Monto Total -->
            <div>
                <label for="monto_total" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Monto Total de la Obligación ($ COP) <span class="text-danger-text">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 font-semibold text-sm">$</span>
                    <input type="number" step="0.01" min="0.01" id="monto_total" name="monto_total" x-model.number="monto" required
                        placeholder="0.00"
                        class="block w-full pl-7 pr-3 py-2 border border-slate-300 rounded-lg text-sm font-semibold font-mono tabular-nums text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                </div>
                @error('monto_total')
                    <p class="text-xs text-danger-text mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha de Emisión y Acuerdo de Pago (opcional) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="fecha_emision" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Fecha de Emisión <span class="text-danger-text">*</span>
                    </label>
                    <input type="date" id="fecha_emision" name="fecha_emision" x-model="fechaEmision" @change="onClienteChange" required
                        class="block w-full py-2 px-3 border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                    @error('fecha_emision')
                        <p class="text-xs text-danger-text mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="fecha_vencimiento" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Fecha de Pago Acordada <span class="text-slate-400 text-[11px] font-normal">(Opcional)</span>
                    </label>
                    <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" x-model="fechaVencimiento"
                        class="block w-full py-2 px-3 border border-slate-300 rounded-lg text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                    @error('fecha_vencimiento')
                        <p class="text-xs text-danger-text mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Observaciones -->
            <div>
                <label for="observaciones" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Observaciones / Términos de Pago <span class="text-slate-400 text-[11px] font-normal">(Opcional)</span>
                </label>
                <textarea id="observaciones" name="observaciones" rows="3"
                    placeholder="Detalles sobre acuerdos verbales, garantía de pago, etc."
                    class="block w-full py-2 px-3 border border-slate-300 rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">{{ old('observaciones') }}</textarea>
                @error('observaciones')
                    <p class="text-xs text-danger-text mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end space-x-3">
            <x-button variant="secondary" href="{{ route('cartera.index') }}">
                Cancelar
            </x-button>
            <x-button variant="primary" type="submit">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Crear Cuenta por Cobrar
            </x-button>
        </div>
    </form>

</div>
@endsection
