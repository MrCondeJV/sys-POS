@extends('layouts.app')

@section('title', 'Apertura de Caja Requerida')

@section('content')
<div class="max-w-xl mx-auto py-12 px-4" x-data="{ cajaSeleccionada: null, modalOpen: false }">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl p-8 text-center space-y-6">
        <div class="h-20 w-20 bg-amber-100 text-amber-600 rounded-3xl mx-auto flex items-center justify-center">
            <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>

        <div>
            <h2 class="text-2xl font-black text-slate-900">Se requiere un turno de caja abierto</h2>
            <p class="text-sm text-slate-500 mt-2">
                Para comenzar a facturar en el Punto de Venta (POS), debes abrir un turno en una de las cajas de tu sucursal ingresando el fondo inicial.
            </p>
        </div>

        @if($cajasDisponibles->isNotEmpty())
            <div class="space-y-3 text-left">
                <label class="block text-xs font-bold text-slate-700 uppercase">Selecciona una caja para abrir turno:</label>
                <div class="space-y-2">
                    @foreach($cajasDisponibles as $caja)
                        <div class="p-4 rounded-2xl border border-slate-200 hover:border-emerald-500 flex items-center justify-between transition cursor-pointer"
                             @click="cajaSeleccionada = { id: {{ $caja->id }}, nombre: '{{ addslashes($caja->nombre) }}', codigo: '{{ $caja->codigo }}' }; modalOpen = true;">
                            <div class="flex items-center space-x-3">
                                <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center font-bold text-xs text-slate-700">
                                    {{ $caja->codigo }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $caja->nombre }}</div>
                                    <div class="text-[11px] text-slate-400">Sucursal: {{ $caja->sucursal->nombre }}</div>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-xl">
                                Abrir &rarr;
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs text-slate-500">
                No hay cajas registradas en tu sucursal. Contacta a un administrador para configurar una caja.
            </div>
        @endif

        <div class="pt-4 border-t border-slate-100 flex justify-center">
            <a href="{{ route('cajas.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
                &larr; Ir a Administración de Cajas
            </a>
        </div>
    </div>

    <!-- Modal Apertura Rápida -->
    <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 p-6">
            <div x-show="modalOpen" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
            <div x-show="modalOpen" @click.away="modalOpen = false"
                class="relative bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100 z-10 space-y-5">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider block">Apertura Rápida</span>
                        <h3 class="text-lg font-black text-slate-900" x-text="cajaSeleccionada ? cajaSeleccionada.nombre : ''"></h3>
                    </div>
                    <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form :action="'/cajas/' + (cajaSeleccionada ? cajaSeleccionada.id : '') + '/abrir'" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="redirect_to" value="pos">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Monto de Apertura (Base) *</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center font-bold text-slate-400">$</span>
                            <input type="number" step="0.01" min="0" name="monto_apertura" required placeholder="0.00" autofocus
                                class="w-full pl-8 pr-3.5 py-2.5 rounded-xl border border-slate-200 text-lg font-bold text-slate-900 focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Observaciones</label>
                        <input type="text" name="observaciones" placeholder="Ej: Apertura para turno matutino"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs">
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                        <button @click="modalOpen = false" type="button" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-50 rounded-xl">Cancelar</button>
                        <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-xs font-bold text-white rounded-xl shadow-sm">
                            Abrir e Iniciar POS
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
