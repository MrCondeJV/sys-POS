@extends('layouts.app')

@section('title', 'Nueva Resolución DIAN')

@section('content')
<div class="space-y-6">
    <x-page-header
        title="Nueva Resolución de Facturación DIAN"
        subtitle="Configura el prefijo, rango de numeración y vigencia autorizada por la DIAN."
        :breadcrumbs="[
            ['label' => 'Resoluciones DIAN', 'url' => route('resoluciones.index')],
            ['label' => 'Nueva Resolución']
        ]">
        <x-slot:actions>
            <a href="{{ route('resoluciones.index') }}" class="btn-secondary">
                Cancelar
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="max-w-3xl mx-auto">
        <div class="card p-6">
            <form action="{{ route('resoluciones.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-base">Número de Resolución DIAN <span class="text-red-500">*</span></label>
                        <input type="text" name="numero_resolucion" value="{{ old('numero_resolucion') }}" required class="input-base text-xs font-mono" placeholder="Ej. 18764000001">
                        @error('numero_resolucion') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label-base">Prefijo Autorizado <span class="text-red-500">*</span></label>
                        <input type="text" name="prefijo" value="{{ old('prefijo') }}" required class="input-base text-xs uppercase font-mono" placeholder="Ej. SETT, FE, POS">
                        @error('prefijo') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label-base">Rango Desde <span class="text-red-500">*</span></label>
                        <input type="number" name="rango_desde" value="{{ old('rango_desde', 1) }}" min="1" required class="input-base text-xs font-mono tabular-nums">
                        @error('rango_desde') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label-base">Rango Hasta <span class="text-red-500">*</span></label>
                        <input type="number" name="rango_hasta" value="{{ old('rango_hasta', 50000) }}" min="1" required class="input-base text-xs font-mono tabular-nums">
                        @error('rango_hasta') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label-base">Fecha Inicio Vigencia <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', date('Y-m-d')) }}" required class="input-base text-xs">
                        @error('fecha_inicio') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label-base">Fecha Fin Vigencia <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_vigencia" value="{{ old('fecha_vigencia', date('Y-m-d', strtotime('+1 year'))) }}" required class="input-base text-xs">
                        @error('fecha_vigencia') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="label-base">Clave Técnica DIAN</label>
                        <input type="password" name="clave_tecnica" value="{{ old('clave_tecnica') }}" class="input-base text-xs font-mono" placeholder="Clave técnica para cálculo del CUFE">
                        @error('clave_tecnica') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label-base">Estado <span class="text-red-500">*</span></label>
                        <select name="estado" required class="input-select text-xs">
                            @foreach($estados as $est)
                                <option value="{{ $est->value }}" @selected(old('estado') === $est->value)>{{ $est->label() }}</option>
                            @endforeach
                        </select>
                        @error('estado') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="es_predeterminada" value="1" @checked(old('es_predeterminada', true)) class="rounded border-slate-300 text-brand-600 shadow-sm focus:ring-brand-500 h-4 w-4">
                            <span class="ml-2 text-xs font-medium text-slate-700">Resolución Principal / Predeterminada</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
                    <a href="{{ route('resoluciones.index') }}" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">Guardar Resolución</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
