@extends('layouts.app')

@section('title', 'Catálogo de Proveedores')

@section('content')
<div class="max-w-[1680px] mx-auto space-y-6" x-data="{
    modalOpen: false,
    editMode: false,
    modalTitle: 'Nuevo Proveedor',
    formAction: '{{ route('proveedores.store') }}',
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
        this.formAction = '/proveedores/' + item.id;
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
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    Fase 6
                </span>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Proveedores Comerciales</h1>
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Administración de socios comerciales, suministradores y acreedores para compras de mercancías.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openCreateModal()" type="button"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Nuevo Proveedor
            </button>
        </div>
    </div>

    <!-- Tarjetas de Métricas Rápidas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Proveedores</div>
                <div class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format($totalProveedores) }}</div>
            <div class="text-xs text-slate-500 mt-1">Registrados en la empresa</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Proveedores Activos</div>
                <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-emerald-600">{{ number_format($totalActivos) }}</div>
            <div class="text-xs text-slate-500 mt-1">Habilitados para compras</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Compras Relacionadas</div>
                <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-blue-600">
                {{ number_format(\App\Models\Compra::count()) }}
            </div>
            <div class="text-xs text-slate-500 mt-1">Órdenes y facturas de compra</div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Acceso Rápido</div>
                <div class="text-sm font-semibold text-slate-800 mt-1">Registrar Compra</div>
                <a href="{{ route('compras.create') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 inline-flex items-center mt-2">
                    Ir a Facturación &rarr;
                </a>
            </div>
            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
        <form action="{{ route('proveedores.index') }}" method="GET" class="flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="buscar" value="{{ $term }}"
                    placeholder="Buscar por razón social, NIT/documento, contacto, email..."
                    class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
            </div>

            <div class="w-full md:w-48">
                <select name="estado" onchange="this.form.submit()"
                    class="w-full py-2 px-3 text-sm bg-slate-50 border border-slate-300 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                    <option value="">Todos los Estados</option>
                    <option value="ACTIVO" {{ $estado === 'ACTIVO' ? 'selected' : '' }}>Solo Activos</option>
                    <option value="INACTIVO" {{ $estado === 'INACTIVO' ? 'selected' : '' }}>Solo Inactivos</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition">
                    Filtrar
                </button>
                @if($term || $estado)
                    <a href="{{ route('proveedores.index') }}"
                        class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-semibold transition inline-flex items-center">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Contenedor Principal Adaptable (Mobile Cards / Desktop Table) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Vista Desktop (100% de ancho sin scroll horizontal) -->
        <div class="hidden lg:block">
            <table class="w-full divide-y divide-slate-200 table-auto">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Identificación</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Razón Social & Contacto</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Comunicación</th>
                        <th class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Ubicación</th>
                        <th class="px-5 py-3.5 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Compras</th>
                        <th class="px-5 py-3.5 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Estado</th>
                        <th class="px-5 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($proveedores as $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <!-- Identificación -->
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 font-mono">
                                {{ $p->tipo_documento?->value ?? 'DOC' }}: {{ $p->numero_documento }}
                            </span>
                        </td>

                        <!-- Razón Social & Contacto -->
                        <td class="px-5 py-4 whitespace-nowrap">
                            <div class="font-bold text-slate-900 text-sm">{{ $p->razon_social }}</div>
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
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600">
                            <div>{{ $p->telefono ?? '—' }}</div>
                            @if($p->email)
                                <div class="text-xs text-indigo-600 truncate max-w-xs">{{ $p->email }}</div>
                            @endif
                        </td>

                        <!-- Ubicación -->
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600">
                            <div>{{ $p->ciudad ?? 'N/A' }}{{ $p->departamento ? ', ' . $p->departamento : '' }}</div>
                            <div class="text-xs text-slate-400 truncate max-w-xs">{{ $p->direccion ?? 'Sin dirección' }}</div>
                        </td>

                        <!-- Compras Asociadas -->
                        <td class="px-5 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $p->compras_count > 0 ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $p->compras_count }} facturas
                            </span>
                        </td>

                        <!-- Estado -->
                        <td class="px-5 py-4 whitespace-nowrap text-center">
                            @if($p->isActivo())
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Activo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-red-500 mr-1.5"></span> Inactivo
                                </span>
                            @endif
                        </td>

                        <!-- Acciones -->
                        <td class="px-5 py-4 whitespace-nowrap text-right text-sm">
                            <div class="flex items-center justify-end space-x-2">
                                <button type="button" @click='openEditModal(@json($p))'
                                    class="p-1.5 text-slate-500 hover:text-indigo-600 rounded-lg hover:bg-slate-100 transition"
                                    title="Editar proveedor">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>

                                <form action="{{ route('proveedores.destroy', $p) }}" method="POST"
                                    onsubmit="return confirm('¿Estás seguro de eliminar este proveedor? Sus registros de compras anteriores se preservarán para auditoría.');"
                                    class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg hover:bg-slate-100 transition"
                                        title="Eliminar proveedor">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <div class="max-w-sm mx-auto">
                                <svg class="h-12 w-12 mx-auto text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                                </svg>
                                <p class="text-sm font-semibold text-slate-700">No se encontraron proveedores</p>
                                <p class="text-xs text-slate-400 mt-1">Registra tu primer proveedor para comenzar a registrar compras de inventario.</p>
                                <button @click="openCreateModal()" type="button" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition">
                                    Nuevo Proveedor
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vista Móvil / Tablet (Cards modernas sin desbordamiento horizontal) -->
        <div class="block lg:hidden divide-y divide-slate-100">
            @forelse($proveedores as $p)
            <div class="p-4 sm:p-5 space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="font-bold text-slate-900 text-base">{{ $p->razon_social }}</div>
                        <div class="text-xs text-slate-500 font-mono mt-0.5">
                            {{ $p->tipo_documento?->value ?? 'DOC' }}: {{ $p->numero_documento }}
                        </div>
                    </div>
                    @if($p->isActivo())
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                            Activo
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700">
                            Inactivo
                        </span>
                    @endif
                </div>

                <div class="text-xs text-slate-600 space-y-1">
                    @if($p->nombre_contacto)
                        <div class="flex items-center text-slate-700">
                            <span class="font-medium text-slate-400 mr-2">Contacto:</span> {{ $p->nombre_contacto }}
                        </div>
                    @endif
                    <div class="flex items-center">
                        <span class="font-medium text-slate-400 mr-2">Tel:</span> {{ $p->telefono ?? '—' }}
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
                    <span class="text-xs text-slate-500 font-medium">
                        {{ $p->compras_count }} facturas registradas
                    </span>
                    <div class="flex items-center space-x-3">
                        <button type="button" @click='openEditModal(@json($p))'
                            class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                            Editar
                        </button>
                        <form action="{{ route('proveedores.destroy', $p) }}" method="POST"
                            onsubmit="return confirm('¿Eliminar proveedor?');" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-bold text-red-500 hover:text-red-700">
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="p-8 text-center text-slate-400 text-sm">
                No hay proveedores registrados.
            </div>
            @endforelse
        </div>

        @if($proveedores->hasPages())
        <div class="p-4 border-t border-slate-100">
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
                 class="relative w-full max-w-2xl transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all border border-slate-200">

                <form :action="formAction" method="POST">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- Modal Header -->
                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                        <h3 class="text-lg font-bold text-slate-900" x-text="modalTitle"></h3>
                        <button type="button" @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 transition">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Razón Social -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Razón Social / Nombre Comercial <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="razon_social" x-model="proveedor.razon_social" required
                                    placeholder="Ej: Distribuciones Andinas S.A.S."
                                    class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            </div>

                            <!-- Tipo Documento -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Tipo de Documento <span class="text-red-500">*</span>
                                </label>
                                <select name="tipo_documento" x-model="proveedor.tipo_documento" required
                                    class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                                    <option value="NIT">NIT - Número de Identificación Tributaria</option>
                                    <option value="CC">CC - Cédula de Ciudadanía</option>
                                    <option value="CE">CE - Cédula de Extranjería</option>
                                    <option value="RUT">RUT - Registro Único Tributario</option>
                                    <option value="PASAPORTE">Pasaporte</option>
                                </select>
                            </div>

                            <!-- Número Documento -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Número de Documento / NIT <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="numero_documento" x-model="proveedor.numero_documento" required
                                    placeholder="Ej: 901234567-8"
                                    class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            </div>

                            <!-- Nombre Contacto -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Nombre de Contacto / Vendedor
                                </label>
                                <input type="text" name="nombre_contacto" x-model="proveedor.nombre_contacto"
                                    placeholder="Ej: Carlos Gómez"
                                    class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Teléfono / Celular
                                </label>
                                <input type="text" name="telefono" x-model="proveedor.telefono"
                                    placeholder="Ej: +57 300 123 4567"
                                    class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            </div>

                            <!-- Email -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Correo Electrónico
                                </label>
                                <input type="email" name="email" x-model="proveedor.email"
                                    placeholder="Ej: ventas@proveedor.com"
                                    class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            </div>

                            <!-- Dirección -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Dirección Comercial
                                </label>
                                <input type="text" name="direccion" x-model="proveedor.direccion"
                                    placeholder="Ej: Calle 45 # 12-34 Bodega 3"
                                    class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            </div>

                            <!-- Ciudad -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Ciudad
                                </label>
                                <input type="text" name="ciudad" x-model="proveedor.ciudad"
                                    placeholder="Ej: Bogotá"
                                    class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            </div>

                            <!-- Departamento -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Departamento / Provincia
                                </label>
                                <input type="text" name="departamento" x-model="proveedor.departamento"
                                    placeholder="Ej: Cundinamarca"
                                    class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                            </div>

                            <!-- Estado -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    Estado Operativo <span class="text-red-500">*</span>
                                </label>
                                <select name="estado" x-model="proveedor.estado" required
                                    class="w-full px-3.5 py-2 text-sm bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                                    <option value="ACTIVO">ACTIVO (Habilitado para emitir compras)</option>
                                    <option value="INACTIVO">INACTIVO (Suspendido temporalmente)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="border-t border-slate-100 px-6 py-4 bg-slate-50 flex items-center justify-end space-x-3 rounded-b-3xl">
                        <button type="button" @click="modalOpen = false"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800 transition">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-5 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition">
                            Guardar Proveedor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
