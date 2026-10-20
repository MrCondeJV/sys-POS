@extends('layouts.app')

@section('title', 'Registrar Nueva Compra')

@section('content')
    <script>
        let compraItemUidCounter = 1;

        function compraForm() {
            return {
                productosCatalog: @json($productos),
                descuento: 0,
                items: [
                    {
                        uid: compraItemUidCounter++,
                        producto_id: '',
                        cantidad: 1,
                        costo_unitario: 0,
                        porcentaje_iva: 19,
                        searchQuery: '',
                        dropdownOpen: false
                    }
                ],

                // Asignar producto a una fila específica
                setLineProduct(index, product) {
                    if (!product) return;

                    // Validar que no esté ya en otra fila (garantizar un solo artículo por línea)
                    const alreadyInOther = this.items.some((it, i) => i !== index && it.producto_id == product.id);
                    if (alreadyInOther) {
                        alert('Este producto ya fue seleccionado en otra fila. Cada línea de compra debe corresponder a un artículo único.');
                        this.items[index].dropdownOpen = false;
                        this.items[index].searchQuery = '';
                        return;
                    }

                    this.items[index].producto_id = product.id;
                    this.items[index].costo_unitario = parseFloat(product.precio_compra) || 0;
                    this.items[index].porcentaje_iva = (product.iva !== null && product.iva !== undefined) ? parseFloat(product.iva) : 19;
                    this.items[index].searchQuery = '';
                    this.items[index].dropdownOpen = false;
                },

                // Limpiar el producto seleccionado para volver a buscar
                clearLineProduct(index) {
                    this.items[index].producto_id = '';
                    this.items[index].costo_unitario = 0;
                    this.items[index].searchQuery = '';
                    this.items[index].dropdownOpen = false;
                },

                // Obtener datos del producto asignado por ID
                getProduct(productId) {
                    return this.productosCatalog.find(p => p.id == productId);
                },

                // Filtrar productos para el buscador de la fila (excluye los ya añadidos en otras filas)
                getFilteredRowProducts(searchQuery, currentIdx) {
                    const q = (searchQuery || '').trim().toLowerCase();
                    return this.productosCatalog.filter(p => {
                        const alreadyInOther = this.items.some((it, i) => i !== currentIdx && it.producto_id == p.id);
                        if (alreadyInOther) {
                            return false;
                        }
                        if (!q) {
                            return true;
                        }
                        const name = (p.nombre || '').toLowerCase();
                        const code = (p.codigo || p.sku || '').toLowerCase();
                        const barcode = (p.codigo_barras || '').toLowerCase();
                        return name.includes(q) || code.includes(q) || barcode.includes(q);
                    }).slice(0, 10);
                },

                // Añadir una nueva fila vacía
                addItem() {
                    this.items.push({
                        uid: compraItemUidCounter++,
                        producto_id: '',
                        cantidad: 1,
                        costo_unitario: 0,
                        porcentaje_iva: 19,
                        searchQuery: '',
                        dropdownOpen: false
                    });
                },

                // Eliminar fila o restablecer si es la única
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    } else {
                        this.items[0].producto_id = '';
                        this.items[0].cantidad = 1;
                        this.items[0].costo_unitario = 0;
                        this.items[0].porcentaje_iva = 19;
                        this.items[0].searchQuery = '';
                        this.items[0].dropdownOpen = false;
                    }
                },

                validateBeforeSubmit(e) {
                    if (this.items.length === 0 || this.items.some(it => !it.producto_id)) {
                        e.preventDefault();
                        alert('Por favor selecciona un artículo para cada línea de compra o elimina las filas vacías antes de registrar la factura.');
                        return false;
                    }
                },

                itemSubtotal(item) {
                    return (parseFloat(item.cantidad) || 0) * (parseFloat(item.costo_unitario) || 0);
                },
                itemIva(item) {
                    return this.itemSubtotal(item) * ((parseFloat(item.porcentaje_iva) || 0) / 100);
                },
                itemTotal(item) {
                    return this.itemSubtotal(item) + this.itemIva(item);
                },
                totalSubtotal() {
                    return this.items.reduce((acc, item) => acc + this.itemSubtotal(item), 0);
                },
                totalIva() {
                    return this.items.reduce((acc, item) => acc + this.itemIva(item), 0);
                },
                grandTotal() {
                    const sub = this.totalSubtotal();
                    const iva = this.totalIva();
                    const desc = parseFloat(this.descuento) || 0;
                    return Math.max(0, sub + iva - desc);
                },
                formatMoney(val) {
                    return '$' + Number(val || 0).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            };
        }
    </script>

    <div class="space-y-6" x-data="compraForm()">
        <!-- Header -->
        <x-page-header
            title="Registrar Factura de Compra"
            subtitle="Ingresa los artículos adquiridos al inventario de la sede y actualiza automáticamente los costos de reposición."
            :breadcrumbs="[
                ['label' => 'Historial de Compras', 'url' => route('compras.index')],
                ['label' => 'Nueva Factura']
            ]">
            <x-slot:actions>
                <a href="{{ route('compras.index') }}" class="btn-secondary">
                    Cancelar
                </a>
            </x-slot:actions>
        </x-page-header>

        <!-- Formulario Principal -->
        <form action="{{ route('compras.store') }}" method="POST" @submit="validateBeforeSubmit($event)" class="space-y-6">
            @csrf

            <!-- 1. Cabecera de la Factura -->
            <div class="card p-5 sm:p-6 space-y-4">
                <div class="border-b border-slate-200 pb-3 flex items-center">
                    <span class="h-5 w-5 rounded bg-brand-50 text-brand-700 font-semibold flex items-center justify-center mr-2.5 text-xs">1</span>
                    <h2 class="text-sm font-semibold text-slate-900">Datos del Comprobante y Proveedor</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Sucursal Receptora -->
                    <div>
                        <label class="label-base">
                            Sede de Recepción <span class="text-danger-text">*</span>
                        </label>
                        <select name="sucursal_id" required class="input">
                            <option value="">Seleccionar Sede</option>
                            @foreach($sucursales as $s)
                                <option value="{{ $s->id }}" {{ (old('sucursal_id', \App\Support\Tenancy\BranchContext::getId()) == $s->id) ? 'selected' : '' }}>
                                    {{ $s->nombre }} ({{ $s->codigo ?? 'S/C' }})
                                </option>
                            @endforeach
                        </select>
                        @error('sucursal_id')
                            <p class="text-xs text-danger-text mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Proveedor Emisor -->
                    <div>
                        <label class="label-base">
                            Proveedor <span class="text-danger-text">*</span>
                        </label>
                        <select name="proveedor_id" required class="input">
                            <option value="">Seleccionar Proveedor</option>
                            @foreach($proveedores as $p)
                                <option value="{{ $p->id }}" {{ old('proveedor_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->razon_social }} ({{ $p->numero_documento }})
                                </option>
                            @endforeach
                        </select>
                        @error('proveedor_id')
                            <p class="text-xs text-danger-text mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Número de Factura -->
                    <div>
                        <label class="label-base">
                            Número de Factura <span class="text-danger-text">*</span>
                        </label>
                        <input type="text" name="numero_factura" value="{{ old('numero_factura') }}" required
                            placeholder="Ej: FE-10492"
                            class="input font-mono">
                        @error('numero_factura')
                            <p class="text-xs text-danger-text mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fecha de Emisión -->
                    <div>
                        <label class="label-base">
                            Fecha de Emisión <span class="text-danger-text">*</span>
                        </label>
                        <input type="date" name="fecha_emision" value="{{ old('fecha_emision', date('Y-m-d')) }}" required
                            max="{{ date('Y-m-d') }}"
                            class="input">
                        @error('fecha_emision')
                            <p class="text-xs text-danger-text mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Forma de Pago -->
                    <div>
                        <label class="label-base">
                            Forma de Pago <span class="text-danger-text">*</span>
                        </label>
                        <select name="tipo_pago" required class="input">
                            <option value="CONTADO" {{ old('tipo_pago') === 'CONTADO' ? 'selected' : '' }}>Contado</option>
                            <option value="CREDITO" {{ old('tipo_pago') === 'CREDITO' ? 'selected' : '' }}>Crédito Comercial</option>
                        </select>
                    </div>

                    <!-- Descuento Global -->
                    <div>
                        <label class="label-base">
                            Descuento Global ($)
                        </label>
                        <input type="number" name="descuento" x-model.number="descuento" min="0" step="0.01"
                            placeholder="0.00"
                            class="input font-mono">
                    </div>

                    <!-- Observaciones -->
                    <div class="sm:col-span-2">
                        <label class="label-base">
                            Notas u Observaciones
                        </label>
                        <input type="text" name="observaciones" value="{{ old('observaciones') }}"
                            placeholder="Opcional: Términos de entrega, guía de transporte..."
                            class="input">
                    </div>
                </div>
            </div>

            <!-- 2. Detalle de Artículos -->
            <div class="card p-5 sm:p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-200 pb-3">
                    <div class="flex items-center">
                        <span class="h-5 w-5 rounded bg-brand-50 text-brand-700 font-semibold flex items-center justify-center mr-2.5 text-xs">2</span>
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900">Líneas de Compra y Artículos</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Cada línea corresponde a un artículo único. Busca el producto en la fila por su nombre o código.</p>
                        </div>
                    </div>
                    <button type="button" @click="addItem()" class="btn-secondary text-xs self-start sm:self-auto">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Añadir Fila
                    </button>
                </div>

                <!-- Tabla de Artículos Desktop -->
                <div class="hidden md:block overflow-visible">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="w-5/12">Artículo</th>
                                <th class="w-2/12 text-right">Cantidad</th>
                                <th class="w-2/12 text-right">Costo Unitario ($)</th>
                                <th class="w-1/12 text-right">% IVA</th>
                                <th class="w-2/12 text-right">Total Línea</th>
                                <th class="w-10 text-center"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(item, idx) in items" :key="item.uid">
                                <tr>
                                    <!-- Selector de Producto -->
                                    <td class="relative">
                                        <!-- Producto seleccionado -->
                                        <div x-show="item.producto_id" class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200">
                                            <div class="flex items-center space-x-2.5 truncate mr-2">
                                                <img x-show="getProduct(item.producto_id)?.imagen_url"
                                                     :src="getProduct(item.producto_id)?.imagen_url || ''"
                                                     class="h-7 w-7 object-cover rounded flex-shrink-0 border border-slate-200">
                                                <div x-show="!getProduct(item.producto_id)?.imagen_url"
                                                     class="h-7 w-7 rounded bg-brand-50 text-brand-700 font-semibold text-xs flex items-center justify-center flex-shrink-0 border border-brand-100">
                                                    <span x-text="(getProduct(item.producto_id)?.nombre || 'P').charAt(0).toUpperCase()"></span>
                                                </div>
                                                <div class="truncate">
                                                    <div class="font-semibold text-xs text-slate-900 truncate"
                                                        x-text="getProduct(item.producto_id)?.nombre"></div>
                                                    <div class="text-[11px] text-slate-500 flex items-center space-x-1.5 font-mono">
                                                        <span x-text="getProduct(item.producto_id)?.codigo || getProduct(item.producto_id)?.sku"></span>
                                                        <span x-text="'• ' + (getProduct(item.producto_id)?.unidad_medida?.codigo || 'UND')"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" @click="clearLineProduct(idx)"
                                                class="btn-secondary py-0.5 px-2 text-xs flex-shrink-0"
                                                title="Cambiar producto seleccionado">
                                                Cambiar
                                            </button>
                                            <input type="hidden" :name="'items[' + idx + '][producto_id]'"
                                                :value="item.producto_id">
                                        </div>

                                        <!-- Buscador en la fila -->
                                        <div x-show="!item.producto_id" class="relative" @click.away="item.dropdownOpen = false">
                                            <div class="relative flex items-center">
                                                <svg class="h-4 w-4 absolute left-3 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                <input type="text" x-model="item.searchQuery"
                                                    @focus="item.dropdownOpen = true"
                                                    @input="item.dropdownOpen = true"
                                                    @keydown.escape="item.dropdownOpen = false"
                                                    @keydown.enter.prevent="
                                                        const list = getFilteredRowProducts(item.searchQuery, idx);
                                                        if (list.length > 0) setLineProduct(idx, list[0]);
                                                    "
                                                    placeholder="Buscar por nombre, SKU o código..."
                                                    class="input pl-9 text-xs">
                                            </div>

                                            <!-- Dropdown flotante -->
                                            <div x-cloak x-show="item.dropdownOpen && !item.producto_id"
                                                class="absolute left-0 right-0 z-40 mt-1 bg-white rounded-lg shadow-lg border border-slate-200 overflow-hidden max-h-56 overflow-y-auto">
                                                <template x-for="p in getFilteredRowProducts(item.searchQuery, idx)"
                                                    :key="p.id">
                                                    <button type="button" @click.stop="setLineProduct(idx, p)"
                                                        class="w-full px-3 py-2 text-left hover:bg-slate-50 transition border-b border-slate-100 last:border-0 flex items-center justify-between group">
                                                        <div class="flex items-center space-x-2 truncate mr-2">
                                                            <template x-if="p.imagen_url">
                                                                <img :src="p.imagen_url" class="h-6 w-6 object-cover rounded flex-shrink-0 border border-slate-200">
                                                            </template>
                                                            <template x-if="!p.imagen_url">
                                                                <div class="h-6 w-6 rounded bg-slate-100 text-slate-600 font-semibold text-[10px] flex items-center justify-center flex-shrink-0">
                                                                    <span x-text="(p.nombre || 'P').charAt(0).toUpperCase()"></span>
                                                                </div>
                                                            </template>
                                                            <div class="truncate">
                                                                <div class="font-medium text-xs text-slate-800 group-hover:text-brand-700 truncate"
                                                                    x-text="p.nombre"></div>
                                                                <div class="text-[11px] text-slate-400 font-mono flex items-center space-x-1">
                                                                    <span class="text-slate-600" x-text="p.codigo || p.sku"></span>
                                                                    <span x-text="'• ' + (p.unidad_medida ? p.unidad_medida.codigo : 'UND')"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="text-right flex-shrink-0 ml-2">
                                                            <span class="text-[10px] text-slate-400 block">Costo ref.</span>
                                                            <span class="font-mono text-xs font-semibold text-slate-800"
                                                                x-text="formatMoney(p.precio_compra)"></span>
                                                        </div>
                                                    </button>
                                                </template>
                                                <template x-if="getFilteredRowProducts(item.searchQuery, idx).length === 0">
                                                    <div class="px-3 py-2.5 text-xs text-slate-500 text-center">
                                                        No se encontraron artículos disponibles
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Cantidad -->
                                    <td>
                                        <input type="number" :name="'items[' + idx + '][cantidad]'"
                                            x-model.number="item.cantidad" min="0.01" step="any" required
                                            class="input text-right font-mono text-xs tabular-nums">
                                    </td>

                                    <!-- Costo Unitario -->
                                    <td>
                                        <input type="number" :name="'items[' + idx + '][costo_unitario]'"
                                            x-model.number="item.costo_unitario" min="0" step="0.01" required
                                            class="input text-right font-mono text-xs tabular-nums">
                                    </td>

                                    <!-- % IVA -->
                                    <td>
                                        <select :name="'items[' + idx + '][porcentaje_iva]'"
                                            x-model.number="item.porcentaje_iva"
                                            class="input text-right font-mono text-xs">
                                            <option value="0">0%</option>
                                            <option value="5">5%</option>
                                            <option value="19">19%</option>
                                        </select>
                                    </td>

                                    <!-- Total Línea -->
                                    <td class="text-right whitespace-nowrap">
                                        <div class="font-semibold text-xs text-slate-900 font-mono tabular-nums"
                                            x-text="formatMoney(itemTotal(item))"></div>
                                        <div class="text-[10px] text-slate-400 font-mono tabular-nums"
                                            x-text="'IVA: ' + formatMoney(itemIva(item))"></div>
                                    </td>

                                    <!-- Eliminar Fila -->
                                    <td class="text-center">
                                        <button type="button" @click="removeItem(idx)"
                                            class="p-1 text-slate-400 hover:text-danger-text hover:bg-danger-bg rounded transition"
                                            title="Eliminar fila">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Botón Añadir Fila Inferior (Desktop) -->
                <div class="pt-2 hidden md:block">
                    <button type="button" @click="addItem()" class="btn-secondary text-xs">
                        <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Añadir Fila
                    </button>
                </div>

                <!-- Vista Móvil de Artículos (Cards) -->
                <div class="block md:hidden space-y-3">
                    <template x-for="(item, idx) in items" :key="item.uid">
                        <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/50 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-600" x-text="'Línea #' + (idx + 1)"></span>
                                <button type="button" @click="removeItem(idx)"
                                    class="text-xs font-semibold text-danger-text hover:opacity-80">
                                    Quitar
                                </button>
                            </div>

                            <!-- Selector Móvil -->
                            <div>
                                <label class="label-base">Artículo</label>
                                <div x-show="item.producto_id" class="flex items-center justify-between p-2 bg-white rounded-lg border border-slate-200">
                                    <div class="flex items-center space-x-2 truncate mr-2">
                                        <img x-show="getProduct(item.producto_id)?.imagen_url"
                                             :src="getProduct(item.producto_id)?.imagen_url || ''"
                                             class="h-7 w-7 object-cover rounded flex-shrink-0 border border-slate-200">
                                        <div class="truncate">
                                            <div class="font-semibold text-xs text-slate-900 truncate"
                                                x-text="getProduct(item.producto_id)?.nombre"></div>
                                            <div class="text-[10px] text-slate-400 font-mono"
                                                x-text="getProduct(item.producto_id)?.codigo || getProduct(item.producto_id)?.sku">
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" @click="clearLineProduct(idx)"
                                        class="btn-secondary py-0.5 px-2 text-xs">
                                        Cambiar
                                    </button>
                                    <input type="hidden" :name="'items[' + idx + '][producto_id]'"
                                        :value="item.producto_id">
                                </div>

                                <div x-show="!item.producto_id" class="relative" @click.away="item.dropdownOpen = false">
                                    <input type="text" x-model="item.searchQuery" @focus="item.dropdownOpen = true"
                                        @input="item.dropdownOpen = true" placeholder="Buscar artículo..."
                                        class="input text-xs">
                                    <div x-cloak x-show="item.dropdownOpen && !item.producto_id"
                                        class="absolute left-0 right-0 z-30 mt-1 bg-white rounded-lg shadow-lg border border-slate-200 max-h-48 overflow-y-auto">
                                        <template x-for="p in getFilteredRowProducts(item.searchQuery, idx)"
                                            :key="p.id">
                                            <button type="button" @click.stop="setLineProduct(idx, p)"
                                                class="w-full px-3 py-2 text-left text-xs hover:bg-slate-50 border-b border-slate-100 last:border-0 flex items-center justify-between">
                                                <div class="truncate mr-2">
                                                    <div class="font-medium text-slate-800 truncate" x-text="p.nombre"></div>
                                                    <div class="text-[10px] text-slate-400 font-mono"
                                                        x-text="p.codigo || p.sku"></div>
                                                </div>
                                                <span class="font-mono text-xs font-semibold text-slate-700 tabular-nums"
                                                    x-text="formatMoney(p.precio_compra)"></span>
                                            </button>
                                        </template>
                                        <template x-if="getFilteredRowProducts(item.searchQuery, idx).length === 0">
                                            <div class="px-3 py-2 text-xs text-slate-500 text-center">
                                                No se encontraron artículos
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="label-base">Cantidad</label>
                                    <input type="number" :name="'items[' + idx + '][cantidad]'"
                                        x-model.number="item.cantidad" min="0.01" step="any" required
                                        class="input text-right font-mono text-xs tabular-nums">
                                </div>
                                <div>
                                    <label class="label-base">Costo Unitario</label>
                                    <input type="number" :name="'items[' + idx + '][costo_unitario]'"
                                        x-model.number="item.costo_unitario" min="0" step="0.01" required
                                        class="input text-right font-mono text-xs tabular-nums">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="label-base">% IVA</label>
                                    <select :name="'items[' + idx + '][porcentaje_iva]'"
                                        x-model.number="item.porcentaje_iva"
                                        class="input text-right font-mono text-xs">
                                        <option value="0">0%</option>
                                        <option value="5">5%</option>
                                        <option value="19">19%</option>
                                    </select>
                                </div>
                                <div class="flex flex-col justify-end text-right">
                                    <div class="text-[10px] text-slate-400">Total Fila:</div>
                                    <div class="text-xs font-semibold text-slate-900 font-mono tabular-nums"
                                        x-text="formatMoney(itemTotal(item))"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Botón Añadir Ítem Móvil -->
                <div class="pt-2 md:hidden">
                    <button type="button" @click="addItem()" class="btn-secondary w-full justify-center text-xs">
                        + Añadir Otra Fila
                    </button>
                </div>
            </div>

            <!-- 3. Resumen y Confirmación -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Columna Informativa de Auditoría -->
                <div class="lg:col-span-2 card p-5 text-sm space-y-2 bg-slate-50 border-slate-200">
                    <div class="font-semibold flex items-center text-slate-800">
                        <svg class="h-4 w-4 mr-2 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Impacto en Inventario y Costos
                    </div>
                    <ul class="list-disc list-inside text-xs text-slate-600 space-y-1 pl-1">
                        <li>Cada producto registrado aumentará automáticamente las existencias de la sucursal receptora seleccionada.</li>
                        <li>Se generará un movimiento inmutable en el <strong>Kardex</strong> con tipo <code class="bg-slate-200 px-1 py-0.5 rounded text-slate-800 font-mono text-[11px]">ENTRADA_COMPRA</code> y referencia a la factura.</li>
                        <li>El costo unitario ingresado se establecerá como el nuevo costo de reposición maestro del producto en el catálogo.</li>
                    </ul>
                </div>

                <!-- Tarjeta de Totales Finales -->
                <div class="card p-5 sm:p-6 space-y-3">
                    <div class="kpi-label">Resumen de Liquidación</div>

                    <div class="space-y-2 text-xs text-slate-600 pt-1">
                        <div class="flex justify-between">
                            <span>Subtotal Neto:</span>
                            <span class="font-mono font-semibold text-slate-800 tabular-nums"
                                x-text="formatMoney(totalSubtotal())"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Impuestos (IVA):</span>
                            <span class="font-mono font-semibold text-slate-800 tabular-nums" x-text="formatMoney(totalIva())"></span>
                        </div>
                        <div class="flex justify-between text-warning-text">
                            <span>Descuento Aplicado:</span>
                            <span class="font-mono font-semibold tabular-nums" x-text="'-' + formatMoney(descuento)"></span>
                        </div>
                        <div class="border-t border-slate-200 pt-3 flex justify-between items-baseline">
                            <span class="text-sm font-semibold text-slate-900">Total a Pagar:</span>
                            <span class="text-xl font-bold text-slate-900 font-mono tabular-nums"
                                x-text="formatMoney(grandTotal())"></span>
                        </div>
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="btn-primary w-full justify-center py-2.5">
                            <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Registrar Factura y Cargar Stock
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection