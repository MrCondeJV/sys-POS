@extends('layouts.app')

@section('title', 'Listas de Precios')

@section('content')
<div class="max-w-[1680px] mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
        <div class="flex items-center space-x-4">
            <div class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black shadow-md shadow-indigo-500/20">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Listas de Precios</h1>
                <p class="text-xs text-slate-500 mt-0.5">Administra tarifas personalizadas por cliente, mayoristas, distribuidores y promociones especiales.</p>
            </div>
        </div>

        @can('create', App\Models\ListaPrecio::class)
        <a href="{{ route('listas-precios.create') }}" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" /></svg>
            <span>Nueva Lista de Precios</span>
        </a>
        @endcan
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="bg-white border border-slate-200 rounded-3xl p-4 shadow-sm">
        <form method="GET" action="{{ route('listas-precios.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, código o descripción..."
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>
            <div class="flex items-center space-x-2">
                <select name="estado" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 bg-white hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm">
                    <option value="">Todos los Estados</option>
                    <option value="ACTIVO" {{ request('estado') === 'ACTIVO' ? 'selected' : '' }}>Activas</option>
                    <option value="INACTIVO" {{ request('estado') === 'INACTIVO' ? 'selected' : '' }}>Inactivas</option>
                </select>
                <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-sm transition">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Tabla de Listas de Precios -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left divide-y divide-slate-200 text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Lista / Código</th>
                        <th class="py-3.5 px-5">Tipo de Ajuste</th>
                        <th class="py-3.5 px-5 text-center">Ajuste Base</th>
                        <th class="py-3.5 px-5 text-center">Precios Específicos</th>
                        <th class="py-3.5 px-5 text-center">Clientes</th>
                        <th class="py-3.5 px-5 text-center">Estado</th>
                        <th class="py-3.5 px-5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($listas as $lista)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center space-x-2">
                                <span class="font-bold text-slate-900 text-sm">{{ $lista->nombre }}</span>
                                @if($lista->es_predeterminada)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800">
                                        Predeterminada
                                    </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                {{ $lista->codigo ? 'CÓD: ' . $lista->codigo : 'Sin código' }}
                                @if($lista->descripcion)
                                    &bull; <span class="text-slate-500 font-sans">{{ Str::limit($lista->descripcion, 40) }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                                {{ $lista->tipo_ajuste->label() }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($lista->tipo_ajuste === App\Enums\TipoAjusteListaPrecio::PORCENTAJE_DESCUENTO)
                                <span class="text-emerald-600 font-black">-{{ $lista->porcentaje_defecto }}%</span>
                            @elseif($lista->tipo_ajuste === App\Enums\TipoAjusteListaPrecio::PORCENTAJE_AUMENTO)
                                <span class="text-indigo-600 font-black">+{{ $lista->porcentaje_defecto }}%</span>
                            @else
                                <span class="text-slate-400 font-medium">Por producto</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                            {{ $lista->detalles_count }} items
                        </td>
                        <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                            {{ $lista->clientes_count }} clientes
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black {{ $lista->estado === App\Enums\EstadoGeneral::ACTIVO ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                {{ $lista->estado->label() }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a href="{{ route('listas-precios.show', $lista) }}" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-xl transition text-[11px]">
                                    Configurar Precios &rarr;
                                </a>
                                @can('update', $lista)
                                <a href="{{ route('listas-precios.edit', $lista) }}" class="p-1.5 text-slate-500 hover:text-slate-800 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                @endcan
                                @can('delete', $lista)
                                @if(!$lista->es_predeterminada && $lista->clientes_count === 0)
                                <form action="{{ route('listas-precios.destroy', $lista) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta lista de precios?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-red-400 hover:text-red-700 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            No hay listas de precios registradas. Haz clic en "Nueva Lista de Precios" para comenzar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($listas->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $listas->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
