@extends('layouts.app')

@section('title', 'Editar Producto - ' . $producto->nombre)

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Breadcrumb & Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('productos.index') }}" class="hover:text-indigo-600 transition">Catálogo</a>
                <span>/</span>
                <span class="text-slate-800">Editar Producto</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $producto->nombre }}</h1>
            <p class="text-sm text-slate-500 font-mono">SKU: {{ $producto->codigo ?? 'Sin SKU' }} | Barras: {{ $producto->codigo_barras ?? 'Sin código' }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('productos.index') }}"
                class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
            <form action="{{ route('productos.destroy', $producto) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2.5 rounded-xl border border-red-200 text-sm font-semibold text-red-600 bg-red-50 hover:bg-red-100 transition shadow-sm">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Eliminar
                </button>
            </form>
        </div>
    </div>

    <!-- Formulario Reactivo con Alpine.js para cálculo de márgenes e IVA -->
    <form action="{{ route('productos.update', $producto) }}" method="POST"
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
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex items-center space-x-3 pb-4 border-b border-slate-100">
                <div class="h-9 w-9 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-bold text-lg">
                    1
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Identificación Básica</h2>
                    <p class="text-xs text-slate-500">Nombre del artículo, SKU y código de barras para lector POS.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-5">
                <!-- Nombre del Producto -->
                <div class="sm:col-span-12">
                    <label for="nombre" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nombre del Producto / Artículo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $producto->nombre) }}" required
                        placeholder="Ej: Gaseosa Colombiana 1.5L, Cemento Gris 50kg, Acetaminofén 500mg"
                        class="block w-full px-4 py-3 border @error('nombre') border-red-300 ring-1 ring-red-300 @else border-slate-300 @enderror rounded-xl text-sm font-medium text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('nombre')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- SKU / Código Interno -->
                <div class="sm:col-span-6">
                    <label for="codigo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Código Interno / SKU
                    </label>
                    <input type="text" name="codigo" id="codigo" value="{{ old('codigo', $producto->codigo) }}"
                        placeholder="Ej: ART-001, REF-8842"
                        class="block w-full px-4 py-3 font-mono border @error('codigo') border-red-300 ring-1 ring-red-300 @else border-slate-300 @enderror rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('codigo')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Código de Barras (EAN-13 / UPC) -->
                <div class="sm:col-span-6">
                    <label for="codigo_barras" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Código de Barras (Lector Scanner)
                    </label>
                    <div class="relative">
                        <input type="text" name="codigo_barras" id="codigo_barras" value="{{ old('codigo_barras', $producto->codigo_barras) }}"
                            placeholder="Escanea o escribe el código..."
                            class="block w-full pl-11 pr-4 py-3 font-mono border @error('codigo_barras') border-red-300 ring-1 ring-red-300 @else border-slate-300 @enderror rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                    </div>
                    @error('codigo_barras')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div class="sm:col-span-12">
                    <label for="descripcion" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Descripción o Especificaciones
                    </label>
                    <textarea name="descripcion" id="descripcion" rows="2"
                        placeholder="Detalles adicionales, tamaño, presentación o notas para vendedores..."
                        class="block w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">{{ old('descripcion', $producto->descripcion) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Tarjeta 2: Clasificación y Unidad -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex items-center space-x-3 pb-4 border-b border-slate-100">
                <div class="h-9 w-9 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center font-bold text-lg">
                    2
                </div>
                <div class="flex-1 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Clasificación de Catálogo</h2>
                        <p class="text-xs text-slate-500">Organiza tus productos para reportes, filtros e inventario rápido.</p>
                    </div>
                    <a href="{{ route('catalogos.index') }}"
                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                        + Administrar Catálogos
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <!-- Categoría -->
                <div>
                    <label for="categoria_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Categoría
                    </label>
                    <select name="categoria_id" id="categoria_id"
                        class="block w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <option value="">-- Sin Categoría --</option>
                        @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ old('categoria_id', $producto->categoria_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nombre }}
                        </option>
                        @endforeach
                    </select>
                    @error('categoria_id')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Marca -->
                <div>
                    <label for="marca_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Marca / Fabricante
                    </label>
                    <select name="marca_id" id="marca_id"
                        class="block w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <option value="">-- Sin Marca --</option>
                        @foreach($marcas as $marca)
                        <option value="{{ $marca->id }}" {{ old('marca_id', $producto->marca_id) == $marca->id ? 'selected' : '' }}>
                            {{ $marca->nombre }}
                        </option>
                        @endforeach
                    </select>
                    @error('marca_id')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Unidad de Medida -->
                <div>
                    <label for="unidad_medida_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Unidad de Medida
                    </label>
                    <select name="unidad_medida_id" id="unidad_medida_id"
                        class="block w-full px-4 py-3 border border-slate-300 rounded-xl text-sm text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <option value="">-- Unidad por defecto (UND) --</option>
                        @foreach($unidades as $u)
                        <option value="{{ $u->id }}" {{ old('unidad_medida_id', $producto->unidad_medida_id) == $u->id ? 'selected' : '' }}>
                            {{ $u->nombre }} ({{ $u->codigo }})
                        </option>
                        @endforeach
                    </select>
                    @error('unidad_medida_id')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Tarjeta 3: Estructura de Precios e Impuestos -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex items-center space-x-3 pb-4 border-b border-slate-100">
                <div class="h-9 w-9 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center font-bold text-lg">
                    3
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Estructura Comercial y Precios</h2>
                    <p class="text-xs text-slate-500">Costos de adquisición, precios para público, mayoristas e IVA.</p>
                </div>
            </div>

            <!-- Calculadora en Tiempo Real -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Margen de Ganancia:</span>
                    <span class="text-lg font-bold" :class="margenGanancia > 0 ? 'text-emerald-600' : 'text-slate-400'" x-text="margenGanancia + '%'"></span>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Utilidad Bruta x Unidad:</span>
                    <span class="text-lg font-bold text-indigo-600" x-text="'$' + Number(gananciaNeta).toLocaleString('es-CO')"></span>
                </div>
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Precio Final (con IVA):</span>
                    <span class="text-lg font-extrabold text-slate-900" x-text="'$' + Number(precioConIva).toLocaleString('es-CO')"></span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Precio de Compra -->
                <div>
                    <label for="precio_compra" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Precio Costo / Compra ($)
                    </label>
                    <input type="number" step="0.01" name="precio_compra" id="precio_compra"
                        x-model="precioCompra"
                        placeholder="0.00"
                        class="block w-full px-4 py-3 border border-slate-300 rounded-xl text-sm font-semibold text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('precio_compra')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Precio de Venta (PVP) -->
                <div>
                    <label for="precio_venta" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Precio Venta Público ($) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" step="0.01" name="precio_venta" id="precio_venta" required
                        x-model="precioVenta"
                        placeholder="0.00"
                        class="block w-full px-4 py-3 border @error('precio_venta') border-red-300 ring-1 ring-red-300 @else border-slate-300 @enderror rounded-xl text-sm font-bold text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('precio_venta')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Precio Mayorista -->
                <div>
                    <label for="precio_mayorista" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Precio Mayorista ($)
                    </label>
                    <input type="number" step="0.01" name="precio_mayorista" id="precio_mayorista"
                        value="{{ old('precio_mayorista', $producto->precio_mayorista) }}"
                        placeholder="Opcional"
                        class="block w-full px-4 py-3 border border-slate-300 rounded-xl text-sm font-medium text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('precio_mayorista')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- IVA (%) -->
                <div>
                    <label for="iva" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        IVA / Impuesto (%)
                    </label>
                    <select name="iva" id="iva" x-model="iva"
                        class="block w-full px-4 py-3 border border-slate-300 rounded-xl text-sm font-medium text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <option value="0">0% (Exento / Excluido)</option>
                        <option value="5">5% (Tarifa Reducida)</option>
                        <option value="19">19% (Tarifa General Colombia)</option>
                    </select>
                    @error('iva')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Tarjeta 4: Inventario y Estado -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">
            <div class="flex items-center space-x-3 pb-4 border-b border-slate-100">
                <div class="h-9 w-9 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center font-bold text-lg">
                    4
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Control de Existencias & Estado</h2>
                    <p class="text-xs text-slate-500">Configura el stock disponible, umbrales de alerta y disponibilidad.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <!-- Stock Disponible -->
                <div>
                    <label for="stock" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Stock Actual en Tienda
                    </label>
                    <input type="number" step="0.01" name="stock" id="stock" value="{{ old('stock', $producto->stock) }}"
                        placeholder="0"
                        class="block w-full px-4 py-3 border border-slate-300 rounded-xl text-sm font-semibold text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('stock')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Stock Mínimo -->
                <div>
                    <label for="stock_minimo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Stock Mínimo (Alerta de Agotado)
                    </label>
                    <input type="number" step="0.01" name="stock_minimo" id="stock_minimo" value="{{ old('stock_minimo', $producto->stock_minimo) }}"
                        placeholder="5"
                        class="block w-full px-4 py-3 border border-slate-300 rounded-xl text-sm font-semibold text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <p class="text-[11px] text-slate-400 mt-1">El sistema alertará cuando el inventario caiga a este nivel o inferior.</p>
                    @error('stock_minimo')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estado -->
                <div>
                    <label for="estado" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Estado del Producto
                    </label>
                    <select name="estado" id="estado"
                        class="block w-full px-4 py-3 border border-slate-300 rounded-xl text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <option value="activo" {{ old('estado', $producto->estado->value ?? $producto->estado) === 'activo' ? 'selected' : '' }}>Activo (Disponible para venta)</option>
                        <option value="inactivo" {{ old('estado', $producto->estado->value ?? $producto->estado) === 'inactivo' ? 'selected' : '' }}>Inactivo (Oculto en caja)</option>
                    </select>
                    @error('estado')
                        <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Botones de Acción Táctiles (Fácil clic en Tablet y Móvil) -->
        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4">
            <a href="{{ route('productos.index') }}"
                class="w-full sm:w-auto px-6 py-3.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 text-center hover:bg-slate-100 transition">
                Cancelar
            </a>
            <button type="submit"
                class="w-full sm:w-auto px-8 py-3.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-500/20 text-center transition">
                Actualizar Producto
            </button>
        </div>
    </form>
</div>
@endsection