@extends('layouts.app')

@section('title', 'Catálogos Auxiliares')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ activeTab: 'categorias' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('productos.index') }}" class="hover:text-indigo-600 transition">Productos</a>
                <span>/</span>
                <span class="text-slate-800">Catálogos Auxiliares</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Clasificación y Catálogos</h1>
            <p class="text-sm text-slate-500">
                Gestiona las categorías, marcas comerciales y unidades de medida de tu negocio.
            </p>
        </div>
        <div>
            <a href="{{ route('productos.index') }}"
                class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-sm">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a Productos
            </a>
        </div>
    </div>

    <!-- Navegación de Tabs Táctiles -->
    <div class="bg-white p-2 rounded-2xl border border-slate-200 shadow-sm flex space-x-2">
        <button type="button" @click="activeTab = 'categorias'"
            :class="activeTab === 'categorias' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
            class="flex-1 py-3 px-4 rounded-xl text-sm font-bold transition flex items-center justify-center space-x-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <span>Categorías</span>
            <span class="ml-1.5 text-xs px-2 py-0.5 rounded-full" :class="activeTab === 'categorias' ? 'bg-indigo-700 text-white' : 'bg-slate-200 text-slate-700'">
                {{ $categorias->count() }}
            </span>
        </button>

        <button type="button" @click="activeTab = 'marcas'"
            :class="activeTab === 'marcas' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
            class="flex-1 py-3 px-4 rounded-xl text-sm font-bold transition flex items-center justify-center space-x-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span>Marcas</span>
            <span class="ml-1.5 text-xs px-2 py-0.5 rounded-full" :class="activeTab === 'marcas' ? 'bg-indigo-700 text-white' : 'bg-slate-200 text-slate-700'">
                {{ $marcas->count() }}
            </span>
        </button>

        <button type="button" @click="activeTab = 'unidades'"
            :class="activeTab === 'unidades' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
            class="flex-1 py-3 px-4 rounded-xl text-sm font-bold transition flex items-center justify-center space-x-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
            </svg>
            <span>Unidades</span>
            <span class="ml-1.5 text-xs px-2 py-0.5 rounded-full" :class="activeTab === 'unidades' ? 'bg-indigo-700 text-white' : 'bg-slate-200 text-slate-700'">
                {{ $unidades->count() }}
            </span>
        </button>
    </div>

    <!-- Contenido: TAB 1 - CATEGORÍAS -->
    <div x-show="activeTab === 'categorias'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Formulario lateral de Creación -->
        <div class="lg:col-span-4">
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center space-x-2 pb-3 border-b border-slate-100">
                    <div class="h-7 w-7 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center font-bold text-xs">
                        +
                    </div>
                    <h2 class="font-bold text-slate-900 text-base">Nueva Categoría</h2>
                </div>
                <form action="{{ route('catalogos.categorias.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="cat_nombre" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Nombre de la Categoría <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombre" id="cat_nombre" required
                            placeholder="Ej: Lácteos, Tornillería, Abarrotes"
                            class="block w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label for="cat_desc" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Descripción (Opcional)
                        </label>
                        <textarea name="descripcion" id="cat_desc" rows="2"
                            placeholder="Breve detalle de la categoría..."
                            class="block w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full py-3 px-4 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                        Guardar Categoría
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Categorías -->
        <div class="lg:col-span-8">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Categorías Registradas</span>
                    <span class="text-xs text-slate-400">Total: {{ $categorias->count() }}</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($categorias as $cat)
                    <div class="p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50/60 transition">
                        <div>
                            <div class="font-bold text-slate-900 text-sm sm:text-base">{{ $cat->nombre }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $cat->descripcion ?? 'Sin descripción adicional' }}</div>
                            <span class="inline-flex items-center text-[11px] font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md mt-1.5">
                                {{ $cat->productos_count }} {{ $cat->productos_count == 1 ? 'producto' : 'productos' }}
                            </span>
                        </div>
                        <div>
                            @if($cat->productos_count === 0)
                            <form action="{{ route('catalogos.categorias.destroy', $cat) }}" method="POST" onsubmit="return confirm('¿Eliminar esta categoría?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition" title="Eliminar categoría">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                            @else
                            <span class="text-[11px] font-medium text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg" title="No se puede eliminar porque tiene productos vinculados">
                                En uso
                            </span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-sm text-slate-500">
                        No hay categorías registradas aún.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido: TAB 2 - MARCAS -->
    <div x-cloak x-show="activeTab === 'marcas'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Formulario lateral de Marcas -->
        <div class="lg:col-span-4">
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center space-x-2 pb-3 border-b border-slate-100">
                    <div class="h-7 w-7 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center font-bold text-xs">
                        +
                    </div>
                    <h2 class="font-bold text-slate-900 text-base">Nueva Marca</h2>
                </div>
                <form action="{{ route('catalogos.marcas.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="marca_nombre" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Nombre de la Marca <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombre" id="marca_nombre" required
                            placeholder="Ej: Nestlé, Stanley, Colanta"
                            class="block w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label for="marca_desc" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Descripción / Fabricante (Opcional)
                        </label>
                        <textarea name="descripcion" id="marca_desc" rows="2"
                            placeholder="Detalles del fabricante o distribuidor..."
                            class="block w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full py-3 px-4 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                        Guardar Marca
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Marcas -->
        <div class="lg:col-span-8">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Marcas Registradas</span>
                    <span class="text-xs text-slate-400">Total: {{ $marcas->count() }}</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($marcas as $m)
                    <div class="p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50/60 transition">
                        <div>
                            <div class="font-bold text-slate-900 text-sm sm:text-base">{{ $m->nombre }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">{{ $m->descripcion ?? 'Sin descripción' }}</div>
                            <span class="inline-flex items-center text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md mt-1.5">
                                {{ $m->productos_count }} {{ $m->productos_count == 1 ? 'producto' : 'productos' }}
                            </span>
                        </div>
                        <div>
                            @if($m->productos_count === 0)
                            <form action="{{ route('catalogos.marcas.destroy', $m) }}" method="POST" onsubmit="return confirm('¿Eliminar esta marca?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition" title="Eliminar marca">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                            @else
                            <span class="text-[11px] font-medium text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg" title="No se puede eliminar porque tiene productos vinculados">
                                En uso
                            </span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-sm text-slate-500">
                        No hay marcas registradas aún.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido: TAB 3 - UNIDADES DE MEDIDA -->
    <div x-cloak x-show="activeTab === 'unidades'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Formulario lateral de Unidades -->
        <div class="lg:col-span-4">
            <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex items-center space-x-2 pb-3 border-b border-slate-100">
                    <div class="h-7 w-7 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center font-bold text-xs">
                        +
                    </div>
                    <h2 class="font-bold text-slate-900 text-base">Nueva Unidad</h2>
                </div>
                <form action="{{ route('catalogos.unidades.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="uni_codigo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Código Corto / Símbolo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="codigo" id="uni_codigo" required maxlength="10"
                            placeholder="Ej: UND, KG, LT, MT, CAJ"
                            class="block w-full px-3.5 py-2.5 uppercase font-mono border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label for="uni_nombre" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Nombre Completo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nombre" id="uni_nombre" required
                            placeholder="Ej: Unidad, Kilogramo, Litro, Metro"
                            class="block w-full px-3.5 py-2.5 border border-slate-300 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>
                    <button type="submit"
                        class="w-full py-3 px-4 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                        Guardar Unidad
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Unidades -->
        <div class="lg:col-span-8">
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Unidades de Medida Registradas</span>
                    <span class="text-xs text-slate-400">Total: {{ $unidades->count() }}</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($unidades as $u)
                    <div class="p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50/60 transition">
                        <div class="flex items-center space-x-3">
                            <span class="h-10 w-12 bg-slate-100 text-slate-800 font-mono font-bold rounded-xl flex items-center justify-center text-xs">
                                {{ $u->codigo }}
                            </span>
                            <div>
                                <div class="font-bold text-slate-900 text-sm sm:text-base">{{ $u->nombre }}</div>
                                <span class="inline-flex items-center text-[11px] font-semibold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md mt-1">
                                    {{ $u->productos_count }} {{ $u->productos_count == 1 ? 'producto' : 'productos' }}
                                </span>
                            </div>
                        </div>
                        <div>
                            @if($u->productos_count === 0)
                            <form action="{{ route('catalogos.unidades.destroy', $u) }}" method="POST" onsubmit="return confirm('¿Eliminar esta unidad de medida?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition" title="Eliminar unidad">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                            @else
                            <span class="text-[11px] font-medium text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg" title="No se puede eliminar porque tiene productos vinculados">
                                En uso
                            </span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-sm text-slate-500">
                        No hay unidades de medida registradas aún.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection