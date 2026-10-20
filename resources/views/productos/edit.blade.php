@extends('layouts.app')

@section('title', 'Editar Producto - ' . $producto->nombre)

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ deleteModalOpen: false }">
    <!-- Breadcrumb & Encabezado -->
    <x-page-header
        :title="$producto->nombre"
        subtitle="SKU: {{ $producto->codigo ?? 'Sin SKU' }} &bull; Barras: {{ $producto->codigo_barras ?? 'Sin código' }}"
        :breadcrumbs="[
            ['label' => 'Catálogo', 'url' => route('productos.index')],
            ['label' => 'Editar Producto']
        ]"
    >
        <x-slot:actions>
            <div class="flex items-center space-x-2">
                <x-button variant="outline" size="sm" href="{{ route('productos.index') }}">
                    <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver
                </x-button>

                <x-button variant="danger" size="sm" type="button" @click="deleteModalOpen = true">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Eliminar
                </x-button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- Formulario Reactivo con Alpine.js para cálculo de márgenes e IVA -->
    <form action="{{ route('productos.update', $producto) }}" method="POST" enctype="multipart/form-data"
          x-data="{
              precioCompra: {{ old('precio_compra', $producto->precio_compra) }},
              precioVenta: {{ old('precio_venta', $producto->precio_venta) }},
              iva: {{ old('iva', $producto->iva) }},
              get margenGanancia() {
                  let pc = parseFloat(this.precioCompra) || 0;
                  let pv = parseFloat(this.precioVenta) || 0;
                  if (pc <= 0 || pv <= 0) return 0;
                  return (((pv - pc) / pc) * 100).toFixed(1);
              },
              get gananciaNeta() {
                  let pc = parseFloat(this.precioCompra) || 0;
                  let pv = parseFloat(this.precioVenta) || 0;
                  return Math.max(0, pv - pc);
              },
              get precioConIva() {
                  let pv = parseFloat(this.precioVenta) || 0;
                  let i = parseFloat(this.iva) || 0;
                  return (pv * (1 + (i / 100))).toFixed(2);
              }
          }"
          class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Tarjeta 1: Información General -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 space-y-6">
            <div class="flex items-center space-x-3 pb-4 border-b border-slate-100">
                <div class="h-7 w-7 bg-slate-100 text-slate-700 rounded-lg flex items-center justify-center font-bold text-xs border border-slate-200">
                    1
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Identificación Básica</h2>
                    <p class="text-xs text-slate-500">Nombre del artículo, SKU y código de barras para lector POS.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-5">
                <!-- Nombre del Producto -->
                <div class="sm:col-span-12">
                    <x-field label="Nombre del Producto / Artículo" name="nombre" :required="true">
                        <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $producto->nombre) }}" required
                            placeholder="Ej: Gaseosa Colombiana 1.5L, Cemento Gris 50kg, Acetaminofén 500mg"
                            class="block w-full px-3.5 py-2 border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                    </x-field>
                </div>

                <!-- SKU / Código Interno -->
                <div class="sm:col-span-6">
                    <x-field label="Código Interno / SKU" name="codigo">
                        <input type="text" name="codigo" id="codigo" value="{{ old('codigo', $producto->codigo) }}"
                            placeholder="Ej: ART-001, REF-8842"
                            class="block w-full px-3.5 py-2 font-mono border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                    </x-field>
                </div>

                <!-- Código de Barras (EAN-13 / UPC) -->
                <div class="sm:col-span-6">
                    <x-field label="Código de Barras (Lector Scanner)" name="codigo_barras">
                        <div class="relative">
                            <input type="text" name="codigo_barras" id="codigo_barras" value="{{ old('codigo_barras', $producto->codigo_barras) }}"
                                placeholder="Escanea o escribe el código..."
                                class="block w-full pl-10 pr-3.5 py-2 font-mono border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                            </div>
                        </div>
                    </x-field>
                </div>

                <!-- Descripción -->
                <div class="sm:col-span-12">
                    <x-field label="Descripción o Especificaciones" name="descripcion">
                        <textarea name="descripcion" id="descripcion" rows="2"
                            placeholder="Detalles adicionales, tamaño, presentación..."
                            class="block w-full px-3.5 py-2 border border-slate-300 rounded-lg text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">{{ old('descripcion', $producto->descripcion) }}</textarea>
                    </x-field>
                </div>

                <!-- Fotografía del Producto -->
                <div class="sm:col-span-12" x-data="{
                    imagePreview: '{{ $producto->imagen_url }}',
                    previewFile(event) {
                        const file = event.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = (e) => { this.imagePreview = e.target.result; };
                            reader.readAsDataURL(file);
                        }
                    },
                    clearImage() {
                        this.imagePreview = null;
                        $refs.fileInput.value = '';
                    }
                }">
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1.5">
                        Fotografía del Producto
                    </label>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 border border-dashed border-slate-300 rounded-xl bg-slate-50/50">
                        <!-- Preview Box -->
                        <div class="h-20 w-20 rounded-lg bg-white border border-slate-200 shadow-xs flex items-center justify-center overflow-hidden flex-shrink-0 relative">
                            <template x-if="imagePreview">
                                <img :src="imagePreview" alt="Vista previa" class="h-full w-full object-cover">
                            </template>
                            <template x-if="!imagePreview">
                                <div class="text-slate-400 text-center p-2">
                                    <svg class="h-6 w-6 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-[10px] block mt-0.5">Sin foto</span>
                                </div>
                            </template>
                        </div>

                        <!-- Selector y Botones -->
                        <div class="flex-1 space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <label class="inline-flex items-center px-3 py-1.5 rounded-lg bg-white text-slate-700 hover:bg-slate-50 border border-slate-300 text-xs font-medium cursor-pointer transition shadow-xs">
                                    <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    Cambiar Imagen
                                    <input type="file" name="imagen" x-ref="fileInput" @change="previewFile" accept="image/png, image/jpeg, image/webp" class="hidden">
                                </label>
                                <button type="button" x-cloak x-show="imagePreview" @click="clearImage"
                                    class="inline-flex items-center px-3 py-1.5 rounded-md bg-danger-bg text-danger-text hover:opacity-90 border border-danger-border text-xs font-medium transition">
                                    Quitar Foto
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-500">
                                Formatos admitidos: JPG, PNG o WEBP (Máx. 2MB).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta 2: Clasificación y Unidad -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 space-y-6">
            <div class="flex items-center space-x-3 pb-4 border-b border-slate-100">
                <div class="h-7 w-7 bg-slate-100 text-slate-700 rounded-lg flex items-center justify-center font-bold text-xs border border-slate-200">
                    2
                </div>
                <div class="flex-1 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Clasificación de Catálogo</h2>
                        <p class="text-xs text-slate-500">Organiza tus productos para reportes, filtros e inventario.</p>
                    </div>
                    <a href="{{ route('catalogos.index') }}"
                        class="text-xs font-semibold text-brand-600 hover:text-brand-700 transition">
                        + Catálogos
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <!-- Categoría -->
                <div>
                    <x-field label="Categoría" name="categoria_id">
                        <select name="categoria_id" id="categoria_id" class="input">
                            <option value="">-- Sin Categoría --</option>
                            @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ old('categoria_id', $producto->categoria_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </x-field>
                </div>

                <!-- Marca -->
                <div>
                    <x-field label="Marca / Fabricante" name="marca_id">
                        <select name="marca_id" id="marca_id" class="input">
                            <option value="">-- Sin Marca --</option>
                            @foreach($marcas as $marca)
                            <option value="{{ $marca->id }}" {{ old('marca_id', $producto->marca_id) == $marca->id ? 'selected' : '' }}>
                                {{ $marca->nombre }}
                            </option>
                            @endforeach
                        </select>
                    </x-field>
                </div>

                <!-- Unidad de Medida -->
                <div>
                    <x-field label="Unidad de Medida" name="unidad_medida_id">
                        <select name="unidad_medida_id" id="unidad_medida_id" class="input">
                            <option value="">-- Unidad por defecto (UND) --</option>
                            @foreach($unidades as $u)
                            <option value="{{ $u->id }}" {{ old('unidad_medida_id', $producto->unidad_medida_id) == $u->id ? 'selected' : '' }}>
                                {{ $u->nombre }} ({{ $u->codigo }})
                            </option>
                            @endforeach
                        </select>
                    </x-field>
                </div>
            </div>
        </div>

        <!-- Tarjeta 3: Precios e Impuestos -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 space-y-6">
            <div class="flex items-center space-x-3 pb-4 border-b border-slate-100">
                <div class="h-7 w-7 bg-slate-100 text-slate-700 rounded-lg flex items-center justify-center font-bold text-xs border border-slate-200">
                    3
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Estructura Comercial y Precios</h2>
                    <p class="text-xs text-slate-500">Costos de adquisición, precios para público, mayoristas e IVA.</p>
                </div>
            </div>

            <!-- Calculadora en Tiempo Real -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50/70 p-4 rounded-lg border border-slate-200 text-xs">
                <div>
                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Margen de Ganancia:</span>
                    <span class="text-base font-bold tabular-nums" :class="margenGanancia > 0 ? 'text-emerald-700' : 'text-slate-400'" x-text="margenGanancia + '%'"></span>
                </div>
                <div>
                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Utilidad Bruta x Unidad:</span>
                    <span class="text-base font-bold text-slate-900 tabular-nums" x-text="'$' + Number(gananciaNeta).toLocaleString('es-CO')"></span>
                </div>
                <div>
                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Precio Final (con IVA):</span>
                    <span class="text-base font-bold text-brand-700 tabular-nums" x-text="'$' + Number(precioConIva).toLocaleString('es-CO')"></span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Precio de Compra -->
                <div>
                    <x-field label="Costo / Compra ($)" name="precio_compra">
                        <input type="number" step="0.01" name="precio_compra" id="precio_compra"
                            x-model="precioCompra"
                            placeholder="0.00"
                            class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-900 tabular-nums placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                    </x-field>
                </div>

                <!-- Precio de Venta (PVP) -->
                <div>
                    <x-field label="Precio Venta Público ($)" name="precio_venta" :required="true">
                        <input type="number" step="0.01" name="precio_venta" id="precio_venta" required
                            x-model="precioVenta"
                            placeholder="0.00"
                            class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-bold text-slate-900 tabular-nums placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                    </x-field>
                </div>

                <!-- Precio Mayorista -->
                <div>
                    <x-field label="Precio Mayorista ($)" name="precio_mayorista">
                        <input type="number" step="0.01" name="precio_mayorista" id="precio_mayorista"
                            value="{{ old('precio_mayorista', $producto->precio_mayorista) }}"
                            placeholder="Opcional"
                            class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-medium text-slate-900 tabular-nums placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                    </x-field>
                </div>

                <!-- IVA (%) -->
                <div>
                    <x-field label="IVA / Impuesto (%)" name="iva">
                        <select name="iva" id="iva" x-model="iva" class="input">
                            @if(isset($impuestos) && $impuestos->isNotEmpty())
                                @foreach($impuestos as $imp)
                                    <option value="{{ (float)$imp->porcentaje }}" {{ (old('iva', $producto->iva) == (float)$imp->porcentaje) ? 'selected' : '' }}>
                                        {{ (float)$imp->porcentaje }}% ({{ $imp->nombre }})
                                    </option>
                                @endforeach
                            @else
                                <option value="0">0% (Exento / Excluido)</option>
                                <option value="5">5% (Tarifa Reducida)</option>
                                <option value="19">19% (Tarifa General Colombia)</option>
                            @endif
                        </select>
                    </x-field>
                </div>
            </div>
        </div>

        <!-- Tarjeta 4: Inventario y Estado -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 space-y-6">
            <div class="flex items-center space-x-3 pb-4 border-b border-slate-100">
                <div class="h-7 w-7 bg-slate-100 text-slate-700 rounded-lg flex items-center justify-center font-bold text-xs border border-slate-200">
                    4
                </div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Control de Existencias & Estado</h2>
                    <p class="text-xs text-slate-500">Configura umbrales de alerta y disponibilidad.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Stock Mínimo -->
                <div>
                    <x-field label="Stock Mínimo (Alerta)" name="stock_minimo">
                        <input type="number" step="0.01" name="stock_minimo" id="stock_minimo" value="{{ old('stock_minimo', $producto->stock_minimo) }}"
                            placeholder="5"
                            class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-xs font-semibold text-slate-900 tabular-nums placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                    </x-field>
                </div>

                <!-- Estado -->
                <div>
                    <x-field label="Estado del Producto" name="estado">
                        <select name="estado" id="estado" class="input">
                            <option value="activo" {{ old('estado', $producto->estado?->value ?? 'activo') === 'activo' ? 'selected' : '' }}>Activo (Disponible para venta)</option>
                            <option value="inactivo" {{ old('estado', $producto->estado?->value ?? 'activo') === 'inactivo' ? 'selected' : '' }}>Inactivo (Oculto en caja)</option>
                        </select>
                    </x-field>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <x-button variant="outline" href="{{ route('productos.index') }}">
                Cancelar
            </x-button>
            <x-button variant="primary" type="submit">
                Actualizar Producto
            </x-button>
        </div>
    </form>

    <!-- Modal Confirmación de Eliminación -->
    <form id="deleteProductForm" action="{{ route('productos.destroy', $producto) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    <x-modal-confirm
        show="deleteModalOpen"
        title="Eliminar Producto"
        message="¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer."
        confirm-text="Sí, eliminar"
        cancel-text="Cancelar"
        confirm-variant="danger"
        @confirm="document.getElementById('deleteProductForm').submit()"
        @cancel="deleteModalOpen = false"
    />
</div>
@endsection