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
                                    <span x-text="formatoCantidad(p.stock)"></span> <span x-text="p.unidad"></span>
                                </span>

                                <!-- Ya está en la cotización (puede tener varias líneas: cajas y m²) -->
                                <template x-if="cantidadEnCotizacion(p.id) > 0">
                                    <span class="absolute bottom-1.5 right-1.5 text-[10px] font-bold bg-brand-600 text-white px-1.5 py-0.5 rounded shadow-xs">
                                        En cotización
                                    </span>
                                </template>
                            </div>

                            <div class="mb-2">
                                <h4 class="font-semibold text-slate-900 text-xs sm:text-sm line-clamp-2 leading-snug group-hover:text-brand-600 transition" x-text="p.nombre"></h4>
                                <div class="text-[11px] font-mono text-slate-400 mt-0.5 truncate" x-text="p.codigo_barras || p.sku || p.codigo"></div>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-slate-100 mt-auto">
                                <span class="text-sm sm:text-base font-bold font-mono tabular-nums text-slate-900">
                                    $<span x-text="formato(precioGrilla(p).precio)"></span><span class="text-[10px] font-semibold text-slate-400" x-text="precioGrilla(p).etiqueta"></span>
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
                        <template x-for="(item, idx) in items" :key="item.key">
                            <div class="p-3 space-y-2">
                                <input type="hidden" :name="`items[${idx}][producto_id]`" :value="item.id">
                                <input type="hidden" :name="`items[${idx}][cantidad]`" :value="item.cantidad">
                                <input type="hidden" :name="`items[${idx}][descuento]`" :value="item.descuento || 0">
                                <template x-if="item.presentacion_id">
                                    <input type="hidden" :name="`items[${idx}][presentacion_id]`" :value="item.presentacion_id">
                                </template>

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
                                            $<span x-text="formato(precioPara(item))"></span> x <span class="font-semibold text-slate-700" x-text="item.unidad_texto"></span> · IVA <span x-text="item.iva_porcentaje"></span>%
                                            <template x-if="item.presentacion_id">
                                                <span class="block text-slate-400">= <span x-text="formatoCantidad(item.cantidad * item.factor)"></span> <span x-text="item.unidad"></span></span>
                                            </template>
                                        </div>
                                    </div>

                                    <div class="flex items-center border border-slate-200 rounded-md bg-white flex-shrink-0">
                                        <button type="button" @click="cambiarCantidad(idx, -1)" class="px-2 py-1 text-slate-500 hover:text-slate-900 hover:bg-slate-50 font-bold">−</button>
                                        <input type="number" :step="item.permite_decimales ? 0.01 : 1" :min="item.permite_decimales ? 0.01 : 1" x-model.number="item.cantidad" @change="normalizarCantidad(item)" class="w-14 text-center text-xs font-mono font-semibold border-0 focus:ring-0 p-0">
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

    <!-- Selector de presentación (venta por medida: m² o caja) -->
    <div x-cloak x-show="selector.abierto" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" @keydown.escape.window="selector.abierto = false">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" @click="selector.abierto = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl border border-slate-200 w-full max-w-md p-5 space-y-4" x-show="selector.producto">
            <div class="pb-3 border-b border-slate-100">
                <div class="text-[10px] font-bold text-brand-600 uppercase tracking-wider">¿En qué unidad se cotiza?</div>
                <h3 class="text-sm font-bold text-slate-900" x-text="selector.producto?.nombre"></h3>
            </div>
            <div class="space-y-2">
                <template x-for="pr in (selector.producto?.presentaciones || [])" :key="pr.id">
                    <button type="button" @click="elegirOpcion(pr.id)"
                            :class="selector.opcion === pr.id ? 'border-brand-600 ring-1 ring-brand-500 bg-brand-50/40' : 'border-slate-200 hover:border-slate-300'"
                            class="w-full text-left p-3 rounded-lg border flex items-center justify-between gap-3 transition">
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">Por <span x-text="pr.nombre.toLowerCase()"></span></span>
                            <span class="block text-[11px] text-slate-500">1 <span x-text="pr.nombre"></span> = <span x-text="formatoCantidad(pr.factor)"></span> <span x-text="selector.producto?.unidad"></span></span>
                        </span>
                        <span class="text-sm font-bold font-mono tabular-nums">$<span x-text="formato(pr.precio)"></span></span>
                    </button>
                </template>
                <button type="button" @click="precioBase(selector.producto) > 0 && elegirOpcion('base')" :disabled="!(precioBase(selector.producto) > 0)"
                        :class="selector.opcion === 'base' ? 'border-brand-600 ring-1 ring-brand-500 bg-brand-50/40' : 'border-slate-200 hover:border-slate-300'"
                        class="w-full text-left p-3 rounded-lg border flex items-center justify-between gap-3 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span>
                        <span class="block text-sm font-semibold text-slate-900">Por <span x-text="selector.producto?.unidad"></span> (fracciones)</span>
                        <span class="block text-[11px]" :class="precioBase(selector.producto) > 0 ? 'text-slate-500' : 'text-danger-text font-semibold'"
                              x-text="precioBase(selector.producto) > 0 ? 'Ej. 2,35 ' + (selector.producto?.unidad || '') : 'Sin precio por ' + (selector.producto?.unidad || '')"></span>
                    </span>
                    <span class="text-sm font-bold font-mono tabular-nums" x-show="precioBase(selector.producto) > 0">$<span x-text="formato(precioBase(selector.producto))"></span></span>
                </button>
            </div>
            <div>
                <label class="label-base">Cantidad (<span x-text="unidadSelector"></span>)</label>
                <input type="number" x-ref="selectorCantidad" x-model.number="selector.cantidad" @keydown.enter.prevent="confirmarSelector()"
                       :step="selectorPermiteDecimales ? 0.01 : 1" :min="selectorPermiteDecimales ? 0.01 : 1" class="input text-lg font-bold font-mono tabular-nums">
                <p class="text-[11px] text-slate-500 mt-1 font-mono tabular-nums" x-text="equivalenciaSelector"></p>
            </div>
            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="selector.abierto = false" class="btn-secondary text-xs">Cancelar</button>
                <button type="button" @click="confirmarSelector()" :disabled="!(selector.cantidad > 0)" class="btn-primary text-xs disabled:opacity-50">Agregar</button>
            </div>
        </div>
    </div>
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
        selector: { abierto: false, producto: null, opcion: null, cantidad: 1 },

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
            // La presentación (ej. caja) tiene precio propio; las listas solo aplican a la unidad base
            if (p.presentacion_id) return parseFloat(p.precio_presentacion);
            const listaId = this.cliente?.lista_precio_id;
            const precioLista = listaId ? p.precios_listas?.[listaId] : undefined;
            return parseFloat(precioLista ?? p.precio_venta);
        },
        cantidadEnCotizacion(id) {
            return this.items.filter(i => i.id === id).length;
        },
        agregar(p) {
            if (p.presentaciones && p.presentaciones.length) {
                const pr = p.presentaciones.find(x => x.predeterminada) || p.presentaciones[0];
                this.selector = { abierto: true, producto: p, opcion: pr.id, cantidad: 1 };
                this.$nextTick(() => { this.$refs.selectorCantidad?.focus(); this.$refs.selectorCantidad?.select(); });
                return;
            }
            this.agregarLinea(p, null, 1);
        },
        agregarLinea(p, pr, cantidad) {
            const key = p.id + '-' + (pr ? pr.id : 0);
            const existente = this.items.find(i => i.key === key);
            if (existente) {
                existente.cantidad = this.redondear(existente.cantidad + cantidad);
                return;
            }
            this.items.push({
                ...p,
                key,
                presentacion_id: pr ? pr.id : null,
                precio_presentacion: pr ? pr.precio : null,
                factor: pr ? pr.factor : 1,
                unidad_texto: pr ? pr.nombre + ' (' + this.formatoCantidad(pr.factor) + ' ' + p.unidad + ')' : p.unidad,
                permite_decimales: pr ? !!pr.permite_decimales : !!p.permite_decimales,
                cantidad,
                descuento: 0,
            });
        },
        precioBase(p) {
            return p ? this.precioPara({ ...p, presentacion_id: null }) : 0;
        },
        precioGrilla(p) {
            const base = this.precioBase(p);
            if (base > 0 || !(p.presentaciones && p.presentaciones.length)) {
                return { precio: base, etiqueta: p.presentaciones?.length ? '/' + p.unidad : '' };
            }
            const pr = p.presentaciones.find(x => x.predeterminada) || p.presentaciones[0];
            return { precio: pr.precio, etiqueta: '/' + pr.nombre };
        },
        elegirOpcion(opcion) {
            this.selector.opcion = opcion;
            if (!this.selectorPermiteDecimales) this.selector.cantidad = Math.max(1, Math.round(this.selector.cantidad || 1));
        },
        get presentacionSelector() {
            return (this.selector.producto?.presentaciones || []).find(x => x.id === this.selector.opcion) || null;
        },
        get selectorPermiteDecimales() {
            return this.presentacionSelector ? !!this.presentacionSelector.permite_decimales : !!this.selector.producto?.permite_decimales;
        },
        get unidadSelector() {
            return this.presentacionSelector ? this.presentacionSelector.nombre : (this.selector.producto?.unidad || '');
        },
        get equivalenciaSelector() {
            const prod = this.selector.producto;
            const q = parseFloat(this.selector.cantidad) || 0;
            if (!prod || q <= 0) return '';
            const pr = this.presentacionSelector;
            if (pr) return '= ' + this.formatoCantidad(q * pr.factor) + ' ' + prod.unidad;
            const ref = prod.presentaciones.find(x => x.predeterminada) || prod.presentaciones[0];
            const completas = Math.floor((q + 1e-9) / ref.factor);
            const resto = this.redondear(q - completas * ref.factor);
            return '= ' + completas + ' ' + ref.nombre + (completas === 1 ? '' : 's') + (resto > 0 ? ' + ' + this.formatoCantidad(resto) + ' ' + prod.unidad : '');
        },
        confirmarSelector() {
            let q = parseFloat(this.selector.cantidad) || 0;
            if (!this.selectorPermiteDecimales) q = Math.round(q);
            if (q <= 0) return;
            this.agregarLinea(this.selector.producto, this.presentacionSelector, this.redondear(q));
            this.selector.abierto = false;
            this.focusSearch();
        },
        cambiarCantidad(idx, delta) {
            const item = this.items[idx];
            const nueva = this.redondear((item.cantidad || 0) + delta);
            if (nueva <= 0) {
                this.items.splice(idx, 1);
            } else {
                item.cantidad = nueva;
            }
        },
        normalizarCantidad(item) {
            let q = parseFloat(item.cantidad) || 0;
            if (!item.permite_decimales) q = Math.round(q);
            item.cantidad = q > 0 ? this.redondear(q) : (item.permite_decimales ? 0.01 : 1);
        },
        redondear(q) {
            return Math.round(q * 10000) / 10000;
        },
        formatoCantidad(q) {
            return new Intl.NumberFormat('es-CO', { maximumFractionDigits: 4 }).format(q || 0);
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
