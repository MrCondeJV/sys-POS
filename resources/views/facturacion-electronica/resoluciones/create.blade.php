@extends('layouts.app')

@section('content')
<div class=\"mb-6\">
<h1 class="text-2xl font-black text-slate-800 tracking-tight">Nueva Resolución de Facturación DIAN</h1>
</div>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
                <form action="{{ route('resoluciones.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Número de Resolución DIAN *</label>
                            <input type="text" name="numero_resolucion" value="{{ old('numero_resolucion') }}" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej. 18764000001">
                            @error('numero_resolucion') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Prefijo Autorizado *</label>
                            <input type="text" name="prefijo" value="{{ old('prefijo') }}" required class="w-full rounded-xl border-slate-200 text-sm uppercase focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej. SETT, FE, POS">
                            @error('prefijo') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Rango Desde *</label>
                            <input type="number" name="rango_desde" value="{{ old('rango_desde', 1) }}" min="1" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('rango_desde') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Rango Hasta *</label>
                            <input type="number" name="rango_hasta" value="{{ old('rango_hasta', 50000) }}" min="1" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('rango_hasta') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Fecha Inicio Vigencia *</label>
                            <input type="date" name="fecha_inicio" value="{{ old('fecha_inicio', date('Y-m-d')) }}" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('fecha_inicio') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Fecha Fin Vigencia *</label>
                            <input type="date" name="fecha_vigencia" value="{{ old('fecha_vigencia', date('Y-m-d', strtotime('+1 year'))) }}" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('fecha_vigencia') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Clave Técnica DIAN</label>
                            <input type="password" name="clave_tecnica" value="{{ old('clave_tecnica') }}" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Clave técnica para cálculo del CUFE">
                            @error('clave_tecnica') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Estado *</label>
                            <select name="estado" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @foreach($estados as $est)
                                    <option value="{{ $est->value }}" @selected(old('estado') === $est->value)>{{ $est->label() }}</option>
                                @endforeach
                            </select>
                            @error('estado') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center pt-6">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="es_predeterminada" value="1" @checked(old('es_predeterminada', true)) class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm font-medium text-slate-700">Resolución Principal / Predeterminada</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <a href="{{ route('resoluciones.index') }}" class="px-4 py-2 border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition text-sm">Cancelar</a>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm transition text-sm">Guardar Resolución</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
