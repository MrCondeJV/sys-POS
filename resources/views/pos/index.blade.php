@extends('layouts.app')

@section('title', 'Punto de Venta (POS)')

@section('content')
<div x-data="posTerminal({
    cajaSesionId: {{ $sesionCaja->id }},
    productos: {{ Js::from($productos) }},
    clientes: {{ Js::from($clientes) }},
    csrfToken: '{{ csrf_token() }}',
    procesarUrl: '{{ route('pos.procesar') }}'
})"
x-init="initTerminal()"
@keydown.window="handleHotkeys($event)"
class="max-w-[1720px] mx-auto pb-10">

    <!-- Header Terminal POS -->
    <div class="bg-white border border-slate-200 rounded-3xl p-4 sm:p-5 mb-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-4">
            <div class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black shadow-md shadow-indigo-500/20">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl font-black text-slate-900 tracking-tight">Terminal de Venta</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        Caja Abierta
                    </span>
                </div>
                <div class="text-xs text-slate-500 flex flex-wrap items-center gap-x-4 gap-y-1 mt-0.5">
                    <span><strong>Caja:</strong> {{ $sesionCaja->caja->nombre }} ({{ $sesionCaja->caja->codigo }})</span>
                    <span>&bull;</span>
                    <span><strong>Sucursal:</strong> {{ $sesionCaja->sucursal->nombre }}</span>
                    <span>&bull;</span>
                    <span><strong>Cajero:</strong> {{ auth()->user()->name }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-3 text-xs">
            <div class="hidden lg:flex items-center space-x-2 bg-slate-50 border border-slate-200 px-3 py-2 rounded-xl text-slate-600">
                <span class="font-bold text-slate-700">Teclas rápidas:</span>
                <span class="bg-white border px-1.5 py-0.5 rounded shadow-xs font-mono font-bold text-slate-800">F2</span> Buscar
                <span class="bg-white border px-1.5 py-0.5 rounded shadow-xs font-mono font-bold text-slate-800">F4</span> Cobrar
                <span class="bg-white border px-1.5 py-0.5 rounded shadow-xs font-mono font-bold text-slate-800">F8</span> Cancelar
            </div>
            <a href="{{ route('cajas.show', $sesionCaja->caja) }}" class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold transition">
                Ver Caja
            </a>
            <a href="{{ route('cajas.cierre', $sesionCaja->caja) }}" class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold transition shadow-sm">
                Cerrar Turno
            </a>
        </div>
    </div>

    <!-- Main Grid: Catalog / Search (Left) & Cart / Checkout (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Columna Izquierda: Búsqueda y Catálogo (7 cols) -->
        <div class="lg:col-span-7 space-y-4">
            
            <!-- Barra de Búsqueda Omnibox con lector de código de barras -->
            <div class="bg-white border border-slate-200 rounded-3xl p-4 shadow-sm">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text"
                           x-ref="searchInput"
                           x-model="searchQuery"
                           @keydown.enter.prevent="onSearchEnter()"
                           placeholder="Escanear código de barras o buscar por nombre / SKU (F2)..."
                           class="w-full pl-12 pr-28 py-3.5 rounded-2xl border border-slate-200 text-slate-900 placeholder-slate-400 font-medium focus:border-indigo-600 focus:ring-2 focus:ring-indigo-600/20 text-base shadow-inner">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center space-x-1">
                        <template x-if="searchQuery">
                            <button @click="searchQuery = ''; focusSearch()" class="p-1 text-slate-400 hover:text-slate-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </template>
                        <span class="bg-slate-100 text-slate-500 font-mono text-xs px-2 py-1 rounded-lg border border-slate-200">F2</span>
                    </div>
                </div>

                <!-- Filtro por Categorías -->
                <div class="flex items-center space-x-2 overflow-x-auto pt-3 mt-3 border-t border-slate-100 no-scrollbar">
                    <button type="button"
                            @click="selectedCategory = null"
                            :class="selectedCategory === null ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition">
                        Todos
                    </button>
                    @foreach($categorias as $cat)
                        <button type="button"
                                @click="selectedCategory = {{ $cat->id }}"
                                :class="selectedCategory === {{ $cat->id }} ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition">
                            {{ $cat->nombre }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Grilla de Productos -->
            <div class="bg-white border border-slate-200 rounded-3xl p-4 shadow-sm min-h-[500px]">
                <div class="flex items-center justify-between mb-3 px-1">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                        Productos disponibles (<span x-text="filteredProducts.length"></span>)
                    </span>
                    <span class="text-xs text-slate-400">Click para agregar al carrito</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 max-h-[580px] overflow-y-auto pr-1">
                    <template x-for="p in filteredProducts" :key="p.id">
                        <div @click="addToCart(p)"
                             class="group p-3 rounded-2xl border border-slate-200 hover:border-indigo-500 hover:shadow-md transition cursor-pointer flex flex-col justify-between bg-slate-50/50 hover:bg-white relative">
                            
                            <!-- Badges -->
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[10px] font-bold text-slate-500 uppercase truncate max-w-[90px]" x-text="p.categoria_nombre"></span>
                                <span :class="p.stock > 5 ? 'bg-emerald-100 text-emerald-800' : (p.stock > 0 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800')"
                                      class="text-[10px] font-black px-1.5 py-0.5 rounded-md">
                                    <span x-text="p.stock"></span> <span x-text="p.unidad"></span>
                                </span>
                            </div>

                            <!-- Title & SKU -->
                            <div class="mb-2">
                                <h4 class="font-bold text-slate-900 text-xs sm:text-sm line-clamp-2 leading-snug group-hover:text-indigo-600 transition" x-text="p.nombre"></h4>
                                <div class="text-[11px] font-mono text-slate-400 mt-0.5 truncate" x-text="p.codigo_barras || p.sku || p.codigo"></div>
                            </div>

                            <!-- Price -->
                            <div class="flex items-center justify-between pt-2 border-t border-slate-200/60 mt-auto">
                                <span class="text-xs sm:text-sm font-black text-slate-900">
                                    $<span x-text="formatCurrency(p.precio_venta)"></span>
                                </span>
                                <div class="h-6 w-6 rounded-lg bg-indigo-50 group-hover:bg-indigo-600 group-hover:text-white text-indigo-600 flex items-center justify-center transition">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty state -->
                <div x-show="filteredProducts.length === 0" class="py-16 text-center text-slate-400 space-y-2">
                    <svg class="h-12 w-12 mx-auto text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-sm font-medium">No se encontraron productos coincidentes con tu búsqueda.</p>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Carrito, Cliente y Cobro (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            
            <div class="bg-white border border-slate-200 rounded-3xl p-5 shadow-sm space-y-4 flex flex-col justify-between">
                
                <!-- Selector de Cliente -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Cliente</label>
                        <template x-if="selectedCliente && selectedCliente.tiene_credito">
                            <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200">
                                Cupo Crédito: $<span x-text="formatCurrency(selectedCliente.cupo_disponible)"></span>
                            </span>
                        </template>
                    </div>
                    <select x-model="selectedClienteId"
                            class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 font-bold text-xs sm:text-sm text-slate-800 bg-slate-50 focus:bg-white focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600">
                        <template x-for="c in clientes" :key="c.id">
                            <option :value="c.id" x-text="c.razon_social + (c.es_consumidor_final ? ' (Consumidor Final)' : ' - ' + c.numero_documento)"></option>
                        </template>
                    </select>
                </div>

                <!-- Tabla de Items en Carrito -->
                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                    <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex items-center justify-between text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <span>Detalle de Venta (<span x-text="cart.length"></span>)</span>
                        <button @click="clearCart()" x-show="cart.length > 0" class="text-red-500 hover:text-red-700 text-[11px] font-bold">
                            Vaciar (F8)
                        </button>
                    </div>

                    <div class="max-h-[320px] overflow-y-auto divide-y divide-slate-100">
                        <template x-for="(item, idx) in cart" :key="item.id">
                            <div class="p-3 hover:bg-slate-50/70 transition flex items-center justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <h5 class="text-xs font-bold text-slate-900 truncate" x-text="item.nombre"></h5>
                                    <div class="text-[11px] text-slate-500">
                                        $<span x-text="formatCurrency(item.precio)"></span> x <span x-text="item.unidad"></span>
                                        <template x-if="item.impuesto_porcentaje > 0">
                                            <span class="text-slate-400 font-mono">(IVA <span x-text="item.impuesto_porcentaje"></span>%)</span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Cantidad Controls -->
                                <div class="flex items-center space-x-1.5">
                                    <button @click="updateQty(idx, -1)" class="h-6 w-6 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs">
                                        -
                                    </button>
                                    <input type="number" step="1" min="1" x-model.number="item.cantidad" @change="if(item.cantidad < 1) item.cantidad = 1"
                                           class="w-12 text-center text-xs font-black py-1 px-1 border border-slate-200 rounded-lg">
                                    <button @click="updateQty(idx, 1)" class="h-6 w-6 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs">
                                        +
                                    </button>
                                </div>

                                <!-- Subtotal Line -->
                                <div class="text-right w-20">
                                    <div class="text-xs font-black text-slate-900">
                                        $<span x-text="formatCurrency(item.cantidad * item.precio)"></span>
                                    </div>
                                    <button @click="removeItem(idx)" class="text-[10px] text-red-500 hover:underline">
                                        Quitar
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div x-show="cart.length === 0" class="py-12 text-center text-slate-400 text-xs">
                            El carrito está vacío.<br>Escanea un código o selecciona productos de la lista.
                        </div>
                    </div>
                </div>

                <!-- Resumen Financiero -->
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 space-y-2">
                    <div class="flex justify-between text-xs text-slate-600 font-medium">
                        <span>Subtotal:</span>
                        <span class="font-bold text-slate-800">$<span x-text="formatCurrency(subtotal)"></span></span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-600 font-medium">
                        <span>Impuestos (IVA):</span>
                        <span class="font-bold text-slate-800">$<span x-text="formatCurrency(impuestosTotal)"></span></span>
                    </div>
                    <template x-if="descuentoTotal > 0">
                        <div class="flex justify-between text-xs text-emerald-600 font-medium">
                            <span>Descuento:</span>
                            <span class="font-bold">-$<span x-text="formatCurrency(descuentoTotal)"></span></span>
                        </div>
                    </template>
                    <div class="border-t border-slate-200 pt-3 flex justify-between items-baseline">
                        <span class="text-sm font-black text-slate-900 uppercase">Total a Pagar:</span>
                        <span class="text-2xl font-black text-indigo-600">
                            $<span x-text="formatCurrency(total)"></span>
                        </span>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <button type="button"
                            @click="clearCart()"
                            :disabled="cart.length === 0"
                            class="py-3 px-4 rounded-2xl border border-slate-300 font-bold text-xs text-slate-600 hover:bg-slate-100 transition disabled:opacity-50">
                        Cancelar (F8)
                    </button>
                    <button type="button"
                            @click="openPaymentModal()"
                            :disabled="cart.length === 0"
                            class="py-3.5 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm shadow-md shadow-emerald-600/20 transition disabled:opacity-50 flex items-center justify-center space-x-2">
                        <span>COBRAR (F4)</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                </div>

            </div>
        </div>

    </div>

    <!-- MODAL DE COBRO Y MÉTODOS DE PAGO (FASE 12) -->
    <div x-cloak x-show="modalPago" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 p-6">
            <div x-show="modalPago" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
            
            <div x-show="modalPago" @click.away="modalPago = false"
                 class="relative bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 z-10 space-y-6">
                
                <!-- Encabezado Modal -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider block">Finalizar Venta</span>
                        <h3 class="text-xl font-black text-slate-900">Total a Cobrar: $<span x-text="formatCurrency(total)"></span></h3>
                    </div>
                    <button @click="modalPago = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Opciones de Factura & Tipo de Pago -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Comprobante</label>
                        <select x-model="tipoComprobante" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
                            <option value="TICKET">Ticket POS</option>
                            <option value="FACTURA">Factura Comercial</option>
                            <option value="NOTA_VENTA">Nota de Venta</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Condición</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="tipoPago = 'CONTADO'"
                                    :class="tipoPago === 'CONTADO' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 font-medium'"
                                    class="py-2 text-xs rounded-xl transition">
                                Contado
                            </button>
                            <button type="button" @click="selectCredito()"
                                    :class="tipoPago === 'CREDITO' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 font-medium'"
                                    class="py-2 text-xs rounded-xl transition">
                                Crédito
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Selector de Método de Pago (Cuando es Contado) -->
                <div x-show="tipoPago === 'CONTADO'" class="space-y-4">
                    <label class="block text-xs font-bold text-slate-700 uppercase">Método de Pago</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <button type="button" @click="metodoPago = 'EFECTIVO'; autoCalcularEfectivo()"
                                :class="metodoPago === 'EFECTIVO' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700 font-black' : 'border-slate-200 text-slate-600'"
                                class="p-3 rounded-2xl border text-xs font-bold flex flex-col items-center justify-center space-y-1 transition">
                            <span>💵 Efectivo</span>
                        </button>
                        <button type="button" @click="metodoPago = 'TARJETA'; pagoCon = total"
                                :class="metodoPago === 'TARJETA' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700 font-black' : 'border-slate-200 text-slate-600'"
                                class="p-3 rounded-2xl border text-xs font-bold flex flex-col items-center justify-center space-y-1 transition">
                            <span>💳 Tarjeta</span>
                        </button>
                        <button type="button" @click="metodoPago = 'TRANSFERENCIA'; pagoCon = total"
                                :class="metodoPago === 'TRANSFERENCIA' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700 font-black' : 'border-slate-200 text-slate-600'"
                                class="p-3 rounded-2xl border text-xs font-bold flex flex-col items-center justify-center space-y-1 transition">
                            <span>📱 Transferencia</span>
                        </button>
                        <button type="button" @click="metodoPago = 'MIXTO'; prepararPagoMixto()"
                                :class="metodoPago === 'MIXTO' ? 'border-indigo-600 bg-indigo-50/50 text-indigo-700 font-black' : 'border-slate-200 text-slate-600'"
                                class="p-3 rounded-2xl border text-xs font-bold flex flex-col items-center justify-center space-y-1 transition">
                            <span>🔄 Pago Mixto</span>
                        </button>
                    </div>

                    <!-- Panel de Efectivo: Pago con y Vueltos -->
                    <div x-show="metodoPago === 'EFECTIVO'" class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-3">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Paga con *</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-slate-400">$</span>
                                    <input type="number" step="100" min="0" x-model.number="pagoCon"
                                           x-ref="pagoConInput"
                                           class="w-full pl-7 pr-3 py-2 text-lg font-black text-slate-900 rounded-xl border border-slate-200 focus:border-indigo-600">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Cambio / Vueltos</label>
                                <div :class="cambio >= 0 ? 'text-emerald-600' : 'text-red-500'"
                                     class="px-3 py-2 text-xl font-black bg-white rounded-xl border border-slate-200">
                                    $<span x-text="formatCurrency(Math.max(0, cambio))"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Billetes Rápidos -->
                        <div class="pt-2">
                            <span class="block text-[11px] font-bold text-slate-400 uppercase mb-1.5">Billetes sugeridos:</span>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="pagoCon = total" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-bold hover:bg-slate-100">
                                    Exacto ($<span x-text="formatCurrency(total)"></span>)
                                </button>
                                <button type="button" @click="pagoCon = 10000" x-show="total <= 10000" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-bold hover:bg-slate-100">$10.000</button>
                                <button type="button" @click="pagoCon = 20000" x-show="total <= 20000" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-bold hover:bg-slate-100">$20.000</button>
                                <button type="button" @click="pagoCon = 50000" x-show="total <= 50000" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-bold hover:bg-slate-100">$50.000</button>
                                <button type="button" @click="pagoCon = 100000" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-bold hover:bg-slate-100">$100.000</button>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de Pago Mixto (Fase 12) -->
                    <div x-show="metodoPago === 'MIXTO'" class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700 uppercase">Detalle de pagos combinados</span>
                            <button type="button" @click="agregarFilaMixta()" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                + Agregar método
                            </button>
                        </div>
                        
                        <div class="space-y-2">
                            <template x-for="(pagoItem, pIdx) in pagosMixtos" :key="pIdx">
                                <div class="flex items-center space-x-2 bg-white p-2 rounded-xl border border-slate-200">
                                    <select x-model="pagoItem.metodo_pago" class="w-1/3 select-compact">
                                        <option value="EFECTIVO">Efectivo</option>
                                        <option value="TARJETA">Tarjeta</option>
                                        <option value="TRANSFERENCIA">Transferencia</option>
                                        <option value="OTRO">Otro</option>
                                    </select>
                                    <div class="relative flex-1">
                                        <span class="absolute inset-y-0 left-0 pl-2 flex items-center font-bold text-slate-400 text-xs">$</span>
                                        <input type="number" step="0.01" min="0" x-model.number="pagoItem.monto"
                                               class="w-full pl-5 input-compact">
                                    </div>
                                    <input type="text" x-model="pagoItem.referencia" placeholder="Ref/voucher"
                                           class="w-28 input-compact">
                                    <button type="button" @click="quitarFilaMixta(pIdx)" class="text-red-500 hover:text-red-700 px-1 font-bold text-xs">
                                        ✕
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="flex justify-between items-center text-xs pt-1">
                            <span class="font-bold text-slate-600">Suma ingresada: $<span x-text="formatCurrency(sumaPagosMixtos)"></span></span>
                            <span :class="sumaPagosMixtos >= total ? 'text-emerald-600 font-bold' : 'text-red-500 font-bold'">
                                <span x-text="sumaPagosMixtos >= total ? '¡Monto cubierto!' : 'Faltan $' + formatCurrency(total - sumaPagosMixtos)"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Panel Crédito Comercial -->
                <div x-show="tipoPago === 'CREDITO'" class="bg-amber-50 p-4 rounded-2xl border border-amber-200 space-y-2">
                    <h5 class="text-xs font-black text-amber-900 uppercase">Venta a Crédito</h5>
                    <p class="text-xs text-amber-800">
                        Se generará una cuenta por cobrar en el módulo de Cartera para el cliente
                        <strong x-text="selectedCliente ? selectedCliente.razon_social : ''"></strong>.
                    </p>
                    <div class="text-xs font-bold text-amber-900 pt-1">
                        Plazo acordado: <span x-text="selectedCliente ? selectedCliente.plazo_dias : 30"></span> días.
                    </div>
                </div>

                <!-- Observaciones -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Observaciones (Opcional)</label>
                    <input type="text" x-model="observaciones" placeholder="Ej: Pago con billete de $100.000 / Cliente frecuente"
                           class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200">
                </div>

                <!-- Error alert in modal -->
                <div x-show="errorMsg" class="p-3 bg-red-50 text-red-700 rounded-xl text-xs font-bold flex items-center space-x-2">
                    <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span x-text="errorMsg"></span>
                </div>

                <!-- Footer Modal -->
                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="modalPago = false" class="px-4 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-50 rounded-xl">
                        Regresar
                    </button>
                    <button type="button"
                            @click="enviarVenta()"
                            :disabled="loading || (tipoPago === 'CONTADO' && metodoPago === 'EFECTIVO' && cambio < 0) || (tipoPago === 'CONTADO' && metodoPago === 'MIXTO' && sumaPagosMixtos < total)"
                            class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs sm:text-sm rounded-xl shadow-md transition disabled:opacity-50 flex items-center space-x-2">
                        <template x-if="loading">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <span x-text="loading ? 'Procesando...' : 'Confirmar Venta (Enter)'"></span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL DE ÉXITO Y TICKET -->
    <div x-cloak x-show="modalExito" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 p-6">
            <div x-show="modalExito" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            
            <div x-show="modalExito"
                 class="relative bg-white rounded-3xl max-w-md w-full p-8 shadow-2xl border border-slate-100 z-10 text-center space-y-5">
                
                <div class="h-16 w-16 bg-emerald-100 text-emerald-600 rounded-3xl mx-auto flex items-center justify-center">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <div>
                    <h3 class="text-xl font-black text-slate-900">¡Venta Exitosa!</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Comprobante <strong x-text="ventaExitosa.numero_venta"></strong> generado correctamente.
                    </p>
                </div>

                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-2 text-left">
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500">Total Venta:</span>
                        <span class="font-black text-slate-900">$<span x-text="formatCurrency(ventaExitosa.total)"></span></span>
                    </div>
                    <template x-if="ventaExitosa.cambio > 0">
                        <div class="flex justify-between text-xs pt-1 border-t border-slate-200">
                            <span class="text-emerald-700 font-bold">Cambio a Entregar:</span>
                            <span class="font-black text-emerald-700 text-sm">$<span x-text="formatCurrency(ventaExitosa.cambio)"></span></span>
                        </div>
                    </template>
                </div>

                <div class="space-y-2 pt-2">
                    <a :href="ventaExitosa.ticket_url" target="_blank"
                       class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs flex items-center justify-center space-x-2 shadow-sm">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        <span>Imprimir Ticket</span>
                    </a>
                    <button type="button" @click="nuevaVenta()"
                            class="w-full py-3 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl font-bold text-xs">
                        Nueva Venta (Enter)
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function posTerminal(config) {
    return {
        cajaSesionId: config.cajaSesionId,
        allProducts: config.productos || [],
        clientes: config.clientes || [],
        csrfToken: config.csrfToken,
        procesarUrl: config.procesarUrl,

        searchQuery: '',
        selectedCategory: null,
        selectedClienteId: null,
        cart: [],

        modalPago: false,
        modalExito: false,
        loading: false,
        errorMsg: '',

        tipoComprobante: 'TICKET',
        tipoPago: 'CONTADO',
        metodoPago: 'EFECTIVO',
        pagoCon: 0,
        observaciones: '',
        pagosMixtos: [],

        ventaExitosa: {
            numero_venta: '',
            total: 0,
            cambio: 0,
            ticket_url: ''
        },

        initTerminal() {
            const defaultCli = this.clientes.find(c => c.es_consumidor_final) || this.clientes[0];
            if (defaultCli) {
                this.selectedClienteId = defaultCli.id;
            }
            this.focusSearch();
        },

        focusSearch() {
            this.$nextTick(() => {
                if (this.$refs.searchInput) {
                    this.$refs.searchInput.focus();
                }
            });
        },

        get selectedCliente() {
            return this.clientes.find(c => c.id == this.selectedClienteId);
        },

        get filteredProducts() {
            let list = this.allProducts;
            if (this.selectedCategory !== null) {
                list = list.filter(p => p.categoria_id === this.selectedCategory);
            }
            if (this.searchQuery && this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase().trim();
                list = list.filter(p => 
                    p.nombre.toLowerCase().includes(q) ||
                    (p.codigo_barras && p.codigo_barras.toLowerCase().includes(q)) ||
                    (p.sku && p.sku.toLowerCase().includes(q)) ||
                    (p.codigo && p.codigo.toLowerCase().includes(q))
                );
            }
            return list;
        },

        get subtotal() {
            return this.cart.reduce((acc, item) => acc + (item.cantidad * item.precio), 0);
        },

        get descuentoTotal() {
            return this.cart.reduce((acc, item) => acc + ((item.descuento || 0) * item.cantidad), 0);
        },

        get impuestosTotal() {
            return this.cart.reduce((acc, item) => {
                const base = (item.cantidad * item.precio) - ((item.descuento || 0) * item.cantidad);
                return acc + (base * ((item.impuesto_porcentaje || 0) / 100));
            }, 0);
        },

        get total() {
            return Math.max(0, this.subtotal - this.descuentoTotal + this.impuestosTotal);
        },

        get cambio() {
            return (this.pagoCon || 0) - this.total;
        },

        get sumaPagosMixtos() {
            return this.pagosMixtos.reduce((acc, p) => acc + (parseFloat(p.monto) || 0), 0);
        },

        addToCart(product) {
            const existing = this.cart.find(i => i.id === product.id);
            if (existing) {
                existing.cantidad += 1;
            } else {
                this.cart.push({
                    id: product.id,
                    nombre: product.nombre,
                    codigo: product.codigo_barras || product.sku || product.codigo,
                    precio: parseFloat(product.precio_venta),
                    cantidad: 1,
                    impuesto_porcentaje: parseFloat(product.iva_porcentaje || 0),
                    descuento: 0,
                    stock: product.stock,
                    unidad: product.unidad
                });
            }
        },

        updateQty(idx, delta) {
            const item = this.cart[idx];
            if (!item) return;
            const newQty = item.cantidad + delta;
            if (newQty <= 0) {
                this.removeItem(idx);
            } else {
                item.cantidad = newQty;
            }
        },

        removeItem(idx) {
            this.cart.splice(idx, 1);
        },

        clearCart() {
            this.cart = [];
            this.focusSearch();
        },

        onSearchEnter() {
            const q = this.searchQuery.trim().toLowerCase();
            if (!q) return;

            const exact = this.allProducts.find(p => 
                (p.codigo_barras && p.codigo_barras.toLowerCase() === q) ||
                (p.sku && p.sku.toLowerCase() === q) ||
                (p.codigo && p.codigo.toLowerCase() === q)
            );

            if (exact) {
                this.addToCart(exact);
                this.searchQuery = '';
                return;
            }

            if (this.filteredProducts.length === 1) {
                this.addToCart(this.filteredProducts[0]);
                this.searchQuery = '';
                return;
            }
        },

        openPaymentModal() {
            if (this.cart.length === 0) return;
            this.errorMsg = '';
            this.tipoPago = 'CONTADO';
            this.metodoPago = 'EFECTIVO';
            this.autoCalcularEfectivo();
            this.modalPago = true;
            this.$nextTick(() => {
                if (this.$refs.pagoConInput) {
                    this.$refs.pagoConInput.focus();
                    this.$refs.pagoConInput.select();
                }
            });
        },

        autoCalcularEfectivo() {
            this.pagoCon = this.total;
        },

        selectCredito() {
            const cli = this.selectedCliente;
            if (!cli || !cli.tiene_credito) {
                alert('El cliente seleccionado no tiene habilitada la línea de crédito comercial.');
                return;
            }
            if (cli.cupo_disponible < this.total) {
                alert('El cupo disponible del cliente ($' + this.formatCurrency(cli.cupo_disponible) + ') es insuficiente para cubrir esta venta ($' + this.formatCurrency(this.total) + ').');
                return;
            }
            this.tipoPago = 'CREDITO';
            this.metodoPago = 'CREDITO';
        },

        prepararPagoMixto() {
            this.pagosMixtos = [
                { metodo_pago: 'EFECTIVO', monto: Math.round(this.total / 2), referencia: '' },
                { metodo_pago: 'TARJETA', monto: Math.round(this.total - Math.round(this.total / 2)), referencia: '' }
            ];
        },

        agregarFilaMixta() {
            this.pagosMixtos.push({ metodo_pago: 'TRANSFERENCIA', monto: 0, referencia: '' });
        },

        quitarFilaMixta(idx) {
            this.pagosMixtos.splice(idx, 1);
        },

        handleHotkeys(e) {
            if (e.key === 'F2') {
                e.preventDefault();
                this.focusSearch();
            } else if (e.key === 'F4') {
                e.preventDefault();
                if (!this.modalPago && this.cart.length > 0) {
                    this.openPaymentModal();
                }
            } else if (e.key === 'F8') {
                e.preventDefault();
                this.clearCart();
            } else if (e.key === 'Escape') {
                if (this.modalPago) this.modalPago = false;
                if (this.modalExito) this.nuevaVenta();
            }
        },

        async enviarVenta() {
            if (this.cart.length === 0) return;
            this.loading = true;
            this.errorMsg = '';

            const payload = {
                caja_sesion_id: this.cajaSesionId,
                cliente_id: this.selectedClienteId,
                tipo_pago: this.tipoPago,
                metodo_pago: this.metodoPago,
                tipo_comprobante: this.tipoComprobante,
                observaciones: this.observaciones,
                pago_con: this.metodoPago === 'EFECTIVO' ? this.pagoCon : this.total,
                items: this.cart.map(i => ({
                    producto_id: i.id,
                    cantidad: i.cantidad,
                    precio_unitario: i.precio,
                    descuento: i.descuento || 0,
                    impuesto_porcentaje: i.impuesto_porcentaje || 0
                }))
            };

            if (this.metodoPago === 'MIXTO') {
                payload.pagos = this.pagosMixtos;
            }

            try {
                const response = await fetch(this.procesarUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Error al procesar la venta.');
                }

                this.ventaExitosa = {
                    numero_venta: data.numero_venta,
                    total: data.total,
                    cambio: data.cambio,
                    ticket_url: data.ticket_url
                };

                this.modalPago = false;
                this.modalExito = true;
                this.cart = [];
                this.observaciones = '';

            } catch (err) {
                this.errorMsg = err.message;
            } finally {
                this.loading = false;
            }
        },

        nuevaVenta() {
            this.modalExito = false;
            this.initTerminal();
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(amount || 0);
        }
    };
}
</script>
@endsection
