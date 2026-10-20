@extends('layouts.app')

@section('title', 'Catálogo de Proveedores')

@section('content')
<div class="space-y-6" x-data="{
    modalOpen: false,
    editMode: false,
    modalTitle: 'Nuevo Proveedor',
    formAction: '{{ route('proveedores.store') }}',
    updateUrlTemplate: '{{ route('proveedores.update', '__PROVEEDOR__') }}',
    proveedor: {
        id: null,
        razon_social: '',
        nombre_contacto: '',
        tipo_documento: 'NIT',
        numero_documento: '',
        telefono: '',
        email: '',
        direccion: '',
        ciudad: '',
        departamento: '',
        estado: 'ACTIVO'
    },
    openCreateModal() {
        this.editMode = false;
        this.modalTitle = 'Registrar Nuevo Proveedor';
        this.formAction = '{{ route('proveedores.store') }}';
        this.proveedor = {
            id: null,
            razon_social: '',
            nombre_contacto: '',
            tipo_documento: 'NIT',
            numero_documento: '',
            telefono: '',
            email: '',
            direccion: '',
            ciudad: '',
            departamento: '',
            estado: 'ACTIVO'
        };
        this.modalOpen = true;
    },
    openEditModal(item) {
        this.editMode = true;
        this.modalTitle = 'Editar Proveedor: ' + item.razon_social;
        this.formAction = this.updateUrlTemplate.replace('__PROVEEDOR__', item.id);
        this.proveedor = {
            id: item.id,
            razon_social: item.razon_social || '',
            nombre_contacto: item.nombre_contacto || '',
            tipo_documento: (typeof item.tipo_documento === 'object' && item.tipo_documento !== null) ? item.tipo_documento.value : (item.tipo_documento || 'NIT'),
            numero_documento: item.numero_documento || '',
            telefono: item.telefono || '',
            email: item.email || '',
            direccion: item.direccion || '',
            ciudad: item.ciudad || '',
            departamento: item.departamento || '',
            estado: (typeof item.estado === 'object' && item.estado !== null) ? item.estado.value : (item.estado || 'ACTIVO')
        };
        this.modalOpen = true;
    }
}">

    <!-- Encabezado de la página -->
    <x-page-header
        title="Proveedores Comerciales"
        subtitle="Administración de socios comerciales, suministradores y acreedores para compras de mercancías.">
        <x-slot:actions>
            <button @click="openCreateModal()" type="button" class="btn-primary">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Nuevo Proveedor
            </button>
        </x-slot:actions>
    </x-page-header>

    <!-- Tarjetas de Métricas Rápidas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-kpi-card
            label="Total Proveedores"
            value="{{ number_format($totalProveedores) }}"
            context="Registrados en la empresa"
            icon="users"
        />

        <x-kpi-card
            label="Proveedores Activos"
            value="{{ number_format($totalActivos) }}"
            context="Habilitados para compras"
            badge="Operativos"
            variant="success"
            icon="check"
        />

        <x-kpi-card
            label="Compras Registradas"
            value="{{ number_format(\App\Models\Compra::count()) }}"
            context="Órdenes y facturas de compra"
            icon="credit-card"
        />

        <div class="card p-5 flex items-center justify-between">
            <div>
                <div class="kpi-label">Acceso Directo</div>
                <div class="text-sm font-semibold text-slate-800 mt-1">Registrar Compra</div>
                <a href="{{ route('compras.create') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-800 inline-flex items-center mt-2 group">
                    Ir a Facturación <span class="group-hover:translate-x-0.5 transition-transform ml-1">&rarr;</span>
                </a>
            </div>
            <div class="p-2.5 rounded-lg bg-brand-50 text-brand-700 border border-brand-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <x-filter-bar
        :action="route('proveedores.index')"
        :has-active-filters="!empty($term) || !empty($estado)"
        :clear-url="route('proveedores.index')"
    >
        <div class="sm:col-span-12 lg:col-span-7 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" name="buscar" value="{{ $term }}"
                placeholder="Buscar por razón social, NIT/documento, contacto, email..."
                class="input pl-9">
        </div>

        <div class="sm:col-span-6 lg:col-span-3">
            <select name="estado" onchange="this.form.submit()" class="input">
                <option value="">Todos los Estados</option>
                <option value="ACTIVO" {{ $estado === 'ACTIVO' ? 'selected' : '' }}>Solo Activos</option>
                <option value="INACTIVO" {{ $estado === 'INACTIVO' ? 'selected' : '' }}>Solo Inactivos</option>
            </select>
        </div>
    </x-filter-bar>

    <!-- Tabla Desktop / Tarjetas Mobile -->
    <div class="card p-0 overflow-hidden">
        <!-- Vista Desktop -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Identificación</th>
                        <th>Razón Social & Contacto</th>
                        <th>Comunicación</th>
                        <th>Ubicación</th>
                        <th class="text-center">Compras</th>
                        <th class="text-center">Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proveedores as $p)
                    <tr>
                        <!-- Identificación -->
                        <td class="font-mono text-xs text-slate-700">
                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold">
                                {{ $p->tipo_documento?->value ?? 'DOC' }}: {{ $p->numero_documento }}
                            </span>
                        </td>

                        <!-- Razón Social & Contacto -->
                        <td>
                            <div class="font-semibold text-slate-900 text-sm">{{ $p->razon_social }}</div>
                            @if($p->nombre_contacto)
                                <div class="text-xs text-slate-500 flex items-center mt-0.5">
                                    <svg class="h-3.5 w-3.5 mr-1 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ $p->nombre_contacto }}
                                </div>
                            @endif
                        </td>

                        <!-- Comunicación -->
                        <td class="text-xs text-slate-600">
                            <div class="tabular-nums font-mono">{{ $p->telefono ?? '—' }}</div>
                            @if($p->email)
                                <div class="text-slate-500 truncate max-w-xs">{{ $p->email }}</div>
                            @endif
                        </td>

                        <!-- Ubicación -->
                        <td class="text-xs text-slate-600">
                            <div class="font-medium text-slate-800">{{ $p->ciudad ?? 'N/A' }}{{ $p->departamento ? ', ' . $p->departamento : '' }}</div>
                            <div class="text-slate-400 truncate max-w-xs">{{ $p->direccion ?? 'Sin dirección' }}</div>
                        </td>

                        <!-- Compras Asociadas -->
                        <td class="text-center">
                            <span class="badge {{ $p->compras_count > 0 ? 'badge-brand' : 'badge-neutral' }} tabular-nums">
                                {{ $p->compras_count }} facturas
                            </span>
                        </td>

                        <!-- Estado -->
                        <td class="text-center">
                            @if($p->isActivo())
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>

                        <!-- Acciones -->
                        <td class="text-right">
                            <div class="flex items-center justify-end space-x-1">
                                <button type="button" @click='openEditModal(@json($p))'
                                    class="p-1.5 text-slate-500 hover:text-brand-600 hover:bg-slate-100 rounded transition"
                                    title="Editar proveedor">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>

                                <x-modal-confirm
                                    name="delete-proveedor-{{ $p->id }}"
                                    title="Eliminar proveedor {{ $p->razon_social }}"
                                    message="¿Estás seguro de que deseas eliminar este proveedor del catálogo?"
                                    consequence="Se aplicará eliminación lógica (soft delete). Las facturas de compra anteriores registradas con este proveedor se conservarán intactas para auditoría."
                                    confirmText="Eliminar proveedor"
                                    variant="danger"
                                    :action="route('proveedores.destroy', $p)"
                                    method="DELETE"
                                >
                                    <x-slot:trigger>
                                        <button type="button"
                                            class="p-1.5 text-slate-400 hover:text-danger-text hover:bg-danger-bg rounded transition"
                                            title="Eliminar proveedor">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </x-slot:trigger>
                                </x-modal-confirm>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <div class="empty-state">
                                <svg class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                                </svg>
                                <p class="empty-state-title">No se encontraron proveedores</p>
                                <p class="empty-state-desc">Registra tu primer proveedor para comenzar a registrar compras de inventario.</p>
                                <button @click="openCreateModal()" type="button" class="btn-primary mt-4">
                                    Nuevo Proveedor
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vista Móvil / Tablet (Cards) -->
        <div class="block lg:hidden divide-y divide-slate-100">
            @forelse($proveedores as $p)
            <div class="p-4 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="font-semibold text-slate-900 text-sm">{{ $p->razon_social }}</div>
                        <div class="text-xs text-slate-500 font-mono mt-0.5">
                            {{ $p->tipo_documento?->value ?? 'DOC' }}: {{ $p->numero_documento }}
                        </div>
                    </div>
                    @if($p->isActivo())
                        <span class="badge badge-success">Activo</span>
                    @else
                        <span class="badge badge-danger">Inactivo</span>
                    @endif
                </div>

                <div class="text-xs text-slate-600 space-y-1">
                    @if($p->nombre_contacto)
                        <div class="flex items-center text-slate-700">
                            <span class="font-medium text-slate-400 mr-2">Contacto:</span> {{ $p->nombre_contacto }}
                        </div>
                    @endif
                    <div class="flex items-center">
                        <span class="font-medium text-slate-400 mr-2">Tel:</span> <span class="tabular-nums font-mono">{{ $p->telefono ?? '—' }}</span>
                    </div>
                    @if($p->email)
                        <div class="flex items-center">
                            <span class="font-medium text-slate-400 mr-2">Email:</span> {{ $p->email }}
                        </div>
                    @endif
                    <div class="flex items-center">
                        <span class="font-medium text-slate-400 mr-2">Ubicación:</span>
                        {{ $p->ciudad ?? 'N/A' }}{{ $p->departamento ? ' (' . $p->departamento . ')' : '' }}
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <span class="text-xs text-slate-500 font-medium tabular-nums">
                        {{ $p->compras_count }} facturas registradas
                    </span>
                    <div class="flex items-center space-x-3">
                        <button type="button" @click='openEditModal(@json($p))'
                            class="text-xs font-semibold text-brand-600 hover:text-brand-800">
                            Editar
                        </button>
                        <button type="button" @click="$dispatch('open-modal-delete-proveedor-{{ $p->id }}', { triggerEl: $el })"
                            class="text-xs font-semibold text-danger-text hover:opacity-80">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-500 text-sm">
                No hay proveedores registrados.
            </div>
            @endforelse
        </div>

        @if($proveedores->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $proveedores->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Dinámico de Proveedor (Crear / Editar) con Alpine.js -->
    <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div x-show="modalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
             @click="modalOpen = false"></div>

        <div class="flex min-h-screen items-center justify-center p-4 text-center">
            <div x-show="modalOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
                 class="relative w-full max-w-2xl transform overflow-hidden rounded-xl bg-white text-left shadow-2xl transition-all border border-slate-200">

                <form :action="formAction" method="POST">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 bg-slate-50/50">
                        <h3 class="text-base font-semibold text-slate-900" x-text="modalTitle"></h3>
                        <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 transition">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Razón Social -->
                            <div class="sm:col-span-2">
                                <label class="label-base">
                                    Razón Social / Nombre Comercial <span class="text-danger-text">*</span>
                                </label>
                                <input type="text" name="razon_social" x-model="proveedor.razon_social" required
                                    placeholder="Ej: Distribuciones Andinas S.A.S."
                                    class="input">
                            </div>

                            <!-- Tipo Documento -->
                            <div>
                                <label class="label-base">
                                    Tipo de Documento <span class="text-danger-text">*</span>
                                </label>
                                <select name="tipo_documento" x-model="proveedor.tipo_documento" required class="input">
                                    <option value="NIT">NIT - Número de Identificación Tributaria</option>
                                    <option value="CC">CC - Cédula de Ciudadanía</option>
                                    <option value="CE">CE - Cédula de Extranjería</option>
                                    <option value="RUT">RUT - Registro Único Tributario</option>
                                    <option value="PASAPORTE">Pasaporte</option>
                                </select>
                            </div>

                            <!-- Número Documento -->
                            <div>
                                <label class="label-base">
                                    Número de Documento / NIT <span class="text-danger-text">*</span>
                                </label>
                                <input type="text" name="numero_documento" x-model="proveedor.numero_documento" required
                                    placeholder="Ej: 901234567-8"
                                    class="input font-mono">
                            </div>

                            <!-- Nombre Contacto -->
                            <div>
                                <label class="label-base">
                                    Nombre de Contacto / Vendedor
                                </label>
                                <input type="text" name="nombre_contacto" x-model="proveedor.nombre_contacto"
                                    placeholder="Ej: Carlos Gómez"
                                    class="input">
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <label class="label-base">
                                    Teléfono / Celular
                                </label>
                                <input type="text" name="telefono" x-model="proveedor.telefono"
                                    placeholder="Ej: +57 300 123 4567"
                                    class="input font-mono">
                            </div>

                            <!-- Email -->
                            <div class="sm:col-span-2">
                                <label class="label-base">
                                    Correo Electrónico
                                </label>
                                <input type="email" name="email" x-model="proveedor.email"
                                    placeholder="Ej: ventas@proveedor.com"
                                    class="input">
                            </div>

                            <!-- Dirección -->
                            <div class="sm:col-span-2">
                                <label class="label-base">
                                    Dirección Comercial
                                </label>
                                <input type="text" name="direccion" x-model="proveedor.direccion"
                                    placeholder="Ej: Calle 45 # 12-34 Bodega 3"
                                    class="input">
                            </div>

                            <!-- Ciudad -->
                            <div>
                                <label class="label-base">
                                    Ciudad
                                </label>
                                <input type="text" name="ciudad" x-model="proveedor.ciudad"
                                    placeholder="Ej: Bogotá"
                                    class="input">
                            </div>

                            <!-- Departamento -->
                            <div>
                                <label class="label-base">
                                    Departamento / Provincia
                                </label>
                                <input type="text" name="departamento" x-model="proveedor.departamento"
                                    placeholder="Ej: Cundinamarca"
                                    class="input">
                            </div>

                            <!-- Estado -->
                            <div class="sm:col-span-2">
                                <label class="label-base">
                                    Estado Operativo <span class="text-danger-text">*</span>
                                </label>
                                <select name="estado" x-model="proveedor.estado" required class="input">
                                    <option value="ACTIVO">ACTIVO (Habilitado para emitir compras)</option>
                                    <option value="INACTIVO">INACTIVO (Suspendido temporalmente)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="border-t border-slate-200 px-6 py-4 bg-slate-50 flex items-center justify-end space-x-3">
                        <button type="button" @click="modalOpen = false" class="btn-secondary">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary">
                            Guardar Proveedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
