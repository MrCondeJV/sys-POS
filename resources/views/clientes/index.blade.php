@extends('layouts.app')

@section('title', 'Catálogo de Clientes')

@section('content')
<div class="max-w-[1680px] mx-auto space-y-6">

    <!-- Encabezado & Botones de Acción -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Catálogo de Clientes</h1>
            <p class="text-sm text-slate-500 mt-1">
                Gestión de clientes, identificación tributaria, parámetros de cartera y consumidor final para POS.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('clientes.create') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Nuevo Cliente
            </a>
        </div>
    </div>

    <!-- Tarjetas de Métricas Rápidas (KPIs) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-3xl border border-slate-200/70 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Clientes</div>
                <div class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-slate-900">{{ number_format($totalClientes) }}</div>
            <div class="text-xs text-slate-500 mt-1">Registrados en la empresa</div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/70 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Clientes Activos</div>
                <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-emerald-600">{{ number_format($totalActivos) }}</div>
            <div class="text-xs text-slate-500 mt-1">Habilitados para facturar</div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/70 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Con Crédito Comercial</div>
                <div class="p-2 rounded-xl bg-amber-50 text-amber-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-amber-600">{{ number_format($totalConCredito) }}</div>
            <div class="text-xs text-slate-500 mt-1">Cupo y cartera autorizada</div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/70 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">Empresas / Jurídicas</div>
                <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
            <div class="mt-2 text-2xl font-black text-blue-600">{{ number_format($totalJuridicos) }}</div>
            <div class="text-xs text-slate-500 mt-1">Sociedades con NIT</div>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="bg-white rounded-3xl border border-slate-200/70 p-4 shadow-sm">
        <form method="GET" action="{{ route('clientes.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="buscar" value="{{ $term ?? '' }}"
                    placeholder="Buscar por razón social, documento, teléfono, email..."
                    class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-xl text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>

            <div class="sm:col-span-3">
                <select name="tipo_persona" class="block w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <option value="">Todos los tipos de persona</option>
                    @foreach(\App\Enums\TipoPersona::cases() as $tp)
                        <option value="{{ $tp->value }}" @selected(($tipoPersona ?? '') === $tp->value)>
                            {{ $tp->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <select name="estado" class="block w-full py-2.5 px-3 border border-slate-300 rounded-xl text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <option value="">Todos los estados</option>
                    <option value="ACTIVO" @selected(($estado ?? '') === 'ACTIVO')>Activo</option>
                    <option value="INACTIVO" @selected(($estado ?? '') === 'INACTIVO')>Inactivo</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit"
                    class="flex-1 inline-flex items-center justify-center px-4 py-2.5 border border-transparent rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition">
                    Filtrar
                </button>
                @if(!empty($term) || !empty($tipoPersona) || !empty($estado))
                    <a href="{{ route('clientes.index') }}"
                        class="p-2.5 border border-slate-300 text-slate-600 hover:text-slate-900 rounded-xl hover:bg-slate-50 transition" title="Limpiar filtros">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla Principal de Clientes -->
    <div class="bg-white rounded-3xl border border-slate-200/70 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                            Cliente / Razón Social
                        </th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                            Identificación
                        </th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                            Contacto
                        </th>
                        <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                            Línea de Crédito
                        </th>
                        <th scope="col" class="px-5 py-3.5 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">
                            Estado
                        </th>
                        <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($clientes as $cliente)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="h-10 w-10 flex-shrink-0 rounded-xl flex items-center justify-center font-bold text-sm {{ $cliente->isConsumidorFinal() ? 'bg-amber-100 text-amber-800 ring-2 ring-amber-400/50' : ($cliente->tipo_persona->value === 'JURIDICA' ? 'bg-blue-100 text-blue-700' : 'bg-indigo-100 text-indigo-700') }}">
                                        @if($cliente->isConsumidorFinal())
                                            CF
                                        @else
                                            {{ strtoupper(substr($cliente->razon_social, 0, 2)) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 text-sm truncate max-w-xs block">
                                                {{ $cliente->razon_social }}
                                            </span>
                                            @if($cliente->isConsumidorFinal())
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                                    PREDETERMINADO POS
                                                </span>
                                            @endif
                                        </div>
                                        @if($cliente->nombre_comercial)
                                            <span class="text-xs text-slate-500 font-medium block">
                                                Comercial: {{ $cliente->nombre_comercial }}
                                            </span>
                                        @endif
                                        <span class="text-xs text-slate-400 block">
                                            {{ $cliente->ciudad ?? 'Sin ciudad' }}{{ $cliente->departamento ? ', ' . $cliente->departamento : '' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-slate-900">
                                    {{ $cliente->tipo_documento->value }} {{ $cliente->numero_documento }}
                                </div>
                                <div class="mt-0.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $cliente->tipo_persona->value === 'JURIDICA' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-indigo-50 text-indigo-700 border border-indigo-200' }}">
                                        {{ $cliente->tipo_persona->label() }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-700 font-medium">
                                    {{ $cliente->telefono ?? 'Sin teléfono' }}
                                </div>
                                <div class="text-xs text-slate-500 truncate max-w-xs">
                                    {{ $cliente->email ?? 'Sin correo' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($cliente->tieneCredito())
                                    <div class="text-sm font-bold text-emerald-600">
                                        ${{ number_format($cliente->cupo_credito, 2) }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        Plazo: {{ $cliente->plazo_dias }} días
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 font-medium italic">Sin crédito</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($cliente->estado->value === 'ACTIVO')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        <span class="w-1.5 h-1.5 mr-1.5 bg-emerald-500 rounded-full"></span>
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200/60">
                                        <span class="w-1.5 h-1.5 mr-1.5 bg-red-500 rounded-full"></span>
                                        Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('clientes.edit', $cliente) }}"
                                        class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                        title="Editar cliente">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </a>

                                    @if($cliente->puedeEliminarse())
                                        <form action="{{ route('clientes.destroy', $cliente) }}" method="POST"
                                            onsubmit="return confirm('¿Estás seguro de que deseas eliminar este cliente?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                                title="Eliminar cliente">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="p-1.5 text-slate-300 cursor-not-allowed" title="El Consumidor Final predeterminado no puede ser eliminado">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="max-w-sm mx-auto">
                                    <div class="h-12 w-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-900">No se encontraron clientes</h3>
                                    <p class="text-xs text-slate-500 mt-1">No hay clientes que coincidan con los criterios de búsqueda o filtros seleccionados.</p>
                                    <div class="mt-4">
                                        <a href="{{ route('clientes.create') }}" class="inline-flex items-center px-3 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                                            Registrar Cliente
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clientes->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $clientes->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
