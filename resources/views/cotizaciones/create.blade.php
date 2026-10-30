@extends('layouts.app')

@section('title', 'Nueva Cotización')

@section('content')
<div class="max-w-[1720px] mx-auto space-y-4 pb-10" x-data="cotizacionForm({
    productos: {{ Js::from($productos) }},
    clientes: {{ Js::from($clientes) }},
    clienteId: @js(old('cliente_id')),
})" x-init="focusSearch()">

    <x-page-header
        title="Nueva Cotización"
        description="Los precios e IVA se toman del catálogo y de la lista de precios del cliente. No afecta el inventario."
        :breadcrumbs="[
            ['label' => 'Cotizaciones', 'url' => route('cotizaciones.index')],
            ['label' => 'Nueva']
        ]">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('cotizaciones.index') }}">Cancelar</x-button>
        </x-slot:actions>
    </x-page-header>


    <form action="{{ route('cotizaciones.store') }}" method="POST" class="grid grid-cols-1 lg:grid-cols-12 gap-5"
          @submit="if (items.length === 0) { $event.preventDefault(); alert('Agrega al menos un producto.'); }">
        @csrf

        <!-- Columna Izquierda: Búsqueda y Catálogo (igual que el POS) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="card p-4">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" x-ref="searchInput" x-model="busqueda" @keydown.enter.prevent="onSearchEnter()"
                           placeholder="Escanear código de barras o buscar por nombre / SKU..."
                           class="input h-11 pl-11 pr-10 text-sm">
                    <template x-if="busqueda">
                        <button type="button" @click="busqueda = ''; focusSearch()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </template>
                </div>

                <!-- Filtro por Categorías -->
                <div class="flex items-center gap-1.5 overflow-x-auto pt-3 mt-3 border-t border-slate-100 no-scrollbar">
                    <button type="button" @click="categoria = null"
                            :class="categoria === null ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition">
                        Todos
                    </button>
                    @foreach($categorias as $cat)
                        <button type="button" @click="categoria = {{ $cat->id }}"
                                :class="categoria === {{ $cat->id }} ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
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
                        Productos (<span x-text="productosFiltrados.length"></span>)
                    </span>
                    <span class="text-xs text-slate-400">Click para agregar a la cotización</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 max-h-[580px] overflow-y-auto pr-1">
                    <template x-for="p in productosFiltrados" :key="p.id">
                        <div @click="agregar(p)"
                             :class="p.stock <= 0 ? 'border-slate-200 bg-slate-50/60' : 'border-slate-200 hover:border-brand-400 hover:bg-brand-50/20 bg-white'"
                             class="group p-3 rounded-lg border transition-all duration-150 cursor-pointer flex flex-col justify-between relative overflow-hidden">

                            <div class="relative w-full h-24 mb-2 rounded-md overflow-hidden bg-surface-muted border border-slate-200/60 flex items-center justify-center flex-shrink-0">
                                <template x-if="p.imagen_url">
                                    <img :src="p.imagen_url" :alt="p.nombre" loading="lazy"
                                         class="h-full w-full object-cover object-center group-hover:scale-105 transition-transform duration-200">
                                </template>
                                <template x-if="!p.imagen_url">
                                    <div class="h-full w-full flex items-center justify-center bg-surface-muted text-slate-400">
                                        <svg class="h-8 w-8 opacity-75" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                </template>

                                <span class="absolute top-1.5 left-1.5 text-[9px] font-semibold text-slate-700 bg-white/95 border border-slate-200/80 px-1.5 py-0.5 rounded shadow-xs uppercase truncate max-w-[85px]"
                                      x-text="p.categoria_nombre"></span>

                                <span :class="p.stock > 5 ? 'badge-success' : (p.stock > 0 ? 'badge-warning' : 'badge-danger')"
                                      class="badge absolute top-1.5 right-1.5 text-[10px] font-bold font-mono px-1.5 py-0.5 shadow-xs">
                                    <span x-text="p.stock"></span> <span x-text="p.unidad"></span>
                                </span>

                                <!-- Cantidad ya cotizada -->
                                <template x-if="cantidadEnCotizacion(p.id) > 0">
                                    <span class="absolute bottom-1.5 right-1.5 text-[10px] font-bold bg-brand-600 text-white px-1.5 py-0.5 rounded shadow-xs">
                                        x<span x-text="cantidadEnCotizacion(p.id)"></span>
                                    </span>
                                </template>
                            </div>

                            <div class="mb-2">
                                <h4 class="font-semibold text-slate-900 text-xs sm:text-sm line-clamp-2 leading-snug group-hover:text-brand-600 transition" x-text="p.nombre"></h4>
                                <div class="text-[11px] font-mono text-slate-400 mt-0.5 truncate" x-text="p.codigo_barras || p.sku || p.codigo"></div>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-slate-100 mt-auto">
                                <span class="text-sm sm:text-base font-bold font-mono tabular-nums text-slate-900">
                                    $<span x-text="formato(precioPara(p))"></span>
                                </span>
                                <div class="h-6 w-6 rounded-md bg-slate-100 group-hover:bg-brand-600 group-hover:text-white text-slate-600 flex items-center justify-center transition shadow-xs">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="productosFiltrados.length === 0" class="py-16 text-center text-slate-400 space-y-2">
                    <svg class="h-10 w-10 mx-auto text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <p class="text-xs font-medium text-slate-500">No se encontraron productos coincidentes con tu búsqueda.</p>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Cliente, ítems cotizados y totales -->
        <div class="lg:col-span-5 space-y-4">
            <div class="card p-5 space-y-4">
                <div>
                    <label class="label-base">Cliente</label>
                    <select name="cliente_id" x-model="clienteId" class="input text-xs sm:text-sm font-medium">
                        <template x-for="c in clientes" :key="c.id">
                            <option :value="c.id" x-text="c.razon_social + (c.es_consumidor_final ? ' (Consumidor Final)' : ' - ' + c.numero_documento)" :selected="c.id == clienteId"></option>
                        </template>
                    </select>
                </div>

                <!-- Ítems cotizados -->
                <div class="border border-slate-200 rounded-lg overflow-hidden">
                    <div class="bg-slate-50 px-3 py-2 border-b border-slate-200 flex items-center justify-between text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <span>Detalle de la Cotización (<span x-text="items.length"></span>)</span>
                        <button type="button" @click="items = []" x-show="items.length > 0" class="text-danger-text hover:underline text-[11px] font-semibold">Vaciar</button>
                    </div>

                    <div class="max-h-[360px] overflow-y-auto divide-y divide-slate-100">
                        <template x-for="(item, idx) in items" :key="item.id">
                            <div class="p-3 space-y-2">
                                <input type="hidden" :name="`items[${idx}][producto_id]`" :value="item.id">
                                <input type="hidden" :name="`items[${idx}][cantidad]`" :value="item.cantidad">
                                <input type="hidden" :name="`items[${idx}][descuento]`" :value="item.descuento || 0">

                                <div class="flex items-center justify-between gap-2.5">
                                    <div class="h-10 w-10 rounded-md bg-slate-100 border border-slate-200/80 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                        <template x-if="item.imagen_url">
                                            <img :src="item.imagen_url" :alt="item.nombre" class="h-full w-full object-cover">
                                        </template>
                                        <template x-if="!item.imagen_url">
                                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                        </template>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <h5 class="text-xs font-semibold text-slate-900 truncate" x-text="item.nombre"></h5>
                                        <div class="text-[11px] text-slate-500 font-mono">
                                            $<span x-text="formato(precioPara(item))"></span> · IVA <span x-text="item.iva_porcentaje"></span>%
                                        </div>
                                    </div>

                                    <div class="flex items-center border border-slate-200 rounded-md bg-white flex-shrink-0">
                                        <button type="button" @click="cambiarCantidad(idx, -1)" class="px-2 py-1 text-slate-500 hover:text-slate-900 hover:bg-slate-50 font-bold">−</button>
                                        <input type="number" step="0.01" min="0.01" x-model.number="item.cantidad" class="w-12 text-center text-xs font-mono font-semibold border-0 focus:ring-0 p-0">
                                        <button type="button" @click="cambiarCantidad(idx, 1)" class="px-2 py-1 text-slate-500 hover:text-slate-900 hover:bg-slate-50 font-bold">+</button>
                                    </div>

                                    <div class="w-24 text-right flex-shrink-0">
                                        <div class="text-xs font-bold font-mono tabular-nums text-slate-900">$<span x-text="formato(totalLinea(item))"></span></div>
                                        <button type="button" @click="items.splice(idx, 1)" class="text-[11px] text-danger-text hover:underline">Quitar</button>
                                    </div>
                                </div>

                                <div class="flex items-center justify-end gap-2 text-[11px] text-slate-500">
                                    <span>Descuento $</span>
                                    <input type="number" step="1" min="0" x-model.number="item.descuento" class="input-compact w-24 text-right font-mono">
                                </div>
                            </div>
                        </template>

                        <div x-show="items.length === 0" class="py-10 text-center text-xs text-slate-500">
                            La cotización está vacía.<br>Busca o selecciona productos de la lista.
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="label-base">Válida hasta <span class="text-slate-400 text-[11px] font-normal">(Opcional)</span></label>
                        <input type="date" name="fecha_validez" value="{{ old('fecha_validez', now()->addDays(15)->toDateString()) }}" min="{{ now()->toDateString() }}" class="input text-xs">
                    </div>
                    <div>
                        <label class="label-base">Observaciones <span class="text-slate-400 text-[11px] font-normal">(Opcional)</span></label>
                        <input type="text" name="observaciones" maxlength="500" value="{{ old('observaciones') }}" class="input text-xs" placeholder="Ej: Incluye entrega en obra">
                    </div>
                </div>

                <!-- Totales -->
                <div class="space-y-1.5 text-sm pt-3 border-t border-slate-200">
                    <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span class="font-mono tabular-nums">$<span x-text="formato(subtotal)"></span></span></div>
                    <div class="flex justify-between" x-show="descuentoTotal > 0"><span class="text-slate-500">Descuentos</span><span class="font-mono tabular-nums">-$<span x-text="formato(descuentoTotal)"></span></span></div>
                    <div class="flex justify-between"><span class="text-slate-500">IVA</span><span class="font-mono tabular-nums">$<span x-text="formato(impuestoTotal)"></span></span></div>
                    <div class="flex justify-between pt-2 border-t border-slate-200 text-lg font-bold"><span>Total</span><span class="font-mono tabular-nums">$<span x-text="formato(total)"></span></span></div>
                </div>

                <button type="submit" class="btn-primary w-full py-3" :disabled="items.length === 0">Guardar Cotización</button>
            </div>
        </div>
    </form>
</div>

<script>
function cotizacionForm(config) {
    const defaultCli = config.clientes.find(c => c.es_consumidor_final) || config.clientes[0];
    return {
        productos: config.productos,
        clientes: config.clientes,
        clienteId: config.clienteId ?? (defaultCli ? defaultCli.id : null),
        busqueda: '',
        categoria: null,
        items: [],

        get cliente() {
            return this.clientes.find(c => c.id == this.clienteId);
        },
        get productosFiltrados() {
            let lista = this.productos;
            if (this.categoria !== null) {
                lista = lista.filter(p => p.categoria_id === this.categoria);
            }
            const q = this.busqueda.toLowerCase().trim();
            if (q) {
                lista = lista.filter(p =>
                    p.nombre.toLowerCase().includes(q) ||
                    (p.codigo && p.codigo.toLowerCase().includes(q)) ||
                    (p.sku && p.sku.toLowerCase().includes(q)) ||
                    (p.codigo_barras && p.codigo_barras.toLowerCase().includes(q))
                );
            }
            return lista;
        },
        focusSearch() {
            this.$nextTick(() => this.$refs.searchInput?.focus());
        },
        // Misma regla que el servidor: lista de precios del cliente o precio de catálogo
        precioPara(p) {
            const listaId = this.cliente?.lista_precio_id;
            const precioLista = listaId ? p.precios_listas?.[listaId] : undefined;
            return parseFloat(precioLista ?? p.precio_venta);
        },
        cantidadEnCotizacion(id) {
            return this.items.find(i => i.id === id)?.cantidad || 0;
        },
        agregar(p) {
            const existente = this.items.find(i => i.id === p.id);
            if (existente) {
                existente.cantidad += 1;
            } else {
                this.items.push({ ...p, cantidad: 1, descuento: 0 });
            }
        },
        cambiarCantidad(idx, delta) {
            const item = this.items[idx];
            const nueva = (item.cantidad || 0) + delta;
            if (nueva <= 0) {
                this.items.splice(idx, 1);
            } else {
                item.cantidad = nueva;
            }
        },
        // Enter: código exacto (lector de barras) o único resultado del filtro
        onSearchEnter() {
            const q = this.busqueda.trim().toLowerCase();
            if (!q) return;
            const exacto = this.productos.find(p =>
                (p.codigo_barras && p.codigo_barras.toLowerCase() === q) ||
                (p.sku && p.sku.toLowerCase() === q) ||
                (p.codigo && p.codigo.toLowerCase() === q)
            );
            const elegido = exacto || (this.productosFiltrados.length === 1 ? this.productosFiltrados[0] : null);
            if (elegido) {
                this.agregar(elegido);
                this.busqueda = '';
            }
        },
        baseLinea(i) {
            return Math.max(0, (i.cantidad || 0) * this.precioPara(i) - (i.descuento || 0));
        },
        totalLinea(i) {
            return this.baseLinea(i) * (1 + (i.iva_porcentaje || 0) / 100);
        },
        get subtotal() { return this.items.reduce((a, i) => a + (i.cantidad || 0) * this.precioPara(i), 0); },
        get descuentoTotal() { return this.items.reduce((a, i) => a + (i.descuento || 0), 0); },
        get impuestoTotal() { return this.items.reduce((a, i) => a + this.baseLinea(i) * ((i.iva_porcentaje || 0) / 100), 0); },
        get total() { return this.subtotal - this.descuentoTotal + this.impuestoTotal; },
        formato(v) {
            return new Intl.NumberFormat('es-CO', { maximumFractionDigits: 0 }).format(v || 0);
        }
    };
}
</script>
@endsection
