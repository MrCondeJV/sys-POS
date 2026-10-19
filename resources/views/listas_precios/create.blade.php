@extends('layouts.app')

@section('title', 'Nueva Lista de Precios')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-page-header
        title="Nueva Lista de Precios"
        subtitle="Define tarifas especiales, listas mayoristas o descuentos por volumen."
        :breadcrumbs="[
            ['label' => 'Listas de Precios', 'url' => route('listas-precios.index')],
            ['label' => 'Nueva Lista']
        ]"
    >
        <x-slot:actions>
            <x-button variant="outline" size="sm" href="{{ route('listas-precios.index') }}">
                Volver al listado
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white border border-slate-200/80 rounded-xl p-6 shadow-xs">
        <form action="{{ route('listas-precios.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-field label="Nombre de la Lista" name="nombre" :required="true">
                        <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Mayoristas / Clientes VIP"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                    </x-field>
                </div>
                <div>
                    <x-field label="Código Identificador" name="codigo">
                        <input type="text" name="codigo" value="{{ old('codigo') }}" placeholder="Ej: LISTA-MAYORISTA"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-semibold uppercase focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                    </x-field>
                </div>
            </div>

            <div>
                <x-field label="Descripción" name="descripcion">
                    <input type="text" name="descripcion" value="{{ old('descripcion') }}" placeholder="Ej: Tarifa aplicada a compras superiores a 50 unidades"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                </x-field>
            </div>

            <div>
                <x-field label="Tipo de Regla / Ajuste" name="tipo_ajuste" :required="true">
                    <select name="tipo_ajuste" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium text-slate-800 focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                        @foreach(App\Enums\TipoAjusteListaPrecio::cases() as $tipo)
                            <option value="{{ $tipo->value }}" {{ old('tipo_ajuste') === $tipo->value ? 'selected' : '' }}>
                                {{ $tipo->label() }}
                            </option>
                        @endforeach
                    </select>
                </x-field>
            </div>

            <div>
                <x-field label="Porcentaje de Ajuste General (%)" name="porcentaje_defecto">
                    <input type="number" step="0.01" min="0" max="100" name="porcentaje_defecto" value="{{ old('porcentaje_defecto', 0) }}" placeholder="0.00"
                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-bold tabular-nums focus:outline-none focus:ring-1 focus:ring-brand-500 focus:border-brand-500 transition">
                </x-field>
                <span class="text-[11px] text-slate-400 block mt-1">Aplica automáticamente sobre el precio regular cuando no se fija un valor individual.</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div class="flex items-center space-x-3 p-3 bg-slate-50/70 rounded-lg border border-slate-200">
                    <input type="checkbox" name="es_predeterminada" id="es_predeterminada" value="1" {{ old('es_predeterminada') ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <label for="es_predeterminada" class="text-xs font-medium text-slate-700 cursor-pointer">
                        Lista Predeterminada
                    </label>
                </div>

                <div class="flex items-center space-x-3 p-3 bg-slate-50/70 rounded-lg border border-slate-200">
                    <input type="checkbox" name="estado" id="estado" value="ACTIVO" {{ old('estado', 'ACTIVO') === 'ACTIVO' ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    <label for="estado" class="text-xs font-medium text-slate-700 cursor-pointer">
                        Lista Activa
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                <x-button variant="outline" href="{{ route('listas-precios.index') }}">
                    Cancelar
                </x-button>
                <x-button variant="primary" type="submit">
                    Guardar Lista
                </x-button>
            </div>
        </form>
    </div>
</div>
@endsection
