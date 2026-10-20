@extends('layouts.app')

@section('title', 'Editar Impuesto')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                <a href="{{ route('impuestos.index') }}" class="hover:text-brand-600 transition">Impuestos</a>
                <span>&rsaquo;</span>
                <span class="text-slate-700 font-semibold">Editar Tarifa</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Editar {{ $impuesto->nombre }}</h1>
        </div>
        <div>
            <a href="{{ route('impuestos.index') }}" class="btn-secondary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </div>
    </div>

    <div class="card p-6">
        <form method="POST" action="{{ route('impuestos.update', $impuesto) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label-base">Código Identificador *</label>
                    <input type="text" name="codigo" value="{{ old('codigo', $impuesto->codigo) }}" required class="input uppercase font-mono">
                    @error('codigo') <p class="text-xs text-danger-text mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label-base">Nombre Comercial *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $impuesto->nombre) }}" required class="input">
                    @error('nombre') <p class="text-xs text-danger-text mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="label-base">Tipo de Impuesto *</label>
                    <select name="tipo" required class="input">
                        @foreach($tipos as $tipo)
                            <option value="{{ $tipo->value }}" {{ old('tipo', $impuesto->tipo->value) === $tipo->value ? 'selected' : '' }}>
                                {{ $tipo->value }} ({{ $tipo->label() }})
                            </option>
                        @endforeach
                    </select>
                    @error('tipo') <p class="text-xs text-danger-text mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label-base">Porcentaje (%) *</label>
                    <input type="number" step="0.01" min="0" max="100" name="porcentaje" value="{{ old('porcentaje', $impuesto->porcentaje) }}" required class="input tabular-nums font-mono font-semibold">
                    @error('porcentaje') <p class="text-xs text-danger-text mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label-base">Estado *</label>
                    <select name="estado" required class="input">
                        @foreach($estados as $est)
                            <option value="{{ $est->value }}" {{ old('estado', $impuesto->estado->value) === $est->value ? 'selected' : '' }}>
                                {{ $est->value }}
                            </option>
                        @endforeach
                    </select>
                    @error('estado') <p class="text-xs text-danger-text mt-1 font-medium">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="label-base">Descripción / Norma de Referencia</label>
                <textarea name="descripcion" rows="2" class="input">{{ old('descripcion', $impuesto->descripcion) }}</textarea>
                @error('descripcion') <p class="text-xs text-danger-text mt-1 font-medium">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3 p-3.5 bg-slate-50 rounded-lg border border-slate-200">
                <input type="checkbox" id="por_defecto" name="por_defecto" value="1" {{ old('por_defecto', $impuesto->por_defecto) ? 'checked' : '' }} class="h-4 w-4 rounded text-brand-600 focus:ring-brand-500 border-slate-300">
                <label for="por_defecto" class="text-sm font-medium text-slate-700 select-none cursor-pointer">
                    Establecer como impuesto predeterminado para nuevos productos
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('impuestos.index') }}" class="btn-secondary">
                    Cancelar
                </a>
                <button type="submit" class="btn-primary">
                    Actualizar Impuesto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
