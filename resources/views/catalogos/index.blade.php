@extends('layouts.app')

@section('title', 'Catálogos Auxiliares')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'categorias' }">
    <x-page-header
        title="Clasificación y Catálogos"
        description="Gestiona las categorías, marcas comerciales y unidades de medida de tu inventario."
        :breadcrumbs="[
            ['label' => 'Productos', 'url' => route('productos.index')],
            ['label' => 'Catálogos Auxiliares']
        ]">
        <x-slot:actions>
            <x-button variant="secondary" href="{{ route('productos.index') }}">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a Productos
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <!-- Navegación de Tabs -->
    <div class="bg-slate-100 p-1 rounded-xl flex space-x-1 max-w-xl">
        <button type="button" @click="activeTab = 'categorias'"
            :class="activeTab === 'categorias' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900 font-medium'"
            class="flex-1 py-2 px-3 rounded-lg text-sm transition flex items-center justify-center space-x-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
            <span>Categorías</span>
            <span class="ml-1 text-xs px-2 py-0.5 rounded-full" :class="activeTab === 'categorias' ? 'bg-brand-50 text-brand-700 font-bold' : 'bg-slate-200/80 text-slate-600'">
                {{ $categorias->count() }}
            </span>
        </button>

        <button type="button" @click="activeTab = 'marcas'"
            :class="activeTab === 'marcas' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900 font-medium'"
            class="flex-1 py-2 px-3 rounded-lg text-sm transition flex items-center justify-center space-x-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span>Marcas</span>
            <span class="ml-1 text-xs px-2 py-0.5 rounded-full" :class="activeTab === 'marcas' ? 'bg-brand-50 text-brand-700 font-bold' : 'bg-slate-200/80 text-slate-600'">
                {{ $marcas->count() }}
            </span>
        </button>

        <button type="button" @click="activeTab = 'unidades'"
            :class="activeTab === 'unidades' ? 'bg-white text-slate-900 shadow-xs font-semibold' : 'text-slate-600 hover:text-slate-900 font-medium'"
            class="flex-1 py-2 px-3 rounded-lg text-sm transition flex items-center justify-center space-x-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
            </svg>
            <span>Unidades</span>
            <span class="ml-1 text-xs px-2 py-0.5 rounded-full" :class="activeTab === 'unidades' ? 'bg-brand-50 text-brand-700 font-bold' : 'bg-slate-200/80 text-slate-600'">
                {{ $unidades->count() }}
            </span>
        </button>
    </div>

    <!-- TAB 1: CATEGORÍAS -->
    <div x-show="activeTab === 'categorias'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-4">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h2 class="font-bold text-slate-900 text-sm">Nueva Categoría</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Clasifica productos para agrupar reportes y búsquedas.</p>
                </div>
                <form action="{{ route('catalogos.categorias.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="cat_nombre" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nombre de la Categoría <span class="text-danger-text">*</span>
                        </label>
                        <input type="text" name="nombre" id="cat_nombre" required
                            placeholder="Ej: Lácteos, Tornillería, Abarrotes"
                            class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                    </div>
                    <div>
                        <label for="cat_desc" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Descripción (Opcional)
                        </label>
                        <textarea name="descripcion" id="cat_desc" rows="3"
                            placeholder="Breve detalle de la categoría..."
                            class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs"></textarea>
                    </div>
                    <x-button variant="primary" type="submit" class="w-full justify-center">
                        Guardar Categoría
                    </x-button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-8">
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50/70 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-600">Categorías Registradas</span>
                    <span class="text-xs text-slate-500 font-medium">Total: {{ $categorias->count() }}</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($categorias as $cat)
                    <div class="p-4 sm:px-5 flex items-center justify-between hover:bg-slate-50/60 transition">
                        <div>
                            <div class="font-semibold text-slate-900 text-sm">{{ $cat->nombre }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $cat->descripcion ?? 'Sin descripción adicional' }}</div>
                            <div class="mt-2">
                                <x-badge color="neutral">
                                    {{ $cat->productos_count }} {{ $cat->productos_count == 1 ? 'producto' : 'productos' }}
                                </x-badge>
                            </div>
                        </div>
                        <div>
                            @if($cat->productos_count === 0)
                            <x-modal-confirm
                                title="¿Eliminar esta categoría?"
                                message="Se eliminará permanentemente la categoría «{{ $cat->nombre }}»."
                                action="{{ route('catalogos.categorias.destroy', $cat) }}"
                                method="DELETE"
                                confirmText="Eliminar"
                                variant="danger"
                            >
                                <x-slot:trigger>
                                    <x-icon-button type="button" variant="danger" icon="trash" title="Eliminar categoría" />
                                </x-slot:trigger>
                            </x-modal-confirm>
                            @else
                            <x-badge color="neutral" title="No se puede eliminar porque tiene productos vinculados">
                                En uso
                            </x-badge>
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

    <!-- TAB 2: MARCAS -->
    <div x-cloak x-show="activeTab === 'marcas'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-4">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h2 class="font-bold text-slate-900 text-sm">Nueva Marca</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Identifica fabricantes o marcas distribuidas.</p>
                </div>
                <form action="{{ route('catalogos.marcas.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="marca_nombre" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nombre de la Marca <span class="text-danger-text">*</span>
                        </label>
                        <input type="text" name="nombre" id="marca_nombre" required
                            placeholder="Ej: Nestlé, Stanley, Colanta"
                            class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                    </div>
                    <div>
                        <label for="marca_desc" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Descripción / Fabricante (Opcional)
                        </label>
                        <textarea name="descripcion" id="marca_desc" rows="3"
                            placeholder="Detalles del fabricante o distribuidor..."
                            class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs"></textarea>
                    </div>
                    <x-button variant="primary" type="submit" class="w-full justify-center">
                        Guardar Marca
                    </x-button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-8">
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50/70 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-600">Marcas Registradas</span>
                    <span class="text-xs text-slate-500 font-medium">Total: {{ $marcas->count() }}</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($marcas as $m)
                    <div class="p-4 sm:px-5 flex items-center justify-between hover:bg-slate-50/60 transition">
                        <div>
                            <div class="font-semibold text-slate-900 text-sm">{{ $m->nombre }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $m->descripcion ?? 'Sin descripción' }}</div>
                            <div class="mt-2">
                                <x-badge color="neutral">
                                    {{ $m->productos_count }} {{ $m->productos_count == 1 ? 'producto' : 'productos' }}
                                </x-badge>
                            </div>
                        </div>
                        <div>
                            @if($m->productos_count === 0)
                            <x-modal-confirm
                                title="¿Eliminar esta marca?"
                                message="Se eliminará permanentemente la marca «{{ $m->nombre }}»."
                                action="{{ route('catalogos.marcas.destroy', $m) }}"
                                method="DELETE"
                                confirmText="Eliminar"
                                variant="danger"
                            >
                                <x-slot:trigger>
                                    <x-icon-button type="button" variant="danger" icon="trash" title="Eliminar marca" />
                                </x-slot:trigger>
                            </x-modal-confirm>
                            @else
                            <x-badge color="neutral" title="No se puede eliminar porque tiene productos vinculados">
                                En uso
                            </x-badge>
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

    <!-- TAB 3: UNIDADES DE MEDIDA -->
    <div x-cloak x-show="activeTab === 'unidades'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-4">
            <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h2 class="font-bold text-slate-900 text-sm">Nueva Unidad</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Establece unidades base para inventario y ventas.</p>
                </div>
                <form action="{{ route('catalogos.unidades.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="uni_codigo" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Código Corto / Símbolo <span class="text-danger-text">*</span>
                        </label>
                        <input type="text" name="codigo" id="uni_codigo" required maxlength="10"
                            placeholder="Ej: UND, KG, LT, MT, CAJ"
                            class="block w-full px-3 py-2 uppercase font-mono border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                    </div>
                    <div>
                        <label for="uni_nombre" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nombre Completo <span class="text-danger-text">*</span>
                        </label>
                        <input type="text" name="nombre" id="uni_nombre" required
                            placeholder="Ej: Unidad, Kilogramo, Litro, Metro"
                            class="block w-full px-3 py-2 border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition shadow-xs">
                    </div>
                    <x-button variant="primary" type="submit" class="w-full justify-center">
                        Guardar Unidad
                    </x-button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-8">
            <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 bg-slate-50/70 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-600">Unidades de Medida Registradas</span>
                    <span class="text-xs text-slate-500 font-medium">Total: {{ $unidades->count() }}</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($unidades as $u)
                    <div class="p-4 sm:px-5 flex items-center justify-between hover:bg-slate-50/60 transition">
                        <div class="flex items-center space-x-3">
                            <span class="h-9 px-2.5 bg-slate-100 text-slate-700 font-mono font-semibold rounded-lg flex items-center justify-center text-xs border border-slate-200">
                                {{ $u->codigo }}
                            </span>
                            <div>
                                <div class="font-semibold text-slate-900 text-sm">{{ $u->nombre }}</div>
                                <div class="mt-1">
                                    <x-badge color="neutral">
                                        {{ $u->productos_count }} {{ $u->productos_count == 1 ? 'producto' : 'productos' }}
                                    </x-badge>
                                </div>
                            </div>
                        </div>
                        <div>
                            @if($u->productos_count === 0)
                            <x-modal-confirm
                                title="¿Eliminar esta unidad de medida?"
                                message="Se eliminará permanentemente la unidad «{{ $u->nombre }}»."
                                action="{{ route('catalogos.unidades.destroy', $u) }}"
                                method="DELETE"
                                confirmText="Eliminar"
                                variant="danger"
                            >
                                <x-slot:trigger>
                                    <x-icon-button type="button" variant="danger" icon="trash" title="Eliminar unidad" />
                                </x-slot:trigger>
                            </x-modal-confirm>
                            @else
                            <x-badge color="neutral" title="No se puede eliminar porque tiene productos vinculados">
                                En uso
                            </x-badge>
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