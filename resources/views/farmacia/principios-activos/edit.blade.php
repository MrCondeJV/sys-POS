@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-black text-slate-800 tracking-tight">Editar Principio Activo: {{ $principio->nombre }}</h1>
</div>

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
        <form action="{{ route('principios-activos.update', $principio) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Nombre de la Sustancia / Fármaco *</label>
                <input type="text" name="nombre" value="{{ old('nombre', $principio->nombre) }}" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('nombre') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Concentración / Forma</label>
                <input type="text" name="concentracion" value="{{ old('concentracion', $principio->concentracion) }}" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('concentracion') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Descripción / Indicaciones</label>
                <textarea name="descripcion" rows="3" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('descripcion', $principio->descripcion) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Estado *</label>
                <select name="estado" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="ACTIVO" @selected(old('estado', $principio->estado->value) === 'ACTIVO')>Activo</option>
                    <option value="INACTIVO" @selected(old('estado', $principio->estado->value) === 'INACTIVO')>Inactivo</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('principios-activos.index') }}" class="px-4 py-2 border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition text-sm">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm transition text-sm">Actualizar</button>
            </div>
        </form>
    </div>
</div>
@endsection
