@extends('layouts.app')

@section('title', 'Editar Resolución DIAN (' . $resolucion->prefijo . ')')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Editar Resolución DIAN ({{ $resolucion->prefijo }})"
        subtitle="Modificación de vigencias, consecutivos y parámetros fiscales."
        :breadcrumbs="[
            ['label' => 'Resoluciones DIAN', 'url' => route('resoluciones.index')],
            ['label' => 'Editar ' . $resolucion->prefijo]
        ]">
        <x-slot:actions>
            <a href="{{ route('resoluciones.index') }}" class="btn-secondary">
                Cancelar
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="max-w-3xl mx-auto">
        <div class="card p-6">
            <form action="{{ route('resoluciones.update', $resolucion) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-base">Número de Resolución *</label>
                        <input type="text" name="numero_resolucion" value="{{ old('numero_resolucion', $resolucion->numero_resolucion) }}" required class="input-base text-xs font-mono">
                        @error('numero_resolucion') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label-base">Prefijo *</label>
                        <input type="text" name="prefijo" value="{{ old('prefijo', $resolucion->prefijo) }}" required class="input-base text-xs uppercase font-mono">
                        @error('prefijo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label-base">Rango Desde *</label>
                        <input type="number" name="rango_desde" value="{{ old('rango_desde', $resolucion->rango_desde) }}" min="1" required class="input-base text-xs font-mono tabular-nums">
                        @error('rango_desde') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label-base">Rango Hasta *</label>
                        <input type="number" name="rango_hasta" value="{{ old('rango_hasta', $resolucion->rango_hasta) }}" min="1" required class="input-base text-xs font-mono tabular-nums">
                        @error('rango_hasta') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label-base">Consecutivo Actual *</label>
                        <input type="number" name="consecutivo_actual" value="{{ old('consecutivo_actual', $resolucion->consecutivo_actual) }}" min="0" required class="input-base text-xs font-mono tabular-nums">
                        @error('consecutivo_actual') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label-base">Estado *</label>
                        <select name="estado" required class="input-select text-xs">
                            @foreach($estados as $est)
                                <option value="{{ $est->value }}" @selected(old('estado', $resolucion->estado->value) === $est->value)>{{ $est->label() }}</option>
                            @endforeach
                        </select>
                        @error('estado') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label-base">Fecha Inicio *</label>
                        <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', $resolucion->fecha_inicio->format('Y-m-d')) }}" required class="input-base text-xs">
                        @error('fecha_inicio') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label-base">Fecha Vigencia *</label>
                        <input type="date" name="fecha_vigencia" value="{{ old('fecha_vigencia', $resolucion->fecha_vigencia->format('Y-m-d')) }}" required class="input-base text-xs">
                        @error('fecha_vigencia') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="label-base">Clave Técnica DIAN</label>
                        <input type="password" name="clave_tecnica" value="{{ old('clave_tecnica', $resolucion->clave_tecnica) }}" class="input-base text-xs font-mono">
                    </div>

                    <div class="flex items-center pt-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="es_predeterminada" value="1" @checked(old('es_predeterminada', $resolucion->es_predeterminada)) class="rounded border-slate-300 text-brand-600 shadow-sm focus:ring-brand-500 h-4 w-4">
                            <span class="ml-2 text-xs font-medium text-slate-700">Resolución Predeterminada</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                    <a href="{{ route('resoluciones.index') }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
