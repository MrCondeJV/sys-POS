@extends('layouts.app')

@section('title', 'Editar Lista de Precios')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-page-header
        :title="'Editar Lista: ' . $listaPrecio->nombre"
        subtitle="Modifica reglas de ajuste, estado o predeterminación."
        :breadcrumbs="[
            ['label' => 'Listas de Precios', 'url' => route('listas-precios.index')],
            ['label' => 'Editar Lista']
        ]"
    >
        <x-slot:actions>
            <x-button variant="outline" size="sm" href="{{ route('listas-precios.index') }}">
                Volver al listado
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white border border-slate-200/80 rounded-xl p-6 shadow-xs">
        <form action="{{ route('listas-precios.update', $listaPrecio) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-field label="Nombre de la Lista" name="nombre" :required="true">
                        <input type="text" name="nombre" value="{{ old('nombre', $listaPrecio->nombre) }}" required
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                    </x-field>
                </div>
                <div>
                    <x-field label="Código Identificador" name="codigo">
                        <input type="text" name="codigo" value="{{ old('codigo', $listaPrecio->codigo) }}"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold uppercase focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                    </x-field>
                </div>
            </div>

            <div>
                <x-field label="Descripción" name="descripcion">
                    <input type="text" name="descripcion" value="{{ old('descripcion', $listaPrecio->descripcion) }}"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                </x-field>
            </div>

            <div>
                <x-field label="Tipo de Regla / Ajuste" name="tipo_ajuste" :required="true">
                    <select name="tipo_ajuste" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium text-slate-800 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                        @foreach(App\Enums\TipoAjusteListaPrecio::cases() as $tipo)
                            <option value="{{ $tipo->value }}" {{ old('tipo_ajuste', $listaPrecio->tipo_ajuste->value) === $tipo->value ? 'selected' : '' }}>
                                {{ $tipo->label() }}
                            </option>
                        @endforeach
                    </select>
                </x-field>
            </div>

            <div>
                <x-field label="Porcentaje de Ajuste General (%)" name="porcentaje_defecto">
                    <input type="number" step="0.01" min="0" max="100" name="porcentaje_defecto" value="{{ old('porcentaje_defecto', $listaPrecio->porcentaje_defecto) }}"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-bold tabular-nums focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                </x-field>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div class="flex items-center space-x-3 p-3 bg-slate-50/70 rounded-lg border border-slate-200">
                    <input type="checkbox" id="es_predeterminada" name="es_predeterminada" value="1" {{ old('es_predeterminada', $listaPrecio->es_predeterminada) ? 'checked' : '' }}
                           class="h-4 w-4 text-brand-600 rounded border-slate-300 focus:ring-brand-500">
                    <label for="es_predeterminada" class="text-xs font-medium text-slate-700 cursor-pointer">
                        Establecer como predeterminada
                    </label>
                </div>

                <div>
                    <x-field label="Estado de la Lista" name="estado">
                        <select name="estado" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold text-slate-800 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                            <option value="ACTIVO" {{ old('estado', $listaPrecio->estado->value) === 'ACTIVO' ? 'selected' : '' }}>Estado: ACTIVO</option>
                            <option value="INACTIVO" {{ old('estado', $listaPrecio->estado->value) === 'INACTIVO' ? 'selected' : '' }}>Estado: INACTIVO</option>
                        </select>
                    </x-field>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                <x-button variant="outline" href="{{ route('listas-precios.index') }}">
                    Cancelar
                </x-button>
                <x-button variant="primary" type="submit">
                    Actualizar Lista
                </x-button>
            </div>
        </form>
    </div>
</div>
@endsection
