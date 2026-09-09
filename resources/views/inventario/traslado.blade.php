@extends('layouts.app')

@section('title', 'Traslado de Existencias entre Sucursales — POS Comercial')

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
                <span class="text-slate-600 font-semibold">Traslados</span>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Traslado entre Sucursales</h1>
            <p class="text-sm text-slate-500 font-medium">Transfiere existencias físicas entre sedes de tu empresa con doble asiento atómico en Kardex.</p>
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
        $initialOrigenId = old('sucursal_origen_id', $sucursalOrigenId ?? ($sucursales->first()?->id ?? ''));
        $initialDestinoId = old('sucursal_destino_id', $sucursales->count() > 1 ? $sucursales->last()->id : '');
        $initialCant = old('cantidad', 1);

        $productosData = [];
        $stockMap = [];
        foreach ($productos as $p) {
            $productosData[$p->id] = [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'codigo' => $p->codigo ?? 'N/A',
                'categoria' => $p->categoria?->nombre ?? 'Sin categoría',
                'unidad' => $p->unidadMedida?->codigo ?? 'UND',
                'imagen_url' => $p->imagen_url,
                'stock_global' => (float) $p->stock,
            ];
            foreach ($p->inventarios as $invItem) {
                $stockMap[$p->id][$invItem->sucursal_id] = (float) $invItem->stock;
            }
        }
    @endphp

    <div x-data="{
            productoId: '{{ $initialProdId }}',
            sucursalOrigenId: '{{ $initialOrigenId }}',
            sucursalDestinoId: '{{ $initialDestinoId }}',
            cantidad: {{ (float) $initialCant }},
            productosData: {{ json_encode($productosData) }},
            stockMap: {{ json_encode($stockMap) }},
            getProducto() {
                return this.productosData[this.productoId] || null;
            },
            getStockOrigen() {
                if (this.stockMap[this.productoId] && this.stockMap[this.productoId][this.sucursalOrigenId] !== undefined) {
                    return this.stockMap[this.productoId][this.sucursalOrigenId];
                }
                return 0.0;
            },
            getStockDestino() {
                if (this.stockMap[this.productoId] && this.stockMap[this.productoId][this.sucursalDestinoId] !== undefined) {
                    return this.stockMap[this.productoId][this.sucursalDestinoId];
                }
                return 0.0;
            },
            getStockOrigenProyectado() {
                return this.getStockOrigen() - (parseFloat(this.cantidad) || 0);
            },
            getStockDestinoProyectado() {
                return this.getStockDestino() + (parseFloat(this.cantidad) || 0);
            },
            esValido() {
                return this.productoId &&
                       this.sucursalOrigenId &&
                       this.sucursalDestinoId &&
                       this.sucursalOrigenId !== this.sucursalDestinoId &&
                       parseFloat(this.cantidad) > 0 &&
                       this.getStockOrigenProyectado() >= 0;
            }
         }"
         class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

        @if($errors->has('general'))
        <div class="p-4 bg-rose-50 border-b border-rose-200 text-rose-800 text-sm font-semibold flex items-center space-x-2">
            <svg class="h-5 w-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $errors->first('general') }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('inventario.traslado.store') }}" class="divide-y divide-slate-100">
            @csrf

            <!-- SECCIÓN 1: Selección del Producto -->
            <div class="p-6 sm:p-8 space-y-6">
                <div class="flex items-center space-x-3 pb-2 border-b border-slate-100">
                    <span class="flex items-center justify-center h-7 w-7 rounded-xl bg-indigo-50 text-indigo-600 font-black text-xs">1</span>
                    <h2 class="text-base font-bold text-slate-900">Artículo a Trasladar</h2>
                </div>

                <div>
                    <label for="producto_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Selecciona el Producto <span class="text-rose-500">*</span>
                    </label>
                    <select name="producto_id" id="producto_id" x-model="productoId" required
                        class="block w-full py-3 pl-4 pr-10 text-sm border border-slate-300 rounded-xl bg-white shadow-2xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium text-slate-800">
                        <option value="" disabled>Seleccione un producto...</option>
                        @foreach($productos as $p)
                        <option value="{{ $p->id }}" {{ $initialProdId == $p->id ? 'selected' : '' }}>
                            {{ $p->nombre }} (SKU: {{ $p->codigo ?? 'N/A' }}) — Stock Consolidado: {{ number_format($p->stock, 2) }}
                        </option>
                        @endforeach
                    </select>
                    @error('producto_id')
                    <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mini Ficha del Producto -->
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
                            <span class="text-xs text-slate-400 block font-medium">Stock Global</span>
                            <span class="text-base font-black text-slate-900 font-mono" x-text="getProducto().stock_global.toFixed(2) + ' ' + getProducto().unidad"></span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- SECCIÓN 2: Sedes y Cantidad -->
            <div class="p-6 sm:p-8 space-y-6">
                <div class="flex items-center space-x-3 pb-2 border-b border-slate-100">
                    <span class="flex items-center justify-center h-7 w-7 rounded-xl bg-indigo-50 text-indigo-600 font-black text-xs">2</span>
                    <h2 class="text-base font-bold text-slate-900">Ruta de Transferencia y Cantidad</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Sucursal Origen -->
                    <div>
                        <label for="sucursal_origen_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Sucursal de Origen (Emisora) <span class="text-rose-500">*</span>
                        </label>
                        <select name="sucursal_origen_id" id="sucursal_origen_id" x-model="sucursalOrigenId" required
                            class="block w-full py-3 pl-4 pr-10 text-sm border border-slate-300 rounded-xl bg-white shadow-2xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium text-slate-800">
                            <option value="" disabled>Seleccione sede de origen...</option>
                            @foreach($sucursales as $s)
                            <option value="{{ $s->id }}" {{ $initialOrigenId == $s->id ? 'selected' : '' }}>
                                {{ $s->nombre }} {{ $s->es_principal ? '(Principal)' : '' }}
                            </option>
                            @endforeach
                        </select>
                        @error('sucursal_origen_id')
                        <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sucursal Destino -->
                    <div>
                        <label for="sucursal_destino_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Sucursal de Destino (Receptora) <span class="text-rose-500">*</span>
                        </label>
                        <select name="sucursal_destino_id" id="sucursal_destino_id" x-model="sucursalDestinoId" required
                            class="block w-full py-3 pl-4 pr-10 text-sm border border-slate-300 rounded-xl bg-white shadow-2xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-medium text-slate-800">
                            <option value="" disabled>Seleccione sede de destino...</option>
                            @foreach($sucursales as $s)
                            <option value="{{ $s->id }}" {{ $initialDestinoId == $s->id ? 'selected' : '' }}>
                                {{ $s->nombre }} {{ $s->es_principal ? '(Principal)' : '' }}
                            </option>
                            @endforeach
                        </select>
                        @error('sucursal_destino_id')
                        <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Cantidad a Transferir -->
                <div class="max-w-xs">
                    <label for="cantidad" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Cantidad a Transferir <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.01" min="0.01" name="cantidad" id="cantidad" x-model="cantidad" required
                        placeholder="0.00"
                        class="block w-full py-3 px-4 text-base border border-slate-300 rounded-xl bg-white shadow-2xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition font-black text-slate-900">
                    @error('cantidad')
                    <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- SIMULADOR EN VIVO DE TRASLADO -->
                <div class="p-6 rounded-2xl bg-indigo-50/50 border border-indigo-100/80 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-900">Simulación del Traslado Atómico</span>
                        <span class="text-xs font-mono font-bold bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-md">
                            Cantidad: <span x-text="(parseFloat(cantidad) || 0).toFixed(2)"></span>
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Origen -->
                        <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-2xs space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-500 uppercase">Sede de Origen</span>
                                <span class="text-rose-600 bg-rose-50 px-2 py-0.5 rounded text-[11px]">- Salida</span>
                            </div>
                            <div class="flex items-baseline justify-between text-xs text-slate-600">
                                <span>Disponible actual:</span>
                                <span class="font-mono font-bold text-slate-800 text-sm" x-text="getStockOrigen().toFixed(2)"></span>
                            </div>
                            <div class="pt-2 border-t border-slate-100 flex items-baseline justify-between">
                                <span class="text-xs font-bold text-slate-700">Quedará en:</span>
                                <span class="font-mono font-black text-base" :class="getStockOrigenProyectado() < 0 ? 'text-rose-600' : 'text-slate-900'" x-text="getStockOrigenProyectado().toFixed(2)"></span>
                            </div>
                        </div>

                        <!-- Destino -->
                        <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-2xs space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-500 uppercase">Sede de Destino</span>
                                <span class="text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded text-[11px]">+ Entrada</span>
                            </div>
                            <div class="flex items-baseline justify-between text-xs text-slate-600">
                                <span>Existencias actuales:</span>
                                <span class="font-mono font-bold text-slate-800 text-sm" x-text="getStockDestino().toFixed(2)"></span>
                            </div>
                            <div class="pt-2 border-t border-slate-100 flex items-baseline justify-between">
                                <span class="text-xs font-bold text-slate-700">Aumentará a:</span>
                                <span class="font-mono font-black text-emerald-600 text-base" x-text="getStockDestinoProyectado().toFixed(2)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Mensajes de Validación -->
                    <template x-if="sucursalOrigenId === sucursalDestinoId && sucursalOrigenId !== ''">
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs font-bold text-amber-800 flex items-center space-x-2">
                            <svg class="h-4 w-4 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>La sucursal de origen y la sucursal de destino deben ser diferentes.</span>
                        </div>
                    </template>

                    <template x-if="getStockOrigenProyectado() < 0">
                        <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold text-rose-800 flex items-center space-x-2">
                            <svg class="h-4 w-4 text-rose-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Bloqueo: La cantidad solicitada supera las existencias disponibles en la sede emisora (<span x-text="getStockOrigen().toFixed(2)"></span>).</span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- SECCIÓN 3: Justificación y Transporte -->
            <div class="p-6 sm:p-8 space-y-6">
                <div class="flex items-center space-x-3 pb-2 border-b border-slate-100">
                    <span class="flex items-center justify-center h-7 w-7 rounded-xl bg-indigo-50 text-indigo-600 font-black text-xs">3</span>
                    <h2 class="text-base font-bold text-slate-900">Justificación y Auditoría de Envío</h2>
                </div>

                <div class="space-y-5">
                    <div>
                        <label for="motivo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Motivo del Traslado <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="motivo" id="motivo" value="{{ old('motivo') }}" required
                            placeholder="Ej: Surtido por alta demanda en sede norte, Transferencia solicitada por gerencia..."
                            class="block w-full py-3 px-4 text-sm border border-slate-300 rounded-xl bg-white shadow-2xs focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition text-slate-800 placeholder-slate-400 font-medium">
                        @error('motivo')
                        <p class="text-xs text-rose-600 mt-1.5 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notas" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Datos del Despacho / Conductor / Guía (Opcional)
                        </label>
                        <textarea name="notas" id="notas" rows="3"
                            placeholder="Número de remesa, nombre del transportador, placa del vehículo..."
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
                        :disabled="!esValido()"
                        class="w-full sm:w-auto px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl text-sm hover:bg-indigo-700 shadow-sm shadow-indigo-200 transition disabled:opacity-40 disabled:cursor-not-allowed">
                    Procesar y Confirmar Traslado
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
