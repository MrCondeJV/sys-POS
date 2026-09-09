@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-black text-slate-800 tracking-tight">Registrar Nuevo Lote de Medicamento</h1>
</div>

<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
        <form action="{{ route('lotes.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Medicamento / Producto *</label>
                    <select name="producto_id" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Seleccione un medicamento...</option>
                        @foreach($productos as $prod)
                            <option value="{{ $prod->id }}" @selected(old('producto_id') == $prod->id)>
                                {{ $prod->nombre }} ({{ $prod->codigo }})
                            </option>
                        @endforeach
                    </select>
                    @error('producto_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Sede / Sucursal *</label>
                    <select name="sucursal_id" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($sucursales as $suc)
                            <option value="{{ $suc->id }}" @selected(old('sucursal_id') == $suc->id)>{{ $suc->nombre }}</option>
                        @endforeach
                    </select>
                    @error('sucursal_id') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Número de Lote *</label>
                    <input type="text" name="numero_lote" value="{{ old('numero_lote') }}" required class="w-full rounded-xl border-slate-200 text-sm uppercase focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej. LOT-2026-X89">
                    @error('numero_lote') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Fecha de Fabricación</label>
                    <input type="date" name="fecha_fabricacion" value="{{ old('fecha_fabricacion') }}" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Fecha de Vencimiento *</label>
                    <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('fecha_vencimiento') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Stock Inicial *</label>
                    <input type="number" step="0.01" name="stock_inicial" value="{{ old('stock_inicial', 1) }}" min="0" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('stock_inicial') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Costo Unitario *</label>
                    <input type="number" step="0.01" name="costo_unitario" value="{{ old('costo_unitario', 0) }}" min="0" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('costo_unitario') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('lotes.index') }}" class="px-4 py-2 border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition text-sm">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm transition text-sm">Guardar Lote</button>
            </div>
        </form>
    </div>
</div>
@endsection
