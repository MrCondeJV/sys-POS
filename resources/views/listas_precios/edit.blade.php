@extends('layouts.app')

@section('title', 'Editar Lista de Precios')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider block">Catálogo de Precios</span>
            <h1 class="text-xl font-black text-slate-900">Editar Lista: {{ $listaPrecio->nombre }}</h1>
        </div>
        <a href="{{ route('listas-precios.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800">
            &larr; Volver al listado
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-sm">
        <form action="{{ route('listas-precios.update', $listaPrecio) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre de la Lista *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $listaPrecio->nombre) }}" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:border-indigo-600">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Código Identificador</label>
                    <input type="text" name="codigo" value="{{ old('codigo', $listaPrecio->codigo) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold uppercase focus:border-indigo-600">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Descripción</label>
                <input type="text" name="descripcion" value="{{ old('descripcion', $listaPrecio->descripcion) }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tipo de Regla / Ajuste *</label>
                <select name="tipo_ajuste" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-800">
                    @foreach(App\Enums\TipoAjusteListaPrecio::cases() as $tipo)
                        <option value="{{ $tipo->value }}" {{ old('tipo_ajuste', $listaPrecio->tipo_ajuste->value) === $tipo->value ? 'selected' : '' }}>
                            {{ $tipo->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Porcentaje de Ajuste General (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="porcentaje_defecto" value="{{ old('porcentaje_defecto', $listaPrecio->porcentaje_defecto) }}"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div class="flex items-center space-x-3 p-3 bg-slate-50 rounded-2xl border border-slate-200">
                    <input type="checkbox" id="es_predeterminada" name="es_predeterminada" value="1" {{ old('es_predeterminada', $listaPrecio->es_predeterminada) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 rounded border-slate-300">
                    <label for="es_predeterminada" class="text-xs font-bold text-slate-700 cursor-pointer">
                        Establecer como predeterminada
                    </label>
                </div>

                <div>
                    <select name="estado" class="w-full px-3.5 py-3 rounded-2xl border border-slate-200 text-xs font-bold text-slate-800">
                        <option value="ACTIVO" {{ old('estado', $listaPrecio->estado->value) === 'ACTIVO' ? 'selected' : '' }}>Estado: ACTIVO</option>
                        <option value="INACTIVO" {{ old('estado', $listaPrecio->estado->value) === 'INACTIVO' ? 'selected' : '' }}>Estado: INACTIVO</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="{{ route('listas-precios.index') }}" class="px-4 py-2.5 text-xs font-bold text-slate-500 hover:bg-slate-50 rounded-xl">Cancelar</a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm">
                    Actualizar Lista
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
