@extends('layouts.app')

@section('title', 'Despachar Traslado entre Sucursales')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <x-page-header
        title="Despachar Traslado entre Sucursales"
        subtitle="Envía existencias desde una sede hacia otra sede de la empresa."
        :breadcrumbs="[
            ['label' => 'Traslados', 'url' => route('traslados.index')],
            ['label' => 'Nuevo Traslado']
        ]"
    >
        <x-slot:actions>
            <x-button variant="outline" size="sm" href="{{ route('traslados.index') }}">
                <svg class="h-4 w-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @if($errors->any())
        <div class="p-4 bg-danger-bg border border-danger-border text-danger-text rounded-lg text-xs font-medium">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('traslados.store') }}" class="bg-white shadow-xs rounded-xl border border-slate-200/80 p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <x-field label="Sucursal de Origen (Despacho)" name="sucursal_origen_id" :required="true">
                    <select name="sucursal_origen_id" id="sucursal_origen_id" required class="input">
                        @foreach($sucursales as $s)
                            <option value="{{ $s->id }}" {{ $sucursalOrigenId == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                        @endforeach
                    </select>
                </x-field>
            </div>
            <div>
                <x-field label="Sucursal de Destino (Recepción)" name="sucursal_destino_id" :required="true">
                    <select name="sucursal_destino_id" id="sucursal_destino_id" required class="input">
                        @foreach($sucursales as $s)
                            <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                        @endforeach
                    </select>
                </x-field>
            </div>
        </div>

        <div>
            <x-field label="Motivo del Traslado" name="motivo" :required="true">
                <input type="text" name="motivo" required
                    placeholder="Ej: Reabastecimiento de inventario sede Norte"
                    value="{{ old('motivo') }}"
                    class="w-full py-2 px-3 text-xs border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
            </x-field>
        </div>

        <div>
            <x-field label="Observaciones Generales" name="observaciones">
                <textarea name="observaciones" rows="2"
                    placeholder="Notas de despacho o transportador..."
                    class="w-full py-2 px-3 text-xs border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">{{ old('observaciones') }}</textarea>
            </x-field>
        </div>

        <div class="border-t border-slate-100 pt-5">
            <h3 class="text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Artículos a Trasladar</h3>
            <p class="text-[11px] text-slate-500 mb-3">Selecciona el producto y la cantidad a transferir.</p>

            <div id="items-container" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 p-3.5 bg-slate-50/70 rounded-lg border border-slate-200">
                    <div class="sm:col-span-6">
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Producto</label>
                        <select name="items[0][producto_id]" required class="input">
                            <option value="">Seleccione un producto...</option>
                            @foreach($productos as $p)
                                @php $stock = $p->inventarios->first()?->stock ?? 0; @endphp
                                <option value="{{ $p->id }}">{{ $p->nombre }} (Stock en origen: {{ $stock }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Cantidad</label>
                        <input type="number" step="0.001" min="0.001" name="items[0][cantidad]" required
                            class="w-full py-2 px-3 text-xs border border-slate-300 rounded-lg text-slate-900 font-bold tabular-nums focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition" placeholder="1.000">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">Observación</label>
                        <input type="text" name="items[0][observaciones]"
                            class="w-full py-2 px-3 text-xs border border-slate-300 rounded-lg text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition" placeholder="Opcional">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2.5 border-t border-slate-100 pt-4">
            <x-button variant="outline" href="{{ route('traslados.index') }}">
                Cancelar
            </x-button>
            <x-button variant="primary" type="submit">
                Despachar Mercancía (En Tránsito)
            </x-button>
        </div>
    </form>
</div>
@endsection
