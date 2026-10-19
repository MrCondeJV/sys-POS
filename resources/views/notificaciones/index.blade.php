@extends('layouts.app')

@section('title', 'Notificaciones y Alertas')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Centro de Notificaciones</h1>
            <p class="text-sm text-slate-500 mt-0.5">
                Alertas automáticas de inventario bajo, arqueos de caja y avisos operativos del sistema.
            </p>
        </div>
        @if($totalNoLeidas > 0)
            <div>
                <form action="{{ route('notificaciones.marcar-todas') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-secondary text-xs">
                        <svg class="h-4 w-4 mr-1.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Marcar todas como leídas ({{ $totalNoLeidas }})
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Filtros de Estado -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-3 text-xs font-semibold">
        <a href="{{ route('notificaciones.index') }}"
            class="px-3 py-1.5 rounded-lg transition {{ !request('filtro') ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            Todas
        </a>
        <a href="{{ route('notificaciones.index', ['filtro' => 'no_leidas']) }}"
            class="px-3 py-1.5 rounded-lg transition inline-flex items-center gap-1.5 {{ request('filtro') === 'no_leidas' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <span>No leídas</span>
            @if($totalNoLeidas > 0)
                <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold rounded-full {{ request('filtro') === 'no_leidas' ? 'bg-white text-slate-900' : 'bg-brand-100 text-brand-700' }}">
                    {{ $totalNoLeidas }}
                </span>
            @endif
        </a>
        <a href="{{ route('notificaciones.index', ['filtro' => 'leidas']) }}"
            class="px-3 py-1.5 rounded-lg transition {{ request('filtro') === 'leidas' ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            Leídas
        </a>
    </div>

    <!-- Lista de Notificaciones -->
    <div class="space-y-3">
        @forelse($notificaciones as $item)
            <div class="card p-4 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $item->nivel->borderClasses() }} {{ !$item->leida ? 'bg-white' : 'bg-slate-50/70 opacity-90' }}">
                <div class="flex items-start gap-3.5 flex-1 min-w-0">
                    <div class="p-2 rounded-lg bg-slate-100 text-slate-600 flex-shrink-0 mt-0.5">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-semibold text-slate-900 truncate">
                                {{ $item->titulo }}
                            </h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $item->nivel->badgeClasses() }}">
                                {{ $item->tipo->label() }}
                            </span>
                            @if(!$item->leida)
                                <span class="h-2 w-2 rounded-full bg-brand-600 flex-shrink-0" title="Nueva"></span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            {{ $item->mensaje }}
                        </p>
                        <div class="text-[11px] text-slate-400 mt-2 flex items-center gap-2 tabular-nums">
                            <span>{{ $item->created_at->diffForHumans() }}</span>
                            <span>•</span>
                            <span class="font-mono">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:self-center flex-shrink-0">
                    @if($item->url_accion)
                        <a href="{{ $item->url_accion }}" class="btn-secondary text-xs py-1.5">
                            Ver Detalle
                            <svg class="h-3.5 w-3.5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif

                    @if(!$item->leida)
                        <form action="{{ route('notificaciones.marcar-leida', $item) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition" title="Marcar como leída">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="card p-10">
                <x-empty-state
                    title="No hay notificaciones"
                    description="El sistema te notificará cuando existan novedades operativas, límites de stock o arqueos de caja."
                />
            </div>
        @endforelse
    </div>

    @if($notificaciones->hasPages())
        <div class="mt-4">
            {{ $notificaciones->links() }}
        </div>
    @endif
</div>
@endsection
