@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Despachar Traslado entre Sucursales</h1>
            <p class="text-sm text-gray-500">Envía existencias desde una sede hacia otra sede de la empresa.</p>
        </div>
        <a href="{{ route('traslados.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Volver</a>
    </div>

    @if($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-md text-sm">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('traslados.store') }}" class="bg-white shadow rounded-lg p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Sucursal de Origen (Despacho)</label>
                <select name="sucursal_origen_id" id="sucursal_origen_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ $sucursalOrigenId == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Sucursal de Destino (Recepción)</label>
                <select name="sucursal_destino_id" id="sucursal_destino_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Motivo del Traslado</label>
            <input type="text" name="motivo" required placeholder="Ej: Reabastecimiento de inventario sede Norte" value="{{ old('motivo') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Observaciones Generales</label>
            <textarea name="observaciones" rows="2" placeholder="Notas de despacho o transportador..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('observaciones') }}</textarea>
        </div>

        <div class="border-t pt-4">
            <h3 class="text-base font-semibold text-gray-900 mb-2">Artículos a Trasladar</h3>
            <p class="text-xs text-gray-500 mb-4">Selecciona el producto y la cantidad a transferir.</p>

            <div id="items-container" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 p-3 bg-gray-50 rounded-md border border-gray-200">
                    <div class="sm:col-span-6">
                        <label class="block text-xs font-medium text-gray-700">Producto</label>
                        <select name="items[0][producto_id]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="">Seleccione un producto...</option>
                            @foreach($productos as $p)
                                @php $stock = $p->inventarios->first()?->stock ?? 0; @endphp
                                <option value="{{ $p->id }}">{{ $p->nombre }} (Stock en origen: {{ $stock }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-medium text-gray-700">Cantidad</label>
                        <input type="number" step="0.001" min="0.001" name="items[0][cantidad]" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" placeholder="1.000">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-medium text-gray-700">Observación</label>
                        <input type="text" name="items[0][observaciones]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" placeholder="Opcional">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('traslados.index') }}" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md shadow">
                Despachar Mercancía (En Tránsito)
            </button>
        </div>
    </form>
</div>
@endsection
