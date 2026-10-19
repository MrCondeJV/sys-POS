@extends('layouts.app')

@section('title', 'Plataforma SaaS — Gestión de Empresas')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" 
     x-data="{
         modalToggleOpen: false,
         targetNombre: '',
         targetUrl: '',
         targetIsActivo: false,
         openModal(nombre, url, isActivo) {
             this.targetNombre = nombre;
             this.targetUrl = url;
             this.targetIsActivo = isActivo;
             this.modalToggleOpen = true;
         },
         closeModal() {
             this.modalToggleOpen = false;
         }
     }"
     @keydown.escape.window="closeModal()">

    <!-- Header Principal -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-[#0F172A] tracking-tight">Plataforma Multiempresa</h1>
            <p class="text-sm text-[#475569] mt-1">Administración centralizada de comercios, sedes y accesos del sistema POS.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('empresas.create') }}"
                class="btn-primary-banking h-10 inline-flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Registrar Nueva Empresa</span>
            </a>
        </div>
    </div>

    <!-- Tarjetas de Métricas SaaS (KPIs) - Estilo Corporativo / Banca Digital -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card-banking p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Total Empresas</span>
                <svg class="h-5 w-5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-bold text-[#0F172A] tabular-nums tracking-tight">{{ number_format($totalEmpresas) }}</div>
                <p class="text-xs text-[#64748B] mt-1">Registradas en la plataforma</p>
            </div>
        </div>

        <div class="card-banking p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Empresas Activas</span>
                <svg class="h-5 w-5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-bold text-[#0F172A] tabular-nums tracking-tight">{{ number_format($activasEmpresas) }}</div>
                <p class="text-xs text-[#64748B] mt-1">{{ $totalEmpresas > 0 ? round(($activasEmpresas / $totalEmpresas) * 100) : 0 }}% del total operativo</p>
            </div>
        </div>

        <div class="card-banking p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Total Sedes</span>
                <svg class="h-5 w-5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-bold text-[#0F172A] tabular-nums tracking-tight">{{ number_format($totalSucursales) }}</div>
                <p class="text-xs text-[#64748B] mt-1">Puntos de venta habilitados</p>
            </div>
        </div>

        <div class="card-banking p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Total Usuarios</span>
                <svg class="h-5 w-5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div class="mt-4">
                <div class="text-3xl font-bold text-[#0F172A] tabular-nums tracking-tight">{{ number_format($totalUsuarios) }}</div>
                <p class="text-xs text-[#64748B] mt-1">Cuentas con acceso al sistema</p>
            </div>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="card-banking p-4">
        <form method="GET" action="{{ route('empresas.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-7">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#64748B]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                        class="w-full pl-10 pr-4 py-2 border border-[#CBD5E1] rounded-lg text-sm text-[#0F172A] placeholder-[#64748B] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8] transition h-10 min-h-[2.5rem]"
                        placeholder="Buscar por nombre comercial, razón social, NIT o ciudad...">
                </div>
            </div>

            <div class="sm:col-span-3">
                <select name="estado"
                    class="w-full py-2 px-3 border border-[#CBD5E1] rounded-lg text-sm text-[#0F172A] focus:outline-none focus:ring-2 focus:ring-[#1D4ED8]/20 focus:border-[#1D4ED8] transition h-10 min-h-[2.5rem]">
                    <option value="">Todos los Estados</option>
                    <option value="ACTIVO" {{ request('estado') === 'ACTIVO' ? 'selected' : '' }}>Activas</option>
                    <option value="INACTIVO" {{ request('estado') === 'INACTIVO' ? 'selected' : '' }}>Inactivas / Suspendidas</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit"
                    class="btn-secondary-banking w-full h-10 inline-flex items-center justify-center gap-1.5 text-sm font-semibold">
                    <svg class="h-4 w-4 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span>Filtrar</span>
                </button>
                @if(request()->hasAny(['buscar', 'estado']))
                <a href="{{ route('empresas.index') }}"
                    class="h-10 px-3 rounded-lg border border-[#CBD5E1] text-[#64748B] hover:text-[#0F172A] hover:bg-slate-50 transition inline-flex items-center justify-center flex-shrink-0" title="Limpiar filtros">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Listado de Comercios / Tenants -->
    <div class="card-banking overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[#E2E8F0] text-left text-sm">
                <thead class="bg-[#F8FAFC]">
                    <tr>
                        <th class="px-5 py-3 text-xs font-semibold text-[#475569] uppercase tracking-wider">Empresa / Comercio</th>
                        <th class="px-5 py-3 text-xs font-semibold text-[#475569] uppercase tracking-wider">NIT / Documento</th>
                        <th class="px-5 py-3 text-xs font-semibold text-[#475569] uppercase tracking-wider">Ubicación & Contacto</th>
                        <th class="px-5 py-3 text-xs font-semibold text-[#475569] uppercase tracking-wider text-center">Sedes / Usuarios</th>
                        <th class="px-5 py-3 text-xs font-semibold text-[#475569] uppercase tracking-wider text-center">Estado</th>
                        <th class="px-5 py-3 text-xs font-semibold text-[#475569] uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E2E8F0] bg-white">
                    @forelse($empresas as $emp)
                    <tr class="hover:bg-[#F8FAFC] transition-colors {{ $emp->id === $empresaActivaId ? 'bg-[#EFF6FF]/60' : '' }}">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if($emp->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($emp->logo_path))
                                <div class="h-10 w-10 rounded-lg p-1 bg-white border border-[#CBD5E1] shadow-2xs flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($emp->logo_path) }}" alt="{{ $emp->nombre_comercial }}" class="h-full w-full object-contain">
                                </div>
                                @else
                                <div class="h-10 w-10 rounded-lg flex items-center justify-center font-bold text-xs text-white {{ $emp->id === $empresaActivaId ? 'bg-[#1D4ED8]' : 'bg-[#1E3A5F]' }} flex-shrink-0 shadow-2xs">
                                    {{ mb_strtoupper(mb_substr($emp->nombre_comercial, 0, 2)) }}
                                </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="font-semibold text-sm text-[#0F172A] flex items-center gap-2">
                                        <span class="truncate">{{ $emp->nombre_comercial }}</span>
                                        @if($emp->id === $empresaActivaId)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-[#EFF6FF] text-[#1D4ED8] border border-[#BFDBFE] flex-shrink-0">
                                            Activa ahora
                                        </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-[#64748B] truncate max-w-xs mt-0.5">{{ $emp->razon_social }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="font-mono text-xs font-semibold text-[#0F172A] tabular-nums">{{ $emp->nit }}{{ $emp->dv ? '-' . $emp->dv : '' }}</div>
                            <div class="text-[11px] text-[#64748B] mt-0.5">{{ $emp->tipo_documento }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-xs font-medium text-[#0F172A]">{{ $emp->ciudad ?? 'N/A' }}{{ $emp->departamento ? ', ' . $emp->departamento : '' }}</div>
                            <div class="text-xs text-[#64748B] truncate max-w-[220px] mt-0.5">{{ $emp->email ?? $emp->telefono ?? 'Sin contacto' }}</div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="inline-flex items-center justify-center gap-2 text-xs text-[#475569]">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-[#F1F5F9] font-medium" title="Sedes / Sucursales">
                                    <svg class="h-3.5 w-3.5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <span class="tabular-nums font-semibold">{{ $emp->sucursales_count }}</span> {{ $emp->sucursales_count === 1 ? 'sede' : 'sedes' }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-[#F1F5F9] font-medium" title="Usuarios">
                                    <svg class="h-3.5 w-3.5 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <span class="tabular-nums font-semibold">{{ $emp->users_count }}</span> {{ $emp->users_count === 1 ? 'usuario' : 'usuarios' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($emp->estado === \App\Enums\EstadoGeneral::ACTIVO)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#DCFCE7] text-[#166534] border border-[#BBF7D0]">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#16A34A]"></span>
                                Activo
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#FEF3C7] text-[#92400E] border border-[#FDE68A]">
                                <span class="h-1.5 w-1.5 rounded-full bg-[#D97706]"></span>
                                Inactivo
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if($emp->id === $empresaActivaId)
                                <form method="POST" action="{{ route('empresas.salir') }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-700 bg-white hover:bg-red-50 border border-red-300 transition-colors shadow-2xs cursor-pointer"
                                        title="Salir del negocio y volver al panel Super Admin">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        <span>Salir del Negocio</span>
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('empresas.seleccionar') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="empresa_id" value="{{ $emp->id }}">
                                    <button type="submit"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-[#1D4ED8] bg-white hover:bg-[#EFF6FF] border border-[#1D4ED8] transition-colors shadow-2xs cursor-pointer"
                                        title="Entrar a este negocio como Super Admin (Modo Soporte)">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                        </svg>
                                        <span>Entrar al Negocio</span>
                                    </button>
                                </form>
                                @endif

                                <a href="{{ route('empresas.edit', $emp) }}"
                                    class="p-2 text-[#64748B] hover:text-[#0F172A] rounded-lg hover:bg-slate-100 transition-colors inline-flex items-center justify-center cursor-pointer"
                                    title="Editar datos de empresa">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>

                                <button type="button"
                                    @click="openModal('{{ addslashes($emp->nombre_comercial) }}', '{{ route('empresas.toggle-estado', $emp) }}', {{ $emp->estado === \App\Enums\EstadoGeneral::ACTIVO ? 'true' : 'false' }})"
                                    class="p-2 rounded-lg transition-colors inline-flex items-center justify-center cursor-pointer {{ $emp->estado === \App\Enums\EstadoGeneral::ACTIVO ? 'text-[#64748B] hover:text-amber-600 hover:bg-amber-50' : 'text-[#64748B] hover:text-[#1D4ED8] hover:bg-blue-50' }}"
                                    title="{{ $emp->estado === \App\Enums\EstadoGeneral::ACTIVO ? 'Suspender comercio' : 'Reactivar comercio' }}">
                                    @if($emp->estado === \App\Enums\EstadoGeneral::ACTIVO)
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    @else
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    @endif
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center text-[#64748B]">
                            <div class="h-12 w-12 mx-auto rounded-xl bg-[#F1F5F9] border border-[#E2E8F0] flex items-center justify-center text-[#64748B] mb-3">
                                <svg class="h-6 w-6 text-[#64748B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <p class="font-semibold text-base text-[#0F172A]">No se encontraron comercios registrados</p>
                            <p class="text-xs text-[#64748B] mt-1 max-w-sm mx-auto">Comienza registrando la primera empresa en la plataforma o limpia los filtros de búsqueda.</p>
                            <div class="mt-4">
                                <a href="{{ route('empresas.create') }}"
                                    class="btn-primary-banking inline-flex items-center gap-2 text-xs">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    <span>Registrar Nueva Empresa</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($empresas->hasPages())
        <div class="px-5 py-3.5 border-t border-[#E2E8F0] bg-[#F8FAFC]">
            {{ $empresas->links() }}
        </div>
        @endif
    </div>

    <!-- Modal Accesible para Suspender / Reactivar Empresa -->
    <div x-cloak x-show="modalToggleOpen" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         role="dialog" 
         aria-modal="true" 
         aria-labelledby="modal-toggle-title">
        <!-- Backdrop con Blur -->
        <div x-show="modalToggleOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-[#0B1F36]/60 backdrop-blur-xs"
             @click="closeModal()"></div>

        <!-- Dialog Box -->
        <div class="min-h-full flex items-center justify-center p-4">
            <div x-show="modalToggleOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-xl border border-[#E2E8F0] shadow-xl max-w-md w-full p-6 text-left"
                 @click.away="closeModal()">

                <div class="flex items-start gap-4">
                    <div :class="targetIsActivo ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-[#1D4ED8]'" 
                         class="h-10 w-10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <template x-if="targetIsActivo">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </template>
                        <template x-if="!targetIsActivo">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                    </div>

                    <div class="flex-1">
                        <h3 id="modal-toggle-title" 
                            class="text-base font-bold text-[#0F172A]"
                            x-text="targetIsActivo ? '¿Suspender empresa comercial?' : '¿Reactivar empresa comercial?'">
                        </h3>
                        <p class="text-xs text-[#475569] mt-2 leading-relaxed">
                            <template x-if="targetIsActivo">
                                <span>Al suspender a <strong class="text-[#0F172A]" x-text="targetNombre"></strong>, ningún usuario podrá iniciar sesión ni registrar ventas hasta que la suscripción sea reactivada.</span>
                            </template>
                            <template x-if="!targetIsActivo">
                                <span>Al reactivar a <strong class="text-[#0F172A]" x-text="targetNombre"></strong>, los usuarios podrán volver a iniciar sesión y facturar en todos sus puntos de venta con normalidad.</span>
                            </template>
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 pt-4 border-t border-[#E2E8F0]">
                    <button type="button"
                        @click="closeModal()"
                        class="px-4 py-2 text-xs font-semibold rounded-lg bg-white hover:bg-slate-50 text-[#475569] border border-[#CBD5E1] transition shadow-2xs cursor-pointer">
                        Cancelar
                    </button>
                    <form method="POST" :action="targetUrl" class="inline">
                        @csrf
                        <button type="submit"
                            :class="targetIsActivo ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-[#1D4ED8] hover:bg-[#1E40AF] text-white'"
                            class="px-4 py-2 text-xs font-semibold rounded-lg transition shadow-2xs cursor-pointer"
                            x-text="targetIsActivo ? 'Suspender Empresa' : 'Reactivar Empresa'">
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
