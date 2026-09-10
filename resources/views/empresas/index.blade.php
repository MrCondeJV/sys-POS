@extends('layouts.app')

@section('title', 'Plataforma SaaS — Gestión de Empresas')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Principal -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800">
                    SaaS Super Admin
                </span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">Plataforma Multiempresa</h1>
            <p class="text-sm text-slate-500">Administra los comercios, sucursales y suscriptores del sistema POS.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('empresas.create') }}"
                class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Registrar Nueva Empresa
            </a>
        </div>
    </div>

    <!-- Tarjetas de Métricas SaaS (KPIs) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-3xl border border-slate-200/70 p-6 shadow-sm flex items-center gap-4">
            <div class="h-12 w-12 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Empresas</p>
                <p class="text-2xl font-black text-slate-900 mt-0.5">{{ number_format($totalEmpresas) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/70 p-6 shadow-sm flex items-center gap-4">
            <div class="h-12 w-12 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Empresas Activas</p>
                <p class="text-2xl font-black text-slate-900 mt-0.5">{{ number_format($activasEmpresas) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/70 p-6 shadow-sm flex items-center gap-4">
            <div class="h-12 w-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Sedes/Sucursales</p>
                <p class="text-2xl font-black text-slate-900 mt-0.5">{{ number_format($totalSucursales) }}</p>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/70 p-6 shadow-sm flex items-center gap-4">
            <div class="h-12 w-12 rounded-2xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Usuarios</p>
                <p class="text-2xl font-black text-slate-900 mt-0.5">{{ number_format($totalUsuarios) }}</p>
            </div>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="bg-white rounded-3xl border border-slate-200/70 p-5 shadow-sm">
        <form method="GET" action="{{ route('empresas.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-4">
            <div class="sm:col-span-8">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                        placeholder="Buscar por nombre comercial, razón social, NIT o ciudad...">
                </div>
            </div>

            <div class="sm:col-span-2">
                <select name="estado"
                    class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Todos los Estados</option>
                    <option value="ACTIVO" {{ request('estado') === 'ACTIVO' ? 'selected' : '' }}>Activas</option>
                    <option value="INACTIVO" {{ request('estado') === 'INACTIVO' ? 'selected' : '' }}>Inactivas / Suspendidas</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit"
                    class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                    Filtrar
                </button>
                @if(request()->hasAny(['buscar', 'estado']))
                <a href="{{ route('empresas.index') }}"
                    class="p-2.5 rounded-xl border border-slate-300 text-slate-500 hover:bg-slate-50 transition" title="Limpiar filtros">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Listado de Comercios / Tenants -->
    <div class="bg-white rounded-3xl border border-slate-200/70 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Empresa / Comercio</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">NIT / Documento</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider">Ubicación & Contacto</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Sedes / Usuarios</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Estado</th>
                        <th class="px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($empresas as $emp)
                    <tr class="hover:bg-slate-50/70 transition {{ $emp->id === $empresaActivaId ? 'bg-indigo-50/40' : '' }}">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-xl flex items-center justify-center font-bold text-sm text-white {{ $emp->id === $empresaActivaId ? 'bg-indigo-600 shadow-md shadow-indigo-200' : 'bg-slate-700' }}">
                                    {{ strtoupper(substr($emp->nombre_comercial, 0, 2)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                        {{ $emp->nombre_comercial }}
                                        @if($emp->id === $empresaActivaId)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-indigo-100 text-indigo-700">
                                            Empresa Activa
                                        </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 truncate max-w-xs">{{ $emp->razon_social }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-800">{{ $emp->nit }}{{ $emp->dv ? '-' . $emp->dv : '' }}</div>
                            <div class="text-xs text-slate-400">{{ $emp->tipo_documento }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-slate-800 text-xs font-medium">{{ $emp->ciudad ?? 'N/A' }}{{ $emp->departamento ? ', ' . $emp->departamento : '' }}</div>
                            <div class="text-xs text-slate-400 truncate max-w-xs">{{ $emp->email ?? $emp->telefono ?? 'Sin contacto' }}</div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="inline-flex items-center gap-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700" title="Sucursales">
                                    🏢 {{ $emp->sucursales_count }} sedes
                                </span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700" title="Usuarios">
                                    👤 {{ $emp->users_count }}
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($emp->estado === \App\Enums\EstadoGeneral::ACTIVO)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                ● Activo
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200/60">
                                ● Inactivo
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if($emp->id !== $empresaActivaId)
                                <form method="POST" action="{{ route('empresas.seleccionar') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="empresa_id" value="{{ $emp->id }}">
                                    <button type="submit"
                                        class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200/60 transition"
                                        title="Administrar los datos de esta empresa">
                                        Entrar a Empresa
                                    </button>
                                </form>
                                @endif

                                <a href="{{ route('empresas.edit', $emp) }}"
                                    class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition"
                                    title="Editar datos fiscales">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>

                                <form method="POST" action="{{ route('empresas.toggle-estado', $emp) }}" class="inline"
                                    onsubmit="return confirm('¿Confirmas cambiar el estado de esta empresa?')">
                                    @csrf
                                    <button type="submit"
                                        class="p-1.5 rounded-lg transition {{ $emp->estado === \App\Enums\EstadoGeneral::ACTIVO ? 'text-amber-500 hover:text-amber-700 hover:bg-amber-50' : 'text-emerald-500 hover:text-emerald-700 hover:bg-emerald-50' }}"
                                        title="{{ $emp->estado === \App\Enums\EstadoGeneral::ACTIVO ? 'Suspender / Desactivar' : 'Reactivar' }}">
                                        @if($emp->estado === \App\Enums\EstadoGeneral::ACTIVO)
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        @else
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                            <div class="h-12 w-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                🏢
                            </div>
                            <p class="font-semibold text-slate-700">No se encontraron empresas registradas</p>
                            <p class="text-xs text-slate-400 mt-1">Registra una nueva empresa para comenzar a operar.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($empresas->hasPages())
        <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/50">
            {{ $empresas->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
