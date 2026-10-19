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

    {{-- Filtros --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('auditoria.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div>
                <label class="label-base">Desde</label>
                <input type="date" name="fecha_desde" value="{{ $fechaDesde }}" class="input-base">
            </div>
            <div>
                <label class="label-base">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}" class="input-base">
            </div>
            <div>
                <label class="label-base">Módulo</label>
                <select name="modulo" class="input-base">
                    <option value="">Todos los Módulos</option>
                    @foreach($modulos as $mod)
                        <option value="{{ $mod }}" {{ $modulo === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-base">Acción</label>
                <select name="accion" class="input-base">
                    <option value="">Todas las Acciones</option>
                    @foreach($acciones as $acc)
                        <option value="{{ $acc }}" {{ $accion === $acc ? 'selected' : '' }}>{{ $acc }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label-base">Usuario</label>
                <select name="user_id" class="input-base">
                    <option value="">Todos los Usuarios</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary w-full justify-center">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filtrar
                </button>
                <a href="{{ route('auditoria.index') }}" class="btn-secondary whitespace-nowrap">Limpiar</a>
            </div>
        </form>
    </div>

    {{-- Tabla de Auditoría --}}
    <div class="card overflow-hidden">
        <div class="table-container">
            <table class="table-corporate">
                <thead>
                    <tr>
                        <th>Fecha / Hora</th>
                        <th>Usuario</th>
                        <th>Módulo</th>
                        <th>Acción</th>
                        <th>Descripción / Registro</th>
                        <th>IP</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($auditorias as $aud)
                    <tr>
                        <td class="tabular-nums font-mono text-xs whitespace-nowrap text-slate-600">
                            {{ $aud->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td>
                            <div class="font-semibold text-slate-900">{{ $aud->usuario?->name ?? 'Sistema' }}</div>
                            <div class="text-xs text-slate-500">{{ $aud->usuario?->email }}</div>
                        </td>
                        <td>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $aud->modulo }}
                            </span>
                        </td>
                        <td>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $aud->badgeClasses() }}">
                                {{ $aud->accion }}
                            </span>
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
                        <td class="text-xs font-mono tabular-nums text-slate-600">{{ $aud->ip ?? '127.0.0.1' }}</td>
                        <td class="text-right">
                            <a href="{{ route('auditoria.show', $aud) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-700">
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
        @if($auditorias->hasPages())
        <div class="p-4 border-t border-slate-200 bg-slate-50/50">
            {{ $auditorias->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
