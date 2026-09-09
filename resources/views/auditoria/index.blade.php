@extends('layouts.app')

@section('title', 'Bitácora de Auditoría')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <span>Seguridad & Trazabilidad</span>
                <span>&rsaquo;</span>
                <span class="text-indigo-600">Auditoría</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-2.5 rounded-2xl bg-slate-900 text-white shadow-md">
                    <svg class="h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </span>
                Bitácora de Auditoría del Sistema
            </h1>
            <p class="text-sm text-slate-500 mt-1">Registro inmutable de operaciones críticas: anulaciones, devoluciones, arqueos de caja y ajustes.</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('auditoria.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="{{ $fechaDesde }}" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ $fechaHasta }}" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Módulo</label>
                <select name="modulo" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
                    <option value="">Todos los Módulos</option>
                    @foreach($modulos as $mod)
                        <option value="{{ $mod }}" {{ $modulo === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Acción</label>
                <select name="accion" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
                    <option value="">Todas las Acciones</option>
                    @foreach($acciones as $acc)
                        <option value="{{ $acc }}" {{ $accion === $acc ? 'selected' : '' }}>{{ $acc }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Usuario</label>
                <select name="user_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
                    <option value="">Todos los Usuarios</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-sm">Filtrar</button>
                <a href="{{ route('auditoria.index') }}" class="px-3 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl border border-slate-200 transition">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Tabla de Auditoría -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Fecha / Hora</th>
                        <th class="py-3.5 px-4">Usuario</th>
                        <th class="py-3.5 px-4">Módulo</th>
                        <th class="py-3.5 px-4">Acción</th>
                        <th class="py-3.5 px-4">Descripción / Registro</th>
                        <th class="py-3.5 px-4">IP</th>
                        <th class="py-3.5 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($auditorias as $aud)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 text-xs font-semibold text-slate-900 whitespace-nowrap">
                            {{ $aud->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $aud->usuario?->name ?? 'Sistema' }}</div>
                            <div class="text-[10px] text-slate-400">{{ $aud->usuario?->email }}</div>
                        </td>
                        <td class="py-3 px-4 text-xs">
                            <span class="px-2 py-0.5 rounded-md font-bold bg-slate-100 text-slate-700">
                                {{ $aud->modulo }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-extrabold border {{ $aud->badgeClasses() }}">
                                {{ $aud->accion }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <div class="text-xs text-slate-900 font-medium line-clamp-2 max-w-md">
                                {{ $aud->descripcion ?? 'Sin descripción adicional' }}
                            </div>
                            @if($aud->auditable_type)
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                {{ class_basename($aud->auditable_type) }} #{{ $aud->auditable_id }}
                            </div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-xs font-mono text-slate-500">{{ $aud->ip ?? '127.0.0.1' }}</td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('auditoria.show', $aud) }}" class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold text-indigo-600 hover:bg-indigo-50 transition">
                                Ver Detalle &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">No hay registros de auditoría que coincidan con los filtros.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $auditorias->links() }}
        </div>
    </div>
</div>
@endsection
