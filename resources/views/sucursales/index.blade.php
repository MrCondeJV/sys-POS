@extends('layouts.app')

@section('title', 'Gestión de Sucursales')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{
    modalOpen: false,
    editMode: false,
    modalTitle: 'Nueva Sucursal',
    formAction: '{{ route('sucursales.store') }}',
    sucursal: {
        id: null,
        nombre: '',
        codigo: '',
        direccion: '',
        telefono: '',
        ciudad: '',
        departamento: '',
        es_principal: false
    },
    openCreateModal() {
        this.editMode = false;
        this.modalTitle = 'Crear Nueva Sucursal';
        this.formAction = '{{ route('sucursales.store') }}';
        this.sucursal = { id: null, nombre: '', codigo: '', direccion: '', telefono: '', ciudad: '', departamento: '', es_principal: false };
        this.modalOpen = true;
    },
    openEditModal(item) {
        this.editMode = true;
        this.modalTitle = 'Editar Sucursal: ' + item.nombre;
        this.formAction = '/sucursales/' + item.id;
        this.sucursal = { ...item, es_principal: Boolean(item.es_principal) };
        this.modalOpen = true;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Sucursales y Puntos de Venta</h1>
            <p class="text-sm text-slate-500 mt-1">
                Puntos operativos y de atención comercial asociados a {{ $empresa->nombre_comercial }}.
            </p>
        </div>
        <div>
            <button @click="openCreateModal()" type="button"
                class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Sucursal
            </button>
        </div>
    </div>

    <!-- Lista de Sucursales (Diseño adaptable: Cards en móvil / Tabla en desktop) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Vista Desktop (Optimizada al 100% sin scroll horizontal) -->
        <div class="hidden lg:block">
            <table class="w-full divide-y divide-slate-200 table-auto">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Sucursal</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Ubicación</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Contacto</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($sucursales as $s)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-9 w-9 rounded-xl bg-indigo-50 text-indigo-700 font-bold flex items-center justify-center mr-3 flex-shrink-0">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800 text-sm">{{ $s->nombre }}</div>
                                    @if(\App\Support\Tenancy\BranchContext::getId() === $s->id)
                                    <span class="inline-flex items-center text-[11px] font-semibold text-emerald-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1"></span> Sucursal Activa
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 font-mono">
                            {{ $s->codigo ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                            <div>{{ $s->ciudad ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-400">{{ $s->direccion ?? 'Sin dirección' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                            {{ $s->telefono ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($s->es_principal)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                Sede Principal
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                Sucursal
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            @if(\App\Support\Tenancy\BranchContext::getId() !== $s->id)
                            <form action="{{ route('sucursales.seleccionar') }}" method="POST" class="inline">
                                @csrf
                                <input type="hidden" name="sucursal_id" value="{{ $s->id }}">
                                <button type="submit" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2.5 py-1 rounded-lg transition">
                                    Usar esta
                                </button>
                            </form>
                            @endif

                            <button @click='openEditModal(@json($s))' type="button"
                                class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-lg transition">
                                Editar
                            </button>

                            @if(!$s->es_principal)
                            <form action="{{ route('sucursales.destroy', $s) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro que deseas eliminar esta sucursal?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-2.5 py-1 rounded-lg transition">
                                    Eliminar
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500">
                            No hay sucursales registradas aún. Haz clic en "Nueva Sucursal" para agregar la primera.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vista Móvil y Tablet (Cards responsivas táctiles) -->
        <div class="lg:hidden divide-y divide-slate-100">
            @forelse($sucursales as $s)
            <div class="p-5 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="font-bold text-slate-900 text-base">{{ $s->nombre }}</div>
                        <div class="text-xs text-slate-500 font-mono mt-0.5">Código: {{ $s->codigo ?? 'N/A' }}</div>
                    </div>
                    @if($s->es_principal)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                        Principal
                    </span>
                    @endif
                </div>

                <div class="text-xs text-slate-600 space-y-1">
                    <div class="flex items-center">
                        <svg class="h-4 w-4 mr-1.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        {{ $s->ciudad ?? 'Ciudad no especificada' }} - {{ $s->direccion ?? 'Sin dirección' }}
                    </div>
                    @if($s->telefono)
                    <div class="flex items-center">
                        <svg class="h-4 w-4 mr-1.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        {{ $s->telefono }}
                    </div>
                    @endif
                </div>

                <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                    <div>
                        @if(\App\Support\Tenancy\BranchContext::getId() === $s->id)
                        <span class="text-xs font-bold text-emerald-600 flex items-center">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 mr-1.5"></span> Activa ahora
                        </span>
                        @else
                        <form action="{{ route('sucursales.seleccionar') }}" method="POST">
                            @csrf
                            <input type="hidden" name="sucursal_id" value="{{ $s->id }}">
                            <button type="submit" class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg">
                                Activar
                            </button>
                        </form>
                        @endif
                    </div>

                    <div class="flex items-center space-x-2">
                        <button @click='openEditModal(@json($s))' type="button"
                            class="text-xs font-semibold text-indigo-700 bg-indigo-50 px-3 py-1.5 rounded-lg">
                            Editar
                        </button>

                        @if(!$s->es_principal)
                        <form action="{{ route('sucursales.destroy', $s) }}" method="POST" onsubmit="return confirm('¿Eliminar esta sucursal?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-700 bg-red-50 px-3 py-1.5 rounded-lg">
                                Eliminar
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="p-6 text-center text-sm text-slate-500">
                No hay sucursales registradas.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Responsivo para Crear / Editar Sucursal -->
    <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Backdrop -->
            <div x-show="modalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="modalOpen = false"></div>

            <!-- Modal Panel -->
            <div x-show="modalOpen"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-100">

                <form :action="formAction" method="POST">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="px-6 pt-6 pb-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900" x-text="modalTitle"></h3>
                        <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nombre de la Sucursal <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nombre" x-model="sucursal.nombre" required
                                class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Ej: Sede Norte">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Código Interno
                                </label>
                                <input type="text" name="codigo" x-model="sucursal.codigo"
                                    class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="SUC-002">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Teléfono
                                </label>
                                <input type="text" name="telefono" x-model="sucursal.telefono"
                                    class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="3001234567">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Dirección
                            </label>
                            <input type="text" name="direccion" x-model="sucursal.direccion"
                                class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Calle 50 # 10-20">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Ciudad
                                </label>
                                <input type="text" name="ciudad" x-model="sucursal.ciudad"
                                    class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Medellín">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Departamento
                                </label>
                                <input type="text" name="departamento" x-model="sucursal.departamento"
                                    class="w-full border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Antioquia">
                            </div>
                        </div>

                        <div class="pt-2">
                            <label class="flex items-center space-x-3 cursor-pointer">
                                <input type="checkbox" name="es_principal" value="1" x-model="sucursal.es_principal"
                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded">
                                <span class="text-sm font-semibold text-slate-700">Designar como Sede Principal</span>
                            </label>
                            <p class="text-xs text-slate-400 mt-1 ml-7">
                                Si se activa, reemplazará automáticamente a cualquier otra sede principal actual.
                            </p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end space-x-3 rounded-b-3xl">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-white transition">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-sm font-bold text-white shadow-sm transition">
                            Guardar Sucursal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
