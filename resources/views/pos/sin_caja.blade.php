@extends('layouts.app')

@section('title', 'Apertura de Caja Requerida')

@section('content')
<div class="max-w-xl mx-auto py-8 px-4" x-data="{ cajaSeleccionada: null, modalOpen: false }">
    <div class="card p-8 text-center space-y-6">
        <div class="h-14 w-14 bg-amber-50 text-amber-700 border border-amber-200 rounded-xl mx-auto flex items-center justify-center">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>

        <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Se requiere un turno de caja abierto</h2>
            <p class="text-sm text-slate-500 mt-1.5 leading-relaxed">
                Para comenzar a facturar en el Punto de Venta (POS), debes abrir un turno en una de las cajas de tu sucursal ingresando el fondo inicial.
            </p>
        </div>

        @if($cajasDisponibles->isNotEmpty())
            <div class="space-y-3 text-left">
                <label class="label-base">Selecciona una caja para abrir turno:</label>
                <div class="space-y-2">
                    @foreach($cajasDisponibles as $caja)
                        <div class="p-3.5 rounded-lg border border-slate-200 hover:border-brand-500 hover:bg-brand-50/20 flex items-center justify-between transition cursor-pointer"
                             @click="cajaSeleccionada = { id: {{ $caja->id }}, nombre: '{{ addslashes($caja->nombre) }}', codigo: '{{ $caja->codigo }}' }; modalOpen = true;">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-md bg-slate-100 flex items-center justify-center font-mono font-semibold text-xs text-slate-700 border border-slate-200">
                                    {{ $caja->codigo }}
                                </div>
                                <div>
                                    <div class="font-semibold text-slate-900 text-sm">{{ $caja->nombre }}</div>
                                    <div class="text-xs text-slate-500">Sucursal: {{ $caja->sucursal?->nombre ?? 'Sin sucursal' }}</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600">
                                Abrir turno
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="p-4 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-500 text-center">
                No hay cajas disponibles en tu sucursal. Contacta a un administrador para configurar una caja.
            </div>
        @endif

        <div class="pt-4 border-t border-slate-200 flex justify-center">
            <a href="{{ route('cajas.index') }}" class="btn-secondary text-xs">
                &larr; Ir a Administración de Cajas
            </a>
        </div>
    </div>

    <!-- Modal Apertura Rápida -->
    <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 p-6">
            <div x-show="modalOpen" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" @click="modalOpen = false"></div>
            <div x-show="modalOpen"
                class="relative bg-white rounded-xl max-w-md w-full p-6 shadow-xl border border-slate-200 z-10 space-y-5">
                
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <div>
                        <span class="text-[10px] font-bold text-brand-600 uppercase tracking-wider block">Apertura de Turno</span>
                        <h3 class="text-base font-bold text-slate-900" x-text="cajaSeleccionada ? cajaSeleccionada.nombre : ''"></h3>
                    </div>
                    <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="'/cajas/' + (cajaSeleccionada ? cajaSeleccionada.id : '') + '/abrir'" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="redirect_to" value="pos">
                    <div>
                        <label class="label-base">Monto de Apertura (Base) *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center font-bold text-slate-400">$</span>
                            <input type="number" step="0.01" min="0" name="monto_apertura" required placeholder="0.00" autofocus
                                class="input-base pl-8 tabular-nums font-mono font-bold text-base">
                        </div>
                    </div>

                    <div>
                        <label class="label-base">Observaciones</label>
                        <input type="text" name="observaciones" placeholder="Ej: Apertura para turno matutino"
                            class="input-base text-xs">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200">
                        <button @click="modalOpen = false" type="button" class="btn-secondary text-xs">Cancelar</button>
                        <button type="submit" class="btn-primary text-xs">
                            Abrir e Iniciar POS
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
