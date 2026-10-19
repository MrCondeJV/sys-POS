@extends('layouts.app')

@section('title', 'Detalle de Auditoría #' . $auditoria->id)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                <a href="{{ route('auditoria.index') }}" class="hover:text-brand-600 transition">Auditoría</a>
                <span>&rsaquo;</span>
                <span class="text-slate-700 font-semibold">Registro #{{ $auditoria->id }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Detalle de Registro de Auditoría</h1>
        </div>
        <div>
            <a href="{{ route('auditoria.index') }}" class="btn-secondary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a la bitácora
            </a>
        </div>
    </div>

    {{-- Metadata Card --}}
    <div class="card p-6 space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 pb-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $auditoria->badgeClasses() }}">
                    {{ $auditoria->accion }}
                </span>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $auditoria->modulo }}</span>
            </div>
            <div class="text-xs font-mono tabular-nums text-slate-600">
                {{ $auditoria->created_at->format('d/m/Y H:i:s') }}
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Usuario</span>
                <div class="text-sm font-semibold text-slate-900 mt-1">{{ $auditoria->usuario?->name ?? 'Sistema' }}</div>
                <div class="text-xs text-slate-500">{{ $auditoria->usuario?->email ?? 'N/A' }}</div>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Dirección IP</span>
                <div class="text-sm font-mono tabular-nums font-semibold text-slate-900 mt-1">{{ $auditoria->ip ?? 'Local' }}</div>
            </div>
            <div>
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Entidad Afectada</span>
                <div class="text-sm font-semibold text-slate-900 mt-1">
                    {{ class_basename($auditoria->auditable_type ?? 'N/A') }} #{{ $auditoria->auditable_id ?? 'N/A' }}
                </div>
            </div>
        </div>

        @if($auditoria->descripcion)
        <div class="pt-4 border-t border-slate-100">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Descripción del Evento</span>
            <p class="text-sm text-slate-800 font-medium mt-1 leading-relaxed">{{ $auditoria->descripcion }}</p>
        </div>
        @endif

        @if($auditoria->user_agent)
        <div class="pt-3 border-t border-slate-100 text-[11px] text-slate-400 font-mono break-all">
            <span class="font-semibold text-slate-500">Agente:</span> {{ $auditoria->user_agent }}
        </div>
        @endif
    </div>

    {{-- Comparativa de Datos --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        {{-- Estado Anterior --}}
        <div class="card overflow-hidden">
            <div class="p-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Estado Anterior</span>
                <span class="text-[10px] px-2 py-0.5 rounded font-semibold bg-rose-50 text-rose-700 border border-rose-200">Antes</span>
            </div>
            <div class="p-4 bg-slate-900 text-slate-200 font-mono text-xs overflow-x-auto min-h-[160px]">
                @if($auditoria->datos_anteriores)
                    <pre class="whitespace-pre-wrap">{{ json_encode($auditoria->datos_anteriores, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @else
                    <span class="text-slate-500 italic">No registra valores previos (Creación o evento puntual).</span>
                @endif
            </div>
        </div>

        {{-- Estado Nuevo --}}
        <div class="card overflow-hidden">
            <div class="p-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">Estado Nuevo / Cambios</span>
                <span class="text-[10px] px-2 py-0.5 rounded font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Después</span>
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
