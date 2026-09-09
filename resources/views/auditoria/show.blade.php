@extends('layouts.app')

@section('title', 'Detalle de Auditoría')

@section('content')
<div class="w-full max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <a href="{{ route('auditoria.index') }}" class="hover:text-indigo-600 transition">Auditoría</a>
                <span>&rsaquo;</span>
                <span class="text-indigo-600">Registro #{{ $auditoria->id }}</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Detalle de Registro de Auditoría</h1>
        </div>
        <div>
            <a href="{{ route('auditoria.index') }}" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition">
                &larr; Volver
            </a>
        </div>
    </div>

    <!-- Metadata Card -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full text-xs font-extrabold border {{ $auditoria->badgeClasses() }}">
                    {{ $auditoria->accion }}
                </span>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $auditoria->modulo }}</span>
            </div>
            <span class="text-xs font-semibold text-slate-500">{{ $auditoria->created_at->format('d/m/Y H:i:s') }}</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase">Usuario</span>
                <div class="text-sm font-extrabold text-slate-900 mt-0.5">{{ $auditoria->usuario?->name ?? 'Sistema' }}</div>
                <div class="text-xs text-slate-500">{{ $auditoria->usuario?->email }}</div>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase">Dirección IP</span>
                <div class="text-sm font-mono font-bold text-slate-900 mt-0.5">{{ $auditoria->ip ?? 'Local' }}</div>
            </div>
            <div>
                <span class="text-xs text-slate-400 font-bold uppercase">Entidad Afectada</span>
                <div class="text-sm font-bold text-slate-900 mt-0.5">
                    {{ class_basename($auditoria->auditable_type ?? 'N/A') }} #{{ $auditoria->auditable_id ?? 'N/A' }}
                </div>
            </div>
        </div>

        @if($auditoria->descripcion)
        <div class="pt-4 border-t border-slate-100">
            <span class="text-xs text-slate-400 font-bold uppercase">Descripción</span>
            <p class="text-sm text-slate-700 font-medium mt-1">{{ $auditoria->descripcion }}</p>
        </div>
        @endif

        @if($auditoria->user_agent)
        <div class="pt-2 text-[11px] text-slate-400 font-mono break-all">
            User-Agent: {{ $auditoria->user_agent }}
        </div>
        @endif
    </div>

    <!-- Comparativa Diff: Datos Anteriores vs Nuevos -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Datos Anteriores -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Estado Anterior</span>
                <span class="text-[10px] px-2 py-0.5 rounded bg-rose-50 text-rose-600 font-bold">Antes</span>
            </div>
            <div class="p-4 bg-slate-900 text-slate-200 font-mono text-xs overflow-x-auto min-h-[160px]">
                @if($auditoria->datos_anteriores)
                    <pre class="whitespace-pre-wrap">{{ json_encode($auditoria->datos_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @else
                    <span class="text-slate-500 italic">No registra valores previos (Creación o evento puntual).</span>
                @endif
            </div>
        </div>

        <!-- Datos Nuevos -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Estado Nuevo / Cambios</span>
                <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 font-bold">Después</span>
            </div>
            <div class="p-4 bg-slate-900 text-slate-200 font-mono text-xs overflow-x-auto min-h-[160px]">
                @if($auditoria->datos_nuevos)
                    <pre class="whitespace-pre-wrap text-emerald-400">{{ json_encode($auditoria->datos_nuevos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @else
                    <span class="text-slate-500 italic">Sin datos nuevos asociados.</span>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
