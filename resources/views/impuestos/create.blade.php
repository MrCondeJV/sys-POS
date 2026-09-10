@extends('layouts.app')

@section('title', 'Nuevo Impuesto')

@section('content')
<div class="w-full max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <a href="{{ route('impuestos.index') }}" class="hover:text-indigo-600 transition">Impuestos</a>
                <span>&rsaquo;</span>
                <span class="text-indigo-600">Crear Tarifa</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Configurar Nuevo Gravamen / Impuesto</h1>
        </div>
        <div>
            <a href="{{ route('impuestos.index') }}" class="px-4 py-2.5 text-sm font-semibold rounded-xl border border-slate-300 bg-white hover:bg-slate-50 transition shadow-sm">
                &larr; Volver
            </a>
        </div>
    </div>

    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200/70 shadow-sm">
        <form method="POST" action="{{ route('impuestos.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Código Identificador *</label>
                    <input type="text" name="codigo" value="{{ old('codigo') }}" required placeholder="Ej: IVA19, EXENTO" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 uppercase font-mono transition">
                    @error('codigo') <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nombre Comercial *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej: IVA General 19%" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    @error('nombre') <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tipo de Impuesto *</label>
                    <select name="tipo" required class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        @foreach($tipos as $tipo)
                            <option value="{{ $tipo->value }}" {{ old('tipo') === $tipo->value ? 'selected' : '' }}>
                                {{ $tipo->value }} ({{ $tipo->label() }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Porcentaje (%) *</label>
                    <input type="number" step="0.01" min="0" max="100" name="porcentaje" value="{{ old('porcentaje', '0.00') }}" required class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-extrabold text-indigo-600 transition">
                    @error('porcentaje') <p class="text-xs text-red-600 mt-1 font-semibold">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Estado *</label>
                    <select name="estado" required class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        @foreach($estados as $est)
                            <option value="{{ $est->value }}" {{ old('estado') === $est->value ? 'selected' : '' }}>
                                {{ $est->value }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Descripción / Norma de Referencia</label>
                <textarea name="descripcion" rows="2" placeholder="Opcional. Ej: Art. 468 del Estatuto Tributario" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">{{ old('descripcion') }}</textarea>
            </div>

            <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <input type="checkbox" id="por_defecto" name="por_defecto" value="1" {{ old('por_defecto') ? 'checked' : '' }} class="h-4 w-4 rounded text-indigo-600 focus:ring-indigo-500 border-slate-300">
                <label for="por_defecto" class="text-sm font-bold text-slate-700 select-none">
                    Establecer como impuesto predeterminado para nuevos productos
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('impuestos.index') }}" class="px-5 py-2.5 text-sm font-semibold rounded-xl border border-slate-300 hover:bg-slate-50 transition">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-sm">
                    Guardar Impuesto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
