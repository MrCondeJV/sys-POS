@extends('layouts.app')

@section('title', 'Configuración de Impuestos')

@section('content')
<div class="w-full max-w-[1680px] mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">
                <span>Configuración</span>
                <span>&rsaquo;</span>
                <span class="text-indigo-600">Impuestos</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-2.5 rounded-2xl bg-indigo-600 text-white shadow-md">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                    </svg>
                </span>
                Impuestos y Gravámenes
            </h1>
            <p class="text-sm text-slate-500 mt-1">Configure tarifas de IVA, productos exentos y excluidos de acuerdo con la normatividad tributaria.</p>
        </div>
        <div>
            <a href="{{ route('impuestos.create') }}" class="inline-flex items-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-md shadow-indigo-100 transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo Impuesto
            </a>
        </div>
    </div>



    <!-- Tabla de Impuestos -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">Código</th>
                        <th class="py-3.5 px-4">Nombre / Tarifa</th>
                        <th class="py-3.5 px-4">Tipo</th>
                        <th class="py-3.5 px-4 text-center">Porcentaje (%)</th>
                        <th class="py-3.5 px-4 text-center">Predeterminado</th>
                        <th class="py-3.5 px-4 text-center">Estado</th>
                        <th class="py-3.5 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($impuestos as $imp)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $imp->codigo }}</td>
                        <td class="py-3 px-4">
                            <div class="font-bold text-slate-900">{{ $imp->nombre }}</div>
                            @if($imp->descripcion)
                                <div class="text-xs text-slate-400">{{ $imp->descripcion }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $imp->tipo->badgeClasses() }}">
                                {{ $imp->tipo->value }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-extrabold text-base {{ $imp->porcentaje > 0 ? 'text-indigo-600' : 'text-slate-400' }}">
                            {{ number_format($imp->porcentaje, 1) }}%
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($imp->por_defecto)
                                <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800">
                                    ★ Por Defecto
                                </span>
                            @else
                                <form action="{{ route('impuestos.por-defecto', $imp) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-slate-400 hover:text-indigo-600 font-semibold transition">
                                        Fijar predeterminado
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $imp->estado->value === 'ACTIVO' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                {{ $imp->estado->value }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('impuestos.edit', $imp) }}" class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition" title="Editar">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </a>
                                <form action="{{ route('impuestos.destroy', $imp) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar este impuesto?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-slate-100 rounded-lg transition" title="Eliminar">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">No hay impuestos configurados para esta empresa.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $impuestos->links() }}
        </div>
    </div>
</div>
@endsection
