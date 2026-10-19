@extends('layouts.app')

@section('title', 'Nuevo Ajuste de Inventario — POS Comercial')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <x-page-header
        title="Nuevo Ajuste de Inventario"
        subtitle="Asienta variaciones físicas justificadas (sobrantes, mermas, averías o conteos cíclicos)."
        :breadcrumbs="[
            ['label' => 'Inventario', 'url' => route('inventario.index')],
            ['label' => 'Ajuste de Stock']
        ]"
    >
        <x-slot:actions>
            <x-button variant="outline" size="sm" href="{{ route('inventario.index') }}">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @php
        $initialProdId = old('producto_id', $productoSeleccionado?->id ?? ($productos->first()?->id ?? ''));
        $initialSucId = old('sucursal_id', $sucursalActivaId ?? ($sucursales->first()?->id ?? ''));
        $initialTipo = old('tipo', 'AJUSTE_POSITIVO');
        $initialCant = old('cantidad', 1);

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
         class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">

        <form method="POST" action="{{ route('inventario.ajuste.store') }}" class="divide-y divide-slate-100">
            @csrf

            <!-- SECCIÓN 1: Selección de Producto y Sucursal -->
            <div class="p-6 space-y-6">
                <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                    <span class="flex items-center justify-center h-7 w-7 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200">1</span>
                    <h2 class="text-sm font-bold text-slate-900">Ubicación y Artículo</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Producto -->
                    <div>
                        <x-field label="Producto a Ajustar" name="producto_id" :required="true">
                            <select name="producto_id" id="producto_id" x-model="productoId" required
                                class="block w-full py-2 px-3 text-xs border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                                <option value="">Selecciona un producto...</option>
                                @foreach($productos as $p)
                                <option value="{{ $p->id }}" {{ $initialProdId == $p->id ? 'selected' : '' }}>
                                    {{ $p->nombre }} ({{ $p->codigo ?? 'Sin código' }})
                                </option>
                                @endforeach
                            </select>
                        </x-field>
                    </div>

                    <!-- Sucursal -->
                    <div>
                        <x-field label="Sucursal Afectada" name="sucursal_id" :required="true">
                            <select name="sucursal_id" id="sucursal_id" x-model="sucursalId" required
                                class="block w-full py-2 px-3 text-xs border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                                @foreach($sucursales as $s)
                                <option value="{{ $s->id }}" {{ $initialSucId == $s->id ? 'selected' : '' }}>
                                    {{ $s->nombre }} ({{ $s->codigo }})
                                </option>
                                @endforeach
                            </select>
                        </x-field>
                    </div>
                </div>

                <!-- Preview del Producto Seleccionado -->
                <template x-if="getProducto()">
                    <div class="p-3.5 bg-slate-50/70 rounded-lg border border-slate-200 flex items-center space-x-3">
                        <div class="h-10 w-10 rounded-md bg-white border border-slate-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                            <template x-if="getProducto().imagen_url">
                                <img :src="getProducto().imagen_url" alt="" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!getProducto().imagen_url">
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </template>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-xs font-semibold text-slate-900 truncate" x-text="getProducto().nombre"></h4>
                            <div class="text-[11px] text-slate-500 flex items-center space-x-2 font-mono mt-0.5">
                                <span>SKU: <span x-text="getProducto().codigo"></span></span>
                                <span>&bull;</span>
                                <span>Stock actual en sede: <strong class="text-slate-800" x-text="getStockActual().toFixed(2)"></strong></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- SECCIÓN 2: Tipo de Ajuste y Cantidad -->
            <div class="p-6 space-y-6">
                <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                    <span class="flex items-center justify-center h-7 w-7 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200">2</span>
                    <h2 class="text-sm font-bold text-slate-900">Variación y Magnitud</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-start">
                    <!-- Selector Tipo -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-2">
                            Sentido del Ajuste <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="relative flex flex-col items-center justify-center p-3.5 rounded-lg border cursor-pointer transition text-center"
                                   :class="tipo === 'AJUSTE_POSITIVO' ? 'border-emerald-500 bg-emerald-50/40 text-emerald-900 font-bold' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50'">
                                <input type="radio" name="tipo" value="AJUSTE_POSITIVO" x-model="tipo" class="sr-only">
                                <span class="text-xs font-bold leading-tight">&plus; Entrada / Sobrante</span>
                                <span class="text-[10px] text-slate-500 mt-0.5">Incrementa stock</span>
                            </label>

                            <label class="relative flex flex-col items-center justify-center p-3.5 rounded-lg border cursor-pointer transition text-center"
                                   :class="tipo === 'AJUSTE_NEGATIVO' ? 'border-rose-500 bg-rose-50/40 text-rose-900 font-bold' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50'">
                                <input type="radio" name="tipo" value="AJUSTE_NEGATIVO" x-model="tipo" class="sr-only">
                                <span class="text-xs font-bold leading-tight">&minus; Salida / Merma</span>
                                <span class="text-[10px] text-slate-500 mt-0.5">Reduce stock</span>
                            </label>
                        </div>
                    </div>

                    <!-- Cantidad a Ajustar -->
                    <div>
                        <x-field label="Cantidad de Unidades" name="cantidad" :required="true">
                            <input type="number" step="0.01" min="0.01" name="cantidad" id="cantidad" x-model="cantidad" required
                                placeholder="0.00"
                                class="block w-full py-2 px-3 text-sm border border-slate-300 rounded-lg text-slate-900 tabular-nums font-bold focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                        </x-field>
                    </div>
                </div>

                <!-- SIMULADOR EN VIVO -->
                <div class="p-4 rounded-lg border transition"
                     :class="getStockProyectado() < 0 ? 'bg-rose-50 border-rose-200' : 'bg-slate-50/80 border-slate-200'">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs">
                        <!-- Stock Actual -->
                        <div class="flex-1">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Stock Actual en Sede</span>
                            <div class="text-base font-bold font-mono text-slate-800 mt-0.5 tabular-nums" x-text="getStockActual().toFixed(2)"></div>
                        </div>

                        <!-- Operador -->
                        <div class="text-center sm:px-3">
                            <span class="text-[10px] font-semibold uppercase tracking-wider block text-slate-400">Efecto</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold mt-0.5 tabular-nums"
                                  :class="tipo === 'AJUSTE_POSITIVO' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                                  x-text="(tipo === 'AJUSTE_POSITIVO' ? '+' : '-') + (parseFloat(cantidad) || 0).toFixed(2)"></span>
                        </div>

                        <!-- Flecha -->
                        <div class="hidden sm:block text-slate-300 font-bold">&rarr;</div>

                        <!-- Saldo Resultante -->
                        <div class="text-left sm:text-right flex-1">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block">Saldo Proyectado</span>
                            <div class="text-xl font-bold font-mono mt-0.5 tabular-nums"
                                 :class="getStockProyectado() < 0 ? 'text-rose-600' : 'text-slate-900'"
                                 x-text="getStockProyectado().toFixed(2)">
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje de Alerta en caso de Stock Negativo -->
                    <template x-if="getStockProyectado() < 0">
                        <div class="mt-3 pt-2.5 border-t border-rose-200 flex items-start space-x-2 text-rose-700 text-xs font-semibold">
                            <svg class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Bloqueo de Seguridad: Esta operación dejaría el inventario en saldo negativo (<span x-text="getStockProyectado().toFixed(2)"></span>). No se permite asentar ajustes que superen las existencias.</span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- SECCIÓN 3: Justificación y Documentación -->
            <div class="p-6 space-y-4">
                <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                    <span class="flex items-center justify-center h-7 w-7 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200">3</span>
                    <h2 class="text-sm font-bold text-slate-900">Justificación y Auditoría</h2>
                </div>

                <div class="space-y-4">
                    <x-field label="Motivo / Referencia Obligatoria" name="motivo" :required="true">
                        <input type="text" name="motivo" id="motivo" value="{{ old('motivo') }}" required
                            placeholder="Ej: Conteo físico mensual, Merma por avería en bodega, Ajuste por rotura de empaque..."
                            class="block w-full py-2 px-3 text-xs border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                    </x-field>

                    <x-field label="Observaciones Adicionales (Opcional)" name="notas">
                        <textarea name="notas" id="notas" rows="2"
                            placeholder="Detalles complementarios para el auditor o contador..."
                            class="block w-full py-2 px-3 text-xs border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">{{ old('notas') }}</textarea>
                    </x-field>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="p-5 bg-slate-50/50 flex items-center justify-end gap-2.5">
                <x-button variant="outline" href="{{ route('inventario.index') }}">
                    Cancelar
                </x-button>
                <button type="submit"
                        :disabled="getStockProyectado() < 0 || !productoId || !sucursalId || !cantidad || cantidad <= 0"
                        class="inline-flex items-center justify-center font-medium rounded-lg px-4 py-2 text-sm bg-brand-600 hover:bg-brand-700 text-white shadow-xs transition disabled:opacity-40 disabled:cursor-not-allowed">
                    Confirmar y Asentar Ajuste
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
