@extends('layouts.app')

@section('title', 'Punto de Venta (POS)')

@section('content')
<div x-data="posTerminal({
    cajaSesionId: {{ $sesionCaja->id }},
    productos: {{ Js::from($productos) }},
    clientes: {{ Js::from($clientes) }},
    csrfToken: '{{ csrf_token() }}',
    procesarUrl: '{{ route('pos.procesar') }}',
    carteraUrlTemplate: '{{ route('pos.cliente-cartera', '__CLIENTE__') }}',
    puedeVerCartera: @js(auth()->user()->can('viewAny', \App\Models\CuentaPorCobrar::class))
})"
x-init="initTerminal()"
@keydown.window="handleHotkeys($event)"
class="max-w-[1720px] mx-auto pb-10">

    <!-- Header Terminal POS Compacto -->
    <div class="card px-4 py-2.5 mb-4 min-h-14 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold shadow-xs">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-base font-bold text-slate-900 tracking-tight">Terminal de Venta</h1>
                    <span class="badge badge-success">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                        Caja Abierta
                    </span>
                </div>
                <div class="text-xs text-slate-500 flex flex-wrap items-center gap-x-2.5 gap-y-0.5 mt-0.5">
                    <span><strong>Caja:</strong> {{ $sesionCaja->caja->nombre }} ({{ $sesionCaja->caja->codigo }})</span>
                    <span>&bull;</span>
                    <span><strong>Sucursal:</strong> {{ $sesionCaja->sucursal?->nombre ?? 'Sin sucursal' }}</span>
                    <span>&bull;</span>
                    <span><strong>Cajero:</strong> {{ auth()->user()->name }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 text-xs">
            <div class="hidden lg:flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1 rounded-lg text-slate-600">
                <span class="font-semibold text-slate-700">Atajos:</span>
                <span class="kbd">F2</span> Buscar
                <span class="kbd">F4</span> Cobrar
                <span class="kbd">F8</span> Cancelar
            </div>
            <a href="{{ route('cajas.show', $sesionCaja->caja) }}" class="btn-secondary text-xs">
                Ver Caja
            </a>
            <a href="{{ route('cajas.cierre', $sesionCaja->caja) }}" class="btn-secondary text-xs">
                Cerrar Turno
            </a>
        </div>
    </div>

    <!-- Main Grid: Catalog / Search (Left) & Cart / Checkout (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        <!-- Columna Izquierda: Búsqueda y Catálogo (7 cols) -->
        <div class="lg:col-span-7 space-y-4">
            
            <!-- Barra de Búsqueda Omnibox con lector de código de barras -->
            <div class="card p-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text"
                           x-ref="searchInput"
                           x-model="searchQuery"
                           @keydown.enter.prevent="onSearchEnter()"
                           placeholder="Escanear código de barras o buscar por nombre / SKU (F2)..."
                           class="input h-11 pl-11 pr-28 text-sm">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center gap-1.5">
                        <template x-if="searchQuery">
                            <button @click="searchQuery = ''; focusSearch()" class="p-1 text-slate-400 hover:text-slate-600">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </template>
                        <span class="kbd">F2</span>
                        <span class="kbd">Enter</span>
                    </div>
                </div>

                <!-- Filtro por Categorías -->
                <div class="flex items-center gap-1.5 overflow-x-auto pt-3 mt-3 border-t border-slate-100 no-scrollbar">
                    <button type="button"
                            @click="selectedCategory = null"
                            :class="selectedCategory === null ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition">
                        Todos
                    </button>
                    @foreach($categorias as $cat)
                        <button type="button"
                                @click="selectedCategory = {{ $cat->id }}"
                                :class="selectedCategory === {{ $cat->id }} ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition">
                            {{ $cat->nombre }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Grilla de Productos -->
            <div class="card p-4 min-h-[500px]">
                <div class="flex items-center justify-between mb-3 px-1">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                        Productos disponibles (<span x-text="filteredProducts.length"></span>)
                    </span>
                    <span class="text-xs text-slate-400">Click para agregar al carrito</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 max-h-[580px] overflow-y-auto pr-1">
                    <template x-for="p in filteredProducts" :key="p.id">
                        <div @click="addToCart(p)"
                             :class="p.stock <= 0 ? 'opacity-60 border-slate-200 bg-slate-50/60' : 'border-slate-200 hover:border-brand-400 hover:bg-brand-50/20 bg-white'"
                             class="group p-3 rounded-lg border transition-all duration-150 cursor-pointer flex flex-col justify-between relative overflow-hidden">
                            
                            <!-- Contenedor de Imagen o Icono de Categoría de Producto -->
                            <div class="relative w-full h-24 mb-2 rounded-md overflow-hidden bg-surface-muted border border-slate-200/60 flex items-center justify-center flex-shrink-0">
                                <!-- Si tiene imagen -->
                                <template x-if="p.imagen_url">
                                    <img :src="p.imagen_url" 
                                         :alt="p.nombre" 
                                         loading="lazy"
                                         class="h-full w-full object-cover object-center group-hover:scale-105 transition-transform duration-200">
                                </template>
                                <!-- Si no tiene imagen: ícono limpio de producto sin texto gigante 'Sin foto' -->
                                <template x-if="!p.imagen_url">
                                    <div class="h-full w-full flex items-center justify-center bg-surface-muted text-slate-400">
                                        <svg class="h-8 w-8 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                </template>

                                <!-- Badge de Categoría (flotante superior izquierda) -->
                                <span class="absolute top-1.5 left-1.5 text-[9px] font-semibold text-slate-700 bg-white/95 border border-slate-200/80 px-1.5 py-0.5 rounded shadow-xs uppercase truncate max-w-[85px]"
                                      x-text="p.categoria_nombre"></span>

                                <!-- Badge de Stock (flotante superior derecha) -->
                                <span :class="p.stock > 5 ? 'badge-success' : (p.stock > 0 ? 'badge-warning' : 'badge-danger')"
                                      class="badge absolute top-1.5 right-1.5 text-[10px] font-bold font-mono px-1.5 py-0.5 shadow-xs">
                                    <span x-text="p.stock"></span> <span x-text="p.unidad"></span>
                                </span>
                            </div>

                            <!-- Title & SKU -->
                            <div class="mb-2">
                                <h4 class="font-semibold text-slate-900 text-xs sm:text-sm line-clamp-2 leading-snug group-hover:text-brand-600 transition" x-text="p.nombre"></h4>
                                <div class="text-[11px] font-mono text-slate-400 mt-0.5 truncate" x-text="p.codigo_barras || p.sku || p.codigo"></div>
                            </div>

                            <!-- Price -->
                            <div class="flex items-center justify-between pt-2 border-t border-slate-100 mt-auto">
                                <span class="text-sm sm:text-base font-bold font-mono tabular-nums text-slate-900">
                                    $<span x-text="formatCurrency(precioPara(p))"></span>
                                </span>
                                <div class="h-6 w-6 rounded-md bg-slate-100 group-hover:bg-brand-600 group-hover:text-white text-slate-600 flex items-center justify-center transition shadow-xs">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty state -->
                <div x-show="filteredProducts.length === 0" class="py-16 text-center text-slate-400 space-y-2">
                    <svg class="h-10 w-10 mx-auto text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-xs font-medium text-slate-500">No se encontraron productos coincidentes con tu búsqueda.</p>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Carrito, Cliente y Cobro (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            
            <div class="card p-5 space-y-4 flex flex-col justify-between">

                <!-- Tipo de Venta: Contado o Crédito -->
                <div>
                    <label class="label-base">Tipo de venta</label>
                    <div class="grid grid-cols-2 gap-2 p-1 bg-slate-100 rounded-lg">
                        <button type="button" @click="cambiarTipoVenta('CONTADO')"
                                :class="tipoPago === 'CONTADO' ? 'bg-white text-slate-900 font-semibold shadow-xs' : 'text-slate-600 font-medium hover:text-slate-900'"
                                class="py-2 text-xs rounded-md transition">
                            Venta de contado
                        </button>
                        <button type="button" @click="cambiarTipoVenta('CREDITO')"
                                :class="tipoPago === 'CREDITO' ? 'bg-white text-amber-800 font-semibold shadow-xs' : 'text-slate-600 font-medium hover:text-slate-900'"
                                class="py-2 text-xs rounded-md transition">
                            Venta a crédito
                        </button>
                    </div>
                </div>

                <!-- Selector de Cliente -->
                <div>
                    <label class="label-base">Cliente</label>
                    <select x-model="selectedClienteId"
                            class="input text-xs sm:text-sm font-medium">
                        <template x-for="c in clientes" :key="c.id">
                            <option :value="c.id" x-text="c.razon_social + (c.es_consumidor_final ? ' (Consumidor Final)' : ' - ' + c.numero_documento)"></option>
                        </template>
                    </select>
                </div>

                <!-- Panel de Crédito del Cliente: deuda, facturas pendientes e historial de abonos -->
                <div x-show="tipoPago === 'CREDITO'" x-cloak class="border border-amber-200 bg-amber-50/60 rounded-lg p-3 space-y-3">
                    <template x-if="selectedCliente && selectedCliente.es_consumidor_final">
                        <p class="text-xs font-semibold text-danger-text">
                            No se puede vender a crédito al Consumidor Final. Selecciona el cliente al que se le fía la venta.
                        </p>
                    </template>

                    <template x-if="selectedCliente && !selectedCliente.es_consumidor_final">
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-2 text-center">
                                <div class="bg-white rounded-md border border-amber-200 px-2 py-1.5">
                                    <div class="text-[10px] font-semibold text-slate-500 uppercase">Debe</div>
                                    <div class="text-sm font-bold font-mono tabular-nums text-slate-900">$<span x-text="formatCurrency(cartera?.total_deuda)"></span></div>
                                </div>
                                <div class="bg-white rounded-md border border-amber-200 px-2 py-1.5">
                                    <div class="text-[10px] font-semibold text-slate-500 uppercase">Cuentas abiertas</div>
                                    <div class="text-sm font-bold font-mono tabular-nums text-slate-900" x-text="cartera ? cartera.cuentas.length : 0"></div>
                                </div>
                            </div>

                            <template x-if="!puedeVerCartera">
                                <p class="text-[11px] text-slate-600">No tienes permiso para consultar el historial de cartera.</p>
                            </template>

                            <template x-if="puedeVerCartera">
                                <div class="space-y-3">
                                    <p x-show="carteraCargando" class="text-[11px] text-slate-500">Cargando cartera del cliente...</p>
                                    <p x-show="carteraError" class="text-[11px] font-semibold text-danger-text" x-text="carteraError"></p>

                                    <template x-if="cartera && !carteraCargando">
                                        <div class="space-y-3">
                                            <div>
                                                <div class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                                                    Facturas pendientes (<span x-text="cartera.cuentas.length"></span>)
                                                </div>
                                                <div class="max-h-32 overflow-y-auto divide-y divide-amber-100 bg-white rounded-md border border-amber-200">
                                                    <template x-for="c in cartera.cuentas" :key="c.id">
                                                        <a :href="c.url" target="_blank" class="flex items-center justify-between gap-2 px-2.5 py-1.5 text-[11px] hover:bg-amber-50">
                                                            <span class="min-w-0">
                                                                <span class="font-mono font-semibold text-slate-900" x-text="c.numero_documento"></span>
                                                                <span class="block text-slate-500" :class="c.vencida ? 'text-danger-text font-semibold' : ''">
                                                                    <span x-text="c.fecha_emision"></span> · <span x-text="c.dias_antiguedad + (c.dias_antiguedad === 1 ? ' día' : ' días')"></span>
                                                                    <template x-if="c.fecha_vencimiento"><span> · acordado <span x-text="c.fecha_vencimiento"></span></span></template>
                                                                </span>
                                                            </span>
                                                            <span class="font-mono font-bold tabular-nums text-slate-900 flex-shrink-0">$<span x-text="formatCurrency(c.saldo_pendiente)"></span></span>
                                                        </a>
                                                    </template>
                                                    <p x-show="cartera.cuentas.length === 0" class="px-2.5 py-2 text-[11px] text-slate-500">Sin deudas pendientes.</p>
                                                </div>
                                            </div>

                                            <div>
                                                <div class="text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">Últimos pagos y abonos</div>
                                                <div class="max-h-32 overflow-y-auto divide-y divide-amber-100 bg-white rounded-md border border-amber-200">
                                                    <template x-for="a in cartera.abonos" :key="a.id">
                                                        <a :href="a.url" target="_blank" class="flex items-center justify-between gap-2 px-2.5 py-1.5 text-[11px] hover:bg-amber-50" :class="a.anulado ? 'opacity-60 line-through' : ''">
                                                            <span class="min-w-0">
                                                                <span class="font-mono font-semibold text-slate-900" x-text="a.numero_recibo"></span>
                                                                <span class="block text-slate-500"><span x-text="a.fecha_pago"></span> · <span x-text="a.metodo_pago"></span><span x-show="a.documento"> · <span x-text="a.documento"></span></span></span>
                                                            </span>
                                                            <span class="font-mono font-bold tabular-nums text-emerald-700 flex-shrink-0">$<span x-text="formatCurrency(a.monto)"></span></span>
                                                        </a>
                                                    </template>
                                                    <p x-show="cartera.abonos.length === 0" class="px-2.5 py-2 text-[11px] text-slate-500">Sin abonos registrados.</p>
                                                </div>
                                            </div>

                                            <a :href="cartera.estado_cuenta_url" target="_blank"
                                               class="w-full inline-flex items-center justify-center gap-1.5 py-2 text-xs font-semibold rounded-lg bg-white border border-amber-300 text-amber-900 hover:bg-amber-100 transition">
                                                Cobrar abono / Ver estado de cuenta en Cartera
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                            </a>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Tabla de Items en Carrito -->
                <div class="border border-slate-200 rounded-lg overflow-hidden">
                    <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex items-center justify-between text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <span>Detalle de Venta (<span x-text="cart.length"></span>)</span>
                        <button @click="clearCart()" x-show="cart.length > 0" class="text-danger-text hover:underline text-[11px] font-semibold">
                            Vaciar (F8)
                        </button>
                    </div>

                    <div class="max-h-[320px] overflow-y-auto divide-y divide-slate-100">
                        <template x-for="(item, idx) in cart" :key="item.id">
                            <div class="p-3 hover:bg-slate-50/70 transition flex items-center justify-between gap-2.5">
                                <!-- Mini Thumbnail -->
                                <div class="h-10 w-10 rounded-md bg-slate-100 border border-slate-200/80 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    <template x-if="item.imagen_url">
                                        <img :src="item.imagen_url" :alt="item.nombre" class="h-full w-full object-cover">
                                    </template>
                                    <template x-if="!item.imagen_url">
                                        <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </template>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h5 class="text-xs font-semibold text-slate-900 truncate" x-text="item.nombre"></h5>
                                    <div class="text-[11px] text-slate-500 tabular-nums">
                                        $<span x-text="formatCurrency(item.precio)"></span> x <span x-text="item.unidad"></span>
                                        <template x-if="item.impuesto_porcentaje > 0">
                                            <span class="text-slate-400 font-mono">(IVA <span x-text="item.impuesto_porcentaje"></span>%)</span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Cantidad Controls -->
                                <div class="flex items-center gap-1">
                                    <button @click="updateQty(idx, -1)" class="h-7 w-7 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs">
                                        -
                                    </button>
                                    <input type="number" step="1" min="1" x-model.number="item.cantidad" @change="if(item.cantidad < 1) item.cantidad = 1"
                                           class="w-12 text-center text-xs font-semibold font-mono tabular-nums py-1 px-1 border border-slate-200 rounded">
                                    <button @click="updateQty(idx, 1)" class="h-7 w-7 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs">
                                        +
                                    </button>
                                </div>

                                <!-- Subtotal Line -->
                                <div class="text-right w-20">
                                    <div class="text-xs font-bold font-mono tabular-nums text-slate-900">
                                        $<span x-text="formatCurrency(item.cantidad * item.precio)"></span>
                                    </div>
                                    <button @click="removeItem(idx)" class="text-[10px] text-danger-text hover:underline">
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
                <div class="bg-slate-50 rounded-lg p-4 border border-slate-200 space-y-2">
                    <div class="flex justify-between text-xs text-slate-600 font-medium">
                        <span>Subtotal:</span>
                        <span class="font-bold font-mono tabular-nums text-slate-800">$<span x-text="formatCurrency(subtotal)"></span></span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-600 font-medium">
                        <span>Impuestos (IVA):</span>
                        <span class="font-bold font-mono tabular-nums text-slate-800">$<span x-text="formatCurrency(impuestosTotal)"></span></span>
                    </div>
                    <template x-if="descuentoTotal > 0">
                        <div class="flex justify-between text-xs text-emerald-700 font-medium">
                            <span>Descuento:</span>
                            <span class="font-bold font-mono tabular-nums">-$<span x-text="formatCurrency(descuentoTotal)"></span></span>
                        </div>
                    </template>
                    <div class="border-t border-slate-200 pt-3 flex justify-between items-baseline">
                        <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Total a Pagar:</span>
                        <span class="text-3xl font-bold font-mono tabular-nums text-slate-900">
                            $<span x-text="formatCurrency(total)"></span>
                        </span>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                    <button type="button"
                            @click="clearCart()"
                            :disabled="cart.length === 0"
                            class="btn-secondary text-xs justify-center h-12 sm:h-14 disabled:opacity-50">
                        Cancelar (F8)
                    </button>
                    <button type="button"
                            @click="openPaymentModal()"
                            :disabled="cart.length === 0"
                            class="sm:col-span-2 bg-accent-pos hover:bg-accent-pos-hover text-white h-12 sm:h-14 text-base font-semibold rounded-lg inline-flex items-center justify-center transition shadow-xs disabled:opacity-50">
                        <span>COBRAR (F4)</span>
                        <svg class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                </div>

            </div>
        </div>

    </div>

    <!-- MODAL DE COBRO Y MÉTODOS DE PAGO (FASE 12) -->
    <div x-cloak x-show="modalPago" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 p-6">
            <div x-show="modalPago" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" @click="modalPago = false"></div>
            
            <div x-show="modalPago"
                 class="relative bg-white rounded-xl max-w-2xl w-full p-6 sm:p-8 shadow-xl border border-slate-200 z-10 space-y-5">
                
                <!-- Encabezado Modal -->
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <div>
                        <span class="text-[10px] font-bold text-brand-600 uppercase tracking-wider block">Finalizar Venta</span>
                        <h3 class="text-xl font-bold font-mono tabular-nums text-slate-900">Total a Cobrar: $<span x-text="formatCurrency(total)"></span></h3>
                    </div>
                    <button @click="modalPago = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <!-- Opciones de Factura & Tipo de Pago -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label-base">Comprobante</label>
                        <select x-model="tipoComprobante" class="input text-xs">
                            <option value="TICKET">Ticket POS</option>
                            <option value="FACTURA">Factura Comercial</option>
                            <option value="NOTA_VENTA">Nota de Venta</option>
                        </select>
                    </div>
                    <div>
                        <label class="label-base">Condición</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="cambiarTipoVenta('CONTADO')"
                                    :class="tipoPago === 'CONTADO' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'bg-slate-100 text-slate-700 font-medium'"
                                    class="py-2 text-xs rounded-lg transition">
                                Contado
                            </button>
                            <button type="button" @click="selectCredito()"
                                    :class="tipoPago === 'CREDITO' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'bg-slate-100 text-slate-700 font-medium'"
                                    class="py-2 text-xs rounded-lg transition">
                                Crédito
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Selector de Método de Pago (Cuando es Contado) -->
                <div x-show="tipoPago === 'CONTADO'" class="space-y-4">
                    <label class="label-base">Método de Pago</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <button type="button" @click="metodoPago = 'EFECTIVO'; autoCalcularEfectivo()"
                                :class="metodoPago === 'EFECTIVO' ? 'border-brand-600 bg-brand-50/40 text-brand-800 font-semibold ring-1 ring-brand-500' : 'border-slate-200 text-slate-700 hover:border-slate-300'"
                                class="p-3 rounded-lg border text-xs flex flex-col items-center justify-center space-y-1 transition">
                            <span>Efectivo</span>
                        </button>
                        <button type="button" @click="metodoPago = 'TARJETA'; pagoCon = total"
                                :class="metodoPago === 'TARJETA' ? 'border-brand-600 bg-brand-50/40 text-brand-800 font-semibold ring-1 ring-brand-500' : 'border-slate-200 text-slate-700 hover:border-slate-300'"
                                class="p-3 rounded-lg border text-xs flex flex-col items-center justify-center space-y-1 transition">
                            <span>Tarjeta</span>
                        </button>
                        <button type="button" @click="metodoPago = 'TRANSFERENCIA'; pagoCon = total"
                                :class="metodoPago === 'TRANSFERENCIA' ? 'border-brand-600 bg-brand-50/40 text-brand-800 font-semibold ring-1 ring-brand-500' : 'border-slate-200 text-slate-700 hover:border-slate-300'"
                                class="p-3 rounded-lg border text-xs flex flex-col items-center justify-center space-y-1 transition">
                            <span>Transferencia</span>
                        </button>
                        <button type="button" @click="metodoPago = 'MIXTO'; prepararPagoMixto()"
                                :class="metodoPago === 'MIXTO' ? 'border-brand-600 bg-brand-50/40 text-brand-800 font-semibold ring-1 ring-brand-500' : 'border-slate-200 text-slate-700 hover:border-slate-300'"
                                class="p-3 rounded-lg border text-xs flex flex-col items-center justify-center space-y-1 transition">
                            <span>Pago Mixto</span>
                        </button>
                    </div>

                    <!-- Panel de Efectivo: Pago con y Vueltos -->
                    <div x-show="metodoPago === 'EFECTIVO'" class="bg-slate-50 p-4 rounded-lg border border-slate-200 space-y-3">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label-base">Paga con *</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-slate-400">$</span>
                                    <input type="number" step="100" min="0" x-model.number="pagoCon"
                                           x-ref="pagoConInput"
                                           class="input pl-7 pr-3 py-2 text-lg font-bold font-mono tabular-nums text-slate-900">
                                </div>
                            </div>
                            <div>
                                <label class="label-base">Cambio / Vueltos</label>
                                <div :class="cambio >= 0 ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-danger-text bg-danger-bg border-danger-border'"
                                     class="px-3 py-2 text-xl font-bold font-mono tabular-nums rounded-lg border">
                                    $<span x-text="formatCurrency(Math.max(0, cambio))"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Billetes Rápidos -->
                        <div class="pt-2">
                            <span class="block text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Billetes sugeridos:</span>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="pagoCon = total" class="px-2.5 py-1 rounded bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Exacto ($<span x-text="formatCurrency(total)"></span>)
                                </button>
                                <button type="button" @click="pagoCon = 10000" x-show="total <= 10000" class="px-2.5 py-1 rounded bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50">$10.000</button>
                                <button type="button" @click="pagoCon = 20000" x-show="total <= 20000" class="px-2.5 py-1 rounded bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50">$20.000</button>
                                <button type="button" @click="pagoCon = 50000" x-show="total <= 50000" class="px-2.5 py-1 rounded bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50">$50.000</button>
                                <button type="button" @click="pagoCon = 100000" class="px-2.5 py-1 rounded bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50">$100.000</button>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de Pago Mixto (Fase 12) -->
                    <div x-show="metodoPago === 'MIXTO'" class="bg-slate-50 p-4 rounded-lg border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="label-base">Detalle de pagos combinados</span>
                            <button type="button" @click="agregarFilaMixta()" class="text-xs font-semibold text-brand-600 hover:text-brand-700">
                                + Agregar método
                            </button>
                        </div>
                        
                        <div class="space-y-2">
                            <template x-for="(pagoItem, pIdx) in pagosMixtos" :key="pIdx">
                                <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200">
                                    <select x-model="pagoItem.metodo_pago" class="w-1/3 select-compact">
                                        <option value="EFECTIVO">Efectivo</option>
                                        <option value="TARJETA">Tarjeta</option>
                                        <option value="TRANSFERENCIA">Transferencia</option>
                                        <option value="OTRO">Otro</option>
                                    </select>
                                    <div class="relative flex-1">
                                        <span class="absolute inset-y-0 left-0 pl-2 flex items-center font-bold text-slate-400 text-xs">$</span>
                                        <input type="number" step="0.01" min="0" x-model.number="pagoItem.monto"
                                               class="w-full pl-5 input-compact font-mono">
                                    </div>
                                    <input type="text" x-model="pagoItem.referencia" placeholder="Ref/voucher"
                                           class="w-28 input-compact">
                                    <button type="button" @click="quitarFilaMixta(pIdx)" class="text-danger-text hover:underline px-1 font-bold text-xs">
                                        ✕
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div class="flex justify-between items-center text-xs pt-1">
                            <span class="font-semibold text-slate-700 font-mono tabular-nums">Suma ingresada: $<span x-text="formatCurrency(sumaPagosMixtos)"></span></span>
                            <span :class="sumaPagosMixtos >= total ? 'text-emerald-700 font-semibold' : 'text-danger-text font-semibold'">
                                <span x-text="sumaPagosMixtos >= total ? '¡Monto cubierto!' : 'Faltan $' + formatCurrency(total - sumaPagosMixtos)"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Panel Crédito Comercial -->
                <div x-show="tipoPago === 'CREDITO'" class="bg-amber-50 p-4 rounded-lg border border-amber-200 space-y-1.5">
                    <h5 class="text-xs font-bold text-amber-900 uppercase tracking-wider">Venta a Crédito</h5>
                    <p class="text-xs text-amber-800 leading-relaxed">
                        Se generará una cuenta por cobrar en el módulo de Cartera para el cliente
                        <strong x-text="selectedCliente ? selectedCliente.razon_social : ''"></strong>.
                    </p>
                    <div class="text-xs font-semibold text-amber-900 pt-1">
                        <span x-show="selectedCliente && selectedCliente.plazo_dias > 0">Plazo acordado con el cliente: <span x-text="selectedCliente?.plazo_dias"></span> días.</span>
                        <span x-show="!selectedCliente || !(selectedCliente.plazo_dias > 0)">Sin plazo acordado: la deuda queda abierta hasta que el cliente abone.</span>
                    </div>
                </div>

                <!-- Observaciones -->
                <div>
                    <label class="label-base">Observaciones (Opcional)</label>
                    <input type="text" x-model="observaciones" placeholder="Ej: Pago con billete de $100.000 / Cliente frecuente"
                           class="input text-xs">
                </div>

                <!-- Error alert in modal -->
                <div x-show="errorMsg" class="p-3 bg-danger-bg border border-danger-border text-danger-text rounded-lg text-xs font-semibold flex items-center gap-2">
                    <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span x-text="errorMsg"></span>
                </div>

                <!-- Footer Modal -->
                <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200">
                    <button type="button" @click="modalPago = false" class="btn-secondary text-xs">
                        Regresar
                    </button>
                    <button type="button"
                            @click="enviarVenta()"
                            :disabled="loading || (tipoPago === 'CONTADO' && metodoPago === 'EFECTIVO' && cambio < 0) || (tipoPago === 'CONTADO' && metodoPago === 'MIXTO' && sumaPagosMixtos < total)"
                            class="bg-accent-pos hover:bg-accent-pos-hover text-white font-semibold rounded-lg inline-flex items-center justify-center transition text-xs sm:text-sm py-2.5 px-5 disabled:opacity-50">
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
            <div x-show="modalExito" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
            
            <div x-show="modalExito"
                 class="relative bg-white rounded-xl max-w-md w-full p-6 sm:p-8 shadow-xl border border-slate-200 z-10 text-center space-y-5">
                
                <div class="h-14 w-14 bg-emerald-50 text-emerald-600 border border-emerald-200 rounded-xl mx-auto flex items-center justify-center">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <div>
                    <h3 class="text-xl font-bold text-slate-900 tracking-tight">¡Venta Exitosa!</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Comprobante <strong class="font-mono text-slate-900" x-text="ventaExitosa.numero_venta"></strong> generado correctamente.
                    </p>
                </div>

                <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 space-y-2 text-left">
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-500">Total Venta:</span>
                        <span class="font-bold font-mono tabular-nums text-slate-900">$<span x-text="formatCurrency(ventaExitosa.total)"></span></span>
                    </div>
                    <template x-if="ventaExitosa.cambio > 0">
                        <div class="flex justify-between text-xs pt-1 border-t border-slate-200">
                            <span class="text-emerald-700 font-semibold">Cambio a Entregar:</span>
                            <span class="font-bold font-mono tabular-nums text-emerald-700 text-sm">$<span x-text="formatCurrency(ventaExitosa.cambio)"></span></span>
                        </div>
                    </template>
                </div>

                <div class="space-y-2 pt-2">
                    <a :href="ventaExitosa.ticket_url" target="_blank"
                       class="btn-primary w-full py-2.5 justify-center text-xs">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                        <span>Imprimir Ticket</span>
                    </a>
                    <button type="button" @click="nuevaVenta()"
                            class="btn-secondary w-full py-2.5 justify-center text-xs">
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
        carteraUrlTemplate: config.carteraUrlTemplate,
        puedeVerCartera: config.puedeVerCartera,

        cartera: null,
        carteraCargando: false,
        carteraError: '',

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
            // Al cambiar de cliente, re-tarifar el carrito con su lista de precios
            this.$watch('selectedClienteId', () => {
                this.cart.forEach(item => { item.precio = this.precioPara(item); });
                if (this.tipoPago === 'CREDITO') this.cargarCartera();
            });
            this.focusSearch();
        },

        // Precio según la lista asignada al cliente seleccionado (el servidor aplica la misma regla)
        precioPara(product) {
            const listaId = this.selectedCliente?.lista_precio_id;
            const precioLista = listaId ? product.precios_listas?.[listaId] : undefined;
            return parseFloat(precioLista ?? product.precio_venta);
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
                    precio: this.precioPara(product),
                    precio_venta: product.precio_venta,
                    precios_listas: product.precios_listas,
                    cantidad: 1,
                    impuesto_porcentaje: parseFloat(product.iva_porcentaje || 0),
                    descuento: 0,
                    stock: product.stock,
                    unidad: product.unidad,
                    imagen_url: product.imagen_url || null
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

        // Selector principal Contado / Crédito del POS
        cambiarTipoVenta(tipo) {
            if (tipo === 'CREDITO') {
                this.tipoPago = 'CREDITO';
                this.metodoPago = 'CREDITO';
                this.cargarCartera();
            } else {
                this.tipoPago = 'CONTADO';
                this.metodoPago = 'EFECTIVO';
                this.autoCalcularEfectivo();
            }
        },

        async cargarCartera() {
            this.cartera = null;
            this.carteraError = '';
            const cli = this.selectedCliente;
            if (!this.puedeVerCartera || !cli || cli.es_consumidor_final) return;

            this.carteraCargando = true;
            try {
                const response = await fetch(this.carteraUrlTemplate.replace('__CLIENTE__', cli.id), {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.error || 'No se pudo cargar la cartera del cliente.');
                // Evita pintar la respuesta de un cliente anterior si el usuario cambió de selección
                if (data.cliente_id != this.selectedClienteId) return;
                this.cartera = data;
            } catch (err) {
                this.carteraError = err.message;
            } finally {
                this.carteraCargando = false;
            }
        },

        openPaymentModal() {
            if (this.cart.length === 0) return;
            this.errorMsg = '';
            if (this.tipoPago === 'CREDITO') {
                if (!this.puedeFiarCliente()) return;
                this.metodoPago = 'CREDITO';
                this.modalPago = true;
                return;
            }
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

        // Cualquier cliente identificado puede comprar a crédito; el consumidor final genérico no
        puedeFiarCliente() {
            const cli = this.selectedCliente;
            if (!cli || cli.es_consumidor_final) {
                alert('No se puede vender a crédito al Consumidor Final. Selecciona el cliente al que se le fía la venta.');
                return false;
            }
            return true;
        },

        selectCredito() {
            if (!this.puedeFiarCliente()) return;
            this.cambiarTipoVenta('CREDITO');
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

                this.aplicarStocks(data.stocks);

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

        // Refresca las existencias del catálogo con las devueltas por el servidor tras la venta
        aplicarStocks(stocks) {
            if (!stocks) return;
            this.allProducts.forEach(p => {
                p.stock = parseFloat(stocks[p.id] ?? 0);
            });
        },

        nuevaVenta() {
            this.modalExito = false;
            this.tipoPago = 'CONTADO';
            this.metodoPago = 'EFECTIVO';
            this.pagoCon = 0;
            this.pagosMixtos = [];
            this.cartera = null;
            this.carteraError = '';
            const defaultCli = this.clientes.find(c => c.es_consumidor_final) || this.clientes[0];
            if (defaultCli) {
                this.selectedClienteId = defaultCli.id;
            }
            this.focusSearch();
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('es-CO', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(amount || 0);
        }
    };
}
</script>
@endsection
