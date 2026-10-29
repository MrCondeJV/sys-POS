@extends('layouts.app')

@section('title', 'Bitácora de Auditoría')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Bitácora de Auditoría"
        description="Registro inmutable de operaciones críticas: anulaciones, devoluciones, arqueos y ajustes del sistema."
    >
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Trazabilidad Inmutable
                </span>
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- Filtros de Auditoría -->
    <x-filter-bar
        :action="route('auditoria.index')"
        :has-active-filters="!empty($fechaDesde) || !empty($fechaHasta) || !empty($modulo) || !empty($accion) || !empty($userId)"
        :clear-url="route('auditoria.index')"
    >
        <div class="sm:col-span-6 lg:col-span-2">
            <label class="block text-xs font-semibold text-text-secondary uppercase mb-1">Desde</label>
            <input type="date" name="fecha_desde" value="{{ $fechaDesde }}" class="input">
        </div>
        <div class="sm:col-span-6 lg:col-span-2">
            <label class="block text-xs font-semibold text-text-secondary uppercase mb-1">Hasta</label>
            <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}" class="input">
        </div>
        <div class="sm:col-span-6 lg:col-span-2">
            <label class="block text-xs font-semibold text-text-secondary uppercase mb-1">Módulo</label>
            <select name="modulo" class="input">
                <option value="">Todos los Módulos</option>
                @foreach($modulos as $mod)
                    <option value="{{ $mod }}" {{ $modulo === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-6 lg:col-span-2">
            <label class="block text-xs font-semibold text-text-secondary uppercase mb-1">Acción</label>
            <select name="accion" class="input">
                <option value="">Todas las Acciones</option>
                @foreach($acciones as $acc)
                    <option value="{{ $acc }}" {{ $accion === $acc ? 'selected' : '' }}>{{ $acc }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-6 lg:col-span-2">
            <label class="block text-xs font-semibold text-text-secondary uppercase mb-1">Usuario</label>
            <select name="user_id" class="input">
                <option value="">Todos los Usuarios</option>
                @foreach($usuarios as $u)
                    <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
    </x-filter-bar>

    {{-- Tabla de Auditoría --}}
    <div class="card overflow-hidden">
        <div class="@container hidden lg:block table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha / Hora</th>
                        <th>Usuario</th>
                        <th class="hidden @4xl:table-cell">Módulo</th>
                        <th>Acción</th>
                        <th>Descripción / Registro</th>
                        <th class="hidden @5xl:table-cell">IP</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditorias as $aud)
                    <tr>
                        <td class="tabular-nums font-mono text-xs text-slate-600">
                            <div class="whitespace-nowrap">{{ $aud->created_at->format('d/m/Y') }}</div>
                            <div class="text-[11px] text-slate-400">{{ $aud->created_at->format('H:i:s') }}</div>
                        </td>
                        <td>
                            <div class="font-semibold text-slate-900">{{ $aud->usuario?->name ?? 'Sistema' }}</div>
                            <div class="text-xs text-slate-500 break-all">{{ $aud->usuario?->email }}</div>
                        </td>
                        <td class="hidden @4xl:table-cell">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $aud->modulo }}
                            </span>
                        </td>
                        <td>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $aud->badgeClasses() }}">
                                {{ $aud->accion }}
                            </span>
                            <div class="@4xl:hidden text-[11px] text-slate-500 mt-1">{{ $aud->modulo }}</div>
                        </td>
                        <td>
                            <div class="text-xs text-slate-900 font-medium line-clamp-2 max-w-md">
                                {{ $aud->descripcion ?? 'Sin descripción adicional' }}
                            </div>
                            @if($aud->auditable_type)
                            <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                {{ class_basename($aud->auditable_type) }} #{{ $aud->auditable_id }}
                            </div>
                            @endif
                        </td>
                        <td class="hidden @5xl:table-cell text-xs font-mono tabular-nums text-slate-600">{{ $aud->ip ?? '127.0.0.1' }}</td>
                        <td class="text-right">
                            <a href="{{ route('auditoria.show', $aud) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-700 whitespace-nowrap">
                                Ver Detalle
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <x-empty-state
                                title="Sin registros de auditoría"
                                description="No hay eventos que coincidan con los filtros seleccionados."
                            />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Vista Móvil y Tablet -->
        <div class="lg:hidden divide-y divide-slate-100">
            @forelse($auditorias as $aud)
            <div class="p-4 space-y-2">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-semibold text-slate-900 text-sm">{{ $aud->usuario?->name ?? 'Sistema' }}</div>
                        <div class="text-[11px] text-slate-500 font-mono tabular-nums">{{ $aud->created_at->format('d/m/Y H:i:s') }} &bull; {{ $aud->ip ?? '127.0.0.1' }}</div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $aud->badgeClasses() }}">
                        {{ $aud->accion }}
                    </span>
                </div>

                <div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $aud->modulo }}
                    </span>
                </div>

                <div class="text-xs text-slate-900 line-clamp-3">
                    {{ $aud->descripcion ?? 'Sin descripción adicional' }}
                </div>

                <div class="flex items-center justify-between gap-3 pt-2 border-t border-slate-100">
                    <span class="text-[11px] text-slate-500 font-mono">
                        @if($aud->auditable_type)
                            {{ class_basename($aud->auditable_type) }} #{{ $aud->auditable_id }}
                        @endif
                    </span>
                    <a href="{{ route('auditoria.show', $aud) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-700 whitespace-nowrap">
                        Ver Detalle
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
            @empty
            <x-empty-state
                title="Sin registros de auditoría"
                description="No hay eventos que coincidan con los filtros seleccionados."
            />
            @endforelse
        </div>
        @if($auditorias->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50/50">
            {{ $auditorias->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
