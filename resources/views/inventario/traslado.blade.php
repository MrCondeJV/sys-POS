@extends('layouts.app')

@section('title', 'Traslado de Existencias entre Sucursales — POS Comercial')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="flex items-center space-x-2 text-xs text-slate-400 mb-1">
                <a href="{{ route('inventario.index') }}" class="hover:text-indigo-600 transition">Inventario</a>
                <span>/</span>
                <span class="text-slate-600 font-semibold">Traslados</span>
            </nav>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Traslado de Existencias entre Sucursales</h1>
            <p class="text-sm text-slate-500 font-medium">Transfiere existencias físicas entre sedes de tu empresa con doble asiento atómico en Kardex.</p>
        </div>

        <a href="{{ route('inventario.index') }}"
            class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 shadow-sm transition">
            <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
        </a>
    </div>

    @php
        $initialProdId = old('producto_id', $productoSeleccionado?->id ?? ($productos->first()?->id ?? ''));
        $initialOrigenId = old('sucursal_origen_id', $sucursalOrigenId ?? ($sucursales->first()?->id ?? ''));
        $initialDestinoId = old('sucursal_destino_id', $sucursales->count() > 1 ? $sucursales->last()->id : '');
        $initialCant = old('cantidad', 1);

        $stockMap = [];
        foreach (\App\Models\Inventario::whereIn('producto_id', $productos->pluck('id'))->get() as $invItem) {
            $stockMap[$invItem->producto_id][$invItem->sucursal_id] = (float) $invItem->stock;
        }
    @endphp

    <div x-data="{
            productoId: '{{ $initialProdId }}',
            sucursalOrigenId: '{{ $initialOrigenId }}',
            sucursalDestinoId: '{{ $initialDestinoId }}',
            cantidad: {{ (float) $initialCant }},
            stockMap: {{ json_encode($stockMap) }},
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
                return this.sucursalOrigenId &&
                       this.sucursalDestinoId &&
                       this.sucursalOrigenId !== this.sucursalDestinoId &&
                       parseFloat(this.cantidad) > 0 &&
                       this.getStockOrigenProyectado() >= 0;
            }
         }"
         class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        @if($errors->has('general'))
        <div class="p-4 bg-rose-50 border-b border-rose-200 text-rose-800 text-sm font-semibold">
            {{ $errors->first('general') }}
        </div>
        @endif

        <form method="POST" action="{{ route('inventario.traslado.store') }}" class="p-6 space-y-6">
            @csrf

            <!-- Producto a trasladar -->
            <div>
                <label for="producto_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Producto a Transferir <span class="text-rose-500">*</span>
                </label>
                <select name="producto_id" id="producto_id" x-model="productoId" required
                    class="block w-full py-2.5 px-3 text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition font-medium">
                    <option value="" disabled>Seleccione un producto...</option>
                    @foreach($productos as $p)
                    <option value="{{ $p->id }}" {{ $initialProdId == $p->id ? 'selected' : '' }}>
                        {{ $p->nombre }} (SKU: {{ $p->codigo ?? 'N/A' }}) — Stock Consolidado: {{ number_format($p->stock, 2) }}
                    </option>
                    @endforeach
                </select>
                @error('producto_id')
                <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Sedes Origen y Destino -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Sucursal Origen -->
                <div>
                    <label for="sucursal_origen_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Sucursal de Origen (Emite) <span class="text-rose-500">*</span>
                    </label>
                    <select name="sucursal_origen_id" id="sucursal_origen_id" x-model="sucursalOrigenId" required
                        class="block w-full py-2.5 px-3 text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition font-medium">
                        <option value="" disabled>Seleccione sede de origen...</option>
                        @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ $initialOrigenId == $s->id ? 'selected' : '' }}>
                            {{ $s->nombre }} {{ $s->es_principal ? '(Principal)' : '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('sucursal_origen_id')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sucursal Destino -->
                <div>
                    <label for="sucursal_destino_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Sucursal de Destino (Recibe) <span class="text-rose-500">*</span>
                    </label>
                    <select name="sucursal_destino_id" id="sucursal_destino_id" x-model="sucursalDestinoId" required
                        class="block w-full py-2.5 px-3 text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition font-medium">
                        <option value="" disabled>Seleccione sede de destino...</option>
                        @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ $initialDestinoId == $s->id ? 'selected' : '' }}>
                            {{ $s->nombre }} {{ $s->es_principal ? '(Principal)' : '' }}
                        </option>
                        @endforeach
                    </select>
                    @error('sucursal_destino_id')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Cantidad a Transferir -->
            <div>
                <label for="cantidad" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Cantidad a Transferir <span class="text-rose-500">*</span>
                </label>
                <div class="relative max-w-xs">
                    <input type="number" step="0.01" min="0.01" name="cantidad" id="cantidad" x-model="cantidad" required
                        class="block w-full py-2.5 px-3 text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition font-bold text-slate-800">
                </div>
                @error('cantidad')
                <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Simulador de Traslado Atómico -->
            <div class="p-5 rounded-2xl bg-indigo-50/50 border border-indigo-100 space-y-4">
                <div class="text-xs font-bold uppercase tracking-wider text-indigo-900">Simulación del Traslado en Tiempo Real</div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Origen -->
                    <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-2xs">
                        <div class="text-xs font-bold text-slate-400 uppercase">Sede de Origen</div>
                        <div class="text-sm font-semibold text-slate-800 mt-1">
                            Disponible: <span class="font-mono font-bold" x-text="getStockOrigen().toFixed(2)"></span>
                        </div>
                        <div class="text-sm font-semibold mt-0.5">
                            Quedará en: <span class="font-mono font-black" :class="getStockOrigenProyectado() < 0 ? 'text-rose-600' : 'text-slate-900'" x-text="getStockOrigenProyectado().toFixed(2)"></span>
                        </div>
                    </div>

                    <!-- Destino -->
                    <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-2xs">
                        <div class="text-xs font-bold text-slate-400 uppercase">Sede de Destino</div>
                        <div class="text-sm font-semibold text-slate-800 mt-1">
                            Existencias actuales: <span class="font-mono font-bold" x-text="getStockDestino().toFixed(2)"></span>
                        </div>
                        <div class="text-sm font-semibold mt-0.5">
                            Aumentará a: <span class="font-mono font-black text-emerald-600" x-text="getStockDestinoProyectado().toFixed(2)"></span>
                        </div>
                    </div>
                </div>

                <!-- Mensajes de advertencia en vivo -->
                <template x-if="sucursalOrigenId === sucursalDestinoId && sucursalOrigenId !== ''">
                    <p class="text-xs font-bold text-rose-600">
                        • La sucursal de origen y la de destino deben ser distintas.
                    </p>
                </template>

                <template x-if="getStockOrigenProyectado() < 0">
                    <p class="text-xs font-bold text-rose-600">
                        • La cantidad solicitada supera el stock disponible en la sucursal de origen.
                    </p>
                </template>
            </div>

            <!-- Justificación y Notas -->
            <div class="space-y-4">
                <div>
                    <label for="motivo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Motivo / Justificación del Traslado <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="motivo" id="motivo" value="{{ old('motivo') }}" required
                        placeholder="Ej: Reposición de stock por alta demanda en sede norte, Pedido interno #104..."
                        class="block w-full py-2.5 px-3 text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('motivo')
                    <p class="text-xs text-rose-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="notas" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Observaciones de Despacho / Conductor (Opcional)
                    </label>
                    <textarea name="notas" id="notas" rows="2"
                        placeholder="Número de guía, responsable de transporte, precinto de seguridad..."
                        class="block w-full py-2 px-3 text-sm border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition">{{ old('notas') }}</textarea>
                </div>
            </div>

            <!-- Botones -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('inventario.index') }}" class="px-4 py-2.5 bg-slate-100 text-slate-700 font-semibold rounded-xl text-sm hover:bg-slate-200 transition">
                    Cancelar
                </a>
                <button type="submit"
                        :disabled="!esValido()"
                        class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-sm hover:bg-indigo-700 shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed">
                    Procesar y Confirmar Traslado
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
