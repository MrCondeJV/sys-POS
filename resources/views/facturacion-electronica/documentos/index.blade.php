@extends('layouts.app')

@section('content')
<div class=\"mb-6\">
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Facturación Electrónica DIAN</h1>
                <p class="text-sm text-slate-500 font-medium">Comprobantes fiscales transmitidos, CUFE y validaciones oficiales</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('resoluciones.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold rounded-xl transition">
                    ⚙️ Resoluciones DIAN
                </a>
                <a href="{{ route('documentos.index') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                    + Emitir desde Ventas
                </a>
            </div>
        </div>
</div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">


            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4">
                <form method="GET" action="{{ route('facturacion-electronica.index') }}" class="flex flex-wrap gap-3 items-center">
                    <div class="relative flex-1 min-w-[220px]">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por consecutivo o CUFE..." class="w-full pl-10 pr-3 py-2 rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <select name="estado" class="rounded-xl border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Todos los Estados DIAN</option>
                        @foreach($estadosDian as $est)
                            <option value="{{ $est->value }}" @selected(request('estado') === $est->value)>{{ $est->label() }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold rounded-xl transition">
                        Filtrar
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-xs uppercase font-bold text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-4">Consecutivo</th>
                                <th class="px-6 py-4">Tipo</th>
                                <th class="px-6 py-4">Cliente</th>
                                <th class="px-6 py-4">Total</th>
                                <th class="px-6 py-4">CUFE / CUDE</th>
                                <th class="px-6 py-4">Estado DIAN</th>
                                <th class="px-6 py-4 text-right">Detalles</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($documentos as $doc)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-6 py-4 font-bold text-slate-800 font-mono">{{ $doc->consecutivo_completo }}</td>
                                    <td class="px-6 py-4 text-xs font-semibold text-slate-700">{{ $doc->tipo->label() }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-slate-800">{{ $doc->documentoVenta->cliente?->razon_social ?? 'Consumidor Final' }}</div>
                                        <div class="text-xs text-slate-400 font-mono">{{ $doc->documentoVenta->cliente?->numero_documento ?? '222222222222' }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-mono font-bold text-slate-800">${{ number_format($doc->documentoVenta->total, 2) }}</td>
                                    <td class="px-6 py-4 font-mono text-xs text-slate-500 max-w-xs truncate" title="{{ $doc->cufe }}">
                                        {{ $doc->cufe ? substr($doc->cufe, 0, 16) . '...' : 'Pendiente' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($doc->estado_dian === \App\Enums\EstadoDian::ACEPTADO)
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">✓ Aceptado DIAN</span>
                                        @elseif($doc->estado_dian === \App\Enums\EstadoDian::RECHAZADO)
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200">✗ Rechazado</span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-200">{{ $doc->estado_dian->label() }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('facturacion-electronica.show', $doc) }}" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                                        No hay documentos electrónicos emitidos aún.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($documentos->hasPages())
                    <div class="p-4 border-t border-slate-100">
                        {{ $documentos->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
