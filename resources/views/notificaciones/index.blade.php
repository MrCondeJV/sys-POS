@extends('layouts.app')

@section('title', 'Notificaciones y Alertas')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Centro de Notificaciones</h1>
            <p class="mt-1 text-sm text-slate-500">
                Alertas automáticas de inventario bajo, arqueos de caja y avisos operativos del sistema.
            </p>
        </div>
        @if($totalNoLeidas > 0)
            <div class="mt-4 sm:mt-0">
                <form action="{{ route('notificaciones.marcar-todas') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-50 border border-indigo-200 rounded-xl text-sm font-semibold text-indigo-700 hover:bg-indigo-100 shadow-sm transition">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Marcar todas como leídas ({{ $totalNoLeidas }})
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Filtros de Estado -->
    <div class="flex space-x-2 border-b border-slate-200 pb-3 mb-6 text-sm font-medium">
        <a href="{{ route('notificaciones.index') }}"
            class="px-3.5 py-1.5 rounded-lg transition {{ !request('filtro') ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
            Todas
        </a>
        <a href="{{ route('notificaciones.index', ['filtro' => 'no_leidas']) }}"
            class="px-3.5 py-1.5 rounded-lg transition flex items-center space-x-1.5 {{ request('filtro') === 'no_leidas' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
            <span>No leídas</span>
            @if($totalNoLeidas > 0)
                <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold rounded-full {{ request('filtro') === 'no_leidas' ? 'bg-white text-indigo-700' : 'bg-indigo-100 text-indigo-700' }}">
                    {{ $totalNoLeidas }}
                </span>
            @endif
        </a>
        <a href="{{ route('notificaciones.index', ['filtro' => 'leidas']) }}"
            class="px-3.5 py-1.5 rounded-lg transition {{ request('filtro') === 'leidas' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' }}">
            Leídas
        </a>
    </div>

    <!-- Lista de Notificaciones -->
    <div class="space-y-3">
        @forelse($notificaciones as $item)
            <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200 transition hover:shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4 {{ $item->nivel->borderClasses() }} {{ !$item->leida ? 'bg-slate-50/50' : 'opacity-85' }}">
                <div class="flex items-start space-x-3.5 flex-1 min-w-0">
                    <span class="text-2xl mt-0.5 flex-shrink-0">{{ $item->tipo->icono() }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center space-x-2">
                            <h3 class="text-sm font-bold text-slate-900 truncate {{ !$item->leida ? 'text-indigo-950' : '' }}">
                                {{ $item->titulo }}
                            </h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $item->nivel->badgeClasses() }}">
                                {{ $item->tipo->label() }}
                            </span>
                            @if(!$item->leida)
                                <span class="h-2 w-2 rounded-full bg-indigo-600 flex-shrink-0" title="Nueva"></span>
                            @endif
                        </div>
                        <p class="text-xs sm:text-sm text-slate-600 mt-1">
                            {{ $item->mensaje }}
                        </p>
                        <div class="text-xs text-slate-400 mt-2 flex items-center space-x-2">
                            <span>{{ $item->created_at->diffForHumans() }}</span>
                            <span>•</span>
                            <span>{{ $item->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center space-x-2 sm:self-center flex-shrink-0">
                    @if($item->url_accion)
                        <a href="{{ $item->url_accion }}" class="inline-flex items-center px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 rounded-lg text-xs font-semibold transition">
                            Ver Detalle
                            <svg class="h-3.5 w-3.5 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif

                    @if(!$item->leida)
                        <form action="{{ route('notificaciones.marcar-leida', $item) }}" method="POST">
                            @csrf
                            <button type="submit" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition" title="Marcar como leída">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-sm">
                <div class="inline-flex p-4 rounded-full bg-slate-100 text-slate-400 mb-3 text-3xl">
                    🔔
                </div>
                <h3 class="text-base font-semibold text-slate-800">No hay notificaciones</h3>
                <p class="text-sm text-slate-500 mt-1">El sistema te notificará cuando haya novedades operativas o alertas de stock.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $notificaciones->links() }}
    </div>
</div>
@endsection
