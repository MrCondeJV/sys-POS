@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Despachar Traslado entre Sucursales</h1>
            <p class="text-sm text-slate-500">Envía existencias desde una sede hacia otra sede de la empresa.</p>
        </div>
        <a href="{{ route('traslados.index') }}" class="text-sm text-slate-500 hover:text-slate-800 transition">&larr; Volver</a>
    </div>

    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('traslados.store') }}" class="bg-white shadow-sm rounded-2xl border border-slate-200 p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Sucursal de Origen (Despacho)</label>
                <select name="sucursal_origen_id" id="sucursal_origen_id" required class="w-full text-sm">
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ $sucursalOrigenId == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Sucursal de Destino (Recepción)</label>
                <select name="sucursal_destino_id" id="sucursal_destino_id" required class="w-full text-sm">
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Motivo del Traslado</label>
            <input type="text" name="motivo" required
                placeholder="Ej: Reabastecimiento de inventario sede Norte"
                value="{{ old('motivo') }}"
                class="w-full text-sm">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Observaciones Generales</label>
            <textarea name="observaciones" rows="2"
                placeholder="Notas de despacho o transportador..."
                class="w-full text-sm">{{ old('observaciones') }}</textarea>
        </div>

        <div class="border-t border-slate-100 pt-6">
            <h3 class="text-base font-bold text-slate-900 mb-1">Artículos a Trasladar</h3>
            <p class="text-xs text-slate-500 mb-4">Selecciona el producto y la cantidad a transferir.</p>

            <div id="items-container" class="space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="sm:col-span-6">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Producto</label>
                        <select name="items[0][producto_id]" required class="w-full text-sm">
                            <option value="">Seleccione un producto...</option>
                            @foreach($productos as $p)
                                @php $stock = $p->inventarios->first()?->stock ?? 0; @endphp
                                <option value="{{ $p->id }}">{{ $p->nombre }} (Stock en origen: {{ $stock }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Cantidad</label>
                        <input type="number" step="0.001" min="0.001" name="items[0][cantidad]" required
                            class="w-full text-sm" placeholder="1.000">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Observación</label>
                        <input type="text" name="items[0][observaciones]"
                            class="w-full text-sm" placeholder="Opcional">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
            <a href="{{ route('traslados.index') }}" class="px-4 py-2 border border-slate-300 text-sm font-semibold rounded-xl text-slate-700 bg-white hover:bg-slate-50 transition">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                Despachar Mercancía (En Tránsito)
            </button>
        </div>
    </form>
</div>
@endsection
