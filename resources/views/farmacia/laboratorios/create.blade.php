@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-black text-slate-800 tracking-tight">Nuevo Laboratorio Farmacéutico</h1>
</div>

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
        <form action="{{ route('laboratorios.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Nombre del Laboratorio *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej. Bayer, Pfizer, Genfar, Tecnoquímicas">
                @error('nombre') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Código Interno</label>
                    <input type="text" name="codigo" value="{{ old('codigo') }}" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej. LAB-01">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Estado *</label>
                <select name="estado" required class="w-full rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="ACTIVO">Activo</option>
                    <option value="INACTIVO">Inactivo</option>
                </select>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('laboratorios.index') }}" class="px-4 py-2 border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition text-sm">Cancelar</a>
                <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm transition text-sm">Guardar Laboratorio</button>
            </div>
        </form>
    </div>
</div>
@endsection
