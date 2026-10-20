@extends('layouts.app')

@section('title', 'Traslado de Existencias entre Sucursales — POS Comercial')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <x-page-header
        title="Traslado entre Sucursales"
        subtitle="Transfiere existencias físicas entre sedes de tu empresa con doble asiento atómico en Kardex."
        :breadcrumbs="[
            ['label' => 'Inventario', 'url' => route('inventario.index')],
            ['label' => 'Traslados']
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
         class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">

        @if($errors->has('general'))
        <div class="p-4 bg-danger-bg border-b border-danger-border text-danger-text text-xs font-semibold flex items-center space-x-2">
            <svg class="h-4 w-4 text-danger flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $errors->first('general') }}</span>
        </div>
        @endif

        <form method="POST" action="{{ route('inventario.traslado.store') }}" class="divide-y divide-slate-100">
            @csrf

            <!-- SECCIÓN 1: Selección de Producto -->
            <div class="p-6 space-y-5">
                <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                    <span class="flex items-center justify-center h-7 w-7 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200">1</span>
                    <h2 class="text-sm font-bold text-slate-900">Artículo a Trasladar</h2>
                </div>

                <div>
                    <x-field label="Producto" name="producto_id" :required="true">
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
            </div>

            <!-- SECCIÓN 2: Ruta de Sedes -->
            <div class="p-6 space-y-5">
                <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                    <span class="flex items-center justify-center h-7 w-7 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200">2</span>
                    <h2 class="text-sm font-bold text-slate-900">Ruta de Sedes y Cantidad</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Origen -->
                    <div>
                        <x-field label="Sucursal de Origen (Despacho)" name="sucursal_origen_id" :required="true">
                            <select name="sucursal_origen_id" id="sucursal_origen_id" x-model="sucursalOrigenId" required
                                class="block w-full py-2 px-3 text-xs border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                                @foreach($sucursales as $s)
                                <option value="{{ $s->id }}" {{ $initialOrigenId == $s->id ? 'selected' : '' }}>
                                    {{ $s->nombre }} ({{ $s->codigo }})
                                </option>
                                @endforeach
                            </select>
                        </x-field>
                        <div class="mt-1 text-[11px] text-slate-500 tabular-nums">
                            Disponible en origen: <strong class="text-slate-800" x-text="getStockOrigen().toFixed(2)"></strong>
                        </div>
                    </div>

                    <!-- Destino -->
                    <div>
                        <x-field label="Sucursal de Destino (Recepción)" name="sucursal_destino_id" :required="true">
                            <select name="sucursal_destino_id" id="sucursal_destino_id" x-model="sucursalDestinoId" required
                                class="block w-full py-2 px-3 text-xs border border-slate-300 rounded-lg text-slate-900 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                                @foreach($sucursales as $s)
                                <option value="{{ $s->id }}" {{ $initialDestinoId == $s->id ? 'selected' : '' }}>
                                    {{ $s->nombre }} ({{ $s->codigo }})
                                </option>
                                @endforeach
                            </select>
                        </x-field>
                        <div class="mt-1 text-[11px] text-slate-500 tabular-nums">
                            Stock actual en destino: <strong class="text-slate-800" x-text="getStockDestino().toFixed(2)"></strong>
                        </div>
                    </div>
                </div>

                <!-- Cantidad -->
                <div>
                    <x-field label="Cantidad de Unidades a Trasladar" name="cantidad" :required="true">
                        <input type="number" step="0.01" min="0.01" name="cantidad" id="cantidad" x-model="cantidad" required
                            placeholder="0.00"
                            class="block w-full py-2 px-3 text-sm border border-slate-300 rounded-lg text-slate-900 font-bold tabular-nums focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                    </x-field>
                </div>

                <!-- Alerta Origen = Destino -->
                <template x-if="sucursalOrigenId && sucursalDestinoId && sucursalOrigenId === sucursalDestinoId">
                    <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-lg text-xs text-amber-800 font-medium">
                        La sucursal de origen y destino no pueden ser la misma sede.
                    </div>
                </template>
            </div>

            <!-- SECCIÓN 3: Justificación -->
            <div class="p-6 space-y-4">
                <div class="flex items-center space-x-3 pb-3 border-b border-slate-100">
                    <span class="flex items-center justify-center h-7 w-7 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200">3</span>
                    <h2 class="text-sm font-bold text-slate-900">Justificación y Notas</h2>
                </div>

                <div class="space-y-4">
                    <x-field label="Motivo del Traslado" name="motivo" :required="true">
                        <input type="text" name="motivo" id="motivo" value="{{ old('motivo') }}" required
                            placeholder="Ej: Reabastecimiento de inventario sede Norte"
                            class="block w-full py-2 px-3 text-xs border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                    </x-field>

                    <x-field label="Observaciones (Opcional)" name="observaciones">
                        <textarea name="observaciones" id="observaciones" rows="2"
                            placeholder="Notas de despacho o transportador..."
                            class="block w-full py-2 px-3 text-xs border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">{{ old('observaciones') }}</textarea>
                    </x-field>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="p-5 bg-slate-50/50 flex items-center justify-end gap-2.5">
                <x-button variant="outline" href="{{ route('inventario.index') }}">
                    Cancelar
                </x-button>
                <button type="submit"
                        :disabled="!esValido()"
                        class="inline-flex items-center justify-center font-medium rounded-lg px-4 py-2 text-sm bg-brand-600 hover:bg-brand-700 text-white shadow-xs transition disabled:opacity-40 disabled:cursor-not-allowed">
                    Confirmar Traslado
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
