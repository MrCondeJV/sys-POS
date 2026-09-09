@extends('layouts.app')

@section('title', 'Nuevo Ajuste de Inventario — POS Comercial')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <nav class="flex items-center space-x-2 text-xs text-slate-400 mb-1.5 font-medium">
                <a href="{{ route('inventario.index') }}" class="hover:text-indigo-600 transition">Inventario</a>
                <svg class="h-3.5 w-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span class="text-slate-600 font-semibold">Ajuste de Stock</span>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Nuevo Ajuste de Inventario</h1>
            <p class="text-sm text-slate-500 font-medium">Asienta variaciones físicas justificadas (sobrantes, mermas, daños o conteos).</p>
        </div>

        <a href="{{ route('inventario.index') }}"
            class="inline-flex items-center px-4 py-2.5 bg-white border border-slate-300 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 hover:border-slate-400 shadow-2xs transition">
            <svg class="h-4 w-4 mr-2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al Inventario
        </a>
    </div>

    @php
        $initialProdId = old('producto_id', $productoSeleccionado?->id ?? ($productos->first()?->id ?? ''));
        $initialSucId = old('sucursal_id', $sucursalActivaId ?? ($sucursales->first()?->id ?? ''));
        $initialTipo = old('tipo', 'AJUSTE_POSITIVO');
        $initialCant = old('cantidad', 1);

        // Mapa detallado de productos y stock por sucursal para reactividad instantánea en Alpine
        $productosData = [];
        $stockMap = [];
        foreach ($productos as $p) {
            $productosData[$p->id] = [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'codigo' => $p->codigo ?? 'N/A',
                'codigo_barras' => $p->codigo_barras ?? 'N/A',
                'categoria' => $p->categoria?->nombre ?? 'Sin categoría',
                'unidad' => $p->unidadMedida?->codigo ?? 'UND',
                'imagen_url' => $p->imagen_url,
                'stock_global' => (float) $p->stock,
                'precio_compra' => (float) $p->precio_compra,
                'precio_venta' => (float) $p->precio_venta,
            ];
            foreach ($p->inventarios as $invItem) {
                $stockMap[$p->id][$invItem->sucursal_id] = (float) $invItem->stock;
            }
        }
    @endphp

    <div x-data="{
            productoId: '{{ $initialProdId }}',
            sucursalId: '{{ $initialSucId }}',
            tipo: '{{ $initialTipo }}',
            cantidad: {{ (float) $initialCant }},
            productosData: {{ json_encode($productosData) }},
            stockMap: {{ json_encode($stockMap) }},
            getProducto() {
                return this.productosData[this.productoId] || null;
            },
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
         class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

        <form method="POST" action="{{ route('inventario.ajuste.store') }}" class="divide-y divide-slate-100">
            @csrf

            <!-- SECCIÓN 1: Selección de Producto y Sucursal -->
            <div class="p-6 sm:p-8 space-y-6">
                <div class="flex items-center space-x-3 pb-2 border-b border-slate-100">
                    <span class="flex items-center justify-center h-7 w-7 rounded-xl bg-indigo-50 text-indigo-600 font-black text-xs">1</span>
                    <h2 class="text-base font-bold text-slate-900">Ubicación y Artículo</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Producto -->
                    <div>
                        <label for="producto_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Producto a Ajustar <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="producto_id" id="producto_id" x-model="productoId" required
                                class="block w-full py-3 pl-4 pr-10 text-sm border border-slate-300 rounded-xl bg-white shadow-2xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium text-slate-800">
                                <option value="" disabled>Seleccione un producto del catálogo...</option>
                                @foreach($productos as $p)
                                <option value="{{ $p->id }}" {{ $initialProdId == $p->id ? 'selected' : '' }}>
                                    {{ $p->nombre }} (SKU: {{ $p->codigo ?? 'N/A' }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @error('producto_id')
                        <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sucursal -->
                    <div>
                        <label for="sucursal_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Sucursal Destino del Ajuste <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <select name="sucursal_id" id="sucursal_id" x-model="sucursalId" required
                                class="block w-full py-3 pl-4 pr-10 text-sm border border-slate-300 rounded-xl bg-white shadow-2xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium text-slate-800">
                                <option value="" disabled>Seleccione una sucursal...</option>
                                @foreach($sucursales as $s)
                                <option value="{{ $s->id }}" {{ $initialSucId == $s->id ? 'selected' : '' }}>
                                    {{ $s->nombre }} {{ $s->es_principal ? '(Sede Principal)' : '' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @error('sucursal_id')
                        <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Mini Ficha del Producto Seleccionado -->
                <template x-if="getProducto()">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-4 transition">
                        <div class="flex items-center space-x-3.5 min-w-0">
                            <div class="h-12 w-12 rounded-xl bg-white border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                <template x-if="getProducto().imagen_url">
                                    <img :src="getProducto().imagen_url" :alt="getProducto().nombre" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!getProducto().imagen_url">
                                    <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </template>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-slate-900 truncate" x-text="getProducto().nombre"></h4>
                                <div class="flex items-center space-x-2 text-xs text-slate-500 mt-0.5">
                                    <span class="font-mono bg-white px-1.5 py-0.5 rounded border border-slate-200 text-slate-700" x-text="'SKU: ' + getProducto().codigo"></span>
                                    <span x-text="getProducto().categoria"></span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right flex-shrink-0">
                            <span class="text-xs text-slate-400 block font-medium">Stock Global Consolidado</span>
                            <span class="text-base font-black text-slate-900 font-mono" x-text="getProducto().stock_global.toFixed(2) + ' ' + getProducto().unidad"></span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- SECCIÓN 2: Tipo de Ajuste y Cantidad -->
            <div class="p-6 sm:p-8 space-y-6">
                <div class="flex items-center space-x-3 pb-2 border-b border-slate-100">
                    <span class="flex items-center justify-center h-7 w-7 rounded-xl bg-indigo-50 text-indigo-600 font-black text-xs">2</span>
                    <h2 class="text-base font-bold text-slate-900">Operación y Cantidad</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tarjetas de Naturaleza del Ajuste -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Tipo de Variación <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <!-- Opción Positiva -->
                            <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition text-center"
                                   :class="tipo === 'AJUSTE_POSITIVO' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 font-bold shadow-xs' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'">
                                <input type="radio" name="tipo" value="AJUSTE_POSITIVO" x-model="tipo" class="sr-only">
                                <div class="h-8 w-8 rounded-full flex items-center justify-center mb-1.5 transition"
                                     :class="tipo === 'AJUSTE_POSITIVO' ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-400'">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <span class="text-sm font-bold leading-tight">Entrada / Sobrante</span>
                                <span class="text-[11px] text-slate-500 mt-1">Incrementa existencias</span>
                            </label>

                            <!-- Opción Negativa -->
                            <label class="relative flex flex-col items-center justify-center p-4 rounded-2xl border-2 cursor-pointer transition text-center"
                                   :class="tipo === 'AJUSTE_NEGATIVO' ? 'border-rose-500 bg-rose-50/50 text-rose-900 font-bold shadow-xs' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50'">
                                <input type="radio" name="tipo" value="AJUSTE_NEGATIVO" x-model="tipo" class="sr-only">
                                <div class="h-8 w-8 rounded-full flex items-center justify-center mb-1.5 transition"
                                     :class="tipo === 'AJUSTE_NEGATIVO' ? 'bg-rose-500 text-white' : 'bg-slate-100 text-slate-400'">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" />
                                    </svg>
                                </div>
                                <span class="text-sm font-bold leading-tight">Merma / Daño</span>
                                <span class="text-[11px] text-slate-500 mt-1">Reduce existencias</span>
                            </label>
                        </div>
                        @error('tipo')
                        <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cantidad a Ajustar -->
                    <div>
                        <label for="cantidad" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Cantidad de Unidades <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" step="0.01" min="0.01" name="cantidad" id="cantidad" x-model="cantidad" required
                                placeholder="0.00"
                                class="block w-full py-3 px-4 text-base border border-slate-300 rounded-xl bg-white shadow-2xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-black text-slate-900">
                        </div>
                        <p class="text-xs text-slate-400 mt-1.5">Ingresa el valor absoluto de unidades físicas a alterar.</p>
                        @error('cantidad')
                        <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- SIMULADOR EN VIVO (Live Calculation Widget) -->
                <div class="p-5 rounded-2xl border transition shadow-xs"
                     :class="getStockProyectado() < 0 ? 'bg-rose-50 border-rose-200' : 'bg-slate-50/80 border-slate-200'">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <!-- Paso 1: Stock Actual -->
                        <div class="flex-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Stock Actual en Sede</span>
                            <div class="text-lg font-bold font-mono text-slate-800 mt-0.5" x-text="getStockActual().toFixed(2)"></div>
                        </div>

                        <!-- Operador -->
                        <div class="text-center sm:px-3">
                            <span class="text-xs font-bold uppercase tracking-wider block text-slate-400">Efecto</span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black mt-0.5"
                                  :class="tipo === 'AJUSTE_POSITIVO' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                                  x-text="(tipo === 'AJUSTE_POSITIVO' ? '+' : '-') + (parseFloat(cantidad) || 0).toFixed(2)"></span>
                        </div>

                        <!-- Flecha -->
                        <div class="hidden sm:block text-slate-300 font-black text-xl">➔</div>

                        <!-- Paso 2: Saldo Resultante -->
                        <div class="text-left sm:text-right flex-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Saldo Proyectado</span>
                            <div class="text-2xl font-black font-mono mt-0.5"
                                 :class="getStockProyectado() < 0 ? 'text-rose-600' : 'text-slate-900'"
                                 x-text="getStockProyectado().toFixed(2)">
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje de Alerta en caso de Stock Negativo -->
                    <template x-if="getStockProyectado() < 0">
                        <div class="mt-4 pt-3 border-t border-rose-200 flex items-start space-x-2 text-rose-700 text-xs font-bold">
                            <svg class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Bloqueo de Seguridad: Esta operación dejaría el inventario en saldo negativo (<span x-text="getStockProyectado().toFixed(2)"></span>). No se permite asentar ajustes que superen las existencias disponibles.</span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- SECCIÓN 3: Justificación y Documentación -->
            <div class="p-6 sm:p-8 space-y-6">
                <div class="flex items-center space-x-3 pb-2 border-b border-slate-100">
                    <span class="flex items-center justify-center h-7 w-7 rounded-xl bg-indigo-50 text-indigo-600 font-black text-xs">3</span>
                    <h2 class="text-base font-bold text-slate-900">Justificación y Auditoría</h2>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="motivo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Motivo / Referencia Obligatoria <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="motivo" id="motivo" value="{{ old('motivo') }}" required
                            placeholder="Ej: Conteo físico mensual, Merma por avería en bodega, Ajuste por rotura de empaque..."
                            class="block w-full py-3 px-4 text-sm border border-slate-300 rounded-xl bg-white shadow-2xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-slate-800 placeholder-slate-400 font-medium">
                        @error('motivo')
                        <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notas" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Observaciones Adicionales (Opcional)
                        </label>
                        <textarea name="notas" id="notas" rows="3"
                            placeholder="Detalles complementarios para el auditor o contador..."
                            class="block w-full py-3 px-4 text-sm border border-slate-300 rounded-xl bg-white shadow-2xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-slate-800 placeholder-slate-400">{{ old('notas') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="p-6 sm:p-8 bg-slate-50/50 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
                <a href="{{ route('inventario.index') }}" class="w-full sm:w-auto px-5 py-3 text-center bg-white border border-slate-300 text-slate-700 font-bold rounded-xl text-sm hover:bg-slate-50 transition shadow-2xs">
                    Cancelar
                </a>
                <button type="submit"
                        :disabled="getStockProyectado() < 0 || !productoId || !sucursalId || !cantidad || cantidad <= 0"
                        class="w-full sm:w-auto px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl text-sm hover:bg-indigo-700 shadow-sm shadow-indigo-200 transition disabled:opacity-40 disabled:cursor-not-allowed">
                    Confirmar y Asentar Ajuste
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
