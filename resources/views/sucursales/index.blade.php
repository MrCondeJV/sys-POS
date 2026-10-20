@extends('layouts.app')

@section('title', 'Gestión de Sucursales')

@section('content')
<div class="space-y-6" x-data="{
    modalOpen: false,
    editMode: false,
    modalTitle: 'Nueva Sucursal',
    formAction: '{{ route('sucursales.store') }}',
    updateUrlBase: '{{ route('sucursales.update', '__SUCURSAL__') }}',
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
        this.formAction = this.updateUrlBase.replace('__SUCURSAL__', item.id);
        this.sucursal = { ...item, es_principal: Boolean(item.es_principal) };
        this.modalOpen = true;
    }
}">
    <!-- Header -->
    <x-page-header
        title="Sucursales y Puntos de Venta"
        subtitle="Puntos operativos y de atención comercial asociados a {{ $empresa->nombre_comercial }}.">
        <x-slot:actions>
            <button @click="openCreateModal()" type="button" class="btn-primary">
                <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Sucursal
            </button>
        </x-slot:actions>
    </x-page-header>

    <!-- Lista de Sucursales -->
    <div class="card p-0 overflow-hidden">
        <!-- Vista Desktop -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Sucursal</th>
                        <th>Código</th>
                        <th>Ubicación</th>
                        <th>Contacto</th>
                        <th>Tipo</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sucursales as $s)
                    <tr>
                        <td>
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded bg-slate-100 text-slate-700 font-bold flex items-center justify-center mr-3 flex-shrink-0 border border-slate-200">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-900 text-sm">{{ $s->nombre }}</div>
                                    @if(\App\Support\Tenancy\BranchContext::getId() === $s->id)
                                    <span class="inline-flex items-center text-[11px] font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1"></span> Sede Activa
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-xs text-slate-600 font-mono">
                            {{ $s->codigo ?? '—' }}
                        </td>
                        <td class="text-xs text-slate-600">
                            <div class="font-medium text-slate-800">{{ $s->ciudad ?? 'N/A' }}</div>
                            <div class="text-slate-400 text-[11px]">{{ $s->direccion ?? 'Sin dirección' }}</div>
                        </td>
                        <td class="text-xs text-slate-600 font-mono">
                            {{ $s->telefono ?? '—' }}
                        </td>
                        <td>
                            @if($s->es_principal)
                            <span class="badge badge-warning">
                                Sede Principal
                            </span>
                            @else
                            <span class="badge badge-neutral">
                                Sucursal
                            </span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end space-x-1.5">
                                @if(\App\Support\Tenancy\BranchContext::getId() !== $s->id)
                                <form action="{{ route('sucursales.seleccionar') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="sucursal_id" value="{{ $s->id }}">
                                    <button type="submit" class="btn-secondary py-1 px-2.5 text-xs text-emerald-700 hover:text-emerald-800">
                                        Usar esta
                                    </button>
                                </form>
                                @endif

                                <button @click='openEditModal(@json($s))' type="button" class="btn-secondary py-1 px-2.5 text-xs">
                                    Editar
                                </button>

                                @if(!$s->es_principal)
                                <x-modal-confirm
                                    title="¿Eliminar sucursal?"
                                    message="Se eliminará la sucursal «{{ $s->nombre }}». Esta acción solo es permitida si no posee inventario ni documentos asociados."
                                    action="{{ route('sucursales.destroy', $s) }}"
                                    method="DELETE"
                                    confirmText="Eliminar Sucursal"
                                    variant="danger"
                                >
                                    <x-slot:trigger>
                                        <button type="button" class="btn-danger py-1 px-2 text-xs">
                                            Eliminar
                                        </button>
                                    </x-slot:trigger>
                                </x-modal-confirm>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12">
                            <div class="empty-state">
                                <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                                </svg>
                                <p class="empty-state-title">No hay sucursales registradas</p>
                                <p class="empty-state-desc">Haz clic en "Nueva Sucursal" para agregar la primera sede operativa.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vista Móvil y Tablet (Cards) -->
        <div class="lg:hidden divide-y divide-slate-100">
            @forelse($sucursales as $s)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="font-semibold text-slate-900 text-sm">{{ $s->nombre }}</div>
                        <div class="text-xs text-slate-500 font-mono mt-0.5">Código: {{ $s->codigo ?? 'N/A' }}</div>
                    </div>
                    @if($s->es_principal)
                    <span class="badge badge-warning">
                        Principal
                    </span>
                    @endif
                </div>

                <div class="text-xs text-slate-600 space-y-1">
                    <div class="flex items-center">
                        <svg class="h-3.5 w-3.5 mr-1.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        {{ $s->ciudad ?? 'Ciudad no especificada' }} - {{ $s->direccion ?? 'Sin dirección' }}
                    </div>
                    @if($s->telefono)
                    <div class="flex items-center">
                        <svg class="h-3.5 w-3.5 mr-1.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span class="font-mono">{{ $s->telefono }}</span>
                    </div>
                    @endif
                </div>

                <div class="pt-2 flex items-center justify-between border-t border-slate-100">
                    <div>
                        @if(\App\Support\Tenancy\BranchContext::getId() === $s->id)
                        <span class="text-xs font-semibold text-emerald-700 flex items-center">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Activa ahora
                        </span>
                        @else
                        <form action="{{ route('sucursales.seleccionar') }}" method="POST">
                            @csrf
                            <input type="hidden" name="sucursal_id" value="{{ $s->id }}">
                            <button type="submit" class="btn-secondary py-1 px-2.5 text-xs text-emerald-700">
                                Activar
                            </button>
                        </form>
                        @endif
                    </div>

                    <div class="flex items-center space-x-2">
                        <button @click='openEditModal(@json($s))' type="button" class="btn-secondary py-1 px-2 text-xs">
                            Editar
                        </button>

                        @if(!$s->es_principal)
                        <x-modal-confirm
                            title="¿Eliminar sucursal?"
                            message="Se eliminará la sucursal «{{ $s->nombre }}»."
                            action="{{ route('sucursales.destroy', $s) }}"
                            method="DELETE"
                            confirmText="Eliminar"
                            variant="danger"
                        >
                            <x-slot:trigger>
                                <button type="button" class="btn-danger py-1 px-2 text-xs">
                                    Eliminar
                                </button>
                            </x-slot:trigger>
                        </x-modal-confirm>
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
                class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200">

                <form :action="formAction" method="POST">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
                        <h3 class="text-base font-semibold text-slate-900" x-text="modalTitle"></h3>
                        <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div>
                            <label class="label-base">
                                Nombre de la Sucursal <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nombre" x-model="sucursal.nombre" required
                                class="input text-xs"
                                placeholder="Ej: Sede Norte">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label-base">
                                    Código Interno
                                </label>
                                <input type="text" name="codigo" x-model="sucursal.codigo"
                                    class="input text-xs font-mono"
                                    placeholder="SUC-002">
                            </div>

                            <div>
                                <label class="label-base">
                                    Teléfono
                                </label>
                                <input type="text" name="telefono" x-model="sucursal.telefono"
                                    class="input text-xs font-mono"
                                    placeholder="3001234567">
                            </div>
                        </div>

                        <div>
                            <label class="label-base">
                                Dirección
                            </label>
                            <input type="text" name="direccion" x-model="sucursal.direccion"
                                class="input text-xs"
                                placeholder="Calle 50 # 10-20">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label-base">
                                    Ciudad
                                </label>
                                <input type="text" name="ciudad" x-model="sucursal.ciudad"
                                    class="input text-xs"
                                    placeholder="Medellín">
                            </div>

                            <div>
                                <label class="label-base">
                                    Departamento
                                </label>
                                <input type="text" name="departamento" x-model="sucursal.departamento"
                                    class="input text-xs"
                                    placeholder="Antioquia">
                            </div>
                        </div>

                        <div class="pt-2">
                            <label class="flex items-center space-x-2.5 cursor-pointer">
                                <input type="checkbox" name="es_principal" value="1" x-model="sucursal.es_principal"
                                    class="h-4 w-4 text-brand-600 focus:ring-brand-500 border-slate-300 rounded">
                                <span class="text-xs font-medium text-slate-800">Designar como Sede Principal</span>
                            </label>
                            <p class="text-[11px] text-slate-400 mt-1 ml-6.5">
                                Si se activa, reemplazará automáticamente a cualquier otra sede principal actual.
                            </p>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-3">
                        <button type="button" @click="modalOpen = false" class="btn-secondary">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary">
                            Guardar Sucursal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
