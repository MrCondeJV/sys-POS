@extends('layouts.app')

@section('title', 'Nuevo Ajuste de Inventario — POS Comercial')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="flex items-center space-x-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('inventario.index') }}" class="hover:text-indigo-600 transition">Inventario</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Ajuste de Stock</span>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Nuevo Ajuste de Inventario</h1>
            <p class="text-sm text-slate-500 font-medium">Asienta variaciones físicas justificadas (sobrantes, mermas, daños o conteos).</p>
        </div>

        <a href="{{ route('inventario.index') }}"
            class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 shadow-sm transition">
            <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
        </a>
    </div>

    <!-- Formulario con Simulador en Vivo -->
    @php
        $initialProdId = old('producto_id', $productoSeleccionado?->id ?? ($productos->first()?->id ?? ''));
        $initialSucId = old('sucursal_id', $sucursalActivaId ?? ($sucursales->first()?->id ?? ''));
        $initialTipo = old('tipo', 'AJUSTE_POSITIVO');
        $initialCant = old('cantidad', 1);

        // Preparamos un mapa JSON de existencias para que Alpine.js simule en vivo sin peticiones extra
        $stockMap = [];
        foreach (\App\Models\Inventario::whereIn('producto_id', $productos->pluck('id'))->get() as $invItem) {
            $stockMap[$invItem->producto_id][$invItem->sucursal_id] = (float) $invItem->stock;
        }
    @endphp

    <div x-data="{
            productoId: '{{ $initialProdId }}',
            sucursalId: '{{ $initialSucId }}',
            tipo: '{{ $initialTipo }}',
            cantidad: {{ (float) $initialCant }},
            stockMap: {{ json_encode($stockMap) }},
            getStockActual() {
                if (this.stockMap[this.productoId] && this.stockMap[this.productoId][this.sucursalId] !== undefined) {
                    return this.stockMap[this.productoId][this.sucursalId];
                }
                return 0.0;
            },
            getStockProyectado() {
                let actual = this.getStockActual();
                let cant = parseFloat(this.cantidad) || 0;
                if (this.tipo === 'AJUSTE_POSITIVO') {
                    return actual + cant;
                } else {
                    return actual - cant;
                }
            }
         }"
         class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        <form method="POST" action="{{ route('inventario.ajuste.store') }}" class="p-6 space-y-6">
            @csrf

            <!-- Selector de Producto y Sucursal -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Producto -->
                <div>
                    <label for="producto_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Producto a Ajustar <span class="text-rose-500">*</span>
                    </label>
                    <select name="producto_id" id="producto_id" x-model="productoId" required
                        class="block w-full py-2.5 px-3 text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition font-medium">
                        <option value="" disabled>Seleccione un producto...</option>
                        @foreach($productos as $p)
                        <option value="{{ $p->id }}" {{ $initialProdId == $p->id ? 'selected' : '' }}>
                            {{ $p->nombre }} (SKU: {{ $p->codigo ?? 'N/A' }}) — Stock Global: {{ number_format($p->stock, 2) }}
                        </option>
                        @endforeach
                    </select>
                    @error('producto_id')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sucursal -->
                <div>
                    <label for="sucursal_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Sucursal Destino del Ajuste <span class="text-rose-500">*</span>
                    </label>
                    <select name="sucursal_id" id="sucursal_id" x-model="sucursalId" required
                        class="block w-full py-2.5 px-3 text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition font-medium">
                        <option value="" disabled>Seleccione una sucursal...</option>
                        @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ $initialSucId == $s->id ? 'selected' : '' }}>
                            {{ $s->nombre }} {{ $s->es_principal ? '(Principal)' : '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('sucursal_id')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Tipo de Ajuste y Cantidad -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Tipo -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Naturaleza del Ajuste <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition text-center"
                               :class="tipo === 'AJUSTE_POSITIVO' ? 'border-emerald-500 bg-emerald-50/40 text-emerald-900 font-bold shadow-sm' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            <input type="radio" name="tipo" value="AJUSTE_POSITIVO" x-model="tipo" class="sr-only">
                            <span class="text-sm">+ Entrada / Sobrante</span>
                            <span class="text-[11px] text-slate-400 mt-0.5">Incrementa stock</span>
                        </label>

                        <label class="relative flex flex-col p-3 rounded-xl border cursor-pointer transition text-center"
                               :class="tipo === 'AJUSTE_NEGATIVO' ? 'border-rose-500 bg-rose-50/40 text-rose-900 font-bold shadow-sm' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                            <input type="radio" name="tipo" value="AJUSTE_NEGATIVO" x-model="tipo" class="sr-only">
                            <span class="text-sm">- Merma / Salida</span>
                            <span class="text-[11px] text-slate-400 mt-0.5">Reduce stock</span>
                        </label>
                    </div>
                    @error('tipo')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cantidad -->
                <div>
                    <label for="cantidad" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Cantidad a Ajustar <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.01" min="0.01" name="cantidad" id="cantidad" x-model="cantidad" required
                        class="block w-full py-2.5 px-3 text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition font-bold text-slate-800">
                    @error('cantidad')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Previsualización / Simulador de Saldo en Vivo -->
            <div class="p-4 rounded-2xl border transition"
                 :class="getStockProyectado() < 0 ? 'bg-rose-50 border-rose-200 text-rose-900' : 'bg-slate-50 border-slate-200 text-slate-800'">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Simulación del Movimiento</div>
                        <div class="text-sm font-semibold mt-0.5">
                            Stock Actual en Sucursal: <span class="font-bold font-mono" x-text="getStockActual().toFixed(2)"></span>
                            <span class="text-slate-400 mx-1.5">➔</span>
                            Efecto: <span class="font-bold" :class="tipo === 'AJUSTE_POSITIVO' ? 'text-emerald-600' : 'text-rose-600'"
                                          x-text="(tipo === 'AJUSTE_POSITIVO' ? '+' : '-') + (parseFloat(cantidad) || 0).toFixed(2)"></span>
                        </div>
                    </div>

                    <div class="text-left sm:text-right">
                        <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Nuevo Saldo Resultante</div>
                        <div class="text-xl font-black font-mono mt-0.5"
                             :class="getStockProyectado() < 0 ? 'text-rose-600' : 'text-slate-900'"
                             x-text="getStockProyectado().toFixed(2)">
                        </div>
                    </div>
                </div>

                <template x-if="getStockProyectado() < 0">
                    <div class="mt-3 text-xs font-bold text-rose-700 flex items-center space-x-1.5">
                        <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Atención: Este ajuste no podrá procesarse porque causaría stock negativo en la sucursal seleccionada.</span>
                    </div>
                </template>
            </div>

            <!-- Motivo y Notas -->
            <div class="space-y-4">
                <div>
                    <label for="motivo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Motivo / Referencia Obligatoria <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="motivo" id="motivo" value="{{ old('motivo') }}" required
                        placeholder="Ej: Conteo físico mensual, Merma por avería en bodega, Ajuste por rotura de empaque..."
                        class="block w-full py-2.5 px-3 text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('motivo')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notas" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Observaciones Adicionales (Opcional)
                    </label>
                    <textarea name="notas" id="notas" rows="2"
                        placeholder="Detalles complementarios para el auditor o contador..."
                        class="block w-full py-2 px-3 text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition">{{ old('notas') }}</textarea>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('inventario.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-700 font-semibold rounded-xl text-sm hover:bg-slate-200 transition">
                    Cancelar
                </a>
                <button type="submit"
                        :disabled="getStockProyectado() < 0"
                        class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-sm hover:bg-indigo-700 shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Confirmar y Asentar Ajuste
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
